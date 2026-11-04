<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title','SampahRute') — SampahRute</title><meta name="description" content="SampahRute - pelaporan dan monitoring pengangkutan sampah Kota Surakarta.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="{{ asset('css/sampahrute.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
<script src="{{ asset('js/sampahrute.js') }}" defer></script>
</head><body>
<header class="site-header"><div class="container nav-wrap"><a class="brand" href="{{ route('home') }}"><span class="brand-icon">◈</span><span>Sampah<span class="accent">Rute</span></span></a><button class="mobile-toggle" type="button" data-menu-toggle aria-label="Buka menu">☰</button>
<nav class="nav-links" data-nav><a href="{{ route('home') }}">Beranda</a><a href="{{ route('home') }}#tentang">Tentang</a><a href="{{ route('home') }}#fitur">Fitur</a><a href="{{ route('map') }}">Peta</a><a href="{{ route('home') }}#kontak">Kontak</a></nav><a class="btn btn-primary nav-login" href="{{ route('login') }}">♙ &nbsp; Login</a></div></header>
@if(session('success'))<div class="container"><div class="alert success">{{ session('success') }}</div></div>@endif
@yield('content')
<footer class="footer" id="kontak"><div class="container footer-grid"><div><a class="brand" href="{{ route('home') }}"><span class="brand-icon">◈</span><span>Sampah<span class="accent">Rute</span></span></a><p>Bersama untuk Surakarta yang lebih bersih, sehat, dan nyaman.</p></div><div><h4>Navigasi</h4><a href="{{ route('home') }}">Beranda</a><a href="{{ route('home') }}#tentang">Tentang</a><a href="{{ route('home') }}#fitur">Fitur</a><a href="{{ route('map') }}">Peta</a></div><div><h4>Informasi</h4><p>Proyek pembelajaran Laravel 11</p><p>Surakarta, Jawa Tengah</p><p>Kontak layanan: belum tersedia</p></div><div><h4>Akses Aplikasi</h4><a href="{{ route('login') }}">Login Admin</a><a href="{{ route('login') }}">Login Petugas</a><a href="{{ route('login') }}">Login Warga</a></div></div><div class="container footer-bottom">© {{ date('Y') }} SampahRute · Prototipe akademik Kelompok 7 <span>Desain frontend Laravel + Blade</span></div></footer>
</body></html>
