<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Facades\CauserResolver;

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

    public static function getMultipleDropdownOptions(array $opsiNames, string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        
        $models = self::whereIn('nama_opsi', $opsiNames)
            ->get()
            ->keyBy('nama_opsi');
        
        $results = [];
        foreach ($opsiNames as $name){
            $model = $models->get($name);
            $dataOpsi = $model->nilai_opsi ?? [];
            
            $isMultiLanguage = !empty($dataOpsi) && isset($dataOpsi[0]['id']) && isset($dataOpsi[0]['en']);
            
            if ($isMultiLanguage) {
                $results[$name] = collect($dataOpsi)->pluck($locale, 'id')->toArray();
            } else {
                $results[$name] = collect($dataOpsi)->pluck('label', 'label')->toArray();
            }
        }
        return $results;
    }

    public static function getDropdownOptions(string $opsiName, string $locale = null): array
    {
        $result = self::getMultipleDropdownOptions([$opsiName], $locale);
        return $result[$opsiName] ?? [];
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
        if (function_exists('causerActivityLog')) {
            CauserResolver::setCauser(causerActivityLog());
        }

        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName(env('APP_NAME'))
            ->setDescriptionForEvent(function ($eventName) {
                $aksi = eventActivityLogBahasa($eventName);
                return "{$aksi} opsi kunjungan: {$this->nama_opsi}";
            });
    }
}