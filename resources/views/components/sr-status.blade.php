@props(['status' => 'menunggu', 'type' => 'lokasi'])
@php
    $labels = [
        'lokasi' => ['menunggu' => 'Belum Diangkut', 'diproses' => 'Sedang Diangkut', 'selesai' => 'Sudah Diangkut', 'libur' => 'Tidak Terjadwal'],
        'laporan' => ['menunggu' => 'Menunggu Verifikasi', 'diverifikasi' => 'Diverifikasi', 'diproses' => 'Ditangani Petugas', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'],
        'log' => ['tepat' => 'Tepat Waktu', 'terlambat' => 'Terlambat', 'terlewat' => 'Terlewat'],
    ];
@endphp
<span {{ $attributes->class(['status-badge', 'status-'.$status]) }}>{{ $labels[$type][$status] ?? ucfirst($status) }}</span>
