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
        Schema::create('reminders', function (Blueprint $table) {
            // PK
            $table->string('id')->primary(); 

            // FK: Wajib terikat ke User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Kolom Reminder
            $table->string('jenis_reminder'); // Misalnya 'Tugas', 'Ujian', 'Mandiri'
            $table->date('tanggal');
            $table->time('jam');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
