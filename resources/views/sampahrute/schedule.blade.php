@extends(isset($role) ? 'sampahrute.layouts.dashboard' : 'sampahrute.layouts.public')
@section('title', 'Jadwal Pengangkutan')
@section('page-title', 'Jadwal Pengangkutan')

@section('content')
@php
    $days = \App\Support\DemoData::DAYS;
    $todayIso = now()->dayOfWeekIso;
@endphp
<section class="{{ isset($role) ? '' : 'container section' }}">
    <div class="page-title-row">
        <div>
            <h1>Jadwal Pengangkutan</h1>
            <p>Pilih hari untuk melihat titik mana saja yang diangkut dan jam berapa.</p>
        </div>
    </div>

    <div class="day-tabs" role="tablist" aria-label="Pilih hari">
        @foreach ($days as $num => $name)
            <button type="button" role="tab" class="{{ $num === $todayIso ? 'active' : '' }}" data-day-tab="{{ $num }}" aria-selected="{{ $num === $todayIso ? 'true' : 'false' }}">
                {{ $name }}
                <small>{{ $num === $todayIso ? 'Hari ini' : now()->startOfWeek()->addDays($num - 1)->format('d/m') }}</small>
            </button>
        @endforeach
    </div>

    @foreach ($days as $num => $name)
        @php $list = collect($locations)->filter(fn ($l) => in_array($num, $l['days'], true))->sortBy('window'); @endphp
        <div class="schedule-grid" data-day-panel="{{ $num }}" @if($num !== $todayIso) hidden @endif>
            @forelse ($list as $loc)
                <article class="panel schedule-card">
                    <div class="schedule-head">
                        <span class="schedule-time">{{ $loc['window'] }}</span>
                        @if ($num === $todayIso)<x-sr-status :status="$loc['status']" />@endif
                    </div>
                    <h3>{{ $loc['name'] }}</h3>
                    <p>📍 {{ $loc['district'] }} · {{ $loc['type'] }}</p>
                    <div class="schedule-date"><span>Petugas</span><strong>{{ $loc['officer']['name'] }}</strong></div>
                    <a href="{{ route('map') }}#{{ $loc['id'] }}" class="text-link">Lihat lokasi di peta</a>
                </article>
            @empty
                <x-sr-empty icon="📭" title="Tidak ada pengangkutan" text="Tidak ada titik yang dijadwalkan pada hari {{ $name }}." />
            @endforelse
        </div>
    @endforeach
    <p class="note-text">Jadwal bersifat ilustrasi untuk prototype.</p>
</section>
@endsection
