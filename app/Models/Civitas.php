<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Civitas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'civitas';
    protected $primaryKey = 'civitas_id';

    protected $fillable = [
        'nama_civitas',
        'nip',
        'nim',
        'jenis_kelamin',
        'nomor_telepon',
        'email',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function kunjungans()
    {
        return $this->hasMany(Kunjungan::class, 'civitas_id', 'civitas_id');
    }
}
