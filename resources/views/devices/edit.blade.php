@extends('layouts.app')

@section('title', 'Editar Dispositivo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-microchip text-guindo me-2"></i>Editar Dispositivo {{ $device->codigo }}</h2>
            <p class="text-muted small mb-0">Actualizar configuración del lector de huella ESP32</p>
        </div>
        <a href="{{ route('devices.index') }}" class="btn btn-dac-outline">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0"><i class="fas fa-edit me-2"></i>Configuración Hardware</h5>
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

                    <form action="{{ route('devices.update', $device) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row form-row mb-3">
                            <div class="col-md-6">
                                <label for="codigo" class="form-label font-weight-bold">Código del Dispositivo *</label>
                                <input type="text" name="codigo" id="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo', $device->codigo) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nombre" class="form-label font-weight-bold">Nombre Descriptivo *</label>
                                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $device->nombre) }}" required>
                            </div>
                        </div>

                        <div class="row form-row mb-3">
                            <div class="col-md-6">
                                <label for="ubicacion_aula" class="form-label font-weight-bold">Ubicación / Aula *</label>
                                <input type="text" name="ubicacion_aula" id="ubicacion_aula" class="form-control @error('ubicacion_aula') is-invalid @enderror" value="{{ old('ubicacion_aula', $device->ubicacion_aula) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mac_address" class="form-label font-weight-bold">Dirección MAC (Wi-Fi)</label>
                                <input type="text" name="mac_address" id="mac_address" class="form-control @error('mac_address') is-invalid @enderror" value="{{ old('mac_address', $device->mac_address) }}">
                            </div>
                        </div>

                        <div class="row form-row mb-4">
                            <div class="col-md-6">
                                <label for="firmware_version" class="form-label font-weight-bold">Versión Firmware *</label>
                                <input type="text" name="firmware_version" id="firmware_version" class="form-control @error('firmware_version') is-invalid @enderror" value="{{ old('firmware_version', $device->firmware_version) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="estado" class="form-label font-weight-bold">Estado *</label>
                                <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                    <option value="ONLINE" {{ old('estado', $device->estado) == 'ONLINE' ? 'selected' : '' }}>En Línea (ONLINE)</option>
                                    <option value="OFFLINE" {{ old('estado', $device->estado) == 'OFFLINE' ? 'selected' : '' }}>Fuera de Línea (OFFLINE)</option>
                                    <option value="MANTENIMIENTO" {{ old('estado', $device->estado) == 'MANTENIMIENTO' ? 'selected' : '' }}>En Mantenimiento</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label font-weight-bold">API Token Actual:</label>
                            <input type="text" class="form-control font-monospace text-muted" value="{{ $device->api_token }}" readonly>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('devices.index') }}" class="btn btn-dac-outline">Cancelar</a>
                            <button type="submit" class="btn btn-dac">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
