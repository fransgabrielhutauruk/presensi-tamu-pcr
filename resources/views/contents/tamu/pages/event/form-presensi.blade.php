@extends('layouts.tamu.main')

@section('content')
    <div class="row">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center mt-5">
                <x-tamu.page-header :title="__('visitor.event_attendance_form')" :subtitle="__('visitor.external_guest')" />

                <div class="text-start mt-4">
                    <a href="{{ route('tamu.event.identitas', $eventId) }}"
                        class="btn btn-link p-0 align-items-center gap-2 text-decoration-none"
                        style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span>{{ __('visitor.back') }}</span>
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
                    action="{{ route('tamu.event.store-presensi-non-civitas') }}" method="POST" data-toggle="validator"
                    novalidate>
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $eventId }}">
                    <x-tamu.partials.data-pengunjung />
                    <x-form.input-field name="institusi" :label="__('visitor.institution')" :placeholder="__('visitor.institution_placeholder')" required="true" />
                    <x-form.input-field name="jabatan" :label="__('visitor.position_job')" :placeholder="__('visitor.position_job_placeholder')" required="true" />
                    <x-form.select-field name="transportasi" :label="__('visitor.transportation_type')" required="true" :options="[
                        __('visitor.car_option') => __('visitor.car_option'),
                        __('visitor.motorcycle_option') => __('visitor.motorcycle_option'),
                        __('visitor.bus_option') => __('visitor.bus_option'),
                        __('visitor.travel_option') => __('visitor.travel_option'),
                        __('visitor.online_ride_option') => __('visitor.online_ride_option'),
                        __('visitor.walking_option') => __('visitor.walking_option'),
                        __('visitor.other_option') => __('visitor.other_option'),
                    ]" />

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
            const form = document.getElementById('event-form');
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

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    resetLinkState();
                }
            });

            function resetLinkState() {
                if (submitBtn && btnText && btnLoading) {
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                }
            }
        });

        // ==================================================== //
        function autoFillForm() {
            document.querySelector('input[name="nama"]').value = 'John Doe Event Test';

            const genderMale = document.querySelector('input[name="jenis_kelamin"][value="Laki-laki"]');
            if (genderMale) genderMale.checked = true;

            document.querySelector('input[name="nomor_telepon"]').value = '081234567890';
            document.querySelector('input[name="email"]').value = 'test.event@example.com';
            document.querySelector('input[name="institusi"]').value = 'PT. Test Company';
            document.querySelector('input[name="jabatan"]').value = 'Test Manager';

            const transportasiField = document.querySelector('select[name="transportasi"]');
            if (transportasiField) transportasiField.value = 'Mobil';

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
