@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')

@php
    use Illuminate\Support\Str;

    $event = $pageData->event;
    $eventDate = $event->formatted_date_range;
    $eventTime = null;
    $eventLocation = $event->lokasi_event ?? null;

    if ($event->waktu_mulai_event || $event->waktu_selesai_event) {
        $eventTime = $event->waktu_mulai_event ? date('H:i', strtotime($event->waktu_mulai_event)) : '-';

        if ($event->waktu_selesai_event) {
            $eventTime .= ' - ' . date('H:i', strtotime($event->waktu_selesai_event));
        }
    }

    $qrCodeFileName = 'qr-code-' . Str::slug($event->nama_event) . '.png';
@endphp

@section('toolbar')
    <x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
        <x-slot:tools>
            <x-theme.back link="{{ route('app.event.index') }}" />

            <x-btn type="button" class="btn btn-success" onclick="printQrCode()">
                <i class="bi bi-printer fs-4 me-sm-1"></i>
                <span class="d-none d-sm-inline">Cetak QR Code</span>
            </x-btn>

            <x-btn type="button" class="btn btn-primary" onclick="downloadQrCode()">
                <i class="bi bi-download fs-4 me-sm-1"></i>
                <span class="d-none d-sm-inline">Download QR Code</span>
            </x-btn>
        </x-slot:tools>
    </x-theme.toolbar>

    <script>
        const eventName = @json($event->nama_event);
        const presensiUrl = @json($pageData->presensiUrl);
        const qrCodeFileName = @json($qrCodeFileName);

        function getQrSvgOrShowError() {
            const svg = document.querySelector('#qr-code svg');

            if (!svg) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'QR Code tidak ditemukan'
                });

                return null;
            }

            return svg;
        }

        function printQrCode() {
            const svg = getQrSvgOrShowError();

            if (!svg) {
                return;
            }

            // 1. Ambil seluruh elemen HTML yang ada di dalam card
            const cardContainer = document.getElementById('qr-card-container');
            const cardHTML = cardContainer.innerHTML;

            // 2. Buat template HTML khusus untuk jendela Print
            const printContent = `
                <!DOCTYPE html>
                <html lang="id">
                <head>
                    <title>Presensi - ${eventName}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
                    <style>
                        /* Optimasi Kertas A4 */
                        @page {
                            size: A4 portrait;
                            margin: 1cm; /* Margin diperkecil agar ruang lebih lega */
                        }
                        
                        body {
                            font-family: system-ui, -apple-system, sans-serif;
                            background-color: #ffffff;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                            color: #181c32;
                        }
                        
                        /* --- Polyfill Warna Metronic --- */
                        .text-primary { color: #009ef7 !important; }
                        .bg-light { background-color: #f5f8fa !important; }
                        .text-gray-600 { color: #7e8299 !important; }
                        .text-gray-700 { color: #5e6278 !important; }
                        .border-gray-200 { border-color: #eff2f5 !important; }
                        
                        /* SEMBUNYIKAN SEMUA TOMBOL SAAT DICETAK */
                        button, .btn { display: none !important; }

                        .input-group .form-control {
                            border-radius: 0.5rem !important;
                            border: 1px solid #e4e6ef;
                            text-align: center;
                            font-weight: 600;
                        }

                        .badge-circle {
                            width: 25px;
                            height: 25px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 50% !important;
                            padding: 0;
                            background-color: #009ef7 !important;
                            color: #ffffff !important;
                            font-weight: bold;
                        }

                        /* --- PERBAIKAN LAYOUT PRINT (AGAR MUAT 1 HALAMAN) --- */
                        
                        /* 1. Timpa spasi bawaan Bootstrap yang terlalu besar untuk kertas fisik */
                        .mb-5 { margin-bottom: 1.5rem !important; }
                        .py-5 { padding-top: 1.5rem !important; padding-bottom: 1.5rem !important; }
                        .p-5 { padding: 1.5rem !important; }
                        .p-md-5 { padding: 1.5rem !important; }
                        .mt-4 { margin-top: 1rem !important; }
                        .gap-4 { gap: 1rem !important; }
                        
                        /* 2. Hindari elemen kotak instruksi terpotong di tengah halaman */
                        .bg-light { 
                            page-break-inside: avoid; 
                            break-inside: avoid; 
                        }
                        
                        /* 3. Perkecil sedikit ukuran font dasar */
                        html, body {
                            font-size: 14px;
                        }
                        
                        /* 4. Pusatkan konten tepat di tengah kertas */
                        .print-wrapper {
                            padding-top: 0;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            min-height: 100vh;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-wrapper container">
                        ${cardHTML}
                    </div>
                    <script>
                        window.onload = function() {
                            setTimeout(function() {
                                window.print();
                            }, 800);
                        };
                        
                        window.onafterprint = function() {
                            window.close();
                        };
                    <\/script>
                </body>
                </html>
            `;

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Popup Terblokir',
                    text: 'Browser Anda memblokir popup. Izinkan popup untuk mencetak QR Code.'
                });
                return;
            }

            printWindow.document.write(printContent);
            printWindow.document.close();
        }

        function downloadQrCode() {
            const svg = getQrSvgOrShowError();

            if (!svg) {
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
                link.download = qrCodeFileName;
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

        function toggleFullscreen() {
            const elem = document.getElementById("qr-card-container");
            const icon = document.getElementById("fullscreen-icon");

            if (!document.fullscreenElement) {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                }
                icon.classList.remove('bi-arrows-fullscreen');
                icon.classList.add('bi-fullscreen-exit');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                icon.classList.remove('bi-fullscreen-exit');
                icon.classList.add('bi-arrows-fullscreen');
            }
        }

        document.addEventListener('fullscreenchange', (event) => {
            if (!document.fullscreenElement) {
                const icon = document.getElementById("fullscreen-icon");
                icon.classList.remove('bi-fullscreen-exit');
                icon.classList.add('bi-arrows-fullscreen');
            }
        });
    </script>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10 my-4">
                <div class="card shadow-sm border-0" id="qr-card-container">
                    <div class="card-body text-center position-relative py-5">

                        <button type="button"
                            class="btn btn-icon btn-sm btn-light-primary position-absolute top-0 end-0 m-4 z-index-1"
                            onclick="toggleFullscreen()" data-bs-toggle="tooltip" title="Tampilkan Fullscreen">
                            <i class="bi bi-arrows-fullscreen fs-4" id="fullscreen-icon"></i>
                        </button>

                        <div class="mb-5" data-cy="event-detail-info">
                            <h2 class="text-primary fw-bolder mb-4" data-cy="text-event-title"
                                style="letter-spacing: -0.5px;">
                                {{ $pageData->event->nama_event }}
                            </h2>

                            <div
                                class="d-flex flex-wrap justify-content-center align-items-center gap-4 fs-6 fw-semibold text-gray-600">
                                <span class="d-flex align-items-center">
                                    <i class="bi bi-calendar text-primary me-2 fs-5"></i> {{ $eventDate }}
                                </span>
                                @if ($eventTime)
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-clock text-primary me-2 fs-5"></i> {{ $eventTime }}
                                    </span>
                                @endif
                                @if ($eventLocation)
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt text-primary me-2 fs-5"></i> {{ $eventLocation }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-5 mt-2" id="qr-container" data-cy="qr-event-container">
                            <div class="border border-gray-200 shadow-sm rounded-4 p-5 bg-white d-inline-block">
                                <div id="qr-code" data-cy="qr-event-code"
                                    class="d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 280px; height: 280px;"> {!! $pageData->qrCodeSvg !!}
                                </div>
                                <div class="mt-4 pt-3 border-top border-gray-200">
                                    <span class="text-dark fw-bold fs-6">Scan QR Code untuk Presensi</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5 mx-auto col-12 col-md-10 col-xl-7" style="max-width: 750px;"
                            data-cy="event-presensi-link-container">
                            <label class="form-label fw-bold text-gray-700 mb-2">Atau akses link alternatif:</label>
                            <div class="input-group shadow-sm">
                                <input type="text" class="form-control bg-light" id="presensi-url"
                                    value="{{ $pageData->presensiUrl }}" readonly data-cy="input-event-presensi-link">

                                <button class="btn btn-primary px-4" type="button" onclick="copyUrl()"
                                    data-bs-toggle="tooltip" title="Salin Link">
                                    <i class="ki-duotone ki-copy fs-5 text-white"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mx-auto col-12 col-md-10 col-xl-7  bg-light rounded-4 p-4 p-md-5 text-start">
                            <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-info-circle me-2"></i>Instruksi Penggunaan:
                            </h6>
                            <div class="row g-4">
                                <div class="col-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-flex align-items-start mb-3">
                                            <span class="badge badge-primary badge-circle flex-shrink-0 me-3">1</span>
                                            <span class="text-gray-700">Scan QR Code menggunakan kamera HP</span>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            <span class="badge badge-primary badge-circle flex-shrink-0 me-3">2</span>
                                            <span class="text-gray-700">Atau buka link presensi di atas secara manual
                                                melalui browser</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-flex align-items-start mb-3">
                                            <span class="badge badge-primary badge-circle flex-shrink-0 me-3">3</span>
                                            <span class="text-gray-700">Isi formulir presensi dengan lengkap</span>
                                        </li>
                                        <li class="d-flex align-items-start">
                                            <span class="badge badge-primary badge-circle flex-shrink-0 me-3">4</span>
                                            <span class="text-gray-700">Kirim formulir presensi dan data berhasil
                                                tersimpan</span>
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

    <style>
        #qr-card-container:fullscreen {
            background-color: #ffffff !important;
            overflow-y: auto;
        }

        #qr-card-container:fullscreen .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        #qr-card-container:-webkit-full-screen {
            background-color: #ffffff !important;
            overflow-y: auto;
        }

        #qr-card-container:-webkit-full-screen .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>
@endsection
