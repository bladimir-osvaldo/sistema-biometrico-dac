@extends('layouts.app')

@section('title', 'Centro de Monitoreo de Hardware')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800" style="color: var(--color-plomo);">Centro de Monitoreo ESP32</h1>

    <div class="row stats-grid mb-4">
        <div class="col-md-4">
            <div class="card stat-card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1 stat-label">
                                Total Dispositivos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 stat-value">{{ $total }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-microchip fa-2x text-gray-300 stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1 stat-label">
                                Dispositivos ONLINE</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 stat-value">{{ $online }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-wifi fa-2x text-gray-300 stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1 stat-label">
                                Dispositivos OFFLINE</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 stat-value">{{ $offline }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-triangle-exclamation fa-2x text-gray-300 stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($devices as $device)
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: var(--color-plomo); color: white;">
                    <h6 class="m-0 font-weight-bold">{{ $device->name }} ({{ $device->codigo }})</h6>
                    <span class="badge badge-{{ $device->status == 'ONLINE' ? 'success' : ($device->status == 'OFFLINE' ? 'danger' : 'warning') }}">
                        {{ $device->status }}
                    </span>
                </div>
                <div class="card-body">
                    <p><strong>Ubicación:</strong> {{ $device->ubicacion }}</p>
                    <p><strong>MAC Address:</strong> {{ $device->mac_address }}</p>
                    <p><strong>Firmware:</strong> {{ $device->firmware }}</p>
                    <p><strong>Último latido (Heartbeat):</strong> {{ \Carbon\Carbon::parse($device->last_heartbeat)->diffForHumans() }}</p>
                    
                    <div class="form-group mb-0">
                        <label><strong>API Token:</strong></label>
                        <div class="input-group">
                            <input type="password" class="form-control" value="{{ $device->api_token }}" id="token_{{ $device->id }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleToken({{ $device->id }})">
                                <i class="fa-solid fa-eye" id="eye_{{ $device->id }}"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-4">
        <p class="text-muted small"><i class="fa-solid fa-rotate"></i> La página se actualiza automáticamente (Demo). Esta vista consume el estado real de los lectores ESP32.</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleToken(id) {
        var input = document.getElementById('token_' + id);
        var icon = document.getElementById('eye_' + id);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
