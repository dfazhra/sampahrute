@props(['steps' => [], 'status' => 'menunggu'])
@php
    $flow = ['menunggu' => 'Laporan dikirim', 'diverifikasi' => 'Diverifikasi admin', 'diproses' => 'Ditugaskan ke petugas', 'selesai' => 'Selesai ditangani'];
    $done = collect($steps)->keyBy('key');
@endphp
<ol class="timeline">
    @if ($status === 'ditolak')
        @foreach ($steps as $step)
            <li class="done {{ $step['key'] === 'ditolak' ? 'is-rejected' : '' }}">
                <span class="tl-dot"></span>
                <div>
                    <strong>{{ $step['label'] }}</strong>
                    <small>{{ $step['time']->translatedFormat('d M Y, H:i') }} oleh {{ $step['by'] }}</small>
                    <p>{{ $step['note'] }}</p>
                </div>
            </li>
        @endforeach
    @else
        @foreach ($flow as $key => $label)
            @php $step = $done->get($key); @endphp
            <li class="{{ $step ? 'done' : 'todo' }}">
                <span class="tl-dot"></span>
                <div>
                    <strong>{{ $label }}</strong>
                    @if ($step)
                        <small>{{ $step['time']->translatedFormat('d M Y, H:i') }} oleh {{ $step['by'] }}</small>
                        <p>{{ $step['note'] }}</p>
                    @else
                        <small>Belum</small>
                    @endif
                </div>
            </li>
        @endforeach
    @endif
</ol>
