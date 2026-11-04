
@extends('sampahrute.layouts.dashboard')

@section('title', 'Buat Laporan')

@section('page-title', 'Buat Laporan Sampah')

@section('content')

<div class="page-title-row">
    <div>
        <div class="eyebrow">— FORMULIR PELAPORAN</div>
        <h1>Laporkan Sampah</h1>
        <p>Lengkapi informasi tentang sampah yang belum diangkut.</p>
    </div>
</div>

<div class="form-layout">

    <form
        class="panel report-form"
        method="POST"
        action="{{ route('warga.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        @if ($errors->any())
            <div class="alert error">
                <strong>Periksa kembali formulir:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-field">
            <label for="title">
                Judul laporan <b>*</b>
            </label>

            <input
                class="input"
                id="title"
                type="text"
                name="title"
                value="{{ old('title') }}"
                required
                maxlength="150"
                placeholder="Contoh: Sampah belum diangkut"
            >
        </div>

        <div class="form-field">
            <label for="location">
                Lokasi sampah <b>*</b>
            </label>

            <select
                class="input"
                id="location"
                name="location"
                required
            >
                <option value="">Pilih lokasi</option>

                @foreach ($points as $p)
                    <option
                        value="{{ $p['name'] }}"
                        @selected(old('location') === $p['name'])
                    >
                        {{ $p['name'] }} — {{ $p['district'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label for="date">
                Tanggal kejadian <b>*</b>
            </label>

            <input
                class="input"
                id="date"
                name="date"
                type="date"
                required
                value="{{ old('date', date('Y-m-d')) }}"
            >
        </div>

        <div class="form-field">
            <label for="description">
                Deskripsi masalah <b>*</b>
            </label>

            <textarea
                class="input"
                id="description"
                name="description"
                rows="5"
                required
                minlength="10"
                maxlength="2000"
                placeholder="Jelaskan kondisi sampah..."
            >{{ old('description') }}</textarea>
        </div>

        <div class="form-field">
            <label for="photo">
                Foto (opsional, maksimal 3 MB)
            </label>

            <input
                class="input"
                type="file"
                id="photo"
                name="photo"
                accept="image/*"
            >

            <img
                id="photo-preview"
                class="upload-preview"
                alt="Pratinjau foto"
                hidden
            >
        </div>

        <button
            type="submit"
            class="btn btn-primary full-width"
        >
            Kirim Laporan (Demo) →
        </button>

        <p class="form-notice">
            * Form saat ini hanya melakukan validasi.
            Backend harus menyimpan laporan dan foto
            ke database serta storage.
        </p>

    </form>

    <aside class="form-side">

        <div class="panel">
            <h3>💡 Tips membuat laporan</h3>

            <p>
                Sebutkan lokasi dan kondisi sampah sejelas mungkin.
                Gunakan foto yang relevan agar petugas mudah memeriksa.
            </p>
        </div>

        <div class="panel">
            <h3>Alur tindak lanjut</h3>

            <ol>
                <li>Warga mengirim laporan</li>
                <li>Admin memverifikasi</li>
                <li>Petugas menangani</li>
                <li>Status diperbarui</li>
            </ol>
        </div>

    </aside>

</div>

@endsection
