@extends('layouts.app')

@section('title', 'Editar Docente: ' . $user->name)

@section('content')

<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
    <a href="{{ route('users.index') }}" class="btn-dac-outline" style="padding:8px 14px; font-size:13px;">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
    <div>
        <h2 style="font-family:'Outfit',sans-serif; font-size:22px; font-weight:700; color:var(--color-guindo);">
            <i class="fa-solid fa-user-pen"></i> Editar Perfil Docente
        </h2>
        <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            Modificando los datos de: <strong>{{ $user->name }}</strong>
        </p>
    </div>
</div>

<form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" id="editUserForm">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

        {{-- ─── COLUMNA IZQUIERDA: Datos Personales ─── --}}
        <div>
            <div class="card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <i class="fa-solid fa-id-card"></i> Datos Personales
                    </div>
                </div>

                {{-- Avatar actual --}}
                <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px; padding:16px; background:rgba(107,12,36,0.04); border-radius:10px; border:1px dashed var(--border-color);">
                    <img src="{{ $user->foto_url }}"
                         alt="Foto actual"
                         id="previewImg"
                         style="width:70px; height:70px; border-radius:50%; object-fit:cover; border:3px solid var(--color-dorado);"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6b0c24&color=ffffff&bold=true&size=80'">
                    <div>
                        <strong style="font-size:14px;">{{ $user->name }}</strong>
                        <div style="font-size:12px; color:var(--text-muted);">
                            {{ $user->roles->pluck('name')->first() ?? 'Sin rol' }} ·
                            {{ $user->codigo_docente ?? 'Sin código' }}
                        </div>
                    </div>
                </div>

                {{-- Nombre completo --}}
                <div class="form-group">
                    <label class="form-label" for="name">
                        Nombre Completo <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="dni">CI / DNI</label>
                        <input type="text" name="dni" id="dni" class="form-control"
                            value="{{ old('dni', $user->dni) }}" placeholder="Ej: 1234567">
                        @error('dni')
                            <small style="color:#ef4444;">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="telefono">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control"
                            value="{{ old('telefono', $user->telefono) }}" placeholder="Ej: 71234567">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="carrera">Carrera / Especialidad</label>
                    <input type="text" name="carrera" id="carrera" class="form-control"
                        value="{{ old('carrera', $user->carrera) }}" placeholder="Ej: Ingeniería de Sistemas">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="cargo">Cargo</label>
                        <input type="text" name="cargo" id="cargo" class="form-control"
                            value="{{ old('cargo', $user->cargo) }}" placeholder="Ej: Docente Titular">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="codigo_docente">Código Docente</label>
                        <input type="text" name="codigo_docente" id="codigo_docente" class="form-control"
                            value="{{ old('codigo_docente', $user->codigo_docente) }}" placeholder="Ej: DOC-1006">
                        @error('codigo_docente')
                            <small style="color:#ef4444;">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estado">Estado Operativo</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="ACTIVO"   {{ old('estado', $user->estado) === 'ACTIVO'    ? 'selected' : '' }}>✅ Activo</option>
                        <option value="INACTIVO" {{ old('estado', $user->estado) === 'INACTIVO'  ? 'selected' : '' }}>❌ Inactivo</option>
                        <option value="LICENCIA" {{ old('estado', $user->estado) === 'LICENCIA'  ? 'selected' : '' }}>🔵 En Licencia</option>
                    </select>
                </div>

                {{-- Actualizar foto --}}
                <div class="form-group">
                    <label class="form-label" for="foto_perfil">
                        Actualizar Fotografía
                        <small style="color:var(--text-muted); font-weight:400;">(JPG, PNG – máx. 2MB)</small>
                    </label>
                    <input type="file" name="foto_perfil" id="foto_perfil" class="form-control"
                        accept="image/jpeg,image/png,image/webp" onchange="previewFoto(this)">
                </div>
            </div>
        </div>

        {{-- ─── COLUMNA DERECHA: Acceso y Rol ─── --}}
        <div>
            <div class="card">
                <div class="card-header-custom">
                    <div class="card-title-custom">
                        <i class="fa-solid fa-key"></i> Acceso y Rol del Sistema
                    </div>
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">
                        Correo Institucional <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Nueva contraseña (opcional) --}}
                <div class="form-group">
                    <label class="form-label" for="password">
                        Nueva Contraseña
                        <small style="color:var(--text-muted); font-weight:400;">(dejar vacío para no cambiar)</small>
                    </label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="••••••••" minlength="8">
                        <button type="button" onclick="togglePwd('password', 'eyePass1')"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                            <i class="fa-solid fa-eye" id="eyePass1"></i>
                        </button>
                    </div>
                    @error('password')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Confirmar nueva contraseña --}}
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar Nueva Contraseña</label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control" placeholder="••••••••">
                        <button type="button" onclick="togglePwd('password_confirmation', 'eyePass2')"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                            <i class="fa-solid fa-eye" id="eyePass2"></i>
                        </button>
                    </div>
                </div>

                {{-- Rol --}}
                <div class="form-group">
                    <label class="form-label" for="role">
                        Rol en el Sistema <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="role" id="role" class="form-select" required>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->name }}"
                                {{ $user->hasRole($rol->name) ? 'selected' : '' }}>
                                {{ $rol->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Info de registro --}}
                <div style="background:var(--input-bg); border:1px solid var(--border-color); border-radius:10px; padding:14px; font-size:12.5px; color:var(--text-muted);">
                    <div style="margin-bottom:6px;">
                        <i class="fa-solid fa-calendar" style="color:var(--color-guindo);"></i>
                        Registrado: <strong>{{ $user->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-clock-rotate-left" style="color:var(--color-guindo);"></i>
                        Última actualización: <strong>{{ $user->updated_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:8px;">
                <a href="{{ route('users.index') }}" class="btn-dac-outline">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn-dac" id="btnUpdate">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
    function togglePwd(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        input.type  = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    }

    function previewFoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImg').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('editUserForm').addEventListener('submit', () => {
        const btn = document.getElementById('btnUpdate');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
        btn.disabled = true;
    });
</script>
@endsection
