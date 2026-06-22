@extends('layouts.tamu.main')

@section('title', __('visitor.event_attendance_form'))

@section('content')
    @php
        $formattedEventDate = $event->formatted_date_range;

        $formattedEventTime = $event->waktu_mulai_event
            ? \Carbon\Carbon::parse($event->waktu_mulai_event)->format('H:i') . ' WIB'
            : null;

        $transportationOptions = [
            __('visitor.car_option') => __('visitor.car_option'),
            __('visitor.motorcycle_option') => __('visitor.motorcycle_option'),
            __('visitor.bus_option') => __('visitor.bus_option'),
            __('visitor.online_ride_option') => __('visitor.online_ride_option'),
            __('visitor.walking_option') => __('visitor.walking_option'),
            __('visitor.other_option') => __('visitor.other_option'),
        ];

        $eventRoleOptions = [
            __('visitor.event_role_participant', [], 'id') => __('visitor.event_role_participant'),
            __('visitor.event_role_speaker', [], 'id') => __('visitor.event_role_speaker'),
            __('visitor.event_role_committee', [], 'id') => __('visitor.event_role_committee'),
        ];
    @endphp

    <div class="row">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center mt-4">
                <x-tamu.page-header :title="__('visitor.event_attendance_form')" :subtitle="__('visitor.external_guest')" />

                <div class="text-start mt-4">
                    <a href="{{ route('tamu.event.identitas', $eventId) }}"
                        class="btn btn-link p-0 align-items-center gap-2 text-decoration-none"
                        style="color: var(--dark-color);" data-cy="btn-back-identitas-event">
                        <i class="fas fa-arrow-left"></i>
                        <span>{{ __('visitor.back') }}</span>
                    </a>
                </div>

                <div class="card border-0 shadow-sm my-4 wow fadeInUp">
                    <div class="card-body px-4 py-3">
                        <div class="row align-items-center">
                            <div class="col-md-9 text-start">
                                <h5 class="fw-bold mb-1">{{ $event->nama_event }}</h5>
                                <div class="row">
                                    @if ($formattedEventDate)
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="fas fa-calendar" style="font-size: 13px"></i>
                                            <span>{{ $formattedEventDate }}</span>
                                        </small>
                                    @endif
                                    @if ($formattedEventTime)
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="fas fa-clock" style="font-size: 12px"></i>
                                            <span>{{ $formattedEventTime }}</span>
                                        </small>
                                    @endif
                                </div>
                                @if ($event->lokasi_event)
                                    <small class="text-muted d-flex align-items-center gap-1">
                                        <i class="fas fa-map-marker-alt" style="font-size: 15px"></i>
                                        <span>{{ $event->lokasi_event }}</span>
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <form id="event-form" class="text-start wow fadeInUp"
                    action="{{ route('tamu.event.store-presensi-non-civitas') }}" method="POST" data-toggle="validator"
                    novalidate data-cy="form-presensi-event-non-civitas">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $eventId }}" data-cy="input-event-id">
                    <x-tamu.partials.data-pengunjung />
                    <x-form.input-field name="instansi" :label="__('visitor.institution')" :placeholder="__('visitor.institution_name_placeholder')" required="true" />
                    <x-form.select-field name="peran" :label="__('visitor.event_role_label')" required="true" :options="$eventRoleOptions"
                        :placeholderDisabled="true" />

                    @if ($event->jenis_kegiatan !== 'pmb')
                        <x-form.select-field name="transportasi" :label="__('visitor.transportation_type')" required="true" :options="$transportationOptions" />
                    @endif

                    @if ($event->jenis_kegiatan === 'pmb')
                        <div class="mt-4">
                            <x-tamu.section-header :title="__('visitor.event_pmb_program')" icon="🎓" />
                            <x-form.radio-group name="minat_masuk_pcr" :label="__('visitor.willing_to_join_pcr')" :required="true"
                                :options="[
                                    __('visitor.yes', [], 'id') => __('visitor.yes'),
                                    __('visitor.no', [], 'id') => __('visitor.no'),
                                    __('visitor.hesitant', [], 'id') => __('visitor.hesitant'),
                                ]" />
                            <x-form.select-field name="prodi_diminati" :label="__('visitor.interested_programs')" required="true" :options="$prodiOptions"
                                :multiple="true" />
                        </div>
                    @endif

                    <div class="mt-5 mb-4">
                        <button type="submit" id="submitBtn" class="btn-default w-100" data-cy="btn-submit-presensi-event">
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

            if (!form || !submitBtn || !btnText || !btnLoading) {
                return;
            }

            form.addEventListener('submit', function(e) {
                const isValid = form.checkValidity();
                if (!isValid) {
                    return;
                }

                e.preventDefault();

                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                submitBtn.disabled = true;

                form.submit();
            });

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    resetLinkState();
                }
            });

            function resetLinkState() {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    </script>
@endsection
