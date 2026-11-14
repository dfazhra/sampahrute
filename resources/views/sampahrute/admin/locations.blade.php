@extends('sampahrute.layouts.dashboard')
@section('title', 'Kelola Lokasi')
@section('page-title', 'Kelola Lokasi Sampah')

@section('content')
@php $l = collect($locations); @endphp

<div class="page-title-row">
    <div>
        <h1>Lokasi Sampah Terdaftar</h1>
        <p>{{ $l->count() }} titik pengangkutan dengan total kapasitas {{ $l->sum('capacity') }} m³.</p>
    </div>
    <div class="button-row">
        <a class="btn btn-soft" href="{{ route('map') }}">🗺 Lihat di peta</a>
        <button type="button" class="btn btn-primary" data-modal-open="location-form" data-fill='{"_title":"Tambah lokasi baru","_submit":"Simpan lokasi"}'>＋ Tambah lokasi</button>
    </div>
</div>

<div class="mini-stats">
    @foreach (['TPS', 'Depo', 'Kontainer'] as $type)
        <div><strong>{{ $l->where('type', $type)->count() }}</strong><span>{{ $type }}</span></div>
    @endforeach
    <div><strong>{{ $l->where('fill', '>=', 85)->count() }}</strong><span>Hampir penuh</span></div>
</div>

<section class="panel table-panel" data-filter-scope>
    <div class="toolbar">
        <input type="search" class="input" placeholder="Cari nama, alamat, atau kode lokasi" data-filter-search aria-label="Cari lokasi">
        <select class="input" data-filter-field="district" aria-label="Filter kecamatan">
            <option value="">Semua kecamatan</option>
            @foreach ($l->pluck('district')->unique()->sort() as $d)
                <option value="{{ $d }}">{{ $d }}</option>
            @endforeach
        </select>
        <select class="input" data-filter-field="type" aria-label="Filter jenis">
            <option value="">Semua jenis</option>
            <option>TPS</option><option>Depo</option><option>Kontainer</option>
        </select>
    </div>
    <div class="table-scroll">
        <table id="location-table">
            <thead><tr><th>Kode</th><th>Lokasi</th><th>Jenis</th><th>Kecamatan</th><th>Jadwal</th><th>Petugas</th><th>Isi</th><th>Hari ini</th><th><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($l as $loc)
                    <tr data-row data-district="{{ $loc['district'] }}" data-type="{{ $loc['type'] }}" data-search="{{ strtolower($loc['id'].' '.$loc['name'].' '.$loc['address'].' '.$loc['village']) }}">
                        <td><strong>{{ $loc['id'] }}</strong></td>
                        <td><strong>{{ $loc['name'] }}</strong><small>{{ $loc['address'] }}</small></td>
                        <td><span class="type-chip">{{ $loc['type'] }}</span><small>{{ $loc['capacity'] }} m³</small></td>
                        <td>{{ $loc['district'] }}<small>{{ $loc['village'] }}</small></td>
                        <td>{{ $loc['days_label'] }}<small>{{ $loc['window'] }}</small></td>
                        <td>{{ $loc['officer']['name'] }}</td>
                        <td><x-sr-fill :value="$loc['fill']" /></td>
                        <td><x-sr-status :status="$loc['status']" /></td>
                        <td><div class="row-actions">
                            <button type="button" class="mini-action ghost" data-modal-open="location-form"
                                data-fill="{{ json_encode(["_title" => "Ubah ".$loc['name'], "_submit" => "Simpan perubahan", "name" => $loc['name'], "type" => $loc['type'], "district" => $loc['district'], "address" => $loc['address'], "lat" => $loc['lat'], "lng" => $loc['lng'], "capacity" => $loc['capacity']]) }}">Ubah</button>
                            <button type="button" class="mini-action danger" data-modal-open="delete-form" data-fill="{{ json_encode(["target" => $loc['name'], "_title" => "Hapus ".$loc['name']."?"]) }}">Hapus</button>
                        </div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <x-sr-empty data-empty hidden />
    </div>
</section>
@endsection

@push('dialogs')
<dialog class="sr-modal modal-wide" id="location-form" aria-labelledby="location-form-title">
    <form method="POST" action="{{ route('demo.save') }}" class="modal-form">
        @csrf
        <input type="hidden" name="entity" value="lokasi">
        <div class="modal-head">
            <h2 id="location-form-title" data-fill-text="_title">Tambah lokasi baru</h2>
            <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">×</button>
        </div>
        <div class="modal-grid">
            <div>
                <div class="form-field">
                    <label for="loc-name">Nama lokasi <b>*</b></label>
                    <input class="input" id="loc-name" name="name" required maxlength="100" placeholder="Contoh: TPS Sumber">
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="loc-type">Jenis <b>*</b></label>
                        <select class="input" id="loc-type" name="type" required>
                            <option>TPS</option><option>Depo</option><option>Kontainer</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="loc-cap">Kapasitas (m³) <b>*</b></label>
                        <input class="input" id="loc-cap" name="capacity" type="number" min="1" max="50" step="0.5" required value="6">
                    </div>
                </div>
                <div class="form-field">
                    <label for="loc-district">Kecamatan <b>*</b></label>
                    <select class="input" id="loc-district" name="district" required>
                        @foreach (['Banjarsari', 'Jebres', 'Laweyan', 'Pasar Kliwon', 'Serengan'] as $d)
                            <option>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label for="loc-address">Alamat <b>*</b></label>
                    <textarea class="input" id="loc-address" name="address" rows="2" required maxlength="200" placeholder="Nama jalan dan kelurahan"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="loc-lat">Latitude</label>
                        <input class="input" id="loc-lat" name="lat" required readonly placeholder="Klik peta">
                    </div>
                    <div class="form-field">
                        <label for="loc-lng">Longitude</label>
                        <input class="input" id="loc-lng" name="lng" required readonly placeholder="Klik peta">
                    </div>
                </div>
            </div>
            <div>
                <label class="picker-label">Titik lokasi <b>*</b></label>
                <div class="leaflet-map picker-map" data-map-picker data-lat-input="loc-lat" data-lng-input="loc-lng"></div>
                <p class="form-notice">Klik peta untuk menaruh penanda. Penanda bisa digeser.</p>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-soft" data-modal-close>Batal</button>
            <button type="submit" class="btn btn-primary" data-fill-text="_submit">Simpan lokasi</button>
        </div>
    </form>
</dialog>

@include('sampahrute.partials.delete-dialog')
@endpush
