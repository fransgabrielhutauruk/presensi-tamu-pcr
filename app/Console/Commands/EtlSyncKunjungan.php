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
        try {
            $oltp = DB::connection(env('DB_CONNECTION', 'mysql'));
            $dwh = DB::connection(env('DB_DWH_CONNECTION', 'mysql_dwh'));

            $oltp->table('tamu')
                ->whereNull('tamu.deleted_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('kunjungan')
                        ->whereColumn('kunjungan.tamu_id', 'tamu.tamu_id')
                        ->where('kunjungan.status_validasi', true)
                        ->whereNull('kunjungan.deleted_at');
                })
                ->select('tamu.*')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    foreach ($rows as $row) {
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
                }, 'tamu_id');
            $this->info('Sync dim_tamu selesai.');

            $oltp->table('civitas')
                ->whereNull('civitas.deleted_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('kunjungan')
                        ->whereColumn('kunjungan.civitas_id', 'civitas.civitas_id')
                        ->where('kunjungan.status_validasi', true)
                        ->whereNull('kunjungan.deleted_at');
                })
                ->select('civitas.*')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    foreach ($rows as $row) {
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
                }, 'civitas_id');
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

            $oltp->table('event')
                ->whereNull('deleted_at')
                ->whereDate('tanggal_event', '<', Carbon::today())
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('kunjungan')
                        ->whereColumn('kunjungan.event_id', 'event.event_id')
                        ->where('kunjungan.status_validasi', true)
                        ->whereNull('kunjungan.deleted_at');
                })
                ->select('event.*')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    foreach ($rows as $row) {
                        $dwh->table('dim_event')->updateOrInsert(
                            ['event_id' => $row->event_id],
                            [
                                'eventkategori_id' => $row->eventkategori_id,
                                'kategori_lokasi' => $row->kategori_lokasi,
                                'jenis_kegiatan' => $row->jenis_kegiatan,
                                'nama_event' => $row->nama_event,
                                'tanggal' => $row->tanggal_event,
                                'waktu_mulai' => $row->waktu_mulai_event,
                                'waktu_selesai' => $row->waktu_selesai_event,
                                'lokasi' => $row->lokasi_event,
                            ]
                        );
                    }
                }, 'event_id');
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
                'instansi' => 'Kunjungan Resmi Instansi',
                'bisnis' => 'Keperluan Bisnis/Kemitraan',
                'ortu' => 'Kunjungan Orang Tua/Wali Mahasiswa',
                'informasi_kampus' => 'Informasi  Kampus/Penerimaan Mahasiswa Baru (PMB)',
                'lainnya' => 'Keperluan lainnya',
                'event' => 'Kunjungan Untuk Menghadiri Event',
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

            $oltp->table('feedback')
                ->join('kunjungan', 'feedback.kunjungan_id', '=', 'kunjungan.kunjungan_id')
                ->where('kunjungan.status_validasi', true)
                ->whereNull('kunjungan.deleted_at')
                ->whereNull('feedback.deleted_at')
                ->select('feedback.*', 'kunjungan.kunjungan_id')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    foreach ($rows as $row) {
                        $dwh->table('dim_feedback')->updateOrInsert(
                            ['feedback_id' => $row->feedback_id],
                            [
                                'kunjungan_id' => $row->kunjungan_id,
                                'rating' => $row->rating,
                                'komentar' => $row->komentar,
                            ]
                        );
                    }
                }, 'feedback_id');
            $this->info('Sync dim_feedback selesai.');

            $this->info('Mulai sync dim_detail.');
            $oltp->table('kunjungan_detail')
                ->join('kunjungan', 'kunjungan_detail.kunjungan_id', '=', 'kunjungan.kunjungan_id')
                ->whereNull('kunjungan.deleted_at')
                ->whereNull('kunjungan_detail.deleted_at')
                ->select('kunjungan_detail.kunjungandetail_id', 'kunjungan_detail.kunci')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    foreach ($rows as $row) {
                        $existingId = $dwh->table('dim_detail')->where('kunci', $row->kunci)->value('detail_id');
                        if ($existingId === null) {
                            $existingId = (int) $dwh->table('dim_detail')->max('detail_id') + 1;
                        }
                        $dwh->table('dim_detail')->updateOrInsert(
                            ['kunci' => $row->kunci],
                            ['detail_id' => $existingId]
                        );
                    }
                }, 'kunjungandetail_id');
            $this->info('Sync dim_detail selesai.');

            $this->info('Mulai sync dim_waktu.');

            $oltp->table('kunjungan')
                ->where('status_validasi', true)
                ->whereNull('deleted_at')
                ->select('kunjungan_id', 'created_at')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    foreach ($rows as $row) {
                        $timestamp = $row->created_at;
                        if ($timestamp === null) {
                            continue;
                        }
                        $parsed = Carbon::parse($timestamp);
                        $waktuId = (int) $parsed->format('Ymd');
                        $dwh->table('dim_waktu')->updateOrInsert(
                            ['waktu_id' => $waktuId],
                            [
                                'tahun' => (int) $parsed->format('Y'),
                                'bulan' => (int) $parsed->format('m'),
                                'tanggal' => $parsed->toDateString(),
                            ]
                        );
                    }
                }, 'kunjungan_id');
            $this->info('Sync dim_waktu selesai.');

            $this->info('Mulai sync fact_kunjungan.');

            $oltp->table('kunjungan')
                ->where('status_validasi', true)
                ->whereNull('deleted_at')
                ->select('kunjungan.*')
                ->chunkById(1000, function ($rows) use ($dwh, $kategoriMap) {
                    foreach ($rows as $row) {
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
                }, 'kunjungan_id');
            $this->info('Sync fact_kunjungan selesai.');

            $this->info('Mulai sync dim_kunjungan_detail.');

            $pihakDitujuId = $dwh->table('dim_detail')->where('kunci', 'pihak_dituju')->value('detail_id');
            if ($pihakDitujuId === null) {
                $pihakDitujuId = (int) $dwh->table('dim_detail')->max('detail_id') + 1;
                $dwh->table('dim_detail')->insert([
                    'detail_id' => $pihakDitujuId,
                    'kunci' => 'pihak_dituju',
                ]);
            }

            $oltp->table('kunjungan_detail')
                ->join('kunjungan', 'kunjungan_detail.kunjungan_id', '=', 'kunjungan.kunjungan_id')
                ->where('kunjungan.status_validasi', true)
                ->whereNull('kunjungan.deleted_at')
                ->whereNull('kunjungan_detail.deleted_at')
                ->select(
                    'kunjungan_detail.kunjungandetail_id',
                    'kunjungan_detail.kunjungan_id',
                    'kunjungan_detail.kunci',
                    'kunjungan_detail.nilai'
                )
                ->chunkById(1000, function ($rows) use ($dwh) {
                    foreach ($rows as $row) {
                        $detailId = $dwh->table('dim_detail')->where('kunci', $row->kunci)->value('detail_id');
                        if ($detailId === null) {
                            continue;
                        }
                        $dwh->table('dim_kunjungan_detail')->updateOrInsert(
                            ['kunjungan_id' => $row->kunjungan_id, 'detail_id' => $detailId],
                            ['nilai' => $row->nilai]
                        );
                    }
                }, 'kunjungandetail_id');

            $oltp->table('kunjungan')
                ->where('status_validasi', true)
                ->whereNull('deleted_at')
                ->where('kategori_tujuan', 'informasi_kampus')
                ->select('kunjungan_id')
                ->chunkById(1000, function ($rows) use ($dwh, $pihakDitujuId) {
                    foreach ($rows as $row) {
                        $dwh->table('dim_kunjungan_detail')->updateOrInsert(
                            ['kunjungan_id' => $row->kunjungan_id, 'detail_id' => $pihakDitujuId],
                            ['nilai' => 'Penerimaan Mahasiswa Baru (PMB)']
                        );
                    }
                }, 'kunjungan_id');
            $this->info('Sync dim_kunjungan_detail selesai.');

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Sync gagal: ' . $exception->getMessage());
            report($exception);

            return Command::FAILURE;
        }
    }
}
