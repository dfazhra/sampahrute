@extends('sampahrute.layouts.dashboard')
@section('title', 'Kelola Jadwal')
@section('page-title', 'Kelola Jadwal Pengangkutan')

@section('content')
@php
    $days = \App\Support\DemoData::DAYS;
    $todayIso = now()->dayOfWeekIso;
    $l = collect($locations);
@endphp

<div class="page-title-row">
    <div>
        <h1>Jadwal Mingguan</h1>
        <p>Atur hari, jam, dan petugas untuk setiap titik pengangkutan.</p>
    </div>
    <button type="button" class="btn btn-primary" data-modal-open="schedule-form" data-fill='{"_title":"Tambah jadwal","days":[]}'>＋ Tambah jadwal</button>
</div>

<div class="officer-load">
    @foreach ($officers as $o)
        @php $mine = $l->where('officer_id', $o['id']); @endphp
        <div class="panel officer-card">
            <span class="avatar">{{ collect(explode(' ', $o['name']))->map(fn ($w) => mb_substr($w, 0, 1))->join('') }}</span>
            <div>
                <strong>{{ $o['name'] }}</strong>
                <small>{{ $o['vehicle'] }}</small>
            </div>
            <span class="load-count" title="Titik yang dipegang">{{ $mine->count() }} titik<small>{{ $mine->where('scheduled_today', true)->count() }} hari ini</small></span>
        </div>
    @endforeach
</div>

<section class="panel table-panel">
    <div class="table-scroll">
        <table class="week-table">
            <thead>
                <tr>
                    <th>Lokasi</th>
                    @foreach ($days as $num => $name)
                        <th class="{{ $num === $todayIso ? 'is-today' : '' }}">{{ $name }}@if($num === $todayIso)<small>Hari ini</small>@endif</th>
                    @endforeach
                    <th>Petugas</th>
                    <th><span class="visually-hidden">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($l as $loc)
                    <tr>
                        <td><strong>{{ $loc['name'] }}</strong><small>{{ $loc['district'] }}</small></td>
                        @foreach ($days as $num => $name)
                            <td class="week-cell {{ $num === $todayIso ? 'is-today' : '' }}">
                                @if (in_array($num, $loc['days'], true))
                                    <span class="slot" title="{{ $name }} {{ $loc['window'] }}">{{ explode('–', $loc['window'])[0] }}</span>
                                @else
                                    <span class="slot-off" aria-label="Libur">·</span>
                                @endif
                            </td>
                        @endforeach
                        <td>{{ $loc['officer']['name'] }}</td>
                        <td>
                            <button type="button" class="mini-action ghost" data-modal-open="schedule-form"
                                data-fill="{{ json_encode(["_title" => "Atur jadwal ".$loc['name'], "location_id" => $loc['id'], "officer_id" => $loc['officer_id'], "days" => $loc['days'], "window_start" => trim(explode('–', $loc['window'])[0]), "window_end" => trim(explode('–', $loc['window'])[1])]) }}">Atur</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
<p class="note-text">Jam yang ditampilkan adalah jam mulai pengangkutan. Kolom berwarna adalah hari ini.</p>
@endsection

@push('dialogs')
<dialog class="sr-modal" id="schedule-form" aria-labelledby="schedule-form-title">
    <form method="POST" action="{{ route('demo.save') }}">
        @csrf
        <input type="hidden" name="entity" value="jadwal">
        <div class="modal-head">
            <h2 id="schedule-form-title" data-fill-text="_title">Tambah jadwal</h2>
            <button type="button" class="icon-btn" data-modal-close aria-label="Tutup">×</button>
        </div>
        <div class="form-field">
            <label for="sch-loc">Lokasi <b>*</b></label>
            <select class="input" id="sch-loc" name="location_id" required>
                @foreach ($locations as $loc)
                    <option value="{{ $loc['id'] }}">{{ $loc['name'] }} — {{ $loc['district'] }}</option>
                @endforeach
            </select>
        </div>
        <fieldset class="form-field">
            <legend>Hari pengangkutan <b>*</b></legend>
            <div class="day-picker">
                @foreach ($days as $num => $name)
                    <label><input type="checkbox" name="days[]" value="{{ $num }}"><span>{{ mb_substr($name, 0, 3) }}</span></label>
                @endforeach
            </div>
        </fieldset>
        <div class="form-row">
            <div class="form-field">
                <label for="sch-start">Jam mulai <b>*</b></label>
                <input class="input" type="time" id="sch-start" name="window_start" required value="06:00">
            </div>
            <div class="form-field">
                <label for="sch-end">Jam selesai <b>*</b></label>
                <input class="input" type="time" id="sch-end" name="window_end" required value="08:00">
            </div>
        </div>
        <div class="form-field">
            <label for="sch-officer">Petugas <b>*</b></label>
            <select class="input" id="sch-officer" name="officer_id" required>
                @foreach ($officers as $o)
                    <option value="{{ $o['id'] }}">{{ $o['name'] }} — {{ $o['area'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-soft" data-modal-close>Batal</button>
            <button type="submit" class="btn btn-primary">Simpan jadwal</button>
        </div>
    </form>
</dialog>
@endpush
