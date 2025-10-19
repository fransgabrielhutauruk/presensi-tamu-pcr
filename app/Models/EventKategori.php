<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\CauserResolver;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EventKategori extends Model
{
    use SoftDeletes;
    use LogsActivity;

    /**
     * definisi nama table
     *
     * @var string
     */
    public $table = 'event_kategori';

    /**
     * set kolom primary key, default primary key kolom adalah id
     *
     * @var string
     */
    protected $primaryKey = 'eventkategori_id';

    /**
     * kolom-kolom yang dapat di ubah data nya
     *
     * @var array
     */
    public $fillable = [
        'nama_kategori',
        'deskripsi_kategori',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * kolom yang tipe data hasil nya perlu diubah
     *
     * @var array
     */
    protected $casts = [];

    public static array $exceptEdit = [
        'eventkategori_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'eventkategori_id', 'eventkategori_id');
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
                return $user . " {$aksi} kategori event: {$this->nama_kategori}";
            });
    }

    /**
     * fungsi kustom, untuk proses insert multiple data row dari controller
     * proses insert dilakukan berulang agar event trigger dari ORM dijalankan
     * event trigger diperlukan untuk proses pencatatan logging model secara otomatis
     *
     * @param  mixed $data
     * @return void
     */
    public static function insertBatch($data = [])
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                self::create($value);
            }
        }
    }

    /**
     * fungsi kustom, untuk proses hapus data dengan kondisi (where option)
     * kondisi where akan melakukan hapus data belalui query builder
     * proses hapus akan dilakukan berulang untuk setiap data dengan looping 
     * penghapusan dilakukan berulang agar event trigger dari ORM dijalankan
     * event trigger diperlukan untuk proses pencatatan logging model secara otomatis
     *
     * @param  mixed $where
     * @return void
     */
    public static function deleteDataWhere($where)
    {
        $dt = self::where($where)->get();
        if ($dt) {
            foreach ($dt as $key => $value) {
                $value->delete();
            }
        }
    }

    /**
     * fungsi kustom, untuk proses update data dengan kondisi (where option)
     * kondisi where akan melakukan update data belalui query builder
     * proses update akan dilakukan berulang untuk setiap data dengan looping 
     * pembaruan data dilakukan berulang agar event trigger dari ORM dijalankan
     * event trigger diperlukan untuk proses pencatatan logging model secara otomatis
     *
     * @param  mixed $where
     * @param  mixed $data
     * @return void
     */
    public static function updateDataWhere($where, $data)
    {
        $dt = self::where($where)->get();
        if ($dt) {
            foreach ($dt as $key => $value) {
                $value->update($data);
            }
        }
    }

    /**
     * fungsi kustom untuk menghasilkan data model secara detail (rinci) dengan seluruh kemungkinan join yang terjadi
     * fungsi ini akan menggunakan query builder secara langsung bukan ORM Eloquent
     * query builder pada data detail digunakan untuk optimasi hasil query yang lebih cepat
     * function detail ini biasa digunakan sebagai penyedia data untuk datatable
     *
     * @param  mixed $where
     */
    public static function getDataDetail($where = [], $whereBinding = [], $get = true)
    {
        $query = DB::table('event_kategori')
            ->selectRaw('*')
            ->from('event_kategori as a')
            ->where(function ($query) use ($where) {
                if (function_exists('notRaw')) {
                    $query->where(notRaw($where));
                }
            })
            ->where(function ($query) use ($where, $whereBinding) {
                if (function_exists('withRaw')) {
                    $query->whereRaw(withRaw($where), $whereBinding);
                }
            })
            ->whereNull('a.deleted_at');

        return $get ? $query->get() : $query;
    }
}
