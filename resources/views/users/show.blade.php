@extends('layouts.app')

@section('title', 'Perfil de Docente - ' . $user->name)

@section('styles')
<style>
    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--color-dorado);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-id-badge text-guindo me-2"></i>Perfil de {{ $user->name }}</h2>
            <p class="text-muted small mb-0">Detalle académico, biometría y registro de asistencias</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('users.index') }}" class="btn btn-dac-outline">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="{{ route('biometria.index', ['user_id' => $user->id]) }}" class="btn btn-dac-gold">
                <i class="fas fa-fingerprint me-1"></i> Registrar Huellas
            </a>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-dac">
                <i class="fas fa-edit me-1"></i> Editar Perfil
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Datos Docente -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->foto_url }}" alt="{{ $user->name }}" class="profile-avatar mb-3 shadow-sm">
                    <h4 class="card-title-custom justify-content-center">{{ $user->name }}</h4>
                    <p class="text-muted mb-2"><strong>Rol:</strong> {{ $user->roles->pluck('name')->implode(', ') ?: $user->role }}</p>
                    
                    @if($user->estado == 'ACTIVO')
                        <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Activo</span>
                    @elseif($user->estado == 'LICENCIA')
                        <span class="badge badge-warning"><i class="fas fa-clock me-1"></i>En Licencia</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>Inactivo</span>
                    @endif
                </div>
                <div class="card-body border-top pt-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent px-0"><i class="fas fa-envelope text-muted me-2"></i> {{ $user->email }}</li>
                        <li class="list-group-item bg-transparent px-0"><i class="fas fa-id-card text-muted me-2"></i> <strong>CI/DNI:</strong> {{ $user->dni ?? 'Sin registrar' }}</li>
                        <li class="list-group-item bg-transparent px-0"><i class="fas fa-barcode text-muted me-2"></i> <strong>Código:</strong> {{ $user->codigo_docente ?? 'Sin registrar' }}</li>
                        <li class="list-group-item bg-transparent px-0"><i class="fas fa-phone text-muted me-2"></i> <strong>Teléfono:</strong> {{ $user->telefono ?? 'Sin registrar' }}</li>
                        <li class="list-group-item bg-transparent px-0"><i class="fas fa-graduation-cap text-muted me-2"></i> <strong>Carrera:</strong> {{ $user->carrera ?? 'No asignada' }}</li>
                        <li class="list-group-item bg-transparent px-0"><i class="fas fa-user-tie text-muted me-2"></i> <strong>Cargo:</strong> {{ $user->cargo ?? 'Docente' }}</li>
                    </ul>
                </div>
            </div>

            <!-- Accesos a Reportes Rápidos -->
            <div class="card mb-4">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0"><i class="fas fa-file-pdf me-2"></i>Exportar Reportes</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('reports.mensual', ['docente_id' => $user->id, 'mes' => substr($mes, 5, 2), 'anio' => substr($mes, 0, 4)]) }}" class="btn btn-dac-outline w-100 text-start">
                        <i class="fas fa-file-pdf text-danger me-2"></i> Reporte Mensual PDF
                    </a>
                    <a href="{{ route('reports.tardanzas', ['docente_id' => $user->id, 'mes' => substr($mes, 5, 2), 'anio' => substr($mes, 0, 4)]) }}" class="btn btn-dac-outline w-100 text-start">
                        <i class="fas fa-clock text-warning me-2"></i> Reporte Tardanzas PDF
                    </a>
                    <a href="{{ route('reports.excel', ['docente_id' => $user->id, 'mes' => substr($mes, 5, 2), 'anio' => substr($mes, 0, 4)]) }}" class="btn btn-dac-outline w-100 text-start">
                        <i class="fas fa-file-excel text-success me-2"></i> Exportar a Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Panel Asistencias y Métricas -->
        <div class="col-lg-8">
            <!-- Filtro de Período -->
            <div class="card mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('users.show', $user) }}" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0 font-weight-bold"><i class="fas fa-filter me-1"></i>Filtrar por Mes:</label>
                        </div>
                        <div class="col-auto">
                            <input type="month" name="mes" value="{{ $mes }}" class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>
                        <div class="col-auto ms-auto">
                            <span class="badge badge-guindo p-2"><i class="fas fa-percent me-1"></i>Puntualidad: <strong>{{ $puntualidadPct }}%</strong></span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPIs de Asistencia del Período -->
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-icon verde"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $puntuales }}</div>
                        <div class="stat-label">Puntuales</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon dorado"><i class="fas fa-clock"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $tardanzas }}</div>
                        <div class="stat-label">Tardanzas</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon rojo"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $faltas }}</div>
                        <div class="stat-label">Faltas Total</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon azul"><i class="fas fa-file-signature"></i></div>
                    <div class="stat-body">
                        <div class="stat-value">{{ $justificadas }}</div>
                        <div class="stat-label">Justificadas</div>
                    </div>
                </div>
            </div>

            <!-- Horarios del Docente -->
            <div class="card mb-4">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="card-title-custom mb-0"><i class="fas fa-calendar-days me-2"></i>Horarios Asignados</h5>
                    <a href="{{ route('schedules.create') }}" class="btn btn-sm btn-dac"><i class="fas fa-plus me-1"></i> Nuevo Horario</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table dac-table mb-0">
                            <thead>
                                <tr>
                                    <th>Materia</th>
                                    <th>Día</th>
                                    <th>Horario</th>
                                    <th>Aula</th>
                                    <th>Tolerancia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->schedules as $schedule)
                                    <tr>
                                        <td><strong>{{ $schedule->materia }}</strong> @if($schedule->codigo_materia)<br><small class="text-muted">{{ $schedule->codigo_materia }}</small>@endif</td>
                                        <td><span class="badge badge-secondary">{{ $schedule->dia_semana }}</span></td>
                                        <td><i class="fas fa-clock text-muted me-1"></i> {{ substr($schedule->hora_inicio, 0, 5) }} - {{ substr($schedule->hora_fin, 0, 5) }}</td>
                                        <td>{{ $schedule->aula }}</td>
                                        <td>{{ $schedule->tolerancia_minutos }} min</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No tiene horarios registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Historial de Asistencias -->
            <div class="card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0"><i class="fas fa-clipboard-check me-2"></i>Historial de Asistencias ({{ $mes }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table dac-table mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora Marcado</th>
                                    <th>Estado</th>
                                    <th>Retraso</th>
                                    <th>Lector / Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $att)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($att->fecha)->format('d/m/Y') }}</td>
                                        <td><strong>{{ substr($att->hora_marcado, 0, 5) }}</strong></td>
                                        <td>
                                            @if($att->estado == 'PUNTUAL')
                                                <span class="badge badge-success">Puntual</span>
                                            @elseif($att->estado == 'TARDANZA')
                                                <span class="badge badge-warning">Tardanza</span>
                                            @elseif($att->estado == 'FALTA_JUSTIFICADA')
                                                <span class="badge badge-info">Justificada</span>
                                            @else
                                                <span class="badge badge-danger">Falta Injustificada</span>
                                            @endif
                                        </td>
                                        <td>{{ $att->minutos_retraso > 0 ? $att->minutos_retraso . ' min' : '-' }}</td>
                                        <td><small class="text-muted">{{ $att->device->nombre ?? $att->modo_marcado }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No hay asistencias registradas en este período.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($attendances->hasPages())
                    <div class="card-footer bg-transparent">
                        {{ $attendances->appends(['mes' => $mes])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
