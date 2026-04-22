<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sopir extends Model
{


    protected $table = 'sopirs';

    protected $fillable = [
        'user_id',
        'nama',
        'no_sim',
        'status',
    ];

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'sopir_id');
    }

    public function peminjaman()
{
    return $this->hasMany(Peminjaman::class, 'sopir_id');
}
    
}