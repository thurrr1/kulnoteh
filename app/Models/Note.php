<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'id', 
        'user_id',
        'id_jadwal', // Foreign Key ke schedules.id
        'judul_catatan',
        'isi_teks', // Deprecated: Gunakan content_json
        'content_json', // JSON array dari List<NoteContentItem>
    ];

    // Cast content_json sebagai array agar otomatis di-decode dari JSON
    protected $casts = [
        'content_json' => 'array',
    ];

    // Relasi: Note dimiliki oleh satu Schedule
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'id_jadwal', 'id');
    }
    
    // Relasi: Note dimiliki oleh satu User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Satu Note bisa memiliki banyak File
    public function files(): HasMany
    {
        return $this->hasMany(FileCatatan::class, 'id_catatan', 'id');
    }
}