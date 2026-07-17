<?php

namespace App\Enums;

enum UserRole: string
{
    case MAHASISWA = 'Mahasiswa';
    case STAF = 'Staf';
    case ADMIN = 'Admin';
    case EKSEKUTIF = 'Eksekutif';
    case SECURITY = 'Security';

    public static function getAllRoles(): array
    {
        return array_map(fn ($role) => $role->value, self::cases());
    }

    public static function getAdminEksekutifStafMahasiswaRoles(): array
    {
        return [
            self::ADMIN->value,
            self::EKSEKUTIF->value,
            self::STAF->value,
            self::MAHASISWA->value,
        ];
    }

    public static function getAdminEksekutifSecurityRoles(): array
    {
        return [
            self::ADMIN->value,
            self::EKSEKUTIF->value,
            self::SECURITY->value,
        ];
    }

    public static function getCivitasRoles(): array
    {
        return [
            self::STAF->value,
            self::MAHASISWA->value,
        ];
    }

    public static function getAdminEksekutifRoles(): array
    {
        return [
            self::ADMIN->value,
            self::EKSEKUTIF->value,
        ];
    }

    public static function getDefaultRoute(string $role): string
    {
        return match ($role) {
            self::ADMIN->value, self::EKSEKUTIF->value => '/app/dashboard',
            self::SECURITY->value => '/app/kunjungan/monitoring',
            self::STAF->value, self::MAHASISWA->value => '/app/event',
            default => '/app/dashboard',
        };
    }
}
