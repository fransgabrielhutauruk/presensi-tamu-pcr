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
                    jf-list="datatable">
                    @slot('action')
                        <x-btn.refresh-datatable />
                    @endslot
                </x-table.dttable>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Detail Log Aktivitas</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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
