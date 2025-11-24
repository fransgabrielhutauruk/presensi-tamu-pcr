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
        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="feedback" jf-list="datatable">
            @slot('action')
                <x-btn.refresh-datatable />
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="feedback"
        title="Detail Feedback">
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
                    <label class="fw-bold text-muted">Waktu Kunjungan:</label>
                    <div data-field="tanggal_kunjungan" class="fw-bold">-</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Identitas:</label>
                    <div data-field="identitas" class="fw-bold">-</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Jenis Kunjungan:</label>
                    <div data-field="jenis_kunjungan" class="fw-bold">-</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">Nama Event:</label>
                    <div data-field="event_nama" class="fw-bold">-</div>
                </div>
            </div>
        </div>

        <div class="mb-7">
            <h5 class="mb-4">Feedback</h5>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="fw-bold text-muted">Rating:</label>
                    <div data-field="rating" class="fw-bold fs-2">-</div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="fw-bold text-muted">Komentar:</label>
                    <div data-field="komentar" class="fw-bold bg-light p-4 rounded">-</div>
                </div>
            </div>
        </div>

        @slot('action')
        @endslot
    </x-modal>
@endsection

@push('scripts')
    <x-script.crud2></x-script.crud2>
    <script>
        function formatLabel(str) {
            return str
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }

        jForm.init({
            name: "feedback",
            base_url: `{{ route('app.feedback.index') }}`,
            onDetail: function(data) {
                console.log('Detail data received:', data);

                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= data.rating) {
                        stars += '<i class="ki-solid ki-star text-warning fs-2"></i>';
                    } else {
                        stars += '<i class="ki-outline ki-star text-muted fs-2"></i>';
                    }
                }
                $('[data-field="rating"]').html(stars);
            }
        });
    </script>
@endpush
