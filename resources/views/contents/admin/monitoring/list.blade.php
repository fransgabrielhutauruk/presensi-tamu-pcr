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
        <div class="row g-4 mb-8">

            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <a href="javascript:void(0)"
                    class="card card-flush h-100 shadow-sm border-0 hoverable text-decoration-none filter-card-trigger"
                    style="border-top: 4px solid #009ef7 !important;" data-filter-target="#filterIsCheckout" data-value="">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-dark lh-1 ls-n2 mb-1"
                                data-cy="text-total-kunjungan-hari-ini">{{ $pageData->totalKunjunganHariIni }}</span>
                            <span class="text-gray-500 fw-semibold fs-7 text-uppercase">Total Kunjungan</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <a href="javascript:void(0)"
                    class="card card-flush h-100 shadow-sm border-0 hoverable text-decoration-none filter-card-trigger"
                    style="border-top: 4px solid #50cd89 !important;" data-filter-target="#filterIsCheckout" data-value="1">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-dark lh-1 ls-n2 mb-1"
                                data-cy="text-kunjungan-sudah-checkout">{{ $pageData->kunjunganSudahCheckout }}</span>
                            <span class="text-gray-500 fw-semibold fs-7 text-uppercase">Sudah Checkout</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <a href="javascript:void(0)"
                    class="card card-flush h-100 shadow-sm border-0 hoverable text-decoration-none filter-card-trigger"
                    style="border-top: 4px solid #f1416c !important;" data-filter-target="#filterIsCheckout" data-value="0">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column">
                            <span class="fs-2hx fw-bold text-dark lh-1 ls-n2 mb-1"
                                data-cy="text-kunjungan-belum-checkout">{{ $pageData->kunjunganBelumCheckout }}</span>
                            <span class="text-gray-500 fw-semibold fs-7 text-uppercase">Belum Checkout</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan-monitoring"
            jf-list="datatable" data-cy="table-monitoring-kunjungan" :server_side="true" :default_order="[[2, 'desc']]">
            @slot('filter')
                <div class="row g-4">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Status Checkout</label>
                        <select id="filterIsCheckout" class="form-select form-select-sm" data-control="select2"
                            data-allow-clear="true" data-placeholder="Semua Status" data-cy="select-filter-checkout-monitoring">
                            <option value="">Semua Status</option>
                            <option value="0" {{ request('filterIsCheckout') == '0' ? 'selected' : '' }}>Belum Checkout
                            </option>
                            <option value="1" {{ request('filterIsCheckout') == '1' ? 'selected' : '' }}>Sudah Checkout
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Identitas</label>
                        <select id="filter_identitas" name="filter_identitas" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua Identitas" data-allow-clear="true"
                            data-cy="select-filter-kunjungan-identitas">
                            <option value="">Semua Identitas</option>
                            <option value="non-civitas">Non-Civitas</option>
                            <option value="civitas">Civitas PCR</option>
                            <option value="vip">Non-Civitas (VIP)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Jenis Kelamin</label>
                        <select id="filter_jenis_kelamin" name="filter_jenis_kelamin" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua Jenis Kelamin" data-allow-clear="true"
                            data-cy="select-filter-kunjungan-jenis-kelamin">
                            <option value="">Semua Jenis Kelamin</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Jenis Kunjungan</label>
                        <select id="filter_jenis_kunjungan" name="filter_jenis_kunjungan" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua Jenis Kunjungan" data-allow-clear="true"
                            data-cy="select-filter-kunjungan-jenis-kunjungan">
                            <option value="">Semua Jenis Kunjungan</option>
                            <option value="event">Event</option>
                            <option value="non_event">Non-Event</option>
                        </select>
                    </div>
                </div>
            @endslot
            @slot('action')
                <x-btn.refresh-datatable />
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="kunjungan-monitoring"
        title="Detail Kunjungan">
        @php
            $modalSections = [
                'Data Tamu' => ['nama', 'identitas', 'jenis_kelamin', 'email', 'nomor_telepon'],
                'Data Kunjungan' => ['jenis_kunjungan', 'kategori_tujuan', 'transportasi'],
                'Data Waktu' => ['tanggal_kunjungan', 'waktu_kunjungan', 'waktu_estimasi_keluar', 'waktu_checkout'],
            ];
        @endphp

        @foreach ($modalSections as $sectionTitle => $fields)
            <div class="mb-7">
                <h5 class="mb-4">{{ $sectionTitle }}</h5>
                <div class="row">
                    @foreach ($fields as $field)
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted">{{ str(str_replace('_', ' ', $field))->title() }}:</label>
                            <div data-field="{{ $field }}" class="fw-bold">-</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

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
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Kategori Lokasi Event:</label>
                    <div data-field="event_kategori_lokasi" class="fw-bold">-</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Lokasi Event:</label>
                    <div data-field="event_lokasi" class="fw-bold">-</div>
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
        const JFORM_CONFIG = {
            name: 'kunjungan-monitoring',
            baseUrl: `{{ route('app.kunjungan.index') }}`,
            statsUrl: `{{ route('app.kunjungan.monitoring.stats') }}`,
            autoRefreshInterval: 300000, // 5 minutes
            detailFields: ['nama', 'identitas', 'jenis_kelamin', 'email', 'nomor_telepon', 'jenis_kunjungan',
                'kategori_tujuan', 'transportasi',
                'tanggal_kunjungan', 'waktu_kunjungan', 'waktu_estimasi_keluar',
                'waktu_checkout', 'event_nama', 'event_kategori', 'event_kategori_lokasi', 'event_lokasi'
            ]
        };

        function formatLabel(str) {
            return str
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }

        function setDetailField(field, value) {
            const element = $(`[data-field="${field}"]`);
            if (!element.length) return;

            const hasValue = value !== undefined && value !== null && value !== '';
            const wrapper = element.closest('.mb-3, .mb-4');

            if (hasValue) {
                element.text(value);
                wrapper.length ? wrapper.show() : element.show();
            } else {
                wrapper.length ? wrapper.hide() : element.hide();
            }
        }

        function populateDetailFields(data) {
            JFORM_CONFIG.detailFields.forEach(field => {
                if (field in data) {
                    setDetailField(field, data[field]);
                }
            });
        }

        function handleEventSection(data) {
            const isEvent = data.event_nama && data.event_nama !== '-';
            $('#dataEventSection').toggle(isEvent);
        }

        function populateAdditionalDetails(data) {
            if (!data.details || !Array.isArray(data.details)) return;

            const detailContainer = $('[data-details-container]');
            if (detailContainer.length === 0) return;

            detailContainer.empty();
            data.details.forEach(detail => {
                const formattedLabel = formatLabel(detail.kunci);
                const detailHtml = `<div class="row mb-2">
                        <div class="col-auto fw-bold">
                            <span class="text-muted">${formattedLabel}: </span>
                            <span class="fw-bold">${detail.nilai}</span>
                        </div>
                    </div>`;
                detailContainer.append(detailHtml);
            });
        }

        function updateStats() {
            $.ajax({
                url: JFORM_CONFIG.statsUrl,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('[data-cy="text-total-kunjungan-hari-ini"]').text(response.totalKunjunganHariIni);
                        $('[data-cy="text-kunjungan-sudah-checkout"]').text(response.kunjunganSudahCheckout);
                        $('[data-cy="text-kunjungan-belum-checkout"]').text(response.kunjunganBelumCheckout);
                    }
                }
            });
        }

        $(document).ready(function() {
            jForm.init({
                name: JFORM_CONFIG.name,
                base_url: JFORM_CONFIG.baseUrl,
                onDetail: function(data) {
                    handleEventSection(data);
                    populateDetailFields(data);
                    populateAdditionalDetails(data);
                }
            });

            $(document).on('click', '#btn-refresh', function() {
                updateStats();
            });

            $(document).on('click', '.filter-card-trigger', function(e) {
                e.preventDefault();

                const targetSelect = $(this).data('filter-target');
                const filterValue = $(this).data('value'); 

                if ($(targetSelect).length) {
                    $(targetSelect).val(filterValue).trigger('change');

                    $('#dataTableBuilder').DataTable().ajax.reload(null, false);
                }
            });

            // Auto-refresh every 5 minutes
            setInterval(function() {
                $('#dataTableBuilder').DataTable().ajax.reload(null, false);
                updateStats();
            }, JFORM_CONFIG.autoRefreshInterval);
        });

        $('#dataTableBuilder').on('preXhr.dt', function(e, settings, data) {
            var vals = {
                filter_jenis_kunjungan: $('#filter_jenis_kunjungan').val() || '',
                filter_is_checkout: $('#filterIsCheckout').val() || '',
                filter_jenis_kelamin: $('#filter_jenis_kelamin').val() || '',
                filter_identitas: $('#filter_identitas').val() || '',
            };
            $.extend(data, vals);

            var count = Object.values(vals).filter(v => v !== '').length;
            $(`.dataTableBuilder-trigger_filter #filter-count`).text(count > 0 ? '(' + count + ')' : '');
        });
    </script>
@endpush