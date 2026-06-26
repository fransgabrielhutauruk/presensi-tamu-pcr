@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')

@section('toolbar')
    <x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
        <x-slot:tools>
        </x-slot:tools>
    </x-theme.toolbar>
@endsection

@section('content')
    <div id="kt_app_content_container" class="app-container container-fluid" data-cue="slideInLeft" data-duration="1000"
        data-delay="0">
        @include('contents.admin.kunjungan.tabs')

        <div class="card mb-5">
            <div class="d-flex justify-content-end p-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse"
                    data-bs-target="#columnCustomizer">
                    <i class="ki-outline ki-setting-2 fs-3"></i> Atur Kolom
                </button>
            </div>
            <div id="columnCustomizer" class="collapse">
                <div class="card-body">
                    <form id="columnForm" method="GET" action="{{ route('app.kunjungan.index') }}">
                        <div class="row">
                            <div class="col-12 mb-4">

                                <div
                                    class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-3">
                                    <label class="form-label fw-bold mb-0">Pilih Kolom yang Akan Ditampilkan:</label>
                                    <div class="form-check flex-shrink-0">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                        <label class="form-check-label fw-bold text-primary" for="checkAll">
                                            Pilih Semua
                                        </label>
                                    </div>
                                </div>

                                <div class="separator separator-dashed mb-4"></div>

                                <div class="row g-3">
                                    @foreach ($pageData->availableColumns as $key => $column)
                                        @if (!isset($column['required']) || !$column['required'])
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                                <div class="form-check">
                                                    <input class="form-check-input column-checkbox flex-shrink-0"
                                                        type="checkbox" name="columns[]" value="{{ $key }}"
                                                        id="col_{{ $key }}"
                                                        {{ in_array($key, $pageData->selectedColumns) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-break"
                                                        for="col_{{ $key }}">
                                                        {{ $column['title'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                            </div>

                            <div class="col-12 d-flex flex-column flex-sm-row justify-content-sm-end gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-secondary w-100 w-sm-auto"
                                    onclick="resetColumns()">
                                    Reset Default
                                </button>
                                <button type="submit" class="btn btn-sm btn-light-primary w-100 w-sm-auto">
                                    Terapkan
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan" jf-list="datatable"
            :server_side="true" :default_order="[[2, 'desc']]">
            @slot('action')
                <x-btn.refresh-datatable />
            @endslot
            @slot('filter')
                <div class="row g-4">
                    <div class="col-md-2">
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
                        <label class="form-label fs-7 fw-semibold">Jenis Kunjungan</label>
                        <select id="filter_jenis_kunjungan" name="filter_jenis_kunjungan" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua Jenis Kunjungan" data-allow-clear="true"
                            data-cy="select-filter-kunjungan-jenis-kunjungan">
                            <option value="">Semua Jenis Kunjungan</option>
                            <option value="event" {{ request('filter_jenis_kunjungan') == 'event' ? 'selected' : '' }}>Event</option>
                            <option value="non_event" {{ request('filter_jenis_kunjungan') == 'non_event' ? 'selected' : '' }}>Non-Event</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold">Dari Tanggal</label>
                        <input type="date" id="filter_date_from" name="filter_date_from" class="form-control form-control-sm"
                            data-cy="input-filter-event-date-from" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold">Sampai Tanggal</label>
                        <input type="date" id="filter_date_to" name="filter_date_to" class="form-control form-control-sm"
                            data-cy="input-filter-event-date-to" />
                    </div>
                </div>
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="kunjungan"
        title="Detail Kunjungan">
        @php
            $detailSections = [
                [
                    'title' => 'Data Tamu',
                    'fields' => ['nama', 'identitas', 'jenis_kelamin', 'email', 'nomor_telepon'],
                ],
                [
                    'title' => 'Data Kunjungan',
                    'fields' => ['jenis_kunjungan', 'kategori_tujuan', 'transportasi'],
                ],
                [
                    'title' => 'Data Waktu',
                    'fields' => ['tanggal_kunjungan', 'waktu_kunjungan', 'waktu_estimasi_keluar', 'waktu_checkout'],
                ],
            ];

            $fieldLabels = [
                'nama' => 'Nama',
                'identitas' => 'Identitas',
                'jenis_kelamin' => 'Jenis Kelamin',
                'email' => 'Email',
                'nomor_telepon' => 'Nomor Telepon',
                'jenis_kunjungan' => 'Jenis Kunjungan',
                'kategori_tujuan' => 'Kategori Tujuan',
                'transportasi' => 'Transportasi',
                'tanggal_kunjungan' => 'Tanggal Kunjungan',
                'waktu_kunjungan' => 'Waktu Kunjungan',
                'waktu_estimasi_keluar' => 'Waktu Estimasi Keluar',
                'waktu_checkout' => 'Waktu Checkout',
            ];
        @endphp

        @foreach ($detailSections as $section)
            <div class="mb-7">
                <h5 class="mb-4">{{ $section['title'] }}</h5>
                <div class="row">
                    @foreach ($section['fields'] as $fieldKey)
                        <div class="col-md-6 mb-3">
                            <label
                                class="fw-bold text-muted">{{ $fieldLabels[$fieldKey] ?? ucwords(str_replace('_', ' ', $fieldKey)) }}:</label>
                            <div data-field="{{ $fieldKey }}" class="fw-bold">-</div>
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

@push('styles')
    <style>
        .column-customizer .form-check {
            margin-bottom: 8px;
        }

        .column-customizer .form-check-label {
            font-weight: 500;
            color: #5e6278;
        }

        .column-customizer .form-check-input:checked+.form-check-label {
            color: #009ef7;
            font-weight: 600;
        }

        .card-header {
            background: #f9f9f9;
        }

        .badge {
            font-size: 0.8rem;
        }

        #checkAll {
            transform: scale(1.2);
        }

        #checkAll+label {
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #checkAll+label:hover {
            opacity: 0.8;
        }

        #checkAll:indeterminate {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        #checkAll:indeterminate+label {
            color: #ffc107 !important;
        }

        #checkAll:indeterminate+label i {
            color: #ffc107;
        }

        .column-checkbox {
            transition: all 0.2s ease;
        }

        .form-check-label {
            transition: all 0.2s ease;
        }

        .separator.separator-dashed {
            border-top: 1px dashed #e4e6ef;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const DEFAULT_COLUMNS = ['action', 'waktu_kunjungan', 'identitas', 'nama', 'jenis_kelamin', 'jenis_kunjungan'];

        function formatLabel(str) {
            return str.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
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
            const detailFields = ['nama', 'jenis_kelamin', 'email', 'nomor_telepon', 'jenis_kunjungan',
                'kategori_tujuan', 'transportasi', 'identitas',
                'tanggal_kunjungan', 'waktu_kunjungan', 'waktu_estimasi_keluar', 'checkout_time', 'event_nama',
                'event_kategori', 'event_kategori_lokasi', 'event_lokasi'
            ];
            detailFields.forEach(field => setDetailField(field, data[field]));
        }

        function handleEventSection(data) {
            const isEvent = data.event_nama && data.event_nama !== '-';
            $('#dataEventSection').toggle(isEvent);
        }

        function populateAdditionalDetails(data) {
            const detailContainer = $('[data-details-container]');
            if (detailContainer.length === 0) return;
            detailContainer.empty();
            if (!data.details || !Array.isArray(data.details)) return;
            data.details.forEach(detail => {
                const formattedLabel = formatLabel(detail.kunci);
                detailContainer.append(
                    `<div class="row mb-2"><div class="col-auto fw-bold"><span class="text-muted">${formattedLabel}: </span><span class="fw-bold">${detail.nilai}</span></div></div>`
                );
            });
        }

        $(document).ready(function() {
            jForm.init({
                name: "kunjungan",
                base_url: `{{ route('app.kunjungan.index') }}`,
                onDetail: function(data) {
                    handleEventSection(data);
                    populateDetailFields(data);
                    populateAdditionalDetails(data);
                },
            });

            @if (request()->has('columns'))
                $('#columnCustomizer').removeClass('show');
            @endif
        });

        $('#checkAll').on('change', function() {
            $('.column-checkbox').prop('checked', $(this).is(':checked'));
            updateCheckAllState();
        });

        $('.column-checkbox').on('change', function() {
            updateCheckAllState();
        });

        function updateCheckAllState() {
            const totalCheckboxes = $('.column-checkbox').length;
            const checkedCheckboxes = $('.column-checkbox:checked').length;

            if (checkedCheckboxes === totalCheckboxes) {
                $('#checkAll').prop('checked', true).prop('indeterminate', false);
            } else if (checkedCheckboxes === 0) {
                $('#checkAll').prop('checked', false).prop('indeterminate', false);
            } else {
                $('#checkAll').prop('checked', false).prop('indeterminate', true);
            }
        }

        updateCheckAllState();

        function resetColumns() {
            $('.column-checkbox').prop('checked', false);
            DEFAULT_COLUMNS.forEach(function(column) {
                $('input[value="' + column + '"]').prop('checked', true);
            });
            updateCheckAllState();
            $('#columnForm').submit();
        }

        $('#dataTableBuilder').on('preXhr.dt', function(e, settings, data) {
            var vals = {
                filter_jenis_kelamin: $('#filter_jenis_kelamin').val() || '',
                filter_identitas: $('#filter_identitas').val() || '',
                filter_jenis_kunjungan: $('#filter_jenis_kunjungan').val() || '',
                filter_date_from: $('#filter_date_from').val() || '',
                filter_date_to: $('#filter_date_to').val() || '',
            };
            $.extend(data, vals);

            var activeCount = Object.values(vals).filter(function(v) {
                return v !== '';
            }).length;
            var $badge = $('#dataTableBuilder-filter-badge');
            if (activeCount > 0) {
                $badge.text(activeCount).removeClass('d-none');
            } else {
                $badge.addClass('d-none');
            }
        });
    </script>
@endpush
