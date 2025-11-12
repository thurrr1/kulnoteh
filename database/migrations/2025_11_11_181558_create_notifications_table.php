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
        Schema::create('notifications', function (Blueprint $table) {
            // PK
            $table->string('id_notifikasi')->primary(); 

            // FK: Wajib terikat ke User (untuk autentikasi)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // FK: Opsional terikat ke Reminder (bisa NULL)
            $table->string('id_reminder')->nullable(); 
            $table->foreign('id_reminder')->references('id')->on('reminders')->onDelete('cascade');
            
            // FK: Opsional terikat ke JadwalKelas (bisa NULL)
            // Walaupun Reminder tidak terhubung langsung ke JadwalKelas, Notifikasi bisa terhubung.
            $table->string('id_jadwal')->nullable(); 
            $table->foreign('id_jadwal')->references('id')->on('schedules')->onDelete('cascade');

            // Kolom Notifikasi
            $table->text('isi_notifikasi'); // Teks notifikasi yang ditampilkan
            $table->string('status')->default('unread'); // Status: read/unread
            $table->timestamp('waktu_notifikasi'); // Kapan notifikasi ini dikirim/dicatat

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
