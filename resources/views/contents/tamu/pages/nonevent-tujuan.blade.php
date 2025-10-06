@extends('layouts.tamu.main')

@section('header')
<div></div>
@endsection

@section('content')
<div class="container">
    <div class="row min-vh-100 align-items-center">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center">
                <div class="relative min-h-screen d-flex flex-column justify-content-center mx-auto">
                    <div class="position-relative" style="z-index: 10;">

                        <header class="text-center">
                            <h1 class="fw-bold fs-2 wow fadeInOut" data-wow-delay="0.5s" style="font-size: 1.75rem; letter-spacing: 0.025em;">
                                Presensi Kunjungan Tamu
                            </h1>
                            <p class="mt-1 text-muted mb-2" style="font-size: 0.875rem; font-weight: 500;">
                                POLITEKNIK CALTEX RIAU
                            </p>
                            <div class="mx-auto rounded-pill" style="height: 3px; width: 5rem; background-color: var(--primary-color);"></div>
                        </header>

                        <div style="margin-top: 2.5rem;">
                            <h2 class="fw-semibold d-flex align-items-center justify-content-center gap-2 wow fadeInUp" data-wow-delay="1s" style="font-size: 1rem;">
                                <span style="font-size: 1.25rem; line-height: 1;">👋</span>
                                <span class="fw-bold" style="font-size: 1.125rem;">Apa tujuan kunjungan Anda hari ini?</span>
                            </h2>
                        </div>

                        <form id="tujuan-form" class="d-flex flex-column flex-fill mt-4 mx-auto">
                            <fieldset class="d-flex flex-column" style="gap: 1rem;">
                                <label for="opt-instansi" class="tujuan-option d-flex align-items-center gap-3 rounded border bg-white px-3 py-3 wow fadeInUp" data-wow-delay="1.2s" style="cursor: pointer; transition: all 0.3s ease; border-color: #e2e8f0;">
                                    <span class="position-relative d-flex align-items-center justify-content-center" style="height: 1.25rem; width: 1.25rem;">
                                        <input class="radio-input" style="appearance: none; width: 1.25rem; height: 1.25rem; border-radius: 50%; border: 1px solid #94a3b8; transition: all 0.3s ease; margin: 0;" type="radio" name="tujuan" id="opt-instansi" value="instansi" required>
                                        <span class="radio-dot position-absolute rounded-circle" style="inset: 0; pointer-events: none; background-color: white; width: 0.5rem; height: 0.5rem; transform: scale(0); transition: transform 0.3s ease; top: 50%; left: 50%; translate: -50% -50%;"></span>
                                    </span>
                                    <span class="user-select-none" style="font-size: 1.125rem; line-height: 1;">🏢</span>
                                    <span class="fw-medium user-select-none text-dark" style="font-size: 0.875rem;">Kunjungan Resmi Instansi</span>
                                </label>

                                <label for="opt-bisnis" class="tujuan-option d-flex align-items-center gap-3 rounded border bg-white px-3 py-3 wow fadeInUp" data-wow-delay="1.4s" style="cursor: pointer; transition: all 0.3s ease; border-color: #e2e8f0;">
                                    <span class="position-relative d-flex align-items-center justify-content-center" style="height: 1.25rem; width: 1.25rem;">
                                        <input class="radio-input" style="appearance: none; width: 1.25rem; height: 1.25rem; border-radius: 50%; border: 1px solid #94a3b8; transition: all 0.3s ease; margin: 0;" type="radio" name="tujuan" id="opt-bisnis" value="bisnis" required>
                                        <span class="radio-dot position-absolute rounded-circle" style="inset: 0; pointer-events: none; background-color: white; width: 0.5rem; height: 0.5rem; transform: scale(0); transition: transform 0.3s ease; top: 50%; left: 50%; translate: -50% -50%;"></span>
                                    </span>
                                    <span class="user-select-none" style="font-size: 1.125rem; line-height: 1;">🤝</span>
                                    <span class="fw-medium user-select-none text-dark" style="font-size: 0.875rem;">Keperluan Bisnis/Kemitraan</span>
                                </label>

                                <label for="opt-ortu" class="tujuan-option d-flex align-items-center gap-3 rounded border bg-white px-3 py-3 wow fadeInUp" data-wow-delay="1.6s" style="cursor: pointer; transition: all 0.3s ease; border-color: #e2e8f0;">
                                    <span class="position-relative d-flex align-items-center justify-content-center" style="height: 1.25rem; width: 1.25rem;">
                                        <input class="radio-input" style="appearance: none; width: 1.25rem; height: 1.25rem; border-radius: 50%; border: 1px solid #94a3b8; transition: all 0.3s ease; margin: 0;" type="radio" name="tujuan" id="opt-ortu" value="ortu" required>
                                        <span class="radio-dot position-absolute rounded-circle" style="inset: 0; pointer-events: none; background-color: white; width: 0.5rem; height: 0.5rem; transform: scale(0); transition: transform 0.3s ease; top: 50%; left: 50%; translate: -50% -50%;"></span>
                                    </span>
                                    <span class="user-select-none" style="font-size: 1.125rem; line-height: 1;">👪</span>
                                    <span class="fw-medium user-select-none text-dark" style="font-size: 0.875rem;">Orang Tua/Wali Mahasiswa</span>
                                </label>

                                <label for="opt-calon-ortu" class="tujuan-option d-flex align-items-center gap-3 rounded border bg-white px-3 py-3 wow fadeInUp" data-wow-delay="1.8s" style="cursor: pointer; transition: all 0.3s ease; border-color: #e2e8f0;">
                                    <span class="position-relative d-flex align-items-center justify-content-center" style="height: 1.25rem; width: 1.25rem;">
                                        <input class="radio-input" style="appearance: none; width: 1.25rem; height: 1.25rem; border-radius: 50%; border: 1px solid #94a3b8; transition: all 0.3s ease; margin: 0;" type="radio" name="tujuan" id="opt-calon-ortu" value="calon_ortu" required>
                                        <span class="radio-dot position-absolute rounded-circle" style="inset: 0; pointer-events: none; background-color: white; width: 0.5rem; height: 0.5rem; transform: scale(0); transition: transform 0.3s ease; top: 50%; left: 50%; translate: -50% -50%;"></span>
                                    </span>
                                    <span class="user-select-none" style="font-size: 1.125rem; line-height: 1;">👨‍👩‍👧</span>
                                    <span class="fw-medium user-select-none text-dark" style="font-size: 0.875rem;">Calon Orang Tua/Wali Mahasiswa</span>
                                </label>

                                <label for="opt-lainnya" class="tujuan-option d-flex align-items-center gap-3 rounded border bg-white px-3 py-3 wow fadeInUp" data-wow-delay="2.0s" style="cursor: pointer; transition: all 0.3s ease; border-color: #e2e8f0;">
                                    <span class="position-relative d-flex align-items-center justify-content-center" style="height: 1.25rem; width: 1.25rem;">
                                        <input class="radio-input" style="appearance: none; width: 1.25rem; height: 1.25rem; border-radius: 50%; border: 1px solid #94a3b8; transition: all 0.3s ease; margin: 0;" type="radio" name="tujuan" id="opt-lainnya" value="lainnya" required>
                                        <span class="radio-dot position-absolute rounded-circle" style="inset: 0; pointer-events: none; background-color: white; width: 0.5rem; height: 0.5rem; transform: scale(0); transition: transform 0.3s ease; top: 50%; left: 50%; translate: -50% -50%;"></span>
                                    </span>
                                    <span class="user-select-none" style="font-size: 1.125rem; line-height: 1;">🗓️</span>
                                    <span class="fw-medium user-select-none text-dark" style="font-size: 0.875rem;">Lainnya</span>
                                </label>
                            </fieldset>

                            <div id="error-message" class="alert alert-danger mt-3" role="alert" style="display: none;"></div>

                            <button type="submit" id="submit-btn" class="btn-default w-100 mt-4 wow fadeInUp" data-wow-delay="2.2s">
                                <span id="btn-text">Lanjutkan</span>
                                <span id="btn-loading" style="display: none;">Loading...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tujuan-option:hover {
        border-color: #22d3ee !important;
        background-color: rgba(34, 211, 238, 0.1) !important;
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

        let isLoading = false;

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

            if (isLoading) return;

            const selectedValue = document.querySelector('input[name="tujuan"]:checked');

            if (!selectedValue) {
                errorMessage.textContent = 'Silakan pilih tujuan kunjungan Anda.';
                errorMessage.style.display = 'block';
                return;
            }

            isLoading = true;
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';

            window.location.href = `{{ route('tamu.nonevent.form') }}?tujuan=${selectedValue.value}`;
        });
    });
</script>
@endsection