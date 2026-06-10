@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')

<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
    <a href="{{ route('dashboard') }}" class="btn-dac-outline" style="padding:8px 14px; font-size:13px;">
        <i class="fa-solid fa-arrow-left"></i> Dashboard
    </a>
    <div>
        <h2 style="font-family:'Outfit',sans-serif; font-size:22px; font-weight:700; color:var(--color-guindo);">
            <i class="fa-solid fa-circle-user"></i> Mi Perfil
        </h2>
        <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            Información personal y resumen operativo de tu cuenta
        </p>
    </div>
</div>

<div class="row g-4">
    {{-- ─── TARJETA DE IDENTIDAD ─── --}}
    <div class="col-lg-4">
        <div class="card text-center">
            <img src="{{ $user->foto_url }}"
                 alt="Foto de perfil"
                 class="rounded-circle mx-auto"
                 style="width:110px; height:110px; object-fit:cover; border:3px solid var(--color-dorado); box-shadow:0 4px 16px rgba(0,0,0,0.15);"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6b0c24&color=ffffff&bold=true&size=140'">

            <h4 style="font-family:'Outfit',sans-serif; color:var(--text-main); margin-top:16px; margin-bottom:2px;">{{ $user->name }}</h4>
            <span class="badge badge-guindo mb-3">{{ $user->roles->pluck('name')->first() ?? 'Docente' }}</span>

            <table style="width:100%; font-size:13px; text-align:left;">
                <tr><td class="text-muted py-1">Correo</td><td class="py-1">{{ $user->email }}</td></tr>
                <tr><td class="text-muted py-1">Código Docente</td><td class="py-1">{{ $user->codigo_docente ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted py-1">CI / DNI</td><td class="py-1">{{ $user->dni ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted py-1">Teléfono</td><td class="py-1">{{ $user->telefono ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted py-1">Carrera</td><td class="py-1">{{ $user->carrera ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted py-1">Cargo</td><td class="py-1">{{ $user->cargo ?? 'N/A' }}</td></tr>
                <tr><td class="text-muted py-1">Estado</td><td class="py-1">
                    @if($user->estado === 'ACTIVO')
                        <span class="badge badge-success">Activo</span>
                    @elseif($user->estado === 'LICENCIA')
                        <span class="badge badge-info">Licencia</span>
                    @else
                        <span class="badge badge-danger">Inactivo</span>
                    @endif
                </td></tr>
            </table>

            <a href="{{ route('profile.edit') }}" class="btn-dac btn-sm mt-3" style="width:100%; justify-content:center;">
                <i class="fa-solid fa-pen-to-square"></i> Editar Perfil
            </a>
        </div>
    </div>

    {{-- ─── RESUMEN OPERATIVO ─── --}}
    <div class="col-lg-8">
        <div class="stats-grid mb-4" style="margin-bottom:16px;">
            <div class="stat-card">
                <div class="stat-icon verde"><i class="fa-solid fa-clipboard-check"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalAsistencias }}</div>
                    <div class="stat-label">Asistencias (mes actual)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon guindo"><i class="fa-solid fa-check-double"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $puntuales }}</div>
                    <div class="stat-label">Marcaciones Puntuales</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon dorado"><i class="fa-solid fa-stopwatch"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $tardanzas }}</div>
                    <div class="stat-label">Tardanzas</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon azul"><i class="fa-solid fa-percentage"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $porcentajePuntualidad }}%</div>
                    <div class="stat-label">Puntualidad del mes</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom"><i class="fa-solid fa-shield-halved"></i> Seguridad de la cuenta</div>
            </div>
            <div style="font-size:13.5px; color:var(--text-muted); line-height:1.8;">
                <p>
                    <i class="fa-solid fa-key me-2" style="color:var(--color-guindo);"></i>
                    La contraseña puede actualizarse desde la opción
                    <strong>Editar Perfil</strong>. El restablecimiento ante olvido debe solicitarse
                    al Administrador de Sistemas.
                </p>
                <p class="mb-0">
                    <i class="fa-solid fa-fingerprint me-2" style="color:var(--color-guindo);"></i>
                    Tu identificación biométrica se gestiona desde el módulo de
                    <strong>Enrolamiento Biométrico</strong> (exclusivo del Administrador).
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
