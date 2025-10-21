@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row min-vh-100">
        <div class="col-md-8 justify-content-center mx-auto">
            <div class="text-center">
                <div class="mb-5 mt-5">
                    <h1 class="fw-bold wow fadeInOut fs-2" style="font-size: 1.75rem; letter-spacing: 0.025em;">
                        Pilih Event yang Diikuti
                    </h1>
                    <p class="text-muted mb-2" style="font-size: 0.875rem; font-weight: 500; letter-spacing: 0.15em;">
                        POLITEKNIK CALTEX RIAU
                    </p>
                    <div class="mx-auto" style="height: 3px; width: 5rem; background-color: var(--primary-color); border-radius: 9999px;"></div>
                </div>

                <div class="text-start mb-4">
                    <a href="{{ route('tamu.tujuan') }}"
                        class="btn btn-link p-0  align-items-center gap-2 text-decoration-none"
                        style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span class="ms-2">Kembali</span>
                    </a>
                </div>

                <div class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm participant-option" data-type="tamu_luar">
                                <div class="card-body p-3 text-center">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <h6 class="mb-1">Tamu Luar</h6>
                                    <small class="text-muted">Pengunjung dari luar PCR</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm participant-option" data-type="civitas_pcr">
                                <div class="card-body p-3 text-center">
                                    <i class="fas fa-id-card fa-2x text-success mb-2"></i>
                                    <h6 class="mb-1">Civitas PCR</h6>
                                    <small class="text-muted">Dosen, Staf, Mahasiswa</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-section mb-1 position-relative d-flex align-items-center events-section">
                    <input type="text"
                        class="form-control input-sm form-sm ps-5"
                        placeholder="Cari event berdasarkan nama"
                        style="border-radius: 8px;"
                        id="search-input">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 translate-middle-y ms-2 text-muted" style="left: 0.75rem;"></i>
                </div>

                <div class="text-end mb-3 events-section" id="search-info">
                    <small class="text-muted">
                        <span id="event-count">{{ $events->count() }}</span> event tersedia
                    </small>
                </div>

                <div class="text-start events-section">
                    @if($events->count() > 0)
                    <div class="row g-3" id="events-container">
                        @foreach($events as $event)
                        <div class="col-12 event-item mb-2"
                            data-name="{{ strtolower($event->nama_event) }}"
                            data-kategori="{{ strtolower($event->eventKategori->nama_kategori ?? '') }}"
                            data-lokasi="{{ strtolower($event->lokasi_event ?? '') }}"
                            data-deskripsi="{{ strtolower($event->deskripsi_event ?? '') }}">
                            <a href="#"
                                class="card border-0 shadow-sm h-100 wow fadeInUp event-card"
                                style="cursor: pointer; text-decoration: none; color: inherit;"
                                onclick="selectEvent('{{ encid($event->event_id) }}')">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-0 fw-bold">{{ $event->nama_event }}</h6>
                                                    @if($event->tanggal_event)
                                                    <div>
                                                        <small class="text-muted d-flex align-items-center gap-1">
                                                            <i class="fas fa-calendar"></i>
                                                            <span>{{ \Carbon\Carbon::parse($event->tanggal_event)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                                                        </small>
                                                    </div>
                                                    @endif

                                                    @if($event->waktu_mulai_event)
                                                    <div>
                                                        <small class="text-muted d-flex align-items-center gap-1">
                                                            <i class="fas fa-clock"></i>
                                                            <span>{{ \Carbon\Carbon::parse($event->waktu_mulai_event)->format('H:i') }} WIB</span>
                                                        </small>
                                                    </div>
                                                    @endif

                                                    @if($event->lokasi_event)
                                                    <div>
                                                        <small class="text-muted d-flex align-items-center gap-1">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                            <span>{{ $event->lokasi_event }}</span>
                                                        </small>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>

                    <div class="text-center py-5 events-section" id="no-results" style="display: none;">
                        <div class="mb-3">
                            <i class="fas fa-search fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted mb-2">Tidak ada event yang ditemukan</h5>
                        <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
                    </div>

                    @else
                    <div class="text-center py-5 events-section" id="empty-state">
                        <div class="mb-3">
                            <i class="fas fa-calendar-times fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted mb-2">Belum ada event hari ini</h5>
                        <p class="text-muted">Silakan kembali lagi nanti untuk melihat event yang tersedia.</p>
                        @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .event-card {
        transition: all 0.3s ease;
    }

    .event-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .event-item.hidden {
        display: none !important;
    }

    .participant-option {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent !important;
    }

    .participant-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .participant-option.selected {
        border: 2px solid var(--primary-color) !important;
        background-color: var(--gray-200);
    }

    .events-section {
        display: none;
    }

    .events-section.show {
        display: block;
    }
</style>

<script>
    let selectedParticipantType = null;
    let selectedEventId = null;

    document.addEventListener('DOMContentLoaded', function() {
        const participantOptions = document.querySelectorAll('.participant-option');
        const eventsSection = document.querySelectorAll('.events-section');
        const searchInput = document.getElementById('search-input');
        const eventItems = document.querySelectorAll('.event-item');
        const eventCount = document.getElementById('event-count');
        const noResults = document.getElementById('no-results');
        const eventsContainer = document.getElementById('events-container');

        // Handle participant type selection
        participantOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove previous selection
                participantOptions.forEach(opt => opt.classList.remove('selected'));

                // Add selection to clicked option
                this.classList.add('selected');
                selectedParticipantType = this.dataset.type;

                // Show events section
                eventsSection.forEach(section => section.classList.add('show'));
            });
        });

        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            eventItems.forEach(function(item) {
                const name = item.dataset.name;
                const kategori = item.dataset.kategori;
                const lokasi = item.dataset.lokasi;

                const isMatch = name.includes(searchTerm) ||
                    kategori.includes(searchTerm) ||
                    lokasi.includes(searchTerm);

                if (isMatch) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            eventCount.textContent = visibleCount;

            if (visibleCount === 0 && searchTerm !== '') {
                noResults.style.display = 'block';
                eventsContainer.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                eventsContainer.style.display = 'block';
            }
        }

        searchInput.addEventListener('input', performSearch);

        window.clearSearch = function() {
            searchInput.value = '';
            performSearch();
            searchInput.focus();
        };
    });

    function selectEvent(eventId) {
        if (!selectedParticipantType) {
            alert('Silakan pilih jenis peserta terlebih dahulu');
            return;
        }

        selectedEventId = eventId;

        if (selectedParticipantType === 'tamu_luar') {
            window.location.href = `{{ route('tamu.event.form') }}?event_id=${eventId}&identitas=tamu_luar`;
        } else if (selectedParticipantType === 'civitas_pcr') {
            window.location.href = `{{ route('tamu.event.civitas-form') }}?event_id=${eventId}&identitas=civitas_pcr`;
        }
    }
</script>
@endsection