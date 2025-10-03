<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Kunjungan extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'kunjungan';
    protected $primaryKey = 'kunjungan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fk_tamu_id',
        'fk_event_id',
        'kategori_tujuan',
        'waktu_presensi',
        'waktu_masuk',
        'waktu_keluar',
        'transportasi',
        'status_validasi',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_by',
    ];

    protected $casts = [
        'waktu_presensi' => 'datetime',
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'status_validasi' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    const KATEGORI_INSTANSI = 'instansi';
    const KATEGORI_BISNIS = 'bisnis';
    const KATEGORI_ORTU = 'ortu';
    const KATEGORI_CALON_ORTU = 'calon_ortu';
    const KATEGORI_LAINNYA = 'lainnya';

    public static function getKategoriOptions()
    {
        return [
            self::KATEGORI_INSTANSI => 'Instansi',
            self::KATEGORI_BISNIS => 'Bisnis',
            self::KATEGORI_ORTU => 'Orang Tua',
            self::KATEGORI_CALON_ORTU => 'Calon Orang Tua',
            self::KATEGORI_LAINNYA => 'Lainnya',
        ];
    }

    public function tamu()
    {
        return $this->belongsTo(Tamu::class, 'fk_tamu_id', 'tamu_id');
    }

    public function detailKunjungan()
    {
        return $this->hasMany(DetailKunjungan::class, 'fk_kunjungan_id', 'kunjungan_id');
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class, 'fk_kunjungan_id', 'kunjungan_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori_tujuan', $kategori);
    }

    public function scopeValidated($query, $validated = true)
    {
        return $query->where('status_validasi', $validated);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('waktu_presensi', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('waktu_presensi', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('waktu_presensi', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('waktu_presensi', now()->month)
                    ->whereYear('waktu_presensi', now()->year);
    }

    public function isValidated()
    {
        return $this->status_validasi;
    }

    public function isActive()
    {
        return is_null($this->waktu_keluar);
    }

    public function getDurationAttribute()
    {
        if ($this->waktu_keluar) {
            return $this->waktu_masuk->diffInMinutes($this->waktu_keluar);
        }
        return $this->waktu_masuk->diffInMinutes(now());
    }

    public function getDetailsAttribute()
    {
        return $this->detailKunjungan()
            ->orderBy('urutan')
            ->pluck('nilai', 'kunci')
            ->toArray();
    }

    public function getKategoriLabelAttribute()
    {
        return self::getKategoriOptions()[$this->kategori_tujuan] ?? $this->kategori_tujuan;
    }
}