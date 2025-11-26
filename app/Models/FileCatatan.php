<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileCatatan extends Model
{
    use HasFactory;
    
    // Sesuaikan dengan Migrasi: PK adalah 'id_file', string, non-incrementing
    protected $primaryKey = 'id_file';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'id_file', 
        'id_catatan',      // Foreign Key ke notes
        'id_reminder',     // Foreign Key ke reminders
        'nama_file',
        'tipe_file',
        'path_file',       // Path/URL penyimpanan di server
    ];

    // Relasi: File dimiliki oleh satu Catatan (Opsional/Nullable)
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'id_catatan', 'id');
    }
    
    // Relasi: File dimiliki oleh satu Reminder (Opsional/Nullable)
    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class, 'id_reminder', 'id');
    }
}