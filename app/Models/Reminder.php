<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reminder extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'id', 
        'user_id',
        'jenis_reminder', // Dari ERD
        'tanggal',
        'jam',
        'keterangan', // Dari ERD (description di Kotlin)
    ];

    // Relasi: Reminder dimiliki oleh satu User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Satu Reminder bisa memiliki banyak File
    public function files(): HasMany
    {
        return $this->hasMany(FileCatatan::class, 'id_reminder', 'id');
    }
}