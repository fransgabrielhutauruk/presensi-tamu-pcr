@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row min-vh-100 align-items-center">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="position-relative text-center" style="z-index: 10;">
                <!-- Error Messages -->
                @if (session('error'))
                <div class="alert alert-danger d-flex align-items-center mb-4 wow fadeInUp" data-wow-delay="0.3s">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
                @endif
                <x-card>
                    <h1 class="wow fadeInOut fs-3" data-wow-delay="0.7s">
                        Sistem Presensi Tamu
                    </h1>

                    <h5 class="wow fadeInOut" data-wow-delay="0.9s">
                        Politeknik Caltex Riau
                    </h5>

                    <div class="d-flex mx-auto justify-content-center mt-5">
                        <a href="{{ route('login.google', ['provider' => 'google']) }}"
                            class="btn-default w-100 d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="width: 20px; height: 20px;">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6 1.54 7.38 2.84l5.4-5.26C33.46 3.67 28.97 2 24 2 14.82 2 6.98 7.99 3.69 16.17l6.65 5.16C12.26 14.14 17.62 9.5 24 9.5z" />
                                <path fill="#4285F4" d="M46.5 24.5c0-1.64-.15-3.2-.43-4.71H24v9.02h12.7c-.55 2.83-2.23 5.22-4.74 6.82l7.66 5.93C43.9 37.33 46.5 31.36 46.5 24.5z" />
                                <path fill="#FBBC05" d="M10.34 28.31a14.5 14.5 0 0 1-.76-4.56c0-1.58.28-3.11.76-4.56l-6.65-5.16A23.86 23.86 0 0 0 2 23.75c0 3.86.92 7.51 2.55 10.72l6.66-5.16z" />
                                <path fill="#34A853" d="M24 47c6.48 0 11.9-2.13 15.87-5.81l-7.66-5.93C30.52 36.57 27.6 37.5 24 37.5c-6.38 0-11.74-4.64-13.66-10.97l-6.65 5.16C6.98 40.01 14.82 47 24 47z" />
                                <path fill="none" d="M2 2h44v44H2z" />
                            </svg>

                            <span>Login With Google</span>
                        </a>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            Gunakan akun email PCR (@pcr.ac.id) untuk masuk ke sistem
                        </small>
                    </div>
                </x-card>

            </div>
        </div>
    </div>
</div>

@endsection