@extends('layouts.app')

@section('title', 'Registrar Nuevo Docente')

@section('content')

<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
    <a href="{{ route('users.index') }}" class="btn-dac-outline" style="padding:8px 14px; font-size:13px;">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
    <div>
        <h2 style="font-family:'Outfit',sans-serif; font-size:22px; font-weight:700; color:var(--color-guindo);">
            <i class="fa-solid fa-user-plus"></i> Registrar Nuevo Docente
        </h2>
        <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            Completa el formulario para agregar un nuevo docente al Programa DAC
        </p>
    </div>
</div>

<form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" id="createUserForm">
    @csrf

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

        {{-- ─── COLUMNA IZQUIERDA: Datos Personales ─── --}}
        <div>
            <div class="card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <i class="fa-solid fa-id-card"></i> Datos Personales
                    </div>
                </div>

                {{-- Nombre completo --}}
                <div class="form-group">
                    <label class="form-label" for="name">
                        Nombre Completo <span style="color:#ef4444;">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        placeholder="Ej: Ing. Juan Pérez García"
                        value="{{ old('name') }}"
                        required>
                    @error('name')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-row">
                    {{-- DNI / CI --}}
                    <div class="form-group">
                        <label class="form-label" for="dni">CI / DNI</label>
                        <input type="text" name="dni" id="dni" class="form-control"
                            placeholder="Ej: 1234567" value="{{ old('dni') }}">
                        @error('dni')
                            <small style="color:#ef4444;">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Teléfono --}}
                    <div class="form-group">
                        <label class="form-label" for="telefono">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control"
                            placeholder="Ej: 71234567" value="{{ old('telefono') }}">
                    </div>
                </div>

                {{-- Carrera / Especialidad --}}
                <div class="form-group">
                    <label class="form-label" for="carrera">Carrera / Especialidad</label>
                    <input type="text" name="carrera" id="carrera" class="form-control"
                        placeholder="Ej: Ingeniería de Sistemas" value="{{ old('carrera') }}">
                </div>

                <div class="form-row">
                    {{-- Cargo --}}
                    <div class="form-group">
                        <label class="form-label" for="cargo">Cargo</label>
                        <input type="text" name="cargo" id="cargo" class="form-control"
                            placeholder="Ej: Docente Titular" value="{{ old('cargo', 'Docente') }}">
                    </div>

                    {{-- Código Docente --}}
                    <div class="form-group">
                        <label class="form-label" for="codigo_docente">Código Docente</label>
                        <input type="text" name="codigo_docente" id="codigo_docente" class="form-control"
                            placeholder="Ej: DOC-1006" value="{{ old('codigo_docente') }}">
                        @error('codigo_docente')
                            <small style="color:#ef4444;">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- Estado --}}
                <div class="form-group">
                    <label class="form-label" for="estado">Estado Operativo</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="ACTIVO" {{ old('estado') === 'ACTIVO' ? 'selected' : '' }}>✅ Activo</option>
                        <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>❌ Inactivo</option>
                        <option value="LICENCIA" {{ old('estado') === 'LICENCIA' ? 'selected' : '' }}>🔵 En Licencia</option>
                    </select>
                </div>

                {{-- Foto de perfil --}}
                <div class="form-group">
                    <label class="form-label" for="foto_perfil">
                        Fotografía de Perfil
                        <small style="color:var(--text-muted); font-weight:400;">(JPG, PNG – máx. 2MB)</small>
                    </label>
                    <input type="file" name="foto_perfil" id="foto_perfil" class="form-control"
                        accept="image/jpeg,image/png,image/webp" onchange="previewFoto(this)">

                    {{-- Preview de la foto --}}
                    <div id="fotoPreview" style="margin-top:12px; display:none; text-align:center;">
                        <img id="previewImg" src="" alt="Preview"
                            style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid var(--color-dorado);">
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── COLUMNA DERECHA: Acceso y Rol ─── --}}
        <div>
            <div class="card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <i class="fa-solid fa-key"></i> Acceso al Sistema
                    </div>
                </div>

                {{-- Email institucional --}}
                <div class="form-group">
                    <label class="form-label" for="email">
                        Correo Institucional <span style="color:#ef4444;">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        placeholder="Ej: docente@dac.edu.bo"
                        value="{{ old('email') }}"
                        required>
                    @error('email')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="form-group">
                    <label class="form-label" for="password">
                        Contraseña <span style="color:#ef4444;">*</span>
                        <small style="color:var(--text-muted); font-weight:400;">(mín. 8 caracteres)</small>
                    </label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="••••••••" required minlength="8">
                        <button type="button" onclick="togglePwd('password', 'eyePass1')"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                            <i class="fa-solid fa-eye" id="eyePass1"></i>
                        </button>
                    </div>
                    @error('password')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Confirmar contraseña --}}
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">
                        Confirmar Contraseña <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control" placeholder="••••••••" required>
                        <button type="button" onclick="togglePwd('password_confirmation', 'eyePass2')"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                            <i class="fa-solid fa-eye" id="eyePass2"></i>
                        </button>
                    </div>
                </div>

                {{-- Rol del sistema --}}
                <div class="form-group">
                    <label class="form-label" for="role">
                        Rol en el Sistema <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="">— Seleccionar rol —</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->name }}" {{ old('role') === $rol->name ? 'selected' : '' }}>
                                {{ $rol->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Información sobre roles --}}
                <div style="background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.3); border-radius:10px; padding:14px; font-size:12.5px; color:var(--text-muted);">
                    <strong style="color:var(--color-guindo);">
                        <i class="fa-solid fa-circle-info"></i> Roles disponibles:
                    </strong>
                    <ul style="margin-top:8px; padding-left:16px; line-height:1.8;">
                        <li><strong>Administrador</strong> – Acceso total al sistema</li>
                        <li><strong>Coordinador DAC</strong> – Gestión académica y reportes</li>
                        <li><strong>Docente</strong> – Solo consulta de sus propias asistencias</li>
                    </ul>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:8px;">
                <a href="{{ route('users.index') }}" class="btn-dac-outline">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn-dac" id="btnCreate">
                    <i class="fa-solid fa-user-plus"></i> Registrar Docente
                </button>
            </div>
        </div>
    </div>

</form>

@endsection

@section('scripts')
<script>
    // Mostrar/ocultar contraseña
    function togglePwd(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        input.type  = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    }

    // Preview de foto de perfil
    function previewFoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('fotoPreview').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Feedback visual al enviar
    document.getElementById('createUserForm').addEventListener('submit', () => {
        const btn = document.getElementById('btnCreate');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
        btn.disabled = true;
    });
</script>
@endsection
