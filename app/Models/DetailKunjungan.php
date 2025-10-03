<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DetailKunjungan extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'detail_kunjungan';
    protected $primaryKey = 'detailkunjungan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fk_kunjungan_id',
        'kunci',
        'nilai',
        'urutan',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_by',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'fk_kunjungan_id', 'kunjungan_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    public function scopeByKey($query, $kunci)
    {
        return $query->where('kunci', $kunci);
    }

    public function getFormattedAttribute()
    {
        return "{$this->kunci}: {$this->nilai}";
    }

    // Static method untuk standar keys yang sering digunakan
    public static function getStandardKeys()
    {
        return [
            'keperluan' => 'Keperluan Kunjungan',
            'instansi' => 'Nama Instansi',
            'divisi' => 'Divisi/Bagian',
            'pic' => 'Person in Charge',
            'alamat' => 'Alamat Asal',
            'catatan' => 'Catatan Tambahan'
        ];
    }
}