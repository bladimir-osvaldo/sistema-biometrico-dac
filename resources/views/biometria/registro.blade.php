@extends('layouts.app')

@section('title', 'Enrolamiento Biométrico Web')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-fingerprint text-guindo me-2"></i>Módulo de Captura Biométrica</h2>
            <p class="text-muted small mb-0">Enrolamiento web y prueba de captura de plantillas de huella dactilar</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-custom mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header-custom">
                    <h5 class="card-title-custom mb-0"><i class="fas fa-user-check me-2"></i>Selección de Docente y Registro de Huellas (3 Slots)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('biometria.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="user_id" class="form-label font-weight-bold">Seleccionar Docente *</label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required onchange="cargarHuellasDocente(this)">
                                <option value="">-- Seleccione un docente --</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id }}" {{ (isset($selectedUser) && $selectedUser->id == $docente->id) ? 'selected' : '' }}>
                                        {{ $docente->name }} (CI: {{ $docente->dni ?? 'Sin DNI' }} | Código: {{ $docente->codigo_docente ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="my-4">

                        <div class="alert alert-warning alert-custom mb-4">
                            <i class="fas fa-microchip me-2"></i>
                            <strong>¿Registraste la huella en el lector ESP32?</strong> Escribe aquí el número (ID) del sensor R307 que apareció en el LCD al enrolar, y presiona Guardar. Así se vincula el docente con su huella en el lector.
                        </div>

                        <div class="mb-4">
                            <label for="r307_id" class="form-label font-weight-bold">ID del sensor R307 (obligatorio para el lector físico)</label>
                            <input type="number" name="r307_id" id="r307_id" class="form-control" min="1" max="300" placeholder="Ej: 1, 2, 3..." value="{{ old('r307_id', optional($selectedUser ? $selectedUser->huellas->whereNotNull('r307_id')->first() : null)->r307_id) }}">
                            @error('r307_id')
                                <small style="color:#ef4444;">{{ $message }}</small>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <h6 class="font-weight-bold mb-3"><i class="fas fa-hand-pointer text-guindo me-2"></i>Captura de Huellas (Prueba Simulación SHA-256)</h6>
                        
                        <!-- Slot 1: Índice Derecho -->
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Dedo 1: Índice Derecho</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                                <input type="text" name="huella_1" id="huella_1" class="form-control font-monospace" placeholder="Hash de plantilla SHA-256 (Ej: 8f9b...)" value="{{ old('huella_1', optional($selectedUser ? $selectedUser->huellas->where('dedo_numero', 1)->first() : null)->template_hash) }}">
                                <button type="button" class="btn btn-dac-outline" onclick="generarHashPrueba('huella_1')">
                                    <i class="fas fa-random me-1"></i> Simular Captura 1
                                </button>
                            </div>
                        </div>

                        <!-- Slot 2: Pulgar Derecho -->
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Dedo 2: Pulgar Derecho</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                                <input type="text" name="huella_2" id="huella_2" class="form-control font-monospace" placeholder="Hash de plantilla SHA-256 (Ej: 3c1a...)" value="{{ old('huella_2', optional($selectedUser ? $selectedUser->huellas->where('dedo_numero', 2)->first() : null)->template_hash) }}">
                                <button type="button" class="btn btn-dac-outline" onclick="generarHashPrueba('huella_2')">
                                    <i class="fas fa-random me-1"></i> Simular Captura 2
                                </button>
                            </div>
                        </div>

                        <!-- Slot 3: Índice Izquierdo -->
                        <div class="mb-4">
                            <label class="form-label font-weight-bold">Dedo 3: Índice Izquierdo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                                <input type="text" name="huella_3" id="huella_3" class="form-control font-monospace" placeholder="Hash de plantilla SHA-256 (Ej: e4d2...)" value="{{ old('huella_3', optional($selectedUser ? $selectedUser->huellas->where('dedo_numero', 3)->first() : null)->template_hash) }}">
                                <button type="button" class="btn btn-dac-outline" onclick="generarHashPrueba('huella_3')">
                                    <i class="fas fa-random me-1"></i> Simular Captura 3
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info alert-custom">
                            <i class="fas fa-info-circle me-2"></i> El botón <strong>Simular Captura</strong> genera un hash de prueba para validar la integración biométrica. En entorno físico real, el sensor de huella ESP32 enviará la plantilla vía API REST.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-dac">
                                <i class="fas fa-save me-1"></i> Guardar Plantillas Biométricas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function generarHashPrueba(inputId) {
    const chars = '0123456789abcdef';
    let hash = '';
    for (let i = 0; i < 64; i++) {
        hash += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById(inputId).value = hash;
}

function cargarHuellasDocente(selectElem) {
    if (selectElem.value) {
        window.location.href = "{{ route('biometria.index') }}?user_id=" + selectElem.value;
    }
}
</script>
@endsection
