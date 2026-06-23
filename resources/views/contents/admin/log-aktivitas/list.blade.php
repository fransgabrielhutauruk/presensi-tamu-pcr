@extends('layouts.apps')

@section('toolbar')
    <x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
        <x-slot:tools>
        </x-slot:tools>
    </x-theme.toolbar>
@endsection


@section('content')
    <div id="kt_app_content_container" class="app-container container-fluid" data-cue="slideInLeft" data-duration="1000"
        data-delay="0">
        <div class="row">
            <div class="col-md">
                <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="log_aktivitas"
                    jf-list="datatable" data-cy="table-log-aktivitas-list" :server_side="true" :default_order="[[2, 'desc']]">
                    @slot('filter')
                        <div class="row g-4">
                            <div class="col-md-2">
                                <label class="form-label fs-7 fw-semibold">User</label>
                                <select id="filter_user" name="filter_user" class="form-select form-select-sm"
                                    data-control="select2" data-placeholder="Semua User" data-allow-clear="true"
                                    data-cy="select-filter-log-user">
                                    <option value="">Semua User</option>
                                    @foreach ($pageData->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fs-7 fw-semibold">Aksi</label>
                                <select id="filter_event" name="filter_event" class="form-select form-select-sm"
                                    data-control="select2" data-placeholder="Semua Aksi" data-allow-clear="true"
                                    data-cy="select-filter-log-aksi">
                                    <option value="">Semua Aksi</option>
                                    <option value="login">Login</option>
                                    <option value="logout">Logout</option>
                                    <option value="created">Tambah Data</option>
                                    <option value="updated">Ubah Data</option>
                                    <option value="deleted">Hapus Data</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fs-7 fw-semibold">Subjek</label>
                                <select id="filter_subject" name="filter_subject" class="form-select form-select-sm"
                                    data-control="select2" data-placeholder="Semua Subjek" data-allow-clear="true"
                                    data-cy="select-filter-log-subjek">
                                    <option value="">Semua Subjek</option>
                                    @foreach ($pageData->subjects as $subject)
                                        <option value="{{ $subject }}">{{ class_basename($subject) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-7 fw-semibold">Dari Tanggal</label>
                                <input type="date" id="filter_date_from" name="filter_date_from"
                                    class="form-control form-control-sm" data-cy="input-filter-log-date-from" />
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-7 fw-semibold">Sampai Tanggal</label>
                                <input type="date" id="filter_date_to" name="filter_date_to"
                                    class="form-control form-control-sm" data-cy="input-filter-log-date-to" />
                            </div>
                        </div>
                    @endslot
                    @slot('action')
                        <x-btn.refresh-datatable />
                    @endslot
                </x-table.dttable>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true" data-cy="modal-log-detail">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Log Aktivitas</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" data-cy="btn-close-modal-log-detail-header">
                        <i class="bi bi-x-lg"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-3 fw-bold">Waktu:</div>
                        <div class="col-md-9" id="detail-created-at"></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-3 fw-bold">User:</div>
                        <div class="col-md-9">
                            <div id="detail-user"></div>
                            <small class="text-muted" id="detail-user-email"></small>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-3 fw-bold">Aktivitas:</div>
                        <div class="col-md-9" id="detail-description"></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-3 fw-bold">Subjek:</div>
                        <div class="col-md-9" id="detail-subject"></div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-3 fw-bold">Properties:</div>
                        <div class="col-md-9">
                            <pre id="detail-properties" class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#dataTableBuilder').on('preXhr.dt', function (e, settings, data) {
                var vals = {
                    filter_user:      $('#filter_user').val() || '',
                    filter_event:     $('#filter_event').val() || '',
                    filter_subject:   $('#filter_subject').val() || '',
                    filter_date_from: $('#filter_date_from').val() || '',
                    filter_date_to:   $('#filter_date_to').val() || '',
                };
                $.extend(data, vals);

                var count = Object.values(vals).filter(v => v !== '').length;
                $(`.dataTableBuilder-trigger_filter #filter-count`).text(count > 0 ? '(' + count + ')' : '');
            });

            $(document).on('click', '.act-filter_reset[data-table="dataTableBuilder"]', function () {
                $('#filter_user, #filter_event, #filter_subject, #filter_date_from, #filter_date_to').val('').trigger('change');
            });
        });

        function viewDetail(id) {
            $.ajax({
                url: '{{ route('app.log-aktivitas.data') }}/detail',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.status) {
                        const data = response.data;
                        $('#detail-created-at').text(data.created_at);
                        $('#detail-user').text(data.user);
                        $('#detail-user-email').text(data.user_email);
                        $('#detail-description').text(data.description);
                        $('#detail-subject').text(data.subject_type + ' #' + (data.subject_id || '-'));

                        if (data.properties && Object.keys(data.properties).length > 0) {
                            $('#detail-properties').text(JSON.stringify(data.properties, null, 2));
                        } else {
                            $('#detail-properties').text('Tidak ada properties');
                        }

                        $('#modal-detail').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat detail log'
                    });
                }
            });
        }
    </script>
@endpush
