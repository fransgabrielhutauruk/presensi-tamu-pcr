<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Feedback extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'feedback';
    protected $primaryKey = 'feedback_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'fk_kunjungan_id',
        'rating',
        'komentar',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'deleted_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    const RATING_VERY_BAD = 1;
    const RATING_BAD = 2;
    const RATING_NEUTRAL = 3;
    const RATING_GOOD = 4;
    const RATING_EXCELLENT = 5;

    public static function getRatingLabels()
    {
        return [
            self::RATING_VERY_BAD => 'Sangat Buruk',
            self::RATING_BAD => 'Buruk',
            self::RATING_NEUTRAL => 'Netral',
            self::RATING_GOOD => 'Baik',
            self::RATING_EXCELLENT => 'Sangat Baik',
        ];
    }

    /**
     * Relationship dengan Kunjungan
     */
    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'fk_kunjungan_id', 'kunjungan_id');
    }

    /**
     * Scope untuk data aktif (tidak dihapus)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope untuk filter berdasarkan rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope untuk rating positif (4-5)
     */
    public function scopePositive($query)
    {
        return $query->whereIn('rating', [4, 5]);
    }

    /**
     * Scope untuk rating negatif (1-2)
     */
    public function scopeNegative($query)
    {
        return $query->whereIn('rating', [1, 2]);
    }

    /**
     * Accessor untuk rating dalam bentuk bintang
     */
    public function getRatingStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Accessor untuk label rating
     */
    public function getRatingLabelAttribute()
    {
        return self::getRatingLabels()[$this->rating] ?? 'Unknown';
    }

    /**
     * Check if rating is positive
     */
    public function isPositive()
    {
        return in_array($this->rating, [4, 5]);
    }

    /**
     * Check if rating is negative
     */
    public function isNegative()
    {
        return in_array($this->rating, [1, 2]);
    }
}