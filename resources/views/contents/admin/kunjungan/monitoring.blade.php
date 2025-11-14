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

        <div class="row g-5 g-xl-10">
            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="card card-flush mb-5">
                    <div class="card-header py-5">
                        <div class="card-title d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <span
                                    class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $pageData->totalKunjunganHariIni }}</span>
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
                                <span
                                    class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2">{{ $pageData->kunjunganSudahCheckout }}</span>
                            </div>
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Sudah Checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan-monitoring"
            jf-list="datatable">
            @slot('action')
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="kunjungan-monitoring"
        title="Detail Kunjungan">
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
                    <label class="fw-bold text-muted">Waktu Keluar (Estimasi):</label>
                    <div data-field="waktu_keluar" class="fw-bold">-</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Waktu Checkout:</label>
                    <div data-field="checkout_time" class="fw-bold">-</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Jumlah Rombongan:</label>
                    <div data-field="jumlah_rombongan" class="fw-bold">-</div>
                </div>
            </div>
        </div>

        <div class="mb-7" id="dataEventSection" style="display: none;">
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
        </div>

        <div class="mb-7">
            <h5 class="mb-4">Detail Tambahan</h5>
            <div data-details-container class="border rounded p-4 bg-light">
            </div>
        </div>

        @slot('action')
        @endslot
    </x-modal>
@endsection

@push('scripts')
    <x-script.crud2></x-script.crud2>
@endpush

@push('scripts')
    <script>
        function formatLabel(str) {
            return str
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }

        $(document).ready(function() {
            jForm.init({
                name: "kunjungan-monitoring",
                base_url: `{{ route('app.kunjungan.index') }}`,
                onDetail: function(data) {
                    const isEvent = data.event_nama && data.event_nama !== '-';

                    if (isEvent) {
                        $('#dataEventSection').show();
                    } else {
                        $('#dataEventSection').hide();
                    }

                    $('[data-field="nama"]').text(data.nama || '-');
                    $('[data-field="jenis_kelamin"]').text(data.jenis_kelamin || '-');
                    $('[data-field="email"]').text(data.email || '-');
                    $('[data-field="nomor_telepon"]').text(data.nomor_telepon || '-');
                    $('[data-field="jenis_kunjungan"]').text(data.jenis_kunjungan || '-');
                    $('[data-field="kategori_tujuan"]').text(data.kategori_tujuan || '-');
                    $('[data-field="transportasi"]').text(data.transportasi || '-');
                    $('[data-field="status_validasi"]').text(data.status_validasi || '-');
                    $('[data-field="is_checkout"]').text(data.is_checkout || '-');
                    $('[data-field="identitas"]').text(data.identitas || '-');
                    $('[data-field="tanggal_kunjungan"]').text(data.tanggal_kunjungan || '-');
                    $('[data-field="waktu_kunjungan"]').text(data.waktu_kunjungan || '-');
                    $('[data-field="waktu_keluar"]').text(data.waktu_keluar || '-');
                    $('[data-field="checkout_time"]').text(data.checkout_time || '-');
                    $('[data-field="jumlah_rombongan"]').text(data.jumlah_rombongan || '-');
                    $('[data-field="event_nama"]').text(data.event_nama || '-');
                    $('[data-field="event_kategori"]').text(data.event_kategori || '-');

                    if (data.details && Array.isArray(data.details)) {
                        var detailContainer = $('[data-details-container]');
                        if (detailContainer.length > 0) {
                            detailContainer.empty();
                            data.details.forEach(function(detail) {
                                var formattedLabel = formatLabel(detail.kunci);
                                var detailHtml = '<div class="row mb-2">' +
                                    '<div class="col-4 fw-bold text-muted">' + formattedLabel +
                                    ':</div>' +
                                    '<div class="col-8 fw-bold">' + detail.nilai + '</div>' +
                                    '</div>';
                                detailContainer.append(detailHtml);
                            });
                        }
                    }
                }
            });

            setInterval(function() {
                $('table[jf-data="kunjungan-monitoring"]').DataTable().ajax.reload(null, false);
            }, 300000);
        });
    </script>
@endpush
