@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row min-vh-100">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center">
                <img
                    src="{{ asset('theme/images/akreditasi-unggul.webp') }}"
                    alt="Logo Akreditasi Unggul"
                    class="mx-auto d-block mb-3 w-25 img-fluid mt-5" />

                <h1 class="wow fadeInOut" data-wow-delay="0.5s">
                    PILIH JENIS KUNJUNGAN
                </h1>

                <p class="text-muted mb-4 fs-6 lh-base">
                    Silakan pilih jenis presensi kunjungan Anda </br> ke Politeknik Caltex Riau
                </p>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden wow fadeInUp">
                            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                                <div class="mb-2">
                                    <h5><i class="fas fa-user fa-2x mb-1"></i></h5>
                                </div>
                                <h4 class="card-title mb-1 fw-bold">Tamu Non Event</h4>
                                <p class="card-text text-muted mb-2 flex-grow-1">
                                    Kunjungan umum / belum ada janji kunjungan
                                </p>
                                <a href="{{ route('tamu.nonevent.tujuan') }}" class="btn-default w-100 mt- fs-6">Pilih Presensi Non Event</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden wow fadeInUp">
                            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                                <div class="mb-2">
                                    <h5><i class="fas fa-calendar-alt fa-2x mb-1"></i></h5>
                                </div>
                                <h4 class="card-title mb-1 fw-bold">Tamu Event</h4>
                                <p class="card-text text-muted mb-2 flex-grow-1">
                                    Kunjungan untuk menghadiri kegiatan / sudah ada janji kunjungan
                                </p>
                                <a href="{{ route('tamu.event.list') }}" class="btn-default w-100 mt-1">Pilih Presensi Event</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection