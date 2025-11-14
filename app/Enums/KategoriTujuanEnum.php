<?php

namespace App\Enums;

enum KategoriTujuanEnum: string
{
    case INSTANSI = 'instansi';
    case BISNIS = 'bisnis';
    case ORTU = 'ortu';
    case INFORMASI_KAMPUS = 'informasi_kampus';
    case LAINNYA = 'lainnya';
    case EVENT = 'event';

    public function description(): string
    {
        return match ($this) {
            self::INSTANSI => 'Kunjungan resmi dari instansi/lembaga',
            self::BISNIS => 'Kunjungan untuk keperluan bisnis',
            self::ORTU => 'Kunjungan orang tua mahasiswa',
            self::INFORMASI_KAMPUS => 'Mencari informasi tentang kampus',
            self::LAINNYA => 'Keperluan lainnya',
            self::EVENT => 'Kunjungan untuk menghadiri event',
        };
    }

    public static function getDescription(?string $value): string
    {
        $enum = self::tryFrom($value);
        return $enum?->description() ?? '-';
    }

    public static function fromValue(?string $value): ?self
    {
        if (!$value) return null;

        return self::tryFrom($value);
    }

    public static function isValid(?string $value): bool
    {
        if (!$value) return false;

        return in_array($value, array_column(self::cases(), 'value'));
    }
}
