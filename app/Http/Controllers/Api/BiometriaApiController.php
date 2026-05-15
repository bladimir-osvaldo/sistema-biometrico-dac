<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;
use App\Models\HuellaDocente;
use App\Models\AuditLog;

/**
 * Controlador API de Enrolamiento Biométrico para dispositivos ESP32 + R307.
 *
 * El sensor R307 guarda las plantillas en su memoria interna con un ID
 * numérico. El ESP32 informa al servidor qué docente está asociado a cada
 * ID (r307_id) para que las marcaciones posteriores se puedan resolver.
 */
class BiometriaApiController extends Controller
{
    /**
     * Valida que el token del dispositivo sea correcto.
     */
    protected function deviceOrFail(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('api_token');
        $device = Device::where('api_token', $token)->first();

        if (!$device) {
            return null;
        }

        $device->update([
            'estado' => 'ONLINE',
            'ultimo_heartbeat' => now()
        ]);

        return $device;
    }

    /**
     * Registra la asociación entre un docente y un ID del sensor R307.
     * POST /api/v1/biometria/enroll
     * Body (JSON o form): api_token, codigo_docente | user_id,
     *                      r307_id, dedo_numero, dedo_nombre
     */
    public function enroll(Request $request)
    {
        $device = $this->deviceOrFail($request);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Dispositivo no autorizado.'], 401);
        }

        $request->validate([
            'r307_id'      => 'required|integer|min:1|max:300',
            'codigo_docente' => 'required_without:user_id|string',
            'user_id'      => 'required_without:codigo_docente|exists:users,id',
            'dedo_numero'  => 'nullable|integer|min:1|max:3',
            'dedo_nombre'  => 'nullable|string|max:100',
        ]);

        // Resolver el docente (por código institucional o por ID)
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
        } else {
            // Búsqueda tolerante: acepta "1001", "DOC-1001", "doc-1001", "DOC-1001", etc.
            $codigo = trim($request->codigo_docente);
            $user = User::where('codigo_docente', $codigo)
                ->orWhere('codigo_docente', 'DOC-' . ltrim($codigo, '0'))
                ->orWhere('codigo_docente', 'like', '%' . $codigo . '%')
                ->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Docente no encontrado (código: {$request->codigo_docente})."
            ], 404);
        }

        // Verificar que el r307_id no esté ya asignado a otro docente
        $ocupado = HuellaDocente::where('r307_id', $request->r307_id)
            ->where('user_id', '!=', $user->id)
            ->first();

        if ($ocupado) {
            return response()->json([
                'success' => false,
                'message' => "El ID {$request->r307_id} ya está asignado a otro docente."
            ], 409);
        }

        $dedoNumero = $request->input('dedo_numero', 1);
        $dedoNombre = $request->input('dedo_nombre', 'Huella principal');

        // Guardar/actualizar la asociación
        $huella = HuellaDocente::updateOrCreate(
            [
                'user_id'      => $user->id,
                'dedo_numero'  => $dedoNumero,
            ],
            [
                'dedo_nombre'    => $dedoNombre,
                'r307_id'        => $request->r307_id,
                'template_hash'  => 'R307:' . $request->r307_id,
                'registrado_por' => $user->id,
                'estado'         => 'ACTIVO',
            ]
        );

        // Referencia rápida en la tabla users
        $user->update(['huella_template_hash' => 'R307:' . $request->r307_id]);

        AuditLog::create([
            'user_id'    => $user->id,
            'accion'     => 'ENROLAMIENTO_ESP32',
            'modulo'     => 'Biometría',
            'detalles'   => "Docente {$user->name} enrolado en R307 ID {$request->r307_id} vía {$device->nombre}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Huella registrada: {$user->name} → R307 ID {$request->r307_id}.",
            'data' => [
                'user_id'      => $user->id,
                'name'         => $user->name,
                'codigo_docente' => $user->codigo_docente,
                'r307_id'      => (int) $request->r307_id,
                'dedo_numero'  => $dedoNumero,
                'dedo_nombre'  => $dedoNombre,
            ],
        ], 201);
    }

    /**
     * Elimina la asociación de un ID del sensor R307.
     * POST /api/v1/biometria/delete
     * Body: api_token, r307_id
     */
    public function delete(Request $request)
    {
        $device = $this->deviceOrFail($request);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Dispositivo no autorizado.'], 401);
        }

        $request->validate([
            'r307_id' => 'required|integer|min:1',
        ]);

        $huella = HuellaDocente::where('r307_id', $request->r307_id)->first();

        if (!$huella) {
            return response()->json([
                'success' => false,
                'message' => "El ID {$request->r307_id} no existe en el sistema."
            ], 404);
        }

        $nombre = $huella->docente->name ?? 'Docente';
        $huella->delete();

        AuditLog::create([
            'user_id'    => $huella->user_id,
            'accion'     => 'ELIMINAR_HUELLA_ESP32',
            'modulo'     => 'Biometría',
            'detalles'   => "Se eliminó la huella R307 ID {$request->r307_id} de {$nombre} vía {$device->nombre}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Huella R307 ID {$request->r307_id} de {$nombre} eliminada del sistema."
        ]);
    }

    /**
     * Lista de huellas registradas (mapeo r307_id → docente) para el ESP32.
     * GET /api/v1/biometria/list
     */
    public function list(Request $request)
    {
        $device = $this->deviceOrFail($request);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Dispositivo no autorizado.'], 401);
        }

        $huellas = HuellaDocente::with('docente:id,name,codigo_docente')
            ->where('estado', 'ACTIVO')
            ->whereNotNull('r307_id')
            ->get(['id', 'r307_id', 'user_id', 'dedo_numero', 'dedo_nombre']);

        return response()->json([
            'success'     => true,
            'device_code' => $device->codigo,
            'count'       => $huellas->count(),
            'fingerprints' => $huellas->map(function ($h) {
                return [
                    'r307_id'        => (int) $h->r307_id,
                    'user_id'        => $h->user_id,
                    'name'           => $h->docente->name ?? 'Desconocido',
                    'codigo_docente' => $h->docente->codigo_docente ?? null,
                    'dedo_numero'    => $h->dedo_numero,
                    'dedo_nombre'    => $h->dedo_nombre,
                ];
            }),
        ]);
    }
}
