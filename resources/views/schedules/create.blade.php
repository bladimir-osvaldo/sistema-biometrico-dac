@extends('layouts.app')

@section('title', 'Crear Horario')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Nuevo Horario</h2>
        <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header-custom">
                    <h5 class="mb-0">Datos del Horario</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-custom">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('schedules.store') }}" method="POST">
                        @csrf
                        <div class="row form-row mb-3">
                            <div class="col-md-12">
                                <label for="user_id" class="form-label">Docente</label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un docente</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('user_id') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->name }} ({{ $docente->codigo_docente ?? 'Sin código' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row form-row mb-3">
                            <div class="col-md-6">
                                <label for="materia" class="form-label">Materia</label>
                                <input type="text" name="materia" id="materia" class="form-control @error('materia') is-invalid @enderror" value="{{ old('materia') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="codigo_materia" class="form-label">Código de Materia</label>
                                <input type="text" name="codigo_materia" id="codigo_materia" class="form-control @error('codigo_materia') is-invalid @enderror" value="{{ old('codigo_materia') }}" required>
                            </div>
                        </div>

                        <div class="row form-row mb-3">
                            <div class="col-md-6">
                                <label for="aula" class="form-label">Aula</label>
                                <input type="text" name="aula" id="aula" class="form-control @error('aula') is-invalid @enderror" value="{{ old('aula') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dia_semana" class="form-label">Día de la Semana</label>
                                <select name="dia_semana" id="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                                    <option value="LUNES" {{ old('dia_semana') == 'LUNES' ? 'selected' : '' }}>Lunes</option>
                                    <option value="MARTES" {{ old('dia_semana') == 'MARTES' ? 'selected' : '' }}>Martes</option>
                                    <option value="MIERCOLES" {{ old('dia_semana') == 'MIERCOLES' ? 'selected' : '' }}>Miércoles</option>
                                    <option value="JUEVES" {{ old('dia_semana') == 'JUEVES' ? 'selected' : '' }}>Jueves</option>
                                    <option value="VIERNES" {{ old('dia_semana') == 'VIERNES' ? 'selected' : '' }}>Viernes</option>
                                    <option value="SABADO" {{ old('dia_semana') == 'SABADO' ? 'selected' : '' }}>Sábado</option>
                                </select>
                            </div>
                        </div>

                        <div class="row form-row mb-4">
                            <div class="col-md-4">
                                <label for="hora_inicio" class="form-label">Hora Inicio</label>
                                <input type="time" name="hora_inicio" id="hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror" value="{{ old('hora_inicio', '13:15') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="hora_fin" class="form-label">Hora Fin</label>
                                <input type="time" name="hora_fin" id="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror" value="{{ old('hora_fin', '14:20') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="tolerancia_minutos" class="form-label">Tolerancia (min)</label>
                                <input type="number" name="tolerancia_minutos" id="tolerancia_minutos" class="form-control @error('tolerancia_minutos') is-invalid @enderror" value="{{ old('tolerancia_minutos', '10') }}" min="0" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-dac">
                                <i class="fas fa-save"></i> Guardar Horario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header-custom bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Horarios Oficiales DAC</h5>
                </div>
                <div class="card-body">
                    <p>Referencia de los horarios oficiales de la Unidad Educativa Tiquipaya:</p>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Turno 1</strong>
                            <span>13:15 - 14:20</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Turno 2</strong>
                            <span>14:30 - 15:30</span>
                        </li>
                    </ul>
                    <div class="mt-3 text-muted small">
                        <i class="fas fa-exclamation-triangle text-warning"></i> La tolerancia sugerida es de 10 minutos.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
