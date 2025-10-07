@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')

@section('toolbar')
<x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
    <x-slot:tools>
    </x-slot:tools>
</x-theme.toolbar>
@endsection

@section('content')
<!--begin::Content container-->
<div id="kt_app_content_container" class="app-container container-fluid" data-cue="slideInLeft" data-duration="1000"
    data-delay="0">
    <div class="row">
        <div class="col-md">
            <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="user" jf-list="datatable">
                @slot('action')
                <x-btn type="primary" class="act-add w-100 w-md-auto" jf-add="user">
                    <i class="bi bi-plus fs-2"></i> Tambah data
                </x-btn>
                @endslot
            </x-table.dttable>
        </div>
    </div>
</div>

<x-modal id="modalForm" type="centered" :static="true" size="" jf-modal="user" title="Pengguna">
    <form id="formData" class="needs-validation" jf-form="user">
        <input type="hidden" name="id" value="">
        <x-form.input type="text" class="mb-2" name="name" label="Nama" required />
        <x-form.input type="email" class="mb-2" name="email" label="Email" required />
        <div class="mb-4">
            <x-form.select name="roles[]" label="Role" required multiple=true>
                @foreach($pageData->roles as $role)
                <option value="{{ $role->name }}">
                    {{ $role->name }}
                </option>
                @endforeach
            </x-form.select>
            <div class="form-text">
                Pilih role tambahan. </br> Role dasar (Mahasiswa/Staf) akan otomatis diberikan saat login pertama kali.
            </div>
        </div>
    </form>
    @slot('action')
    <x-btn.form action="save" class="act-save" jf-save="user" />
    @endslot
</x-modal>
@endsection

@push('scripts')
<x-script.crud2></x-script.crud2>
<script>
    jForm.init({
        name: "user",
        base_url: `{{ route('app.user.index') }}`
    })
</script>
@endpush