@extends('layouts.app')

@section('title', 'Registrar Dispositivo IoT')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-microchip text-guindo me-2"></i>Nuevo Lector Biométrico ESP32</h2>
            <p class="text-muted small mb-0">Registrar nuevo dispositivo de captura para control de asistencia</p>
        </div>
        <a href="{{ route('devices.index') }}" class="btn btn-dac-outline">
            <i class="fas fa-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0"><i class="fas fa-plus-circle me-2"></i>Parámetros del Dispositivo Hardware</h5>
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

                    <form action="{{ route('devices.store') }}" method="POST">
                        @csrf
                        
                        <div class="row form-row mb-3">
                            <div class="col-md-6">
                                <label for="codigo" class="form-label font-weight-bold">Código del Dispositivo *</label>
                                <input type="text" name="codigo" id="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo', 'ESP32-AULA-' . rand(100, 999)) }}" required placeholder="Ej: ESP32-AULA-101">
                            </div>
                            <div class="col-md-6">
                                <label for="nombre" class="form-label font-weight-bold">Nombre Descriptivo *</label>
                                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required placeholder="Ej: Lector Puerta Principal">
                            </div>
                        </div>

                        <div class="row form-row mb-3">
                            <div class="col-md-6">
                                <label for="ubicacion_aula" class="form-label font-weight-bold">Ubicación / Aula *</label>
                                <input type="text" name="ubicacion_aula" id="ubicacion_aula" class="form-control @error('ubicacion_aula') is-invalid @enderror" value="{{ old('ubicacion_aula') }}" required placeholder="Ej: Aula 204 - Bloque A">
                            </div>
                            <div class="col-md-6">
                                <label for="mac_address" class="form-label font-weight-bold">Dirección MAC (Wi-Fi)</label>
                                <input type="text" name="mac_address" id="mac_address" class="form-control @error('mac_address') is-invalid @enderror" value="{{ old('mac_address') }}" placeholder="Ej: AA:BB:CC:DD:EE:11">
                            </div>
                        </div>

                        <div class="row form-row mb-4">
                            <div class="col-md-6">
                                <label for="firmware_version" class="form-label font-weight-bold">Versión Firmware *</label>
                                <input type="text" name="firmware_version" id="firmware_version" class="form-control @error('firmware_version') is-invalid @enderror" value="{{ old('firmware_version', '1.0.0') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="estado" class="form-label font-weight-bold">Estado Inicial *</label>
                                <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                    <option value="OFFLINE" {{ old('estado') == 'OFFLINE' ? 'selected' : '' }}>Fuera de Línea (OFFLINE)</option>
                                    <option value="ONLINE" {{ old('estado') == 'ONLINE' ? 'selected' : '' }}>En Línea (ONLINE)</option>
                                    <option value="MANTENIMIENTO" {{ old('estado') == 'MANTENIMIENTO' ? 'selected' : '' }}>En Mantenimiento</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info alert-custom">
                            <i class="fas fa-key me-2"></i> El <strong>API Token Sanctum (Bearer Token)</strong> para la autenticación segura del microcontrolador ESP32 se generará automáticamente al guardar.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('devices.index') }}" class="btn btn-dac-outline">Cancelar</a>
                            <button type="submit" class="btn btn-dac">
                                <i class="fas fa-save me-1"></i> Registrar Dispositivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
