@extends('sampahrute.layouts.dashboard')
@section('title', 'Riwayat Pengangkutan')
@section('page-title', 'Riwayat Pengangkutan')

@section('content')
@php
    $g = collect($logs);
    $done = $g->where('result', '!=', 'terlewat');
    $onTime = $g->count() ? round($g->where('result', 'tepat')->count() / $g->count() * 100) : 0;
    $range = collect(range(13, 0))->map(fn ($d) => today()->subDays($d));
    $byLoc = $g->groupBy('location');
    $isAdmin = $role === 'admin';
@endphp

<div class="page-title-row">
    <div>
        <h1>{{ $isAdmin ? 'Monitoring Tanggal Pengangkutan' : 'Riwayat Pengangkutan Saya' }}</h1>
        <p>Bandingkan jadwal dengan waktu pengangkutan yang dicatat petugas selama 14 hari terakhir.</p>
    </div>
    <button type="button" class="btn btn-soft" data-export-csv="log-table" data-filename="riwayat-pengangkutan.csv">⤓ Unduh CSV</button>
</div>

<div class="dashboard-stats">
    <x-sr-stat icon="🚛" :value="$done->count()" label="Pengangkutan tercatat" note="14 hari terakhir" />
    <x-sr-stat icon="⏱" :value="$onTime.'%'" label="Tepat waktu" :note="$g->where('result', 'tepat')->count().' dari '.$g->count().' jadwal'" />
    <x-sr-stat icon="🐢" :value="$g->where('result', 'terlambat')->count()" label="Terlambat" note="Lewat dari jam jadwal" />
    <x-sr-stat icon="⏭" :value="$g->where('result', 'terlewat')->count()" label="Terlewat" note="Tidak diangkut sama sekali" />
</div>

<section class="panel table-panel">
    <div class="panel-head panel-pad">
        <h3>Kalender ketepatan per lokasi</h3>
        <div class="map-legend inline">
            <span><i style="background:#159b56"></i>Tepat</span>
            <span><i style="background:#f1ad24"></i>Terlambat</span>
            <span><i style="background:#e2514b"></i>Terlewat</span>
            <span><i style="background:#e7eee9"></i>Tidak terjadwal</span>
        </div>
    </div>
    <div class="table-scroll">
        <table class="heat-table">
            <thead>
                <tr>
                    <th>Lokasi</th>
                    @foreach ($range as $day)
                        <th class="{{ $day->isToday() ? 'is-today' : '' }}" title="{{ $day->translatedFormat('l, d M') }}">{{ $day->format('d') }}<small>{{ $day->translatedFormat('D') }}</small></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($byLoc as $locName => $items)
                    @php $map = $items->keyBy(fn ($x) => $x['date']->toDateString()); @endphp
                    <tr>
                        <td><strong>{{ $locName }}</strong></td>
                        @foreach ($range as $day)
                            @php $x = $map->get($day->toDateString()); @endphp
                            <td class="heat-cell">
                                @if ($x)
                                    <span class="heat heat-{{ $x['result'] }}" title="{{ $day->translatedFormat('d M') }}: {{ $x['actual'] ? 'diangkut '.$x['actual'] : 'terlewat' }} (jadwal {{ $x['window'] }})"></span>
                                @else
                                    <span class="heat heat-none" title="{{ $day->translatedFormat('d M') }}: tidak terjadwal atau belum dicatat"></span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="panel table-panel mt-4" data-filter-scope>
    <div class="toolbar">
        <input type="search" class="input" placeholder="Cari lokasi, petugas, atau catatan" data-filter-search aria-label="Cari riwayat">
        <select class="input" data-filter-max="age" aria-label="Rentang waktu">
            <option value="">14 hari terakhir</option>
            <option value="0">Hari ini</option>
            <option value="6">7 hari terakhir</option>
        </select>
        <select class="input" data-filter-field="location" aria-label="Filter lokasi">
            <option value="">Semua lokasi</option>
            @foreach ($byLoc->keys()->sort() as $name)
                <option>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-tabs tabs-pad">
        @foreach (['all' => 'Semua', 'tepat' => 'Tepat waktu', 'terlambat' => 'Terlambat', 'terlewat' => 'Terlewat'] as $key => $label)
            <button type="button" class="{{ $key === 'all' ? 'active' : '' }}" data-filter-status="{{ $key }}">{{ $label }} <span>{{ $key === 'all' ? $g->count() : $g->where('result', $key)->count() }}</span></button>
        @endforeach
    </div>
    <div class="table-scroll">
        <table id="log-table">
            <thead><tr><th>Tanggal</th><th>Lokasi</th>@if($isAdmin)<th>Petugas</th>@endif<th>Jadwal</th><th>Diangkut</th><th>Volume</th><th>Hasil</th><th>Catatan</th></tr></thead>
            <tbody>
                @foreach ($g as $x)
                    <tr data-row data-status="{{ $x['result'] }}" data-location="{{ $x['location'] }}" data-age="{{ (int) $x['date']->diffInDays(today(), true) }}" data-search="{{ strtolower($x['location'].' '.$x['officer'].' '.$x['note']) }}">
                        <td><strong>{{ $x['date']->translatedFormat('d M Y') }}</strong><small>{{ $x['date']->translatedFormat('l') }}</small></td>
                        <td>{{ $x['location'] }}<small>{{ $x['district'] }}</small></td>
                        @if($isAdmin)<td>{{ $x['officer'] }}</td>@endif
                        <td>{{ $x['window'] }}</td>
                        <td>{{ $x['actual'] ?? '—' }}</td>
                        <td>{{ $x['volume'] ? $x['volume'].' m³' : '—' }}</td>
                        <td><x-sr-status :status="$x['result']" type="log" /></td>
                        <td class="wrap-cell">{{ $x['note'] ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-sr-empty data-empty hidden />
    </div>
</section>
@endsection
