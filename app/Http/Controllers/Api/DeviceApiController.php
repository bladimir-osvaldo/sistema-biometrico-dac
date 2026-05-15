<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;
use App\Models\HuellaDocente;

/**
 * Controlador de Gestión de Dispositivos IoT ESP32 vía API REST.
 */
class DeviceApiController extends Controller
{
    /**
     * Retorna la configuración inicial y lista de huellas activas para el ESP32.
     * GET /api/v1/device/config
     */
    public function getConfig(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('api_token');
        $device = Device::where('api_token', $token)->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo no autorizado.'
            ], 401);
        }

        $device->update([
            'estado' => 'ONLINE',
            'ultimo_heartbeat' => now()
        ]);

        // Obtener usuarios activos con su código y hash biométrico
        $docentes = User::where('estado', 'ACTIVO')
            ->whereNotNull('huella_template_hash')
            ->get(['id', 'name', 'codigo_docente', 'huella_template_hash']);

        // Mapeo de IDs del sensor R307 → docente (para que el ESP32 muestre el nombre)
        $huellas = HuellaDocente::with('docente:id,name,codigo_docente')
            ->where('estado', 'ACTIVO')
            ->whereNotNull('r307_id')
            ->get();

        $fingerprints = $huellas->map(function ($h) {
            return [
                'r307_id'        => (int) $h->r307_id,
                'user_id'        => $h->user_id,
                'name'           => $h->docente->name ?? 'Desconocido',
                'codigo_docente' => $h->docente->codigo_docente ?? null,
            ];
        });

        // Próximo ID libre para enrolar (el sensor R307 soporta hasta 300)
        $maxId = $huellas->max('r307_id');

        return response()->json([
            'success' => true,
            'server_time' => now()->toDateTimeString(),
            'device_code' => $device->codigo,
            'ubicacion' => $device->ubicacion_aula,
            'sync_interval_seconds' => 30,
            'max_r307_id' => 300,
            'next_free_r307_id' => ($maxId ? $maxId + 1 : 1),
            'users' => $docentes,
            'fingerprints' => $fingerprints,
        ]);
    }

    /**
     * Recibe actualización de estado (Heartbeat) del ESP32.
     * POST /api/v1/device/heartbeat
     */
    public function heartbeat(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('api_token');
        $device = Device::where('api_token', $token)->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Dispositivo no autorizado.'], 401);
        }

        $device->update([
            'estado' => 'ONLINE',
            'firmware_version' => $request->input('firmware_version', $device->firmware_version),
            'mac_address' => $request->input('mac_address', $device->mac_address),
            'ultimo_heartbeat' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'ONLINE',
            'server_time' => now()->toDateTimeString()
        ]);
    }

    /**
     * Endpoint para actualización OTA de firmware.
     * GET /api/v1/device/firmware/latest
     */
    public function latestFirmware(Request $request)
    {
        return response()->json([
            'success' => true,
            'version' => '1.2.0',
            'download_url' => asset('firmware/esp32_latest.bin'),
            'sha256' => hash('sha256', 'esp32_firmware_v120_bin_content')
        ]);
    }
}
