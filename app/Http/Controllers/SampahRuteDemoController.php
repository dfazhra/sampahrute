<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\DemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Controller utama SampahRute (terintegrasi penuh dengan Database & Auth Laravel).
 */
class SampahRuteDemoController extends Controller
{
    private function data(Request $request): DemoData
    {
        return new DemoData();
    }

    private function render(Request $request, string $view, array $extra = [])
    {
        $data = $this->data($request);
        $user = Auth::user();
        $role = $extra['role'] ?? $user?->role;

        return view($view, array_merge([
            'data' => $data,
            'points' => $data->mapPoints(),
            'locations' => $data->locations(),
            'reports' => $data->reports(),
            'officers' => $data->officers(),
            'notifications' => $data->notifications($role, $user),
            'me' => $user ? $user->toViewArray() : (DemoData::DEMO_USERS[$role] ?? null),
            'sessionRole' => $user?->role,
        ], $extra));
    }

    private function checkRole(Request $request, array $allowedRoles): ?RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Masuk dulu untuk membuka halaman ini.');
        }

        $user = Auth::user();
        abort_unless(in_array($user->role, $allowedRoles, true), 403, 'Halaman ini hanya untuk peran ' . implode(' / ', $allowedRoles) . '.');

        return null;
    }

    /** Halaman publik yang tampil di dalam dashboard jika pengguna sudah login. */
    private function publicOrDashboard(Request $request, string $view)
    {
        $role = Auth::user()?->role;

        return $this->render($request, $view, $role ? ['role' => $role] : []);
    }

    /* ---------------------------------------------------------------
     | Publik
     * --------------------------------------------------------------*/

    public function landing(Request $request)
    {
        return $this->render($request, 'sampahrute.landing');
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            if (in_array($role, ['admin', 'petugas', 'warga'], true)) {
                return redirect()->route($role . '.dashboard');
            }
        }

        return $this->render($request, 'sampahrute.auth.login');
    }

    public function register(Request $request)
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            if (in_array($role, ['admin', 'petugas', 'warga'], true)) {
                return redirect()->route($role . '.dashboard');
            }
        }

        return $this->render($request, 'sampahrute.auth.register');
    }

    public function registerStore(Request $request)
    {
        $valid = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'contact' => ['required', 'string', 'max:30'],
            'area' => ['required', 'in:Banjarsari,Jebres,Laweyan,Pasar Kliwon,Serengan'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'username.unique' => 'Username ini sudah dipakai. Pilih username lain.',
            'username.regex' => 'Username hanya boleh huruf, angka, titik, garis bawah, atau strip.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $username = strtolower(trim($valid['username']));
        $user = User::create([
            'name' => $valid['name'],
            'username' => $username,
            'email' => ($valid['email'] ?? null) ?: ($username . '@warga.id'),
            'password' => Hash::make($valid['password']),
            'role' => 'warga',
            'title' => 'Warga ' . $valid['area'],
            'contact' => $valid['contact'],
            'area' => $valid['area'],
            'active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put(['demo_role' => $user->role, 'demo_username' => $user->username]);

        return redirect()
            ->route('warga.dashboard')
            ->with('success', "Selamat datang di SampahRute, {$user->name}! Akun warga Anda berhasil dibuat.");
    }

    public function map(Request $request)
    {
        return $this->publicOrDashboard($request, 'sampahrute.map');
    }

    public function locations(Request $request)
    {
        return $this->publicOrDashboard($request, 'sampahrute.locations');
    }

    public function schedule(Request $request)
    {
        return $this->publicOrDashboard($request, 'sampahrute.schedule');
    }

    /* ---------------------------------------------------------------
     | Autentikasi Database
     * --------------------------------------------------------------*/

    public function signIn(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = strtolower(trim($data['email']));
        $remember = $request->boolean('remember');

        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Username atau password salah. Coba salah satu akun demo di bawah form.'])
                ->onlyInput('email');
        }

        if (!$user->active) {
            return back()
                ->withErrors(['email' => 'Akun ini sedang nonaktif. Hubungi admin.'])
                ->onlyInput('email');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->put(['demo_role' => $user->role, 'demo_username' => $user->username]);

        return redirect()
            ->route($user->role . '.dashboard')
            ->with('success', 'Masuk sebagai ' . $user->name . ' (' . ucfirst($user->role) . ').');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda sudah keluar.');
    }

    /* ---------------------------------------------------------------
     | Admin
     * --------------------------------------------------------------*/

    public function admin(Request $request)
    {
        return $this->checkRole($request, ['admin'])
            ?? $this->render($request, 'sampahrute.admin.dashboard', ['role' => 'admin', 'logs' => $this->data($request)->logs()]);
    }

    public function adminReports(Request $request)
    {
        return $this->checkRole($request, ['admin'])
            ?? $this->render($request, 'sampahrute.admin.reports', ['role' => 'admin']);
    }

    public function adminLocations(Request $request)
    {
        return $this->checkRole($request, ['admin'])
            ?? $this->render($request, 'sampahrute.admin.locations', ['role' => 'admin']);
    }

    public function adminSchedule(Request $request)
    {
        return $this->checkRole($request, ['admin'])
            ?? $this->render($request, 'sampahrute.admin.schedule', ['role' => 'admin']);
    }

    public function adminHistory(Request $request)
    {
        return $this->checkRole($request, ['admin'])
            ?? $this->render($request, 'sampahrute.history', ['role' => 'admin', 'logs' => $this->data($request)->logs()]);
    }

    public function adminUsers(Request $request)
    {
        return $this->checkRole($request, ['admin'])
            ?? $this->render($request, 'sampahrute.admin.users', ['role' => 'admin', 'users' => $this->data($request)->users()]);
    }

    /* ---------------------------------------------------------------
     | Petugas
     * --------------------------------------------------------------*/

    private function currentOfficerCode(): string
    {
        return Auth::user()?->officer_code ?: DemoData::DEMO_USERS['petugas']['officer_id'];
    }

    private function officerRoute(DemoData $data): array
    {
        $officerCode = $this->currentOfficerCode();
        $mine = array_filter(
            $data->locations(),
            fn($l) => $l['officer_id'] === $officerCode && $l['scheduled_today']
        );

        uasort($mine, fn($a, $b) => strcmp($a['window'], $b['window']));

        return $mine;
    }

    public function officer(Request $request)
    {
        if ($response = $this->checkRole($request, ['petugas'])) {
            return $response;
        }

        $data = $this->data($request);

        return $this->render($request, 'sampahrute.petugas.dashboard', [
            'role' => 'petugas',
            'route' => $this->officerRoute($data),
            'myReports' => $data->reportsFor('petugas'),
        ]);
    }

    public function tasks(Request $request)
    {
        if ($response = $this->checkRole($request, ['petugas'])) {
            return $response;
        }

        $data = $this->data($request);

        return $this->render($request, 'sampahrute.petugas.tasks', [
            'role' => 'petugas',
            'route' => $this->officerRoute($data),
            'myReports' => $data->reportsFor('petugas'),
        ]);
    }

    public function officerHistory(Request $request)
    {
        if ($response = $this->checkRole($request, ['petugas'])) {
            return $response;
        }

        $officerCode = $this->currentOfficerCode();
        $logs = array_values(array_filter(
            $this->data($request)->logs(),
            fn($l) => $l['officer_id'] === $officerCode
        ));

        return $this->render($request, 'sampahrute.history', ['role' => 'petugas', 'logs' => $logs]);
    }

    /* ---------------------------------------------------------------
     | Warga
     * --------------------------------------------------------------*/

    public function citizen(Request $request)
    {
        return $this->checkRole($request, ['warga'])
            ?? $this->render($request, 'sampahrute.warga.dashboard', ['role' => 'warga', 'myReports' => $this->data($request)->reportsFor('warga')]);
    }

    public function citizenReports(Request $request)
    {
        return $this->checkRole($request, ['warga'])
            ?? $this->render($request, 'sampahrute.warga.reports', ['role' => 'warga', 'myReports' => $this->data($request)->reportsFor('warga')]);
    }

    public function citizenReport(Request $request, string $id)
    {
        if ($response = $this->checkRole($request, ['warga'])) {
            return $response;
        }

        $report = $this->data($request)->reportsFor('warga')[$id] ?? null;
        abort_unless($report, 404, 'Laporan ' . $id . ' tidak ditemukan di akun Anda.');

        return $this->render($request, 'sampahrute.warga.show', ['role' => 'warga', 'report' => $report]);
    }

    public function reportForm(Request $request)
    {
        return $this->checkRole($request, ['warga'])
            ?? $this->render($request, 'sampahrute.warga.create', ['role' => 'warga']);
    }

    public function submitReport(Request $request)
    {
        if ($response = $this->checkRole($request, ['warga'])) {
            return $response;
        }

        $valid = $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|in:' . implode(',', array_keys(DemoData::CATEGORIES)),
            'location_id' => 'required|string',
            'date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|min:10|max:2000',
            'photo' => 'nullable|image|max:3072',
        ], [
            'location_id.required' => 'Pilih lokasi sampah.',
            'date.before_or_equal' => 'Tanggal kejadian tidak boleh di masa depan.',
            'description.min' => 'Deskripsi minimal 10 karakter supaya petugas paham kondisinya.',
        ]);

        $data = $this->data($request);
        abort_unless($data->location($valid['location_id']), 422);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'report-' . now()->format('YmdHis') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/reports'), $filename);
            $photoPath = 'uploads/reports/' . $filename;
        }

        $id = $data->addReport([
            'title' => $valid['title'],
            'category' => $valid['category'],
            'location_id' => $valid['location_id'],
            'date' => $valid['date'],
            'description' => $valid['description'],
            'photo' => $photoPath,
        ], Auth::user());

        return redirect()->route('warga.show', $id)
            ->with('success', "Laporan $id berhasil disimpan ke database. Admin akan segera memverifikasinya.");
    }

    /* ---------------------------------------------------------------
     | Aksi Database (Status Laporan, Konfirmasi Angkut, CRUD Admin)
     * --------------------------------------------------------------*/

    /** Ubah status laporan (admin) atau tandai selesai (petugas). */
    public function updateStatus(Request $request)
    {
        if ($response = $this->checkRole($request, ['admin', 'petugas'])) {
            return $response;
        }

        $role = Auth::user()->role;
        $data = $this->data($request);

        $valid = $request->validate([
            'report_id' => 'required|string',
            'status' => 'required|in:' . implode(',', DemoData::REPORT_STATUSES),
            'officer_id' => 'nullable|string',
            'reason' => 'nullable|required_if:status,ditolak|string|max:300',
        ], [
            'reason.required_if' => 'Tulis alasan penolakan supaya warga tahu.',
        ]);

        $report = $data->report($valid['report_id']);
        abort_unless($report, 404);

        if ($role === 'petugas') {
            $officerCode = $this->currentOfficerCode();
            abort_unless($report['officer_id'] === $officerCode && $valid['status'] === 'selesai', 403, 'Petugas hanya bisa menyelesaikan laporan yang ditugaskan kepadanya.');
        }

        if ($valid['status'] === 'diproses' && empty($valid['officer_id']) && !$report['officer_id']) {
            return back()->withErrors(['officer_id' => 'Pilih petugas sebelum menugaskan laporan.']);
        }

        $data->setReportStatus($report['id'], $valid['status'], $valid['officer_id'] ?? null, $valid['reason'] ?? null);

        $labels = [
            'menunggu' => 'dikembalikan ke antrean',
            'diverifikasi' => 'diverifikasi',
            'diproses' => 'ditugaskan ke petugas',
            'selesai' => 'ditandai selesai',
            'ditolak' => 'ditolak',
        ];

        return back()->with('success', "Laporan {$report['id']} {$labels[$valid['status']]}.");
    }

    /** Petugas mengonfirmasi pengangkutan di satu titik. */
    public function confirmPickup(Request $request)
    {
        if ($response = $this->checkRole($request, ['petugas'])) {
            return $response;
        }

        $data = $this->data($request);

        $valid = $request->validate([
            'location_id' => 'required|string',
            'status' => 'required|in:diproses,selesai',
            'time' => 'nullable|required_if:status,selesai|date_format:H:i',
            'volume' => 'nullable|numeric|min:0|max:50',
            'note' => 'nullable|string|max:300',
            'photo' => 'nullable|image|max:3072',
        ], [
            'time.required_if' => 'Isi jam pengangkutan.',
        ]);

        $loc = $data->location($valid['location_id']);
        $officerCode = $this->currentOfficerCode();
        abort_unless($loc && $loc['officer_id'] === $officerCode, 403);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'pickup-' . now()->format('YmdHis') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pickups'), $filename);
            $photoPath = 'uploads/pickups/' . $filename;
        }

        $log = [];
        if ($valid['status'] === 'selesai') {
            $end = trim(explode('–', $loc['window'])[1]);
            $log = [
                'id' => 'LOG-' . $loc['id'] . '-' . now()->format('Ymd'),
                'date' => today()->toDateString(),
                'location_id' => $loc['id'],
                'location' => $loc['name'],
                'district' => $loc['district'],
                'officer_id' => $loc['officer_id'],
                'officer' => $loc['officer']['name'],
                'window' => $loc['window'],
                'actual' => $valid['time'],
                'volume' => $valid['volume'] ?? null,
                'result' => $valid['time'] <= $end ? 'tepat' : 'terlambat',
                'note' => $valid['note'] ?? '',
                'photo' => $photoPath,
            ];
        }

        $data->setLocationStatus($loc['id'], $valid['status'], $log);

        $msg = $valid['status'] === 'selesai'
            ? "{$loc['name']} tercatat sudah diangkut pukul {$valid['time']}."
            : "Pengangkutan di {$loc['name']} dimulai.";

        return back()->with('success', $msg);
    }

    /** Simpan lokasi, jadwal, pengguna, atau hapus data ke database. */
    public function saveDemo(Request $request)
    {
        if ($response = $this->checkRole($request, ['admin'])) {
            return $response;
        }

        $entity = $request->input('entity');

        $rules = match ($entity) {
            'lokasi' => [
                'name' => 'required|string|max:100',
                'type' => 'required|in:TPS,Depo,Kontainer',
                'district' => 'required|string',
                'address' => 'required|string|max:200',
                'lat' => 'required|numeric|between:-7.65,-7.50',
                'lng' => 'required|numeric|between:110.75,110.90',
                'capacity' => 'required|numeric|min:1|max:50',
            ],
            'jadwal' => [
                'location_id' => 'required|string',
                'officer_id' => 'required|string',
                'days' => 'required|array|min:1',
                'window_start' => 'required|date_format:H:i',
                'window_end' => 'required|date_format:H:i|after:window_start',
            ],
            'pengguna' => [
                'name' => 'required|string|max:100',
                'username' => ['required', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
                'role' => 'required|in:admin,petugas,warga',
            ],
            'hapus' => [
                'target' => 'required|string',
            ],
            default => [
                'entity' => 'required|in:lokasi,jadwal,pengguna,hapus',
            ],
        };

        $valid = $request->validate($rules, [
            'lat.between' => 'Titik harus berada di wilayah Surakarta. Klik peta untuk memilih.',
            'lng.between' => 'Titik harus berada di wilayah Surakarta. Klik peta untuk memilih.',
            'days.required' => 'Pilih minimal satu hari pengangkutan.',
            'window_end.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $data = $this->data($request);

        return match ($entity) {
            'lokasi' => tap(back()->with('success', "Lokasi {$valid['name']} berhasil disimpan ke database."), fn() => $data->saveLocation($valid)),
            'jadwal' => tap(back()->with('success', 'Jadwal pengangkutan berhasil diperbarui di database.'), fn() => $data->saveSchedule($valid)),
            'pengguna' => tap(back()->with('success', "Akun {$valid['name']} (@{$valid['username']}) berhasil disimpan. Password awal: {$valid['role']}123"), fn() => $data->saveUser($valid)),
            'hapus' => tap(back()->with('success', "{$valid['target']} berhasil dihapus dari database."), fn() => $data->deleteEntity($valid['target'])),
        };
    }

    public function resetDemo(Request $request)
    {
        $currentUserId = Auth::id();
        $currentUsername = Auth::user()?->username;

        $this->data($request)->reset();

        if ($currentUsername) {
            $user = User::where('username', $currentUsername)->first();
            if ($user) {
                Auth::login($user);
            }
        }

        return back()->with('success', 'Database berhasil direset ke kondisi awal (Data Surakarta).');
    }
}
