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
                        <span id="selectedCount" class="badge badge-light fs-7">0 dipilih</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" id="bulkValidateBtn" data-action="validate"
                            disabled>
                            <i class="bi bi-check2-circle fs-4"></i> Validasi Terpilih
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="bulkRejectBtn" data-action="reject"
                            disabled>
                            <i class="bi bi-x-circle fs-4"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan-validasi"
            jf-list="datatable">
            @slot('action')
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalDetailValidasi" type="centered" :static="true" size="lg" title="Detail Kunjungan">
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
            <x-btn.form action="save" id="validateSingleBtn" text="Validasi" title="Validasi" />
            <x-btn.form action="cancle" id="rejectSingleBtn" text="Hapus" title="Hapus" />
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
            jForm.init({
                name: "kunjungan-validasi",
                base_url: `{{ route('app.kunjungan.index') }}`,
                onDetail: function(data) {
                    const clickedElement = $('[jf-detail]:last');
                    if (clickedElement.length > 0) {
                        currentDetailId = clickedElement.attr('jf-detail');
                    }
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

        function showDetailModal(data) {
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
                            '<div class="col-4 fw-bold text-muted">' + formattedLabel + ':</div>' +
                            '<div class="col-8 fw-bold">' + detail.nilai + '</div>' +
                            '</div>';
                        detailContainer.append(detailHtml);
                    });
                }
            }

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

            Swal.fire({
                title: actionTitle,
                text: `Apakah Anda yakin ingin ${actionText} ${ids.length} kunjungan yang dipilih?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: action === 'validate' ? 'Ya, Validasi' : 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`{{ route('app.kunjungan.index') }}/bulk-validasi`, {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        ids: ids,
                        action: action
                    }).done(function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            $('table[jf-data="kunjungan-validasi"]').DataTable().ajax.reload(null, false);
                            clearSelection();
                        } else {
                            Swal.fire('Gagal!', response.message || 'Terjadi kesalahan', 'error');
                        }
                    }).fail(function() {
                        Swal.fire('Gagal!', `Terjadi kesalahan saat ${actionText} kunjungan.`, 'error');
                    });
                }
            });
        }

        function validateSingle() {
            if (!currentDetailId) {
                Swal.fire('Error!', 'ID kunjungan tidak ditemukan.', 'error');
                return;
            }

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
                    $.post(`{{ route('app.kunjungan.index') }}/validate/${currentDetailId}`, {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }).done(function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', 'Kunjungan berhasil divalidasi.', 'success');
                            $('#modalDetailValidasi').modal('hide');
                            $('table[jf-data="kunjungan-validasi"]').DataTable().ajax.reload(null, false);
                            clearSelection();
                        } else {
                            Swal.fire('Gagal!', response.message || 'Terjadi kesalahan', 'error');
                        }
                    }).fail(function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat memvalidasi kunjungan.', 'error');
                    });
                }
            });
        }

        function rejectSingle() {
            if (!currentDetailId) {
                Swal.fire('Error!', 'ID kunjungan tidak ditemukan.', 'error');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Penghapusan',
                text: 'Apakah Anda yakin menghapus kunjungan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`{{ route('app.kunjungan.index') }}/reject/${currentDetailId}`, {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }).done(function(response) {
                        if (response.status) {
                            Swal.fire('Berhasil!', 'Kunjungan berhasil dihapus.', 'success');
                            $('#modalDetailValidasi').modal('hide');
                            $('table[jf-data="kunjungan-validasi"]').DataTable().ajax.reload(null, false);
                            clearSelection();
                        } else {
                            Swal.fire('Gagal!', response.message || 'Terjadi kesalahan', 'error');
                        }
                    }).fail(function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menolak kunjungan.', 'error');
                    });
                }
            });
        }
    </script>
@endpush
