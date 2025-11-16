@extends('layouts.tamu.main')




@php
    $message = $exception->getMessage() ?: 'Halaman Tidak Ditemukan.';
@endphp

@section('title', 'Halaman Tidak Ditemukan')

@breadcrumbs(['Halaman Tidak Ditemukan', '#'])

@section('content')
    <div class="container">
        <div class="row min-vh-100 align-items-center">
            <div class="error-page">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="error-page-image wow fadeInUp">
                                <img src="images/404-error-img.png" alt="">
                            </div>
                            <div class="error-page-content">
                                <div class="section-title">
                                    <h2 class="wow fadeInUp" data-wow-delay="0.25s">Oops!<span> {{ $message }}</span>
                                    </h2>
                                </div>
                                <div class="error-page-content-body">
                                    <p class="wow fadeInUp" data-wow-delay="0.5s">
                                        Maaf, halaman yang Anda cari tidak ditemukan. Halaman ini mungkin telah dihapus,
                                        dipindahkan, atau tidak tersedia lagi.
                                    </p>
                                    <a class="btn-default wow fadeInUp" data-wow-delay="0.75s"
                                        href="{{ route('tamu.home') }}">
                                        Kembali ke Beranda
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
