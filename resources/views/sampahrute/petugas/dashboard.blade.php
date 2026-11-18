@extends('sampahrute.layouts.dashboard')
@section('title', 'Dashboard Petugas')
@section('page-title', 'Dashboard Petugas')

@section('content')
    @php
        $stops = collect($route);
        $done = $stops->where('status', 'selesai')->count();
        $next = $stops->first(fn($s) => $s['status'] !== 'selesai');
        $openReports = collect($myReports)->where('status', 'diproses');
        $routePoints = $stops->values()->map(fn($s, $i) => ['n' => $i + 1, 'name' => $s['name'], 'lat' => $s['lat'], 'lng' => $s['lng'], 'status' => $s['status'], 'window' => $s['window']]);
    @endphp

    <div class="welcome-banner">
        <div>
            <span>Panel petugas · {{ $officers[$me['officer_id'] ?? 'PTG-01']['vehicle'] ?? 'Truk Pengangkut' }}</span>
            <h1>Halo, {{ explode(' ', $me['name'])[0] }}.
                {{ $done === $stops->count() && $stops->count() ? 'Rute hari ini sudah beres 🎉' : 'Siap bertugas hari ini? 🚚' }}
            </h1>
            <p>
                @if ($next)
                    Titik berikutnya: <strong>{{ $next['name'] }}</strong>, jadwal {{ $next['window'] }}.
                @else
                    Semua titik di rute hari ini sudah tercatat diangkut.
                @endif
            </p>
            <div class="banner-progress">
                <span class="progress-track light"><span
                        style="width: {{ $stops->count() ? $done / $stops->count() * 100 : 0 }}%"></span></span>
                <small>{{ $done }} dari {{ $stops->count() }} titik selesai</small>
            </div>
        </div>
        <a href="{{ route('petugas.tasks') }}" class="btn btn-lime">Buka tugas hari ini</a>
    </div>

    <div class="dashboard-stats">
        <x-sr-stat icon="📍" :value="$stops->count()" label="Titik di rute hari ini" />
        <x-sr-stat icon="⏳" :value="$stops->where('status', 'menunggu')->count()" label="Belum diangkut" />
        <x-sr-stat icon="✅" :value="$done" label="Sudah diangkut" />
        <x-sr-stat icon="📌" :value="$openReports->count()" label="Laporan ditugaskan" />
    </div>

    <div class="dash-two-cols">
        <section class="panel">
            <div class="panel-head">
                <h3>Urutan rute hari ini</h3><a class="text-link" href="{{ route('petugas.tasks') }}">Kelola</a>
            </div>
            <ol class="route-list">
                @forelse ($stops as $s)
                    <li class="route-stop is-{{ $s['status'] }}">
                        <span class="stop-num">{{ $loop->iteration }}</span>
                        <span class="stop-info"><strong>{{ $s['name'] }}</strong><small>{{ $s['window'] }} ·
                                {{ $s['address'] }}</small></span>
                        <x-sr-status :status="$s['status']" />
                    </li>
                @empty
                    <x-sr-empty icon="🛌" title="Tidak ada jadwal hari ini" text="Nikmati hari libur Anda." />
                @endforelse
            </ol>
        </section>
        <section class="panel">
            <div class="panel-head">
                <h3>Peta rute</h3>
            </div>
            <div class="leaflet-map dashboard-map" data-points='@json($points)' data-route='@json($routePoints)'
                data-mode="route"></div>
        </section>
    </div>

    <section class="panel mt-4">
        <div class="panel-head">
            <h3>Laporan warga yang ditugaskan</h3><a class="text-link" href="{{ route('petugas.tasks') }}#laporan">Semua
                tugas</a>
        </div>
        @forelse ($openReports as $rep)
            <div class="report-line">
                <div><strong>{{ $rep['id'] }} · {{ $rep['title'] }}</strong><small>{{ $rep['location'] }} · dilaporkan
                        {{ $rep['created']->diffForHumans() }}</small></div>
                <x-sr-priority :level="$rep['priority']" />
            </div>
        @empty
            <x-sr-empty icon="👍" title="Tidak ada laporan aktif" text="Laporan yang ditugaskan admin akan muncul di sini." />
        @endforelse
    </section>
@endsection