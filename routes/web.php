<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SampahRuteDemoController;

/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK
|--------------------------------------------------------------------------
*/

Route::get('/', [
    SampahRuteDemoController::class, 'landing'
])->name('home');

Route::get('/login', [
    SampahRuteDemoController::class, 'login'
])->name('login');

Route::post('/login', [
    SampahRuteDemoController::class, 'signIn'
])->name('demo.signin');

Route::post('/logout', [
    SampahRuteDemoController::class, 'logout'
])->name('logout');

Route::get('/peta', [
    SampahRuteDemoController::class, 'map'
])->name('map');

Route::get('/lokasi', [
    SampahRuteDemoController::class, 'locations'
])->name('locations');

Route::get('/jadwal', [
    SampahRuteDemoController::class, 'schedule'
])->name('schedule');


/*
|--------------------------------------------------------------------------
| HALAMAN ADMIN
|--------------------------------------------------------------------------
*/

Route::get('/admin/dashboard', [
    SampahRuteDemoController::class, 'admin'
])->name('admin.dashboard');

Route::get('/admin/laporan', [
    SampahRuteDemoController::class, 'adminReports'
])->name('admin.reports');

Route::get('/admin/lokasi', [
    SampahRuteDemoController::class, 'adminLocations'
])->name('admin.locations');

Route::get('/admin/jadwal', [
    SampahRuteDemoController::class, 'adminSchedule'
])->name('admin.schedule');


/*
|--------------------------------------------------------------------------
| HALAMAN PETUGAS
|--------------------------------------------------------------------------
*/

Route::get('/petugas/dashboard', [
    SampahRuteDemoController::class, 'officer'
])->name('petugas.dashboard');

Route::get('/petugas/tugas', [
    SampahRuteDemoController::class, 'tasks'
])->name('petugas.tasks');


/*
|--------------------------------------------------------------------------
| HALAMAN WARGA
|--------------------------------------------------------------------------
*/

Route::get('/warga/dashboard', [
    SampahRuteDemoController::class, 'citizen'
])->name('warga.dashboard');

Route::get('/warga/laporan', [
    SampahRuteDemoController::class, 'citizenReports'
])->name('warga.reports');

Route::get('/warga/laporan/buat', [
    SampahRuteDemoController::class, 'reportForm'
])->name('warga.create');

Route::post('/warga/laporan', [
    SampahRuteDemoController::class, 'submitReport'
])->name('warga.store');


/*
|--------------------------------------------------------------------------
| UPDATE STATUS DEMO
|--------------------------------------------------------------------------
*/

Route::post('/demo/status', [
    SampahRuteDemoController::class, 'updateStatus'
])->name('demo.status');
