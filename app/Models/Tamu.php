<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tamu extends Model
{
    use HasFactory;

    protected $table = 'tamu';
    protected $primaryKey = 'tamu_id';

    protected $fillable = [
        'name',
        'gender', 
        'email',
        'phone_number',
    ];

    public function kunjungans()
    {
        return $this->hasMany(Kunjungan::class, 'tamu_id', 'tamu_id');
    }
}