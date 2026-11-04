<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;

class SampahRuteDemoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DATA LOKASI CONTOH
    |--------------------------------------------------------------------------
    */

    private function sampleLocations(): array
    {
        return [
            [
                'name' => 'TPS Manahan',
                'district' => 'Banjarsari',
                'address' => 'Area Manahan, Surakarta',
                'lat' => -7.5568,
                'lng' => 110.8004,
                'status' => 'selesai',
                'date' => '30 Okt 2026',
            ],
            [
                'name' => 'TPS Jebres',
                'district' => 'Jebres',
                'address' => 'Area Jebres, Surakarta',
                'lat' => -7.5596,
                'lng' => 110.8481,
                'status' => 'diproses',
                'date' => '30 Okt 2026',
            ],
            [
                'name' => 'TPS Pasar Gede',
                'district' => 'Pasar Kliwon',
                'address' => 'Area Pasar Gede, Surakarta',
                'lat' => -7.5704,
                'lng' => 110.8323,
                'status' => 'menunggu',
                'date' => '31 Okt 2026',
            ],
            [
                'name' => 'TPS Laweyan',
                'district' => 'Laweyan',
                'address' => 'Area Laweyan, Surakarta',
                'lat' => -7.5709,
                'lng' => 110.7976,
                'status' => 'selesai',
                'date' => '31 Okt 2026',
            ],
            [
                'name' => 'TPS Serengan',
                'district' => 'Serengan',
                'address' => 'Area Serengan, Surakarta',
                'lat' => -7.5922,
                'lng' => 110.8192,
                'status' => 'menunggu',
                'date' => '1 Nov 2026',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DATA LAPORAN CONTOH
    |--------------------------------------------------------------------------
    */

    private function reports(): array
    {
        return [
            [
                'id' => 'SR-001',
                'title' => 'Sampah belum diangkut',
                'location' => 'TPS Pasar Gede',
                'resident' => 'Andi Pratama',
                'date' => '29 Okt 2026',
                'status' => 'menunggu',
            ],
            [
                'id' => 'SR-002',
                'title' => 'Penumpukan sampah rumah tangga',
                'location' => 'TPS Jebres',
                'resident' => 'Rina Putri',
                'date' => '28 Okt 2026',
                'status' => 'diproses',
            ],
            [
                'id' => 'SR-003',
                'title' => 'Sampah melebihi kapasitas',
                'location' => 'TPS Manahan',
                'resident' => 'Budi Santoso',
                'date' => '27 Okt 2026',
                'status' => 'selesai',
            ],
            [
                'id' => 'SR-004',
                'title' => 'Pengangkutan terlambat',
                'location' => 'TPS Serengan',
                'resident' => 'Dewi Lestari',
                'date' => '29 Okt 2026',
                'status' => 'menunggu',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER HALAMAN
    |--------------------------------------------------------------------------
    */

    private function render(string $view, array $extra = [])
    {
        return view($view, array_merge([
            'points' => $this->sampleLocations(),
            'reports' => $this->reports(),
        ], $extra));
    }

    /*
    |--------------------------------------------------------------------------
    | PEMERIKSAAN ROLE DEMO
    |--------------------------------------------------------------------------
    */

    private function checkRole(
        Request $request,
        array $allowedRoles
    ): ?RedirectResponse {
        abort_unless(app()->environment('local'), 403);

        $role = $request->session()->get('demo_role');

        if (!$role) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        abort_unless(
            in_array($role, $allowedRoles, true),
            403,
            'Anda tidak memiliki akses ke halaman ini.'
        );

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN PUBLIK
    |--------------------------------------------------------------------------
    */

    public function landing()
    {
        return $this->render('sampahrute.landing');
    }

    public function login(Request $request)
    {
        abort_unless(app()->environment('local'), 403);

        $role = $request->session()->get('demo_role');

        if (in_array($role, ['admin', 'petugas', 'warga'], true)) {
            return redirect()->route($role . '.dashboard');
        }

        return $this->render('sampahrute.auth.login');
    }

    public function map()
    {
        return $this->render('sampahrute.map');
    }

    public function locations()
    {
        return $this->render('sampahrute.locations');
    }

    public function schedule()
    {
        return $this->render('sampahrute.schedule');
    }

    /*
    |--------------------------------------------------------------------------
    | PROSES LOGIN DEMO
    |--------------------------------------------------------------------------
    */

    public function signIn(Request $request)
    {
        abort_unless(app()->environment('local'), 403);

        $data = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $accounts = [
            'admin' => [
                'password' => 'admin123',
                'role' => 'admin',
            ],
            'petugas' => [
                'password' => 'petugas123',
                'role' => 'petugas',
            ],
            'warga' => [
                'password' => 'warga123',
                'role' => 'warga',
            ],
        ];

        $username = strtolower(trim($data['email']));
        $password = $data['password'];

        $account = $accounts[$username] ?? null;

        if (
            !$account ||
            !hash_equals($account['password'], $password)
        ) {
            return back()
                ->withErrors([
                    'email' => 'Username atau password salah.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $request->session()->put([
            'demo_role' => $account['role'],
            'demo_username' => $username,
        ]);

        return redirect()
            ->route($account['role'] . '.dashboard')
            ->with('success', 'Berhasil masuk sebagai ' . ucfirst($account['role']));
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda berhasil keluar.');
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ADMIN
    |--------------------------------------------------------------------------
    */

    public function admin(Request $request)
    {
        if ($response = $this->checkRole($request, ['admin'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.admin.dashboard',
            ['role' => 'admin']
        );
    }

    public function adminReports(Request $request)
    {
        if ($response = $this->checkRole($request, ['admin'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.admin.reports',
            ['role' => 'admin']
        );
    }

    public function adminLocations(Request $request)
    {
        if ($response = $this->checkRole($request, ['admin'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.locations',
            ['role' => 'admin']
        );
    }

    public function adminSchedule(Request $request)
    {
        if ($response = $this->checkRole($request, ['admin'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.schedule',
            ['role' => 'admin']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD PETUGAS
    |--------------------------------------------------------------------------
    */

    public function officer(Request $request)
    {
        if ($response = $this->checkRole($request, ['petugas'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.petugas.dashboard',
            ['role' => 'petugas']
        );
    }

    public function tasks(Request $request)
    {
        if ($response = $this->checkRole($request, ['petugas'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.petugas.tasks',
            ['role' => 'petugas']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD WARGA
    |--------------------------------------------------------------------------
    */

    public function citizen(Request $request)
    {
        if ($response = $this->checkRole($request, ['warga'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.warga.dashboard',
            ['role' => 'warga']
        );
    }

    public function citizenReports(Request $request)
    {
        if ($response = $this->checkRole($request, ['warga'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.warga.reports',
            ['role' => 'warga']
        );
    }

    public function reportForm(Request $request)
    {
        if ($response = $this->checkRole($request, ['warga'])) {
            return $response;
        }

        return $this->render(
            'sampahrute.warga.create',
            ['role' => 'warga']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PENGIRIMAN LAPORAN DEMO
    |--------------------------------------------------------------------------
    */

    public function submitReport(Request $request)
    {
        if ($response = $this->checkRole($request, ['warga'])) {
            return $response;
        }

        $request->validate([
            'title' => 'required|string|max:150',
            'location' => 'required|string|max:150',
            'date' => 'required|date',
            'description' => 'required|string|min:10|max:2000',
            'photo' => 'nullable|image|max:3072',
        ]);

        return redirect()
            ->route('warga.reports')
            ->with(
                'success',
                'Form valid. Laporan belum disimpan karena masih mode demo.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS DEMO
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request)
    {
        if ($response = $this->checkRole(
            $request,
            ['admin', 'petugas']
        )) {
            return $response;
        }

        $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai',
        ]);

        return back()->with(
            'success',
            'Status diuji. Perubahan belum disimpan ke database.'
        );
    }
}
