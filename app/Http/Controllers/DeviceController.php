<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\AuditLog;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para la Gestión CRUD de Lectores Biométricos IoT (ESP32).
 */
class DeviceController extends Controller
{
    /**
     * Muestra el listado de lectores de huella registrados.
     */
    public function index()
    {
        $devices = Device::withCount('attendances')->latest()->paginate(10);
        return view('devices.index', compact('devices'));
    }

    /**
     * Muestra el formulario para registrar un nuevo lector ESP32.
     */
    public function create()
    {
        return view('devices.create');
    }

    /**
     * Guarda un nuevo dispositivo con token API autogenerado y DB::transaction.
     */
    public function store(StoreDeviceRequest $request)
    {
        $device = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['api_token'] = Str::random(60);

            $dev = Device::create($data);

            AuditLog::create([
                'user_id'    => Auth::id(),
                'accion'     => 'CREAR_DISPOSITIVO',
                'modulo'     => 'Dispositivos',
                'detalles'   => "Lector '{$dev->nombre}' ({$dev->codigo}) registrado en {$dev->ubicacion_aula}",
                'ip_address' => $request->ip(),
            ]);

            return $dev;
        });

        return redirect()->route('devices.index')
            ->with('success', "✅ Dispositivo «{$device->nombre}» registrado exitosamente. Token API: {$device->api_token}");
    }

    /**
     * Muestra el detalle técnico y estadísticas del lector.
     */
    public function show(Device $device)
    {
        $device->load(['attendances' => function ($q) {
            $q->with('user')->latest()->take(20);
        }]);

        return view('devices.show', compact('device'));
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(Device $device)
    {
        return view('devices.edit', compact('device'));
    }

    /**
     * Actualiza la información del dispositivo usando DB::transaction.
     */
    public function update(UpdateDeviceRequest $request, Device $device)
    {
        DB::transaction(function () use ($request, $device) {
            $device->update($request->validated());

            AuditLog::create([
                'user_id'    => Auth::id(),
                'accion'     => 'ACTUALIZAR_DISPOSITIVO',
                'modulo'     => 'Dispositivos',
                'detalles'   => "Dispositivo '{$device->nombre}' actualizado por " . Auth::user()->name,
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('devices.index')
            ->with('success', "✅ Dispositivo «{$device->nombre}» actualizado correctamente.");
    }

    /**
     * Elimina un lector ESP32 usando DB::transaction.
     */
    public function destroy(Request $request, Device $device)
    {
        $nombre = $device->nombre;

        DB::transaction(function () use ($request, $device, $nombre) {
            $device->delete();

            AuditLog::create([
                'user_id'    => Auth::id(),
                'accion'     => 'ELIMINAR_DISPOSITIVO',
                'modulo'     => 'Dispositivos',
                'detalles'   => "Lector '{$nombre}' eliminado por " . Auth::user()->name,
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('devices.index')
            ->with('success', "🗑️ Dispositivo «{$nombre}» eliminado correctamente.");
    }
}
