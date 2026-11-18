<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:deploy-init', function () {
    $dbPath = database_path('database.sqlite');
    if (! file_exists($dbPath)) {
        touch($dbPath);
        $this->info('Created database/database.sqlite');
    }

    $this->call('migrate', ['--force' => true]);

    if (User::count() === 0) {
        $this->info('Seeding initial Surakarta data...');
        $this->call('db:seed', ['--force' => true]);
    }
})->purpose('Initialize SQLite database, run migrations, and seed if empty');
