<?php

use App\Http\Controllers\SampahRuteDemoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HALAMAN PUBLIK & AUTENTIKASI
|--------------------------------------------------------------------------
*/

Route::get('/', [SampahRuteDemoController::class, 'landing'])->name('home');
Route::get('/peta', [SampahRuteDemoController::class, 'map'])->name('map');
Route::get('/lokasi', [SampahRuteDemoController::class, 'locations'])->name('locations');
Route::get('/jadwal', [SampahRuteDemoController::class, 'schedule'])->name('schedule');

Route::get('/login', [SampahRuteDemoController::class, 'login'])->name('login');
Route::post('/login', [SampahRuteDemoController::class, 'signIn'])->name('demo.signin');
Route::get('/register', [SampahRuteDemoController::class, 'register'])->name('register');
Route::post('/register', [SampahRuteDemoController::class, 'registerStore'])->name('register.store');
Route::post('/logout', [SampahRuteDemoController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| HALAMAN ADMIN (Middleware role:admin)
|--------------------------------------------------------------------------
*/

Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [SampahRuteDemoController::class, 'admin'])->name('dashboard');
    Route::get('/laporan', [SampahRuteDemoController::class, 'adminReports'])->name('reports');
    Route::get('/lokasi', [SampahRuteDemoController::class, 'adminLocations'])->name('locations');
    Route::get('/jadwal', [SampahRuteDemoController::class, 'adminSchedule'])->name('schedule');
    Route::get('/riwayat', [SampahRuteDemoController::class, 'adminHistory'])->name('history');
    Route::get('/pengguna', [SampahRuteDemoController::class, 'adminUsers'])->name('users');
});

/*
|--------------------------------------------------------------------------
| HALAMAN PETUGAS (Middleware role:petugas)
|--------------------------------------------------------------------------
*/

Route::middleware('role:petugas')->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [SampahRuteDemoController::class, 'officer'])->name('dashboard');
    Route::get('/tugas', [SampahRuteDemoController::class, 'tasks'])->name('tasks');
    Route::get('/riwayat', [SampahRuteDemoController::class, 'officerHistory'])->name('history');
});

/*
|--------------------------------------------------------------------------
| HALAMAN WARGA (Middleware role:warga)
|--------------------------------------------------------------------------
*/

Route::middleware('role:warga')->prefix('warga')->name('warga.')->group(function () {
    Route::get('/dashboard', [SampahRuteDemoController::class, 'citizen'])->name('dashboard');
    Route::get('/laporan', [SampahRuteDemoController::class, 'citizenReports'])->name('reports');
    Route::get('/laporan/buat', [SampahRuteDemoController::class, 'reportForm'])->name('create');
    Route::post('/laporan', [SampahRuteDemoController::class, 'submitReport'])->name('store');
    Route::get('/laporan/{id}', [SampahRuteDemoController::class, 'citizenReport'])->name('show');
});

/*
|--------------------------------------------------------------------------
| AKSI DATABASE (Update Status, Konfirmasi Angkut, CRUD Admin, Reset)
|--------------------------------------------------------------------------
*/

Route::post('/demo/status', [SampahRuteDemoController::class, 'updateStatus'])
    ->middleware('role:admin,petugas')
    ->name('demo.status');

Route::post('/demo/pickup', [SampahRuteDemoController::class, 'confirmPickup'])
    ->middleware('role:petugas')
    ->name('demo.pickup');

Route::post('/demo/save', [SampahRuteDemoController::class, 'saveDemo'])
    ->middleware('role:admin')
    ->name('demo.save');

Route::post('/demo/reset', [SampahRuteDemoController::class, 'resetDemo'])
    ->middleware('role:admin,petugas,warga')
    ->name('demo.reset');
