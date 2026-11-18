<?php

namespace App\Support;

use App\Models\Location;
use App\Models\Officer;
use App\Models\PickupLog;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Layanan data utama SampahRute berbasis Database (SQLite + Eloquent).
 *
 * Tetap mempertahankan nama kelas & tanda tangan method DemoData agar
 * seluruh tampilan Blade yang sudah ada berjalan mulus tanpa perubahan.
 */
class DemoData
{
    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public const REPORT_STATUSES = ['menunggu', 'diverifikasi', 'diproses', 'selesai', 'ditolak'];

    public const CATEGORIES = [
        'belum_diangkut' => 'Belum diangkut',
        'penumpukan' => 'Penumpukan sampah',
        'kapasitas' => 'Melebihi kapasitas',
        'terlambat' => 'Pengangkutan terlambat',
        'lainnya' => 'Lainnya',
    ];

    /** Fallback profil pengguna per peran. */
    public const DEMO_USERS = [
        'admin' => ['name' => 'Rahmat Hidayat', 'title' => 'Pengelola Lingkungan'],
        'petugas' => ['name' => 'Agus Wibowo', 'title' => 'Petugas Pengangkut', 'officer_id' => 'PTG-01'],
        'warga' => ['name' => 'Andi Pratama', 'title' => 'Warga Banjarsari'],
    ];

    private Carbon $today;

    public function __construct()
    {
        $this->today = Carbon::today();
    }

    /* ------------------------------------------------------------------
     | Petugas
     * -----------------------------------------------------------------*/

    public function officers(): array
    {
        $result = [];
        foreach (Officer::orderBy('code')->get() as $o) {
            $result[$o->code] = $o->toViewArray();
        }

        return $result;
    }

    /* ------------------------------------------------------------------
     | Lokasi / titik pengangkutan
     * -----------------------------------------------------------------*/

    public function locations(): array
    {
        $officers = $this->officers();
        $todayIso = $this->today->dayOfWeekIso;
        $result = [];

        foreach (Location::orderBy('code')->get() as $model) {
            $days = array_map('intval', $model->days ?? [1, 2, 3, 4, 5, 6, 7]);
            $scheduledToday = in_array($todayIso, $days, true);
            $status = $scheduledToday ? $model->status : 'libur';
            $fill = $status === 'selesai' ? min((int) $model->fill, 10) : (int) $model->fill;

            $loc = [
                'id' => $model->code,
                'name' => $model->name,
                'type' => $model->type,
                'district' => $model->district,
                'village' => $model->village ?? $model->district,
                'address' => $model->address,
                'lat' => (float) $model->lat,
                'lng' => (float) $model->lng,
                'capacity' => (float) $model->capacity,
                'fill' => $fill,
                'days' => $days,
                'window' => $model->window,
                'officer_id' => $model->officer_code,
                'status' => $status,
                'scheduled_today' => $scheduledToday,
                'officer' => $officers[$model->officer_code] ?? ['id' => '-', 'name' => '-', 'vehicle' => '-'],
                'days_label' => count($days) === 7
                    ? 'Setiap hari'
                    : implode(', ', array_map(fn ($d) => self::DAYS[$d] ?? '', $days)),
            ];

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

    public function reports(): array
    {
        $locations = $this->locations();
        $officers = $this->officers();
        $result = [];

        foreach (Report::orderByDesc('created_at')->orderByDesc('id')->get() as $model) {
            $created = $model->created_at ? Carbon::instance($model->created_at) : now();
            $loc = $locations[$model->location_code] ?? null;
            $officer = $model->officer_code ? ($officers[$model->officer_code] ?? null) : null;

            $r = [
                'id' => $model->code,
                'user_id' => $model->user_id,
                'title' => $model->title,
                'category' => $model->category,
                'category_label' => self::CATEGORIES[$model->category] ?? 'Lainnya',
                'location_id' => $model->location_code,
                'location' => $loc['name'] ?? '-',
                'location_data' => $loc,
                'district' => $loc['district'] ?? '-',
                'resident' => $model->resident_name,
                'created' => $created,
                'date' => $created->translatedFormat('d M Y'),
                'status' => $model->status,
                'priority' => $model->priority,
                'officer_id' => $model->officer_code,
                'officer' => $officer,
                'photo' => $model->photo,
                'description' => $model->description,
                'reject_reason' => $model->reject_reason,
                'verified_at' => $model->verified_at ? Carbon::instance($model->verified_at) : null,
                'assigned_at' => $model->assigned_at ? Carbon::instance($model->assigned_at) : null,
                'completed_at' => $model->completed_at ? Carbon::instance($model->completed_at) : null,
                'rejected_at' => $model->rejected_at ? Carbon::instance($model->rejected_at) : null,
            ];

            $r['timeline'] = $this->timeline($r);
            $result[$r['id']] = $r;
        }

        return $result;
    }

    public function report(string $id): ?array
    {
        return $this->reports()[$id] ?? null;
    }

    public function reportsFor(string $role, ?User $user = null): array
    {
        $user ??= Auth::user();
        $reports = $this->reports();

        if ($role === 'warga') {
            $residentName = $user?->name ?? self::DEMO_USERS['warga']['name'];
            $userId = $user?->id;

            return array_filter(
                $reports,
                fn ($r) => ($userId && $r['user_id'] === $userId) || $r['resident'] === $residentName
            );
        }

        if ($role === 'petugas') {
            $officerCode = $user?->officer_code ?: self::DEMO_USERS['petugas']['officer_id'];

            return array_filter($reports, fn ($r) => $r['officer_id'] === $officerCode);
        }

        return $reports;
    }

    /** Riwayat status sebuah laporan, untuk tampilan timeline. */
    private function timeline(array $r): array
    {
        $t = $r['created']->copy();
        $steps = [[
            'key' => 'menunggu',
            'label' => 'Laporan dikirim',
            'by' => $r['resident'],
            'time' => $t->copy(),
            'note' => 'Laporan masuk dan menunggu verifikasi admin.',
        ]];

        if ($r['status'] === 'ditolak') {
            $steps[] = [
                'key' => 'ditolak',
                'label' => 'Laporan ditolak',
                'by' => 'Admin',
                'time' => $r['rejected_at'] ?? $t->copy()->addMinutes(45),
                'note' => $r['reject_reason'] ?? 'Laporan tidak dapat diproses.',
            ];

            return $steps;
        }

        $order = ['menunggu' => 0, 'diverifikasi' => 1, 'diproses' => 2, 'selesai' => 3];
        $level = $order[$r['status']] ?? 0;

        if ($level >= 1) {
            $steps[] = [
                'key' => 'diverifikasi',
                'label' => 'Diverifikasi admin',
                'by' => 'Admin',
                'time' => $r['verified_at'] ?? $t->copy()->addMinutes(40),
                'note' => 'Lokasi dan kondisi laporan sudah dicek.',
            ];
        }
        if ($level >= 2) {
            $steps[] = [
                'key' => 'diproses',
                'label' => 'Ditugaskan ke petugas',
                'by' => 'Admin',
                'time' => $r['assigned_at'] ?? $t->copy()->addMinutes(75),
                'note' => 'Ditangani oleh '.($r['officer']['name'] ?? 'petugas').'.',
            ];
        }
        if ($level >= 3) {
            $steps[] = [
                'key' => 'selesai',
                'label' => 'Selesai ditangani',
                'by' => $r['officer']['name'] ?? 'Petugas',
                'time' => $r['completed_at'] ?? $t->copy()->addHours(5),
                'note' => 'Sampah sudah diangkut dan area dibersihkan.',
            ];
        }

        return $steps;
    }

    /* ------------------------------------------------------------------
     | Riwayat pengangkutan (monitoring tanggal angkut)
     * -----------------------------------------------------------------*/

    public function logs(): array
    {
        $logs = [];

        foreach (PickupLog::orderByDesc('pickup_date')->orderByDesc('actual_time')->get() as $model) {
            $logs[] = [
                'id' => $model->code,
                'date' => Carbon::parse($model->pickup_date),
                'location_id' => $model->location_code,
                'location' => $model->location_name,
                'district' => $model->district,
                'officer_id' => $model->officer_code,
                'officer' => $model->officer_name,
                'window' => $model->window,
                'actual' => $model->actual_time,
                'volume' => $model->volume !== null ? (float) $model->volume : null,
                'result' => $model->result,
                'note' => $model->note ?? '',
                'photo' => $model->photo,
            ];
        }

        return $logs;
    }

    /* ------------------------------------------------------------------
     | Pengguna & notifikasi
     * -----------------------------------------------------------------*/

    public function users(): array
    {
        return User::orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'petugas' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get()
            ->map(fn (User $u) => $u->toViewArray())
            ->all();
    }

    public function notifications(?string $role, ?User $user = null): array
    {
        $user ??= Auth::user();
        $reports = $this->reports();
        $locations = $this->locations();
        $items = [];

        if ($role === 'admin') {
            foreach (array_filter($reports, fn ($r) => $r['status'] === 'menunggu') as $r) {
                $items[] = [
                    'icon' => '📄',
                    'text' => "Laporan baru {$r['id']} di {$r['location']}",
                    'time' => $r['created']->diffForHumans(),
                    'route' => route('admin.reports').'#'.$r['id'],
                ];
            }
            foreach (array_filter($locations, fn ($l) => $l['fill'] >= 85) as $l) {
                $items[] = [
                    'icon' => '⚠️',
                    'text' => "{$l['name']} hampir penuh ({$l['fill']}%)",
                    'time' => 'Hari ini',
                    'route' => route('admin.locations'),
                ];
            }
        } elseif ($role === 'petugas') {
            $officerCode = $user?->officer_code ?: 'PTG-01';
            foreach ($this->reportsFor('petugas', $user) as $r) {
                if ($r['status'] === 'diproses') {
                    $items[] = [
                        'icon' => '📌',
                        'text' => "Tugas laporan {$r['id']}: {$r['location']}",
                        'time' => $r['created']->diffForHumans(),
                        'route' => route('petugas.tasks').'#laporan',
                    ];
                }
            }
            $pending = count(array_filter(
                $locations,
                fn ($l) => $l['officer_id'] === $officerCode && in_array($l['status'], ['menunggu', 'diproses'], true)
            ));
            if ($pending) {
                $items[] = [
                    'icon' => '🚚',
                    'text' => "{$pending} titik belum selesai di rute hari ini",
                    'time' => 'Hari ini',
                    'route' => route('petugas.tasks'),
                ];
            }
        } elseif ($role === 'warga') {
            foreach ($this->reportsFor('warga', $user) as $r) {
                $last = end($r['timeline']);
                $items[] = [
                    'icon' => '🔔',
                    'text' => "{$r['id']}: {$last['label']}",
                    'time' => $last['time']->diffForHumans(),
                    'route' => route('warga.show', $r['id']),
                ];
            }
        }

        return array_slice($items, 0, 6);
    }

    /* ------------------------------------------------------------------
     | Mutasi Database
     * -----------------------------------------------------------------*/

    public function setReportStatus(string $id, string $status, ?string $officerId = null, ?string $reason = null): void
    {
        $report = Report::where('code', $id)->first();
        if (! $report) {
            return;
        }

        $report->status = $status;

        if ($officerId !== null) {
            $report->officer_code = $officerId ?: null;
        }

        if ($reason !== null) {
            $report->reject_reason = $reason;
        }

        $now = now();
        if ($status === 'diverifikasi' && ! $report->verified_at) {
            $report->verified_at = $now;
        } elseif ($status === 'diproses') {
            $report->verified_at ??= $now;
            $report->assigned_at = $now;
        } elseif ($status === 'selesai') {
            $report->verified_at ??= $now;
            $report->assigned_at ??= $now;
            $report->completed_at = $now;
        } elseif ($status === 'ditolak') {
            $report->rejected_at = $now;
        } elseif ($status === 'menunggu') {
            $report->reject_reason = null;
            $report->rejected_at = null;
        }

        $report->save();
    }

    public function setLocationStatus(string $id, string $status, array $log = []): void
    {
        $location = Location::where('code', $id)->first();
        if (! $location) {
            return;
        }

        $location->status = $status;
        if ($status === 'selesai') {
            $location->fill = min((int) $location->fill, 10);
        }
        $location->save();

        if ($status === 'selesai' && $log) {
            $dateStr = isset($log['date']) ? Carbon::parse($log['date'])->toDateString() : today()->toDateString();
            $code = 'LOG-'.$location->code.'-'.Carbon::parse($dateStr)->format('Ymd');

            PickupLog::updateOrCreate(
                ['code' => $code],
                [
                    'pickup_date' => $dateStr,
                    'location_code' => $location->code,
                    'location_name' => $log['location'] ?? $location->name,
                    'district' => $log['district'] ?? $location->district,
                    'officer_code' => $log['officer_id'] ?? $location->officer_code,
                    'officer_name' => $log['officer'] ?? ($location->officer?->name ?? '-'),
                    'window' => $log['window'] ?? $location->window,
                    'actual_time' => $log['actual'] ?? now()->format('H:i'),
                    'volume' => $log['volume'] ?? null,
                    'result' => $log['result'] ?? 'tepat',
                    'note' => $log['note'] ?? '',
                    'photo' => $log['photo'] ?? null,
                ]
            );
        }
    }

    public function addReport(array $data, ?User $user = null): string
    {
        $user ??= Auth::user();

        $lastCode = Report::orderByDesc('id')->value('code');
        $nextNum = $lastCode ? ((int) preg_replace('/\D+/', '', $lastCode)) + 1 : 1;
        $code = sprintf('SR-%03d', max($nextNum, Report::count() + 1));
        while (Report::where('code', $code)->exists()) {
            $nextNum++;
            $code = sprintf('SR-%03d', $nextNum);
        }

        $priority = match ($data['category'] ?? 'lainnya') {
            'belum_diangkut', 'kapasitas' => 'tinggi',
            'penumpukan', 'terlambat' => 'sedang',
            default => 'rendah',
        };

        Report::create([
            'code' => $code,
            'user_id' => $user?->id,
            'resident_name' => $user?->name ?? self::DEMO_USERS['warga']['name'],
            'location_code' => $data['location_id'],
            'officer_code' => null,
            'title' => $data['title'],
            'category' => $data['category'],
            'incident_date' => $data['date'] ?? today()->toDateString(),
            'description' => $data['description'],
            'photo' => $data['photo'] ?? null,
            'status' => 'menunggu',
            'priority' => $priority,
        ]);

        return $code;
    }

    public function saveLocation(array $data): Location
    {
        $existing = Location::where('name', $data['name'])->first();
        if ($existing) {
            $existing->update([
                'type' => $data['type'],
                'district' => $data['district'],
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'capacity' => $data['capacity'],
            ]);

            return $existing;
        }

        $lastCode = Location::orderByDesc('id')->value('code');
        $nextNum = $lastCode ? ((int) preg_replace('/\D+/', '', $lastCode)) + 1 : 1;
        $code = sprintf('LOK-%02d', max($nextNum, Location::count() + 1));
        while (Location::where('code', $code)->exists()) {
            $nextNum++;
            $code = sprintf('LOK-%02d', $nextNum);
        }

        $defaultOfficer = match ($data['district']) {
            'Banjarsari', 'Laweyan' => 'PTG-01',
            'Serengan' => 'PTG-03',
            default => 'PTG-02',
        };

        return Location::create([
            'code' => $code,
            'name' => $data['name'],
            'type' => $data['type'],
            'district' => $data['district'],
            'village' => $data['district'],
            'address' => $data['address'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'capacity' => $data['capacity'],
            'fill' => 25,
            'days' => [1, 2, 3, 4, 5, 6, 7],
            'window' => '06:00–08:00',
            'officer_code' => $defaultOfficer,
            'status' => 'menunggu',
        ]);
    }

    public function saveSchedule(array $data): ?Location
    {
        $location = Location::where('code', $data['location_id'])->first();
        if (! $location) {
            return null;
        }

        $days = array_values(array_unique(array_map('intval', $data['days'])));
        sort($days);

        $location->update([
            'days' => $days,
            'window' => $data['window_start'].'–'.$data['window_end'],
            'officer_code' => $data['officer_id'],
        ]);

        return $location;
    }

    public function saveUser(array $data): User
    {
        $username = strtolower(trim($data['username']));
        $existing = User::where('username', $username)->first();

        if ($existing) {
            $existing->update([
                'name' => $data['name'],
                'role' => $data['role'],
                'title' => match ($data['role']) {
                    'admin' => 'Pengelola Lingkungan',
                    'petugas' => 'Petugas Pengangkut',
                    default => 'Warga '.($existing->area ?: 'Surakarta'),
                },
            ]);

            if ($data['role'] === 'petugas' && ! $existing->officer_code) {
                $code = $this->createOfficerForUser($existing);
                $existing->update(['officer_code' => $code]);
            }

            return $existing;
        }

        $defaultPassword = $data['role'].'123';
        $user = User::create([
            'name' => $data['name'],
            'username' => $username,
            'email' => $username.'@sampahrute.id',
            'password' => Hash::make($defaultPassword),
            'role' => $data['role'],
            'title' => match ($data['role']) {
                'admin' => 'Pengelola Lingkungan',
                'petugas' => 'Petugas Pengangkut',
                default => 'Warga Surakarta',
            },
            'contact' => $username.'@sampahrute.id',
            'area' => $data['role'] === 'admin' ? 'Semua wilayah' : 'Surakarta',
            'active' => true,
        ]);

        if ($data['role'] === 'petugas') {
            $code = $this->createOfficerForUser($user);
            $user->update(['officer_code' => $code]);
        }

        return $user;
    }

    private function createOfficerForUser(User $user): string
    {
        $lastCode = Officer::orderByDesc('id')->value('code');
        $nextNum = $lastCode ? ((int) preg_replace('/\D+/', '', $lastCode)) + 1 : 1;
        $code = sprintf('PTG-%02d', max($nextNum, Officer::count() + 1));
        while (Officer::where('code', $code)->exists()) {
            $nextNum++;
            $code = sprintf('PTG-%02d', $nextNum);
        }

        Officer::create([
            'code' => $code,
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->contact ?? '0812-2600-0000',
            'area' => $user->area ?? 'Surakarta',
            'vehicle' => 'Truk Pengangkut',
            'active' => true,
        ]);

        return $code;
    }

    public function deleteEntity(string $target): bool
    {
        $loc = Location::where('code', $target)->orWhere('name', $target)->first();
        if ($loc) {
            return (bool) $loc->delete();
        }

        $user = User::where('username', $target)->orWhere('name', $target)->first();
        if ($user) {
            if ($user->officer_code) {
                Officer::where('code', $user->officer_code)->delete();
            }

            return (bool) $user->delete();
        }

        return false;
    }

    public function reset(): void
    {
        Artisan::call('db:seed', ['--force' => true]);
    }
}
