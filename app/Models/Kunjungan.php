<?php

namespace App\Models;

use App\Models\Tamu;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\KunjunganDetail;
use App\Enums\KategoriTujuanEnum;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Facades\CauserResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kunjungan extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

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
        'is_vip',
    ];

    protected $casts = [
        'status_validasi' => 'boolean',
        'is_checkout' => 'boolean',
        'is_vip' => 'boolean',
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

    public static function getIdentitasBadge($identitas, $is_vip=false): string
    {
        if ($identitas == 'non-civitas' && $is_vip == false) {
            return '<span class="badge badge-warning">Non-Civitas</span>';
        } elseif ($identitas == 'non-civitas' && $is_vip == true) {
            return '<span class="badge badge-info">VIP Non-Civitas</span>';
        } elseif ($identitas == 'civitas') {
            return '<span class="badge badge-primary">Civitas PCR</span>';
        } else {
            return '<span class="badge badge-light">' . ($identitas ?? 'Tidak Diketahui') . '</span>';
        }
    }

    public static function getJenisKunjunganBadge($eventId): string
    {
        if (!empty($eventId)) {
            return '<span class="badge badge-info">Event</span>';
        } else {
            return '<span class="badge badge-secondary">Non-Event</span>';
        }
    }

    public static function getStatusValidasiBadge($statusValidasi): string
    {
        if ($statusValidasi) {
            return '<span class="badge badge-success">Tervalidasi</span>';
        } else {
            return '<span class="badge badge-warning">Belum Validasi</span>';
        }
    }

    public static function getStatusCheckoutBadge($isCheckout): string
    {
        if ($isCheckout) {
            return '<span class="badge badge-success">Sudah Checkout</span>';
        } else {
            return '<span class="badge badge-warning">Belum Checkout</span>';
        }
    }

    /**
     * fungsi yang di panggil saat event crud dijalankan
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = userId();
        });

        static::updating(function ($model) {
            $model->updated_by = userId();
        });

        static::deleting(function ($model) {
            $model->deleted_by = userId();
            $model->update();
        });

        static::restoring(function ($model) {
            $model->deleted_by = NULL;
        });
    }

    /**
     * fungsi yang di panggil setelah proses crud selesai dijalankan (event trigger) untuk proses pencatatan log
     * pencatatan log menggunakan spatie/activitylogging
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        CauserResolver::setCauser(causerActivityLog());
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName(env('APP_NAME'))
            ->setDescriptionForEvent(function ($eventName) {
                $aksi = eventActivityLogBahasa($eventName);
                return "{$aksi} kunjungan";
            });
    }
}
