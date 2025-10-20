@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center">
                <div class="mb-4 mt-5">
                    <h1 class="fw-bold wow fadeInOut fs-2" data-wow-delay="0.1s" style="font-size: 1.75rem; letter-spacing: 0.025em;">
                        <span>Form Presensi Event</span>
                    </h1>
                    <p class="text-muted mb-2 text-uppercase" style="font-size: 0.875rem; font-weight: 500; letter-spacing: 0.15em;">
                        {{ $event->nama_event}}
                    </p>
                    <div class="mx-auto" style="height: 3px; width: 5rem; background-color: var(--primary-color); border-radius: 9999px;"></div>
                </div>

                <div class="text-start">
                    <a href="{{ route('tamu.event.list') }}"
                        class="btn btn-link p-0 mb-4 d-flex align-items-center gap-2 text-decoration-none"
                        style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>

                @if(app()->environment('local'))
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                    <small><i class="fas fa-info-circle"></i> Mode Development - Auto Fill untuk Testing</small>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoFillForm()">
                        <i class="fas fa-magic"></i> Auto Fill
                    </button>
                </div>
                @endif

                <div class="wow fadeInUp" data-wow-delay="0.2s" style="margin-bottom: 2rem;">
                    <form id="event-form" class="text-start" action="{{ route('tamu.event.store') }}" method="POST" data-toggle="validator">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $eventId }}">

                        <div id="error-messages" class="alert alert-danger" style="display: none;">
                            <ul id="error-list" class="mb-0"></ul>
                        </div>

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Terdapat kesalahan:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <div class="form-section wow fadeInUp mt-4" data-wow-delay="0.3s">
                            <div class="mb-3">
                                <h3 class="d-flex align-items-center gap-2 mb-3" style="font-size: 1.125rem; font-weight: 600;">
                                    <span>🧑‍💼</span>
                                    <span>Data Pengunjung</span>
                                </h3>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold control-label" for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Nama lengkap" value="{{ old('name') }}" required>
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold control-label" for="gender">Jenis Kelamin <span class="text-danger">*</span></label>
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
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold control-label" for="phone_number">No. Telepon <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone_number" id="phone_number" placeholder="08xxxxxxxxxx" value="{{ old('phone_number') }}" required>
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold control-label" for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="email@contoh.com" value="{{ old('email') }}" required>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>

                        <div class="form-section wow fadeInUp mt-4" data-wow-delay="0.4s">
                            <div class="mb-3">
                                <h3 class="d-flex align-items-center gap-2 mb-3" style="font-size: 1.125rem; font-weight: 600;">
                                    <span>🏢</span>
                                    <span>Data Tambahan (Opsional)</span>
                                </h3>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold control-label" for="instansi">Instansi/Perusahaan</label>
                                <input type="text" class="form-control" name="instansi" id="instansi" placeholder="Nama instansi/perusahaan" value="{{ old('instansi') }}">
                                <div class="help-block with-errors"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold control-label" for="jabatan">Jabatan/Posisi</label>
                                <input type="text" class="form-control" name="jabatan" id="jabatan" placeholder="Jabatan/posisi" value="{{ old('jabatan') }}">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>

                        <div class="form-section wow fadeInUp mt-4" data-wow-delay="0.5s">
                            <div class="mb-3">
                                <h3 class="d-flex align-items-center gap-2 mb-3" style="font-size: 1.125rem; font-weight: 600;">
                                    <span>🚗</span>
                                    <span>Data Kunjungan</span>
                                </h3>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold control-label" for="transportasi">Jenis Kendaraan/Transportasi <span class="text-danger">*</span></label>
                                <select class="form-select" name="transportasi" id="transportasi" required>
                                    <option value="">Pilih Kendaraan/Transportasi</option>
                                    <option value="Mobil" {{ old('transportasi') == 'Mobil' ? 'selected' : '' }}>Mobil</option>
                                    <option value="Motor" {{ old('transportasi') == 'Motor' ? 'selected' : '' }}>Motor</option>
                                    <option value="Bus" {{ old('transportasi') == 'Bus' ? 'selected' : '' }}>Bus</option>
                                    <option value="Travel" {{ old('transportasi') == 'Travel' ? 'selected' : '' }}>Travel</option>
                                    <option value="Online Ride" {{ old('transportasi') == 'Online Ride' ? 'selected' : '' }}>Online Ride</option>
                                    <option value="Jalan Kaki" {{ old('transportasi') == 'Jalan Kaki' ? 'selected' : '' }}>Jalan Kaki</option>
                                    <option value="Lainnya" {{ old('transportasi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>

                        <div class="my-4">
                            <button type="submit" id="submitBtn" class="btn-default w-100">
                                <span id="btn-text">Kirim</span>
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

            const form = document.getElementById('event-form');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            const submitBtn = document.getElementById('submitBtn');

            if (form) {
                form.addEventListener('submit', function(e) {
                    if (btnText && btnLoading && submitBtn) {
                        btnText.style.display = 'none';
                        btnLoading.style.display = 'inline';
                        submitBtn.disabled = true;
                    }
                });
            }

            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('blur', function() {
                    const formGroup = this.closest('.form-group');
                    const helpBlock = formGroup ? formGroup.querySelector('.help-block.with-errors') : null;

                    if (this.value.trim() === '') {
                        formGroup?.classList.add('has-error');
                        if (helpBlock) {
                            helpBlock.textContent = 'Field ini wajib diisi.';
                        }
                    } else {
                        formGroup?.classList.remove('has-error');
                        if (helpBlock) {
                            helpBlock.textContent = '';
                        }
                    }
                });
            });
        });

        // ===================================================================
        function autoFillForm() {
            document.querySelector('input[name="name"]').value = 'John Doe Event Test';
            document.querySelector('input[name="gender"][value="Laki-laki"]').checked = true;
            document.querySelector('input[name="phone_number"]').value = '081234567890';
            document.querySelector('input[name="email"]').value = 'test.event@example.com';
            document.querySelector('input[name="instansi"]').value = 'PT. Test Company';
            document.querySelector('input[name="jabatan"]').value = 'Test Manager';

            const transportasiField = document.querySelector('select[name="transportasi"]');
            if (transportasiField) transportasiField.value = 'Mobil';

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