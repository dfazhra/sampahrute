@extends('sampahrute.layouts.public')

@section('title', 'Beranda')

@section('content')
@php
    $locCol = collect($locations);
    $repCol = collect($reports);
    $scheduledToday = $locCol->where('scheduled_today', true);
    $doneToday = $scheduledToday->where('status', 'selesai')->count();
@endphp

{{-- HERO --}}
<section class="lp-hero">
    <img class="lp-hero-img" src="{{ asset('assets/hero-collection.jpg') }}" alt="Petugas mengangkut sampah dengan truk di Surakarta">
    <div class="lp-hero-shade"></div>
    <div class="container lp-hero-content">
        <p class="lp-kicker">Monitoring pengangkutan sampah Surakarta</p>
        <h1>Sampah Terangkut.<br>Laporan Terpantau.<br><span>Kota Lebih Bersih.</span></h1>
        <p class="lp-lead">Laporkan sampah yang belum diangkut, cek jadwal truk di titik terdekat, dan ikuti tindak lanjutnya sampai selesai.</p>
        <div class="button-row">
            <a href="{{ route('login') }}" class="btn lp-btn-main">Laporkan sampah <span aria-hidden="true">→</span></a>
            <a href="{{ route('map') }}" class="btn btn-outline">Lihat peta lokasi</a>
        </div>
    </div>
</section>

{{-- KEUNGGULAN --}}
<section class="lp-features">
    <div class="container lp-features-grid">
        @foreach ([
            ['📝', 'Lapor dalam 1 menit', 'Pilih titik di peta, tulis kondisinya, lampirkan foto.'],
            ['📡', 'Status langsung terlihat', 'Petugas mencatat jam angkut, status di peta ikut berubah.'],
            ['📅', 'Jadwal yang jelas', 'Hari dan jam angkut tiap titik bisa dicek kapan saja.'],
            ['🛡', 'Tindak lanjut tercatat', 'Setiap laporan punya riwayat dari dikirim sampai selesai.'],
        ] as [$icon, $title, $text])
            <div class="lp-feature">
                <span class="lp-feature-icon" aria-hidden="true">{{ $icon }}</span>
                <div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $text }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- TENTANG --}}
<section class="section lp-about" id="tentang">
    <div class="container lp-about-grid">
        <div>
            <p class="lp-label">Tentang SampahRute</p>
            <h2>Satu tempat untuk warga, petugas, dan pengelola</h2>
            <p class="lp-text">
                SampahRute adalah prototipe sistem monitoring pengangkutan sampah untuk Kota Surakarta.
                Pengelola memastikan setiap titik terlayani sesuai jadwal, petugas mencatat pengangkutan
                dari lapangan, dan warga bisa melapor bila sampah belum diangkut.
            </p>
            <a href="#alur" class="btn btn-primary lp-pill">Lihat cara kerjanya <span aria-hidden="true">→</span></a>

            <div class="lp-roles">
                <div class="lp-role-avatars" aria-hidden="true">
                    <span>🛠</span><span>🚚</span><span>🏠</span>
                </div>
                <p><strong>3 peran pengguna</strong>Admin, petugas, dan warga</p>
            </div>
        </div>

        <div class="lp-collage">
            <figure class="lp-collage-main">
                <img src="{{ asset('assets/worker.jpg') }}" alt="Petugas mengangkut sampah dari bak">
                <figcaption class="lp-badge">
                    <span aria-hidden="true">📍</span>
                    <strong>{{ $locCol->count() }}</strong>
                    <small>titik pengangkutan terpantau</small>
                </figcaption>
            </figure>
            <img src="{{ asset('assets/app-mobile.jpg') }}" alt="Tampilan aplikasi SampahRute di ponsel">
            <img src="{{ asset('assets/surakarta.jpg') }}" alt="Kawasan Kota Surakarta">
        </div>
    </div>
</section>

{{-- STATISTIK --}}
<section class="lp-stats">
    <div class="container lp-stats-grid">
        @foreach ([
            ['📍', $locCol->count(), 'Titik pengangkutan', $locCol->pluck('district')->unique()->count().' kecamatan'],
            ['🚛', $doneToday.'/'.$scheduledToday->count(), 'Diangkut hari ini', 'Diperbarui petugas'],
            ['📄', $repCol->count(), 'Laporan warga', $repCol->where('status', 'selesai')->count().' sudah selesai'],
            ['🕒', '05.30', 'Angkut dimulai', 'Setiap hari'],
        ] as [$icon, $value, $label, $note])
            <div class="lp-stat">
                <span class="lp-stat-icon" aria-hidden="true">{{ $icon }}</span>
                <div>
                    <strong>{{ $value }}</strong>
                    <span>{{ $label }}</span>
                    <small>{{ $note }}</small>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- FITUR --}}
<section class="lp-services" id="fitur">
    <div class="container">
        <div class="lp-services-head">
            <div>
                <p class="lp-label light">Fitur utama</p>
                <h2>Semua yang dibutuhkan untuk lingkungan bersih</h2>
                <p>Dari laporan warga sampai catatan pengangkutan petugas, semuanya terhubung dalam satu sistem.</p>
            </div>
            <a href="{{ route('login') }}" class="btn lp-btn-main">Coba aplikasinya</a>
        </div>

        <div class="lp-cards">
            @foreach ([
                ['app-mobile.jpg', '📝', 'Pelaporan Warga', 'Laporkan sampah yang belum diangkut langsung dari ponsel.', ['Pilih lokasi dari peta', 'Lampirkan foto', 'Pantau statusnya'], route('login')],
                ['hero-collection.jpg', '🚛', 'Monitoring Angkut', 'Petugas mencatat jam dan bukti pengangkutan di setiap titik.', ['Rute harian petugas', 'Foto bukti angkut', 'Riwayat 14 hari'], route('login')],
                ['surakarta.jpg', '🗺', 'Peta Surakarta', 'Lihat semua titik pengangkutan dan statusnya hari ini.', ['Warna sesuai status', 'Cari per kecamatan', 'Detail tiap titik'], route('map')],
                ['worker.jpg', '📅', 'Jadwal Petugas', 'Jadwal mingguan setiap titik beserta petugasnya.', ['Hari dan jam angkut', 'Petugas per wilayah', 'Terbuka untuk warga'], route('schedule')],
            ] as [$img, $icon, $title, $text, $bullets, $url])
                <a class="lp-card" href="{{ $url }}">
                    <div class="lp-card-img">
                        <img src="{{ asset('assets/'.$img) }}" alt="" loading="lazy">
                        <span class="lp-card-icon" aria-hidden="true">{{ $icon }}</span>
                    </div>
                    <div class="lp-card-body">
                        <h3>{{ $title }}</h3>
                        <p>{{ $text }}</p>
                        <ul>
                            @foreach ($bullets as $pt)
                                <li>{{ $pt }}</li>
                            @endforeach
                        </ul>
                        <span class="lp-card-arrow" aria-hidden="true">→</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- CARA KERJA --}}
<section class="section how-section" id="alur">
    <div class="container">
        <p class="lp-label">Cara kerja</p>
        <h2 class="lp-h2">Dari laporan sampai sampah terangkut</h2>
        <ol class="how-steps">
            <li><span class="how-num">1</span><h3>Warga melapor</h3><p>Pilih titik di peta, jelaskan kondisinya, dan lampirkan foto bila ada.</p></li>
            <li><span class="how-num">2</span><h3>Admin memverifikasi</h3><p>Laporan dicek lalu ditugaskan ke petugas yang memegang wilayah tersebut.</p></li>
            <li><span class="how-num">3</span><h3>Petugas mengangkut</h3><p>Petugas mencatat jam angkut dan foto bukti, status langsung berubah.</p></li>
            <li><span class="how-num">4</span><h3>Warga memantau</h3><p>Perkembangan laporan terlihat di akun warga sampai selesai.</p></li>
        </ol>
    </div>
</section>

{{-- PETA --}}
<section class="map-feature">
    <div class="container map-feature-grid">
        <div>
            <p class="lp-label">Peta interaktif</p>
            <h2>Cek titik sampah di sekitar Anda</h2>
            <p>Warna penanda menunjukkan status pengangkutan hari ini. Klik penanda untuk melihat jadwal dan petugasnya.</p>
            <a href="{{ route('map') }}" class="btn btn-primary lp-pill">Buka peta lengkap <span aria-hidden="true">→</span></a>
        </div>
        <div class="map-preview-box">
            <div id="landing-map" class="leaflet-map" data-points='@json($points)' data-mode="preview"></div>
            @include('sampahrute.partials.map-legend')
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="section faq-section" id="faq">
    <div class="container faq-grid">
        <div>
            <p class="lp-label">Pertanyaan umum</p>
            <h2>Yang sering ditanyakan</h2>
            <p class="section-lead">Belum menemukan jawabannya? Hubungi pengelola lingkungan di wilayah Anda.</p>
        </div>
        <div class="faq-list">
            <details open><summary>Siapa yang bisa membuat laporan?</summary><p>Semua warga yang punya akun SampahRute. Masuk sebagai warga, lalu pilih menu Buat Laporan.</p></details>
            <details><summary>Berapa lama laporan ditindaklanjuti?</summary><p>Admin memverifikasi laporan pada jam kerja, lalu menugaskan petugas wilayah. Statusnya bisa dipantau di halaman Laporan Saya.</p></details>
            <details><summary>Apa arti warna penanda di peta?</summary><p>Merah berarti belum diangkut hari ini, kuning sedang diangkut, hijau sudah diangkut, dan abu-abu tidak ada jadwal hari ini.</p></details>
            <details><summary>Apakah lokasi di peta adalah TPS resmi?</summary><p>Belum. Ini prototipe akademik, sehingga titik dan jadwal adalah data contoh.</p></details>
        </div>
    </div>
</section>

{{-- AJAKAN --}}
<section class="lp-cta">
    <div class="container lp-cta-inner">
        <span class="lp-cta-icon" aria-hidden="true">♻</span>
        <div>
            <h2>Lingkungan bersih dimulai dari satu laporan.</h2>
            <p>Lihat sampah menumpuk? Laporkan sekarang, petugas akan menindaklanjuti.</p>
        </div>
        <a href="{{ route('login') }}" class="btn lp-btn-main">Laporkan sampah <span aria-hidden="true">→</span></a>
    </div>
</section>
@endsection
