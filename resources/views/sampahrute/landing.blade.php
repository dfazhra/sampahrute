
@extends('sampahrute.layouts.public')

@section('title', 'Beranda')

@section('content')

{{-- HERO SECTION --}}
<section class="hero">
    <div class="hero-photo"></div>
    <div class="hero-shade"></div>

    <div class="container hero-content">
        <div class="eyebrow light">
            — BERSAMA UNTUK SURAKARTA YANG LEBIH BERSIH
        </div>

        <h1>
            Lingkungan Bersih,<br>
            <span>Pengangkutan Terpantau.</span>
        </h1>

        <p>
            Laporkan sampah yang belum diangkut,
            pantau jadwal pengangkutan, dan temukan
            titik lokasi sampah terdaftar di Surakarta.
        </p>

        <div class="button-row">
            <a href="{{ route('login') }}" class="btn btn-lime">
                ➤ &nbsp; Laporkan Sampah <span>→</span>
            </a>

            <a href="{{ route('map') }}" class="btn btn-outline">
                ⌖ &nbsp; Lihat Peta
            </a>
        </div>

        <div class="hero-meta">
            <span>✓ Pelaporan mudah</span>
            <span>✓ Informasi transparan</span>
            <span>✓ Berbasis lokasi</span>
        </div>
    </div>
</section>

{{-- TENTANG SAMPAHRUTE --}}
<section class="section" id="tentang">
    <div class="container about-grid">

        <div>
            <div class="eyebrow">
                — PLATFORM MONITORING LINGKUNGAN
            </div>

            <h2>Tentang <span class="accent">SampahRute</span></h2>

            <p class="section-lead">
                SampahRute merupakan prototipe aplikasi
                monitoring pengangkutan sampah untuk
                membantu warga, petugas, dan pengelola
                lingkungan di Surakarta.
            </p>

            <p>
                Dengan informasi lokasi, jadwal pengangkutan,
                dan laporan warga dalam satu sistem,
                tindak lanjut masalah sampah dapat
                dipantau lebih terstruktur.
            </p>

            <a href="#fitur" class="btn btn-primary">
                Pelajari Fitur →
            </a>
        </div>

        <div class="about-images">
            <img
                class="about-phone"
                src="{{ asset('assets/app-mobile.jpg') }}"
                alt="Ilustrasi aplikasi SampahRute"
            >

            <img
                src="{{ asset('assets/worker.jpg') }}"
                alt="Petugas pengangkut sampah"
            >

            <img
                src="{{ asset('assets/surakarta.jpg') }}"
                alt="Kawasan Surakarta"
            >
        </div>

    </div>
</section>


{{-- STATISTIK DEMO --}}
<section class="stats-strip">
    <div class="container stats-grid">

        <x-sr-stat
            icon="📍"
            value="5"
            label="Titik Lokasi Demo"
            note="Contoh data presentasi"
        />

        <x-sr-stat
            icon="📄"
            value="4"
            label="Laporan Demo"
            note="Laporan ilustratif"
        />

        <x-sr-stat
            icon="🚚"
            value="2"
            label="Lokasi Terlayani"
            note="Status dari data demo"
        />

        <x-sr-stat
            icon="👥"
            value="3"
            label="Peran Pengguna"
            note="Admin, Petugas, Warga"
        />

    </div>
</section>


{{-- FITUR UTAMA --}}
<section class="section feature-section" id="fitur">

    <div class="container">

        <div class="section-head">
            <div>
                <div class="eyebrow">— FITUR LENGKAP</div>
                <h2>Fitur Utama SampahRute</h2>
            </div>

            <a href="{{ route('login') }}" class="text-link">
                Mulai Jelajahi →
            </a>
        </div>

        <div class="feature-grid">

            @php
                $features = [
                    [
                        'icon' => '📱',
                        'title' => 'Pelaporan Warga',
                        'description' => 'Laporkan sampah yang belum diangkut dengan deskripsi, lokasi, dan foto.',
                        'route' => 'login',
                    ],
                    [
                        'icon' => '🚛',
                        'title' => 'Monitoring Pengangkutan',
                        'description' => 'Pantau status pekerjaan dan tindak lanjut pengangkutan.',
                        'route' => 'login',
                    ],
                    [
                        'icon' => '📊',
                        'title' => 'Dashboard Admin',
                        'description' => 'Kelola laporan, lokasi, dan jadwal.',
                        'route' => 'login',
                    ],
                    [
                        'icon' => '🗺',
                        'title' => 'Peta Surakarta',
                        'description' => 'Jelajahi peta interaktif dengan filter lokasi dan status.',
                        'route' => 'map',
                    ],
                    [
                        'icon' => '📅',
                        'title' => 'Jadwal Petugas',
                        'description' => 'Lihat jadwal pengangkutan menurut titik lokasi.',
                        'route' => 'schedule',
                    ],
                    [
                        'icon' => '🗑',
                        'title' => 'Daftar Lokasi Sampah',
                        'description' => 'Lihat daftar titik pengangkutan terdaftar.',
                        'route' => 'locations',
                    ],
                ];
            @endphp

            @foreach ($features as $feature)
                <
                    class="feature-card"
                    href="{{ route($feature['route']) }}"
                >
                    <span class="feature-art">
                        {{ $feature['icon'] }}
                    </span>

                    <h3>{{ $feature['title'] }}</h3>

                    <p>{{ $feature['description'] }}</p>

                    <span class="card-arrow">↗</span>
                </a>
            @endforeach

        </div>
    </div>
</section>


{{-- PETA SURAKARTA --}}
<section class="map-feature">

    <div class="container map-feature-grid">

        <div>
            <div class="eyebrow">— PETA INTERAKTIF</div>

            <h2>Lihat Titik Lokasi Sampah di Surakarta</h2>

            <p>
                Jelajahi peta untuk mengetahui titik
                pengangkutan, status pelayanan, dan
                jadwal di wilayah Surakarta.
                Titik saat ini merupakan data demo,
                bukan lokasi TPS terverifikasi.
            </p>

            <a href="{{ route('map') }}" class="btn btn-primary">
                Buka Peta Lengkap →
            </a>
        </div>

        <div class="map-preview-box">
            <div
                id="landing-map"
                class="leaflet-map"
                data-points='@json($points)'
                data-mode="preview"
            ></div>

            <span class="map-label-overlay">
                ● Lokasi terdaftar (demo)
            </span>
        </div>

    </div>
</section>


{{-- AJAKAN MENGGUNAKAN APLIKASI --}}
<section class="cta-section">

    <div class="container cta-inner">

        <div>
            <div class="eyebrow light">
                — SURAKARTA LEBIH BERSIH DIMULAI DARI KITA
            </div>

            <h2>
                Mari Berpartisipasi<br>
                Menjaga Surakarta Tetap Bersih
            </h2>

            <p>
                Laporkan masalah, pantau pengangkutan,
                dan dukung lingkungan yang sehat.
            </p>
        </div>

    </div>

</section>

@endsection
