@extends('sampahrute.layouts.dashboard')

@section('title', 'Daftar Tugas Petugas')
@section('page-title', 'Tugas Pengangkutan')

@section('content')

<div class="page-title-row">
    <div>
        <div class="eyebrow">— PEKERJAAN LAPANGAN</div>
        <h1>Daftar Tugas Petugas</h1>
        <p>Periksa jadwal dan ubah status tugas (simulasi).</p>
    </div>

    <a href="{{ route('map') }}" class="btn btn-primary">
        Lihat Peta →
    </a>
</div>

@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

<div class="task-grid">

    @foreach ($points as $p)

        <div class="panel task-card">

            <div class="task-head">
                <div>
                    <h3>{{ $p['name'] }}</h3>
                    <p>📍 {{ $p['address'] }}</p>
                </div>

                <x-sr-status :status="$p['status']" />
            </div>

            <p>🗓️ {{ $p['date'] }}</p>

            <form
                method="POST"
                action="{{ route('demo.status') }}"
            >
                @csrf

                <select
                    name="status"
                    class="input"
                    required
                >
                    <option value="menunggu"
                        @selected($p['status'] === 'menunggu')>
                        Belum Diangkut
                    </option>

                    <option value="diproses"
                        @selected($p['status'] === 'diproses')>
                        Dalam Proses
                    </option>

                    <option value="selesai"
                        @selected($p['status'] === 'selesai')>
                        Selesai
                    </option>
                </select>

                <button
                    type="submit"
                    class="btn btn-primary full-width"
                    style="margin-top:10px;"
                >
                    Perbarui Status 
                </button>
            </form>

        </div>

    @endforeach

</div>

<p class="form-notice">
    Perubahan status belum disimpan.
    Backend perlu menghubungkan tugas dengan petugas
    dan menyimpan status ke database.
</p>

@endsection
