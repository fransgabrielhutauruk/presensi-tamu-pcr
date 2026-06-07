@extends('layouts.tamu.main')

@section('title', __('visitor.thank_you'))

@section('content')
    @php
        $isEventVisit = $kunjungan->kategori_tujuan?->value === 'event';
        $visitorName = $kunjungan->tamu->nama_tamu ?? $kunjungan->civitas->nama_civitas;
        $visitTime = $kunjungan->created_at->format('d/m/Y H:i');
        $visitingParty = collect($kunjungan->details)->where('kunci', 'pihak_dituju')->first()['nilai'] ?? '-';
        $checkoutLabel = $isEventVisit ? __('visitor.checkout_now_event') : __('visitor.checkout_now');
        $checkoutRoute = route('tamu.checkout', encid($kunjungan->kunjungan_id));
    @endphp

    <div class="row justify-content-center align-items-center" style="min-height: 80vh">
        <div class="col-md-5 mt-3">
            <div class="text-center">
                <x-card class="wow fadeInUp py-2">
                    <h1 class="mt-3 lh-1 fs-2" data-cy="text-terima-kasih">{{ __('visitor.thank_you') }}!</h1>
                    <p class="mt-1 lh-sm text-body" data-cy="text-registrasi-berhasil">{{ __('visitor.registration_complete') }}</p>

                    <div class="alert alert-light mt-3 mb-3 text-start">
                        <p class="mb-0"><strong>{{ __('visitor.visitor_name') }}:</strong>
                            {{ $visitorName }}</p>
                        <p class="mb-0"><strong>{{ __('visitor.visit_time') }}:</strong>
                            {{ $visitTime }}</p>
                        @if (!$isEventVisit)
                            <p class="mb-0"><strong>{{ __('visitor.visiting_party') }}:</strong>
                                {{ $visitingParty }}
                            </p>
                        @else
                            <p class="mb-0"><strong>Event:</strong>
                                {{ $kunjungan->event->nama_event }}
                            </p>
                        @endif
                        <small class="text-muted lh-sm">
                            <i class="fas fa-exclamation-triangle me-1 mt-2"></i>
                            {{ __('visitor.checkout_reminder') }}
                        </small>
                    </div>

                    <a href="{{ $checkoutRoute }}" class="btn-default w-100 mt-2" id="route" data-cy="btn-checkout-sekarang">
                        <span id="beforeSubmit">{{ $checkoutLabel }}</span>
                        <span id="loadingIndicator" style="display: none;">
                            <i class="fas fa-spinner fa-spin me-2"></i>{{ __('common.processing') }}
                        </span>
                    </a>
                </x-card>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initRouteLoadingState();
            });

            function initRouteLoadingState() {
                const routeLink = document.getElementById('route');
                const beforeSubmit = document.getElementById('beforeSubmit');
                const loadingIndicator = document.getElementById('loadingIndicator');

                if (!routeLink || !beforeSubmit || !loadingIndicator) {
                    return;
                }

                routeLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    beforeSubmit.style.display = 'none';
                    loadingIndicator.style.display = 'inline';

                    routeLink.style.pointerEvents = 'none';

                    window.location.href = this.getAttribute('href');
                });
            }
        </script>
    @endsection
