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
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-bold mb-0">Pilih Kolom yang Akan Ditampilkan:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                        <label class="form-check-label fw-bold text-primary" for="checkAll">
                                            Pilih Semua
                                        </label>
                                    </div>
                                </div>
                                <div class="separator separator-dashed mb-4"></div>
                                <div class="row">
                                    @foreach ($pageData->availableColumns as $key => $column)
                                        @if (!isset($column['required']) || !$column['required'])
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input column-checkbox" type="checkbox"
                                                        name="columns[]" value="{{ $key }}"
                                                        id="col_{{ $key }}"
                                                        {{ in_array($key, $pageData->selectedColumns) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="col_{{ $key }}">
                                                        {{ $column['title'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-primary me-2">
                                    <i class="bi bi-check2-circle fs-3"></i> Terapkan
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="resetColumns()">
                                    <i class="ki-outline ki-arrows-circle fs-3"></i> Reset Default
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan" jf-list="datatable">
            @slot('action')
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="kunjungan"
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
        function formatLabel(str) {
            return str
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }

        $(document).ready(function() {
            jForm.init({
                name: "kunjungan",
                base_url: `{{ route('app.kunjungan.index') }}`,
                onDetail: function(data) {
                    console.log('Detail data received:', data);

                    const isEvent = data.event_nama && data.event_nama !== '-';

                    if (isEvent) {
                        $('#dataEventSection').show();
                    } else {
                        $('#dataEventSection').hide();
                    }

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
                },

            });

            @if (request()->has('columns'))
                $('#columnCustomizer').removeClass('show');
            @endif
        });

        $('#checkAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.column-checkbox').prop('checked', isChecked);
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
            const defaultColumns = ['nama', 'jenis_kelamin', 'identitas', 'jenis_kunjungan', 'waktu_kunjungan',
                'status_validasi'
            ];
            $('.column-checkbox').prop('checked', false);
            defaultColumns.forEach(function(column) {
                $('input[value="' + column + '"]').prop('checked', true);
            });
            updateCheckAllState();
            $('#columnForm').submit();
        }
    </script>
@endpush
