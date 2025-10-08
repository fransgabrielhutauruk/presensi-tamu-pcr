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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
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
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="fas fa-sign-out-alt fa-4x text-primary mb-3"></i>
                            <h2 class="text-dark mb-2">Konfirmasi Checkout</h2>
                            <p class="text-muted">Apakah Anda yakin ingin melakukan checkout?</p>
                        </div>

                        <div class="detail-item text-start mb-4">
                            <h5 class="mb-3"><i class="fas fa-user me-2"></i>Detail Kunjungan</h5>
                            <div class="row">
                                <div class="col-sm-4"><strong>Nama:</strong></div>
                                <div class="col-sm-8">{{ $kunjungan->tamu->nama_lengkap ?? $kunjungan->tamu->name }}</div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-4"><strong>Kategori:</strong></div>
                                <div class="col-sm-8">{{ $kunjungan->kategori_tujuan }}</div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-4"><strong>Transportasi:</strong></div>
                                <div class="col-sm-8">{{ $kunjungan->transportasi }}</div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-4"><strong>Waktu Masuk:</strong></div>
                                <div class="col-sm-8">{{ $kunjungan->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('tamu.checkout', $kunjungan->kunjungan_id) }}">
                            @csrf
                            <div class="d-grid gap-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check me-2"></i>
                                    Ya, Checkout Sekarang
                                </button>
                                <a href="javascript:history.back()" class="btn btn-secondary btn-lg">
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