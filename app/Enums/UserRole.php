<?php

namespace App\Enums;

enum UserRole: string
{
    case MAHASISWA = 'Mahasiswa';
    case STAF = 'Staf';
    case ADMIN = 'Admin';
    case EKSEKUTIF = 'Eksekutif';

    public static function getAllRoles(): array
    {
        return array_map(fn($role) => $role->value, self::cases());
    }

    public static function getAdminRoles(): array
    {
        return [
            self::ADMIN->value,
            self::EKSEKUTIF->value,
        ];
    }

    public static function getGeneralRoles(): array
    {
        return [
            self::MAHASISWA->value,
            self::STAF->value,
            self::ADMIN->value,
            self::EKSEKUTIF->value,
        ];
    }

    public static function getStudentStaffRoles(): array
    {
        return [
            self::MAHASISWA->value,
            self::ADMIN->value,
            self::EKSEKUTIF->value,
        ];
    }
}