@extends('sampahrute.layouts.dashboard')
@section('title', 'Laporan Saya')
@section('page-title', 'Laporan Saya')

@section('content')
@php $mine = collect($myReports); @endphp

<div class="page-title-row">
    <div>
        <h1>Laporan Saya</h1>
        <p>{{ $mine->count() }} laporan dikirim dari akun Anda.</p>
    </div>
    <a href="{{ route('warga.create') }}" class="btn btn-primary">＋ Buat laporan</a>
</div>

<div data-filter-scope>
    <div class="filter-tabs">
        @foreach (['all' => 'Semua', 'menunggu' => 'Menunggu', 'diverifikasi' => 'Diverifikasi', 'diproses' => 'Ditangani', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $key => $label)
            <button type="button" class="{{ $key === 'all' ? 'active' : '' }}" data-filter-status="{{ $key }}">{{ $label }} <span>{{ $key === 'all' ? $mine->count() : $mine->where('status', $key)->count() }}</span></button>
        @endforeach
    </div>

    <div class="report-list">
        @forelse ($mine as $r)
            <a class="panel report-item link-card" href="{{ route('warga.show', $r['id']) }}" data-row data-status="{{ $r['status'] }}">
                <div class="report-card-icon">📄</div>
                <div>
                    <span class="small-muted">{{ $r['id'] }} · {{ $r['created']->translatedFormat('d M Y, H:i') }}</span>
                    <h3>{{ $r['title'] }}</h3>
                    <p>📍 {{ $r['location'] }} · {{ $r['category_label'] }}</p>
                    @if ($r['status'] === 'ditolak' && $r['reject_reason'])
                        <p class="reject-reason">Alasan: {{ $r['reject_reason'] }}</p>
                    @endif
                </div>
                <x-sr-status :status="$r['status']" type="laporan" />
                <span class="card-chevron" aria-hidden="true">›</span>
            </a>
        @empty
            <x-sr-empty icon="📝" title="Belum ada laporan" text="Laporan yang Anda kirim akan muncul di sini.">
                <a href="{{ route('warga.create') }}" class="btn btn-soft">Buat laporan pertama</a>
            </x-sr-empty>
        @endforelse
        <x-sr-empty data-empty hidden title="Tidak ada laporan dengan status ini" text="Pilih filter lain untuk melihat laporan Anda." />
    </div>
</div>
@endsection
