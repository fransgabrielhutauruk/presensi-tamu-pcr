@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')

@php
    use Illuminate\Support\Str;
@endphp

@section('toolbar')
    <x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
        <x-slot:tools>
            <x-theme.back link="{{ route('app.event.index') }}" />
            <x-btn type="button" class="btn btn-success" onclick="printQrCode()">
                <i class="bi bi-printer fs-4 me-1"></i> Cetak QR Code
            </x-btn>
            <x-btn type="button" class="btn btn-primary" onclick="downloadQrCode()">
                <i class="bi bi-download fs-4 me-1"></i> Download QR Code
            </x-btn>
        </x-slot:tools>
    </x-theme.toolbar>


    <script>
        function printQrCode() {
            const svg = document.querySelector('#qr-code svg');
            if (!svg) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'QR Code tidak ditemukan'
                });
                return;
            }

            // Create print content
            const eventName = @json($pageData->event->nama_event);
            const presensiUrl = @json($pageData->presensiUrl);
            
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>QR Code - ${eventName}</title>
                    <style>
                        @page {
                            size: A4;
                            margin: 1cm;
                        }
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            margin: 0;
                            padding: 0;
                            background: white;
                            color: #333;
                        }
                        .print-container {
                            max-width: 100%;
                            margin: 0 auto;
                            padding: 1cm;
                            border-radius: 15px;
                            background: white;
                            height: calc(100vh - 2cm);
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                        }
                        .print-header {
                            text-align: center;
                            margin-bottom: 1cm;
                        }
                        .main-title {
                            font-size: 36px;
                            font-weight: bold;
                            color: #000;
                            margin-bottom: 0.3cm;
                            letter-spacing: 6px;
                            text-transform: uppercase;
                        }
                        .event-title {
                            font-size: 20px;
                            font-weight: 600;
                            color: #333;
                            margin-bottom: 0.2cm;
                            line-height: 1.2;
                        }
                        .content-middle {
                            flex: 1;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                        }
                        .qr-container {
                            text-align: center;
                            margin: 0.5cm 0;
                        }
                        .qr-code {
                            width: 7cm;
                            height: 7cm;
                            margin: 0 auto;
                            border: 3px solid #000;
                            border-radius: 10px;
                            padding: 0.5cm;
                            background: white;
                        }
                        .qr-code svg {
                            width: 100%;
                            height: 100%;
                        }
                        .url-container {
                            margin-top: 0.8cm;
                            text-align: center;
                        }
                        .url-label {
                            font-size: 16px;
                            font-weight: 600;
                            color: #333;
                            margin-bottom: 0.3cm;
                        }
                        .url-box {
                            background: #f8f9fa;
                            border: 2px solid #000;
                            border-radius: 8px;
                            padding: 10px 15px;
                            margin: 0 auto;
                            max-width: 14cm;
                            word-break: break-all;
                            font-family: 'Courier New', monospace;
                            font-size: 12px;
                            color: #333;
                        }
                        .instructions {
                            padding: 0.8cm;
                            border-radius: 10px;
                            border: 2px solid #000;
                        }
                        .instructions-title {
                            font-size: 16px;
                            font-weight: 600;
                            color: #333;
                            text-align: center;
                            margin-bottom: 0.6cm;
                        }
                        .instruction-grid {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 0.6cm;
                        }
                        .instruction-item {
                            display: flex;
                            align-items: flex-start;
                            margin-bottom: 0.4cm;
                        }
                        .instruction-number {
                            background: #000;
                            color: white;
                            width: 24px;
                            height: 24px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-weight: bold;
                            font-size: 12px;
                            margin-right: 10px;
                            flex-shrink: 0;
                        }
                        .instruction-text {
                            font-size: 12px;
                            line-height: 1.3;
                            color: #555;
                        }
                        @media print {
                            body { 
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-container">
                        <div class="print-header">
                            <div class="main-title">PRESENSI</div>
                            <div class="event-title">{nama_event}</div>
                        </div>
                        
                        <div class="content-middle">
                            <div class="qr-container">
                                <div class="qr-code">
                                    ${svg.outerHTML}
                                </div>
                            </div>
                            
                            <div class="url-container">
                                <div class="url-label">Link Presensi</div>
                                <div class="url-box">{url}</div>
                            </div>
                        </div>
                        
                        <div class="instructions">
                            <div class="instructions-title">Instruksi Penggunaan:</div>
                            <div class="instruction-grid">
                                <div class="instruction-item">
                                    <div class="instruction-number">1</div>
                                    <div class="instruction-text">Scan QR Code menggunakan kamera HP</div>
                                </div>
                                <div class="instruction-item">
                                    <div class="instruction-number">2</div>
                                    <div class="instruction-text">Atau buka link presensi secara manual</div>
                                </div>
                                <div class="instruction-item">
                                    <div class="instruction-number">3</div>
                                    <div class="instruction-text">Isi form presensi</div>
                                </div>
                                <div class="instruction-item">
                                    <div class="instruction-number">4</div>
                                    <div class="instruction-text">Data presensi tersimpan di sistem</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            `.replace('{nama_event}', eventName).replace('{url}', presensiUrl);

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            };
        }

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
            <div class="col-lg-10 my-3 py-3">
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
                            @if ($pageData->event->waktu_mulai_event || $pageData->event->waktu_selesai_event)
                                <div class="text-muted mb-1">
                                    <i class="bi bi-clock"></i>
                                    {{ $pageData->event->waktu_mulai_event ? date('H:i', strtotime($pageData->event->waktu_mulai_event)) : '-' }}
                                    @if ($pageData->event->waktu_selesai_event)
                                        - {{ date('H:i', strtotime($pageData->event->waktu_selesai_event)) }}
                                    @endif
                                </div>
                            @endif
                            @if ($pageData->event->lokasi_event)
                                <div class="text-muted mb-1">
                                    <i class="bi bi-geo-alt"></i> {{ $pageData->event->lokasi_event }}
                                </div>
                            @endif
                        </div>

                        <div class="mb-5" id="qr-container">
                            <div class="border rounded p-4 bg-white d-inline-block">
                                <div id="qr-code"
                                    style="width: 250px; height: 250px; display: flex; align-items: center; justify-content: center;">
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
                                <input type="text" class="form-control" id="presensi-url"
                                    value="{{ $pageData->presensiUrl }}" readonly>
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
