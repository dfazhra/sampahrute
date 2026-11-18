<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('officers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g. PTG-01
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('area')->nullable();
            $table->string('vehicle')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g. LOK-01
            $table->string('name', 100);
            $table->string('type', 30); // TPS, Depo, Kontainer
            $table->string('district', 60);
            $table->string('village', 60)->nullable();
            $table->string('address', 200);
            $table->decimal('lat', 10, 6);
            $table->decimal('lng', 10, 6);
            $table->decimal('capacity', 6, 2)->default(6);
            $table->unsignedTinyInteger('fill')->default(20);
            $table->json('days'); // [1,2,3,4,5,6,7]
            $table->string('window', 30)->default('06:00–08:00');
            $table->string('officer_code', 20)->nullable()->index();
            $table->string('status', 20)->default('menunggu'); // menunggu, diproses, selesai, libur
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g. SR-001
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resident_name', 100);
            $table->string('location_code', 20)->index();
            $table->string('officer_code', 20)->nullable()->index();
            $table->string('title', 150);
            $table->string('category', 40);
            $table->date('incident_date')->nullable();
            $table->text('description');
            $table->string('photo')->nullable();
            $table->string('status', 20)->default('menunggu')->index(); // menunggu, diverifikasi, diproses, selesai, ditolak
            $table->string('priority', 20)->default('sedang'); // rendah, sedang, tinggi
            $table->string('reject_reason', 300)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pickup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique(); // e.g. LOG-LOK-01-1118
            $table->date('pickup_date')->index();
            $table->string('location_code', 20)->index();
            $table->string('location_name', 100);
            $table->string('district', 60);
            $table->string('officer_code', 20)->nullable()->index();
            $table->string('officer_name', 100);
            $table->string('window', 30);
            $table->string('actual_time', 10)->nullable(); // H:i
            $table->decimal('volume', 6, 2)->nullable();
            $table->string('result', 20)->default('tepat'); // tepat, terlambat, terlewat
            $table->string('note', 300)->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_logs');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('officers');
    }
};
