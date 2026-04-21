<?php

namespace App\Models\Dimension;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Facades\CauserResolver;
use Spatie\Activitylog\LogOptions;

class DmPegawai extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dm_pegawai';
    protected $primaryKey = 'pegawai_id';

    protected $fillable = [
        'nip',
        'nama',
        'email',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static $exceptEdit = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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
            $model->deleted_by = null;
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        CauserResolver::setCauser(causerActivityLog());

        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName(env('APP_NAME'))
            ->setDescriptionForEvent(function ($eventName) {
                $aksi = eventActivityLogBahasa($eventName);
                return "{$aksi} pegawai";
            });
    }

    public static function findByNip(string $nip): ?self
    {
        return static::where('nip', $nip)->first();
    }

    public static function upsertByNip(array $data): self
    {
        $pegawai = static::findByNip($data['nip']);

        if ($pegawai) {
            $pegawai->update($data);
        } else {
            $pegawai = static::create($data);
        }

        return $pegawai;
    }

    public static function getDataDetail($where = [], $get = true)
    {
        $query = static::query()
            ->select('*')
            ->where($where)
            ->orderBy('nama', 'asc');

        return $get ? $query->get() : $query;
    }
}
