@extends('sampahrute.layouts.dashboard')
@section('title', 'Pengguna')
@section('page-title', 'Kelola Pengguna')

@section('content')
@php
    $u = collect($users);
    $roleLabels = ['all' => 'Semua', 'admin' => 'Admin', 'petugas' => 'Petugas', 'warga' => 'Warga'];
@endphp

<div class="page-title-row">
    <div>
        <h1>Pengguna</h1>
        <p>Kelola akun admin, petugas, dan warga yang memakai SampahRute.</p>
    </div>
    <button type="button" class="btn btn-primary" data-modal-open="user-form" data-fill='{"_title":"Tambah pengguna"}'>＋ Tambah pengguna</button>
</div>

<section class="panel table-panel" data-filter-scope>
    <div class="toolbar">
        <input type="search" class="input" placeholder="Cari nama, username, atau kontak" data-filter-search aria-label="Cari pengguna">
    </div>
    <div class="filter-tabs tabs-pad">
        @foreach ($roleLabels as $key => $label)
            <button type="button" class="{{ $key === 'all' ? 'active' : '' }}" data-filter-status="{{ $key }}">{{ $label }} <span>{{ $key === 'all' ? $u->count() : $u->where('role', $key)->count() }}</span></button>
        @endforeach
    </div>
    <div class="table-scroll">
        <table>
            <thead><tr><th>Nama</th><th>Peran</th><th>Kontak</th><th>Wilayah</th><th>Status</th><th><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($u as $user)
                    <tr data-row data-status="{{ $user['role'] }}" data-search="{{ strtolower($user['name'].' '.$user['username'].' '.$user['contact']) }}">
                        <td>
                            <div class="user-cell">
                                <span class="avatar small">{{ collect(explode(' ', $user['name']))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('') }}</span>
                                <span><strong>{{ $user['name'] }}</strong><small>{{ '@'.$user['username'] }}</small></span>
                            </div>
                        </td>
                        <td><span class="role-chip role-{{ $user['role'] }}">{{ ucfirst($user['role']) }}</span></td>
                        <td>{{ $user['contact'] }}</td>
                        <td>{{ $user['area'] }}</td>
                        <td>@if ($user['active'])<span class="dot-label on">Aktif</span>@else<span class="dot-label off">Nonaktif</span>@endif</td>
                        <td><div class="row-actions">
                            <button type="button" class="mini-action ghost" data-modal-open="user-form" data-fill="{{ json_encode(["_title" => "Ubah ".$user['name'], "name" => $user['name'], "username" => $user['username'], "role" => $user['role']]) }}">Ubah</button>
                            <button type="button" class="mini-action danger" data-modal-open="delete-form" data-fill="{{ json_encode(["target" => $user['name'], "_title" => "Hapus akun ".$user['name']."?"]) }}">Hapus</button>
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
<dialog class="sr-modal" id="user-form" aria-labelledby="user-form-title">
    <form method="POST" action="{{ route('demo.save') }}">
        @csrf
        <input type="hidden" name="entity" value="pengguna">
        <div class="modal-head">
            <h2 id="user-form-title" data-fill-text="_title">Tambah pengguna</h2>
            <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">×</button>
        </div>
        <div class="form-field">
            <label for="usr-name">Nama lengkap <b>*</b></label>
            <input class="input" id="usr-name" name="name" required maxlength="100">
        </div>
        <div class="form-row">
            <div class="form-field">
                <label for="usr-username">Username <b>*</b></label>
                <input class="input" id="usr-username" name="username" required pattern="[A-Za-z0-9._\-]+" maxlength="50">
            </div>
            <div class="form-field">
                <label for="usr-role">Peran <b>*</b></label>
                <select class="input" id="usr-role" name="role" required>
                    <option value="warga">Warga</option>
                    <option value="petugas">Petugas</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <p class="form-notice">Password awal dikirim ke pengguna dan wajib diganti saat login pertama.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-soft" data-modal-close>Batal</button>
            <button type="submit" class="btn btn-primary">Simpan pengguna</button>
        </div>
    </form>
</dialog>
@include('sampahrute.partials.delete-dialog')
@endpush
