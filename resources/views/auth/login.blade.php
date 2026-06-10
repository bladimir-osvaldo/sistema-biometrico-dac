<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema Biométrico DAC</title>
    <meta name="description" content="Control de Asistencia Biométrica – Programa DAC, Unidad Educativa Tiquipaya">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ═══════════════════════════════════════════════
           Paleta Institucional DAC
           Guindo: #6b0c24  |  Dorado: #d4af37
           Plomo:  #1e293b  |  Blanco: #f8fafc
           ═══════════════════════════════════════════════ */
        :root {
            --guindo:      #6b0c24;
            --guindo-dark: #4a0818;
            --guindo-glow: rgba(107,12,36,0.35);
            --dorado:      #d4af37;
            --plomo:       #1e293b;
            --blanco:      #f8fafc;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0f1e 0%, #1a0a12 50%, #0d1117 100%);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ─── Panel izquierdo (Hero visual) ──────────────────── */
        .login-hero {
            flex: 1;
            display: none;
            flex-direction: column;
            justify-content: flex-end;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        @media (min-width: 960px) {
            .login-hero { display: flex; }
        }

        /* Banner de fondo */
        .hero-banner {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* Gradiente oscuro sobre el banner */
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(10,15,30,0.3) 0%,
                rgba(74,8,24,0.65) 60%,
                rgba(10,15,30,0.95) 100%
            );
        }

        /* Contenido sobre el banner */
        .hero-content {
            position: relative;
            z-index: 1;
            padding: 40px 50px;
        }

        .hero-logo-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
        }

        .hero-logo-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 3px solid var(--dorado);
            overflow: hidden;
            background: #fff;
            flex-shrink: 0;
            box-shadow: 0 0 20px var(--guindo-glow);
        }

        .hero-logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-brand h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
        }

        .hero-brand p {
            font-size: 12px;
            color: var(--dorado);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 3px;
        }

        .hero-description {
            color: rgba(255,255,255,0.75);
            font-size: 14px;
            line-height: 1.7;
            max-width: 380px;
            margin-bottom: 28px;
        }

        /* Chips de horarios DAC */
        .horarios-chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .horario-chip {
            background: rgba(212,175,55,0.15);
            border: 1px solid rgba(212,175,55,0.4);
            color: var(--dorado);
            padding: 8px 16px;
            border-radius: 24px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ─── Panel derecho (Formulario Login) ──────────────── */
        .login-panel {
            width: 100%;
            max-width: 400px;
            background: var(--blanco);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 32px;
            position: relative;
        }

        @media (min-width: 960px) {
            .login-panel { min-height: 100vh; }
        }

        /* Línea dorada superior */
        .login-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--guindo), var(--dorado), var(--guindo));
        }

        /* Encabezado del formulario */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            border: 3px solid var(--dorado);
            overflow: hidden;
            background: var(--guindo);
            margin: 0 auto 14px;
            box-shadow: 0 4px 20px var(--guindo-glow);
        }

        .login-logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-logo-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: var(--dorado);
        }

        .login-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: var(--guindo);
            line-height: 1.2;
        }

        .login-header p {
            font-size: 13px;
            color: #64748b;
            margin-top: 6px;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 7px;
        }

        .input-wrapper { position: relative; }

        .input-icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
            pointer-events: none;
        }

        .input-toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            font-size: 14px;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
        }

        .input-toggle-password:hover { color: var(--guindo); }

        .form-control {
            width: 100%;
            padding: 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: var(--guindo);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(107,12,36,0.12);
        }

        .form-control::placeholder { color: #94a3b8; }

        /* Opción "Recordarme" */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #475569;
            margin-bottom: 18px;
        }

        .remember-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--guindo);
            cursor: pointer;
        }

        /* Botón principal de login */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--guindo), var(--guindo-dark));
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.25s;
            box-shadow: 0 4px 14px var(--guindo-glow);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #7d0e2a, var(--guindo));
            transform: translateY(-1px);
            box-shadow: 0 6px 20px var(--guindo-glow);
        }

        .btn-login:active { transform: translateY(0); }

        /* Alerta de error */
        .alert-error {
            background: linear-gradient(135deg, #fff1f2, #fee2e2);
            border: 1px solid #fca5a5;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        /* Link "¿Olvidaste tu contraseña?" */
        .forgot-link {
            font-size: 12px;
            color: var(--guindo);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .forgot-link:hover { text-decoration: underline; }

        /* Footer del panel de login */
        .login-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .login-footer p {
            font-size: 11.5px;
            color: #94a3b8;
            line-height: 1.6;
        }

        .login-footer strong { color: var(--guindo); }

        /* ─── MODAL OLVIDÓ SU CONTRASEÑA ──────────────────── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show { display: flex; }

        .modal-box {
            background: #ffffff;
            width: 90%;
            max-width: 420px;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border-top: 4px solid var(--dorado);
            text-align: center;
            animation: modalIn 0.25s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(-8px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-icon {
            width: 60px;
            height: 60px;
            background: rgba(212,175,55,0.12);
            border: 2px solid rgba(212,175,55,0.4);
            color: var(--guindo);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
        }

        .modal-box h3 {
            font-family: 'Outfit', sans-serif;
            color: var(--guindo);
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .modal-box p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.65;
            margin-bottom: 18px;
        }

        .modal-contact-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
            font-size: 13px;
            margin-bottom: 20px;
            color: #334155;
        }

        .modal-contact-box strong { color: var(--guindo); font-size: 15px; }

        .btn-modal-close {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--guindo), var(--guindo-dark));
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-modal-close:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <!-- ═══════════════════════════════════════════════════
         PANEL IZQUIERDO: Banner visual del proyecto DAC
         ═══════════════════════════════════════════════════ -->
    <div class="login-hero">
        {{-- Banner del proyecto como fondo --}}
        <img src="{{ asset('images/banner.png') }}"
             alt="Banner Programa DAC – Unidad Educativa Tiquipaya"
             class="hero-banner"
             onerror="this.style.background='linear-gradient(135deg,#6b0c24,#4a0818)'; this.style.objectFit='fill'">

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <!-- Logo + nombre del programa -->
            <div class="hero-logo-row">
                <div class="hero-logo-circle">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Logo DAC"
                         onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:28px;color:#d4af37;\'><i class=\'fa-solid fa-fingerprint\'></i></div>'">
                </div>
                <div class="hero-brand">
                    <h1>SISTEMA BIOMÉTRICO DAC</h1>
                    <p>Unidad Educativa Tiquipaya · Bolivia</p>
                </div>
            </div>

            <!-- Descripción del sistema -->
            <p class="hero-description">
                Plataforma institucional de control de asistencia con tecnología biométrica ESP32,
                diseñada para el Programa de Desarrollo de Aptitudes y Capacidades (DAC).
                Registro preciso, reportes automáticos y gestión de horarios en tiempo real.
            </p>

            <!-- Horarios oficiales DAC -->
            <div class="horarios-chips">
                <div class="horario-chip">
                    <i class="fa-solid fa-clock"></i>
                    Turno 1: 13:15 – 14:20
                </div>
                <div class="horario-chip">
                    <i class="fa-solid fa-clock"></i>
                    Turno 2: 14:30 – 15:30
                </div>
                <div class="horario-chip">
                    <i class="fa-solid fa-calendar-week"></i>
                    Lunes – Viernes
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         PANEL DERECHO: Formulario de inicio de sesión
         ═══════════════════════════════════════════════════ -->
    <div class="login-panel">

        <!-- Encabezado con logo -->
        <div class="login-header">
            <div class="login-logo-circle">
                <img src="{{ asset('images/logo.png') }}"
                     alt="Logo"
                     onerror="this.parentElement.innerHTML='<div class=\'login-logo-fallback\'><i class=\'fa-solid fa-fingerprint\'></i></div>'">
            </div>
            <h2>SISTEMA BIOMÉTRICO</h2>
            <p>Programa DAC · Unidad Educativa Tiquipaya</p>
        </div>

        {{-- Alerta de errores de credenciales --}}
        @if ($errors->any())
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation fa-lg" style="margin-top:2px;"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Alerta informativa de sesión --}}
        @if (session('info'))
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #3b82f6;color:#1e40af;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;display:flex;gap:10px;align-items:center;">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- FORMULARIO DE LOGIN -->
        <form action="{{ route('login') }}" method="POST" id="loginForm" autocomplete="on">
            @csrf

            {{-- Campo: Correo Institucional --}}
            <div class="form-group">
                <label class="form-label" for="email">
                    Correo Electrónico Institucional
                </label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope input-icon-left"></i>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="ejemplo@dac.edu.bo"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email">
                </div>
            </div>

            {{-- Campo: Contraseña --}}
            <div class="form-group">
                <label class="form-label" for="password">
                    Contraseña
                    <button type="button" class="forgot-link" onclick="openForgotModal()" id="btnForgot">
                        ¿Olvidaste tu contraseña?
                    </button>
                </label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon-left"></i>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password">
                    <button type="button" class="input-toggle-password" id="togglePassword" title="Mostrar u ocultar contraseña" aria-label="Mostrar contraseña">
                        <i class="fa-solid fa-eye" id="passwordEyeIcon"></i>
                    </button>
                </div>
            </div>

            {{-- Botón de acceso --}}
            <button type="submit" class="btn-login" id="btnLogin">
                <i class="fa-solid fa-fingerprint"></i>
                Acceder al Sistema
            </button>
        </form>

        <!-- Footer del formulario -->
        <div class="login-footer">
            <p>
                &copy; {{ date('Y') }} <strong>Programa DAC</strong> · Unidad Educativa Tiquipaya · Bolivia<br>
                Sistema Biométrico de Control de Asistencia Docente
            </p>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         MODAL: Recuperación de Contraseña
         ═══════════════════════════════════════════════════ -->
    <div class="modal-overlay" id="modalForgot" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-box">
            <div class="modal-icon">
                <i class="fa-solid fa-key"></i>
            </div>
            <h3 id="modalTitle">Restablecer Contraseña</h3>
            <p>
                Por políticas de seguridad del Programa DAC, el restablecimiento de contraseñas
                debe ser solicitado directamente al <strong>Administrador de Sistemas</strong>,
                quien asignará una nueva clave temporal de acceso.
            </p>
            <div class="modal-contact-box">
                <i class="fa-solid fa-envelope" style="color:var(--guindo);"></i>
                Contacta al administrador:<br>
                <strong>admin@dac.edu.bo</strong>
            </div>
            <button type="button" class="btn-modal-close" onclick="closeForgotModal()">
                Entendido
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         SCRIPTS
         ═══════════════════════════════════════════════════ -->
    <script>
        // ─── Mostrar / Ocultar Contraseña ─────────────────────
        const toggleBtn   = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon     = document.getElementById('passwordEyeIcon');

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.className  = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });

        // ─── Modal de contraseña olvidada ──────────────────────
        function openForgotModal() {
            document.getElementById('modalForgot').classList.add('show');
        }

        function closeForgotModal() {
            document.getElementById('modalForgot').classList.remove('show');
        }

        // Cerrar modal al hacer clic fuera del cuadro
        document.getElementById('modalForgot').addEventListener('click', function(e) {
            if (e.target === this) closeForgotModal();
        });

        // ─── Feedback visual en botón login ───────────────────
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verificando...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
