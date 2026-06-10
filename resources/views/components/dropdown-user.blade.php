{{--
    Componente: Menú desplegable de cuenta (estilo Gmail).
    Muestra el avatar del usuario y un menú con acciones de perfil,
    cambio de contraseña y cierre de sesión.

    Uso:  @include('components.dropdown-user')
--}}

<div class="user-menu" id="userMenu">
    {{-- Botón disparador: avatar + nombre + rol + flecha --}}
    <button type="button" class="user-menu-btn" id="userMenuBtn" aria-haspopup="true" aria-expanded="false">
        <img src="{{ auth()->user()->foto_url }}"
             alt="Avatar"
             class="user-menu-avatar"
             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'U') }}&background=6b0c24&color=ffffff&bold=true&size=80'">
        <span class="user-menu-meta">
            <span class="user-menu-name d-block">{{ Str::limit(auth()->user()->name ?? 'Usuario', 18) }}</span>
            <span class="user-menu-role d-block">{{ auth()->user()->roles->pluck('name')->first() ?? 'Docente' }}</span>
        </span>
        <i class="fa-solid fa-chevron-down user-menu-chevron"></i>
    </button>

    {{-- Panel desplegable --}}
    <div class="user-menu-dropdown" role="menu">
        <div class="user-menu-head">
            <strong>{{ auth()->user()->name ?? 'Usuario' }}</strong>
            <span>{{ auth()->user()->email }}</span>
        </div>

        <a href="{{ route('profile.show') }}" class="user-menu-item" role="menuitem">
            <i class="fa-solid fa-circle-user"></i> Ver Mi Perfil
        </a>
        <a href="{{ route('profile.edit') }}" class="user-menu-item" role="menuitem">
            <i class="fa-solid fa-pen-to-square"></i> Editar Perfil
        </a>
        <a href="{{ route('profile.edit') }}#password-section" class="user-menu-item" role="menuitem">
            <i class="fa-solid fa-key"></i> Cambiar Contraseña
        </a>

        <div class="user-menu-divider"></div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="user-menu-item user-menu-logout" role="menuitem">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</div>
