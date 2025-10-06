<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KunjunganDetail extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_detail';
    protected $primaryKey = 'kunjungandetail_id';

    protected $fillable = [
        'kunjungan_id',
        'kunci',
        'nilai',
        'urutan',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the kunjungan that ownsp the detail.
     */
    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id', 'kunjungan_id');
    }
}