<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';
    protected $primaryKey = 'kunjungan_id';

    protected $fillable = [
        'tamu_id',
        'kategori_tujuan',
        'waktu_masuk',
        'waktu_keluar',
        'transportasi',
        'status_validasi',
        'waktu_presensi',
    ];

    protected $casts = [
        'status_validasi' => 'boolean',
        'waktu_presensi' => 'datetime',
    ];

    public function tamu()
    {
        return $this->belongsTo(Tamu::class, 'tamu_id', 'tamu_id');
    }

    public function details()
    {
        return $this->hasMany(KunjunganDetail::class, 'kunjungan_id', 'kunjungan_id');
    }
}