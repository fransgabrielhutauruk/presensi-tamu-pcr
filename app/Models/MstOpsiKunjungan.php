<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\CauserResolver;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MstOpsiKunjungan extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'mst_opsi_kunjungan';
    protected $primaryKey = 'opsikunjungan_id';

    protected $fillable = [
        'nama_opsi',
        'deskripsi_opsi',
        'nilai_opsi',
    ];

    protected $casts = [
        'nilai_opsi' => 'array',
    ];

    public static function getMultipleDropdownOptions(array $opsiNames): array
    {
        $models = self::whereIn('nama_opsi', $opsiNames)
            ->get()
            ->keyBy('nama_opsi');
        $results = [];
        foreach ($opsiNames as $name){
            $model = $models->get($name);
            $dataOpsi = $model->nilai_opsi ?? [];
            $results[$name] = collect($dataOpsi)->pluck('label', 'label')->toArray();
        }
        return $results;
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
            $model->created_by = userInisial();
        });

        static::updating(function ($model) {
            $model->updated_by = userInisial();
        });

        static::deleting(function ($model) {
            $model->deleted_by = userInisial();
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
        if (function_exists('causerActivityLog')) {
            CauserResolver::setCauser(causerActivityLog());
        }

        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName(env('APP_NAME'))
            ->setDescriptionForEvent(function ($eventName) {
                $aksi = function_exists('eventActivityLogBahasa') ? eventActivityLogBahasa($eventName) : $eventName;
                $user = function_exists('userInisial') ? userInisial() : 'System';
                return $user . " {$aksi} opsi kunjungan: {$this->nama_opsi}";
            });
    }
}