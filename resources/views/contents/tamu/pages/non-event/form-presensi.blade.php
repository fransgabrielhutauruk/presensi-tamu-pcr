@extends('layouts.tamu.main')

@section('content')
    @php
        $tujuanMap = [
            'instansi' => __('visitor.institutional_official'),
            'bisnis' => __('visitor.business_matters'),
            'ortu' => __('visitor.parent_guardian_visit'),
            'informasi_kampus' => __('visitor.campus_information'),
            'lainnya' => __('visitor.other_purposes'),
        ];
    @endphp

    <div class="container">
        <div class="row min-vh-100">
            <div class="col-md-5 justify-content-center mx-auto">
                <div class="text-center mt-5">
                    <x-tamu.page-header :title="$tujuanMap[$tujuan] ?? 'Kunjungan'" />

                    <div class="text-start mt-4">
                        <a href="{{ route('tamu.non-event.tujuan') }}"
                            class="btn btn-link p-0 mb-4 gap-2 text-decoration-none"
                            style="color: var(--dark-color);">
                            <i class="fas fa-arrow-left"></i>
                            <span>{{ __('visitor.back') }}</span>
                        </a>
                    </div>

                    <form id="tamu-form" class="text-start wow fadeInUp"
                        action="{{ route('tamu.non-event.store-presensi') }}" method="POST" data-toggle="validator"
                        novalidate>
                        @csrf
                        <input type="hidden" name="kategori_tujuan" value="{{ $tujuan }}">

                        @if (app()->environment('local'))
                            <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                                <small><i class="fas fa-info-circle"></i> {{ __('visitor.development_mode') }}</small>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoFillForm()">
                                    <i class="fas fa-magic"></i> {{ __('visitor.auto_fill') }}
                                </button>
                            </div>
                        @endif

                        @switch($tujuan)
                            @case('instansi')
                                @include('components.tamu.partials.instansi')
                            @break

                            @case('bisnis')
                                @include('components.tamu.partials.bisnis')
                            @break

                            @case('ortu')
                                @include('components.tamu.partials.ortu')
                            @break

                            @case('informasi_kampus')
                                @include('components.tamu.partials.calon-ortu')
                            @break

                            @case('lainnya')
                                @include('components.tamu.partials.lainnya')
                            @break
                        @endswitch

                        <div class="mt-5 mb-4">
                            <button type="submit" id="submitBtn" class="btn-default w-100">
                                <span id="btn-text">{{ __('visitor.submit') }}</span>
                                <span id="btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('visitor.processing') }}
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
            const form = document.getElementById('tamu-form');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');

            form.addEventListener('submit', function(e) {
                const isValid = form.checkValidity();
                if (!isValid) {
                    return;
                }
                e.preventDefault();
                if (btnText && btnLoading && submitBtn) {
                    btnText.style.display = 'none';
                    btnLoading.style.display = 'inline';
                    submitBtn.disabled = true;
                }
                form.submit();
            });
        });

        // ==================================================== //
        function autoFillForm() {
            const tujuan = '{{ $tujuan }}';

            document.querySelector('input[name="nama"]').value = 'John Doe Test';

            const genderMale = document.querySelector('input[name="jenis_kelamin"][value="Laki-laki"]');
            if (genderMale) genderMale.checked = true;

            document.querySelector('input[name="nomor_telepon"]').value = '081234567890';
            document.querySelector('input[name="email"]').value = 'test@example.com';

            const keperluanField = document.querySelector('textarea[name="keperluan"]');
            if (keperluanField) keperluanField.value = 'Konsultasi dan pembahasan kerjasama';

            const transportasiField = document.querySelector('select[name="transportasi"]');
            if (transportasiField) transportasiField.value = 'Mobil';

            const waktuKeluarField = document.querySelector('input[name="waktu_keluar"]');
            if (waktuKeluarField) waktuKeluarField.value = '16:00';

            setTimeout(() => {
                if (tujuan === 'ortu') {
                    const hubunganField = document.querySelector('select[name="hubungan_dengan_mahasiswa"]');
                    const pihakDitujuOrtuField = document.querySelector('select[name="pihak_dituju"]');
                    const namaMahasiswaField = document.querySelector('input[name="nama_mahasiswa"]');
                    const nimMahasiswaField = document.querySelector('input[name="nim_mahasiswa"]');
                    const jumlahRombongan = document.querySelector('input[name="jumlah_rombongan"]');

                    if (jumlahRombongan) jumlahRombongan.value = 1;
                    if (hubunganField) hubunganField.value = 'Orang Tua';
                    if (pihakDitujuOrtuField) pihakDitujuOrtuField.value = 'BAAK';
                    if (namaMahasiswaField) namaMahasiswaField.value = 'Jane Doe';
                    if (nimMahasiswaField) nimMahasiswaField.value = '12345678';
                } else if (tujuan === 'instansi') {
                    const instansiField = document.querySelector('input[name="instansi"]');
                    const jenisInstansiField = document.querySelector('select[name="jenis_instansi"]');
                    const jabatanField = document.querySelector('input[name="jabatan"]');
                    const pihakDitujuField = document.querySelector('select[name="pihak_dituju"]');
                    const jumlahRombongan = document.querySelector('input[name="jumlah_rombongan"]');

                    if (jumlahRombongan) jumlahRombongan.value = 1;
                    if (instansiField) instansiField.value = 'Kementerian Teknologi';
                    if (jenisInstansiField) jenisInstansiField.value = 'Pemerintah Pusat';
                    if (jabatanField) jabatanField.value = 'Staff IT';
                    if (pihakDitujuField) pihakDitujuField.value = 'Direktur';
                } else if (tujuan === 'bisnis') {
                    const instansiField = document.querySelector('input[name="instansi"]');
                    const bidangUsahaField = document.querySelector('select[name="bidang_usaha"]');
                    const skalaPerusahaanField = document.querySelector('select[name="skala_perusahaan"]');
                    const jabatanField = document.querySelector('input[name="jabatan"]');
                    const pihakDitujuField = document.querySelector('select[name="pihak_dituju"]');
                    const jumlahRombongan = document.querySelector('input[name="jumlah_rombongan"]');

                    if (jumlahRombongan) jumlahRombongan.value = 1;
                    if (instansiField) instansiField.value = 'PT. Teknologi Maju';
                    if (bidangUsahaField) bidangUsahaField.value = 'Teknologi Informasi';
                    if (skalaPerusahaanField) skalaPerusahaanField.value = 'Perusahaan Menengah (50-250 karyawan)';
                    if (jabatanField) jabatanField.value = 'Business Development Manager';
                    if (pihakDitujuField) pihakDitujuField.value = 'BP3M';
                } else if (tujuan === 'informasi_kampus') {
                    const asalSekolahField = document.querySelector('input[name="asal_sekolah"]');
                    const prodiDiminatiField = document.querySelector('select[name="prodi_diminati"]');
                    const jumlahRombongan = document.querySelector('input[name="jumlah_rombongan"]');

                    if (jumlahRombongan) jumlahRombongan.value = 1;
                    if (asalSekolahField) asalSekolahField.value = 'SMA Negeri 1 Jakarta';
                    if (prodiDiminatiField) prodiDiminatiField.value = 'Teknik Informatika';
                } else if (tujuan === 'lainnya') {
                    const asalField = document.querySelector('input[name="asal"]');
                    const keperluanDetailField = document.querySelector('textarea[name="keperluan_detail"]');
                    const pihakDitujuLainnyaField = document.querySelector('input[name="pihak_dituju"]');
                    const jumlahRombongan = document.querySelector('input[name="jumlah_rombongan"]');

                    if (jumlahRombongan) jumlahRombongan.value = 1;
                    if (asalField) asalField.value = 'Universitas Test';
                    if (keperluanDetailField) keperluanDetailField.value = 'Konsultasi proyek akhir mahasiswa';
                    if (pihakDitujuLainnyaField) pihakDitujuLainnyaField.value = 'Dosen Pembimbing';
                }
            }, 200);

            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
            <i class="fas fa-check-circle"></i> {{ __('visitor.form_auto_filled') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

            const form = document.getElementById('tamu-form');
            form.insertBefore(alertDiv, form.firstChild);

            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
@endsection
