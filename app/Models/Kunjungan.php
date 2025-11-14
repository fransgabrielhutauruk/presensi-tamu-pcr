<?php

namespace App\Models;

use App\Models\Tamu;
use App\Models\Event;
use App\Models\KunjunganDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\KategoriTujuanEnum;

class Kunjungan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kunjungan';
    protected $primaryKey = 'kunjungan_id';

    protected $fillable = [
        'tamu_id',
        'event_id',
        'identitas',
        'kategori_tujuan',
        'jumlah_rombongan',
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
        'kategori_tujuan' => KategoriTujuanEnum::class
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