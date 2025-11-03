@extends('layouts.tamu.main')

@section('content')
    <div class="container">
        <div class="row min-vh-100 align-items-center">
            <div class="col-md-5 justify-content-center mx-auto">
                <div class="card py-5 px-4 text-center wow fadeInUp">
                    <img src="{{ asset('theme/images/akreditasi-unggul.webp') }}" alt="Logo Akreditasi Unggul"
                        class="img-fluid mx-auto" style="width:30%" />

                    <div class="mt-2">
                        <h1 class="fs-2">{{ __('visitor.checkout_confirmation') }}</h1>
                        <p class="text-muted lh-sm">{{ __('visitor.checkout_message') }}</p>
                    </div>

                    <div class="alert alert-light text-start mb-4">
                        <p class="mb-0"><strong>{{ __('visitor.visitor_name') }}:</strong> {{ $kunjungan->tamu->nama_tamu }}</p>
                        <p class="mb-0"><strong>{{ __('visitor.visit_time') }}:</strong>
                            {{ $kunjungan->created_at->format('d/m/Y H:i') }}</p>
                        @if ($kunjungan->kategori_tujuan != 'event')
                            <p class="mb-0"><strong>{{ __('visitor.visit_purpose') }}:</strong>
                                {{ collect($kunjungan->details)->where('kunci', 'pihak_dituju')->first()['nilai'] ?? '-' }}
                            </p>
                        @else
                            <p class="mb-0"><strong>Event:</strong>
                                {{ $kunjungan->event->nama_event }}
                            </p>
                        @endif
                    </div>

                    <form method="POST" id="formCheckout"
                        action="{{ route('tamu.checkout-store', encid($kunjungan->kunjungan_id)) }}">
                        @csrf
                        <button type="submit" id="submitBtn" class="btn btn-default w-100 mt-2">
                            <span id="beforeSubmit">{{ __('visitor.confirm_checkout') }}</span>
                            <span id="loadingIndicator" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('common.processing') }}
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formCheckout = document.querySelector('#formCheckout');
            const submitBtn = document.querySelector('#submitBtn');
            const beforeSubmit = document.querySelector('#beforeSubmit');
            const loadingIndicator = document.querySelector('#loadingIndicator');

            if (formCheckout) {
                formCheckout.addEventListener('submit', function(e) {
                    beforeSubmit.style.display = 'none';
                    loadingIndicator.style.display = 'inline';
                    submitBtn.disabled = true;
                });

            }
        });
    </script>
@endsection
