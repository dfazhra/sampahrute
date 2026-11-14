@extends(isset($role) ? 'sampahrute.layouts.dashboard' : 'sampahrute.layouts.public')
@section('title', 'Daftar Lokasi')
@section('page-title', 'Daftar Lokasi Sampah')

@section('content')
<section class="{{ isset($role) ? '' : 'container section' }}">
    <div class="page-title-row">
        <div>
            <h1>Lokasi Sampah Terdaftar</h1>
            <p>Titik pengangkutan beserta jadwal dan status hari ini.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('map') }}">🗺 Lihat peta</a>
    </div>
    <div class="panel table-panel" data-filter-scope>
        <div class="toolbar">
            <input type="search" class="input" placeholder="Cari lokasi atau kecamatan" data-filter-search aria-label="Cari lokasi">
        </div>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Lokasi</th><th>Kecamatan</th><th>Jadwal</th><th>Angkut berikutnya</th><th>Status hari ini</th></tr></thead>
                <tbody>
                    @foreach ($locations as $loc)
                        <tr data-row data-search="{{ strtolower($loc['name'].' '.$loc['district'].' '.$loc['address']) }}">
                            <td><strong>{{ $loc['name'] }}</strong><small>{{ $loc['address'] }}</small></td>
                            <td>{{ $loc['district'] }}</td>
                            <td>{{ $loc['days_label'] }}<small>{{ $loc['window'] }}</small></td>
                            <td>{{ $loc['next']->isToday() ? 'Hari ini' : $loc['next']->translatedFormat('l, d M') }}</td>
                            <td><x-sr-status :status="$loc['status']" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <x-sr-empty data-empty hidden />
        </div>
    </div>
</section>
@endsection
