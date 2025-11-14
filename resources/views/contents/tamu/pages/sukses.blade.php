@extends('layouts.tamu.main')

@section('content')
    <div class="container">
        <div class="row min-vh-100 justify-content-center align-items-center">
            <div class="col-md-5">
                <div class="text-center">
                    <x-card class="wow fadeInUp py-2">
                        <img src="{{ asset('theme/images/akreditasi-unggul.webp') }}" alt="Logo Akreditasi Unggul"
                            class="mx-auto d-block mb-3 img-fluid" style="width: 30%;" />
                        <h1 class="mt-3 lh-1 fs-2">{{ __('visitor.thank_you') }}!</h1>
                        <p class="mt-1 lh-sm text-body">{{ __('visitor.registration_complete') }}</p>

                        <div class="alert alert-light mt-3 mb-3 text-start">
                            <p class="mb-0"><strong>{{ __('visitor.visitor_name') }}:</strong> {{ $kunjungan->tamu->nama_tamu }}</p>
                            <p class="mb-0"><strong>{{ __('visitor.visit_time') }}:</strong>
                                {{ $kunjungan->created_at->format('d/m/Y H:i') }}</p>
                            @if ($kunjungan->kategori_tujuan?->value != 'event')
                                <p class="mb-0"><strong>{{ __('visitor.visiting_party') }}:</strong>
                                    {{ collect($kunjungan->details)->where('kunci', 'pihak_dituju')->first()['nilai'] ?? '-' }}
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

                        <a href="{{ route('tamu.checkout', encid($kunjungan->kunjungan_id)) }}"
                            class="btn-default w-100 mt-2" id="route">
                            <span id="beforeSubmit">{{ $kunjungan->kategori_tujuan?->value == 'event' ? __('visitor.checkout_now_event') : __('visitor.checkout_now') }}</span>
                            <span id="loadingIndicator" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>{{ __('common.processing') }}
                            </span>
                        </a>
                    </x-card>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const routeLink = document.querySelector('#route');

                const beforeSubmit = document.querySelector('#beforeSubmit');
                const loadingIndicator = document.querySelector('#loadingIndicator');

                if (routeLink) {
                    routeLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        beforeSubmit.style.display = 'none';
                        loadingIndicator.style.display = 'inline';

                        routeLink.style.pointerEvents = 'none';

                        window.location.href = this.getAttribute('href');
                    });
                }
            });
        </script>
    @endsection
