<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tanggal tampil dalam Bahasa Indonesia dan zona waktu WIB.
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');
    }
}
