<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Officer;
use App\Models\PickupLog;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');
        PickupLog::truncate();
        Report::truncate();
        Location::truncate();
        Officer::truncate();
        User::truncate();
        DB::statement('PRAGMA foreign_keys = ON;');

        $today = Carbon::today();

        // 1. Seed Users (Admin)
        $adminPass = Hash::make('admin123');
        $petugasPass = Hash::make('petugas123');
        $wargaPass = Hash::make('warga123');

        User::create([
            'name' => 'Rahmat Hidayat',
            'username' => 'admin',
            'email' => 'rahmat@sampahrute.id',
            'password' => $adminPass,
            'role' => 'admin',
            'title' => 'Pengelola Lingkungan',
            'contact' => 'rahmat@sampahrute.id',
            'area' => 'Semua wilayah',
            'active' => true,
        ]);

        User::create([
            'name' => 'Lina Marlina',
            'username' => 'lina.admin',
            'email' => 'lina@sampahrute.id',
            'password' => $adminPass,
            'role' => 'admin',
            'title' => 'Pengelola Lingkungan',
            'contact' => 'lina@sampahrute.id',
            'area' => 'Semua wilayah',
            'active' => true,
        ]);

        // 2. Seed Officers & Petugas Users
        $officersData = [
            ['code' => 'PTG-01', 'username' => 'petugas', 'name' => 'Agus Wibowo', 'phone' => '0812-2600-1101', 'area' => 'Banjarsari & Laweyan', 'vehicle' => 'Truk AD 8123 KL', 'active' => true],
            ['code' => 'PTG-02', 'username' => 'joko', 'name' => 'Joko Susilo', 'phone' => '0812-2600-1102', 'area' => 'Jebres & Pasar Kliwon', 'vehicle' => 'Truk AD 8456 KL', 'active' => true],
            ['code' => 'PTG-03', 'username' => 'wahyu', 'name' => 'Wahyu Hidayat', 'phone' => '0812-2600-1103', 'area' => 'Serengan', 'vehicle' => 'Pikap AD 1947 TR', 'active' => true],
            ['code' => 'PTG-04', 'username' => 'bambang', 'name' => 'Bambang Purnomo', 'phone' => '0812-2600-1104', 'area' => 'Cadangan', 'vehicle' => 'Motor sampah AD 3310 RS', 'active' => false],
        ];

        $officersByCode = [];
        foreach ($officersData as $o) {
            $u = User::create([
                'name' => $o['name'],
                'username' => $o['username'],
                'email' => $o['username'].'@sampahrute.id',
                'password' => $petugasPass,
                'role' => 'petugas',
                'title' => 'Petugas Pengangkut',
                'contact' => $o['phone'],
                'area' => $o['area'],
                'officer_code' => $o['code'],
                'active' => $o['active'],
            ]);

            $officersByCode[$o['code']] = Officer::create([
                'code' => $o['code'],
                'user_id' => $u->id,
                'name' => $o['name'],
                'phone' => $o['phone'],
                'area' => $o['area'],
                'vehicle' => $o['vehicle'],
                'active' => $o['active'],
            ]);
        }

        // 3. Seed Warga Users
        $citizensData = [
            ['name' => 'Andi Pratama', 'username' => 'warga', 'area' => 'Banjarsari'],
            ['name' => 'Rina Putri', 'username' => 'rina.putri', 'area' => 'Jebres'],
            ['name' => 'Budi Santoso', 'username' => 'budi.santoso', 'area' => 'Banjarsari'],
            ['name' => 'Dewi Lestari', 'username' => 'dewi.lestari', 'area' => 'Serengan'],
            ['name' => 'Sari Wulandari', 'username' => 'sari.wulandari', 'area' => 'Jebres'],
            ['name' => 'Hendra Gunawan', 'username' => 'hendra.gunawan', 'area' => 'Jebres'],
        ];

        $usersByName = [];
        foreach ($citizensData as $c) {
            $u = User::create([
                'name' => $c['name'],
                'username' => $c['username'],
                'email' => $c['username'].'@warga.id',
                'password' => $wargaPass,
                'role' => 'warga',
                'title' => 'Warga '.$c['area'],
                'contact' => '0813-2900-'.substr( (string) abs(crc32($c['name'])), 0, 4),
                'area' => $c['area'],
                'active' => true,
            ]);
            $usersByName[$c['name']] = $u;
        }

        // 4. Seed Locations
        $daily = [1, 2, 3, 4, 5, 6, 7];
        $baseLocations = [
            ['code' => 'LOK-01', 'name' => 'TPS Manahan', 'type' => 'TPS', 'district' => 'Banjarsari', 'village' => 'Manahan', 'address' => 'Jl. Adi Sucipto, Manahan', 'lat' => -7.5568, 'lng' => 110.8004, 'capacity' => 6, 'fill' => 20, 'days' => $daily, 'window' => '06:00–08:00', 'officer_code' => 'PTG-01', 'status' => 'selesai'],
            ['code' => 'LOK-02', 'name' => 'TPS Jebres', 'type' => 'TPS', 'district' => 'Jebres', 'village' => 'Jebres', 'address' => 'Jl. Ir. Sutami, Jebres', 'lat' => -7.5596, 'lng' => 110.8481, 'capacity' => 8, 'fill' => 65, 'days' => $daily, 'window' => '06:30–08:30', 'officer_code' => 'PTG-02', 'status' => 'diproses'],
            ['code' => 'LOK-03', 'name' => 'TPS Pasar Gede', 'type' => 'Depo', 'district' => 'Jebres', 'village' => 'Sudiroprajan', 'address' => 'Jl. Urip Sumoharjo, Sudiroprajan', 'lat' => -7.5704, 'lng' => 110.8323, 'capacity' => 10, 'fill' => 92, 'days' => $daily, 'window' => '05:30–07:30', 'officer_code' => 'PTG-02', 'status' => 'menunggu'],
            ['code' => 'LOK-04', 'name' => 'TPS Laweyan', 'type' => 'TPS', 'district' => 'Laweyan', 'village' => 'Laweyan', 'address' => 'Jl. Dr. Rajiman, Laweyan', 'lat' => -7.5709, 'lng' => 110.7976, 'capacity' => 6, 'fill' => 15, 'days' => $daily, 'window' => '07:00–09:00', 'officer_code' => 'PTG-01', 'status' => 'selesai'],
            ['code' => 'LOK-05', 'name' => 'TPS Serengan', 'type' => 'TPS', 'district' => 'Serengan', 'village' => 'Serengan', 'address' => 'Jl. Veteran, Serengan', 'lat' => -7.5922, 'lng' => 110.8192, 'capacity' => 5, 'fill' => 80, 'days' => [1, 3, 5, 7], 'window' => '07:00–09:00', 'officer_code' => 'PTG-03', 'status' => 'menunggu'],
            ['code' => 'LOK-06', 'name' => 'TPS Banyuanyar', 'type' => 'TPS', 'district' => 'Banjarsari', 'village' => 'Banyuanyar', 'address' => 'Jl. Sumpah Pemuda, Banyuanyar', 'lat' => -7.5452, 'lng' => 110.8141, 'capacity' => 6, 'fill' => 70, 'days' => $daily, 'window' => '08:00–10:00', 'officer_code' => 'PTG-01', 'status' => 'menunggu'],
            ['code' => 'LOK-07', 'name' => 'Depo Mojosongo', 'type' => 'Depo', 'district' => 'Jebres', 'village' => 'Mojosongo', 'address' => 'Jl. Tangkuban Perahu, Mojosongo', 'lat' => -7.5368, 'lng' => 110.8392, 'capacity' => 12, 'fill' => 35, 'days' => [2, 4, 6], 'window' => '06:00–08:00', 'officer_code' => 'PTG-02', 'status' => 'selesai'],
            ['code' => 'LOK-08', 'name' => 'Kontainer Kerten', 'type' => 'Kontainer', 'district' => 'Laweyan', 'village' => 'Kerten', 'address' => 'Jl. Slamet Riyadi, Kerten', 'lat' => -7.5617, 'lng' => 110.7838, 'capacity' => 4, 'fill' => 55, 'days' => $daily, 'window' => '09:00–11:00', 'officer_code' => 'PTG-01', 'status' => 'diproses'],
            ['code' => 'LOK-09', 'name' => 'TPS Semanggi', 'type' => 'TPS', 'district' => 'Pasar Kliwon', 'village' => 'Semanggi', 'address' => 'Jl. Kyai Mojo, Semanggi', 'lat' => -7.5853, 'lng' => 110.8412, 'capacity' => 6, 'fill' => 48, 'days' => $daily, 'window' => '06:00–08:00', 'officer_code' => 'PTG-02', 'status' => 'menunggu'],
        ];

        foreach ($baseLocations as $loc) {
            Location::create($loc);
        }

        // 5. Seed Reports
        $baseReports = [
            ['code' => 'SR-001', 'title' => 'Sampah belum diangkut dua hari', 'category' => 'belum_diangkut', 'location_code' => 'LOK-03', 'resident' => 'Andi Pratama', 'days_ago' => 0, 'time' => '07:15', 'status' => 'menunggu', 'priority' => 'tinggi', 'officer_code' => null, 'photo' => 'assets/hero-collection.jpg', 'description' => 'Tumpukan kantong sampah di depan depo sudah meluber ke bahu jalan sejak kemarin pagi. Banyak lalat dan mulai berbau.'],
            ['code' => 'SR-002', 'title' => 'Penumpukan sampah rumah tangga', 'category' => 'penumpukan', 'location_code' => 'LOK-02', 'resident' => 'Rina Putri', 'days_ago' => 1, 'time' => '16:40', 'status' => 'diproses', 'priority' => 'sedang', 'officer_code' => 'PTG-02', 'photo' => null, 'description' => 'Sampah rumah tangga menumpuk di sisi timur TPS, sebagian kantong sobek dimakan kucing.'],
            ['code' => 'SR-003', 'title' => 'Kontainer melebihi kapasitas', 'category' => 'kapasitas', 'location_code' => 'LOK-01', 'resident' => 'Budi Santoso', 'days_ago' => 3, 'time' => '06:20', 'status' => 'selesai', 'priority' => 'sedang', 'officer_code' => 'PTG-01', 'photo' => null, 'description' => 'Kontainer penuh sehingga warga menaruh sampah di luar kontainer.'],
            ['code' => 'SR-004', 'title' => 'Pengangkutan terlambat', 'category' => 'terlambat', 'location_code' => 'LOK-05', 'resident' => 'Dewi Lestari', 'days_ago' => 1, 'time' => '10:05', 'status' => 'diverifikasi', 'priority' => 'sedang', 'officer_code' => null, 'photo' => null, 'description' => 'Jadwal angkut jam 7 pagi tapi sampai jam 10 truk belum datang.'],
            ['code' => 'SR-005', 'title' => 'Sampah berserakan di sekitar TPS', 'category' => 'penumpukan', 'location_code' => 'LOK-06', 'resident' => 'Andi Pratama', 'days_ago' => 2, 'time' => '18:10', 'status' => 'diproses', 'priority' => 'tinggi', 'officer_code' => 'PTG-01', 'photo' => 'assets/surakarta.jpg', 'description' => 'Sampah tercecer hingga ke saluran air di samping TPS. Khawatir menyumbat saat hujan.'],
            ['code' => 'SR-006', 'title' => 'Bau menyengat dari kontainer', 'category' => 'lainnya', 'location_code' => 'LOK-08', 'resident' => 'Andi Pratama', 'days_ago' => 5, 'time' => '12:30', 'status' => 'selesai', 'priority' => 'rendah', 'officer_code' => 'PTG-01', 'photo' => null, 'description' => 'Bau dari kontainer tercium sampai ke rumah warga, sepertinya ada sampah basah yang lama tidak diangkut.'],
            ['code' => 'SR-007', 'title' => 'Sampah belum diangkut', 'category' => 'belum_diangkut', 'location_code' => 'LOK-03', 'resident' => 'Sari Wulandari', 'days_ago' => 0, 'time' => '08:02', 'status' => 'ditolak', 'priority' => 'rendah', 'officer_code' => null, 'photo' => null, 'description' => 'Depo Pasar Gede penuh dan belum diangkut.', 'reject_reason' => 'Laporan sama dengan SR-001 yang sedang ditangani.'],
            ['code' => 'SR-008', 'title' => 'Sampah tidak diangkut sejak Kamis', 'category' => 'belum_diangkut', 'location_code' => 'LOK-07', 'resident' => 'Hendra Gunawan', 'days_ago' => 0, 'time' => '09:40', 'status' => 'menunggu', 'priority' => 'sedang', 'officer_code' => null, 'photo' => null, 'description' => 'Depo terlihat penuh dan belum ada truk datang sejak Kamis.'],
        ];

        foreach ($baseReports as $r) {
            $created = $today->copy()->subDays($r['days_ago'])->setTimeFromTimeString($r['time']);
            $user = $usersByName[$r['resident']] ?? null;

            Report::create([
                'code' => $r['code'],
                'user_id' => $user?->id,
                'resident_name' => $r['resident'],
                'location_code' => $r['location_code'],
                'officer_code' => $r['officer_code'],
                'title' => $r['title'],
                'category' => $r['category'],
                'incident_date' => $created->toDateString(),
                'description' => $r['description'],
                'photo' => $r['photo'],
                'status' => $r['status'],
                'priority' => $r['priority'],
                'reject_reason' => $r['reject_reason'] ?? null,
                'verified_at' => in_array($r['status'], ['diverifikasi', 'diproses', 'selesai'], true) ? $created->copy()->addMinutes(40) : null,
                'assigned_at' => in_array($r['status'], ['diproses', 'selesai'], true) ? $created->copy()->addMinutes(75) : null,
                'completed_at' => $r['status'] === 'selesai' ? $created->copy()->addHours(5) : null,
                'rejected_at' => $r['status'] === 'ditolak' ? $created->copy()->addMinutes(45) : null,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }

        // 6. Seed 14-day Pickup Logs
        foreach ($baseLocations as $loc) {
            $officerName = $officersByCode[$loc['officer_code']]->name ?? '-';

            for ($d = 0; $d <= 13; $d++) {
                $day = $today->copy()->subDays($d);
                if (! in_array($day->dayOfWeekIso, $loc['days'], true)) {
                    continue;
                }

                [$start, $end] = array_map('trim', explode('–', $loc['window']));
                $seed = abs(crc32($loc['code'].$day->toDateString()));

                if ($d === 0) {
                    if ($loc['status'] !== 'selesai') {
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

                PickupLog::create([
                    'code' => 'LOG-'.$loc['code'].'-'.$day->format('Ymd'),
                    'pickup_date' => $day->toDateString(),
                    'location_code' => $loc['code'],
                    'location_name' => $loc['name'],
                    'district' => $loc['district'],
                    'officer_code' => $loc['officer_code'],
                    'officer_name' => $officerName,
                    'window' => $loc['window'],
                    'actual_time' => $actual?->format('H:i'),
                    'volume' => $result === 'terlewat' ? null : round($loc['capacity'] * (0.45 + ($seed % 45) / 100), 1),
                    'result' => $result,
                    'note' => $result === 'terlewat' ? 'Truk dialihkan ke lokasi lain.' : ($result === 'terlambat' ? 'Antrean di TPA Putri Cempo.' : ''),
                ]);
            }
        }
    }
}
