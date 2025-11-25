@extends('layouts.tamu.main')




@php
    $message = $exception->getMessage() ?: 'Terlalu Banyak Permintaan.';
@endphp

@section('title', 'Terlalu Banyak Permintaan')

@breadcrumbs(['Terlalu Banyak Permintaan', '#'])

@section('content')
    <div class="row align-items-center" style="min-height: 90vh">
        <div class="error-page">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="error-page-content">
                            <div class="section-title">
                                <h2 class="wow fadeInUp" data-wow-delay="0.25s">Oops!<span> {{ $message }}</span>
                                </h2>
                            </div>
                            <div class="error-page-content-body">
                                <p class="wow fadeInUp" data-wow-delay="0.5s">
                                    Maaf, Anda telah melakukan terlalu banyak permintaan dalam waktu singkat.
                                    Silakan tunggu beberapa saat dan coba lagi.
                                </p>
                                <p class="wow fadeInUp" data-wow-delay="0.65s">
                                    <strong>Tunggu 1-2 menit sebelum mencoba kembali.</strong>
                                </p>
                                <a class="btn-default wow fadeInUp" data-wow-delay="0.75s" href="javascript:void(0);"
                                    onclick="location.reload()">
                                    Muat Ulang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
