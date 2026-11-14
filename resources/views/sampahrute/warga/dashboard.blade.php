@extends('sampahrute.layouts.dashboard')
@section('title', 'Dashboard Warga')
@section('page-title', 'Dashboard Warga')

@section('content')
@php
    $mine = collect($myReports);
    $nearby = collect($locations)->where('district', 'Banjarsari');
    $active = $mine->whereIn('status', ['menunggu', 'diverifikasi', 'diproses']);
@endphp

<div class="welcome-banner">
    <div>
        <span>Portal warga</span>
        <h1>Lingkungan bersih dimulai dari kita 🌿</h1>
        <p>Lihat sampah menumpuk atau tidak diangkut? Laporkan dalam satu menit dan pantau tindak lanjutnya di sini.</p>
    </div>
    <a href="{{ route('warga.create') }}" class="btn btn-lime">＋ Buat laporan</a>
</div>

<div class="dashboard-stats">
    <x-sr-stat icon="📄" :value="$mine->count()" label="Laporan saya" />
    <x-sr-stat icon="⏳" :value="$mine->whereIn('status', ['menunggu', 'diverifikasi'])->count()" label="Menunggu tindak lanjut" />
    <x-sr-stat icon="🚛" :value="$mine->where('status', 'diproses')->count()" label="Sedang ditangani" />
    <x-sr-stat icon="✅" :value="$mine->where('status', 'selesai')->count()" label="Selesai" />
</div>

<div class="dash-two-cols">
    <section class="panel">
        <div class="panel-head"><h3>Laporan aktif</h3><a class="text-link" href="{{ route('warga.reports') }}">Semua laporan</a></div>
        @forelse ($active as $r)
            <a class="report-line link-line" href="{{ route('warga.show', $r['id']) }}">
                <div>
                    <strong>{{ $r['title'] }}</strong>
                    <small>{{ $r['id'] }} · {{ $r['location'] }} · {{ $r['created']->diffForHumans() }}</small>
                    <span class="mini-steps" aria-label="Tahap {{ count($r['timeline']) }} dari 4">
                        @for ($i = 1; $i <= 4; $i++)<i class="{{ $i <= count($r['timeline']) ? 'on' : '' }}"></i>@endfor
                    </span>
                </div>
                <x-sr-status :status="$r['status']" type="laporan" />
            </a>
        @empty
            <x-sr-empty icon="🌱" title="Tidak ada laporan aktif" text="Semua laporan Anda sudah selesai ditangani.">
                <a href="{{ route('warga.create') }}" class="btn btn-soft">Buat laporan</a>
            </x-sr-empty>
        @endforelse
    </section>

    <section class="panel">
        <div class="panel-head"><h3>Jadwal angkut di Banjarsari</h3><a class="text-link" href="{{ route('schedule') }}">Jadwal lengkap</a></div>
        @foreach ($nearby as $loc)
            <div class="report-line">
                <div>
                    <strong>{{ $loc['name'] }}</strong>
                    <small>Berikutnya {{ $loc['next']->isToday() ? 'hari ini' : ($loc['next']->isTomorrow() ? 'besok' : $loc['next']->translatedFormat('l')) }}, {{ $loc['window'] }}</small>
                </div>
                <x-sr-status :status="$loc['status']" />
            </div>
        @endforeach
        <div class="quick-panel compact">
            <a href="{{ route('map') }}">🗺 <span>Lihat peta sampah<small>Cek status titik terdekat</small></span>›</a>
        </div>
    </section>
</div>
@endsection
