<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\PickupLog;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_load_successfully(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/peta')->assertStatus(200);
        $this->get('/lokasi')->assertStatus(200);
        $this->get('/jadwal')->assertStatus(200);
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }

    public function test_authentication_and_role_middleware(): void
    {
        // Guest redirected to login
        $this->get('/admin/dashboard')->assertRedirect('/login');

        // Login as warga
        $this->post('/login', [
            'email' => 'warga',
            'password' => 'warga123',
        ])->assertRedirect('/warga/dashboard');

        $this->get('/warga/dashboard')->assertStatus(200);
        $this->get('/admin/dashboard')->assertStatus(403);
    }

    public function test_warga_registration_and_report_submission(): void
    {
        $this->post('/register', [
            'name' => 'Siti Aminah',
            'username' => 'siti.aminah',
            'contact' => '0812-3456-7890',
            'area' => 'Banjarsari',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/warga/dashboard');

        $this->assertDatabaseHas('users', [
            'username' => 'siti.aminah',
            'role' => 'warga',
        ]);

        // Submit report
        $this->post('/warga/laporan', [
            'title' => 'Tumpukan sampah plastik di sudut TPS',
            'category' => 'penumpukan',
            'location_id' => 'LOK-01',
            'date' => today()->toDateString(),
            'description' => 'Banyak kantong sampah plastik belum terangkut sejak pagi hari.',
        ])->assertRedirect('/warga/laporan/SR-009');

        $this->assertDatabaseHas('reports', [
            'code' => 'SR-009',
            'resident_name' => 'Siti Aminah',
            'location_code' => 'LOK-01',
            'status' => 'menunggu',
        ]);
    }

    public function test_admin_crud_and_report_workflow(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/dashboard')->assertStatus(200);
        $this->get('/admin/laporan')->assertStatus(200);
        $this->get('/admin/lokasi')->assertStatus(200);
        $this->get('/admin/jadwal')->assertStatus(200);
        $this->get('/admin/riwayat')->assertStatus(200);
        $this->get('/admin/pengguna')->assertStatus(200);

        // Add location
        $this->post('/demo/save', [
            'entity' => 'lokasi',
            'name' => 'TPS Sumber Baru',
            'type' => 'TPS',
            'district' => 'Banjarsari',
            'address' => 'Jl. Kahuripan, Sumber',
            'lat' => -7.5450,
            'lng' => 110.8100,
            'capacity' => 8,
        ])->assertRedirect();

        $this->assertDatabaseHas('locations', [
            'name' => 'TPS Sumber Baru',
            'district' => 'Banjarsari',
        ]);

        // Verify and assign report SR-001
        $this->post('/demo/status', [
            'report_id' => 'SR-001',
            'status' => 'diverifikasi',
        ])->assertRedirect();

        $this->post('/demo/status', [
            'report_id' => 'SR-001',
            'status' => 'diproses',
            'officer_id' => 'PTG-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'code' => 'SR-001',
            'status' => 'diproses',
            'officer_code' => 'PTG-01',
        ]);
    }

    public function test_petugas_pickup_confirmation(): void
    {
        $petugas = User::where('username', 'petugas')->firstOrFail();
        $this->actingAs($petugas);

        $this->get('/petugas/dashboard')->assertStatus(200);
        $this->get('/petugas/tugas')->assertStatus(200);
        $this->get('/petugas/riwayat')->assertStatus(200);

        $this->post('/demo/pickup', [
            'location_id' => 'LOK-06',
            'status' => 'selesai',
            'time' => '08:30',
            'volume' => 4.5,
            'note' => 'Area sudah bersih.',
        ])->assertRedirect();

        $this->assertEquals('selesai', Location::where('code', 'LOK-06')->value('status'));
        $this->assertTrue(PickupLog::where('location_code', 'LOK-06')->where('actual_time', '08:30')->exists());
    }
}
