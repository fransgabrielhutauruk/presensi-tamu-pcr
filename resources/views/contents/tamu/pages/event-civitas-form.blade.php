@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row min-vh-100">
        <div class="col-md-8 justify-content-center mx-auto">
            <div class="text-center">
                <div class="mb-5 mt-5">
                    <h1 class="fw-bold wow fadeInOut fs-2" style="font-size: 1.75rem; letter-spacing: 0.025em;">
                        Presensi Event - Civitas PCR
                    </h1>
                    <p class="text-muted mb-2" style="font-size: 0.875rem; font-weight: 500; letter-spacing: 0.15em;">
                        POLITEKNIK CALTEX RIAU
                    </p>
                    <div class="mx-auto" style="height: 3px; width: 5rem; background-color: var(--primary-color); border-radius: 9999px;"></div>
                </div>

                <div class="card border-0 shadow-sm mb-4 wow fadeInUp">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                            </div>
                            <div class="col-md-9 text-start">
                                <h4 class="fw-bold mb-2">{{ $event->nama_event }}</h4>

                                @if($event->eventKategori)
                                <div class="mb-2">
                                    <span class="badge bg-secondary">{{ $event->eventKategori->nama_kategori }}</span>
                                </div>
                                @endif

                                @if($event->deskripsi_event)
                                <p class="text-muted mb-2">{{ $event->deskripsi_event }}</p>
                                @endif

                                <div class="row">
                                    @if($event->tanggal_event)
                                    <div class="col-md-6">
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="fas fa-calendar"></i>
                                            <span>{{ \Carbon\Carbon::parse($event->tanggal_event)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                                        </small>
                                    </div>
                                    @endif

                                    @if($event->waktu_mulai_event)
                                    <div class="col-md-6">
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="fas fa-clock"></i>
                                            <span>{{ \Carbon\Carbon::parse($event->waktu_mulai_event)->format('H:i') }} WIB</span>
                                        </small>
                                    </div>
                                    @endif
                                </div>

                                @if($event->lokasi_event)
                                <div class="mt-2">
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

                <div class="card border-0 shadow-sm wow fadeInUp">
                    <div class="card-body p-4">
                        <form action="{{ route('tamu.event.civitas-store') }}" method="POST" id="presensiForm">
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $eventId }}">
                            <input type="hidden" name="identitas" value="civitas_pcr">

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="jenis_civitas" class="form-label fw-bold text-start d-block">
                                        Jenis Civitas <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="jenis_civitas" name="jenis_civitas" required>
                                        <option value="">Pilih Jenis Civitas</option>
                                        <option value="dosen" {{ old('jenis_civitas') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                        <option value="staff" {{ old('jenis_civitas') == 'staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="mahasiswa" {{ old('jenis_civitas') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                    </select>
                                    @error('jenis_civitas')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nim_nip" class="form-label fw-bold text-start d-block">
                                        <span id="nim_nip_label">NIM/NIP</span> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        id="nim_nip"
                                        name="nim_nip"
                                        placeholder="Masukkan NIM/NIP"
                                        value="{{ old('nim_nip') }}"
                                        required>
                                    @error('nim_nip')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nama_lengkap" class="form-label fw-bold text-start d-block">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        id="nama_lengkap"
                                        name="nama_lengkap"
                                        placeholder="Masukkan nama lengkap"
                                        value="{{ old('nama_lengkap') }}"
                                        required>
                                    @error('nama_lengkap')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold text-start d-block">
                                        Jenis Kelamin <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex gap-4 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="gender-male" value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="gender-male">
                                                Laki-laki
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="gender-female" value="Perempuan" {{ old('gender') == 'Perempuan' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="gender-female">
                                                Perempuan
                                            </label>
                                        </div>
                                    </div>
                                    @error('gender')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-bold text-start d-block">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        placeholder="Masukkan email"
                                        value="{{ old('email') }}"
                                        required>
                                    @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label fw-bold text-start d-block">
                                        Nomor HP <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel"
                                        class="form-control"
                                        id="phone_number"
                                        name="phone_number"
                                        placeholder="Contoh: 08123456789"
                                        value="{{ old('phone_number') }}"
                                        required>
                                    @error('phone_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3" id="prodi_unit_section">
                                    <label for="prodi_unit" class="form-label fw-bold text-start d-block">
                                        <span id="prodi_unit_label">Prodi/Unit Kerja</span>
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        id="prodi_unit"
                                        name="prodi_unit"
                                        placeholder="Masukkan prodi/unit kerja"
                                        value="{{ old('prodi_unit') }}">
                                    @error('prodi_unit')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="transportasi" class="form-label fw-bold text-start d-block">
                                        Transportasi <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="transportasi" name="transportasi" required>
                                        <option value="">Pilih transportasi</option>
                                        <option value="Motor" {{ old('transportasi') == 'Motor' ? 'selected' : '' }}>Motor</option>
                                        <option value="Mobil" {{ old('transportasi') == 'Mobil' ? 'selected' : '' }}>Mobil</option>
                                        <option value="Ojek Online" {{ old('transportasi') == 'Ojek Online' ? 'selected' : '' }}>Ojek Online</option>
                                        <option value="Angkutan Umum" {{ old('transportasi') == 'Angkutan Umum' ? 'selected' : '' }}>Angkutan Umum</option>
                                        <option value="Jalan Kaki" {{ old('transportasi') == 'Jalan Kaki' ? 'selected' : '' }}>Jalan Kaki</option>
                                        <option value="Lainnya" {{ old('transportasi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    @error('transportasi')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary py-3 fw-bold">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Daftar Presensi Event
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-start mt-3 mb-4 wow fadeInUp">
                    <a href="{{ route('tamu.event.identity-selection', ['event_id' => $eventId]) }}" 
                        class="btn btn-link p-0 d-flex align-items-center gap-2 text-decoration-none" 
                        style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span class="ms-2">Kembali ke Pilih Jenis Peserta</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisCivitasSelect = document.getElementById('jenis_civitas');
        const nimNipLabel = document.getElementById('nim_nip_label');
        const prodiUnitLabel = document.getElementById('prodi_unit_label');
        const prodiUnitSection = document.getElementById('prodi_unit_section');

        const phoneInput = document.getElementById('phone_number');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('08')) {
                e.target.value = value;
            } else if (value.startsWith('8')) {
                e.target.value = '0' + value;
            } else {
                e.target.value = value;
            }
        });

        jenisCivitasSelect.addEventListener('change', function() {
            const selectedValue = this.value;

            switch (selectedValue) {
                case 'dosen':
                    nimNipLabel.textContent = 'NIP';
                    prodiUnitLabel.textContent = 'Program Studi';
                    prodiUnitSection.style.display = 'block';
                    break;
                case 'staff':
                    nimNipLabel.textContent = 'NIP';
                    prodiUnitLabel.textContent = 'Unit Kerja';
                    prodiUnitSection.style.display = 'block';
                    break;
                case 'mahasiswa':
                    nimNipLabel.textContent = 'NIM';
                    prodiUnitLabel.textContent = 'Program Studi';
                    prodiUnitSection.style.display = 'block';
                    break;
                default:
                    nimNipLabel.textContent = 'NIM/NIP';
                    prodiUnitLabel.textContent = 'Prodi/Unit Kerja';
                    prodiUnitSection.style.display = 'block';
            }
        });

        if (jenisCivitasSelect.value) {
            jenisCivitasSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection