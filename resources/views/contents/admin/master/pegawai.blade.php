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
        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="pegawai" jf-list="datatable" :server_side="true" :default_order="[[2, 'asc']]">
            @slot('action')
                <x-btn type="success" id="btn-sync-pegawai">
                    <i class="bi bi-arrow-repeat fs-2"></i> Sync Data Pegawai
                </x-btn>
                <x-btn.refresh-datatable />
            @endslot
        </x-table.dttable>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#btn-sync-pegawai').on('click', function() {
                const btn = $(this);
                const originalContent = btn.html();
                
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin melakukan sinkronisasi data pegawai dari API?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Sync Sekarang',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable button and show loading
                        btn.prop('disabled', true);
                        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Syncing...');
                        
                        // Call sync API
                        ajaxRequest({
                            link: '{{ route("app.master.store", "sync-pegawai") }}',
                            method: 'POST',
                            swal_success: false,
                            callback: function(origin, resp) {
                                // Re-enable button
                                btn.prop('disabled', false);
                                btn.html(originalContent);
                                
                                if (resp.status) {
                                    const data = resp.data || {};
                                    const syncedCount = data.synced || 0;
                                    const failedCount = data.failed || 0;
                                    const totalCount = data.total || 0;
                                    
                                    let message = `<div class="text-start">`;
                                    message += `<p class="mb-2">${resp.message}</p>`;
                                    message += `<ul class="mb-0">`;
                                    message += `<li>Total data dari API: <strong>${totalCount}</strong></li>`;
                                    message += `<li>Berhasil disinkronkan: <strong class="text-success">${syncedCount}</strong></li>`;
                                    if (failedCount > 0) {
                                        message += `<li>Gagal: <strong class="text-danger">${failedCount}</strong></li>`;
                                    }
                                    message += `</ul></div>`;
                                    
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        html: message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    });
                                    
                                    // Reload DataTable
                                    $('table[jf-data="pegawai"]').DataTable().ajax.reload(null, false);
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: resp.message || 'Terjadi kesalahan saat sinkronisasi',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                // Re-enable button
                                btn.prop('disabled', false);
                                btn.html(originalContent);
                                
                                let errorMessage = 'Terjadi kesalahan saat sinkronisasi';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                
                                Swal.fire({
                                    title: 'Error!',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
