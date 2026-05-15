<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Controlador para la Central de Monitoreo de Hardware en Vivo.
 */
class MonitoreoController extends Controller
{
    /**
     * Muestra la interfaz del Centro de Monitoreo.
     */
    public function index()
    {
        $limiteCincoMinutos = Carbon::now()->subMinutes(5);
        
        // Actualizar estados automáticamente
        Device::where('ultimo_heartbeat', '<', $limiteCincoMinutos)->update(['estado' => 'OFFLINE']);
        Device::where('ultimo_heartbeat', '>=', $limiteCincoMinutos)->update(['estado' => 'ONLINE']);

        $devices = Device::all();
        $total   = $devices->count();
        $online  = $devices->where('estado', 'ONLINE')->count();
        $offline = $devices->where('estado', 'OFFLINE')->count();

        return view('monitoreo.index', compact('devices', 'total', 'online', 'offline'));
    }

    /**
     * Devuelve el estado de los dispositivos en formato JSON para refresco por AJAX/WebSocket.
     */
    public function estadoDispositivos()
    {
        $limiteCincoMinutos = Carbon::now()->subMinutes(5);
        
        Device::where('ultimo_heartbeat', '<', $limiteCincoMinutos)->update(['estado' => 'OFFLINE']);
        Device::where('ultimo_heartbeat', '>=', $limiteCincoMinutos)->update(['estado' => 'ONLINE']);

        $devices = Device::all()->map(function ($dev) {
            return [
                'id'                 => $dev->id,
                'codigo'             => $dev->codigo,
                'nombre'             => $dev->nombre,
                'ubicacion_aula'     => $dev->ubicacion_aula,
                'mac_address'        => $dev->mac_address,
                'firmware_version'   => $dev->firmware_version,
                'estado'             => $dev->estado,
                'ultimo_heartbeat'   => $dev->ultimo_heartbeat ? $dev->ultimo_heartbeat->diffForHumans() : 'Sin señal',
            ];
        });

        return response()->json([
            'success' => true,
            'total'   => $devices->count(),
            'online'  => $devices->where('estado', 'ONLINE')->count(),
            'offline' => $devices->where('estado', 'OFFLINE')->count(),
            'devices' => $devices
        ]);
    }
}
