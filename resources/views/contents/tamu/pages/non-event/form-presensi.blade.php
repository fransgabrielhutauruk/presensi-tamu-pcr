@extends('layouts.tamu.main')

@php
    $tujuanConfig = [
        'instansi' => [
            'title' => __('visitor.institutional_official'),
            'partial' => 'components.tamu.partials.instansi',
        ],
        'bisnis' => [
            'title' => __('visitor.business_matters'),
            'partial' => 'components.tamu.partials.bisnis',
        ],
        'ortu' => [
            'title' => __('visitor.parent_guardian_visit'),
            'partial' => 'components.tamu.partials.ortu',
        ],
        'informasi_kampus' => [
            'title' => __('visitor.campus_information'),
            'partial' => 'components.tamu.partials.calon-ortu',
        ],
        'lainnya' => [
            'title' => __('visitor.other_purposes'),
            'partial' => 'components.tamu.partials.lainnya',
        ],
    ];

    $tujuanKey = is_string($tujuan) ? $tujuan : '';

    $currentTujuan = $tujuanConfig[$tujuanKey] ?? [
        'title' => 'Kunjungan',
        'partial' => null,
    ];
@endphp

@section('title', $currentTujuan['title'])

@section('content')

    <div class="row">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center mt-4">
                <x-tamu.page-header :title="$currentTujuan['title']" />

                <div class="text-start mt-4">
                    <a href="{{ route('tamu.non-event.tujuan') }}" class="btn btn-link p-0 mb-2 gap-2 text-decoration-none"
                        style="color: var(--dark-color);" data-cy="btn-back-tujuan">
                        <i class="fas fa-arrow-left"></i>
                        <span>{{ __('visitor.back') }}</span>
                    </a>
                </div>

                <form id="tamu-form" class="text-start wow fadeInUp" action="{{ route('tamu.non-event.store-presensi') }}"
                    method="POST" data-toggle="validator" novalidate data-cy="form-presensi-non-event">
                    @csrf
                    <input type="hidden" name="kategori_tujuan" value="{{ $tujuanKey }}"
                        data-cy="input-kategori-tujuan">

                    @if ($currentTujuan['partial'])
                        @include($currentTujuan['partial'])
                    @endif

                    <div class="mt-5 mb-4">
                        <button type="submit" id="submitBtn" class="btn-default w-100" data-cy="btn-submit-presensi">
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
            const form = document.getElementById('tamu-form');
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
                if (submitBtn && btnText && btnLoading) {
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                }
            }
        });
    </script>
@endsection
