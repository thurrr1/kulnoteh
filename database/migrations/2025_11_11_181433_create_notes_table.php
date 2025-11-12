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
        Schema::create('notes', function (Blueprint $table) {
            // PK
            $table->string('id')->primary(); 

            // FK: Wajib terikat ke User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // FK: Wajib terikat ke JadwalKelas (schedules) - Sesuai konfirmasi Anda
            $table->string('id_jadwal'); 
            $table->foreign('id_jadwal')->references('id')->on('schedules')->onDelete('cascade');

            // Kolom Catatan
            $table->string('judul_catatan');
            $table->longText('isi_teks')->nullable(); // Boleh NULL (jika catatan hanya berisi file)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
