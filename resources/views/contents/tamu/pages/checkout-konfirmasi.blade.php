@extends('layouts.tamu.main')

@section('content')
    <div class="container">
        <div class="row min-vh-100 align-items-center">
            <div class="col-md-5 justify-content-center mx-auto">
                <div class="card py-5 px-4 text-center wow fadeInUp">
                    <img src="{{ asset('theme/images/akreditasi-unggul.webp') }}" alt="Logo Akreditasi Unggul"
                        class="img-fluid mx-auto" style="width:30%" />

                    <div class="mt-2">
                        <h1 class="fs-2">Konfirmasi Checkout</h1>
                        <p class="text-muted lh-sm">Apakah Anda yakin ingin melakukan checkout?</p>
                    </div>

                    <div class="detail-item text-start mb-4">
                        <h5 class="fs-6"><i class="fas fa-user me-2"></i>Detail Kunjungan</h5>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>Nama:</strong></div>
                            <div class="col-sm-8">{{ $kunjungan->tamu->nama_tamu }}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>Waktu Masuk:</strong></div>
                            <div class="col-sm-8">{{ $kunjungan->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    <form method="POST" id="formCheckout"
                        action="{{ route('tamu.checkout-store', encid($kunjungan->kunjungan_id)) }}">
                        @csrf
                        <button type="submit" id="submitBtn" class="btn btn-default w-100 mt-2">
                            <span id="beforeSubmit">Ya, Checkout Sekarang</span>
                            <span id="loadingIndicator" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>Memproses...
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
