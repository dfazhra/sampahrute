@extends(isset($role) ? 'sampahrute.layouts.dashboard' : 'sampahrute.layouts.public')
@section('title', 'Peta Surakarta')
@section('page-title', 'Peta Lokasi Sampah')

@section('content')
@php $p = collect($points); @endphp

@unless (isset($role))
    <section class="page-hero">
        <div class="container">
            <h1>Peta Lokasi Sampah Surakarta</h1>
            <p>Cari titik pengangkutan dan lihat apakah sampah di sana sudah diangkut hari ini.</p>
        </div>
    </section>
@endunless

<div class="{{ isset($role) ? '' : 'container' }} map-page-layout">
    <div class="map-list-panel">
        <h3>Daftar lokasi <span class="count-circle" data-visible-count>{{ $p->count() }}</span></h3>
        <input id="location-search" class="input" type="search" placeholder="Cari lokasi atau kecamatan" aria-label="Cari lokasi">
        <div class="filter-tabs" data-map-filters>
            <button class="active" data-filter="all">Semua</button>
            <button data-filter="menunggu">Belum <span>{{ $p->where('status', 'menunggu')->count() }}</span></button>
            <button data-filter="diproses">Proses <span>{{ $p->where('status', 'diproses')->count() }}</span></button>
            <button data-filter="selesai">Selesai <span>{{ $p->where('status', 'selesai')->count() }}</span></button>
        </div>
        <div class="map-list">
            @foreach ($points as $pt)
                <button type="button" class="map-list-item" data-point-id="{{ $pt['id'] }}" data-point-name="{{ $pt['name'] }}" data-point-district="{{ $pt['district'] }}" data-point-status="{{ $pt['status'] }}">
                    <span class="map-dot dot-{{ $pt['status'] }}" aria-hidden="true"></span>
                    <span>
                        <strong>{{ $pt['name'] }}</strong>
                        <small>{{ $pt['district'] }} · {{ $pt['type'] }} · {{ $pt['window'] }}</small>
                        <x-sr-fill :value="$pt['fill']" />
                    </span>
                    <span aria-hidden="true">›</span>
                </button>
            @endforeach
            <x-sr-empty data-empty hidden title="Lokasi tidak ditemukan" text="Coba kata kunci lain, misalnya nama kecamatan." />
        </div>
    </div>
    <div class="map-wrap">
        <div id="full-map" class="leaflet-map map-full" data-points='@json($points)' data-mode="full"
             data-report-url="{{ ($sessionRole ?? null) === 'warga' ? route('warga.create') : (($sessionRole ?? null) ? '' : route('login')) }}"></div>
        @include('sampahrute.partials.map-legend')
    </div>
</div>
<div class="{{ isset($role) ? '' : 'container' }} note-text">Koordinat titik dalam prototype ini adalah ilustrasi, bukan data TPS resmi.</div>
@endsection
