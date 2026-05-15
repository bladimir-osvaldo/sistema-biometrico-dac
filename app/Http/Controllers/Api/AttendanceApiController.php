<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\AuditLog;
use Carbon\Carbon;

/**
 * Controlador de API REST para la recepción de marcaciones biométricas desde dispositivos ESP32.
 * Soporta autenticación por token de dispositivo, prevención de duplicados y sincronización offline.
 */
class AttendanceApiController extends Controller
{
    /**
     * Endpoint POST /api/v1/attendance
     * Recibe marcaciones individuales o en lote (offline queue).
     */
    public function register(Request $request)
    {
        // 1. Validar autenticación de dispositivo vía Bearer Token
        $token = $request->bearerToken() ?? $request->input('api_token');
        $device = Device::where('api_token', $token)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo no autorizado. Token inválido.'
            ], 401);
        }

        // Actualizar último heartbeat del dispositivo ESP32
        $device->update([
            'estado' => 'ONLINE',
            'ultimo_heartbeat' => now()
        ]);

        // 2. Comprobar si el payload es un lote de marcaciones (offline sync) o marcación única
        $records = $request->input('records');
        if (is_array($records) && count($records) > 0) {
            $processed = 0;
            foreach ($records as $record) {
                if ($this->processSingleAttendance($record, $device, 'OFFLINE_SYNC', $request->ip())) {
                    $processed++;
                }
            }
            return response()->json([
                'success' => true,
                'message' => "Sincronización en lote completada. {$processed} marcaciones procesadas.",
                'processed_count' => $processed
            ], 200);
        }

        // Marcación única individual en tiempo real
        $singleData = [
            'user_id' => $request->input('user_id'),
            'codigo_docente' => $request->input('codigo_docente'),
            'r307_id' => $request->input('r307_id'),
            'huella_hash' => $request->input('huella_hash'),
            'timestamp' => $request->input('timestamp', now()->toDateTimeString()),
        ];

        $result = $this->processSingleAttendance($singleData, $device, 'ONLINE', $request->ip());

        if ($result['status']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Procesa la lógica de negocio para una marcación individual.
     */
    private function processSingleAttendance(array $data, Device $device, string $modo, ?string $ip)
    {
        // Buscar docente por ID, código, ID R307 o hash de huella (en huellas_docentes o users)
        $user = null;
        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
        } elseif (!empty($data['codigo_docente'])) {
            $user = User::where('codigo_docente', $data['codigo_docente'])->first();
        } elseif (isset($data['r307_id']) && $data['r307_id'] !== '' && $data['r307_id'] !== null) {
            $huellaR307 = \App\Models\HuellaDocente::where('r307_id', $data['r307_id'])
                ->where('estado', 'ACTIVO')
                ->first();
            if ($huellaR307) {
                $user = $huellaR307->docente;
            }
        } elseif (!empty($data['huella_hash'])) {
            $huellaDocente = \App\Models\HuellaDocente::where('template_hash', $data['huella_hash'])->where('estado', 'ACTIVO')->first();
            if ($huellaDocente) {
                $user = $huellaDocente->docente;
            } else {
                $user = User::where('huella_template_hash', $data['huella_hash'])->first();
            }
        }

        if (!$user) {
            return [
                'status' => false,
                'message' => 'Docente no encontrado en el sistema.'
            ];
        }

        $timestamp = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : Carbon::now();
        $fecha = $timestamp->toDateString();
        $hora = $timestamp->toTimeString();

        // 3. Regla del Negocio RR-02: Prevención de marcaciones duplicadas (< 30 segundos)
        $duplicado = Attendance::where('user_id', $user->id)
            ->where('fecha', $fecha)
            ->where('created_at', '>=', Carbon::parse($timestamp)->subSeconds(30))
            ->first();

        if ($duplicado) {
            return [
                'status' => false,
                'message' => 'Marcación duplicada detectada (intervalo < 30s). Registro ignorado.',
                'data' => $duplicado
            ];
        }

        // 4. Buscar horario programado activo para el día de la semana
        $diasMapa = [
            'Monday' => 'LUNES',
            'Tuesday' => 'MARTES',
            'Wednesday' => 'MIERCOLES',
            'Thursday' => 'JUEVES',
            'Friday' => 'VIERNES',
            'Saturday' => 'SABADO'
        ];
        $diaSemanaActual = $diasMapa[$timestamp->format('l')] ?? 'LUNES';

        $schedule = Schedule::where('user_id', $user->id)
            ->where('dia_semana', $diaSemanaActual)
            ->where('activo', true)
            ->first();

        // 5. Cálculo de puntualidad / tardanza
        $estadoAsistencia = 'PUNTUAL';
        $minutosRetraso = 0;

        if ($schedule) {
            $horaInicio = Carbon::parse($schedule->hora_inicio);
            $tolerancia = env('TOLERANCIA_ASISTENCIA_MINUTOS', $schedule->tolerancia_minutos);
            $horaLimite = (clone $horaInicio)->addMinutes((int)$tolerancia);

            $horaMarcadaObj = Carbon::parse($hora);

            if ($horaMarcadaObj->greaterThan($horaLimite)) {
                $estadoAsistencia = 'TARDANZA';
                $minutosRetraso = $horaInicio->diffInMinutes($horaMarcadaObj);
            }
        }

        // 6. Guardar marcado de asistencia
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule ? $schedule->id : null,
            'device_id' => $device->id,
            'fecha' => $fecha,
            'hora_marcado' => $hora,
            'estado' => $estadoAsistencia,
            'modo_marcado' => $modo,
            'minutos_retraso' => $minutosRetraso,
            'ip_origen' => $ip,
            'observaciones' => "Marcado desde {$device->nombre} ({$device->ubicacion_aula})",
        ]);

        // Registrar auditoría
        AuditLog::create([
            'user_id' => $user->id,
            'accion' => 'MARCACION_BIOMETRICA',
            'modulo' => 'Asistencias',
            'detalles' => "Docente {$user->name} marcó {$estadoAsistencia} a las {$hora} ({$modo})",
            'ip_address' => $ip,
        ]);

        return [
            'status' => true,
            'message' => "Asistencia registrada correctamente como {$estadoAsistencia}.",
            'data' => [
                'attendance_id' => $attendance->id,
                'user' => $user->name,
                'estado' => $estadoAsistencia,
                'hora' => $hora,
                'minutos_retraso' => $minutosRetraso
            ]
        ];
    }
}
