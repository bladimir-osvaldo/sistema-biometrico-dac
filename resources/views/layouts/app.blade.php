<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | Sistema Biométrico DAC</title>
    <meta name="description" content="Sistema Biométrico de Control de Asistencia - Programa DAC, Unidad Educativa Tiquipaya">

    <!-- Google Fonts: Inter + Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 (grid, utilidades y componentes que usan las vistas) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ========================================================================
           SISTEMA DE DISEÑO INSTITUCIONAL – PROYECTO DAC
           Paleta: Guindo #6b0c24 | Dorado #d4af37 | Plomo #1e293b | Blanco #f8fafc
           ======================================================================== */
        :root {
            --color-guindo:       #6b0c24;
            --color-guindo-dark:  #4a0818;
            --color-guindo-light: #8b1a34;
            --color-dorado:       #d4af37;
            --color-dorado-dark:  #b89428;
            --color-plomo:        #1e293b;
            --color-plomo-light:  #334155;
            --color-blanco:       #f8fafc;

            /* Tema claro (por defecto) */
            --bg-body:      #f1f5f9;
            --bg-card:      #ffffff;
            --bg-sidebar:   #1e293b;
            --text-main:    #0f172a;
            --text-muted:   #64748b;
            --border-color: #e2e8f0;
            --card-shadow:  0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
            --input-bg:     #f8fafc;
        }

        /* Tema oscuro */
        body.dark-mode {
            --bg-body:      #121212;
            --bg-card:      #1a1a2e;
            --bg-sidebar:   #0d0d1a;
            --text-main:    #eef2f7;
            --text-muted:   #9aa3b2;
            --border-color: #2a2a3d;
            --card-shadow:  0 4px 20px rgba(0,0,0,0.45);
            --input-bg:     #23233a;
        }

        /* Tema sepia */
        body.theme-sepia {
            --bg-body:      #f5e7d3;
            --bg-card:      #fdf6e9;
            --bg-sidebar:   #3d2b1f;
            --text-main:    #3b2a20;
            --text-muted:   #7a6553;
            --border-color: #e0cdb4;
            --card-shadow:  0 2px 10px rgba(94,64,30,0.12);
            --input-bg:     #fffaf0;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ============================
           SIDEBAR (Barra lateral fija)
           ============================ */
        aside.sidebar {
            width: 265px;
            background-color: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 200;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            transition: background-color 0.3s ease;
        }

        /* Cabecera del sidebar con gradiente guindo */
        .sidebar-header {
            padding: 0;
            border-bottom: 1px solid rgba(212,175,55,0.3);
            background: linear-gradient(160deg, var(--color-guindo) 0%, var(--color-guindo-dark) 100%);
            position: relative;
            overflow: hidden;
        }

        /* Banner institucional en el sidebar */
        .sidebar-banner {
            width: 100%;
            height: 85px;
            object-fit: cover;
            object-position: center;
            display: block;
            opacity: 0.9;
        }

        /* Superposición de texto sobre el banner */
        .sidebar-brand-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(74,8,24,0.92) 0%, transparent 100%);
            padding: 10px 16px 8px;
        }

        .sidebar-logo-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Círculo con el logo del proyecto */
        .logo-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid var(--color-dorado);
            overflow: hidden;
            flex-shrink: 0;
            background: #fff;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 13px;
            color: #ffffff;
            line-height: 1.2;
            letter-spacing: 0.3px;
        }

        .brand-subtitle {
            font-size: 10px;
            color: var(--color-dorado);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* Sección del menú */
        .sidebar-nav {
            padding: 16px 10px;
            flex-grow: 1;
            overflow-y: auto;
        }

        /* Etiqueta de grupo en el sidebar */
        .sidebar-group-label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 12px 14px 6px;
        }

        /* Ítem de navegación */
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 3px;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar-nav a i {
            width: 18px;
            text-align: center;
            font-size: 15px;
            transition: color 0.2s;
        }

        .sidebar-nav a:hover {
            background-color: rgba(255,255,255,0.06);
            color: #ffffff;
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--color-guindo), var(--color-guindo-light));
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(107,12,36,0.35);
        }

        .sidebar-nav a.active i {
            color: var(--color-dorado);
        }

        /* Indicador activo (borde lateral dorado) */
        .sidebar-nav a.active::before {
            content: '';
            position: absolute;
            left: 0; top: 20%; bottom: 20%;
            width: 3px;
            background: var(--color-dorado);
            border-radius: 0 3px 3px 0;
        }

        /* Pie del sidebar (info usuario logueado) */
        .sidebar-user-footer {
            padding: 14px;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid var(--color-dorado);
            object-fit: cover;
        }

        .sidebar-user-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #e2e8f0;
            line-height: 1.2;
        }

        .sidebar-user-role {
            font-size: 10px;
            color: var(--color-dorado);
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* ================================
           CONTENIDO PRINCIPAL (Main)
           ================================ */
        main.main-content {
            margin-left: 265px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Barra superior (Top Navbar) */
        header.top-navbar {
            background-color: var(--bg-card);
            height: 68px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            box-shadow: var(--card-shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            transition: background-color 0.3s;
        }

        /* Breadcrumb / Título de página */
        .page-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--color-guindo);
        }

        body.dark-mode .page-title { color: var(--color-dorado); }

        .page-breadcrumb {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Controles del navbar derecho */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Botón modo oscuro/claro */
        .theme-toggle-btn {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 38px; height: 38px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            transition: all 0.2s;
        }

        .theme-toggle-btn:hover {
            background: var(--color-dorado);
            color: #1e293b;
            border-color: var(--color-dorado);
        }

        /* Selector de temas desplegable */
        .theme-selector { position: relative; }

        .theme-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            min-width: 160px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.18);
            padding: 6px;
            z-index: 300;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: all 0.2s ease;
        }

        .theme-selector.open .theme-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .theme-dropdown .theme-option {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 12px;
            border: none;
            background: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            cursor: pointer;
            text-align: left;
            font-family: 'Inter', sans-serif;
            transition: background 0.15s;
        }

        .theme-dropdown .theme-option i { width: 16px; text-align: center; color: var(--color-guindo); }

        .theme-dropdown .theme-option:hover { background: var(--input-bg); }

        .theme-dropdown .theme-option.active {
            background: rgba(107,12,36,0.08);
            color: var(--color-guindo);
            font-weight: 700;
        }

        body.dark-mode .theme-dropdown .theme-option.active,
        body.theme-sepia .theme-dropdown .theme-option.active {
            background: rgba(212,175,55,0.12);
            color: var(--color-dorado-dark);
        }

        /* Chip de usuario (nombre + rol) en navbar */
        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 14px 5px 5px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 40px;
            cursor: default;
        }

        .user-chip-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid var(--color-dorado);
            object-fit: cover;
        }

        .user-chip-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.2;
            max-width: 140px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-chip-role {
            font-size: 10px;
            font-weight: 700;
            color: var(--color-guindo);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.dark-mode .user-chip-role { color: var(--color-dorado); }

        /* Botón logout */
        .btn-logout {
            background-color: transparent;
            border: 1px solid #fca5a5;
            color: #ef4444;
            font-size: 13px;
            cursor: pointer;
            padding: 8px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background-color: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }

        /* ==========================================
           MENÚ DESPLEGABLE DE CUENTA (estilo Gmail)
           ========================================== */
        .user-menu {
            position: relative;
        }

        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 12px 5px 5px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-main);
        }

        .user-menu-btn:hover {
            border-color: var(--color-dorado);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .user-menu-btn .user-menu-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid var(--color-dorado);
            object-fit: cover;
        }

        .user-menu-btn .user-menu-meta { text-align: left; line-height: 1.15; }

        .user-menu-btn .user-menu-name {
            font-size: 12.5px;
            font-weight: 600;
            max-width: 130px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-menu-btn .user-menu-role {
            font-size: 9.5px;
            font-weight: 700;
            color: var(--color-guindo);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.dark-mode .user-menu-btn .user-menu-role { color: var(--color-dorado); }

        .user-menu-btn .user-menu-chevron {
            font-size: 11px;
            color: var(--text-muted);
            transition: transform 0.2s;
        }

        .user-menu.open .user-menu-btn .user-menu-chevron { transform: rotate(180deg); }

        .user-menu-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            min-width: 240px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.18);
            padding: 8px;
            z-index: 300;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: all 0.2s ease;
        }

        .user-menu.open .user-menu-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-menu-dropdown .user-menu-head {
            padding: 12px 12px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 6px;
        }

        .user-menu-dropdown .user-menu-head strong {
            display: block;
            font-size: 13.5px;
            color: var(--text-main);
        }

        .user-menu-dropdown .user-menu-head span {
            display: block;
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .user-menu-dropdown a.user-menu-item,
        .user-menu-dropdown button.user-menu-item {
            display: flex;
            align-items: center;
            gap: 11px;
            width: 100%;
            padding: 10px 12px;
            border-radius: 9px;
            border: none;
            background: none;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            text-decoration: none;
            cursor: pointer;
            text-align: left;
            transition: background 0.15s;
            font-family: 'Inter', sans-serif;
        }

        .user-menu-dropdown a.user-menu-item i,
        .user-menu-dropdown button.user-menu-item i {
            width: 17px;
            text-align: center;
            color: var(--color-guindo);
        }

        .user-menu-dropdown a.user-menu-item:hover,
        .user-menu-dropdown button.user-menu-item:hover {
            background: var(--input-bg);
        }

        .user-menu-dropdown .user-menu-divider {
            height: 1px;
            background: var(--border-color);
            margin: 6px 4px;
        }

        .user-menu-dropdown button.user-menu-item.user-menu-logout { color: #ef4444; }
        .user-menu-dropdown button.user-menu-item.user-menu-logout i { color: #ef4444; }

        /* ============================
           CONTENIDO DE LA PÁGINA
           ============================ */
        .content-container {
            padding: 28px 30px;
            flex-grow: 1;
        }

        /* CARDS */
        .card {
            background-color: var(--bg-card);
            border-radius: 14px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .card-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--color-guindo);
        }

        body.dark-mode .card-header-custom { border-color: var(--color-dorado); }

        .card-title-custom {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--color-guindo);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        body.dark-mode .card-title-custom { color: var(--color-dorado); }

        /* BOTONES DAC */
        .btn-dac {
            background: linear-gradient(135deg, var(--color-guindo), var(--color-guindo-dark));
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(107,12,36,0.3);
        }

        .btn-dac:hover {
            background: linear-gradient(135deg, var(--color-guindo-light), var(--color-guindo));
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107,12,36,0.4);
        }

        .btn-dac-gold {
            background: linear-gradient(135deg, var(--color-dorado), var(--color-dorado-dark));
            color: #1e293b;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13.5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(212,175,55,0.3);
        }

        .btn-dac-gold:hover {
            transform: translateY(-1px);
            color: #1e293b;
            box-shadow: 0 4px 12px rgba(212,175,55,0.4);
        }

        .btn-dac-outline {
            background: transparent;
            color: var(--color-guindo);
            border: 2px solid var(--color-guindo);
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-dac-outline:hover {
            background: var(--color-guindo);
            color: #fff;
        }

        .btn-danger {
            background: transparent;
            color: #ef4444;
            border: 1px solid #fca5a5;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-danger:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

        .btn-sm {
            padding: 6px 12px !important;
            font-size: 12px !important;
        }

        /* TABLAS ELEGANTES */
        .table-responsive { width: 100%; overflow-x: auto; }

        table.dac-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        table.dac-table thead th {
            background: linear-gradient(135deg, var(--color-plomo), var(--color-plomo-light));
            color: #ffffff;
            padding: 13px 16px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }

        table.dac-table td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13.5px;
            color: var(--text-main);
            vertical-align: middle;
        }

        table.dac-table tr:last-child td { border-bottom: none; }
        table.dac-table tbody tr:hover { background-color: rgba(107,12,36,0.04); }
        body.dark-mode table.dac-table tbody tr:hover { background-color: rgba(212,175,55,0.05); }

        /* BADGES / ETIQUETAS */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .badge-success   { background: #dcfce7; color: #15803d; }
        .badge-warning   { background: #fef3c7; color: #92400e; }
        .badge-danger    { background: #fee2e2; color: #b91c1c; }
        .badge-info      { background: #dbeafe; color: #1d4ed8; }
        .badge-guindo    { background: rgba(107,12,36,0.1); color: var(--color-guindo); }
        .badge-dorado    { background: rgba(212,175,55,0.15); color: #92400e; }
        .badge-secondary { background: #f1f5f9; color: #475569; }

        /* ALERTAS */
        .alert-custom {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-left: 4px solid #22c55e;
            color: #14532d;
        }

        .alert-error {
            background: linear-gradient(135deg, #fff1f2, #fee2e2);
            border-left: 4px solid #ef4444;
            color: #7f1d1d;
        }

        .alert-info {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-left: 4px solid #3b82f6;
            color: #1e3a5f;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border-left: 4px solid #f59e0b;
            color: #78350f;
        }

        /* FORMULARIOS */
        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 7px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border-color);
            border-radius: 9px;
            font-size: 13.5px;
            background-color: var(--input-bg);
            color: var(--text-main);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-guindo);
            box-shadow: 0 0 0 3px rgba(107,12,36,0.12);
        }

        body.dark-mode .form-control, body.dark-mode .form-select {
            background-color: var(--input-bg);
            border-color: var(--border-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        /* ESTADÍSTICAS / KPI CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: 14px;
            padding: 22px 24px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon.guindo  { background: rgba(107,12,36,0.1); color: var(--color-guindo); }
        .stat-icon.dorado  { background: rgba(212,175,55,0.15); color: var(--color-dorado-dark); }
        .stat-icon.verde   { background: rgba(34,197,94,0.1); color: #15803d; }
        .stat-icon.rojo    { background: rgba(239,68,68,0.1); color: #b91c1c; }
        .stat-icon.azul    { background: rgba(59,130,246,0.1); color: #1d4ed8; }
        .stat-icon.plomo   { background: rgba(100,116,139,0.1); color: #475569; }

        .stat-body { flex-grow: 1; }

        .stat-value {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
        }

        .stat-label {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 500;
        }

        .stat-trend {
            font-size: 11px;
            font-weight: 700;
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .stat-trend.up   { color: #15803d; }
        .stat-trend.down { color: #b91c1c; }

        /* Línea decorativa inferior en stat-cards */
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-guindo), var(--color-dorado));
            border-radius: 0 0 14px 14px;
        }

        /* AVATAR USUARIO EN TABLA */
        .user-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--color-dorado);
        }

        /* PAGINACIÓN */
        .pagination-container {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination-container .page-link {
            padding: 8px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .pagination-container .page-link:hover,
        .pagination-container .page-link.active {
            background: var(--color-guindo);
            color: #fff;
            border-color: var(--color-guindo);
        }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show { display: flex; }

        .modal-box {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 30px;
            width: 90%;
            max-width: 520px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            border-top: 4px solid var(--color-dorado);
            animation: modalIn 0.25s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95) translateY(-10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* FOOTER */
        footer.main-footer {
            padding: 18px 30px;
            text-align: center;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 12.5px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            aside.sidebar { transform: translateX(-265px); }
            main.main-content { margin-left: 0; }
        }

        /* SCROLLBAR PERSONALIZADA */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--color-guindo); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--color-guindo-dark); }

        /* ANIMACIÓN FADE IN para el contenido */
        .content-container { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Skeleton loader (carga) */
        .skeleton {
            background: linear-gradient(90deg, var(--border-color) 25%, var(--bg-body) 50%, var(--border-color) 75%);
            background-size: 200% 100%;
            animation: skeleton 1.5s infinite;
            border-radius: 6px;
        }
        @keyframes skeleton {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ============================
           @yield('styles') extra
           ============================ */
        @yield('styles')
    </style>
</head>
<body>
    <script>
        // Aplicar el tema guardado ANTES de que el navegador pinte (evita parpadeo)
        (function () {
            var t = localStorage.getItem('dac-theme') || 'light';
            if (t === 'dark') document.body.classList.add('dark-mode');
            if (t === 'sepia') document.body.classList.add('theme-sepia');
        })();
    </script>

    <!-- =====================================================
         SIDEBAR DE NAVEGACIÓN INSTITUCIONAL DAC
         ===================================================== -->
    <aside class="sidebar" id="sidebar">

        <!-- CABECERA CON BANNER Y LOGO -->
        <div class="sidebar-header">
            {{-- Banner del proyecto en el sidebar --}}
            <img src="{{ asset('images/banner.png') }}"
                 alt="Banner DAC"
                 class="sidebar-banner"
                 onerror="this.style.display='none'">

            <!-- Overlay con logo y nombre del sistema -->
            <div class="sidebar-brand-overlay">
                <div class="sidebar-logo-row">
                    <div class="logo-circle">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Logo DAC"
                             onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-fingerprint\' style=\'font-size:18px; color:var(--color-guindo); padding:6px;\'></i>'">
                    </div>
                    <div>
                        <div class="brand-title">SISTEMA BIOMÉTRICO</div>
                        <div class="brand-subtitle">Programa DAC · Tiquipaya</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MENÚ DE NAVEGACIÓN SEGÚN ROL -->
<nav class="sidebar-nav">
    <div class="sidebar-group-label">Principal</div>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" id="nav-profile">
        <i class="fa-solid fa-circle-user"></i>
        <span>Mi Perfil</span>
    </a>

    @if(auth()->check())
        @php $userRole = auth()->user(); @endphp
        @if($userRole->hasRole('Administrador'))
            {{-- MENÚ ROL ADMIN: ACCESO TOTAL --}}
            <div class="sidebar-group-label">Gestión Académica</div>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}" id="nav-users">
                <i class="fa-solid fa-user-graduate"></i>
                <span>Docentes y Usuarios</span>
            </a>
            <a href="{{ route('schedules.index') }}" class="{{ request()->routeIs('schedules.*') ? 'active' : '' }}" id="nav-schedules">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Horarios y Aulas</span>
            </a>

            <div class="sidebar-group-label">Control de Asistencia</div>
            <a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'active' : '' }}" id="nav-attendances">
                <i class="fa-solid fa-clipboard-user"></i>
                <span>Asistencias y Reportes</span>
            </a>
            <a href="{{ route('justifications.index') }}" class="{{ request()->routeIs('justifications.*') ? 'active' : '' }}" id="nav-justifications">
                <i class="fa-solid fa-file-signature"></i>
                <span>Justificaciones</span>
            </a>

            <div class="sidebar-group-label">Infraestructura</div>
            <a href="{{ route('devices.index') }}" class="{{ request()->routeIs('devices.*') ? 'active' : '' }}" id="nav-devices">
                <i class="fa-solid fa-microchip"></i>
                <span>Lectores ESP32</span>
            </a>

            <div class="sidebar-group-label">Módulos Avanzados</div>
            <a href="{{ route('biometria.index') }}" class="{{ request()->routeIs('biometria.*') ? 'active' : '' }}" id="nav-biometria">
                <i class="fa-solid fa-fingerprint"></i>
                <span>Enrolamiento Biométrico</span>
            </a>
            <a href="{{ route('monitoreo.index') }}" class="{{ request()->routeIs('monitoreo.*') ? 'active' : '' }}" id="nav-monitoreo">
                <i class="fa-solid fa-desktop"></i>
                <span>Centro de Monitoreo</span>
            </a>
            <a href="{{ route('memorandos.index') }}" class="{{ request()->routeIs('memorandos.*') ? 'active' : '' }}" id="nav-memorandos">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Memorandos</span>
            </a>

        @elseif($userRole->hasRole('Coordinador DAC'))
            {{-- MENÚ ROL COORDINADOR: Horarios, Justificaciones, Reportes (Sin usuarios ni dispositivos) --}}
            <div class="sidebar-group-label">Gestión Académica</div>
            <a href="{{ route('schedules.index') }}" class="{{ request()->routeIs('schedules.*') ? 'active' : '' }}" id="nav-schedules">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Horarios y Aulas</span>
            </a>

            <div class="sidebar-group-label">Control de Asistencia</div>
            <a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'active' : '' }}" id="nav-attendances">
                <i class="fa-solid fa-clipboard-user"></i>
                <span>Asistencias y Reportes</span>
            </a>
            <a href="{{ route('justifications.index') }}" class="{{ request()->routeIs('justifications.*') ? 'active' : '' }}" id="nav-justifications">
                <i class="fa-solid fa-file-signature"></i>
                <span>Justificaciones</span>
            </a>
            <a href="{{ route('memorandos.index') }}" class="{{ request()->routeIs('memorandos.*') ? 'active' : '' }}" id="nav-memorandos">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Memorandos</span>
            </a>

        @elseif($userRole->hasRole('Docente'))
            {{-- MENÚ ROL DOCENTE: Perfil, Mi Horario, Mis Asistencias, Solicitar Justificación --}}
            <div class="sidebar-group-label">Mi Espacio</div>
            <a href="{{ route('schedule.mine') }}" class="{{ request()->routeIs('schedule.mine') ? 'active' : '' }}" id="nav-schedule-mine">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Mi Horario</span>
            </a>
            <a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'active' : '' }}" id="nav-attendances">
                <i class="fa-solid fa-clipboard-user"></i>
                <span>Mis Asistencias</span>
            </a>
            <a href="{{ route('justifications.index') }}" class="{{ request()->routeIs('justifications.*') ? 'active' : '' }}" id="nav-justifications">
                <i class="fa-solid fa-file-signature"></i>
                <span>Solicitar Justificación</span>
            </a>
        @endif
    @endif
</nav>


        <!-- PIE DEL SIDEBAR (usuario logueado) -->
        <div class="sidebar-user-footer">
            <img src="{{ auth()->user()->foto_url ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name ?? 'U').'&background=6b0c24&color=ffffff&bold=true&size=80' }}"
                 alt="Avatar"
                 class="sidebar-user-avatar"
                 onerror="this.src='https://ui-avatars.com/api/?name=DAC&background=6b0c24&color=ffffff&bold=true'">
            <div>
                <div class="sidebar-user-name">{{ Str::limit(auth()->user()->name ?? 'Usuario', 22) }}</div>
                <div class="sidebar-user-role">{{ auth()->user()->roles->pluck('name')->first() ?? 'Docente' }}</div>
            </div>
        </div>

    </aside>

    <!-- =====================================================
         CONTENIDO PRINCIPAL
         ===================================================== -->
    <main class="main-content">

        <!-- BARRA SUPERIOR -->
        <header class="top-navbar">
            <div class="page-header-left">
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="navbar-actions">
                <!-- Selector de temas: claro / oscuro / sepia -->
                <div class="theme-selector" id="themeSelector">
                    <button class="theme-toggle-btn" id="themeToggle" title="Cambiar tema de color" aria-expanded="false">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <div class="theme-dropdown" id="themeDropdown">
                        <button type="button" class="theme-option" data-theme="light">
                            <i class="fa-solid fa-sun"></i> Claro
                        </button>
                        <button type="button" class="theme-option" data-theme="dark">
                            <i class="fa-solid fa-moon"></i> Oscuro
                        </button>
                        <button type="button" class="theme-option" data-theme="sepia">
                            <i class="fa-solid fa-book-open"></i> Sepia
                        </button>
                    </div>
                </div>

                <!-- Menú desplegable de cuenta (avatar + opciones) -->
                @include('components.dropdown-user')
            </div>
        </header>

        <!-- CONTENEDOR DE PÁGINA -->
        <div class="content-container">

            {{-- Alertas de sesión --}}
            @if(session('success'))
                <div class="alert-custom alert-success" id="alertSuccess">
                    <i class="fa-solid fa-circle-check fa-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-custom alert-error" id="alertError">
                    <i class="fa-solid fa-circle-xmark fa-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="alert-custom alert-info">
                    <i class="fa-solid fa-circle-info fa-lg"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert-custom alert-warning">
                    <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- FOOTER INSTITUCIONAL -->
        <footer class="main-footer">
            &copy; {{ date('Y') }}
            <strong style="color: var(--color-guindo);">PROYECTO-DAC</strong> ·
            Sistema Biométrico de Control de Asistencia ·
            Unidad Educativa Tiquipaya ·
            <span style="color: var(--color-dorado-dark);">Laravel 9 + ESP32</span>
        </footer>

    </main>

    <!-- =====================================================
         JAVASCRIPT GLOBAL (Modo Oscuro, Alertas)
         ===================================================== -->
    <script>
        // ─── Selector de Temas: Claro / Oscuro / Sepia ─────────────
        const themeSelector = document.getElementById('themeSelector');
        const themeToggle   = document.getElementById('themeToggle');
        const themeIcon     = document.getElementById('themeIcon');
        const body          = document.body;

        const THEME_ICONS = {
            light: 'fa-sun',
            dark:  'fa-moon',
            sepia: 'fa-book-open'
        };

        function applyTheme(theme) {
            body.classList.remove('dark-mode', 'theme-sepia');
            if (theme === 'dark')  body.classList.add('dark-mode');
            if (theme === 'sepia') body.classList.add('theme-sepia');

            themeIcon.className = 'fa-solid ' + (THEME_ICONS[theme] || 'fa-moon');
            localStorage.setItem('dac-theme', theme);

            document.querySelectorAll('.theme-option').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.theme === theme);
            });
        }

        // Tema guardado (con fallback a claro)
        let savedTheme = localStorage.getItem('dac-theme') || 'light';
        if (!['light', 'dark', 'sepia'].includes(savedTheme)) savedTheme = 'light';
        applyTheme(savedTheme);

        if (themeToggle && themeSelector) {
            themeToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = themeSelector.classList.toggle('open');
                themeToggle.setAttribute('aria-expanded', String(isOpen));
            });

            document.addEventListener('click', (e) => {
                if (themeSelector.classList.contains('open') && !themeSelector.contains(e.target)) {
                    themeSelector.classList.remove('open');
                    themeToggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && themeSelector.classList.contains('open')) {
                    themeSelector.classList.remove('open');
                    themeToggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.querySelectorAll('.theme-option').forEach(btn => {
                btn.addEventListener('click', () => {
                    applyTheme(btn.dataset.theme);
                    themeSelector.classList.remove('open');
                    themeToggle.setAttribute('aria-expanded', 'false');
                });
            });
        }

        // ─── Menú desplegable de cuenta ──────────────────────────
        const userMenu       = document.getElementById('userMenu');
        const userMenuBtn    = document.getElementById('userMenuBtn');

        if (userMenu && userMenuBtn) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = userMenu.classList.contains('open');
                userMenu.classList.toggle('open', !isOpen);
                userMenuBtn.setAttribute('aria-expanded', String(!isOpen));
            });

            // Cerrar al hacer clic fuera
            document.addEventListener('click', (e) => {
                if (userMenu.classList.contains('open') && !userMenu.contains(e.target)) {
                    userMenu.classList.remove('open');
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
            });

            // Cerrar con tecla Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && userMenu.classList.contains('open')) {
                    userMenu.classList.remove('open');
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // ─── Auto-ocultar alertas de sesión ───────────────────────
        setTimeout(() => {
            ['alertSuccess', 'alertError'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.transition = 'opacity 0.5s';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 500);
                }
            });
        }, 5000);
    </script>

    <!-- Bootstrap 5 Bundle (JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html>
