<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Sumber data contoh untuk prototype frontend SampahRute.
 *
 * Semua data di sini adalah ilustrasi. Perubahan dari form demo
 * (ubah status, laporan baru, konfirmasi angkut) disimpan sementara
 * di cache Laravel, jadi terlihat oleh semua peran (admin, petugas,
 * warga) selama presentasi dan bisa direset dari sidebar.
 * Saat backend dibangun, kelas ini diganti dengan Model + database.
 */
class DemoData
{
    public const DAYS = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    public const REPORT_STATUSES = ['menunggu', 'diverifikasi', 'diproses', 'selesai', 'ditolak'];

    public const CATEGORIES = [
        'belum_diangkut' => 'Belum diangkut',
        'penumpukan' => 'Penumpukan sampah',
        'kapasitas' => 'Melebihi kapasitas',
        'terlambat' => 'Pengangkutan terlambat',
        'lainnya' => 'Lainnya',
    ];

    /** Akun demo yang sedang "login" untuk tiap peran. */
    public const DEMO_USERS = [
        'admin' => ['name' => 'Rahmat Hidayat', 'title' => 'Pengelola Lingkungan'],
        'petugas' => ['name' => 'Agus Wibowo', 'title' => 'Petugas Pengangkut', 'officer_id' => 'PTG-01'],
        'warga' => ['name' => 'Andi Pratama', 'title' => 'Warga Banjarsari'],
    ];

    private Carbon $today;

    private const CACHE_KEY = 'sampahrute_demo_state';

    public function __construct()
    {
        $this->today = Carbon::today();
    }

    /* ------------------------------------------------------------------
     | Petugas
     * -----------------------------------------------------------------*/

    public function officers(): array
    {
        return [
            'PTG-01' => ['id' => 'PTG-01', 'name' => 'Agus Wibowo', 'phone' => '0812-2600-1101', 'area' => 'Banjarsari & Laweyan', 'vehicle' => 'Truk AD 8123 KL'],
            'PTG-02' => ['id' => 'PTG-02', 'name' => 'Joko Susilo', 'phone' => '0812-2600-1102', 'area' => 'Jebres & Pasar Kliwon', 'vehicle' => 'Truk AD 8456 KL'],
            'PTG-03' => ['id' => 'PTG-03', 'name' => 'Wahyu Hidayat', 'phone' => '0812-2600-1103', 'area' => 'Serengan', 'vehicle' => 'Pikap AD 1947 TR'],
            'PTG-04' => ['id' => 'PTG-04', 'name' => 'Bambang Purnomo', 'phone' => '0812-2600-1104', 'area' => 'Cadangan', 'vehicle' => 'Motor sampah AD 3310 RS'],
        ];
    }

    /* ------------------------------------------------------------------
     | Lokasi / titik pengangkutan
     * -----------------------------------------------------------------*/

    private function baseLocations(): array
    {
        $daily = [1, 2, 3, 4, 5, 6, 7];

        return [
            ['id' => 'LOK-01', 'name' => 'TPS Manahan', 'type' => 'TPS', 'district' => 'Banjarsari', 'village' => 'Manahan', 'address' => 'Jl. Adi Sucipto, Manahan', 'lat' => -7.5568, 'lng' => 110.8004, 'capacity' => 6, 'fill' => 20, 'days' => $daily, 'window' => '06:00–08:00', 'officer_id' => 'PTG-01', 'status' => 'selesai'],
            ['id' => 'LOK-02', 'name' => 'TPS Jebres', 'type' => 'TPS', 'district' => 'Jebres', 'village' => 'Jebres', 'address' => 'Jl. Ir. Sutami, Jebres', 'lat' => -7.5596, 'lng' => 110.8481, 'capacity' => 8, 'fill' => 65, 'days' => $daily, 'window' => '06:30–08:30', 'officer_id' => 'PTG-02', 'status' => 'diproses'],
            ['id' => 'LOK-03', 'name' => 'TPS Pasar Gede', 'type' => 'Depo', 'district' => 'Jebres', 'village' => 'Sudiroprajan', 'address' => 'Jl. Urip Sumoharjo, Sudiroprajan', 'lat' => -7.5704, 'lng' => 110.8323, 'capacity' => 10, 'fill' => 92, 'days' => $daily, 'window' => '05:30–07:30', 'officer_id' => 'PTG-02', 'status' => 'menunggu'],
            ['id' => 'LOK-04', 'name' => 'TPS Laweyan', 'type' => 'TPS', 'district' => 'Laweyan', 'village' => 'Laweyan', 'address' => 'Jl. Dr. Rajiman, Laweyan', 'lat' => -7.5709, 'lng' => 110.7976, 'capacity' => 6, 'fill' => 15, 'days' => $daily, 'window' => '07:00–09:00', 'officer_id' => 'PTG-01', 'status' => 'selesai'],
            ['id' => 'LOK-05', 'name' => 'TPS Serengan', 'type' => 'TPS', 'district' => 'Serengan', 'village' => 'Serengan', 'address' => 'Jl. Veteran, Serengan', 'lat' => -7.5922, 'lng' => 110.8192, 'capacity' => 5, 'fill' => 80, 'days' => [1, 3, 5, 7], 'window' => '07:00–09:00', 'officer_id' => 'PTG-03', 'status' => 'menunggu'],
            ['id' => 'LOK-06', 'name' => 'TPS Banyuanyar', 'type' => 'TPS', 'district' => 'Banjarsari', 'village' => 'Banyuanyar', 'address' => 'Jl. Sumpah Pemuda, Banyuanyar', 'lat' => -7.5452, 'lng' => 110.8141, 'capacity' => 6, 'fill' => 70, 'days' => $daily, 'window' => '08:00–10:00', 'officer_id' => 'PTG-01', 'status' => 'menunggu'],
            ['id' => 'LOK-07', 'name' => 'Depo Mojosongo', 'type' => 'Depo', 'district' => 'Jebres', 'village' => 'Mojosongo', 'address' => 'Jl. Tangkuban Perahu, Mojosongo', 'lat' => -7.5368, 'lng' => 110.8392, 'capacity' => 12, 'fill' => 35, 'days' => [2, 4, 6], 'window' => '06:00–08:00', 'officer_id' => 'PTG-02', 'status' => 'selesai'],
            ['id' => 'LOK-08', 'name' => 'Kontainer Kerten', 'type' => 'Kontainer', 'district' => 'Laweyan', 'village' => 'Kerten', 'address' => 'Jl. Slamet Riyadi, Kerten', 'lat' => -7.5617, 'lng' => 110.7838, 'capacity' => 4, 'fill' => 55, 'days' => $daily, 'window' => '09:00–11:00', 'officer_id' => 'PTG-01', 'status' => 'diproses'],
            ['id' => 'LOK-09', 'name' => 'TPS Semanggi', 'type' => 'TPS', 'district' => 'Pasar Kliwon', 'village' => 'Semanggi', 'address' => 'Jl. Kyai Mojo, Semanggi', 'lat' => -7.5853, 'lng' => 110.8412, 'capacity' => 6, 'fill' => 48, 'days' => $daily, 'window' => '06:00–08:00', 'officer_id' => 'PTG-02', 'status' => 'menunggu'],
        ];
    }

    public function locations(): array
    {
        $officers = $this->officers();
        $overrides = $this->get('location_status', []);
        $todayIso = $this->today->dayOfWeekIso;
        $result = [];

        foreach ($this->baseLocations() as $loc) {
            $loc['status'] = $overrides[$loc['id']] ?? $loc['status'];
            $loc['scheduled_today'] = in_array($todayIso, $loc['days'], true);

            if (! $loc['scheduled_today']) {
                $loc['status'] = 'libur';
            }

            if ($loc['status'] === 'selesai') {
                $loc['fill'] = min($loc['fill'], 10);
            }

            $loc['officer'] = $officers[$loc['officer_id']] ?? null;
            $loc['days_label'] = count($loc['days']) === 7
                ? 'Setiap hari'
                : implode(', ', array_map(fn ($d) => self::DAYS[$d], $loc['days']));
            $loc['next'] = $this->nextCollection($loc);
            $loc['date'] = $loc['next']->translatedFormat('d M Y');
            $loc['last_collected'] = $this->lastCollected($loc);
            $result[$loc['id']] = $loc;
        }

        return $result;
    }

    public function location(string $id): ?array
    {
        return $this->locations()[$id] ?? null;
    }

    /** Data ringkas lokasi untuk peta (dikirim ke JavaScript sebagai JSON). */
    public function mapPoints(): array
    {
        return array_values(array_map(fn ($l) => [
            'id' => $l['id'],
            'name' => $l['name'],
            'type' => $l['type'],
            'district' => $l['district'],
            'address' => $l['address'],
            'lat' => $l['lat'],
            'lng' => $l['lng'],
            'status' => $l['status'],
            'fill' => $l['fill'],
            'date' => $l['date'],
            'window' => $l['window'],
            'days' => $l['days_label'],
            'officer' => $l['officer']['name'] ?? '-',
        ], $this->locations()));
    }

    private function nextCollection(array $loc): Carbon
    {
        $start = ($loc['scheduled_today'] && $loc['status'] !== 'selesai') ? 0 : 1;

        for ($i = $start; $i <= 7; $i++) {
            $day = $this->today->copy()->addDays($i);
            if (in_array($day->dayOfWeekIso, $loc['days'], true)) {
                return $day;
            }
        }

        return $this->today->copy();
    }

    private function lastCollected(array $loc): Carbon
    {
        if ($loc['status'] === 'selesai') {
            return $this->today->copy();
        }

        for ($i = 1; $i <= 7; $i++) {
            $day = $this->today->copy()->subDays($i);
            if (in_array($day->dayOfWeekIso, $loc['days'], true)) {
                return $day;
            }
        }

        return $this->today->copy()->subWeek();
    }

    /* ------------------------------------------------------------------
     | Laporan warga
     * -----------------------------------------------------------------*/

    private function baseReports(): array
    {
        return [
            ['id' => 'SR-001', 'title' => 'Sampah belum diangkut dua hari', 'category' => 'belum_diangkut', 'location_id' => 'LOK-03', 'resident' => 'Andi Pratama', 'days_ago' => 0, 'time' => '07:15', 'status' => 'menunggu', 'priority' => 'tinggi', 'officer_id' => null, 'photo' => 'assets/hero-collection.jpg', 'description' => 'Tumpukan kantong sampah di depan depo sudah meluber ke bahu jalan sejak kemarin pagi. Banyak lalat dan mulai berbau.'],
            ['id' => 'SR-002', 'title' => 'Penumpukan sampah rumah tangga', 'category' => 'penumpukan', 'location_id' => 'LOK-02', 'resident' => 'Rina Putri', 'days_ago' => 1, 'time' => '16:40', 'status' => 'diproses', 'priority' => 'sedang', 'officer_id' => 'PTG-02', 'photo' => null, 'description' => 'Sampah rumah tangga menumpuk di sisi timur TPS, sebagian kantong sobek dimakan kucing.'],
            ['id' => 'SR-003', 'title' => 'Kontainer melebihi kapasitas', 'category' => 'kapasitas', 'location_id' => 'LOK-01', 'resident' => 'Budi Santoso', 'days_ago' => 3, 'time' => '06:20', 'status' => 'selesai', 'priority' => 'sedang', 'officer_id' => 'PTG-01', 'photo' => null, 'description' => 'Kontainer penuh sehingga warga menaruh sampah di luar kontainer.'],
            ['id' => 'SR-004', 'title' => 'Pengangkutan terlambat', 'category' => 'terlambat', 'location_id' => 'LOK-05', 'resident' => 'Dewi Lestari', 'days_ago' => 1, 'time' => '10:05', 'status' => 'diverifikasi', 'priority' => 'sedang', 'officer_id' => null, 'photo' => null, 'description' => 'Jadwal angkut jam 7 pagi tapi sampai jam 10 truk belum datang.'],
            ['id' => 'SR-005', 'title' => 'Sampah berserakan di sekitar TPS', 'category' => 'penumpukan', 'location_id' => 'LOK-06', 'resident' => 'Andi Pratama', 'days_ago' => 2, 'time' => '18:10', 'status' => 'diproses', 'priority' => 'tinggi', 'officer_id' => 'PTG-01', 'photo' => 'assets/surakarta.jpg', 'description' => 'Sampah tercecer hingga ke saluran air di samping TPS. Khawatir menyumbat saat hujan.'],
            ['id' => 'SR-006', 'title' => 'Bau menyengat dari kontainer', 'category' => 'lainnya', 'location_id' => 'LOK-08', 'resident' => 'Andi Pratama', 'days_ago' => 5, 'time' => '12:30', 'status' => 'selesai', 'priority' => 'rendah', 'officer_id' => 'PTG-01', 'photo' => null, 'description' => 'Bau dari kontainer tercium sampai ke rumah warga, sepertinya ada sampah basah yang lama tidak diangkut.'],
            ['id' => 'SR-007', 'title' => 'Sampah belum diangkut', 'category' => 'belum_diangkut', 'location_id' => 'LOK-03', 'resident' => 'Sari Wulandari', 'days_ago' => 0, 'time' => '08:02', 'status' => 'ditolak', 'priority' => 'rendah', 'officer_id' => null, 'photo' => null, 'description' => 'Depo Pasar Gede penuh dan belum diangkut.', 'reject_reason' => 'Laporan sama dengan SR-001 yang sedang ditangani.'],
            ['id' => 'SR-008', 'title' => 'Sampah tidak diangkut sejak Kamis', 'category' => 'belum_diangkut', 'location_id' => 'LOK-07', 'resident' => 'Hendra Gunawan', 'days_ago' => 0, 'time' => '09:40', 'status' => 'menunggu', 'priority' => 'sedang', 'officer_id' => null, 'photo' => null, 'description' => 'Depo terlihat penuh dan belum ada truk datang sejak Kamis.'],
        ];
    }

    public function reports(): array
    {
        $statusOverrides = $this->get('report_status', []);
        $officerOverrides = $this->get('report_officer', []);
        $reasonOverrides = $this->get('report_reason', []);
        $locations = $this->locations();
        $officers = $this->officers();

        $all = array_merge($this->get('new_reports', []), $this->baseReports());
        $result = [];

        foreach ($all as $r) {
            $id = $r['id'];
            $r['status'] = $statusOverrides[$id] ?? $r['status'];
            $r['officer_id'] = array_key_exists($id, $officerOverrides) ? $officerOverrides[$id] : $r['officer_id'];
            $r['reject_reason'] = $reasonOverrides[$id] ?? ($r['reject_reason'] ?? null);
            $r['created'] = $this->today->copy()->subDays($r['days_ago'])->setTimeFromTimeString($r['time']);
            $r['date'] = $r['created']->translatedFormat('d M Y');
            $r['location'] = $locations[$r['location_id']]['name'] ?? '-';
            $r['location_data'] = $locations[$r['location_id']] ?? null;
            $r['district'] = $locations[$r['location_id']]['district'] ?? '-';
            $r['officer'] = $r['officer_id'] ? ($officers[$r['officer_id']] ?? null) : null;
            $r['category_label'] = self::CATEGORIES[$r['category']] ?? 'Lainnya';
            $r['timeline'] = $this->timeline($r);
            $result[$id] = $r;
        }

        uasort($result, fn ($a, $b) => $b['created'] <=> $a['created']);

        return $result;
    }

    public function report(string $id): ?array
    {
        return $this->reports()[$id] ?? null;
    }

    public function reportsFor(string $role): array
    {
        $reports = $this->reports();

        return match ($role) {
            'warga' => array_filter($reports, fn ($r) => $r['resident'] === self::DEMO_USERS['warga']['name']),
            'petugas' => array_filter($reports, fn ($r) => $r['officer_id'] === self::DEMO_USERS['petugas']['officer_id']),
            default => $reports,
        };
    }

    /** Riwayat status sebuah laporan, untuk tampilan timeline. */
    private function timeline(array $r): array
    {
        $t = $r['created']->copy();
        $steps = [[
            'key' => 'menunggu', 'label' => 'Laporan dikirim', 'by' => $r['resident'],
            'time' => $t->copy(), 'note' => 'Laporan masuk dan menunggu verifikasi admin.',
        ]];

        if ($r['status'] === 'ditolak') {
            $steps[] = ['key' => 'ditolak', 'label' => 'Laporan ditolak', 'by' => 'Admin', 'time' => $t->copy()->addMinutes(45), 'note' => $r['reject_reason'] ?? 'Laporan tidak dapat diproses.'];

            return $steps;
        }

        $order = ['menunggu' => 0, 'diverifikasi' => 1, 'diproses' => 2, 'selesai' => 3];
        $level = $order[$r['status']] ?? 0;

        if ($level >= 1) {
            $steps[] = ['key' => 'diverifikasi', 'label' => 'Diverifikasi admin', 'by' => 'Admin', 'time' => $t->copy()->addMinutes(40), 'note' => 'Lokasi dan kondisi laporan sudah dicek.'];
        }
        if ($level >= 2) {
            $steps[] = ['key' => 'diproses', 'label' => 'Ditugaskan ke petugas', 'by' => 'Admin', 'time' => $t->copy()->addMinutes(75), 'note' => 'Ditangani oleh '.($r['officer']['name'] ?? 'petugas').'.'];
        }
        if ($level >= 3) {
            $steps[] = ['key' => 'selesai', 'label' => 'Selesai ditangani', 'by' => $r['officer']['name'] ?? 'Petugas', 'time' => $t->copy()->addHours(5), 'note' => 'Sampah sudah diangkut dan area dibersihkan.'];
        }

        return $steps;
    }

    /* ------------------------------------------------------------------
     | Riwayat pengangkutan (monitoring tanggal angkut)
     * -----------------------------------------------------------------*/

    public function logs(): array
    {
        $logs = [];
        $locations = $this->locations();
        $officers = $this->officers();

        foreach ($this->get('today_logs', []) as $log) {
            $log['date'] = Carbon::parse($log['date']);
            $logs[] = $log;
        }

        foreach ($locations as $loc) {
            for ($d = 0; $d <= 13; $d++) {
                $day = $this->today->copy()->subDays($d);
                if (! in_array($day->dayOfWeekIso, $loc['days'], true)) {
                    continue;
                }

                [$start, $end] = array_map('trim', explode('–', $loc['window']));
                $seed = crc32($loc['id'].$day->toDateString());

                if ($d === 0) {
                    // Hari ini: hanya lokasi berstatus selesai yang sudah tercatat.
                    if ($loc['status'] !== 'selesai' || $this->hasTodayLog($loc['id'])) {
                        continue;
                    }
                    $result = 'tepat';
                } else {
                    $roll = $seed % 12;
                    $result = $roll === 0 ? 'terlewat' : ($roll <= 2 ? 'terlambat' : 'tepat');
                }

                $actual = null;
                if ($result !== 'terlewat') {
                    $base = Carbon::parse($day->toDateString().' '.$start);
                    $actual = $result === 'terlambat'
                        ? Carbon::parse($day->toDateString().' '.$end)->addMinutes(15 + $seed % 70)
                        : $base->copy()->addMinutes($seed % 100);
                }

                $logs[] = [
                    'id' => 'LOG-'.$loc['id'].'-'.$day->format('md'),
                    'date' => $day,
                    'location_id' => $loc['id'],
                    'location' => $loc['name'],
                    'district' => $loc['district'],
                    'officer_id' => $loc['officer_id'],
                    'officer' => $officers[$loc['officer_id']]['name'] ?? '-',
                    'window' => $loc['window'],
                    'actual' => $actual?->format('H:i'),
                    'volume' => $result === 'terlewat' ? null : round($loc['capacity'] * (0.45 + ($seed % 45) / 100), 1),
                    'result' => $result,
                    'note' => $result === 'terlewat' ? 'Truk dialihkan ke lokasi lain.' : ($result === 'terlambat' ? 'Antrean di TPA Putri Cempo.' : ''),
                ];
            }
        }

        usort($logs, fn ($a, $b) => [$b['date'], $b['actual'] ?? ''] <=> [$a['date'], $a['actual'] ?? '']);

        return $logs;
    }

    private function hasTodayLog(string $locationId): bool
    {
        foreach ($this->get('today_logs', []) as $log) {
            if ($log['location_id'] === $locationId) {
                return true;
            }
        }

        return false;
    }

    /* ------------------------------------------------------------------
     | Pengguna & notifikasi
     * -----------------------------------------------------------------*/

    public function users(): array
    {
        $users = [
            ['name' => 'Rahmat Hidayat', 'username' => 'admin', 'role' => 'admin', 'contact' => 'rahmat@sampahrute.id', 'area' => 'Semua wilayah', 'active' => true],
            ['name' => 'Lina Marlina', 'username' => 'lina.admin', 'role' => 'admin', 'contact' => 'lina@sampahrute.id', 'area' => 'Semua wilayah', 'active' => true],
        ];

        foreach ($this->officers() as $o) {
            $users[] = ['name' => $o['name'], 'username' => strtolower(strtok($o['name'], ' ')), 'role' => 'petugas', 'contact' => $o['phone'], 'area' => $o['area'], 'active' => $o['id'] !== 'PTG-04'];
        }

        foreach ([['Andi Pratama', 'Banjarsari'], ['Rina Putri', 'Jebres'], ['Budi Santoso', 'Banjarsari'], ['Dewi Lestari', 'Serengan'], ['Sari Wulandari', 'Jebres'], ['Hendra Gunawan', 'Jebres']] as [$name, $area]) {
            $users[] = ['name' => $name, 'username' => strtolower(str_replace(' ', '.', $name)), 'role' => 'warga', 'contact' => '08xx-xxxx-'.substr((string) crc32($name), 0, 4), 'area' => $area, 'active' => true];
        }

        return $users;
    }

    public function notifications(?string $role): array
    {
        $reports = $this->reports();
        $locations = $this->locations();
        $items = [];

        if ($role === 'admin') {
            foreach (array_filter($reports, fn ($r) => $r['status'] === 'menunggu') as $r) {
                $items[] = ['icon' => '📄', 'text' => "Laporan baru {$r['id']} di {$r['location']}", 'time' => $r['created']->diffForHumans(), 'route' => route('admin.reports').'#'.$r['id']];
            }
            foreach (array_filter($locations, fn ($l) => $l['fill'] >= 85) as $l) {
                $items[] = ['icon' => '⚠️', 'text' => "{$l['name']} hampir penuh ({$l['fill']}%)", 'time' => 'Hari ini', 'route' => route('admin.locations')];
            }
        } elseif ($role === 'petugas') {
            foreach ($this->reportsFor('petugas') as $r) {
                if ($r['status'] === 'diproses') {
                    $items[] = ['icon' => '📌', 'text' => "Tugas laporan {$r['id']}: {$r['location']}", 'time' => $r['created']->diffForHumans(), 'route' => route('petugas.tasks').'#laporan'];
                }
            }
            $pending = count(array_filter($locations, fn ($l) => $l['officer_id'] === 'PTG-01' && in_array($l['status'], ['menunggu', 'diproses'], true)));
            if ($pending) {
                $items[] = ['icon' => '🚚', 'text' => "{$pending} titik belum selesai di rute hari ini", 'time' => 'Hari ini', 'route' => route('petugas.tasks')];
            }
        } elseif ($role === 'warga') {
            foreach ($this->reportsFor('warga') as $r) {
                $last = end($r['timeline']);
                $items[] = ['icon' => '🔔', 'text' => "{$r['id']}: {$last['label']}", 'time' => $last['time']->diffForHumans(), 'route' => route('warga.show', $r['id'])];
            }
        }

        return array_slice($items, 0, 6);
    }

    /* ------------------------------------------------------------------
     | Aksi demo (disimpan sementara di cache file)
     * -----------------------------------------------------------------*/

    public function setReportStatus(string $id, string $status, ?string $officerId = null, ?string $reason = null): void
    {
        $this->put("report_status.$id", $status);
        if ($officerId !== null) {
            $this->put("report_officer.$id", $officerId ?: null);
        }
        if ($reason) {
            $this->put("report_reason.$id", $reason);
        }
    }

    public function setLocationStatus(string $id, string $status, array $log = []): void
    {
        $this->put("location_status.$id", $status);

        if ($status === 'selesai' && $log) {
            $logs = $this->get('today_logs', []);
            $logs[] = $log;
            $this->put('today_logs', $logs);
        }
    }

    public function addReport(array $data): string
    {
        $new = $this->get('new_reports', []);
        $id = sprintf('SR-%03d', 9 + count($new));
        array_unshift($new, array_merge($data, [
            'id' => $id,
            'resident' => self::DEMO_USERS['warga']['name'],
            'days_ago' => 0,
            'time' => now()->format('H:i'),
            'status' => 'menunggu',
            'priority' => 'sedang',
            'officer_id' => null,
            'photo' => null,
        ]));
        $this->put('new_reports', $new);

        return $id;
    }

    public function reset(): void
    {
        Cache::store('file')->forget(self::CACHE_KEY);
    }

    private function get(string $key, mixed $default = null): mixed
    {
        return Arr::get(Cache::store('file')->get(self::CACHE_KEY, []), $key, $default) ?? $default;
    }

    private function put(string $key, mixed $value): void
    {
        $state = Cache::store('file')->get(self::CACHE_KEY, []);
        Arr::set($state, $key, $value);
        Cache::store('file')->forever(self::CACHE_KEY, $state);
    }
}
