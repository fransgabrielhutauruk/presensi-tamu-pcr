@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')

@php
use Illuminate\Support\Str;
@endphp

@section('toolbar')
<x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
    <x-slot:tools>
        <x-theme.back link="{{ route('app.event.index') }}" />
        <x-btn type="button" class="btn btn-primary" onclick="downloadQrCode()">
            <i class="bi bi-download fs-4 me-1"></i> Download QR Code
        </x-btn>
    </x-slot:tools>
</x-theme.toolbar>


<script>
    function downloadQrCode() {
        const svg = document.querySelector('#qr-code svg');
        if (!svg) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'QR Code tidak ditemukan'
            });
            return;
        }

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 1024;
        canvas.height = 1024;

        const data = (new XMLSerializer()).serializeToString(svg);
        const img = new Image();

        img.onload = function() {
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.drawImage(img, 0, 0, 1024, 1024);

            const link = document.createElement('a');
            link.download = 'qr-code-{{ Str::slug($pageData->event->nama_event) }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        };

        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(data)));
    }

    function copyUrl() {
        const urlInput = document.getElementById('presensi-url');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(urlInput.value).then(function() {
            Swal.fire({
                icon: 'success',
                text: 'Link presensi berhasil disalin',
                timer: 1500,
                showConfirmButton: false
            });
        }).catch(function() {
            document.execCommand('copy');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Link presensi berhasil disalin',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }
</script>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 my-3 py-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-5">
                        <h4 class="text-primary mb-2">{{ $pageData->event->nama_event }}</h4>
                        <div class="text-muted mb-1">
                            <i class="bi bi-tag"></i> {{ $pageData->event->eventKategori->nama_kategori ?? '-' }}
                        </div>
                        <div class="text-muted mb-1">
                            <i class="bi bi-calendar"></i>
                            {{ $pageData->event->tanggal_event ? date('d F Y', strtotime($pageData->event->tanggal_event)) : '-' }}
                        </div>
                        @if($pageData->event->waktu_mulai_event || $pageData->event->waktu_selesai_event)
                        <div class="text-muted mb-1">
                            <i class="bi bi-clock"></i>
                            {{ $pageData->event->waktu_mulai_event ? date('H:i', strtotime($pageData->event->waktu_mulai_event)) : '-' }}
                            @if($pageData->event->waktu_selesai_event)
                            - {{ date('H:i', strtotime($pageData->event->waktu_selesai_event)) }}
                            @endif
                        </div>
                        @endif
                        @if($pageData->event->lokasi_event)
                        <div class="text-muted mb-1">
                            <i class="bi bi-geo-alt"></i> {{ $pageData->event->lokasi_event }}
                        </div>
                        @endif
                    </div>

                    <div class="mb-5" id="qr-container">
                        <div class="border rounded p-4 bg-white d-inline-block">
                            <div id="qr-code" style="width: 250px; height: 250px; display: flex; align-items: center; justify-content: center;">
                                {!! $pageData->qrCodeSvg !!}
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">Scan QR Code untuk Presensi Event</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light">
                        <h6 class="mb-2">Link Presensi:</h6>
                        <div class="input-group">
                            <input type="text" class="form-control" id="presensi-url" value="{{ $pageData->presensiUrl }}" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="copyUrl()">
                                <i class="bi bi-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="mb-3">Instruksi Penggunaan:</h6>
                        <div class="row text-start">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-1-circle text-primary me-2"></i>
                                        Tamu scan QR Code menggunakan kamera HP
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-2-circle text-primary me-2"></i>
                                        Atau buka link presensi secara manual
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-3-circle text-primary me-2"></i>
                                        Isi form presensi event dengan lengkap
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-4-circle text-primary me-2"></i>
                                        Data presensi tersimpan di sistem
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('script')

@endsection