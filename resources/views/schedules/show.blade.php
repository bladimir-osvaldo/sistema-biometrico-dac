@extends('layouts.app')

@section('title', 'Detalle del Horario')

@section('content')

<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
    <a href="{{ route('schedules.index') }}" class="btn-dac-outline" style="padding:8px 14px; font-size:13px;">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
    <div>
        <h2 style="font-family:'Outfit',sans-serif; font-size:22px; font-weight:700; color:var(--color-guindo);">
            <i class="fa-solid fa-calendar-days"></i> Detalle del Horario
        </h2>
        <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            Información completa de la clase asignada
        </p>
    </div>
</div>

<div class="card" style="max-width:900px;">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="fa-solid fa-chalkboard-user"></i> {{ $schedule->materia }}
            @if($schedule->codigo_materia)
                <span class="badge badge-info">{{ $schedule->codigo_materia }}</span>
            @endif
        </div>
        @can('update', $schedule)
            <a href="{{ route('schedules.edit', $schedule->id) }}" class="btn-dac" style="padding:8px 14px; font-size:13px;">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
        @endcan
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-top:20px;">
        <div class="stat-box">
            <span class="stat-label">Docente</span>
            <span class="stat-value">{{ $schedule->user->name ?? '—' }}</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Código Docente</span>
            <span class="stat-value">{{ $schedule->user->codigo_docente ?? '—' }}</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Día</span>
            <span class="stat-value">{{ $schedule->dia_semana }}</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Aula</span>
            <span class="stat-value">{{ $schedule->aula }}</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Hora Inicio</span>
            <span class="stat-value">{{ substr($schedule->hora_inicio, 0, 5) }}</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Hora Fin</span>
            <span class="stat-value">{{ substr($schedule->hora_fin, 0, 5) }}</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Tolerancia</span>
            <span class="stat-value">{{ $schedule->tolerancia_minutos }} min</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Estado</span>
            <span class="stat-value">
                @if($schedule->activo)
                    <span class="badge badge-success">Activo</span>
                @else
                    <span class="badge badge-danger">Inactivo</span>
                @endif
            </span>
        </div>
    </div>

    @if($schedule->attendances->isNotEmpty())
        <h3 style="font-family:'Outfit',sans-serif; font-size:16px; color:var(--color-guindo); margin:28px 0 12px;">
            <i class="fa-solid fa-clipboard-user"></i> Últimas asistencias registradas
        </h3>
        <div class="table-responsive">
            <table class="dac-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Dispositivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule->attendances as $att)
                        <tr>
                            <td>{{ $att->fecha }}</td>
                            <td>
                                @php
                                    $badges = ['PUNTUAL' => 'success', 'TARDANZA' => 'warning', 'FALTA_INJUSTIFICADA' => 'danger', 'FALTA_JUSTIFICADA' => 'secondary'];
                                @endphp
                                <span class="badge badge-{{ $badges[$att->estado] ?? 'secondary' }}">{{ $att->estado }}</span>
                            </td>
                            <td>{{ $att->device->nombre ?? 'ESP32' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
