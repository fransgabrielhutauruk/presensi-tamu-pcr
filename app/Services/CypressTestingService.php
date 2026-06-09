<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Civitas;
use App\Models\Dimension\DmPegawai;
use App\Models\Event;
use App\Models\EventKategori;
use Illuminate\Http\Request;

class CypressTestingService
{
    public function isMockEnabled(Request $request): bool
    {
        return app()->environment('local') && $this->hasCypressSignal($request);
    }

    public function shouldUseMockCallback(Request $request): bool
    {
        return $this->isMockEnabled($request);
    }

    public function getScenario(Request $request, string $default = 'single-role'): string
    {
        $scenario = (string) ($request->query('cy_scenario')
            ?? $request->attributes->get('cy_scenario')
            ?? ($request->hasSession() ? $request->session()->get('cy_scenario') : null)
            ?? $default);

        return $scenario !== '' ? $scenario : $default;
    }

    public function resolveMockIdentity(Request $request): array
    {
        $scenario = $this->getScenario($request);

        if ($scenario === 'multi-role') {
            return [
                'multi-role@pcr.ac.id',
                'Multi Role PCR',
                [UserRole::STAF->value, UserRole::ADMIN->value],
            ];
        }

        if ($scenario === 'eksekutif-role') {
            return [
                'eksekutif@pcr.ac.id',
                'Eksekutif PCR',
                [UserRole::EKSEKUTIF->value],
            ];
        }

        if ($scenario === 'security-role') {
            return [
                'security@pcr.ac.id',
                'Security PCR',
                [UserRole::SECURITY->value],
            ];
        }

        if ($scenario === 'mahasiswa-role') {
            return [
                'mahasiswa@mahasiswa.pcr.ac.id',
                'Mahasiswa PCR',
                [UserRole::MAHASISWA->value],
            ];
        }

        if ($scenario === 'non-campus') {
            return [
                'user@gmail.com',
                'User Gmail',
                [],
            ];
        }

        return [
            'staf@pcr.ac.id',
            'Staf PCR',
            [UserRole::STAF->value],
        ];
    }

    public function setMockActiveRole(Request $request): void
    {
        if (!$this->isMockEnabled($request) || !$request->hasSession()) {
            return;
        }

        $scenario = $this->getScenario($request);
        if ($scenario === 'multi-role') {
            session(['active_role' => UserRole::STAF->value]);
        }
        if ($scenario === 'eksekutif-role') {
            session(['active_role' => UserRole::EKSEKUTIF->value]);
        }
        if ($scenario === 'security-role') {
            session(['active_role' => UserRole::SECURITY->value]);
        }
        if ($scenario === 'mahasiswa-role') {
            session(['active_role' => UserRole::MAHASISWA->value]);
        }
    }

    public function ensureEventKategoriExists(): void
    {
        EventKategori::firstOrCreate(
            ['nama_kategori' => 'Kategori Cypress'],
            ['deskripsi_kategori' => 'Kategori event untuk pengujian Cypress']
        );
    }

    public function ensureEventFixturesForToday(): void
    {
        Civitas::withTrashed()->where('nip', '123456')->forceDelete();
        Civitas::withTrashed()->where('nim', '2312098765')->forceDelete();

        $eventKategori = EventKategori::firstOrCreate(
            ['nama_kategori' => 'Kategori Cypress'],
            ['deskripsi_kategori' => 'Kategori event untuk pengujian Cypress']
        );

        Event::firstOrCreate(
            [
                'nama_event' => 'Event Cypress Hari Ini',
                'tanggal_event' => now()->toDateString(),
            ],
            [
                'eventkategori_id' => $eventKategori->eventkategori_id,
                'kategori_lokasi' => 'dalam_kampus',
                'jenis_kegiatan' => 'non_pmb',
                'deskripsi_event' => 'Event dummy untuk pengujian presensi event',
                'waktu_mulai_event' => '09:00:00',
                'waktu_selesai_event' => '12:00:00',
                'lokasi_event' => 'Kampus PCR',
                'link_dokumentasi_event' => null,
            ]
        );

        Event::firstOrCreate(
            [
                'nama_event' => 'Event Luar Kampus PMB Cypress',
                'tanggal_event' => now()->toDateString(),
            ],
            [
                'eventkategori_id' => $eventKategori->eventkategori_id,
                'kategori_lokasi' => 'luar_kampus',
                'jenis_kegiatan' => 'pmb',
                'deskripsi_event' => 'Event luar kampus PMB dummy untuk pengujian',
                'waktu_mulai_event' => '09:00:00',
                'waktu_selesai_event' => '12:00:00',
                'lokasi_event' => 'Gedung Serbaguna Pekanbaru',
                'link_dokumentasi_event' => null,
            ]
        );

        $civitas = Civitas::withTrashed()->firstOrNew(['nim' => '2312012345']);
        $civitas->fill([
            'nama_civitas' => 'Andi Pratama',
            'jenis_kelamin' => 'Laki-laki',
            'nomor_telepon' => '081211112222',
            'email' => 'andi@pcr.ac.id',
            'deleted_by' => null,
        ]);
        $civitas->deleted_at = null;
        $civitas->save();

        $pegawai = DmPegawai::withTrashed()->firstOrNew(['nip' => '123456']);
        $pegawai->fill([
            'nama' => 'Bambang Setiawan',
            'email' => 'bambang@pcr.ac.id',
            'deleted_by' => null,
        ]);
        $pegawai->deleted_at = null;
        $pegawai->save();
    }

    public function shouldUseMockMahasiswaApi(Request $request, string $nimNip): bool
    {
        return $this->isMockEnabled($request) && $nimNip === '2312098765';
    }

    private function hasCypressSignal(Request $request): bool
    {
        $userAgent = $request->userAgent() ?? '';
        $sessionMock = $request->hasSession() && $request->session()->get('cy_mock', false) === true;

        return str_contains($userAgent, 'Cypress')
            || $request->boolean('cy_mock')
            || $request->has('cy_scenario')
            || $sessionMock
            || $request->attributes->get('cy_mock', false) === true;
    }
}
