@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')

@section('toolbar')
<x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
    <x-slot:tools>
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-light-primary fs-7">
                <i class="ki-outline ki-calendar fs-7 me-1"></i>
                {{ $pageData->tanggalHariIni }}
            </span>
        </div>
    </x-slot:tools>
</x-theme.toolbar>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-fluid" data-cue="slideInLeft" data-duration="1000"
    data-delay="0">

    <div class="row g-5 g-xl-10 mb-5">
        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush mb-5">
                <div class="card-header py-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $pageData->totalKunjunganHariIni }}</span>
                        </div>
                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Kunjungan Hari Ini</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush mb-5">
                <div class="card-header py-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2">{{ $pageData->kunjunganTervalidasi }}</span>
                        </div>
                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Tervalidasi</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush h mb-5">
                <div class="card-header py-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2">{{ $pageData->kunjunganBelumValidasi }}</span>
                        </div>
                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Belum Validasi</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush mb-5">
                <div class="card-header py-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2">{{ $pageData->kunjunganSudahCheckout }}</span>
                        </div>
                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Sudah Checkout</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Info -->
    <!-- <div class="alert alert-info d-flex align-items-center mb-5">
        <i class="ki-outline ki-information fs-1 text-info me-4"></i>
        <div>
            <h5 class="mb-1">Monitoring Kunjungan Real-Time</h5>
            <p class="mb-0">Halaman ini menampilkan semua kunjungan yang terjadi pada hari ini ({{ $pageData->tanggalHariIni }}) diurutkan berdasarkan waktu kunjungan terbaru dan status validasi.</p>
        </div>
    </div> -->

    <!-- Data Table -->
    <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="monitoring-kunjungan" jf-list="datatable">
        @slot('action')
        <!-- <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light-primary btn-sm" onclick="location.reload();">
                <i class="ki-outline ki-arrows-circle fs-4"></i> Refresh Data
            </button>
            @if($pageData->kunjunganBelumValidasi > 0)
            <a href="{{ route('app.kunjungan.validasi') }}" class="btn btn-warning btn-sm">
                <i class="ki-outline ki-time fs-4"></i> {{ $pageData->kunjunganBelumValidasi }} Menunggu Validasi
            </a>
            @endif
        </div> -->
        @endslot
    </x-table.dttable>
</div>

<!-- Modal Detail (reuse from kunjungan) -->
<x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="monitoring-kunjungan" title="Detail Kunjungan">
    <!-- Data Tamu -->
    <div class="mb-7">
        <h5 class="mb-4">Data Tamu</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Nama:</label>
                <div data-field="nama" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Jenis Kelamin:</label>
                <div data-field="jenis_kelamin" class="fw-bold">-</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Email:</label>
                <div data-field="email" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Nomor Telepon:</label>
                <div data-field="nomor_telepon" class="fw-bold">-</div>
            </div>
        </div>
    </div>

    <!-- Data Kunjungan -->
    <div class="mb-7">
        <h5 class="mb-4">Data Kunjungan</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Jenis Kunjungan:</label>
                <div data-field="jenis_kunjungan" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Kategori Tujuan:</label>
                <div data-field="kategori_tujuan" class="fw-bold">-</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Transportasi:</label>
                <div data-field="transportasi" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Status Validasi:</label>
                <div data-field="status_validasi" class="fw-bold">-</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Status Checkout:</label>
                <div data-field="is_checkout" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Identitas:</label>
                <div data-field="identitas" class="fw-bold">-</div>
            </div>
        </div>
    </div>

    <!-- Data Waktu -->
    <div class="mb-7">
        <h5 class="mb-4">Data Waktu</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Tanggal Kunjungan:</label>
                <div data-field="tanggal_kunjungan" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Waktu Kunjungan:</label>
                <div data-field="waktu_kunjungan" class="fw-bold">-</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Waktu Keluar:</label>
                <div data-field="waktu_keluar" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Waktu Checkout:</label>
                <div data-field="checkout_time" class="fw-bold">-</div>
            </div>
        </div>
    </div>

    <!-- Data Event (jika ada) -->
    <div class="mb-7">
        <h5 class="mb-4">Data Event</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Nama Event:</label>
                <div data-field="event_nama" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Kategori Event:</label>
                <div data-field="event_kategori" class="fw-bold">-</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Tanggal Event:</label>
                <div data-field="event_tanggal" class="fw-bold">-</div>
            </div>
        </div>
    </div>

    <!-- Data Detail Tambahan -->
    <div class="mb-7">
        <h5 class="mb-4">Detail Tambahan</h5>
        <div data-details-container class="border rounded p-4 bg-light">
            <!-- Detail akan ditampilkan di sini -->
        </div>
    </div>

    @slot('action')
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
    @endslot
</x-modal>
@endsection

@push('scripts')
<x-script.crud2></x-script.crud2>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        jForm.init({
            name: "monitoring-kunjungan",
            base_url: `{{ route('app.monitoring.kunjungan') }}`,
            url: {
                detail: `{{ route('app.monitoring.data') }}/detail`
            },
            onDetail: function(data) {
                console.log('Detail data received:', data);
            }
        });

        // Handle validasi action (similar to validasi page)
        $(document).on('click', '[jf-validate]', function(e) {
            e.preventDefault();

            const id = $(this).attr('jf-validate');
            const url = `{{ route('app.kunjungan.index') }}/update/validasi`;

            Swal.fire({
                title: 'Konfirmasi Validasi',
                text: 'Apakah Anda yakin ingin memvalidasi kunjungan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Validasi',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send POST request to validate
                    $.post(url, {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: id
                    }).done(function(response) {
                        Swal.fire('Berhasil!', 'Kunjungan berhasil divalidasi.', 'success');
                        // Reload datatable and refresh statistics
                        $('table[jf-data="monitoring-kunjungan"]').DataTable().ajax.reload(null, false);
                        setTimeout(() => {
                            location.reload(); // Refresh to update statistics
                        }, 1500);
                    }).fail(function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat memvalidasi kunjungan.', 'error');
                    });
                }
            });
        });

        // Auto refresh every 5 minutes
        setInterval(function() {
            $('table[jf-data="monitoring-kunjungan"]').DataTable().ajax.reload(null, false);
        }, 300000); // 5 minutes = 300000ms
    });
</script>
@endpush