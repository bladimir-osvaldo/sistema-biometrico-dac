<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HuellaDocente;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para la Gestión y Registro Biométrico Web de Huellas Dactilares.
 */
class BiometriaController extends Controller
{
    /**
     * Muestra la pantalla de enrolamiento de huellas biométricas.
     */
    public function index(Request $request)
    {
        $selectedUserId = $request->query('user_id');
        $docentes = User::role('Docente')->with(['huellas'])->get();

        if ($docentes->isEmpty()) {
            $docentes = User::where('cargo', 'Docente')->orWhere('role', 'docente')->with(['huellas'])->get();
        }

        $selectedUser = $selectedUserId ? User::with('huellas')->find($selectedUserId) : null;

        return view('biometria.registro', compact('docentes', 'selectedUser'));
    }

    /**
     * Guarda o actualiza la captura biométrica (hasta 3 slots por docente) usando DB::transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'r307_id' => 'nullable|integer|min:1|max:300',
            'huella_1' => 'nullable|string',
            'huella_2' => 'nullable|string',
            'huella_3' => 'nullable|string',
        ], [
            'user_id.required' => 'Debe seleccionar un docente para el enrolamiento.',
        ]);

        $user = User::findOrFail($request->user_id);

        DB::transaction(function () use ($request, $user) {
            // Si el admin escribe un ID del sensor R307, se vincula directamente
            if ($request->filled('r307_id')) {
                HuellaDocente::updateOrCreate(
                    [
                        'user_id'      => $user->id,
                        'dedo_numero'  => 1,
                    ],
                    [
                        'dedo_nombre'    => 'Huella R307 ID ' . $request->r307_id,
                        'r307_id'        => $request->r307_id,
                        'template_hash'  => 'R307:' . $request->r307_id,
                        'registrado_por' => Auth::id(),
                        'estado'         => 'ACTIVO'
                    ]
                );
                $user->update(['huella_template_hash' => 'R307:' . $request->r307_id]);
            }

            $slots = [
                1 => ['nombre' => 'Índice Derecho', 'hash' => $request->huella_1],
                2 => ['nombre' => 'Pulgar Derecho', 'hash' => $request->huella_2],
                3 => ['nombre' => 'Índice Izquierdo', 'hash' => $request->huella_3],
            ];

            foreach ($slots as $num => $slotData) {
                if (!empty($slotData['hash'])) {
                    HuellaDocente::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'dedo_numero' => $num
                        ],
                        [
                            'dedo_nombre' => $slotData['nombre'],
                            'template_hash' => $slotData['hash'],
                            'registrado_por' => Auth::id(),
                            'estado' => 'ACTIVO'
                        ]
                    );
                }
            }

            // Actualizar también el hash principal en la tabla users para compatibilidad rápida
            if (!empty($request->huella_1)) {
                $user->update(['huella_template_hash' => $request->huella_1]);
            }

            AuditLog::create([
                'user_id'    => Auth::id(),
                'accion'     => 'REGISTRO_BIOMETRICO',
                'modulo'     => 'Biometría',
                'detalles'   => "Plantillas biométricas actualizadas para docente {$user->name}",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('biometria.index', ['user_id' => $user->id])
            ->with('success', "✅ Plantillas biométricas registradas correctamente para «{$user->name}».");
    }

    /**
     * Endpoint API para verificar si una plantilla biométrica coincide con el docente.
     * GET/POST /api/v1/biometria/verificar/{user_id}
     */
    public function verificar(Request $request, $user_id)
    {
        $templateHash = $request->input('template_hash');

        if (!$templateHash) {
            return response()->json(['success' => false, 'matched' => false, 'message' => 'Template hash requerido.'], 400);
        }

        $huella = HuellaDocente::where('user_id', $user_id)
            ->where('template_hash', $templateHash)
            ->where('estado', 'ACTIVO')
            ->first();

        $user = User::find($user_id);
        $userMatch = ($user && $user->huella_template_hash === $templateHash);

        $matched = ($huella !== null || $userMatch);

        return response()->json([
            'success' => true,
            'matched' => $matched,
            'user_id' => $user_id,
            'message' => $matched ? 'Coincidencia biométrica confirmada.' : 'No hay coincidencia biométrica.'
        ]);
    }
}
