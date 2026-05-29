@extends('layouts.tamu.main')

@section('title', __('visitor.event_attendance_form'))

@section('content')
    @php
        $formattedEventDate = $event->tanggal_event
            ? \Carbon\Carbon::parse($event->tanggal_event)->locale('id')->isoFormat('dddd, D MMMM Y')
            : null;

        $formattedEventTime = $event->waktu_mulai_event
            ? \Carbon\Carbon::parse($event->waktu_mulai_event)->format('H:i') . ' WIB'
            : null;

        $genderOptions = [
            __('visitor.male', [], 'id') => __('visitor.male'),
            __('visitor.female', [], 'id') => __('visitor.female'),
        ];

        $lookupEndpoints = [
            'local' => route('tamu.event.check-civitas'),
            'external' => route('tamu.event.fetch-external-data'),
        ];

        $lookupMessages = [
            'invalidIdentifier' => __('visitor.nim_nip_length_error'),
            'foundFillRemaining' => __('visitor.lookup_found_fill_remaining'),
            'notFoundManual' => __('visitor.lookup_not_found_manual'),
            'errorManual' => __('visitor.lookup_error_manual'),
        ];

        $eventRoleOptions = [
            __('visitor.event_role_participant', [], 'id') => __('visitor.event_role_participant'),
            __('visitor.event_role_speaker', [], 'id') => __('visitor.event_role_speaker'),
            __('visitor.event_role_committee', [], 'id') => __('visitor.event_role_committee'),
        ];
    @endphp

    <div class="row">
        <div class="col-md-6 justify-content-center mx-auto">
            <div class="text-center mt-5">
                <x-tamu.page-header :title="__('visitor.event_attendance_form')" :subtitle="__('visitor.pcr_civitas')" />

                <div class="text-start mt-3 mb-4 wow fadeInUp">
                    <a href="{{ route('tamu.event.identitas', $eventId) }}"
                        class="btn btn-link p-0 align-items-center gap-2 text-decoration-none"
                        style="color: var(--dark-color);" data-cy="btn-back-identitas-event-civitas">
                        <i class="fas fa-arrow-left"></i>
                        <span class="ms-2">{{ __('visitor.back') }}</span>
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
                                            <i class="fas fa-calendar"></i>
                                            <span>{{ $formattedEventDate }}</span>
                                        </small>
                                    @endif
                                    @if ($formattedEventTime)
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i class="fas fa-clock"></i>
                                            <span>{{ $formattedEventTime }}</span>
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
                    novalidate data-cy="form-presensi-event-civitas">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $eventId }}" data-cy="input-event-id-civitas">
                    <div id="step-1" data-cy="step-1-civitas">
                        <x-tamu.section-header :title="__('visitor.personal_data')" icon="👤" />
                        <x-form.input-field name="nim_nip" :label="__('visitor.nim_nip')" :placeholder="__('visitor.nim_nip_placeholder')" required="true"
                            id="nim-nip-input" />

                        <div id="lookup-feedback" class="alert d-none" role="alert" data-cy="lookup-feedback-civitas">
                        </div>

                        <div class="mb-4 mt-3">
                            <button type="button" id="btn-lookup" class="btn-default w-100" style="padding: 0.8rem"
                                data-cy="btn-lookup-civitas">
                                <span id="lookup-btn-text"><i
                                        class="fas fa-search me-2"></i>{{ __('visitor.lookup_check_data') }}</span>
                                <span id="lookup-btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('visitor.lookup_loading') }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div id="step-2" style="display: none;" data-cy="step-2-civitas">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <x-tamu.section-header :title="__('visitor.personal_data')" icon="👤" />
                            <button type="button" id="btn-change-identifier"
                                class="btn btn-link p-0 text-decoration-underline" data-cy="btn-change-identifier-civitas">
                                {{ __('visitor.change_nim_nip') }}
                            </button>
                        </div>

                        <x-form.input-field name="nama" :label="__('visitor.full_name')" :placeholder="__('visitor.enter_visitor_name')" required="true"
                            id="nama-input" />
                        <x-form.radio-group name="jenis_kelamin" :label="__('visitor.gender')" :required="true" :options="$genderOptions"
                            id="jenis-kelamin-input" />

                        @php
                            $phoneRules =
                                'pattern=[0-9]+ data-pattern-error="' . __('visitor.phone_pattern_error') . '"';
                        @endphp

                        <x-form.input-field name="nomor_telepon" id="nomor-telepon-input" :label="__('visitor.phone_number')"
                            :placeholder="__('visitor.enter_phone')" required="true" type="tel" :validationRules="$phoneRules" />

                        <x-form.input-field name="email" :label="__('visitor.email_address')" :placeholder="__('visitor.enter_email')" required="true"
                            type="email" id="email-input" />
                        <x-form.select-field name="peran" id="peran-input" :label="__('visitor.event_role_label')" required="true"
                            :options="$eventRoleOptions" :placeholderDisabled="true" />

                        <div class="mt-5 mb-4">
                            <button type="submit" id="submitBtn" class="btn-default w-100"
                                data-cy="btn-submit-presensi-event-civitas">
                                <span id="btn-text">{{ __('visitor.submit') }}</span>
                                <span id="btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('visitor.processing') }}
                                </span>
                            </button>
                        </div>
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

            const step2 = document.getElementById('step-2');
            const nimNipInput = document.getElementById('nim-nip-input');
            const btnLookup = document.getElementById('btn-lookup');
            const lookupBtnText = document.getElementById('lookup-btn-text');
            const lookupBtnLoading = document.getElementById('lookup-btn-loading');
            const btnChangeIdentifier = document.getElementById('btn-change-identifier');
            const lookupFeedback = document.getElementById('lookup-feedback');

            const namaInput = document.getElementById('nama-input');
            const emailInput = document.getElementById('email-input');
            const nomorTeleponInput = document.getElementById('nomor-telepon-input');
            const peranInput = document.getElementById('peran-input');
            const genderRadios = Array.from(document.querySelectorAll('input[name="jenis_kelamin"]'));

            if (!form || !submitBtn || !btnText || !btnLoading || !step2 || !nimNipInput || !btnLookup ||
                !lookupBtnText || !lookupBtnLoading || !btnChangeIdentifier || !lookupFeedback || !namaInput ||
                !emailInput || !nomorTeleponInput || !peranInput) {
                return;
            }

            const step2Fields = Array.from(step2.querySelectorAll('input, select, textarea'));

            const fieldInputs = {
                nama: namaInput,
                email: emailInput,
                nomor_telepon: nomorTeleponInput,
            };

            const readOnlyFields = new Set();
            const lookupEndpoints = @json($lookupEndpoints);
            const lookupMessages = @json($lookupMessages);

            btnLookup.addEventListener('click', function() {
                const nimNip = nimNipInput.value.trim();
                if (!isValidIdentifier(nimNip)) {
                    showLookupFeedback('danger', lookupMessages.invalidIdentifier);
                    clearFormFields();
                    hideStep2();
                    return;
                }

                runLookupFlow(nimNip);
            });

            btnChangeIdentifier.addEventListener('click', function() {
                nimNipInput.readOnly = false;
                nimNipInput.focus();
                clearFormFields();
                hideStep2();
                clearLookupFeedback();
                btnLookup.style.display = 'block';
                clearFormValidation();
            });

            nimNipInput.addEventListener('input', function() {
                clearLookupFeedback();
            });

            async function runLookupFlow(nimNip) {
                setLookupLoading(true);
                clearLookupFeedback();
                clearFormFields();

                try {
                    const localResult = await postJson(lookupEndpoints.local, {
                        nim_nip: nimNip
                    });

                    if (localResult.status && localResult.source === 'civitas') {
                        handleLookupSuccess(localResult);
                        return;
                    }

                    const externalResult = await postJson(lookupEndpoints.external, {
                        nim_nip: nimNip
                    });

                    if (externalResult.status) {
                        handleLookupSuccess(externalResult);
                        return;
                    }

                    handleLookupManualEntry('warning', (externalResult && externalResult.message) ?
                        externalResult.message : lookupMessages.notFoundManual);
                } catch (error) {
                    console.error('Lookup error:', error);
                    handleLookupManualEntry('danger', lookupMessages.errorManual);
                } finally {
                    setLookupLoading(false);
                }
            }

            function handleLookupSuccess(result) {
                revealStep2();
                applyLookupData(result.data, result.autofilled_fields || []);
                btnLookup.style.display = 'none';
                showLookupFeedback('success', lookupMessages.foundFillRemaining);
            }

            function handleLookupManualEntry(feedbackType, message) {
                revealStep2();
                btnLookup.style.display = 'none';
                unlockAllFields();
                showLookupFeedback(feedbackType, message);
            }

            function postJson(url, payload) {
                return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async (response) => {
                        const json = await response.json().catch(() => ({}));
                        return json;
                    });
            }

            function isValidIdentifier(value) {
                return /^(\d{6}|\d{10})$/.test(value);
            }

            function setLookupLoading(isLoading) {
                btnLookup.disabled = isLoading;
                lookupBtnText.style.display = isLoading ? 'none' : 'inline';
                lookupBtnLoading.style.display = isLoading ? 'inline' : 'none';
            }

            function revealStep2() {
                step2.style.display = 'block';
                nimNipInput.readOnly = true;
                setStep2FieldsState(true);
            }

            function hideStep2() {
                step2.style.display = 'none';
                nimNipInput.readOnly = false;
                setStep2FieldsState(false);
            }

            function setStep2FieldsState(enabled) {
                step2Fields.forEach((field) => {
                    if (field.type === 'hidden') {
                        return;
                    }

                    if (enabled) {
                        field.disabled = false;
                        if (field.dataset.wasRequired === 'true') {
                            field.required = true;
                        }
                    } else {
                        if (field.required) {
                            field.dataset.wasRequired = 'true';
                        }
                        field.required = false;
                        field.disabled = true;
                        field.classList.remove('is-valid', 'is-invalid');
                        field.removeAttribute('aria-invalid');
                    }
                });
            }

            function showLookupFeedback(type, message) {
                lookupFeedback.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning',
                    'alert-info');
                lookupFeedback.classList.add('alert-' + type);
                lookupFeedback.textContent = message;
            }

            function clearLookupFeedback() {
                lookupFeedback.classList.add('d-none');
                lookupFeedback.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
                lookupFeedback.textContent = '';
            }

            function clearFormValidation() {
                form.classList.remove('was-validated');
                form.querySelectorAll('.is-valid, .is-invalid').forEach((el) => {
                    el.classList.remove('is-valid', 'is-invalid');
                });
                form.querySelectorAll('[aria-invalid="true"]').forEach((el) => {
                    el.removeAttribute('aria-invalid');
                });

                if (window.jQuery && typeof window.jQuery.fn.validator === 'function') {
                    window.jQuery(form).validator('destroy');
                    window.jQuery(form).validator();
                }
            }

            function applyLookupData(data, autofilledFields) {
                unlockAllFields();

                if (data.nama) {
                    namaInput.value = data.nama;
                }
                if (data.email) {
                    emailInput.value = data.email;
                }
                if (data.nomor_telepon) {
                    nomorTeleponInput.value = data.nomor_telepon;
                }
                if (data.jenis_kelamin) {
                    const matchedRadio = genderRadios.find((radio) => radio.value === data.jenis_kelamin);
                    if (matchedRadio) {
                        matchedRadio.checked = true;
                    }
                }

                autofilledFields.forEach((field) => {
                    lockField(field);
                });
            }

            function lockField(fieldName) {
                readOnlyFields.add(fieldName);

                if (fieldName === 'jenis_kelamin') {
                    const selectedGender = genderRadios.find((radio) => radio.checked);
                    if (selectedGender) {
                        genderRadios.forEach((radio) => {
                            radio.disabled = true;
                        });
                        setGenderHiddenValue(selectedGender.value);
                    }
                    return;
                }

                const input = fieldInputs[fieldName];
                if (!input) {
                    return;
                }

                input.readOnly = true;
                input.style.cursor = 'not-allowed';
                input.classList.add('bg-light');
            }

            function unlockAllFields() {
                readOnlyFields.clear();

                Object.values(fieldInputs).forEach((input) => {
                    if (!input) {
                        return;
                    }

                    input.readOnly = false;
                    input.style.cursor = 'text';
                    input.classList.remove('bg-light');
                });

                genderRadios.forEach((radio) => {
                    radio.disabled = false;
                });
                removeGenderHiddenInput();
            }

            function clearFormFields() {
                namaInput.value = '';
                emailInput.value = '';
                nomorTeleponInput.value = '';
                peranInput.value = '';
                genderRadios.forEach(radio => radio.checked = false);
                unlockAllFields();
            }

            function setGenderHiddenValue(value) {
                let hiddenInput = document.getElementById('jenis-kelamin-hidden');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id = 'jenis-kelamin-hidden';
                    hiddenInput.name = 'jenis_kelamin';
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = value;
            }

            function removeGenderHiddenInput() {
                const hiddenInput = document.getElementById('jenis-kelamin-hidden');
                if (hiddenInput) {
                    hiddenInput.remove();
                }
            }

            form.addEventListener('submit', function(e) {
                const isValid = form.checkValidity();
                if (!isValid) {
                    return;
                }

                if (readOnlyFields.has('jenis_kelamin')) {
                    const selectedGender = genderRadios.find((radio) => radio.checked);
                    if (selectedGender) {
                        setGenderHiddenValue(selectedGender.value);
                    }
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

            if (nimNipInput.value.trim() && (namaInput.value.trim() || emailInput.value.trim() || nomorTeleponInput
                    .value
                    .trim() || peranInput.value.trim())) {
                revealStep2();
            } else {
                hideStep2();
            }

            function resetLinkState() {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    </script>
@endsection
