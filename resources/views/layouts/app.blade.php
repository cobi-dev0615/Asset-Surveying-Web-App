<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SER Inventarios')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #f5f5f5;
            --surface: #ffffff;
            --text: #444444;
            --text-secondary: #6c757d;
            --text-light: #adb5bd;
            --border: #e4e4e4;
            --border-dark: #cecece;

            --primary: #778c85;
            --primary-hover: #657a73;
            --primary-light: #e8edeb;
            --success: #2bc381;
            --success-light: #d4f4e4;
            --warning: #d0d725;
            --warning-light: #f5f6d0;
            --danger: #af37ff;
            --danger-light: #f0deff;
            --info: #3787ff;
            --info-light: #dce8ff;

            --sidebar-width: 260px;
            --sidebar-bg: #4d504f;
            --sidebar-hover: rgba(255,255,255,0.08);
            --sidebar-active: rgba(255,255,255,0.15);
            --sidebar-text: rgba(255,255,255,0.65);
            --sidebar-text-active: #ffffff;
            --sidebar-border: rgba(255,255,255,0.08);
            --sidebar-gradient: linear-gradient(270deg, rgba(70,138,240,0.18), transparent);

            --radius: 4px;
            --radius-lg: 4px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow: 0 1px 3px rgba(0,0,0,0.12), 0 0 2px rgba(0,0,0,0.14);
            --shadow-lg: 0 10px 20px rgba(0,0,0,0.19), 0 -1px 6px rgba(0,0,0,0.13);
            --transition: all 0.2s ease;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.5;
            font-size: 0.875rem;
        }

        a { color: var(--primary); text-decoration: none; transition: var(--transition); }
        a:hover { color: var(--primary-hover); }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            background-image: var(--sidebar-gradient);
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--sidebar-border);
            background: rgba(0,0,0,0.15);
        }
        .sidebar-brand-icon {
            width: 38px; height: 38px;
            background: var(--primary);
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 1rem;
        }
        .sidebar-brand-text { color: #fff; font-size: 0.95rem; font-weight: 600; }
        .sidebar-brand-text small { display: block; color: var(--sidebar-text); font-size: 0.7rem; font-weight: 400; }

        .sidebar-section {
            padding: 1rem 1.25rem 0.4rem;
            font-size: 0.65rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.1em; color: rgba(255,255,255,0.35);
        }

        .sidebar-nav { flex: 1; padding: 0.5rem 0; overflow-y: auto; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.55rem 1.25rem; margin: 0;
            color: var(--sidebar-text);
            font-size: 0.82rem; transition: var(--transition);
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }
        .sidebar-nav a.active { background: var(--sidebar-active); color: var(--sidebar-text-active); border-left-color: var(--primary); font-weight: 500; }
        .sidebar-nav a svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.6; }
        .sidebar-nav a.active svg { opacity: 1; }

        .sidebar-sub { padding-left: 1rem; }
        .sidebar-sub a { font-size: 0.78rem; padding: 0.4rem 1.25rem 0.4rem 2.5rem; border-left: none; }
        .sidebar-sub a::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: rgba(255,255,255,0.25); margin-right: 0.5rem; flex-shrink: 0; }
        .sidebar-sub a.active::before { background: var(--primary); }

        .sidebar-toggle-btn {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.55rem 1.25rem; margin: 0;
            color: var(--sidebar-text);
            font-size: 0.82rem; transition: var(--transition);
            border-left: 3px solid transparent;
            cursor: pointer; width: 100%; background: none; border: none; border-left: 3px solid transparent;
            text-align: left;
        }
        .sidebar-toggle-btn:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }
        .sidebar-toggle-btn svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.6; }
        .sidebar-toggle-btn .chevron { margin-left: auto; transition: transform 0.2s; width: 14px; height: 14px; }
        .sidebar-toggle-btn.open .chevron { transform: rotate(90deg); }
        .sidebar-collapsible { display: none; }
        .sidebar-collapsible.open { display: block; }

        .sidebar-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--sidebar-border);
            color: rgba(255,255,255,0.35);
            font-size: 0.7rem;
            background: rgba(0,0,0,0.1);
        }

        /* ===== MAIN ===== */
        .main { margin-left: var(--sidebar-width); min-height: 100vh; }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            height: 56px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .topbar-left h1 { font-size: 1rem; font-weight: 600; color: var(--text); }
        .topbar-right { display: flex; align-items: center; gap: 0.75rem; }
        .topbar-user {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.3rem 0.5rem;
            border-radius: var(--radius);
            cursor: default;
        }
        .topbar-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.75rem;
        }
        .topbar-user-info { line-height: 1.2; }
        .topbar-user-info .name { font-size: 0.82rem; font-weight: 500; }
        .topbar-user-info .role { font-size: 0.68rem; color: var(--text-secondary); }

        .content { padding: 1.25rem 1.5rem 2rem; }

        /* ===== BREADCRUMB ===== */
        .breadcrumb {
            display: flex; align-items: center; gap: 0.35rem;
            margin-bottom: 1rem; font-size: 0.78rem; color: var(--text-secondary);
        }
        .breadcrumb a { color: var(--text-secondary); }
        .breadcrumb a:hover { color: var(--primary); }
        .breadcrumb-sep { color: var(--text-light); }

        /* ===== PAGE HEADER ===== */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
        .page-header h2 { font-size: 1.25rem; font-weight: 600; color: var(--text); }
        .page-header-actions { display: flex; gap: 0.5rem; }

        /* ===== PANELS (SmartAdmin style) ===== */
        .panel {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.25rem;
            border: 1px solid var(--border);
        }
        .panel-hdr {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            background: linear-gradient(to bottom, #fafafa, #f5f5f5);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .panel-hdr h2 { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; color: var(--text); margin: 0; }
        .panel-hdr .panel-toolbar { display: flex; gap: 0.5rem; align-items: center; }
        .panel-body { padding: 1.25rem; }
        .panel-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--border);
            background: #fafafa;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
        }

        /* Backwards compat with old card classes */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: 1.25rem; }
        .card-header {
            padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border);
            font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.02em;
            display: flex; align-items: center; justify-content: space-between;
            background: linear-gradient(to bottom, #fafafa, #f5f5f5);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .card-body { padding: 1.25rem; }
        .card-footer { padding: 0.75rem 1.25rem; border-top: 1px solid var(--border); background: #fafafa; border-radius: 0 0 var(--radius-lg) var(--radius-lg); }

        /* ===== STATS ===== */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); padding: 1.1rem 1.25rem;
            box-shadow: var(--shadow-sm); display: flex; align-items: flex-start; justify-content: space-between;
        }
        .stat-card .stat-info {}
        .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1; color: var(--text); }
        .stat-card .stat-label { color: var(--text-secondary); font-size: 0.75rem; margin-top: 0.25rem; }
        .stat-card .stat-icon {
            width: 42px; height: 42px; border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
        }
        .stat-card .stat-icon svg { width: 20px; height: 20px; }
        .stat-icon-blue { background: var(--info-light); color: var(--info); }
        .stat-icon-green { background: var(--success-light); color: var(--success); }
        .stat-icon-orange { background: var(--warning-light); color: #8a8b15; }
        .stat-icon-red { background: var(--danger-light); color: var(--danger); }
        .stat-icon-primary { background: var(--primary-light); color: var(--primary); }

        /* ===== TABLES (striped + bordered like SmartAdmin) ===== */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 0.65rem 0.85rem; text-align: left;
            font-size: 0.75rem; font-weight: 600;
            color: var(--text); background: #f8f8f8;
            border: 1px solid var(--border); white-space: nowrap;
        }
        tbody td {
            padding: 0.55rem 0.85rem; border: 1px solid var(--border);
            font-size: 0.82rem; vertical-align: middle;
        }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody tr:hover { background: #f0f4f2; }
        .table-empty { padding: 2.5rem; text-align: center; color: var(--text-secondary); }
        .table-empty svg { width: 40px; height: 40px; margin-bottom: 0.75rem; opacity: 0.3; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 0.85rem; border: 1px solid transparent;
            border-radius: var(--radius); font-size: 0.82rem; font-weight: 500;
            cursor: pointer; transition: var(--transition); line-height: 1.5;
            text-decoration: none;
        }
        .btn:hover { box-shadow: var(--shadow); }
        .btn:active { transform: translateY(0); }
        .btn svg { width: 14px; height: 14px; }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 2px 6px rgba(119,140,133,0.35); }
        .btn-primary:hover { background: var(--primary-hover); color: #fff; }
        .btn-success { background: var(--success); color: #fff; box-shadow: 0 2px 6px rgba(43,195,129,0.35); }
        .btn-success:hover { background: #25a96f; color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; box-shadow: 0 2px 6px rgba(175,55,255,0.35); }
        .btn-danger:hover { background: #9a2de6; color: #fff; }
        .btn-warning { background: var(--warning); color: #333; box-shadow: 0 2px 6px rgba(208,215,37,0.35); }
        .btn-info { background: var(--info); color: #fff; box-shadow: 0 2px 6px rgba(55,135,255,0.35); }
        .btn-info:hover { background: #2b75e6; color: #fff; }
        .btn-outline { background: var(--surface); border-color: var(--border-dark); color: var(--text); }
        .btn-outline:hover { background: var(--bg); color: var(--text); }
        .btn-ghost { background: transparent; color: var(--text-secondary); border: none; }
        .btn-ghost:hover { background: var(--bg); color: var(--text); box-shadow: none; }
        .btn-sm { padding: 0.3rem 0.55rem; font-size: 0.75rem; }
        .btn-sm svg { width: 13px; height: 13px; }
        .btn-lg { padding: 0.6rem 1.15rem; font-size: 0.92rem; }
        .btn-block { width: 100%; justify-content: center; }

        /* ===== FORMS ===== */
        .form-group { margin-bottom: 1.1rem; }
        .form-label { display: block; margin-bottom: 0.35rem; font-weight: 500; font-size: 0.82rem; color: var(--text); }
        .form-control {
            width: 100%; padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-dark); border-radius: var(--radius);
            font-size: 0.82rem; background: var(--surface);
            transition: var(--transition); line-height: 1.5;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(119,140,133,0.15); }
        .form-control::placeholder { color: var(--text-light); }
        select.form-control { appearance: auto; }
        textarea.form-control { resize: vertical; min-height: 80px; }
        .form-error { color: #dc3545; font-size: 0.78rem; margin-top: 0.2rem; }
        .form-hint { color: var(--text-secondary); font-size: 0.78rem; margin-top: 0.2rem; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .form-check { display: flex; align-items: center; gap: 0.5rem; }
        .form-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--primary); }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex; align-items: center; gap: 0.2rem;
            padding: 0.18rem 0.55rem; border-radius: 3px;
            font-size: 0.7rem; font-weight: 600; white-space: nowrap;
        }
        .badge-success { background: var(--success); color: #fff; }
        .badge-warning { background: var(--warning); color: #333; }
        .badge-danger { background: var(--danger); color: #fff; }
        .badge-info { background: var(--info); color: #fff; }
        .badge-primary { background: var(--primary); color: #fff; }
        .badge-gray { background: #e9ecef; color: var(--text-secondary); }

        /* ===== ALERTS ===== */
        .alert { padding: 0.75rem 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.82rem; display: flex; align-items: center; gap: 0.65rem; }
        .alert-success { background: var(--success-light); color: #1a7a4f; border: 1px solid #b5e6cc; }
        .alert-danger { background: #fce4ec; color: #b71c1c; border: 1px solid #f5c2c7; }
        .alert-warning { background: var(--warning-light); color: #666804; border: 1px solid #e8e99c; }
        .alert-info { background: var(--info-light); color: #1a4f9e; border: 1px solid #b6d4fe; }

        /* ===== MODAL ===== */
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; }
        .modal-backdrop.active { display: flex; }
        .modal { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .modal-header h3 { font-size: 1rem; font-weight: 600; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { padding: 0.75rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.5rem; }

        /* ===== PAGINATION ===== */
        .pagination { display: flex; align-items: center; justify-content: center; gap: 0.2rem; margin-top: 1rem; }
        .pagination a, .pagination span {
            padding: 0.35rem 0.65rem; border: 1px solid var(--border); border-radius: var(--radius);
            font-size: 0.8rem; color: var(--text-secondary); transition: var(--transition);
        }
        .pagination a:hover { background: var(--bg); color: var(--text); }
        .pagination .active { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* ===== SEARCH ===== */
        .search-box { position: relative; }
        .search-box input { padding-left: 2.1rem; }
        .search-box svg { position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--text-light); }

        /* ===== TOOLBAR ===== */
        .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
        .toolbar-left { display: flex; align-items: center; gap: 0.5rem; flex: 1; }
        .toolbar-right { display: flex; align-items: center; gap: 0.5rem; }

        /* ===== PROGRESS BAR ===== */
        .progress { height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
        .progress-bar { height: 100%; border-radius: 4px; transition: width 0.3s; background: var(--primary); color: #fff; font-size: 0.68rem; font-weight: 600; display: flex; align-items: center; justify-content: center; }
        .progress-bar-success { background: var(--success); }
        .progress-bar-info { background: var(--info); }
        .progress-bar-warning { background: var(--warning); }
        .progress-bar-danger { background: var(--danger); }
        .progress-bar-primary { background: var(--primary); }

        /* ===== TABS ===== */
        .nav-tabs { display: flex; border-bottom: 2px solid var(--border); margin-bottom: 1rem; gap: 0; }
        .nav-tabs a {
            padding: 0.6rem 1rem; font-size: 0.82rem; font-weight: 500;
            color: var(--text-secondary); border-bottom: 2px solid transparent;
            margin-bottom: -2px; transition: var(--transition);
        }
        .nav-tabs a:hover { color: var(--text); }
        .nav-tabs a.active { color: var(--primary); border-bottom-color: var(--primary); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .content { padding: 1rem; }
            .topbar { padding: 0 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="/img/logo-ser.png" alt="SER" style="height:40px; width:auto; object-fit:contain;">
            <div class="sidebar-brand-text">
                SER Inventarios
                <small>Plataforma de Administración</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            {{-- Tablero --}}
            <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Tablero
            </a>

            {{-- Activos --}}
            <div class="sidebar-section">Activos</div>
            <a href="/productos" class="{{ request()->is('productos*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                Catálogo de Activos
            </a>

            {{-- Reportes --}}
            <div class="sidebar-section">Reportes</div>
            <button class="sidebar-toggle-btn {{ request()->is('reportes*') ? 'open' : '' }}" onclick="toggleSubmenu(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Reportes
                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="sidebar-collapsible sidebar-sub {{ request()->is('reportes*') ? 'open' : '' }}">
                <a href="/reportes/conteo" class="{{ request()->is('reportes/conteo') ? 'active' : '' }}">Activos Encontrados</a>
                <a href="/reportes/no-encontrados" class="{{ request()->is('reportes/no-encontrados') ? 'active' : '' }}">Activos No Encontrados</a>
                <a href="/reportes/global" class="{{ request()->is('reportes/global') ? 'active' : '' }}">Reporte Global</a>
                <a href="/reportes/acumulado" class="{{ request()->is('reportes/acumulado') ? 'active' : '' }}">Reporte Acumulado</a>
            </div>

            {{-- Transferencias --}}
            <div class="sidebar-section">Transferencias</div>
            <button class="sidebar-toggle-btn {{ request()->is('transferencias*') || request()->is('traspasos*') ? 'open' : '' }}" onclick="toggleSubmenu(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                Transferencias
                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="sidebar-collapsible sidebar-sub {{ request()->is('transferencias*') || request()->is('traspasos*') ? 'open' : '' }}">
                <a href="/transferencias/nueva" class="{{ request()->is('transferencias/nueva') ? 'active' : '' }}">Nueva Solicitud</a>
                <a href="/transferencias/solicitadas" class="{{ request()->is('transferencias/solicitadas') ? 'active' : '' }}">Órdenes Solicitadas</a>
                <a href="/transferencias/recibidas" class="{{ request()->is('transferencias/recibidas') ? 'active' : '' }}">Órdenes Recibidas</a>
                <a href="/traspasos" class="{{ request()->is('traspasos*') ? 'active' : '' }}">Historial de Traspasos</a>
            </div>

            {{-- Inventario de Productos --}}
            <div class="sidebar-section">Inventario de Productos</div>
            <a href="/inventarios" class="{{ request()->is('inventarios*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                Sesiones de Inventario
            </a>

            {{-- Activo Fijo --}}
            <div class="sidebar-section">Activo Fijo</div>
            <a href="/activo-fijo" class="{{ request()->is('activo-fijo*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12.01"/></svg>
                Sesiones de Activo Fijo
            </a>

            {{-- Administración --}}
            <div class="sidebar-section">Administración</div>
            <button class="sidebar-toggle-btn {{ request()->is('empresas*') || request()->is('sucursales*') ? 'open' : '' }}" onclick="toggleSubmenu(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14m6-14v14m6-14v14m6-14v14M6 3h12l3 4H3l3-4z"/></svg>
                Empresas
                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <div class="sidebar-collapsible sidebar-sub {{ request()->is('empresas*') || request()->is('sucursales*') ? 'open' : '' }}">
                <a href="/empresas" class="{{ request()->is('empresas*') ? 'active' : '' }}">Catálogo de Empresas</a>
                <a href="/sucursales" class="{{ request()->is('sucursales*') ? 'active' : '' }}">Catálogo de Sucursales</a>
            </div>

            <a href="/usuarios" class="{{ request()->is('usuarios*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Usuarios
            </a>
        </nav>

        <div class="sidebar-footer">
            SER Inventarios v1.0.0
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn btn-ghost" onclick="document.getElementById('sidebar').classList.toggle('open')" style="display:none;" id="menu-toggle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1>@yield('title')</h1>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->nombres, 0, 2)) }}</div>
                    <div class="topbar-user-info">
                        <div class="name">{{ Auth::user()->nombres }}</div>
                        <div class="role">{{ Auth::user()->rol->nombre }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    @stack('scripts')
    <script>
        if (window.innerWidth <= 768) document.getElementById('menu-toggle')?.style.setProperty('display', 'flex');
        function toggleSubmenu(btn) {
            btn.classList.toggle('open');
            const sub = btn.nextElementSibling;
            if (sub) sub.classList.toggle('open');
        }
    </script>
</body>
</html>
