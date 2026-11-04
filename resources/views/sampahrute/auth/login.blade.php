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
            <div style="
                background:#fef2f2;
                border:1px solid #fecaca;
                color:#b91c1c;
                padding:12px;
                border-radius:10px;
                margin:15px 0;
            ">
                <strong>Login gagal!</strong>
                <ul style="margin:8px 0 0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
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
                Masuk →
            </button>
        </form>

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
