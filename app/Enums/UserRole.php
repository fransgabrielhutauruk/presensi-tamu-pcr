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
        return array_map(fn($role) => $role->value, self::cases());
    }

    public static function getAdminEksekutifSecurityRoles(): array
    {
        return [
            self::ADMIN->value,
            self::EKSEKUTIF->value,
            self::SECURITY->value,
        ];
    }
}
