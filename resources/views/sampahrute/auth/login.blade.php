@extends('sampahrute.layouts.public')

@section('title', 'Login SampahRute')

@section('content')
<section class="login-background">
    <div class="login-card">

        <div class="login-symbol">♻</div>

        <h1>Selamat Datang Kembali!</h1>
        <p>Masuk menggunakan akun SampahRute Anda.</p>

        {{-- Pesan error --}}
        @if ($errors->any())
            <div class="alert error login-alert" role="alert">
                <strong>Login gagal.</strong> {{ $errors->first() }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert error login-alert" role="alert">{{ session('error') }}</div>
        @endif

        {{-- Form login --}}
        <form method="POST" action="{{ route('demo.signin') }}">
            @csrf

            <div class="login-field">
                <label for="email">Username</label>

                <input
                    type="text"
                    id="email"
                    name="email"
                    class="input"
                    placeholder="Contoh: admin"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="login-field">
                <label for="password">Password</label>

                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        id="togglePassword"
                        aria-label="Tampilkan password"
                        aria-pressed="false"
                    >
                        Lihat
                    </button>
                </div>
            </div>

            <div class="login-options">
                <label class="remember-label">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                    >
                    Ingat saya
                </label>
            </div>

            <button
                type="submit"
                class="btn btn-primary full-width"
            >
                Masuk
            </button>
        </form>

        <div class="demo-accounts">
            <p>Akun demo untuk presentasi — klik untuk mengisi otomatis:</p>
            <div>
                @foreach (['admin' => ['🛠', 'Admin'], 'petugas' => ['🚚', 'Petugas'], 'warga' => ['🏠', 'Warga']] as $user => [$icon, $label])
                    <button type="button" class="demo-chip" data-demo-user="{{ $user }}" data-demo-pass="{{ $user }}123">
                        <span aria-hidden="true">{{ $icon }}</span>{{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <p style="margin-top: 1rem; text-align: center; font-size: 0.92rem;">
            Warga baru? <a href="{{ route('register') }}" style="font-weight: 700; color: var(--green-700, #0b7541);">Daftar akun warga</a>
        </p>

        <a class="back-link" href="{{ route('home') }}">
            ← Kembali ke Beranda
        </a>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');

    if (password && toggle) {
        toggle.addEventListener('click', function () {
            const visible = password.type === 'text';

            password.type = visible ? 'password' : 'text';
            toggle.textContent = visible ? 'Lihat' : 'Sembunyikan';
            toggle.setAttribute('aria-pressed', String(!visible));
            toggle.setAttribute(
                'aria-label',
                visible ? 'Tampilkan password' : 'Sembunyikan password'
            );
        });
    }
});
</script>
@endsection
