@extends('sampahrute.layouts.dashboard')
@section('title', 'Tugas Hari Ini')
@section('page-title', 'Tugas Pengangkutan')

@section('content')
@php
    $stops = collect($route);
    $assigned = collect($myReports);
@endphp

<div class="page-title-row">
    <div>
        <h1>Tugas Hari Ini</h1>
        <p>{{ now()->translatedFormat('l, d F Y') }} · {{ $stops->where('status', 'selesai')->count() }}/{{ $stops->count() }} titik selesai</p>
    </div>
    <a href="{{ route('map') }}" class="btn btn-soft">🗺 Lihat peta</a>
</div>

<div class="segmented" role="tablist">
    <a href="#rute" class="active" data-tab="rute" role="tab">Rute rutin <span>{{ $stops->count() }}</span></a>
    <a href="#laporan" data-tab="laporan" role="tab">Laporan ditugaskan <span>{{ $assigned->where('status', 'diproses')->count() }}</span></a>
</div>

<div data-tab-panel="rute">
    <div class="task-grid">
        @forelse ($stops as $s)
            <article class="panel task-card stop-card is-{{ $s['status'] }}">
                <div class="task-heading">
                    <span class="stop-num">{{ $loop->iteration }}</span>
                    <div class="grow">
                        <h3>{{ $s['name'] }}</h3>
                        <p>📍 {{ $s['address'] }}</p>
                    </div>
                    <x-sr-status :status="$s['status']" />
                </div>
                <dl class="task-meta">
                    <div><dt>Jadwal</dt><dd>{{ $s['window'] }}</dd></div>
                    <div><dt>Jenis</dt><dd>{{ $s['type'] }} · {{ $s['capacity'] }} m³</dd></div>
                    <div><dt>Perkiraan isi</dt><dd><x-sr-fill :value="$s['fill']" /></dd></div>
                </dl>
                <div class="task-actions">
                    @if ($s['status'] === 'selesai')
                        <p class="done-note">✓ Sudah diangkut hari ini</p>
                    @else
                        @if ($s['status'] === 'menunggu')
                            <form method="POST" action="{{ route('demo.pickup') }}">
                                @csrf
                                <input type="hidden" name="location_id" value="{{ $s['id'] }}">
                                <input type="hidden" name="status" value="diproses">
                                <button class="btn btn-soft full-width">▶ Mulai angkut</button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-primary full-width" data-modal-open="pickup-{{ $s['id'] }}">✓ Konfirmasi sudah diangkut</button>
                    @endif
                    <a class="text-link" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination={{ $s['lat'] }},{{ $s['lng'] }}">Petunjuk arah ↗</a>
                </div>
            </article>
        @empty
            <x-sr-empty icon="🛌" title="Tidak ada jadwal hari ini" text="Rute Anda kosong hari ini. Cek jadwal minggu ini di menu Jadwal." />
        @endforelse
    </div>
</div>

<div data-tab-panel="laporan" hidden>
    <div class="report-list">
        @forelse ($assigned as $rep)
            <article class="panel report-item report-task">
                <div class="report-card-icon">📄</div>
                <div>
                    <span class="small-muted">{{ $rep['id'] }} · {{ $rep['created']->translatedFormat('d M Y, H:i') }}</span>
                    <h3>{{ $rep['title'] }}</h3>
                    <p>📍 {{ $rep['location'] }} — {{ $rep['location_data']['address'] ?? '' }}</p>
                    <p class="report-desc">{{ $rep['description'] }}</p>
                </div>
                <div class="report-side">
                    <x-sr-status :status="$rep['status']" type="laporan" />
                    @if ($rep['status'] === 'diproses')
                        <form method="POST" action="{{ route('demo.status') }}">
                            @csrf
                            <input type="hidden" name="report_id" value="{{ $rep['id'] }}">
                            <input type="hidden" name="status" value="selesai">
                            <button class="btn btn-primary">Tandai selesai</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <x-sr-empty icon="👍" title="Tidak ada laporan ditugaskan" text="Laporan dari admin akan muncul di sini." />
        @endforelse
    </div>
</div>
@endsection

@push('dialogs')
    @foreach ($stops->where('status', '!=', 'selesai') as $s)
        <dialog class="sr-modal" id="pickup-{{ $s['id'] }}" aria-labelledby="pickup-title-{{ $s['id'] }}">
            <form method="POST" action="{{ route('demo.pickup') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="location_id" value="{{ $s['id'] }}">
                <input type="hidden" name="status" value="selesai">
                <div class="modal-head">
                    <div>
                        <h2 id="pickup-title-{{ $s['id'] }}">Konfirmasi pengangkutan</h2>
                        <small class="small-muted">{{ $s['name'] }} · jadwal {{ $s['window'] }}</small>
                    </div>
                    <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">×</button>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="time-{{ $s['id'] }}">Jam diangkut <b>*</b></label>
                        <input class="input" type="time" id="time-{{ $s['id'] }}" name="time" required data-default-now>
                    </div>
                    <div class="form-field">
                        <label for="vol-{{ $s['id'] }}">Perkiraan volume (m³)</label>
                        <input class="input" type="number" id="vol-{{ $s['id'] }}" name="volume" min="0" max="50" step="0.5" placeholder="{{ $s['capacity'] }}">
                    </div>
                </div>
                <div class="form-field">
                    <label>Foto bukti</label>
                    <label class="dropzone" data-dropzone>
                        <input type="file" name="photo" accept="image/*" capture="environment">
                        <span data-dropzone-text>📷 Ambil atau pilih foto kondisi setelah diangkut</span>
                        <img alt="Pratinjau foto bukti" hidden>
                    </label>
                </div>
                <div class="form-field">
                    <label for="note-{{ $s['id'] }}">Catatan</label>
                    <textarea class="input" id="note-{{ $s['id'] }}" name="note" rows="2" maxlength="300" placeholder="Opsional, misalnya ada sampah B3 atau akses jalan tertutup"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-soft" data-modal-close>Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan konfirmasi</button>
                </div>
            </form>
        </dialog>
    @endforeach
@endpush
