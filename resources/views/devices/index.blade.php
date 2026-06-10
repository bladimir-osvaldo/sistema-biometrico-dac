@extends('layouts.app')

@section('title', 'Lectores Biométricos ESP32')

@section('content')
<div class="card">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="fa-solid fa-microchip"></i> Dispositivos Lectores IoT Registrados (ESP32)
        </div>
        <button class="btn-dac" onclick="document.getElementById('modalDevice').style.display='flex'">
            <i class="fa-solid fa-plus"></i> Registrar Nuevo Lector ESP32
        </button>
    </div>

    <div class="table-responsive">
        <table class="dac-table">
            <thead>
                <tr>
                    <th>Código Dispositivo</th>
                    <th>Nombre / Lector</th>
                    <th>Aula / Ubicación</th>
                    <th>Dirección MAC</th>
                    <th>Firmware</th>
                    <th>Estado</th>
                    <th>Último Heartbeat</th>
                    <th>API Token ESP32</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devices as $device)
                <tr>
                    <td><strong style="color:var(--color-guindo);">{{ $device->codigo }}</strong></td>
                    <td><strong>{{ $device->nombre }}</strong></td>
                    <td><span class="badge badge-info">{{ $device->ubicacion_aula }}</span></td>
                    <td><code>{{ $device->mac_address ?? 'Buscando...' }}</code></td>
                    <td>v{{ $device->firmware_version }}</td>
                    <td>
                        @if($device->estado === 'ONLINE')
                            <span class="badge badge-success"><i class="fa-solid fa-signal"></i> ONLINE</span>
                        @else
                            <span class="badge badge-danger"><i class="fa-solid fa-signal-slash"></i> OFFLINE</span>
                        @endif
                    </td>
                    <td>
                        <small>{{ $device->ultimo_heartbeat ? $device->ultimo_heartbeat->diffForHumans() : 'Sin conexión previa' }}</small>
                    </td>
                    <td>
                        <code style="font-size:10px; background:var(--bg-body); padding:4px 6px; border-radius:4px;">{{ Str::limit($device->api_token, 20) }}...</code>
                    </td>
                    <td>
                        <form action="{{ route('devices.destroy', $device->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar lector?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer;" title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL REGISTRAR LECTOR ESP32 -->
<div id="modalDevice" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:var(--bg-card); width:100%; max-width:480px; padding:30px; border-radius:12px; box-shadow:var(--card-shadow); border-top:5px solid var(--color-guindo);">
        <h3 style="font-family:'Outfit',sans-serif; color:var(--color-guindo); margin-bottom:20px;">Registrar Dispositivo ESP32</h3>
        
        <form action="{{ route('devices.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Código del Lector *</label>
                <input type="text" name="codigo" required placeholder="Ej: ESP32-AULA-102" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
            </div>

            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Nombre Descriptivo *</label>
                <input type="text" name="nombre" required placeholder="Ej: Lector Biométrico Bloque B" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
            </div>

            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Ubicación / Aula *</label>
                <input type="text" name="ubicacion_aula" required placeholder="Ej: Aula 102 - Planta Baja" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
            </div>

            <div style="margin-bottom:15px;">
                <label style="font-size:12px; font-weight:600;">Dirección MAC Wi-Fi (Opcional)</label>
                <input type="text" name="mac_address" placeholder="Ej: 24:0A:C4:11:22:33" style="width:100%; padding:8px; border-radius:6px; border:1px solid var(--border-color);">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" onclick="document.getElementById('modalDevice').style.display='none'" style="padding:8px 16px; border-radius:6px; border:1px solid var(--border-color); background:none; cursor:pointer;">Cancelar</button>
                <button type="submit" class="btn-dac">Registrar ESP32</button>
            </div>
        </form>
    </div>
</div>
@endsection
