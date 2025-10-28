@extends('layouts.tamu.main')

@section('content')
    <div class="container">
        <div class="row min-vh-100 align-items-center">
            <div class="col-md-6 justify-content-center mx-auto">
                <div class="text-center">
                    <x-tamu.page-header title="{{ $event->nama_event }}" question="Apakah Tamu Luar atau Civitas PCR?" />

                    <div class="row g-4 mb-4 mt-1 px-3">
                        <a href="{{ route('tamu.event.form-presensi-luar', $eventId) }}"
                            class="card border-0 shadow-sm py-1 participant-option wow fadeInUp"
                            style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bold mb-0">Tamu Luar</h5>
                                <p class="text-muted mb-0">Pengunjung dari luar Politeknik Caltex Riau</p>
                            </div>
                        </a>

                        <a href="{{ route('tamu.event.form-presensi-civitas', $eventId) }}"
                            class="card border-0 shadow-sm participant-option wow fadeInUp"
                            style="cursor: pointer; transition: all 0.3s ease;">
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bold mb-0">Civitas PCR</h5>
                                <p class="text-muted mb-0">Dosen, Staff, atau Mahasiswa PCR</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .participant-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .participant-option:hover .fas {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }
    </style>
@endsection
