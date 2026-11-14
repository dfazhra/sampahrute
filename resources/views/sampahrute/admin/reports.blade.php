@extends('sampahrute.layouts.dashboard')
@section('title', 'Kelola Laporan')
@section('page-title', 'Kelola Laporan Warga')

@section('content')
@php
    $r = collect($reports);
    $statusTabs = ['all' => 'Semua', 'menunggu' => 'Menunggu', 'diverifikasi' => 'Diverifikasi', 'diproses' => 'Ditangani', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];
@endphp

<div class="page-title-row">
    <div>
        <h1>Kelola Laporan Warga</h1>
        <p>Verifikasi laporan masuk, tugaskan ke petugas, dan pantau sampai selesai.</p>
    </div>
    <button type="button" class="btn btn-soft" data-export-csv="report-table" data-filename="laporan-sampahrute.csv">⤓ Unduh CSV</button>
</div>

<div class="flow-strip" aria-label="Alur penanganan laporan">
    @foreach (['Menunggu verifikasi', 'Diverifikasi', 'Ditangani petugas', 'Selesai'] as $i => $step)
        <span><b>{{ $i + 1 }}</b>{{ $step }}</span>
    @endforeach
</div>

<section class="panel table-panel" data-filter-scope>
    <div class="toolbar">
        <input type="search" class="input" placeholder="Cari ID, judul, lokasi, atau pelapor" data-filter-search aria-label="Cari laporan">
        <select class="input" data-filter-field="district" aria-label="Filter kecamatan">
            <option value="">Semua kecamatan</option>
            @foreach ($r->pluck('district')->unique()->sort() as $d)
                <option value="{{ $d }}">{{ $d }}</option>
            @endforeach
        </select>
        <select class="input" data-filter-field="priority" aria-label="Filter prioritas">
            <option value="">Semua prioritas</option>
            <option value="tinggi">Prioritas tinggi</option>
            <option value="sedang">Prioritas sedang</option>
            <option value="rendah">Prioritas rendah</option>
        </select>
    </div>
    <div class="filter-tabs tabs-pad" role="tablist" aria-label="Filter status">
        @foreach ($statusTabs as $key => $label)
            <button type="button" class="{{ $key === 'all' ? 'active' : '' }}" data-filter-status="{{ $key }}">
                {{ $label }} <span>{{ $key === 'all' ? $r->count() : $r->where('status', $key)->count() }}</span>
            </button>
        @endforeach
    </div>

    <div class="table-scroll">
        <table id="report-table">
            <thead>
                <tr><th>ID</th><th>Laporan</th><th>Pelapor</th><th>Masuk</th><th>Prioritas</th><th>Petugas</th><th>Status</th><th><span class="visually-hidden">Aksi</span></th></tr>
            </thead>
            <tbody>
                @foreach ($r as $item)
                    <tr id="row-{{ $item['id'] }}" data-row
                        data-status="{{ $item['status'] }}"
                        data-district="{{ $item['district'] }}"
                        data-priority="{{ $item['priority'] }}"
                        data-search="{{ strtolower($item['id'].' '.$item['title'].' '.$item['location'].' '.$item['resident']) }}">
                        <td><strong>{{ $item['id'] }}</strong></td>
                        <td><strong>{{ $item['title'] }}</strong><small>{{ $item['location'] }} · {{ $item['district'] }}</small></td>
                        <td>{{ $item['resident'] }}</td>
                        <td>{{ $item['date'] }}<small>{{ $item['created']->diffForHumans() }}</small></td>
                        <td><x-sr-priority :level="$item['priority']" /></td>
                        <td>{{ $item['officer']['name'] ?? '—' }}</td>
                        <td><x-sr-status :status="$item['status']" type="laporan" /></td>
                        <td><button type="button" class="mini-action" data-modal-open="detail-{{ $item['id'] }}">Tinjau</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-sr-empty data-empty hidden />
    </div>
</section>
@endsection

@push('dialogs')
    @foreach ($reports as $item)
        <dialog class="drawer" id="detail-{{ $item['id'] }}" aria-labelledby="title-{{ $item['id'] }}">
            <div class="drawer-head">
                <div>
                    <small class="small-muted">{{ $item['id'] }} · {{ $item['category_label'] }}</small>
                    <h2 id="title-{{ $item['id'] }}">{{ $item['title'] }}</h2>
                </div>
                <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">×</button>
            </div>

            <div class="drawer-body">
                <div class="drawer-badges">
                    <x-sr-status :status="$item['status']" type="laporan" />
                    <x-sr-priority :level="$item['priority']" />
                </div>

                @if ($item['photo'])
                    <img class="drawer-photo" src="{{ asset($item['photo']) }}" alt="Foto laporan {{ $item['id'] }}">
                @else
                    <div class="drawer-photo no-photo">Pelapor tidak melampirkan foto</div>
                @endif

                <p class="drawer-desc">{{ $item['description'] }}</p>

                <dl class="info-grid">
                    <div><dt>Lokasi</dt><dd>{{ $item['location'] }}<small>{{ $item['location_data']['address'] ?? '' }}</small></dd></div>
                    <div><dt>Pelapor</dt><dd>{{ $item['resident'] }}</dd></div>
                    <div><dt>Masuk</dt><dd>{{ $item['created']->translatedFormat('d M Y, H:i') }}</dd></div>
                    <div><dt>Petugas</dt><dd>{{ $item['officer']['name'] ?? 'Belum ditugaskan' }}</dd></div>
                </dl>

                <h3 class="drawer-sub">Riwayat penanganan</h3>
                <x-sr-timeline :steps="$item['timeline']" :status="$item['status']" />
            </div>

            <div class="drawer-actions">
                @if ($item['status'] === 'menunggu')
                    <form method="POST" action="{{ route('demo.status') }}">
                        @csrf
                        <input type="hidden" name="report_id" value="{{ $item['id'] }}">
                        <input type="hidden" name="status" value="diverifikasi">
                        <button class="btn btn-primary full-width">✓ Verifikasi laporan</button>
                    </form>
                    <details class="reject-box">
                        <summary>Tolak laporan</summary>
                        <form method="POST" action="{{ route('demo.status') }}">
                            @csrf
                            <input type="hidden" name="report_id" value="{{ $item['id'] }}">
                            <input type="hidden" name="status" value="ditolak">
                            <label for="reason-{{ $item['id'] }}">Alasan penolakan</label>
                            <textarea class="input" id="reason-{{ $item['id'] }}" name="reason" rows="2" required maxlength="300" placeholder="Contoh: laporan sama dengan SR-001"></textarea>
                            <button class="btn btn-danger full-width">Tolak laporan</button>
                        </form>
                    </details>
                @elseif ($item['status'] === 'diverifikasi')
                    <form method="POST" action="{{ route('demo.status') }}" class="assign-form">
                        @csrf
                        <input type="hidden" name="report_id" value="{{ $item['id'] }}">
                        <input type="hidden" name="status" value="diproses">
                        <label for="officer-{{ $item['id'] }}">Tugaskan ke petugas</label>
                        <select class="input" id="officer-{{ $item['id'] }}" name="officer_id" required>
                            <option value="">Pilih petugas</option>
                            @foreach ($officers as $o)
                                <option value="{{ $o['id'] }}" @selected(($item['location_data']['officer_id'] ?? null) === $o['id'])>{{ $o['name'] }} — {{ $o['area'] }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary full-width">🚚 Tugaskan petugas</button>
                    </form>
                @elseif ($item['status'] === 'diproses')
                    <p class="drawer-note">Sedang ditangani {{ $item['officer']['name'] ?? 'petugas' }}. Petugas akan menandai selesai dari aplikasinya.</p>
                    <form method="POST" action="{{ route('demo.status') }}">
                        @csrf
                        <input type="hidden" name="report_id" value="{{ $item['id'] }}">
                        <input type="hidden" name="status" value="selesai">
                        <button class="btn btn-soft full-width">Tandai selesai</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('demo.status') }}">
                        @csrf
                        <input type="hidden" name="report_id" value="{{ $item['id'] }}">
                        <input type="hidden" name="status" value="menunggu">
                        <button class="btn btn-soft full-width">↺ Buka kembali laporan</button>
                    </form>
                @endif
            </div>
        </dialog>
    @endforeach
@endpush
