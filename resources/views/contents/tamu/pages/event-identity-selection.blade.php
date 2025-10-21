@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row min-vh-100">
        <div class="col-md-6 justify-content-center mx-auto">
            <div class="text-center">
                <div class="mb-5 mt-5">
                    <h1 class="fw-bold wow fadeInOut fs-2" style="font-size: 1.75rem; letter-spacing: 0.025em;">
                        Pilih Jenis Peserta
                    </h1>
                    <p class="text-muted mb-2" style="font-size: 0.875rem; font-weight: 500; letter-spacing: 0.15em;">
                        {{ $event->nama_event }}
                    </p>
                    <div class="mx-auto" style="height: 3px; width: 5rem; background-color: var(--primary-color); border-radius: 9999px;"></div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 participant-option wow fadeInUp" 
                             style="cursor: pointer; transition: all 0.3s ease;"
                             onclick="selectIdentitas('tamu_luar')">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-users fa-3x text-primary"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Tamu Luar</h5>
                                <p class="text-muted mb-0">Pengunjung dari luar Politeknik Caltex Riau</p>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-arrow-right me-1"></i>
                                        Klik untuk melanjutkan
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 participant-option wow fadeInUp" 
                             style="cursor: pointer; transition: all 0.3s ease;"
                             onclick="selectIdentitas('civitas_pcr')">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-id-card fa-3x text-success"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Civitas PCR</h5>
                                <p class="text-muted mb-0">Dosen, Staff, dan Mahasiswa PCR</p>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-arrow-right me-1"></i>
                                        Klik untuk melanjutkan
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-start mt-4 mb-4 wow fadeInUp">
                    <a href="{{ route('tamu.event.list') }}"
                        class="btn btn-link p-0 d-flex align-items-center gap-2 text-decoration-none"
                        style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span class="ms-2">Kembali ke Daftar Event</span>
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

<script>
    function selectIdentitas(identitas) {
        const eventId = '{{ $eventId }}';
        
        if (identitas === 'tamu_luar') {
            window.location.href = `{{ route('tamu.event.form') }}?event_id=${eventId}&identitas=tamu_luar`;
        } else if (identitas === 'civitas_pcr') {
            window.location.href = `{{ route('tamu.event.civitas-form') }}?event_id=${eventId}&identitas=civitas_pcr`;
        }
    }
</script>
@endsection