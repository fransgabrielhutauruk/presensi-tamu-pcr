@extends('layouts.tamu.main')

@php
    $tujuanMap = [
        'instansi' => __('visitor.institutional_official'),
        'bisnis' => __('visitor.business_matters'),
        'ortu' => __('visitor.parent_guardian_visit'),
        'informasi_kampus' => __('visitor.campus_information'),
        'lainnya' => __('visitor.other_purposes'),
    ];
@endphp

@section('title', $tujuanMap[$tujuan])

@section('content')

    <div class="row">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center mt-5">
                <x-tamu.page-header :title="$tujuanMap[$tujuan] ?? 'Kunjungan'" />

                <div class="text-start mt-4">
                    <a href="{{ route('tamu.non-event.tujuan') }}" class="btn btn-link p-0 mb-2 gap-2 text-decoration-none"
                        style="color: var(--dark-color);">
                        <i class="fas fa-arrow-left"></i>
                        <span>{{ __('visitor.back') }}</span>
                    </a>
                </div>

                <form id="tamu-form" class="text-start wow fadeInUp" action="{{ route('tamu.non-event.store-presensi') }}"
                    method="POST" data-toggle="validator" novalidate>
                    @csrf
                    <input type="hidden" name="kategori_tujuan" value="{{ $tujuan }}">

                    @switch($tujuan)
                        @case('instansi')
                            @include('components.tamu.partials.instansi')
                        @break

                        @case('bisnis')
                            @include('components.tamu.partials.bisnis')
                        @break

                        @case('ortu')
                            @include('components.tamu.partials.ortu')
                        @break

                        @case('informasi_kampus')
                            @include('components.tamu.partials.calon-ortu')
                        @break

                        @case('lainnya')
                            @include('components.tamu.partials.lainnya')
                        @break
                    @endswitch

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
            const form = document.getElementById('tamu-form');
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
    </script>
@endsection
