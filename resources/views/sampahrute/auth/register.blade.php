@extends('sampahrute.layouts.public')

@section('title', 'Daftar Akun Warga — SampahRute')

@section('content')
<section class="login-background">
    <div class="login-card">

        <div class="login-symbol">🌿</div>

        <h1>Daftar Akun Warga</h1>
        <p>Buat akun untuk melaporkan dan memantau pengangkutan sampah di lingkungan Anda.</p>

        @if ($errors->any())
            <div class="alert error login-alert" role="alert">
                <strong>Pendaftaran gagal.</strong> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="login-field">
                <label for="name">Nama Lengkap</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="input"
                    placeholder="Contoh: Siti Aminah"
                    value="{{ old('name') }}"
                    required
                    maxlength="100"
                >
            </div>

            <div class="login-field">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="input"
                    placeholder="Contoh: siti.aminah"
                    value="{{ old('username') }}"
                    pattern="[A-Za-z0-9._\-]+"
                    required
                    maxlength="50"
                >
            </div>

            <div class="login-field">
                <label for="contact">Nomor WhatsApp / Telepon</label>
                <input
                    type="text"
                    id="contact"
                    name="contact"
                    class="input"
                    placeholder="Contoh: 0812-3456-7890"
                    value="{{ old('contact') }}"
                    required
                    maxlength="30"
                >
            </div>

            <div class="login-field">
                <label for="area">Kecamatan Domisili</label>
                <select id="area" name="area" class="input" required>
                    <option value="">Pilih Kecamatan</option>
                    @foreach (['Banjarsari', 'Jebres', 'Laweyan', 'Pasar Kliwon', 'Serengan'] as $d)
                        <option value="{{ $d }}" @selected(old('area') === $d)>{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <div class="login-field">
                <label for="password">Password (minimal 6 karakter)</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="input"
                    placeholder="Buat password"
                    required
                    minlength="6"
                >
            </div>

            <div class="login-field">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="input"
                    placeholder="Ulangi password"
                    required
                    minlength="6"
                >
            </div>

            <button type="submit" class="btn btn-primary full-width">
                Daftar & Masuk
            </button>
        </form>

        <p style="margin-top: 1.25rem; text-align: center; font-size: 0.92rem;">
            Sudah punya akun? <a href="{{ route('login') }}" style="font-weight: 700; color: var(--green-700, #0b7541);">Masuk di sini</a>
        </p>

        <a class="back-link" href="{{ route('home') }}">
            ← Kembali ke Beranda
        </a>

    </div>
</section>
@endsection
