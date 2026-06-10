@extends('layouts.app')

@section('title', 'Detalle del Lector ESP32')

@section('content')

<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
    <a href="{{ route('devices.index') }}" class="btn-dac-outline" style="padding:8px 14px; font-size:13px;">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
    <div>
        <h2 style="font-family:'Outfit',sans-serif; font-size:22px; font-weight:700; color:var(--color-guindo);">
            <i class="fa-solid fa-microchip"></i> Lector {{ $device->codigo }}
        </h2>
        <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            {{ $device->nombre }} · {{ $device->ubicacion_aula }}
        </p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="fa-solid fa-circle-info"></i> Información Técnica</div>
            </div>

            <table class="dac-table">
                <tr><td class="text-muted">Código</td><td><strong>{{ $device->codigo }}</strong></td></tr>
                <tr><td class="text-muted">Nombre</td><td>{{ $device->nombre }}</td></tr>
                <tr><td class="text-muted">Ubicación</td><td><span class="badge badge-info">{{ $device->ubicacion_aula }}</span></td></tr>
                <tr><td class="text-muted">MAC Address</td><td><code>{{ $device->mac_address ?? 'Sin registrar' }}</code></td></tr>
                <tr><td class="text-muted">Firmware</td><td>v{{ $device->firmware_version }}</td></tr>
                <tr><td class="text-muted">Estado</td><td>
                    @if($device->estado === 'ONLINE')
                        <span class="badge badge-success">ONLINE</span>
                    @else
                        <span class="badge badge-danger">OFFLINE</span>
                    @endif
                </td></tr>
                <tr><td class="text-muted">Último Heartbeat</td><td>{{ $device->ultimo_heartbeat ? $device->ultimo_heartbeat->diffForHumans() : 'Sin conexión' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="fa-solid fa-clipboard-user"></i> Últimas Marcaciones ({{ $device->attendances->count() }})</div>
            </div>
            <div class="table-responsive">
                <table class="dac-table">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($device->attendances as $att)
                            <tr>
                                <td>{{ $att->user->name ?? 'N/D' }}</td>
                                <td>{{ \Carbon\Carbon::parse($att->fecha)->format('d/m/Y') }}</td>
                                <td>{{ substr($att->hora_marcado, 0, 5) }}</td>
                                <td>
                                    @if($att->estado === 'PUNTUAL')
                                        <span class="badge badge-success">Puntual</span>
                                    @elseif($att->estado === 'TARDANZA')
                                        <span class="badge badge-warning">Tardanza</span>
                                    @else
                                        <span class="badge badge-danger">{{ $att->estado }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Sin marcaciones registradas en este lector.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
