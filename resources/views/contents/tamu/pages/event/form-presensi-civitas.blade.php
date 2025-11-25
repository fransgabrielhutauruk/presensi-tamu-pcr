@extends('layouts.tamu.main')

@section('title', __('visitor.event_attendance_form'))

@section('content')
    <div class="row">
        <div class="col-md-6 justify-content-center mx-auto">
            <div class="text-center mt-5">
                <x-tamu.page-header :title="__('visitor.event_attendance_form')" :subtitle="__('visitor.pcr_civitas')" />

                <div class="text-start mt-3 mb-4 wow fadeInUp">
                    <a href="{{ route('tamu.event.identitas', $eventId) }}"
                        class="btn btn-link p-0 align-items-center gap-2 text-decoration-none"
                        style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span class="ms-2">{{ __('visitor.back') }}</span>
                    </a>
                </div>

                @if (app()->environment('local'))
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                        <small><i class="fas fa-info-circle"></i> {{ __('visitor.development_mode') }}</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoFillForm()">
                            <i class="fas fa-magic"></i> {{ __('visitor.auto_fill') }}
                        </button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm my-4 wow fadeInUp">
                    <div class="card-body px-4 py-3">
                        <div class="row align-items-center">
                            <div class="col-md-9 text-start">
                                <h5 class="fw-bold mb-1">{{ $event->nama_event }}</h5>
                                <div class="row">
                                    @if ($event->tanggal_event)
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="fas fa-calendar"></i>
                                            <span>{{ \Carbon\Carbon::parse($event->tanggal_event)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                                        </small>
                                    @endif
                                    @if ($event->waktu_mulai_event)
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="fas fa-clock"></i>
                                            <span>{{ \Carbon\Carbon::parse($event->waktu_mulai_event)->format('H:i') }}
                                                WIB</span>
                                        </small>
                                    @endif
                                </div>
                                @if ($event->lokasi_event)
                                    <small class="text-muted d-flex align-items-center gap-1">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>{{ $event->lokasi_event }}</span>
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <form id="event-form" class="text-start wow fadeInUp"
                    action="{{ route('tamu.event.store-presensi-civitas') }}" method="POST" data-toggle="validator"
                    novalidate>
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $eventId }}">
                    <x-tamu.section-header :title="__('visitor.personal_data')" icon="👤" />
                    <x-form.input-field name="nim_nip" :label="__('visitor.nim_nip')" :placeholder="__('visitor.nim_nip_placeholder')" required="true" />
                    <x-form.input-field name="nama" :label="__('visitor.full_name')" :placeholder="__('visitor.enter_visitor_name')" required="true" />
                    <x-form.radio-group name="jenis_kelamin" :label="__('visitor.gender')" :required="true" :options="[
                        __('visitor.male', [], 'id') => __('visitor.male'),
                        __('visitor.female', [], 'id') => __('visitor.female'),
                    ]" />

                    <x-form.input-field
                        name="nomor_telepon"
                        :label="__('visitor.phone_number')"
                        :placeholder="__('visitor.enter_phone')"
                        required="true"
                        type="tel"
                        :validationRules='"pattern=\"[0-9]+\" data-pattern-error=\"" . __("visitor.phone_pattern_error") . "\""' />
                    <x-form.input-field name="email" :label="__('visitor.email_address')" :placeholder="__('visitor.enter_email')" required="true"
                        type="email" />
                    <x-form.input-field name="jabatan" :label="__('visitor.position_job')" :placeholder="__('visitor.position_job_placeholder')" required="true" />
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



        // ==================================================
        // ==================================================== //
        function autoFillForm() {
            document.querySelector('input[name="nama"]').value = 'John Doe Event Test';

            const genderMale = document.querySelector('input[name="jenis_kelamin"][value="Laki-laki"]');
            if (genderMale) genderMale.checked = true;

            document.querySelector('input[name="nomor_telepon"]').value = '081234567890';
            document.querySelector('input[name="email"]').value = 'test.event@example.com';
            document.querySelector('input[name="nim_nip"]').value = '2253';
            document.querySelector('input[name="jabatan"]').value = 'Manajer';

            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
            <i class="fas fa-check-circle"></i> {{ __('visitor.form_auto_filled') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

            const form = document.getElementById('event-form');
            form.insertBefore(alertDiv, form.firstChild);

            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
@endsection
