@extends('layouts.app')

@section('title', 'Generación de Memorandos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-file-pdf text-guindo me-2"></i>Módulo de Memorandos Institucionales</h2>
            <p class="text-muted small mb-0">Emisión formal de llamadas de atención por impuntualidad o inasistencias</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0"><i class="fas fa-file-signature me-2"></i>Parámetros del Memorando</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-error alert-custom">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('memorandos.generar') }}" method="POST" target="_blank">
                        @csrf
                        
                        <div class="row form-row mb-3">
                            <div class="col-md-12">
                                <label for="user_id" class="form-label font-weight-bold">Seleccionar Docente *</label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">-- Seleccione el docente --</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('user_id') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->name }} (CI: {{ $docente->dni ?? 'Sin DNI' }} - Código: {{ $docente->codigo_docente ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row form-row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_desde" class="form-label font-weight-bold">Fecha Desde *</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control @error('fecha_desde') is-invalid @enderror" value="{{ old('fecha_desde', date('Y-m-01')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_hasta" class="form-label font-weight-bold">Fecha Hasta *</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control @error('fecha_hasta') is-invalid @enderror" value="{{ old('fecha_hasta', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row form-row mb-4">
                            <div class="col-md-12">
                                <label for="tipo" class="form-label font-weight-bold">Motivo / Tipo de Memorando *</label>
                                <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                    <option value="RETRASO_REITERADO" {{ old('tipo') == 'RETRASO_REITERADO' ? 'selected' : '' }}>Retraso Reincidente (3 o más tardanzas en el mes)</option>
                                    <option value="INASISTENCIA_INJUSTIFICADA" {{ old('tipo') == 'INASISTENCIA_INJUSTIFICADA' ? 'selected' : '' }}>Inasistencia Injustificada a Clases</option>
                                    <option value="INCUMPLIMIENTO_HORARIO" {{ old('tipo') == 'INCUMPLIMIENTO_HORARIO' ? 'selected' : '' }}>Incumplimiento General de Horario Institucional</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" name="accion" value="preview" class="btn btn-dac-outline">
                                <i class="fas fa-eye me-1"></i> Vista Previa
                            </button>
                            <button type="submit" name="accion" value="descargar" class="btn btn-dac">
                                <i class="fas fa-download me-1"></i> Generar y Descargar PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
