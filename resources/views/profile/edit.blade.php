@extends('layouts.app')

@section('title', 'Editar Mi Perfil')

@section('content')

<div style="max-width:700px; margin:0 auto;">

    <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
        <a href="{{ route('dashboard') }}" class="btn-dac-outline" style="padding:8px 14px; font-size:13px;">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
        <div>
            <h2 style="font-family:'Outfit',sans-serif; font-size:22px; font-weight:700; color:var(--color-guindo);">
                <i class="fa-solid fa-user-gear"></i> Editar Mi Perfil
            </h2>
            <p style="font-size:13px; color:var(--text-muted); margin-top:2px;">
                Actualiza tu información personal, correo electrónico y fotografía de perfil
            </p>
        </div>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header-custom">
                <div class="card-title-custom">
                    <i class="fa-solid fa-id-card"></i> Información Personal
                </div>
            </div>

            {{-- Avatar actual --}}
            <div style="display:flex; align-items:center; gap:20px; margin-bottom:24px; padding:16px; background:var(--input-bg); border-radius:12px; border:1px solid var(--border-color);">
                <img src="{{ $user->foto_url }}"
                     alt="Foto de perfil"
                     id="avatarPreview"
                     style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--color-dorado); box-shadow:0 4px 12px rgba(0,0,0,0.15);"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6b0c24&color=ffffff&bold=true&size=100'">
                <div>
                    <strong style="font-size:16px; font-family:'Outfit',sans-serif; color:var(--text-main);">{{ $user->name }}</strong>
                    <div style="font-size:12px; color:var(--color-guindo); font-weight:700; text-transform:uppercase; margin-top:2px;">
                        {{ $user->roles->pluck('name')->first() ?? 'Usuario' }}
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                        Código: <strong>{{ $user->codigo_docente ?? 'N/A' }}</strong> · DNI: <strong>{{ $user->dni ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            {{-- Nombre completo --}}
            <div class="form-group">
                <label class="form-label" for="name">Nombre Completo <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <small style="color:#ef4444;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label" for="email">Correo Electrónico Institucional <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <small style="color:#ef4444;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Teléfono --}}
            <div class="form-group">
                <label class="form-label" for="telefono">Teléfono de Contacto</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono', $user->telefono) }}" placeholder="Ej: 71234567">
            </div>

            {{-- Fotografía de Perfil --}}
            <div class="form-group">
                <label class="form-label" for="foto_perfil">
                    Cambiar Fotografía de Perfil
                    <small style="color:var(--text-muted); font-weight:400;">(JPG, PNG, WebP – máx. 2MB)</small>
                </label>
                <input type="file" name="foto_perfil" id="foto_perfil" class="form-control" accept="image/jpeg,image/png,image/webp" onchange="previewAvatar(this)">
                @error('foto_perfil')
                    <small style="color:#ef4444;">{{ $message }}</small>
                @enderror
            </div>

            <div style="border-top:1px solid var(--border-color); margin:24px 0 20px; padding-top:20px;" id="password-section">
                <h4 style="font-family:'Outfit',sans-serif; color:var(--color-guindo); font-size:15px; margin-bottom:14px;">
                    <i class="fa-solid fa-lock"></i> Cambiar Contraseña (Opcional)
                </h4>

                <div class="form-group">
                    <label class="form-label" for="password">Nueva Contraseña <small style="color:var(--text-muted);">(dejar en blanco para conservar la actual)</small></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" minlength="8">
                    @error('password')
                        <small style="color:#ef4444;">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••">
                </div>
            </div>

            {{-- Botones --}}
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:10px;">
                <a href="{{ route('dashboard') }}" class="btn-dac-outline">Cancelar</a>
                <button type="submit" class="btn-dac">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                </button>
            </div>
        </div>

    </form>
</div>

@endsection

@section('scripts')
<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
