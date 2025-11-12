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
        Schema::create('file_notes', function (Blueprint $table) {
            // PK
            $table->string('id_file')->primary(); 

            // FK: Opsional terikat ke Catatan
            $table->string('id_catatan')->nullable(); 
            $table->foreign('id_catatan')->references('id')->on('notes')->onDelete('cascade');

            // FK: Opsional terikat ke Reminder
            $table->string('id_reminder')->nullable(); 
            $table->foreign('id_reminder')->references('id')->on('reminders')->onDelete('cascade');

            // Kolom File
            $table->string('nama_file');
            $table->string('tipe_file');
            $table->string('path_file'); // Path/URL penyimpanan file di server

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_notes');
    }
};
