<!doctype html>
<html lang="id">
<head>
    @include('sampahrute.partials.head')
    <title>@yield('title', 'SampahRute') — SampahRute</title>
    <meta name="description" content="SampahRute - pelaporan dan monitoring pengangkutan sampah Kota Surakarta.">
</head>
<body>
<a class="skip-link" href="#konten">Lewati ke konten</a>
<div class="topbar">
    <div class="container topbar-inner">
        <span>📍 Melayani 5 kecamatan di Kota Surakarta</span>
        <span>🕒 Pengangkutan setiap hari, 05.30–11.00 WIB</span>
        <span class="topbar-end">♻ Prototipe akademik Kelompok 7</span>
    </div>
</div>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="{{ route('home') }}"><span class="brand-icon">◈</span><span>Sampah<span class="accent">Rute</span></span></a>
        <button class="mobile-toggle" type="button" data-menu-toggle aria-label="Buka menu">☰</button>
        <nav class="nav-links" data-nav>
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('home') }}#tentang">Tentang</a>
            <a href="{{ route('home') }}#alur">Cara Kerja</a>
            <a href="{{ route('map') }}" class="{{ request()->routeIs('map') ? 'is-active' : '' }}">Peta</a>
            <a href="{{ route('schedule') }}" class="{{ request()->routeIs('schedule') ? 'is-active' : '' }}">Jadwal</a>
            <a href="{{ route('home') }}#faq">FAQ</a>
        </nav>
        @if ($sessionRole ?? null)
            <a class="btn nav-cta nav-login" href="{{ route($sessionRole.'.dashboard') }}">Buka Dashboard</a>
        @else
            <a class="btn nav-cta nav-login" href="{{ route('login') }}">Masuk / Lapor</a>
        @endif
    </div>
</header>

<main id="konten">
    @yield('content')
</main>

<footer class="footer" id="kontak">
    <div class="container footer-grid">
        <div>
            <a class="brand" href="{{ route('home') }}"><span class="brand-icon">◈</span><span>Sampah<span class="accent">Rute</span></span></a>
            <p>Bersama untuk Surakarta yang lebih bersih, sehat, dan nyaman.</p>
        </div>
        <div>
            <h4>Navigasi</h4>
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('map') }}">Peta</a>
            <a href="{{ route('locations') }}">Daftar Lokasi</a>
            <a href="{{ route('schedule') }}">Jadwal</a>
        </div>
        <div>
            <h4>Informasi</h4>
            <p>Proyek pembelajaran Laravel 11</p>
            <p>Surakarta, Jawa Tengah</p>
            <p>Data lokasi dan laporan adalah contoh</p>
        </div>
        <div>
            <h4>Akses Aplikasi</h4>
            <a href="{{ route('login') }}">Login Admin</a>
            <a href="{{ route('login') }}">Login Petugas</a>
            <a href="{{ route('login') }}">Login Warga</a>
        </div>
    </div>
    <div class="container footer-bottom">© {{ date('Y') }} SampahRute · Prototipe akademik Kelompok 7 <span>Desain frontend Laravel + Blade</span></div>
</footer>

@include('sampahrute.partials.flash')
@stack('dialogs')
</body>
</html>
