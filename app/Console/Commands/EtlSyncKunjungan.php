<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EtlSyncKunjungan extends Command
{
    protected $signature = 'app:etl-sync-kunjungan';

    protected $description = 'Melakukan sinkronisasi data dari OLTP ke Data Warehouse';

    public function handle()
    {
        try {
            $oltp = DB::connection(env('DB_CONNECTION', 'mysql'));
            $dwh = DB::connection(env('DB_DWH_CONNECTION', 'sqlsrv_dwh'));

            // =========================================================
            // 1. SYNC DIM TAMU
            // =========================================================
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
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        $dataToUpsert[] = [
                            'tamu_id' => $row->tamu_id,
                            'nama' => $row->nama_tamu,
                            'email' => $row->email_tamu,
                            'nomor_telepon' => $row->nomor_telepon_tamu,
                            'jenis_kelamin' => $row->jenis_kelamin_tamu,
                        ];
                    }
                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                            $dwh->table('dim_tamu')->upsert(
                                $chunk,
                                ['tamu_id'],
                                ['nama', 'email', 'nomor_telepon', 'jenis_kelamin']
                            );
                        }
                    }
                }, 'tamu_id');
            $this->info('Sync dim_tamu selesai.');

            // =========================================================
            // 2. SYNC DIM CIVITAS
            // =========================================================
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
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        $dataToUpsert[] = [
                            'civitas_id' => $row->civitas_id,
                            'nama' => $row->nama_civitas,
                            'nip' => $row->nip,
                            'nim' => $row->nim,
                            'email' => $row->email,
                            'nomor_telepon' => $row->nomor_telepon,
                            'jenis_kelamin' => $row->jenis_kelamin,
                        ];
                    }
                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                            $dwh->table('dim_civitas')->upsert(
                                $chunk,
                                ['civitas_id'],
                                ['nama', 'nip', 'nim', 'email', 'nomor_telepon', 'jenis_kelamin']
                            );
                        }
                    }
                }, 'civitas_id');
            $this->info('Sync dim_civitas selesai.');

            // =========================================================
            // 3. SYNC DIM EVENT KATEGORI
            // =========================================================
            $eventKategoriRows = $oltp->table('event_kategori')
                ->whereNull('deleted_at')
                ->get();
            $dataToUpsert = [];
            foreach ($eventKategoriRows as $row) {
                $dataToUpsert[] = [
                    'eventkategori_id' => $row->eventkategori_id,
                    'kategori_event' => $row->nama_kategori,
                ];
            }
            if (! empty($dataToUpsert)) {
                foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                    $dwh->table('dim_event_kategori')->upsert($chunk, ['eventkategori_id'], ['kategori_event']);
                }
            }
            $this->info('Sync dim_event_kategori selesai.');

            // =========================================================
            // 4. SYNC DIM EVENT
            // =========================================================
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
                    $kategoriLokasiMap = [
                        'dalam_kampus' => 'Dalam Kampus',
                        'luar_kampus' => 'Luar Kampus',
                    ];
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        $dataToUpsert[] = [
                            'event_id' => $row->event_id,
                            'eventkategori_id' => $row->eventkategori_id,
                            'kategori_lokasi' => $kategoriLokasiMap[$row->kategori_lokasi] ?? $row->kategori_lokasi,
                            'jenis_kegiatan' => $row->jenis_kegiatan,
                            'nama_event' => $row->nama_event,
                            'tanggal' => $row->tanggal_event,
                            'waktu_mulai' => $row->waktu_mulai_event,
                            'waktu_selesai' => $row->waktu_selesai_event,
                            'lokasi' => $row->lokasi_event,
                        ];
                    }
                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                            $dwh->table('dim_event')->upsert(
                                $chunk,
                                ['event_id'],
                                ['eventkategori_id', 'kategori_lokasi', 'jenis_kegiatan', 'nama_event', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'lokasi']
                            );
                        }
                    }
                }, 'event_id');
            $this->info('Sync dim_event selesai.');

            // =========================================================
            // 5. SYNC DIM TRANSPORTASI
            // =========================================================
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

            // =========================================================
            // 6. SYNC DIM KUNJUNGAN KATEGORI
            // =========================================================
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

            // =========================================================
            // 7. SYNC DIM FEEDBACK
            // =========================================================
            $oltp->table('feedback')
                ->join('kunjungan', 'feedback.kunjungan_id', '=', 'kunjungan.kunjungan_id')
                ->where('kunjungan.status_validasi', true)
                ->whereNull('kunjungan.deleted_at')
                ->whereNull('feedback.deleted_at')
                ->select('feedback.*', 'kunjungan.kunjungan_id')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        $dataToUpsert[] = [
                            'feedback_id' => $row->feedback_id,
                            'kunjungan_id' => $row->kunjungan_id,
                            'rating' => $row->rating,
                            'komentar' => $row->komentar,
                        ];
                    }
                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                            $dwh->table('dim_feedback')->upsert(
                                $chunk,
                                ['feedback_id'],
                                ['kunjungan_id', 'rating', 'komentar']
                            );
                        }
                    }
                }, 'feedback_id');
            $this->info('Sync dim_feedback selesai.');

            // =========================================================
            // 8. SYNC DIM DETAIL
            // =========================================================
            $this->info('Mulai sync dim_detail.');
            $kunciValues = $oltp->table('kunjungan_detail')
                ->join('kunjungan', 'kunjungan_detail.kunjungan_id', '=', 'kunjungan.kunjungan_id')
                ->whereNull('kunjungan.deleted_at')
                ->whereNull('kunjungan_detail.deleted_at')
                ->distinct()
                ->pluck('kunjungan_detail.kunci');

            foreach ($kunciValues as $kunci) {
                $existingId = $dwh->table('dim_detail')->where('kunci', $kunci)->value('detail_id');
                if ($existingId === null) {
                    $nextId = (int) $dwh->table('dim_detail')->max('detail_id') + 1;
                    $dwh->table('dim_detail')->insert([
                        'detail_id' => $nextId,
                        'kunci' => $kunci,
                    ]);
                }
            }
            $this->info('Sync dim_detail selesai.');

            // =========================================================
            // 9. SYNC DIM WAKTU
            // =========================================================
            $this->info('Mulai sync dim_waktu.');
            $oltp->table('kunjungan')
                ->where('status_validasi', true)
                ->whereNull('deleted_at')
                ->select('kunjungan_id', 'created_at')
                ->chunkById(1000, function ($rows) use ($dwh) {
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        if ($row->created_at === null) {
                            continue;
                        }

                        $parsed = Carbon::parse($row->created_at);
                        $waktuId = (int) $parsed->format('Ymd');

                        $dataToUpsert[$waktuId] = [
                            'waktu_id' => $waktuId,
                            'tahun' => (int) $parsed->format('Y'),
                            'bulan' => (int) $parsed->format('m'),
                            'tanggal' => $parsed->toDateString(),
                        ];
                    }
                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk(array_values($dataToUpsert), 100) as $chunk) {
                            $dwh->table('dim_waktu')->upsert(
                                $chunk,
                                ['waktu_id'],
                                ['tahun', 'bulan', 'tanggal']
                            );
                        }
                    }
                }, 'kunjungan_id');
            $this->info('Sync dim_waktu selesai.');

            // =========================================================
            // 10. SYNC FACT KUNJUNGAN
            // =========================================================
            $this->info('Mulai sync fact_kunjungan.');

            $mapTransportasiDwh = $dwh->table('dim_transportasi')->pluck('transportasi_id', 'transportasi')->toArray();
            $mapKategoriDwh = $dwh->table('dim_kunjungan_kategori')->pluck('kunjungankategori_id', 'kategori_kunjungan')->toArray();

            $oltp->table('kunjungan')
                ->where('status_validasi', true)
                ->whereNull('deleted_at')
                ->select('kunjungan.*')
                ->chunkById(1000, function ($rows) use ($dwh, $kategoriMap, $mapTransportasiDwh, $mapKategoriDwh) {
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        if ($row->created_at === null) {
                            continue;
                        }

                        $kategoriKunjungan = $row->kategori_tujuan;

                        $transportasiId = $row->transportasi !== null ? ($mapTransportasiDwh[$row->transportasi] ?? null) : null;
                        $kunjunganKategoriLabel = $kategoriKunjungan !== null ? ($kategoriMap[$kategoriKunjungan] ?? $kategoriKunjungan) : null;
                        $kunjunganKategoriId = $kunjunganKategoriLabel !== null ? ($mapKategoriDwh[$kunjunganKategoriLabel] ?? null) : null;

                        $parsedWaktu = Carbon::parse($row->created_at);

                        $dataToUpsert[] = [
                            'kunjungan_id' => $row->kunjungan_id,
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
                        ];
                    }

                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                            $dwh->table('fact_kunjungan')->upsert(
                                $chunk,
                                ['kunjungan_id'],
                                ['tamu_id', 'civitas_id', 'identitas', 'is_vip', 'jenis_kunjungan', 'event_id', 'kunjungankategori_id', 'transportasi_id', 'waktu_id', 'waktu_masuk', 'waktu_keluar', 'jumlah_kunjungan']
                            );
                        }
                    }
                }, 'kunjungan_id');
            $this->info('Sync fact_kunjungan selesai.');

            // =========================================================
            // 11. SYNC DIM KUNJUNGAN DETAIL
            // =========================================================
            $this->info('Mulai sync dim_kunjungan_detail.');

            $pihakDitujuId = $dwh->table('dim_detail')->where('kunci', 'pihak_dituju')->value('detail_id');
            if ($pihakDitujuId === null) {
                $pihakDitujuId = (int) $dwh->table('dim_detail')->max('detail_id') + 1;
                $dwh->table('dim_detail')->insert([
                    'detail_id' => $pihakDitujuId,
                    'kunci' => 'pihak_dituju',
                ]);
            }

            $mapDetailDwh = $dwh->table('dim_detail')->pluck('detail_id', 'kunci')->toArray();

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
                ->chunkById(1000, function ($rows) use ($dwh, $mapDetailDwh) {
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        $detailId = $mapDetailDwh[$row->kunci] ?? null;
                        if ($detailId === null) {
                            continue;
                        }

                        $dataToUpsert[] = [
                            'kunjungan_id' => $row->kunjungan_id,
                            'detail_id' => $detailId,
                            'nilai' => $row->nilai,
                        ];
                    }
                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                            $dwh->table('dim_kunjungan_detail')->upsert(
                                $chunk,
                                ['kunjungan_id', 'detail_id'],
                                ['nilai']
                            );
                        }
                    }
                }, 'kunjungandetail_id');

            // Bagian khusus untuk 'informasi_kampus'
            $oltp->table('kunjungan')
                ->where('status_validasi', true)
                ->whereNull('deleted_at')
                ->where('kategori_tujuan', 'informasi_kampus')
                ->select('kunjungan_id')
                ->chunkById(1000, function ($rows) use ($dwh, $pihakDitujuId) {
                    $dataToUpsert = [];
                    foreach ($rows as $row) {
                        $dataToUpsert[] = [
                            'kunjungan_id' => $row->kunjungan_id,
                            'detail_id' => $pihakDitujuId,
                            'nilai' => 'Penerimaan Mahasiswa Baru (PMB)',
                        ];
                    }
                    if (! empty($dataToUpsert)) {
                        foreach (array_chunk($dataToUpsert, 100) as $chunk) {
                            $dwh->table('dim_kunjungan_detail')->upsert(
                                $chunk,
                                ['kunjungan_id', 'detail_id'],
                                ['nilai']
                            );
                        }
                    }
                }, 'kunjungan_id');
            $this->info('Sync dim_kunjungan_detail selesai.');

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Sync gagal: '.$exception->getMessage());
            report($exception);

            return Command::FAILURE;
        }
    }
}
