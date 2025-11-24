@extends('layouts.tamu.main')

@section('content')
    <div class="row pt-5 d-flex align-items-center" style="min-height: 90vh">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center">
                <div class="relative min-h-screen d-flex flex-column justify-content-center mx-auto">
                    <div class="position-relative" style="z-index: 10;">
                        <x-tamu.page-header title="{{ __('visitor.visit_purpose_title') }}"
                            question="{{ __('visitor.select_purpose') }}" />
                        <form id="tujuan-form" class="d-flex flex-column flex-fill mt-4 mx-auto">
                            <fieldset class="d-flex flex-column" style="gap: 1rem;">
                                <x-form.radio-option name="tujuan" id="opt-instansi" value="instansi" icon="🏢"
                                    label="{{ __('visitor.institutional_visit') }}" delay="0.2s" :required="true" />

                                <x-form.radio-option name="tujuan" id="opt-bisnis" value="bisnis" icon="🤝"
                                    label="{{ __('visitor.business_partnership') }}" delay="0.3s" :required="true" />

                                <x-form.radio-option name="tujuan" id="opt-ortu" value="ortu" icon="👪"
                                    label="{{ __('visitor.parent_student') }}" delay="0.4s" :required="true" />

                                <x-form.radio-option name="tujuan" id="opt-calon-ortu" value="informasi_kampus"
                                    icon="👨‍👩‍👧" label="{{ __('visitor.campus_info_pmb') }}" delay="0.5s"
                                    :required="true" />

                                <x-form.radio-option name="tujuan" id="opt-lainnya" value="lainnya" icon="🗓️"
                                    label="{{ __('visitor.other') }}" delay="0.6s" :required="true" />
                            </fieldset>

                            <div id="error-message" class="alert alert-danger mt-3" role="alert" style="display: none;">
                            </div>

                            <button type="submit" id="submit-btn" class="btn-default w-100 mt-4 wow fadeInUp"
                                data-wow-delay="0.7s">
                                {{ __('visitor.continue') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tujuan-option:hover {
            border-color: #22d3ee !important;
            background-color: var(--gray-200) !important;
        }

        .tujuan-option.selected {
            border-color: var(--primary-color) !important;
        }

        .tujuan-option.selected .radio-input {
            border-color: var(--primary-color) !important;
            background-color: var(--primary-color) !important;
        }

        .tujuan-option.selected .radio-dot {
            transform: scale(1) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('tujuan-form');
            const radioInputs = document.querySelectorAll('input[name="tujuan"]');
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoading = document.getElementById('btn-loading');
            const errorMessage = document.getElementById('error-message');

            radioInputs.forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.tujuan-option').forEach(option => {
                        option.classList.remove('selected');
                    });

                    if (this.checked) {
                        this.closest('.tujuan-option').classList.add('selected');
                    }

                    errorMessage.style.display = 'none';
                });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const selectedValue = document.querySelector('input[name="tujuan"]:checked');

                if (!selectedValue) {
                    errorMessage.textContent = '{{ __('visitor.purpose_required') }}';
                    errorMessage.style.display = 'block';
                    return;
                }

                window.location.href =
                    `{{ route('tamu.non-event.form-presensi') }}?tujuan=${selectedValue.value}`;
            });
        });
    </script>
@endsection
