@extends('sampahrute.layouts.dashboard')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
@php
    $r = collect($reports);
    $l = collect($locations);
    $todayLocs = $l->where('scheduled_today', true);
    $doneToday = $todayLocs->where('status', 'selesai')->count();
    $weekLogs = collect($logs)->filter(fn ($x) => $x['date']->gte(today()->subDays(6)));
    $onTime = $weekLogs->count() ? round($weekLogs->where('result', 'tepat')->count() / $weekLogs->count() * 100) : 0;
    $full = $l->where('fill', '>=', 85);
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $missedYesterday = collect($logs)->filter(fn ($x) => $x['date']->isYesterday() && $x['result'] === 'terlewat');

    // Grafik laporan 7 hari terakhir
    $days = collect(range(6, 0))->map(fn ($d) => today()->subDays($d));
    $perDay = $days->map(fn ($day) => ['label' => $day->translatedFormat('D'), 'date' => $day->translatedFormat('d M'), 'count' => $r->filter(fn ($x) => $x['created']->isSameDay($day))->count()]);
    $maxDay = max(1, $perDay->max('count'));

    // Komposisi status laporan
    $statusColors = ['menunggu' => '#e2514b', 'diverifikasi' => '#3b82c4', 'diproses' => '#f1ad24', 'selesai' => '#159b56', 'ditolak' => '#9aa8a0'];
    $statusLabels = ['menunggu' => 'Menunggu verifikasi', 'diverifikasi' => 'Diverifikasi', 'diproses' => 'Ditangani petugas', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];
    $total = max(1, $r->count());
    $acc = 0; $stops = [];
    foreach ($statusColors as $key => $color) {
        $pct = $r->where('status', $key)->count() / $total * 100;
        $stops[] = "$color $acc% ".($acc + $pct).'%';
        $acc += $pct;
    }
@endphp

<div class="welcome-banner">
    <div>
        <span>Panel pengelola</span>
        <h1>{{ $greeting }}, {{ explode(' ', $me['name'])[0] }} 👋</h1>
        <p>
            Hari ini {{ $doneToday }} dari {{ $todayLocs->count() }} titik terjadwal sudah diangkut,
            dan ada {{ $r->where('status', 'menunggu')->count() }} laporan warga yang menunggu verifikasi.
        </p>
    </div>
    <a href="{{ route('admin.reports') }}?status=menunggu" class="btn btn-lime">Verifikasi laporan</a>
</div>

<div class="dashboard-stats">
    <x-sr-stat icon="📄" :value="$r->where('status', 'menunggu')->count()" label="Menunggu verifikasi" :note="$r->count().' laporan total'" />
    <x-sr-stat icon="🚛" :value="$doneToday.'/'.$todayLocs->count()" label="Titik diangkut hari ini" note="Sesuai jadwal hari ini" />
    <x-sr-stat icon="⏱" :value="$onTime.'%'" label="Tepat waktu (7 hari)" :note="$weekLogs->count().' pengangkutan tercatat'" />
    <x-sr-stat icon="⚠️" :value="$full->count()" label="Lokasi hampir penuh" note="Isi 85% ke atas" />
</div>

<div class="dash-two-cols">
    <section class="panel">
        <div class="panel-head">
            <h3>Laporan masuk 7 hari terakhir</h3>
            <a class="text-link" href="{{ route('admin.reports') }}">Lihat laporan</a>
        </div>
        <div class="bar-chart" role="img" aria-label="Grafik jumlah laporan per hari">
            @foreach ($perDay as $d)
                <div class="bar-col">
                    <span class="bar-value">{{ $d['count'] }}</span>
                    <span class="bar" style="height: {{ max(4, $d['count'] / $maxDay * 100) }}%" title="{{ $d['date'] }}: {{ $d['count'] }} laporan"></span>
                    <span class="bar-label">{{ $d['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h3>Status laporan</h3></div>
        <div class="donut-wrap">
            <div class="donut" style="background: conic-gradient({{ implode(', ', $stops) }})">
                <span><strong>{{ $r->count() }}</strong>laporan</span>
            </div>
            <ul class="legend">
                @foreach ($statusColors as $key => $color)
                    <li><i style="background: {{ $color }}"></i>{{ $statusLabels[$key] }}<b>{{ $r->where('status', $key)->count() }}</b></li>
                @endforeach
            </ul>
        </div>
    </section>
</div>

<div class="dash-two-cols mt-4">
    <section class="panel">
        <div class="panel-head">
            <h3>Perlu perhatian</h3>
            <small class="small-muted">{{ $r->where('status', 'menunggu')->count() + $full->count() + $missedYesterday->count() }} item</small>
        </div>
        <div class="attention-list">
            @foreach ($r->where('status', 'menunggu') as $item)
                <a href="{{ route('admin.reports') }}#{{ $item['id'] }}" class="attention-item">
                    <span class="att-icon att-red">📄</span>
                    <span><strong>{{ $item['id'] }} · {{ $item['title'] }}</strong><small>{{ $item['location'] }} · {{ $item['created']->diffForHumans() }}</small></span>
                    <x-sr-priority :level="$item['priority']" />
                </a>
            @endforeach
            @foreach ($full as $loc)
                <a href="{{ route('admin.locations') }}" class="attention-item">
                    <span class="att-icon att-amber">🗑</span>
                    <span><strong>{{ $loc['name'] }} hampir penuh</strong><small>{{ $loc['district'] }} · jadwal {{ $loc['window'] }}</small></span>
                    <x-sr-fill :value="$loc['fill']" />
                </a>
            @endforeach
            @foreach ($missedYesterday as $log)
                <a href="{{ route('admin.history') }}" class="attention-item">
                    <span class="att-icon att-gray">⏭</span>
                    <span><strong>{{ $log['location'] }} terlewat kemarin</strong><small>Petugas {{ $log['officer'] }}</small></span>
                    <x-sr-status :status="$log['result']" type="log" />
                </a>
            @endforeach
            @if ($r->where('status', 'menunggu')->isEmpty() && $full->isEmpty() && $missedYesterday->isEmpty())
                <x-sr-empty icon="🎉" title="Semua aman" text="Tidak ada laporan atau lokasi yang perlu ditindaklanjuti." />
            @endif
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h3>Peta monitoring</h3>
            <a class="text-link" href="{{ route('map') }}">Buka peta penuh</a>
        </div>
        <div id="admin-map" class="leaflet-map dashboard-map" data-points='@json($points)' data-mode="preview"></div>
        @include('sampahrute.partials.map-legend')
    </section>
</div>

<section class="panel table-panel mt-4">
    <div class="panel-head panel-pad">
        <h3>Progres pengangkutan hari ini</h3>
        <div class="progress-inline">
            <span class="progress-track"><span style="width: {{ $todayLocs->count() ? $doneToday / $todayLocs->count() * 100 : 0 }}%"></span></span>
            <small>{{ $doneToday }}/{{ $todayLocs->count() }} selesai</small>
        </div>
    </div>
    <div class="table-scroll">
        <table>
            <thead><tr><th>Lokasi</th><th>Petugas</th><th>Jam jadwal</th><th>Perkiraan isi</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($todayLocs as $loc)
                    <tr>
                        <td><strong>{{ $loc['name'] }}</strong><small>{{ $loc['district'] }}</small></td>
                        <td>{{ $loc['officer']['name'] }}<small>{{ $loc['officer']['vehicle'] }}</small></td>
                        <td>{{ $loc['window'] }}</td>
                        <td><x-sr-fill :value="$loc['fill']" /></td>
                        <td><x-sr-status :status="$loc['status']" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
