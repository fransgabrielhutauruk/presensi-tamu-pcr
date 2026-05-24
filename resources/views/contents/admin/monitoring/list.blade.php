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
                <div class="card card-flush mb-5" data-cy="card-total-kunjungan-hari-ini">
                    <div class="card-header py-5">
                        <div class="card-title d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <span
                                    class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2" data-cy="text-total-kunjungan-hari-ini">{{ $pageData->totalKunjunganHariIni }}</span>
                            </div>
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Kunjungan Hari Ini</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-6 col-xl-3">
                <div class="card card-flush mb-5" data-cy="card-kunjungan-sudah-checkout">
                    <div class="card-header py-5">
                        <div class="card-title d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <span
                                    class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2" data-cy="text-kunjungan-sudah-checkout">{{ $pageData->kunjunganSudahCheckout }}</span>
                            </div>
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Sudah Checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan-monitoring"
            jf-list="datatable" data-cy="table-monitoring-kunjungan">
            @slot('action')
                <x-btn.refresh-datatable />
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="kunjungan-monitoring"
        title="Detail Kunjungan">
        @php
            $modalSections = [
                'Data Tamu' => ['nama', 'jenis_kelamin', 'email', 'nomor_telepon'],
                'Data Kunjungan' => [
                    'jenis_kunjungan',
                    'kategori_tujuan',
                    'transportasi',
                    'status_validasi',
                    'is_checkout',
                    'identitas',
                ],
                'Data Waktu' => [
                    'tanggal_kunjungan',
                    'waktu_kunjungan',
                    'waktu_keluar',
                    'checkout_time',
                ],
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
            detailFields: ['nama', 'jenis_kelamin', 'email', 'nomor_telepon', 'jenis_kunjungan',
                'kategori_tujuan', 'transportasi', 'status_validasi', 'is_checkout',
                'identitas', 'tanggal_kunjungan', 'waktu_kunjungan', 'waktu_keluar',
                'checkout_time', 'event_nama', 'event_kategori'
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
                        const cardFlush = $('[jf-data="kunjungan-monitoring"]').closest('.app-container').find(
                            '.card-flush');
                        cardFlush.eq(0).find('.fs-2hx').text(response.totalKunjunganHariIni);
                        cardFlush.eq(1).find('.fs-2hx').text(response.kunjunganSudahCheckout);
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

            // Auto-refresh every 5 minutes
            setInterval(function() {
                $('table[jf-data="kunjungan-monitoring"]').DataTable().ajax.reload(null, false);
                updateStats();
            }, JFORM_CONFIG.autoRefreshInterval);
        });
    </script>
@endpush
