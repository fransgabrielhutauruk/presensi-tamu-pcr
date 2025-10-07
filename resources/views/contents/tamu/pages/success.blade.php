@extends('layouts.tamu.main')

@section('header')
<div></div>
@endsection

@section('content')
<div class="container">
    <div class="row min-vh-100 justify-content-center align-items-center">
        <div class="col-md-5">
            <div class="text-center">
                <x-card class="wow fadeInUp py-2" data-wow-delay="0.5s">
                    <img
                        src="{{ asset('theme/images/akreditasi-unggul.webp') }}"
                        alt="Logo Akreditasi Unggul"
                        class="mx-auto d-block mb-3 w-25 img-fluid" />
                    <h1 class="mt-3 mb-0 lh-1 fs-2">Terima Kasih!</h1>
                    <p class="lead my-0">Presensi kunjungan Anda telah berhasil disimpan.</p>
                    <a href="{{ route('tamu.home') }}" class="btn-default w-100 mt-3">Kembali ke Home</a>
                </x-card>
            </div>
        </div>
    </div>
    @endsection