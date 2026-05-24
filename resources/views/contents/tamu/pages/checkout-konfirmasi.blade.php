@extends('layouts.tamu.main')

@section('title', __('visitor.checkout_confirmation'))

@section('content')
    @php
        $isEventVisit = $kunjungan->kategori_tujuan?->value === 'event';
        $visitorName = $kunjungan->tamu->nama_tamu ?? $kunjungan->civitas->nama_civitas;
        $visitTime = $kunjungan->created_at->format('d/m/Y H:i');
        $visitingParty = collect($kunjungan->details)->where('kunci', 'pihak_dituju')->first()['nilai'] ?? '-';
        $checkoutRoute = route('tamu.checkout-store', encid($kunjungan->kunjungan_id));
    @endphp

    <div class="row align-items-center" style="min-height: 90vh">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="card py-5 px-4 text-center wow fadeInUp" data-cy="card-checkout-konfirmasi">
                <img src="{{ asset('theme/images/akreditasi-unggul.webp') }}" alt="Logo Akreditasi Unggul"
                    class="img-fluid mx-auto" style="width:30%" />

                <div class="mt-2">
                    <h1 class="fs-2" data-cy="text-checkout-konfirmasi">{{ __('visitor.checkout_confirmation') }}</h1>
                    <p class="text-muted lh-sm" data-cy="text-checkout-message">{{ __('visitor.checkout_message') }}</p>
                </div>

                <div class="alert alert-light text-start mb-4" data-cy="checkout-ringkasan-kunjungan">
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
                </div>

                <form method="POST" id="formCheckout" action="{{ $checkoutRoute }}" data-cy="form-checkout">
                    @csrf
                    <button type="submit" id="submitBtn" class="btn btn-default w-100 mt-2" data-cy="btn-konfirmasi-checkout">
                        <span id="beforeSubmit">{{ __('visitor.confirm_checkout') }}</span>
                        <span id="loadingIndicator" style="display: none;">
                            <i class="fas fa-spinner fa-spin me-2"></i>{{ __('common.processing') }}
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initCheckoutSubmitLoadingState();
        });

        function initCheckoutSubmitLoadingState() {
            const formCheckout = document.getElementById('formCheckout');
            const submitBtn = document.getElementById('submitBtn');
            const beforeSubmit = document.getElementById('beforeSubmit');
            const loadingIndicator = document.getElementById('loadingIndicator');

            if (!formCheckout || !submitBtn || !beforeSubmit || !loadingIndicator) {
                return;
            }

            formCheckout.addEventListener('submit', function() {
                beforeSubmit.style.display = 'none';
                loadingIndicator.style.display = 'inline';
                submitBtn.disabled = true;
            }
            );
        }
    </script>
@endsection
