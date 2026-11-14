@extends('sampahrute.layouts.dashboard')
@section('title', 'Laporan '.$report['id'])
@section('page-title', 'Detail Laporan')

@section('content')
@php $loc = $report['location_data']; @endphp

<a href="{{ route('warga.reports') }}" class="back-inline">‹ Kembali ke laporan saya</a>

<div class="page-title-row">
    <div>
        <span class="small-muted">{{ $report['id'] }} · {{ $report['category_label'] }}</span>
        <h1>{{ $report['title'] }}</h1>
        <p>Dikirim {{ $report['created']->translatedFormat('l, d F Y \p\u\k\u\l H:i') }}</p>
    </div>
    <x-sr-status :status="$report['status']" type="laporan" class="status-lg" />
</div>

<div class="detail-layout">
    <section class="panel">
        <h3 class="panel-title">Perkembangan laporan</h3>
        <x-sr-timeline :steps="$report['timeline']" :status="$report['status']" />
        @if ($report['status'] === 'selesai')
            <div class="alert success">Terima kasih! Laporan Anda membantu menjaga {{ $loc['district'] ?? 'Surakarta' }} tetap bersih.</div>
        @endif
    </section>

    <aside class="detail-side">
        <section class="panel">
            <h3 class="panel-title">Isi laporan</h3>
            @if ($report['photo'])
                <img class="drawer-photo" src="{{ asset($report['photo']) }}" alt="Foto laporan {{ $report['id'] }}">
            @endif
            <p class="drawer-desc">{{ $report['description'] }}</p>
            <dl class="info-grid one">
                <div><dt>Lokasi</dt><dd>{{ $report['location'] }}<small>{{ $loc['address'] ?? '' }}</small></dd></div>
                <div><dt>Petugas</dt><dd>{{ $report['officer']['name'] ?? 'Belum ditugaskan' }}</dd></div>
                <div><dt>Jadwal angkut lokasi</dt><dd>{{ $loc['days_label'] ?? '-' }}, {{ $loc['window'] ?? '' }}</dd></div>
            </dl>
        </section>
        <section class="panel">
            <div class="leaflet-map mini-map" data-points="{{ json_encode(array_values(array_filter($points, fn ($p) => $p['id'] === $report['location_id']))) }}" data-mode="single"></div>
        </section>
    </aside>
</div>
@endsection
