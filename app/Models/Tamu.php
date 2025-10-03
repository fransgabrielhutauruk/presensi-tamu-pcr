<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Tamu extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'tamu';
    protected $primaryKey = 'tamu_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public function kunjungan()
    {
        return $this->hasMany(Kunjungan::class, 'fk_tamu_id', 'tamu_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', 'like', '%' . $email . '%');
    }

    public function scopeByPhone($query, $phone)
    {
        return $query->where('phone_number', $phone);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getFormattedPhoneAttribute()
    {
        return $this->phone_number;
    }

    public function getTotalKunjunganAttribute()
    {
        return $this->kunjungan()->count();
    }

    public function getLastKunjunganAttribute()
    {
        return $this->kunjungan()->latest('waktu_presensi')->first();
    }
}