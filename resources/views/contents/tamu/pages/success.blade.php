@extends('layouts.tamu.main')

@section('header')
<div></div>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                    <i class="fas fa-check text-white" style="font-size: 3rem;"></i>
                </div>
                <h1 class="mt-4 mb-3 text-success">Registrasi Berhasil!</h1>
                <p class="lead">Terima kasih telah mendaftar sebagai tamu. Data Anda telah berhasil disimpan.</p>
            </div>

            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h4 class="mb-3">Informasi Pendaftaran</h4>
                    
                    @if(session('presensi_data'))
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nama Lengkap:</strong><br>{{ session('presensi_data.nama_lengkap') }}</p>
                                <p><strong>Jenis Kelamin:</strong><br>{{ ucfirst(session('presensi_data.gender')) }}</p>
                                <p><strong>Email:</strong><br>{{ session('presensi_data.email') }}</p>
                                <p><strong>No. Telepon:</strong><br>{{ session('presensi_data.no_telepon') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Tujuan Kunjungan:</strong><br>
                                    @switch(session('presensi_data.tujuan_kunjungan'))
                                        @case('instansi')
                                            Instansi/Pemerintahan
                                            @break
                                        @case('bisnis')
                                            Kerjasama Bisnis
                                            @break
                                        @case('ortu')
                                            Orang Tua/Wali Mahasiswa
                                            @break
                                        @case('calon_ortu')
                                            Calon Mahasiswa/Orang Tua
                                            @break
                                        @default
                                            Lainnya
                                    @endswitch
                                </p>
                                <p><strong>Tanggal Kunjungan:</strong><br>{{ \Carbon\Carbon::parse(session('presensi_data.tanggal_kunjungan'))->format('d F Y') }}</p>
                                <p><strong>Waktu Kunjungan:</strong><br>{{ \Carbon\Carbon::parse(session('presensi_data.waktu_kunjungan'))->format('H:i') }} WIB</p>
                            </div>
                        </div>
                    @endif

                    <div class="alert alert-info mt-4" role="alert">
                        <h5 class="alert-heading">Informasi Penting:</h5>
                        <p class="mb-2">• Simpan informasi ini sebagai bukti pendaftaran</p>
                        <p class="mb-2">• Harap datang 15 menit sebelum waktu kunjungan yang dijadwalkan</p>
                        <p class="mb-2">• Bawa identitas diri (KTP/SIM/Passport) saat berkunjung</p>
                        <p class="mb-0">• Status pendaftaran akan dikonfirmasi melalui email atau telepon</p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="#" class="btn btn-primary me-3">
                            <i class="fas fa-plus"></i> Daftar Kunjungan Lagi
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection