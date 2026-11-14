<!doctype html>
<html lang="id">
<head>
    @include('sampahrute.partials.head')
    <title>@yield('title', 'Dashboard') — SampahRute</title>
</head>
<body class="dashboard-body">
@php
    $role = $role ?? 'warga';
    $pending = collect($reports ?? [])->where('status', 'menunggu')->count();
    $myTasks = collect($locations ?? [])->where('officer_id', 'PTG-01')->whereIn('status', ['menunggu', 'diproses'])->count();
    $menu = match ($role) {
        'admin' => [
            ['📊', 'Dashboard', 'admin.dashboard', null],
            ['📋', 'Kelola Laporan', 'admin.reports', $pending ?: null],
            ['📍', 'Kelola Lokasi', 'admin.locations', null],
            ['📆', 'Kelola Jadwal', 'admin.schedule', null],
            ['🕒', 'Riwayat Pengangkutan', 'admin.history', null],
            ['👥', 'Pengguna', 'admin.users', null],
            ['🗺', 'Peta Monitoring', 'map', null],
        ],
        'petugas' => [
            ['📊', 'Dashboard', 'petugas.dashboard', null],
            ['🚚', 'Tugas Hari Ini', 'petugas.tasks', $myTasks ?: null],
            ['🕒', 'Riwayat Pengangkutan', 'petugas.history', null],
            ['📆', 'Jadwal', 'schedule', null],
            ['🗺', 'Peta Lokasi', 'map', null],
        ],
        default => [
            ['🏠', 'Dashboard', 'warga.dashboard', null],
            ['➕', 'Buat Laporan', 'warga.create', null],
            ['📋', 'Laporan Saya', 'warga.reports', null],
            ['📆', 'Jadwal', 'schedule', null],
            ['🗺', 'Peta Lokasi', 'map', null],
        ],
    };
@endphp
<a class="skip-link" href="#konten">Lewati ke konten</a>
<div class="dashboard-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand brand-light" href="{{ route('home') }}"><span class="brand-icon">◈</span><span>Sampah<span class="green-light">Rute</span></span></a>
        <div class="sidebar-caption">Menu {{ ucfirst($role) }}</div>
        <nav class="side-menu" aria-label="Menu utama">
            @foreach ($menu as [$icon, $label, $routeName, $badge])
                <a class="{{ request()->routeIs($routeName) || (str_ends_with($routeName, '.reports') && request()->routeIs('warga.show')) ? 'active' : '' }}" href="{{ route($routeName) }}" @if(request()->routeIs($routeName)) aria-current="page" @endif>
                    <span aria-hidden="true">{{ $icon }}</span>{{ $label }}
                    @if ($badge)<em class="menu-badge" title="{{ $badge }} perlu tindakan">{{ $badge }}</em>@endif
                </a>
            @endforeach
        </nav>
        <div class="sidebar-bottom">
            <form action="{{ route('demo.reset') }}" method="POST" data-confirm="Kembalikan semua data demo ke kondisi awal?">
                @csrf
                <button type="submit" class="side-logout">↺ Reset data demo</button>
            </form>
            <a href="{{ route('home') }}">← Kembali ke Beranda</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="side-logout">⎋ Keluar akun</button>
            </form>
        </div>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-toggle></div>

    <div class="dashboard-main">
        <header class="dashboard-top">
            <button class="dash-menu-button" type="button" data-sidebar-toggle aria-label="Buka menu" aria-controls="sidebar">☰</button>
            <div>
                <span class="small-muted">SampahRute / {{ ucfirst($role) }}</span>
                <strong>@yield('page-title', 'Dashboard')</strong>
            </div>

            <div class="top-actions">
                <span class="today-chip">📅 {{ now()->translatedFormat('l, d F Y') }}</span>

                <div class="notif" data-dropdown>
                    <button type="button" class="notif-button" data-dropdown-toggle aria-expanded="false" aria-label="Notifikasi ({{ count($notifications ?? []) }})">
                        🔔 @if (count($notifications ?? []))<em>{{ count($notifications) }}</em>@endif
                    </button>
                    <div class="notif-menu" data-dropdown-menu hidden>
                        <div class="notif-head"><strong>Notifikasi</strong><small>{{ count($notifications ?? []) }} terbaru</small></div>
                        @forelse ($notifications ?? [] as $n)
                            <a href="{{ $n['route'] }}" class="notif-item">
                                <span aria-hidden="true">{{ $n['icon'] }}</span>
                                <span><b>{{ $n['text'] }}</b><small>{{ $n['time'] }}</small></span>
                            </a>
                        @empty
                            <p class="notif-empty">Belum ada notifikasi baru.</p>
                        @endforelse
                    </div>
                </div>

                <div class="user-chip">
                    <span class="avatar">{{ collect(explode(' ', $me['name'] ?? ucfirst($role)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('') }}</span>
                    <span>{{ $me['name'] ?? ucfirst($role) }} <small>{{ $me['title'] ?? 'Mode demo' }}</small></span>
                </div>
            </div>
        </header>

        <main class="dashboard-content" id="konten">
            @yield('content')
        </main>
    </div>
</div>

@include('sampahrute.partials.flash')
@stack('dialogs')
</body>
</html>
