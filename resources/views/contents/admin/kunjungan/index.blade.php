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
    <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="kunjungan" jf-list="datatable">
        @slot('action')
        <!-- <x-btn type="primary" class="act-add" jf-add="kunjungan">
            <i class="bi bi-plus fs-2"></i> Tambah Kunjungan
        </x-btn> -->
        @endslot
    </x-table.dttable>
</div>

<x-modal id="modalForm" type="centered" :static="true" size="lg" jf-modal="kunjungan" title="Kunjungan">
    <form id="formData" class="needs-validation" jf-form="kunjungan">
        <input type="hidden" name="id" value="">
        <div class="mb-4">
            <x-form.input type="text" label="Nama Tamu" name="nama" value="" required></x-form.input>
        </div>
        <div class="mb-4">
            <x-form.select name="jenis_kelamin" label="Jenis Kelamin" required>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </x-form.select>
        </div>
        <div class="mb-4">
            <x-form.input type="email" label="Email" name="email" value="" required></x-form.input>
        </div>
        <div class="mb-4">
            <x-form.input type="text" label="Nomor Telepon" name="nomor_telepon" value="" required></x-form.input>
        </div>
        <div class="mb-4">
            <x-form.select name="kategori_tujuan" label="Kategori Tujuan" required>
                <option value="instansi">Kunjungan Resmi Instansi</option>
                <option value="bisnis">Keperluan Bisnis/Kemitraan</option>
                <option value="ortu">Orang Tua/Wali Mahasiswa</option>
                <option value="calon_ortu">Calon Orang Tua/Wali Mahasiswa</option>
                <option value="lainnya">Lainnya</option>
            </x-form.select>
        </div>
        <div class="mb-4">
            <x-form.textarea label="Keperluan" name="keperluan" value="" rows="3"></x-form.textarea>
        </div>
        <div class="mb-4">
            <x-form.select name="transportasi" label="Transportasi" required>
                <option value="Mobil">Mobil</option>
                <option value="Motor">Motor</option>
                <option value="Bus">Bus</option>
                <option value="Travel">Travel</option>
                <option value="Online Ride">Online Ride</option>
                <option value="Jalan Kaki">Jalan Kaki</option>
                <option value="Lainnya">Lainnya</option>
            </x-form.select>
        </div>
    </form>
    @slot('action')
    <x-btn.form action="save" class="act-save" jf-save="kunjungan" />
    @endslot
</x-modal>

<!-- Modal Detail -->
<x-modal id="modalDetail" type="centered" :static="true" size="lg" jf-detail-modal="kunjungan" title="Detail Kunjungan">
    <!-- Data Tamu -->
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

    <!-- Data Kunjungan -->
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

    <!-- Data Waktu -->
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
                <label class="fw-bold text-muted">Waktu Keluar:</label>
                <div data-field="waktu_keluar" class="fw-bold">-</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Waktu Checkout:</label>
                <div data-field="checkout_time" class="fw-bold">-</div>
            </div>
        </div>
    </div>

    <!-- Data Event (jika ada) -->
    <div class="mb-7">
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
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">Tanggal Event:</label>
                <div data-field="event_tanggal" class="fw-bold">-</div>
            </div>
        </div>
    </div>

    <!-- Data Detail Tambahan -->
    <div class="mb-7">
        <h5 class="mb-4">Detail Tambahan</h5>
        <div data-details-container class="border rounded p-4 bg-light">
            <!-- Detail akan ditampilkan di sini -->
        </div>
    </div>

    @slot('action')
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
    @endslot
</x-modal>
@endsection

@push('scripts')
<x-script.crud2></x-script.crud2>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        jForm.init({
            name: "kunjungan",
            base_url: `{{ route('app.kunjungan.index') }}`,
            onDetail: function(data) {
                console.log('Detail data received:', data);
            },
            onAdd: function() {
                console.log('Form tambah dibuka');
            },
            onEdit: function(data) {
                console.log('Form edit dibuka', data);
            }
        })
    });
</script>
@endpush