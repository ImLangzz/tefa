<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin - KatalogTEFA')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="admin-body">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <span class="brand-icon">🏫</span>
            <span>Katalog<strong>TEFA</strong></span>
        </a>
        <p class="admin-sidebar-label">ADMINISTRATOR</p>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Ringkasan</a>
            <a href="{{ route('admin.reports.index') }}"
                class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Laporan & Moderasi</a>
            <a href="{{ route('admin.products.index') }}"
                class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Semua Produk</a>
        </nav>
        <div class="admin-sidebar-footer">
            <span>{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="admin-logout">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <span class="admin-eyebrow">PANEL ADMIN</span>
                <h1>@yield('heading', 'Administrasi Katalog TEFA')</h1>
            </div>
            <span class="admin-role-badge">Admin</span>
        </header>

        @if(session('success'))
            <div class="flash-success"><span>✅ {{ session('success') }}</span></div>
        @endif
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>

</html>
