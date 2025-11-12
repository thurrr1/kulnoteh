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
        Schema::create('schedules', function (Blueprint $table) {
            // PK: Kita sepakati menggunakan string/UUID untuk konsistensi dengan Kotlin
            $table->string('id')->primary(); 
            
            // FK: Wajib (NOT NULL) terikat ke User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Kolom Jadwal dan Mata Kuliah
            $table->string('nama_matakuliah');
            $table->unsignedSmallInteger('sks'); // 1-6 SKS
            $table->string('dosen')->nullable();
            
            // Kolom Waktu dan Lokasi
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruangan')->nullable(); // Ruangan bisa opsional

            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
