<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EtlSyncKunjungan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:etl-sync-kunjungan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oltp = DB::connection(env('DB_CONNECTION', 'mysql'));
        $dwh = DB::connection(env('DB_DWH_CONNECTION', 'mysql_dwh'));

        $tamuRows = $oltp->table('tamu')
            ->join('kunjungan', 'tamu.tamu_id', '=', 'kunjungan.tamu_id')
            ->where('kunjungan.status_validasi', true)
            ->whereNull('kunjungan.deleted_at')
            ->whereNull('tamu.deleted_at')
            ->select('tamu.*')
            ->distinct()
            ->get();
        
        foreach ($tamuRows as $row) {
            $dwh->table('dim_tamu')->updateOrInsert(
                ['tamu_id' => $row->tamu_id],
                [
                    'nama' => $row->nama_tamu,
                    'email' => $row->email_tamu,
                    'nomor_telepon' => $row->nomor_telepon_tamu,
                    'jenis_kelamin' => $row->jenis_kelamin_tamu,
                ]
            );
        }
        $this->info('Sync dim_tamu selesai.');

        $civitasRows = $oltp->table('civitas')
            ->join('kunjungan', 'civitas.civitas_id', '=', 'kunjungan.civitas_id')
            ->where('kunjungan.status_validasi', true)
            ->whereNull('kunjungan.deleted_at')
            ->whereNull('civitas.deleted_at')
            ->select('civitas.*')
            ->distinct()
            ->get();
        foreach ($civitasRows as $row) {
            $dwh->table('dim_civitas')->updateOrInsert(
                ['civitas_id' => $row->civitas_id],
                [
                    'nama' => $row->nama_civitas,
                    'nip' => $row->nip,
                    'nim' => $row->nim,
                    'email' => $row->email,
                    'nomor_telepon' => $row->nomor_telepon,
                    'jenis_kelamin' => $row->jenis_kelamin,
                ]
            );
        }
        $this->info('Sync dim_civitas selesai.');

        $eventKategoriRows = $oltp->table('event_kategori')
            ->whereNull('deleted_at')
            ->get();
        foreach ($eventKategoriRows as $row) {
            $dwh->table('dim_event_kategori')->updateOrInsert(
                ['eventkategori_id' => $row->eventkategori_id],
                ['kategori_event' => $row->nama_kategori]
            );
        }
        $this->info('Sync dim_event_kategori selesai.');

        $eventRows = $oltp->table('event')
            ->whereNull('deleted_at')
            ->get();
        foreach ($eventRows as $row) {
            $dwh->table('dim_event')->updateOrInsert(
                ['event_id' => $row->event_id],
                [
                    'eventkategori_id' => $row->eventkategori_id,
                    'nama_event' => $row->nama_event,
                    'tanggal' => $row->tanggal_event,
                    'waktu_mulai' => $row->waktu_mulai_event,
                    'waktu_selesai' => $row->waktu_selesai_event,
                    'lokasi' => $row->lokasi_event,
                ]
            );
        }
        $this->info('Sync dim_event selesai.');

        $transportasiValues = $oltp->table('kunjungan')
            ->where('status_validasi', true)
            ->whereNull('deleted_at')
            ->whereNotNull('transportasi')
            ->distinct()
            ->pluck('transportasi');
        foreach ($transportasiValues as $transportasi) {
            $existingId = $dwh->table('dim_transportasi')->where('transportasi', $transportasi)->value('transportasi_id');
            if ($existingId === null) {
                $nextId = (int) $dwh->table('dim_transportasi')->max('transportasi_id') + 1;
                $dwh->table('dim_transportasi')->insert([
                    'transportasi_id' => $nextId,
                    'transportasi' => $transportasi,
                ]);
            }
        }
        $this->info('Sync dim_transportasi selesai.');

        $kategoriValues = $oltp->table('kunjungan')
            ->where('status_validasi', true)
            ->whereNull('deleted_at')
            ->whereNotNull('kategori_tujuan')
            ->distinct()
            ->pluck('kategori_tujuan');
        $kategoriMap = [
            'instansi' => 'Kunjungan resmi dari instansi/lembaga',
            'bisnis' => 'Kunjungan untuk keperluan bisnis',
            'ortu' => 'Kunjungan orang tua mahasiswa',
            'informasi_kampus' => 'Mencari informasi tentang kampus',
            'lainnya' => 'Keperluan lainnya',
            'event' => 'Kunjungan untuk menghadiri event',
        ];
        foreach ($kategoriValues as $kategori) {
            $kategoriLabel = $kategoriMap[$kategori] ?? $kategori;
            $existingId = $dwh->table('dim_kunjungan_kategori')->where('kategori_kunjungan', $kategoriLabel)->value('kunjungankategori_id');
            if ($existingId === null) {
                $nextId = (int) $dwh->table('dim_kunjungan_kategori')->max('kunjungankategori_id') + 1;
                $dwh->table('dim_kunjungan_kategori')->insert([
                    'kunjungankategori_id' => $nextId,
                    'kategori_kunjungan' => $kategoriLabel,
                ]);
            }
        }
        $this->info('Sync dim_kunjungan_kategori selesai.');

        $feedbackRows = $oltp->table('feedback')
            ->join('kunjungan', 'feedback.kunjungan_id', '=', 'kunjungan.kunjungan_id')
            ->where('kunjungan.status_validasi', true)
            ->whereNull('kunjungan.deleted_at')
            ->whereNull('feedback.deleted_at')
            ->select('feedback.*', 'kunjungan.kunjungan_id')
            ->get();
        foreach ($feedbackRows as $row) {
            $dwh->table('dim_feedback')->updateOrInsert(
                ['feedback_id' => $row->feedback_id],
                [
                    'kunjungan_id' => $row->kunjungan_id,
                    'rating' => $row->rating,
                    'komentar' => $row->komentar,
                ]
            );
        }
        $this->info('Sync dim_feedback selesai.');

        $detailRows = $oltp->table('kunjungan_detail')
            ->join('kunjungan', 'kunjungan_detail.kunjungan_id', '=', 'kunjungan.kunjungan_id')
            ->whereNull('kunjungan.deleted_at')
            ->whereNull('kunjungan_detail.deleted_at')
            ->distinct()
            ->get();
        foreach ($detailRows as $row) {
            $existingId = $dwh->table('dim_detail')->where('kunci', $row->kunci)->value('detail_id');
            if ($existingId === null) {
                $existingId = (int) $dwh->table('dim_detail')->max('detail_id') + 1;
            }
            $dwh->table('dim_detail')->updateOrInsert(
                ['kunci' => $row->kunci],
                [
                    'detail_id' => $existingId,
                ]
            );
        }
        $this->info('Sync dim_detail selesai.');

        $waktuRows = $oltp->table('kunjungan')
            ->where('status_validasi', true)
            ->whereNull('deleted_at')
            ->select('created_at')
            ->get();
        $waktuMap = [];
        foreach ($waktuRows as $row) {
            $timestamp = $row->created_at;
            if ($timestamp === null) {
                continue;
            }
            $parsed = Carbon::parse($timestamp);
            $waktuId = (int) $parsed->format('Ymd');
            $waktuMap[$waktuId] = [
                'tahun' => (int) $parsed->format('Y'),
                'bulan' => (int) $parsed->format('m'),
                'tanggal' => $parsed->toDateString(),
            ];
        }
        foreach ($waktuMap as $waktuId => $payload) {
            $dwh->table('dim_waktu')->updateOrInsert(
                ['waktu_id' => $waktuId],
                $payload
            );
        }
        $this->info('Sync dim_waktu selesai.');

        $kunjunganRows = $oltp->table('kunjungan')
            ->where('status_validasi', true)
            ->whereNull('deleted_at')
            ->get();
        foreach ($kunjunganRows as $row) {
            $kategoriKunjungan = $row->kategori_tujuan;
            $transportasiId = $row->transportasi !== null
                ? $dwh->table('dim_transportasi')->where('transportasi', $row->transportasi)->value('transportasi_id')
                : null;
            $kunjunganKategoriLabel = $kategoriKunjungan !== null
                ? ($kategoriMap[$kategoriKunjungan] ?? $kategoriKunjungan)
                : null;
            $kunjunganKategoriId = $kunjunganKategoriLabel !== null
                ? $dwh->table('dim_kunjungan_kategori')->where('kategori_kunjungan', $kunjunganKategoriLabel)->value('kunjungankategori_id')
                : null;

            $kunjunganWaktu = $row->created_at;
            if ($kunjunganWaktu === null) {
                continue;
            }
            $parsedWaktu = Carbon::parse($kunjunganWaktu);

            $dwh->table('fact_kunjungan')->updateOrInsert(
                ['kunjungan_id' => $row->kunjungan_id],
                [
                    'tamu_id' => $row->tamu_id,
                    'civitas_id' => $row->civitas_id,
                    'identitas' => $row->civitas_id !== null ? 'Civitas PCR' : 'Non-Civitas',
                    'is_vip' => $row->is_vip == 1 ? 'VIP' : 'Non-VIP',
                    'jenis_kunjungan' => $row->event_id !== null ? 'Event' : 'Non-Event',
                    'event_id' => $row->event_id,
                    'kunjungankategori_id' => $kunjunganKategoriId,
                    'transportasi_id' => $transportasiId,
                    'waktu_id' => (int) $parsedWaktu->format('Ymd'),
                    'waktu_masuk' => $parsedWaktu->format('H:i:s'),
                    'waktu_keluar' => $row->waktu_keluar,
                    'jumlah_kunjungan' => 1,
                ]
            );
        }
        $this->info('Sync fact_kunjungan selesai.');

        $kunjunganDetailRows = $oltp->table('kunjungan_detail')
            ->join('kunjungan', 'kunjungan_detail.kunjungan_id', '=', 'kunjungan.kunjungan_id')
            ->where('kunjungan.status_validasi', true)
            ->whereNull('kunjungan.deleted_at')
            ->whereNull('kunjungan_detail.deleted_at')
            ->select('kunjungan_detail.*')
            ->get();
        foreach ($kunjunganDetailRows as $row) {
            $detailId = $dwh->table('dim_detail')->where('kunci', $row->kunci)->value('detail_id');
            if ($detailId === null) {
                continue;
            }
            $dwh->table('dim_kunjungan_detail')->updateOrInsert(
                ['kunjungan_id' => $row->kunjungan_id, 'detail_id' => $detailId],
                ['nilai' => $row->nilai]
            );
        }
        $this->info('Sync dim_kunjungan_detail selesai.');
    }
}
