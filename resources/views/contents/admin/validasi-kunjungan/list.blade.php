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

        <div class="card mb-5">
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center">
                        <span id="selectedCount" class="badge badge-light fs-7"
                            data-cy="text-selected-count-validasi-kunjungan">0 dipilih</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" id="bulkValidateBtn" data-action="validate"
                            disabled data-cy="btn-bulk-validate-validasi-kunjungan">
                            <i class="bi bi-check2-circle fs-4"></i> Validasi Terpilih
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="bulkRejectBtn" data-action="reject"
                            disabled data-cy="btn-bulk-reject-validasi-kunjungan">
                            <i class="bi bi-x-circle fs-4"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" :server_side="true" :default_order="[[2, 'desc']]"
            jf-data="kunjungan-validasi" jf-list="datatable" data-cy="table-validasi-kunjungan">
            @slot('action')
                <x-btn.refresh-datatable />
            @endslot
            @slot('filter')
                <div class="row g-4">
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold">Jenis Kelamin</label>
                        <select id="filter_jenis_kelamin_validasi" name="filter_jenis_kelamin"
                            class="form-select form-select-sm" data-control="select2" data-placeholder="Semua Jenis Kelamin"
                            data-allow-clear="true" data-cy="select-filter-validasi-jenis-kelamin">
                            <option value="">Semua Jenis Kelamin</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Identitas</label>
                        <select id="filter_identitas_validasi" name="filter_identitas" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua Identitas" data-allow-clear="true"
                            data-cy="select-filter-validasi-identitas">
                            <option value="">Semua Identitas</option>
                            <option value="non-civitas">Non-Civitas</option>
                            <option value="civitas">Civitas PCR</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Jenis Kunjungan</label>
                        <select id="filter_jenis_kunjungan_validasi" name="filter_jenis_kunjungan"
                            class="form-select form-select-sm" data-control="select2" data-placeholder="Semua Jenis Kunjungan"
                            data-allow-clear="true" data-cy="select-filter-validasi-jenis-kunjungan">
                            <option value="">Semua Jenis Kunjungan</option>
                            <option value="event">Event</option>
                            <option value="non_event">Non-Event</option>
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

    <x-modal id="modalDetailValidasi" type="centered" :static="true" size="lg" title="Detail Kunjungan"
        data-cy="modal-detail-validasi-kunjungan">
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
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Transportasi:</label>
                    <div data-field="transportasi" class="fw-bold">-</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Status Validasi:</label>
                    <div data-field="status_validasi" class="fw-bold">-</div>
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
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Waktu Estimasi Keluar:</label>
                    <div data-field="waktu_estimasi_keluar" class="fw-bold">-</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Waktu Checkout:</label>
                    <div data-field="waktu_checkout" class="fw-bold">-</div>
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
            <x-btn.form action="save" id="validateSingleBtn" text="Validasi" title="Validasi"
                data-cy="btn-validate-single-validasi-kunjungan" />
            <x-btn.form action="cancle" id="rejectSingleBtn" text="Hapus" title="Hapus"
                data-cy="btn-reject-single-validasi-kunjungan" />
        @endslot
    </x-modal>
@endsection

@push('scripts')
    <x-script.crud2></x-script.crud2>
@endpush

@push('styles')
    <style>
        #bulkActionPanel {
            border-left: 4px solid #009ef7;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
            display: block !important;
        }

        #bulkActionPanel .card-body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
        }

        #bulkActionPanel .btn {
            transition: all 0.2s ease-in-out;
        }

        #selectedCount {
            transition: all 0.2s ease-in-out;
        }

        .form-check-input {
            transform: scale(1.1);
        }

        .row-checkbox:checked {
            background-color: #009ef7;
            border-color: #009ef7;
        }

        .badge {
            font-size: 0.8rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn:disabled {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
        }

        .btn {
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTable th:first-child,
        .dataTables_wrapper .dataTable td:first-child {
            text-align: center !important;
        }

        input[type="checkbox"]:indeterminate {
            background-color: #ffc107;
            border-color: #ffc107;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10h8'/%3e%3c/svg%3e");
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentDetailId = null;

        $(document).ready(function() {
            $(document).off('click', '[jf-data="kunjungan-validasi"] [jf-detail]');
            $(document).on('click', '[jf-data="kunjungan-validasi"] [jf-detail]', function() {
                currentDetailId = $(this).attr('jf-detail');
            });

            jForm.init({
                name: "kunjungan-validasi",
                base_url: `{{ route('app.kunjungan.index') }}`,
                onDetail: function(data) {
                    showDetailModal(data);
                }
            });

            $('#datatable').on('draw.dt', function() {
                setTimeout(updateBulkActionPanel, 100);
            });

            $(document).on('change', '.row-checkbox', updateBulkActionPanel);

            $(document).on('change', '#checkAllValidasi', function() {
                $('.row-checkbox').prop('checked', $(this).is(':checked'));
                updateBulkActionPanel();
            });

            setTimeout(updateBulkActionPanel, 500);

            $(document).on('click', '#bulkValidateBtn, #bulkRejectBtn', function() {
                bulkAction($(this).data('action'));
            });

            $(document).on('click', '#validateSingleBtn', validateSingle);
            $(document).on('click', '#rejectSingleBtn', rejectSingle);
        });

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
            const detailFields = [
                'nama',
                'jenis_kelamin',
                'email',
                'nomor_telepon',
                'jenis_kunjungan',
                'kategori_tujuan',
                'transportasi',
                'status_validasi',
                'identitas',
                'tanggal_kunjungan',
                'waktu_kunjungan',
                'waktu_estimasi_keluar',
                'waktu_checkout',
                'event_nama',
                'event_kategori',
                'event_kategori_lokasi',
                'event_lokasi'
            ];

            detailFields.forEach(field => {
                setDetailField(field, data[field]);
            });
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
                const detailHtml = `<div class="row mb-2">
                    <div class="col-auto fw-bold">
                        <span class="text-muted">${formattedLabel}: </span>
                        <span class="fw-bold">${detail.nilai}</span>
                    </div>
                </div>`;
                detailContainer.append(detailHtml);
            });
        }

        function showDetailModal(data) {
            handleEventSection(data);
            populateDetailFields(data);
            populateAdditionalDetails(data);

            $('#modalDetailValidasi').modal('show');
        }

        function updateBulkActionPanel() {
            const checkedBoxes = $('.row-checkbox:checked');
            const totalBoxes = $('.row-checkbox');
            const count = checkedBoxes.length;

            $('#selectedCount').text(count + ' dipilih');

            if (count > 0) {
                $('#bulkValidateBtn, #bulkRejectBtn').prop('disabled', false);
                $('#bulkValidateBtn').removeClass('btn-light').addClass('btn-success');
                $('#bulkRejectBtn').removeClass('btn-light').addClass('btn-danger');
                $('#selectedCount').removeClass('badge-light').addClass('badge-primary');
            } else {
                $('#bulkValidateBtn, #bulkRejectBtn').prop('disabled', true);
                $('#bulkValidateBtn').removeClass('btn-success').addClass('btn-light');
                $('#bulkRejectBtn').removeClass('btn-danger').addClass('btn-light');
                $('#selectedCount').removeClass('badge-primary').addClass('badge-light');
            }

            const checkAllBox = $('#checkAllValidasi');
            if (count === totalBoxes.length && totalBoxes.length > 0) {
                checkAllBox.prop('checked', true).prop('indeterminate', false);
            } else if (count === 0) {
                checkAllBox.prop('checked', false).prop('indeterminate', false);
            } else if (count > 0) {
                checkAllBox.prop('checked', false).prop('indeterminate', true);
            }
        }

        function clearSelection() {
            $('.row-checkbox, #checkAllValidasi').prop('checked', false);
            updateBulkActionPanel();
        }

        const _csrfToken = $('meta[name="csrf-token"]').attr('content');

        function reloadValidationTable() {
            $('table[jf-data="kunjungan-validasi"]').DataTable().ajax.reload(null, false);
        }

        function confirmAndPost(opts) {
            // opts: { title, text, icon, confirmText, postUrl, postData, onSuccessReload, failMessage }
            return Swal.fire({
                title: opts.title || 'Konfirmasi',
                text: opts.text || '',
                icon: opts.icon || 'question',
                showCancelButton: true,
                confirmButtonText: opts.confirmText || 'Ya',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                const data = Object.assign({
                    _token: _csrfToken
                }, opts.postData || {});

                return $.post(opts.postUrl, data)
                    .done(function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', response.message || opts.successMessage ||
                                'Operasi berhasil.', 'success');
                            if (opts.onSuccessHideModal) {
                                $('#modalDetailValidasi').modal('hide');
                            }
                            if (opts.onSuccessReload) {
                                reloadValidationTable();
                            }
                            if (typeof opts.onSuccess === 'function') opts.onSuccess(response);
                            clearSelection();
                        } else {
                            Swal.fire('Gagal!', response.message || opts.failMessage || 'Terjadi kesalahan',
                                'error');
                        }
                    }).fail(function() {
                        Swal.fire('Gagal!', opts.failMessage || 'Terjadi kesalahan', 'error');
                    });
            });
        }

        function bulkAction(action) {
            const checkedBoxes = $('.row-checkbox:checked');
            const ids = [];

            checkedBoxes.each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                Swal.fire('Peringatan!', 'Pilih minimal satu kunjungan terlebih dahulu.', 'warning');
                return;
            }

            const actionText = action === 'validate' ? 'memvalidasi' : 'menghapus';
            const actionTitle = action === 'validate' ? 'Validasi Massal' : 'Hapus Massal';

            confirmAndPost({
                title: actionTitle,
                text: `Apakah Anda yakin ingin ${actionText} ${ids.length} kunjungan yang dipilih?`,
                icon: 'question',
                confirmText: action === 'validate' ? 'Ya, Validasi' : 'Ya, Hapus',
                postUrl: `{{ route('app.kunjungan.index') }}/bulk-validasi`,
                postData: {
                    ids: ids,
                    action: action
                },
                onSuccessReload: true,
                failMessage: `Terjadi kesalahan saat ${actionText} kunjungan.`
            });
        }

        function validateSingle() {
            if (!currentDetailId) {
                Swal.fire('Error!', 'ID kunjungan tidak ditemukan.', 'error');
                return;
            }

            confirmAndPost({
                title: 'Konfirmasi Validasi',
                text: 'Apakah Anda yakin ingin memvalidasi kunjungan ini?',
                icon: 'question',
                confirmText: 'Ya, Validasi',
                postUrl: `{{ route('app.kunjungan.index') }}/validate/${currentDetailId}`,
                postData: {},
                onSuccessHideModal: true,
                onSuccessReload: true,
                failMessage: 'Terjadi kesalahan saat memvalidasi kunjungan.'
            });
        }

        function rejectSingle() {
            if (!currentDetailId) {
                Swal.fire('Error!', 'ID kunjungan tidak ditemukan.', 'error');
                return;
            }

            confirmAndPost({
                title: 'Konfirmasi Penghapusan',
                text: 'Apakah Anda yakin menghapus kunjungan ini?',
                icon: 'warning',
                confirmText: 'Ya, Hapus',
                postUrl: `{{ route('app.kunjungan.index') }}/reject/${currentDetailId}`,
                postData: {},
                onSuccessHideModal: true,
                onSuccessReload: true,
                failMessage: 'Terjadi kesalahan saat menolak kunjungan.'
            });
        }
    </script>
@endpush

@push('scripts')
    <script>
        $('#dataTableBuilder').on('preXhr.dt', function(e, settings, data) {
            var vals = {
                filter_jenis_kelamin: $('#filter_jenis_kelamin_validasi').val() || '',
                filter_identitas: $('#filter_identitas_validasi').val() || '',
                filter_jenis_kunjungan: $('#filter_jenis_kunjungan_validasi').val() || '',
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

        $(document).on('click', '.act-filter_reset[data-table="dataTableBuilder"]', function() {
            $('#filter_jenis_kelamin_validasi, #filter_identitas_validasi, #filter_jenis_kunjungan_validasi')
                .val('').trigger('change');
            $('#dataTableBuilder-filter-badge').addClass('d-none');
        });
    </script>
@endpush
