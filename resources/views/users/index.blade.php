@extends('layouts.app')

@section('title', 'Gestión de Docentes & Usuarios')

@section('content')

{{-- Encabezado con estadísticas rápidas --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon guindo"><i class="fa-solid fa-users"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $users->total() }}</div>
            <div class="stat-label">Total Registrados</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon verde"><i class="fa-solid fa-user-check"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $users->where('estado', 'ACTIVO')->count() }}</div>
            <div class="stat-label">Activos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon dorado"><i class="fa-solid fa-fingerprint"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $users->whereNotNull('huella_template_hash')->count() }}</div>
            <div class="stat-label">Con Biometría</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rojo"><i class="fa-solid fa-user-xmark"></i></div>
        <div class="stat-body">
            <div class="stat-value">{{ $users->where('estado', 'INACTIVO')->count() }}</div>
            <div class="stat-label">Inactivos</div>
        </div>
    </div>
</div>

{{-- Tabla principal de usuarios --}}
<div class="card">
    <div class="card-header-custom">
        <div class="card-title-custom">
            <i class="fa-solid fa-users-gear"></i> Nómina de Docentes y Usuarios
        </div>
        <a href="{{ route('users.create') }}" class="btn-dac">
            <i class="fa-solid fa-user-plus"></i> Registrar Nuevo Docente
        </a>
    </div>

    <div class="table-responsive">
        <table class="dac-table">
            <thead>
                <tr>
                    <th>Docente / Usuario</th>
                    <th>CI / DNI</th>
                    <th>Código</th>
                    <th>Carrera / Especialidad</th>
                    <th>Rol</th>
                    <th>Biometría</th>
                    <th>Estado</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    {{-- Docente con avatar --}}
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="{{ $user->foto_url }}"
                                 style="width:38px; height:38px; border-radius:50%; border:2px solid var(--color-dorado); object-fit:cover;"
                                 alt="Avatar"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6b0c24&color=ffffff&bold=true&size=80'">
                            <div>
                                <a href="{{ route('users.show', $user->id) }}" style="font-weight:700; color:var(--text-main); text-decoration:none;">
                                    {{ $user->name }}
                                </a>
                                <div style="font-size:12px; color:var(--text-muted);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- CI/DNI --}}
                    <td>
                        <code style="background:rgba(107,12,36,0.06); padding:3px 8px; border-radius:4px; font-size:12.5px;">
                            {{ $user->dni ?? '—' }}
                        </code>
                    </td>

                    {{-- Código docente --}}
                    <td>
                        <strong style="color:var(--color-guindo); font-size:13px;">
                            {{ $user->codigo_docente ?? '—' }}
                        </strong>
                    </td>

                    {{-- Carrera --}}
                    <td style="font-size:13px; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $user->carrera ?? 'Sin asignar' }}
                    </td>

                    {{-- Rol --}}
                    <td>
                        @php $roleName = $user->roles->pluck('name')->first() ?? 'Sin Rol'; @endphp
                        @if($roleName === 'Administrador')
                            <span class="badge badge-danger">
                                <i class="fa-solid fa-shield-halved"></i> {{ $roleName }}
                            </span>
                        @elseif($roleName === 'Coordinador DAC')
                            <span class="badge badge-dorado">
                                <i class="fa-solid fa-user-tie"></i> {{ $roleName }}
                            </span>
                        @else
                            <span class="badge badge-info">
                                <i class="fa-solid fa-chalkboard-user"></i> {{ $roleName }}
                            </span>
                        @endif
                    </td>

                    {{-- Biometría --}}
                    <td>
                        @if($user->huella_template_hash)
                            <span class="badge badge-success">
                                <i class="fa-solid fa-fingerprint"></i> Registrada
                            </span>
                        @else
                            <span class="badge badge-warning">
                                <i class="fa-solid fa-triangle-exclamation"></i> Pendiente
                            </span>
                        @endif
                    </td>

                    {{-- Estado --}}
                    <td>
                        @if($user->estado === 'ACTIVO')
                            <span class="badge badge-success">✅ Activo</span>
                        @elseif($user->estado === 'LICENCIA')
                            <span class="badge badge-info">🔵 Licencia</span>
                        @else
                            <span class="badge badge-danger">❌ Inactivo</span>
                        @endif
                    </td>

                    {{-- Acciones --}}
                    <td style="text-align:center;">
                        <div style="display:flex; gap:6px; justify-content:center;">
                            {{-- Ver perfil --}}
                            <a href="{{ route('users.show', $user->id) }}" class="btn-dac-outline btn-sm" title="Ver perfil">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            {{-- Editar --}}
                            <a href="{{ route('users.edit', $user->id) }}" class="btn-dac btn-sm" title="Editar usuario">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- Eliminar --}}
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Está seguro de eliminar al usuario «{{ $user->name }}»?\n\nEsta acción no se puede revertir.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm" title="Eliminar usuario">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">
                        <i class="fa-solid fa-users-slash fa-2x" style="margin-bottom:10px; display:block; opacity:0.3;"></i>
                        No hay usuarios registrados en el sistema.
                        <br>
                        <a href="{{ route('users.create') }}" class="btn-dac" style="margin-top:12px; display:inline-flex;">
                            <i class="fa-solid fa-user-plus"></i> Registrar Primer Docente
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($users->hasPages())
    <div style="margin-top:20px; display:flex; justify-content:center;">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection
