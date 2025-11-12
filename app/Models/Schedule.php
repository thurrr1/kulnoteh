<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Wajib import

class Schedule extends Model
{
    use HasFactory;
    
    // Override default PK setting
    protected $primaryKey = 'id';
    public $incrementing = false; // Karena kita menggunakan string/UUID, bukan auto-increment
    protected $keyType = 'string';

    protected $fillable = [
        'id', // ID harus masuk fillable karena diisi dari client (UUID) atau di generate sebelum save
        'user_id',
        'nama_matakuliah',
        'sks',
        'dosen',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
    ];
    
    // Relasi: Setiap Schedule dimiliki oleh satu User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}