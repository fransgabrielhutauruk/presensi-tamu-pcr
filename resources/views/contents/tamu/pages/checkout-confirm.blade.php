<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Checkout - PCR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background:
                radial-gradient(circle at 22% 28%, rgba(191, 219, 254, 0.7) 0%, rgba(191, 219, 254, 0.35) 18%, rgba(191, 219, 254, 0.08) 42%, rgba(191, 219, 254, 0) 65%),
                linear-gradient(135deg, rgba(59, 130, 246, 0.10) 0%, rgba(99, 102, 241, 0.08) 37%, rgba(56, 189, 248, 0.07) 63%, rgba(14, 165, 233, 0.05) 85%),
                linear-gradient(to bottom, #f0faff 0%, #e0f2fe 55%, #ffffff 90%);
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(45deg, #004b5f, #004b5f);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-secondary {
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
        }

        .detail-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-body py-5 px-3 text-center">
                        <img
                            src="{{ asset('theme/images/akreditasi-unggul.webp') }}"
                            alt="Logo Akreditasi Unggul"
                            class="mx-auto d-block w-25 mb-3 img-fluid" />

                        <div class="mb-4">
                            <h2 class="text-dark mb-2">Konfirmasi Checkout</h2>
                            <p class="text-muted">Apakah Anda yakin ingin melakukan checkout?</p>
                        </div>

                        <div class="detail-item text-start mb-4 py-4">
                            <h5 class="mb-3 fs-6"><i class="fas fa-user me-2"></i>Detail Kunjungan</h5>
                            <div class="row">
                                <div class="col-sm-4"><strong>Nama:</strong></div>
                                <div class="col-sm-8">{{ $kunjungan->tamu->nama_lengkap ?? $kunjungan->tamu->name }}</div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-4"><strong>Waktu Masuk:</strong></div>
                                <div class="col-sm-8">{{ $kunjungan->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('tamu.checkout', $kunjungan->kunjungan_id) }}">
                            @csrf
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>
                                    Ya, Checkout Sekarang
                                </button>
                                <a href="javascript:history.back()" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Batal
                                </a>
                            </div>
                        </form>

                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Checkout akan dicatat secara otomatis
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>