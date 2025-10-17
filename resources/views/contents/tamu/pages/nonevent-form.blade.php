@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center">
                <div class="mb-4 mt-5">
                    <h1 id="page-title" class="fw-bold wow fadeInOut fs-2" data-wow-delay="0.1s" style="font-size: 1.75rem; letter-spacing: 0.025em;">
                        <span id="title-text">Kunjungan Resmi Instansi</span>
                    </h1>
                    <p class="text-muted mb-2" style="font-size: 0.875rem; font-weight: 500; letter-spacing: 0.15em;">
                        POLITEKNIK CALTEX RIAU
                    </p>
                    <div class="mx-auto" style="height: 3px; width: 5rem; background-color: var(--primary-color); border-radius: 9999px;"></div>
                </div>

                <div class="text-start">
                    <button type="button" class="btn btn-link p-0 mb-4 d-flex align-items-center gap-2 text-decoration-none"
                        onclick="window.location.href='{{ route('tamu.nonevent.tujuan') }}'" style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </button>
                </div>

                <form id="tamu-form" class="text-start" action="{{ route('tamu.nonevent.store') }}" method="POST" data-toggle="validator">
                    @csrf
                    <input type="hidden" name="kategori_tujuan" id="hidden-tujuan" value="">
                    <div id="error-messages" class="alert alert-danger" style="display: none;">
                        <ul id="error-list" class="mb-0"></ul>
                    </div>

                    @if(app()->environment('local'))
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                        <small><i class="fas fa-info-circle"></i> Mode Development - Auto Fill untuk Testing</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoFillForm()">
                            <i class="fas fa-magic"></i> Auto Fill
                        </button>
                    </div>
                    @endif

                    <div class="form-section wow fadeInUp" data-wow-delay="0.3s">
                        <div class="mb-3">
                            <h3 class="d-flex align-items-center gap-2 mb-3" style="font-size: 1.125rem; font-weight: 600;">
                                <span>🧑‍💼</span>
                                <span>Data Pengunjung</span>
                            </h3>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold control-label" for="name">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Nama lengkap" required>
                            <div class="help-block with-errors"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold control-label" for="gender">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="gender-male" value="Laki-laki" required>
                                    <label class="form-check-label" for="gender-male">
                                        Laki-laki
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="gender-female" value="Perempuan" required>
                                    <label class="form-check-label" for="gender-female">
                                        Perempuan
                                    </label>
                                </div>
                            </div>
                            <div class="help-block with-errors"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold control-label" for="phone_number">No. Telepon <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone_number" id="phone_number" placeholder="08xxxxxxxxxx" required>
                            <div class="help-block with-errors"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold control-label" for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="email@contoh.com" required>
                            <div class="help-block with-errors"></div>
                        </div>

                        <div id="conditional-fields-pengunjung"></div>
                    </div>

                    <div id="dynamic-section" class="form-section wow fadeInUp mt-5" data-wow-delay="0.4s" style="display: none;">
                        <div class="mb-3">
                            <h3 id="dynamic-title" class="d-flex align-items-center gap-2 mb-3" style="font-size: 1.125rem; font-weight: 600;">
                                <span id="dynamic-emoji">📋</span>
                                <span id="dynamic-text">Detail</span>
                            </h3>
                        </div>

                        <div id="dynamic-fields"></div>
                    </div>

                    <div class="form-section wow fadeInUp mt-5" data-wow-delay="0.5s">
                        <div class="mb-3">
                            <h3 class="d-flex align-items-center gap-2 mb-3" style="font-size: 1.125rem; font-weight: 600;">
                                <span>🗓️</span>
                                <span>Data Kunjungan</span>
                            </h3>
                        </div>

                        <div id="conditional-fields-kunjungan"></div>

                        <div class="form-group mb-3 w-50">
                            <label class="form-label fw-semibold control-label" for="waktu_keluar">Jam Selesai (Estimasi) <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="waktu_keluar" id="waktu_keluar" required>
                            <div class="help-block with-errors"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold control-label" for="transportasi">Jenis Kendaraan/Transportasi <span class="text-danger">*</span></label>
                            <select class="form-select" name="transportasi" id="transportasi" required>
                                <option value="">Pilih Kendaraan/Transportasi</option>
                                <option value="Mobil">Mobil</option>
                                <option value="Motor">Motor</option>
                                <option value="Bus">Bus</option>
                                <option value="Travel">Travel</option>
                                <option value="Online Ride">Online Ride</option>
                                <option value="Jalan Kaki">Jalan Kaki</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>

                    <div class="my-4">
                        <button type="submit" id="submitBtn" class="btn-default w-100">
                            <span id="btn-text"></i>Kirim</span>
                            <span id="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const style = document.createElement('style');
        style.textContent = `
            .has-error .form-control {
                border-color: #dc3545 !important;
                box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 0 4px rgba(220,53,69,.1) !important;
            }
            
            .help-block.with-errors {
                color: #dc3545;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
        `;
        document.head.appendChild(style);

        const urlParams = new URLSearchParams(window.location.search);
        const tujuan = urlParams.get('tujuan') || 'instansi';

        const tujuanMap = {
            instansi: 'Kunjungan Resmi Instansi',
            bisnis: 'Keperluan Bisnis/Kemitraan',
            ortu: 'Orang Tua/Wali Mahasiswa',
            calon_ortu: 'Calon Orang Tua/Wali Mahasiswa',
            lainnya: 'Lainnya'
        };

        document.getElementById('title-text').textContent = tujuanMap[tujuan] || 'Kunjungan';
        document.getElementById('hidden-tujuan').value = tujuan;

        generateConditionalFields(tujuan);
        generateDynamicSection(tujuan);

        const form = document.getElementById('tamu-form');
        const btnText = document.getElementById('btn-text');
        const btnLoading = document.getElementById('btn-loading');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function(e) {
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;
        });
    });

    function generateConditionalFields(tujuan) {
        const pengunjungContainer = document.getElementById('conditional-fields-pengunjung');
        const kunjunganContainer = document.getElementById('conditional-fields-kunjungan');

        pengunjungContainer.innerHTML = '';
        kunjunganContainer.innerHTML = '';

        if (tujuan === 'ortu') {
            pengunjungContainer.innerHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="hubungan_dengan_mahasiswa">Hubungan dengan Mahasiswa <span class="text-danger">*</span></label>
                    <select class="form-select" name="hubungan_dengan_mahasiswa" id="hubungan_dengan_mahasiswa" required>
                        <option value="">Pilih Hubungan</option>
                        <option value="Orang Tua">Orang Tua</option>
                        <option value="Wali">Wali</option>
                        <option value="Saudara">Saudara</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
            `;

            kunjunganContainer.innerHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="pihak_dituju_ortu">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <select class="form-select" name="pihak_dituju_ortu" id="pihak_dituju_ortu" required>
                        <option value="">Pilih Pihak yang Dituju</option>
                        <option value="BAAK">BAAK</option>
                        <option value="Program Studi">Program Studi</option>
                        <option value="Dosen Wali">Dosen Wali</option>
                        <option value="Bagian Keuangan">Bagian Keuangan</option>
                        <option value="Kemahasiswaan">Kemahasiswaan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="keperluan">Keperluan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="keperluan" id="keperluan" rows="3" placeholder="Jelaskan keperluan kunjungan Anda" required></textarea>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        } else if (tujuan === 'lainnya') {
            pengunjungContainer.innerHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="asal">Instansi/Asal <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="asal" id="asal" placeholder="Instansi/Asal" required>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        } else if (tujuan === 'instansi') {
            kunjunganContainer.innerHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="pihak_dituju">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <select class="form-select" name="pihak_dituju" id="pihak_dituju" required>
                        <option value="">Pilih Pihak yang Dituju</option>
                        <option value="Direktur">Direktur</option>
                        <option value="Wakil Direktur">Wakil Direktur</option>
                        <option value="BP3M">BP3M</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="tujuan_spesifik">Tujuan Spesifik <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="tujuan_spesifik" id="tujuan_spesifik" rows="3" placeholder="Jelaskan tujuan spesifik kunjungan Anda" required></textarea>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        } else if (tujuan === 'bisnis') {
            kunjunganContainer.innerHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="pihak_dituju_bisnis">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <select class="form-select" name="pihak_dituju" id="pihak_dituju_bisnis" required>
                        <option value="">Pilih Pihak yang Dituju</option>
                        <option value="Direktur">Direktur</option>
                        <option value="Wakil Direktur">Wakil Direktur</option>
                        <option value="BP3M">BP3M</option>
                        <option value="Sumatera Carrer Center (SCC)">Sumatera Carrer Center (SCC)</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="jenis_kerjasama">Jenis Kerjasama <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="jenis_kerjasama" id="jenis_kerjasama" rows="3" placeholder="Jelaskan jenis kerjasama yang diinginkan" required></textarea>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        }
    }

    function generateDynamicSection(tujuan) {
        const dynamicSection = document.getElementById('dynamic-section');
        const dynamicEmoji = document.getElementById('dynamic-emoji');
        const dynamicText = document.getElementById('dynamic-text');
        const dynamicFields = document.getElementById('dynamic-fields');

        const sectionConfig = {
            instansi: {
                title: 'Data Instansi & Kunjungan',
                emoji: '🏛️'
            },
            bisnis: {
                title: 'Data Perusahaan & Kerjasama',
                emoji: '🤝'
            },
            ortu: {
                title: 'Data Mahasiswa',
                emoji: '👨‍👩‍👧‍👦'
            },
            calon_ortu: {
                title: 'Data Calon Mahasiswa',
                emoji: '🎓'
            },
            lainnya: {
                title: 'Detail Keperluan',
                emoji: '📋'
            }
        };

        const config = sectionConfig[tujuan];
        if (!config) return;

        dynamicEmoji.textContent = config.emoji;
        dynamicText.textContent = config.title;

        let fieldsHTML = '';

        if (tujuan === 'instansi') {
            fieldsHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="instansi">Nama Instansi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="instansi" id="instansi" placeholder="Nama instansi" required>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="jenis_instansi">Jenis Instansi <span class="text-danger">*</span></label>
                    <select class="form-select" name="jenis_instansi" id="jenis_instansi" required>
                        <option value="">Pilih Jenis Instansi</option>
                        <option value="Pemerintah Pusat">Pemerintah Pusat</option>
                        <option value="Pemerintah Daerah">Pemerintah Daerah</option>
                        <option value="BUMN">BUMN</option>
                        <option value="Swasta">Swasta</option>
                        <option value="Perguruan Tinggi">Perguruan Tinggi</option>
                        <option value="Yayasan">Yayasan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="jabatan">Jabatan/Posisi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="jabatan" id="jabatan" placeholder="Jabatan/Posisi" required>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        } else if (tujuan === 'bisnis') {
            fieldsHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="instansi_bisnis">Nama Perusahaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="instansi" id="instansi_bisnis" placeholder="Nama perusahaan" required>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="bidang_usaha">Bidang Usaha <span class="text-danger">*</span></label>
                    <select class="form-select" name="bidang_usaha" id="bidang_usaha" required>
                        <option value="">Pilih Bidang Usaha</option>
                        <option value="Teknologi Informasi">Teknologi Informasi</option>
                        <option value="Manufaktur">Manufaktur</option>
                        <option value="Jasa Konsultasi">Jasa Konsultasi</option>
                        <option value="Perdagangan">Perdagangan</option>
                        <option value="Konstruksi">Konstruksi</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Keuangan/Perbankan">Keuangan/Perbankan</option>
                        <option value="Media & Komunikasi">Media & Komunikasi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="skala_perusahaan">Skala Perusahaan <span class="text-danger">*</span></label>
                    <select class="form-select" name="skala_perusahaan" id="skala_perusahaan" required>
                        <option value="">Pilih Skala Perusahaan</option>
                        <option value="Startup">Startup</option>
                        <option value="Perusahaan Kecil (< 50 karyawan)">Perusahaan Kecil (< 50 karyawan)</option>
                        <option value="Perusahaan Menengah (50-250 karyawan)">Perusahaan Menengah (50-250 karyawan)</option>
                        <option value="Perusahaan Besar (> 250 karyawan)">Perusahaan Besar (> 250 karyawan)</option>
                        <option value="Multinational Corporation">Multinational Corporation</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="jabatan_bisnis">Jabatan/Posisi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="jabatan" id="jabatan_bisnis" placeholder="Jabatan/Posisi" required>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        } else if (tujuan === 'ortu') {
            fieldsHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="nama_mahasiswa">Nama Mahasiswa <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_mahasiswa" id="nama_mahasiswa" required>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="nim_mahasiswa">NIM Mahasiswa <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nim_mahasiswa" id="nim_mahasiswa" required>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        } else if (tujuan === 'calon_ortu') {
            fieldsHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="asal_sekolah">Asal Sekolah <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="asal_sekolah" id="asal_sekolah" required>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="prodi_diminati">Program Studi yang Diminati <span class="text-danger">*</span></label>
                    <select class="form-select" name="prodi_diminati" id="prodi_diminati" required>
                        <option value="">Pilih Program Studi</option>
                        <option value="Teknik Informatika">Teknik Informatika</option>
                        <option value="Sistem Informasi">Sistem Informasi</option>
                        <option value="Teknologi Rekayasa Komputer">Teknologi Rekayasa Komputer</option>
                        <option value="Teknik Mesin">Teknik Mesin</option>
                        <option value="Teknologi Rekayasa Sistem Elektronika">Teknologi Rekayasa Sistem Elektronika</option>
                        <option value="Teknologi Rekayasa Mekatronika">Teknologi Rekayasa Mekatronika</option>
                        <option value="Teknik Elektronika">Teknik Elektronika</option>
                        <option value="Teknik Listrik">Teknik Listrik</option>
                        <option value="Teknologi Rekayasa Jaringan Telekomunikasi">Teknologi Rekayasa Jaringan Telekomunikasi</option>
                        <option value="Akuntansi Perpajakan">Akuntansi Perpajakan</option>
                        <option value="Bisnis Digital">Bisnis Digital</option>
                        <option value="Hubungan Masyarakat dan Komunikasi Digital">Hubungan Masyarakat dan Komunikasi Digital</option>
                    </select>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        } else if (tujuan === 'lainnya') {
            fieldsHTML = `
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="keperluan_detail">Detail Keperluan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="keperluan_detail" id="keperluan_detail" rows="3" required></textarea>
                    <div class="help-block with-errors"></div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label fw-semibold control-label" for="pihak_dituju_lainnya">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="pihak_dituju_lainnya" id="pihak_dituju_lainnya" required>
                    <div class="help-block with-errors"></div>
                </div>
            `;
        }

        if (fieldsHTML) {
            dynamicFields.innerHTML = fieldsHTML;
            dynamicSection.style.display = 'block';
        }
    }

    // ==================================================== //
    function autoFillForm() {
        const urlParams = new URLSearchParams(window.location.search);
        const tujuan = urlParams.get('tujuan') || 'instansi';

        document.querySelector('input[name="name"]').value = 'John Doe Test';
        document.querySelector('input[name="gender"][value="Laki-laki"]').checked = true;
        document.querySelector('input[name="phone_number"]').value = '081234567890';
        document.querySelector('input[name="email"]').value = 'test@example.com';

        const transportasiField = document.querySelector('select[name="transportasi"]');
        if (transportasiField) transportasiField.value = 'Mobil';

        const waktuKeluarField = document.querySelector('input[name="waktu_keluar"]');
        if (waktuKeluarField) waktuKeluarField.value = '16:00';

        setTimeout(() => {
            if (tujuan === 'ortu') {
                const hubunganField = document.querySelector('select[name="hubungan_dengan_mahasiswa"]');
                if (hubunganField) hubunganField.value = 'Orang Tua';
            } else if (tujuan === 'lainnya') {
                const asalField = document.querySelector('input[name="asal"]');
                if (asalField) asalField.value = 'Universitas Test';
            }

            if (tujuan === 'ortu') {
                const pihakDitujuOrtuField = document.querySelector('select[name="pihak_dituju_ortu"]');
                const keperluanField = document.querySelector('textarea[name="keperluan"]');

                if (pihakDitujuOrtuField) pihakDitujuOrtuField.value = 'BAAK';
                if (keperluanField) keperluanField.value = 'Konsultasi mengenai akademik dan administrasi mahasiswa';
            } else if (tujuan === 'instansi') {
                const pihakDitujuField = document.querySelector('select[name="pihak_dituju"]');
                const tujuanSpesifikField = document.querySelector('textarea[name="tujuan_spesifik"]');

                if (pihakDitujuField) pihakDitujuField.value = 'Direktur';
                if (tujuanSpesifikField) tujuanSpesifikField.value = 'Membahas kerjasama penelitian dan pengembangan teknologi antara instansi kami dengan Politeknik Caltex Riau';
            } else if (tujuan === 'bisnis') {
                const pihakDitujuField = document.querySelector('select[name="pihak_dituju"]');
                const jenisKerjasamaField = document.querySelector('textarea[name="jenis_kerjasama"]');

                if (pihakDitujuField) pihakDitujuField.value = 'BP3M';
                if (jenisKerjasamaField) jenisKerjasamaField.value = 'Kerjasama dalam program Kerja Praktik (KP) untuk mahasiswa dan kemungkinan rekrutmen lulusan terbaik';
            }

            if (tujuan === 'instansi') {
                const instansiField = document.querySelector('input[name="instansi"]');
                const jenisInstansiField = document.querySelector('select[name="jenis_instansi"]');
                const jabatanField = document.querySelector('input[name="jabatan"]');

                if (instansiField) instansiField.value = 'Kementerian Teknologi';
                if (jenisInstansiField) jenisInstansiField.value = 'Pemerintah Pusat';
                if (jabatanField) jabatanField.value = 'Staff IT';
            } else if (tujuan === 'bisnis') {
                const instansiField = document.querySelector('input[name="instansi"]');
                const bidangUsahaField = document.querySelector('select[name="bidang_usaha"]');
                const skalaPerusahaanField = document.querySelector('select[name="skala_perusahaan"]');
                const jabatanField = document.querySelector('input[name="jabatan"]');

                if (instansiField) instansiField.value = 'PT. Teknologi Maju';
                if (bidangUsahaField) bidangUsahaField.value = 'Teknologi Informasi';
                if (skalaPerusahaanField) skalaPerusahaanField.value = 'Perusahaan Menengah (50-250 karyawan)';
                if (jabatanField) jabatanField.value = 'Business Development Manager';
            } else if (tujuan === 'ortu') {
                const namaMahasiswaField = document.querySelector('input[name="nama_mahasiswa"]');
                const nimMahasiswaField = document.querySelector('input[name="nim_mahasiswa"]');

                if (namaMahasiswaField) namaMahasiswaField.value = 'Jane Doe';
                if (nimMahasiswaField) nimMahasiswaField.value = '12345678';
            } else if (tujuan === 'calon_ortu') {
                const asalSekolahField = document.querySelector('input[name="asal_sekolah"]');
                const prodiDiminatiField = document.querySelector('select[name="prodi_diminati"]');

                if (asalSekolahField) asalSekolahField.value = 'SMA Negeri 1 Jakarta';
                if (prodiDiminatiField) prodiDiminatiField.value = 'Teknik Informatika';
            } else if (tujuan === 'lainnya') {
                const keperluanDetailField = document.querySelector('textarea[name="keperluan_detail"]');
                const pihakDitujuLainnyaField = document.querySelector('input[name="pihak_dituju_lainnya"]');

                if (keperluanDetailField) keperluanDetailField.value = 'Konsultasi proyek akhir mahasiswa';
                if (pihakDitujuLainnyaField) pihakDitujuLainnyaField.value = 'Dosen Pembimbing';
            }
        }, 200);

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle"></i> Form berhasil diisi otomatis untuk testing!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const formSection = document.querySelector('.form-section');
        formSection.insertBefore(alertDiv, formSection.firstChild);

        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
</script>
@endsection