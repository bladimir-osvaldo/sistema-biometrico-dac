@extends('layouts.app')

@section('title', 'Asistencias')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800" style="color: var(--color-plomo); font-weight: 700;">Control de Asistencia</h1>
        <div>
            <a href="{{ route('attendances.export.pdf', request()->all()) }}" class="btn btn-sm btn-danger" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('attendances.export.excel', request()->all()) }}" class="btn btn-sm btn-success">
                <i class="fa-solid fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Docente</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos los Docentes</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}" {{ request('user_id') == $docente->id ? 'selected' : '' }}>
                                {{ $docente->nombre }} {{ $docente->apellido }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="PUNTUAL" {{ request('estado') == 'PUNTUAL' ? 'selected' : '' }}>Puntual</option>
                        <option value="TARDANZA" {{ request('estado') == 'TARDANZA' ? 'selected' : '' }}>Tardanza</option>
                        <option value="FALTA" {{ request('estado') == 'FALTA' ? 'selected' : '' }}>Falta</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn text-white w-100 mb-2" style="background-color: var(--color-guindo);">
                        <i class="fa-solid fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table dac-table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Docente</th>
                        <th>Código</th>
                        <th>Materia/Aula</th>
                        <th>Fecha</th>
                        <th>Hora Marcado</th>
                        <th>Estado</th>
                        <th>Modo</th>
                        <th>Retraso</th>
                        <th>Lector ESP32</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $asistencia)
                        @php
                            $retraso = 0;
                            if ($asistencia->estado === 'TARDANZA' && $asistencia->schedule) {
                                $horaEntrada = \Carbon\Carbon::parse($asistencia->schedule->hora_entrada);
                                $horaMarcado = \Carbon\Carbon::parse($asistencia->hora);
                                $retraso = $horaEntrada->diffInMinutes($horaMarcado, false);
                                if ($retraso < 0) $retraso = 0;
                            }
                        @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-2">
                                    <i class="fa-solid fa-user-circle fa-2x text-secondary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $asistencia->user->nombre }} {{ $asistencia->user->apellido }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $asistencia->user->codigo_rfid ?? 'N/A' }}</td>
                        <td>
                            <div>{{ $asistencia->schedule->materia ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $asistencia->schedule->aula ?? 'N/A' }}</small>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($asistencia->hora)->format('H:i:s') }}</td>
                        <td>
                            @if($asistencia->estado == 'PUNTUAL')
                                <span class="badge bg-success">PUNTUAL</span>
                            @elseif($asistencia->estado == 'TARDANZA')
                                <span class="badge bg-warning text-dark">TARDANZA</span>
                            @else
                                <span class="badge bg-danger">{{ $asistencia->estado }}</span>
                            @endif
                        </td>
                        <td>
                            @if($asistencia->modo_marcado == 'RFID')
                                <span class="badge bg-info"><i class="fa-solid fa-id-card"></i> RFID</span>
                            @else
                                <span class="badge bg-secondary"><i class="fa-solid fa-fingerprint"></i> HUELLA</span>
                            @endif
                        </td>
                        <td>
                            @if($retraso > 0)
                                <span class="text-danger fw-bold">{{ $retraso }} min</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $asistencia->device->nombre ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No se encontraron registros de asistencia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-4 d-flex justify-content-center">
                {{ $attendances->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
