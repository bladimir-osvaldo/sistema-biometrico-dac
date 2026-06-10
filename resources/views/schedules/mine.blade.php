@extends('layouts.app')

@section('title', 'Mi Horario')

@section('content')

<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
    <a href="{{ route('dashboard') }}" class="btn-dac-outline" style="padding:8px 14px; font-size:13px;">
        <i class="fa-solid fa-arrow-left"></i> Dashboard
    </a>
    <div>
        <h2 style="font-family:'Outfit',sans-serif; font-size:22px; font-weight:700; color:var(--color-guindo);">
            <i class="fa-solid fa-calendar-days"></i> Mi Horario de Clases
        </h2>
        <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            Horarios oficiales DAC · Turno 1 (13:15–14:20) · Turno 2 (14:30–15:30)
        </p>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="dac-table">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Materia</th>
                    <th>Código</th>
                    <th>Aula</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Tolerancia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                    <tr>
                        <td><strong>{{ $schedule->dia_semana }}</strong></td>
                        <td><strong style="color:var(--color-guindo);">{{ $schedule->materia }}</strong></td>
                        <td>{{ $schedule->codigo_materia ?? 'N/A' }}</td>
                        <td><span class="badge badge-info">{{ $schedule->aula }}</span></td>
                        <td>{{ substr($schedule->hora_inicio, 0, 5) }}</td>
                        <td>{{ substr($schedule->hora_fin, 0, 5) }}</td>
                        <td>{{ $schedule->tolerancia_minutos }} min</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No tienes horarios asignados. Contacta a tu Coordinador DAC.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
