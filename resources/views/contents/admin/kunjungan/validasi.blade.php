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
    
    <div class="alert alert-info d-flex align-items-center mb-5">
        <i class="ki-outline ki-information fs-1 text-info me-4"></i>
        <div>
            <h5 class="mb-1">Validasi Kunjungan</h5>
            <p class="mb-0">Halaman ini menampilkan daftar kunjungan yang belum divalidasi. Pastikan data tamu valid dan bukan data dummy sebelum melakukan validasi.</p>
        </div>
    </div>

    <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan-validasi" jf-list="datatable">
        @slot('action')
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-warning fs-7">{{ \App\Models\Kunjungan::where('status_validasi', false)->count() }} kunjungan belum divalidasi</span>
        </div>
        @endslot
    </x-table.dttable>
</div>
@endsection

@push('scripts')
<x-script.crud2></x-script.crud2>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        jForm.init({
            name: "kunjungan-validasi",
            base_url: `{{ route('app.kunjungan.validasi') }}`
        })

        $(document).on('click', '[data-action="validate"]', function(e) {
            e.preventDefault();
            
            const url = $(this).attr('href');
            const id = $(this).data('id');
            
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
                    $.post(url, {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }).done(function(response) {
                        Swal.fire('Berhasil!', 'Kunjungan berhasil divalidasi.', 'success');
                        $('#datatable').DataTable().ajax.reload(null, false);
                    }).fail(function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat memvalidasi kunjungan.', 'error');
                    });
                }
            });
        });
    });
</script>
@endpush