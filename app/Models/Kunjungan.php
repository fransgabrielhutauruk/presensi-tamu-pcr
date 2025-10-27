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
        'event_id',
        'identitas',
        'kategori_tujuan',
        'waktu_masuk',
        'waktu_keluar',
        'transportasi',
        'status_validasi',
        'waktu_presensi',
        'is_checkout',
        'checkout_time',
        'reminder_sent_at',
    ];

    protected $casts = [
        'status_validasi' => 'boolean',
        'is_checkout' => 'boolean',
        'waktu_presensi' => 'datetime',
        'checkout_time' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function tamu()
    {
        return $this->belongsTo(Tamu::class, 'tamu_id', 'tamu_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    public function details()
    {
        return $this->hasMany(KunjunganDetail::class, 'kunjungan_id', 'kunjungan_id');
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class, 'kunjungan_id', 'kunjungan_id');
    }
}