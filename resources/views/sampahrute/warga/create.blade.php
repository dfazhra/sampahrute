@extends('sampahrute.layouts.dashboard')
@section('title', 'Buat Laporan')
@section('page-title', 'Buat Laporan Sampah')

@section('content')
<div class="page-title-row">
    <div>
        <h1>Laporkan Sampah</h1>
        <p>Isi data berikut. Laporan yang jelas lebih cepat diverifikasi.</p>
    </div>
</div>

<div class="form-layout">
    <form class="panel report-form" method="POST" action="{{ route('warga.store') }}" enctype="multipart/form-data" novalidate data-validate>
        @csrf

        @if ($errors->any())
            <div class="alert error" role="alert">
                <strong>Periksa kembali formulir:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <fieldset class="form-field">
            <legend>Jenis masalah <b>*</b></legend>
            <div class="chip-picker">
                @foreach (\App\Support\DemoData::CATEGORIES as $key => $label)
                    <label>
                        <input type="radio" name="category" value="{{ $key }}" required @checked(old('category', 'belum_diangkut') === $key) data-title-suggest="{{ $label === 'Lainnya' ? '' : $label }}">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="form-field">
            <label for="title">Judul laporan <b>*</b></label>
            <input class="input" id="title" type="text" name="title" value="{{ old('title', 'Belum diangkut') }}" required maxlength="150" placeholder="Contoh: Sampah belum diangkut sejak kemarin">
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="location_id">Lokasi sampah <b>*</b></label>
                <select class="input" id="location_id" name="location_id" required data-map-select="report-map">
                    <option value="">Pilih lokasi</option>
                    @foreach (collect($locations)->groupBy('district') as $district => $items)
                        <optgroup label="{{ $district }}">
                            @foreach ($items as $p)
                                <option value="{{ $p['id'] }}" @selected(old('location_id', request('lokasi')) === $p['id'])>{{ $p['name'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="form-field">
                <label for="date">Tanggal kejadian <b>*</b></label>
                <input class="input" id="date" name="date" type="date" required max="{{ date('Y-m-d') }}" value="{{ old('date', date('Y-m-d')) }}">
            </div>
        </div>

        <div class="leaflet-map select-map" id="report-map" data-points='@json($points)' data-mode="select" data-select-input="location_id"></div>
        <p class="form-notice">Atau klik penanda di peta untuk memilih lokasi.</p>

        <div class="form-field">
            <label for="description">Deskripsi masalah <b>*</b></label>
            <textarea class="input" id="description" name="description" rows="5" required minlength="10" maxlength="2000" data-counter placeholder="Jelaskan kondisi sampah, sejak kapan, dan dampaknya">{{ old('description') }}</textarea>
            <small class="counter" data-counter-for="description">0/2000</small>
        </div>

        <div class="form-field">
            <label>Foto (opsional, maks. 3 MB)</label>
            <label class="dropzone" data-dropzone>
                <input type="file" id="photo" name="photo" accept="image/*">
                <span data-dropzone-text>📷 Seret foto ke sini atau klik untuk memilih</span>
                <img alt="Pratinjau foto" hidden>
            </label>
        </div>

        <button type="submit" class="btn btn-primary full-width">Kirim laporan</button>
    </form>

    <aside class="form-side">
        <div class="panel">
            <h3>💡 Tips laporan yang baik</h3>
            <ul class="tips">
                <li>Pilih titik lokasi yang paling dekat dengan tumpukan sampah.</li>
                <li>Sebutkan sejak kapan sampah belum diangkut.</li>
                <li>Foto dari jarak yang memperlihatkan sekitar lokasi.</li>
            </ul>
        </div>
        <div class="panel">
            <h3>Setelah laporan dikirim</h3>
            <ol class="steps">
                <li><strong>Admin memverifikasi</strong><small>Biasanya kurang dari 1 jam kerja</small></li>
                <li><strong>Petugas ditugaskan</strong><small>Sesuai wilayah lokasi</small></li>
                <li><strong>Sampah diangkut</strong><small>Status berubah jadi selesai</small></li>
            </ol>
        </div>
    </aside>
</div>
@endsection
