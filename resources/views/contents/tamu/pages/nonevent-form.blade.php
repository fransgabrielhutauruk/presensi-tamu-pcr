@extends('layouts.tamu.main')

@section('header')
<div></div>
@endsection

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

                <form id="tamu-form" class="text-start" action="{{ route('tamu.nonevent.store') }}" method="POST">
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

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Nama lengkap" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
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
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">No. Telepon <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone_number" placeholder="08xxxxxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" placeholder="email@contoh.com" required>
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

                        <div class="mb-3 w-50">
                            <label class="form-label fw-semibold">Jam Selesai (Estimasi) <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="waktu_keluar" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jenis Kendaraan/Transportasi <span class="text-danger">*</span></label>
                            <select class="form-select" name="transportasi" required>
                                <option value="">Pilih Kendaraan/Transportasi</option>
                                <option value="Mobil">Mobil</option>
                                <option value="Motor">Motor</option>
                                <option value="Bus">Bus</option>
                                <option value="Travel">Travel</option>
                                <option value="Online Ride">Online Ride</option>
                                <option value="Jalan Kaki">Jalan Kaki</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="my-4">
                        <button type="submit" class="btn-default w-100">
                            <span id="btn-text">Kirim</span>
                            <span id="btn-loading" style="display: none;">Mengirim...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';

            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            throw {
                                isValidation: true,
                                errors: data.errors || data.message
                            };
                        });
                    } else if (!response.ok) {
                        return response.json().then(data => {
                            throw {
                                isValidation: false,
                                message: data.message || 'Terjadi kesalahan'
                            };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status) {
                        window.location.href = data.data.redirect_url;
                    } else {
                        showError(data.message);
                        btnText.style.display = 'inline';
                        btnLoading.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    if (error.isValidation) {
                        if (typeof error.errors === 'object') {
                            showValidationErrors(error.errors);
                        } else {
                            showError(error.errors);
                        }
                    } else {
                        showError(error.message || 'Terjadi kesalahan saat mengirim data. Silakan coba lagi.');
                    }

                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                });
        });

        function showError(message) {
            const errorDiv = document.getElementById('error-messages');
            const errorList = document.getElementById('error-list');

            errorList.innerHTML = `<li>${message}</li>`;
            errorDiv.style.display = 'block';

            errorDiv.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function showValidationErrors(errors) {
            const errorDiv = document.getElementById('error-messages');
            const errorList = document.getElementById('error-list');

            let errorHtml = '';
            Object.values(errors).forEach(errorArray => {
                if (Array.isArray(errorArray)) {
                    errorArray.forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                } else {
                    errorHtml += `<li>${errorArray}</li>`;
                }
            });

            errorList.innerHTML = errorHtml;
            errorDiv.style.display = 'block';

            errorDiv.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function hideErrors() {
            const errorDiv = document.getElementById('error-messages');
            errorDiv.style.display = 'none';
        }

        form.addEventListener('input', hideErrors);
    });

    function generateConditionalFields(tujuan) {
        const pengunjungContainer = document.getElementById('conditional-fields-pengunjung');
        const kunjunganContainer = document.getElementById('conditional-fields-kunjungan');

        pengunjungContainer.innerHTML = '';
        kunjunganContainer.innerHTML = '';

        if (tujuan === 'ortu') {
            pengunjungContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Hubungan dengan Mahasiswa <span class="text-danger">*</span></label>
                    <select class="form-select" name="hubungan_dengan_mahasiswa" required>
                        <option value="">Pilih Hubungan</option>
                        <option value="Orang Tua">Orang Tua</option>
                        <option value="Wali">Wali</option>
                        <option value="Saudara">Saudara</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            `;

            kunjunganContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <select class="form-select" name="pihak_dituju_ortu" required>
                        <option value="">Pilih Pihak yang Dituju</option>
                        <option value="BAAK">BAAK</option>
                        <option value="Program Studi">Program Studi</option>
                        <option value="Dosen Wali">Dosen Wali</option>
                        <option value="Bagian Keuangan">Bagian Keuangan</option>
                        <option value="Kemahasiswaan">Kemahasiswaan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Keperluan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="keperluan" rows="3" placeholder="Jelaskan keperluan kunjungan Anda" required></textarea>
                </div>
            `;
        } else if (tujuan === 'lainnya') {
            pengunjungContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Instansi/Asal <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="asal" placeholder="Instansi/Asal" required>
                </div>
            `;
        } else if (tujuan === 'instansi') {
            kunjunganContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <select class="form-select" name="pihak_dituju" required>
                        <option value="">Pilih Pihak yang Dituju</option>
                        <option value="Direktur">Direktur</option>
                        <option value="Wakil Direktur">Wakil Direktur</option>
                        <option value="BP3M">BP3M</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tujuan Spesifik <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="tujuan_spesifik" rows="3" placeholder="Jelaskan tujuan spesifik kunjungan Anda" required></textarea>
                </div>
            `;
        } else if (tujuan === 'bisnis') {
            kunjunganContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <select class="form-select" name="pihak_dituju" required>
                        <option value="">Pilih Pihak yang Dituju</option>
                        <option value="Direktur">Direktur</option>
                        <option value="Wakil Direktur">Wakil Direktur</option>
                        <option value="BP3M">BP3M</option>
                        <option value="Sumatera Carrer Center (SCC)">Sumatera Carrer Center (SCC)</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jenis Kerjasama <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="jenis_kerjasama" rows="3" placeholder="Jelaskan jenis kerjasama yang diinginkan" required></textarea>
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
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Instansi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="instansi" placeholder="Nama instansi" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jenis Instansi <span class="text-danger">*</span></label>
                    <select class="form-select" name="jenis_instansi" required>
                        <option value="">Pilih Jenis Instansi</option>
                        <option value="Pemerintah Pusat">Pemerintah Pusat</option>
                        <option value="Pemerintah Daerah">Pemerintah Daerah</option>
                        <option value="BUMN">BUMN</option>
                        <option value="Swasta">Swasta</option>
                        <option value="Perguruan Tinggi">Perguruan Tinggi</option>
                        <option value="Yayasan">Yayasan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jabatan/Posisi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="jabatan" placeholder="Jabatan/Posisi" required>
                </div>
            `;
        } else if (tujuan === 'bisnis') {
            fieldsHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Perusahaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="instansi" placeholder="Nama perusahaan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Bidang Usaha <span class="text-danger">*</span></label>
                    <select class="form-select" name="bidang_usaha" required>
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
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Skala Perusahaan <span class="text-danger">*</span></label>
                    <select class="form-select" name="skala_perusahaan" required>
                        <option value="">Pilih Skala Perusahaan</option>
                        <option value="Startup">Startup</option>
                        <option value="Perusahaan Kecil (< 50 karyawan)">Perusahaan Kecil (< 50 karyawan)</option>
                        <option value="Perusahaan Menengah (50-250 karyawan)">Perusahaan Menengah (50-250 karyawan)</option>
                        <option value="Perusahaan Besar (> 250 karyawan)">Perusahaan Besar (> 250 karyawan)</option>
                        <option value="Multinational Corporation">Multinational Corporation</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Jabatan/Posisi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="jabatan" placeholder="Jabatan/Posisi" required>
                </div>
            `;
        } else if (tujuan === 'ortu') {
            fieldsHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Mahasiswa <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_mahasiswa" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">NIM Mahasiswa <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nim_mahasiswa" required>
                </div>
            `;
        } else if (tujuan === 'calon_ortu') {
            fieldsHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Asal Sekolah <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="asal_sekolah" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Program Studi yang Diminati <span class="text-danger">*</span></label>
                    <select class="form-select" name="prodi_diminati" required>
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
                </div>
            `;
        } else if (tujuan === 'lainnya') {
            fieldsHTML = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Detail Keperluan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="keperluan_detail" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pihak yang Dituju <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="pihak_dituju_lainnya" required>
                </div>
            `;
        }

        if (fieldsHTML) {
            dynamicFields.innerHTML = fieldsHTML;
            dynamicSection.style.display = 'block';
        }
    }

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