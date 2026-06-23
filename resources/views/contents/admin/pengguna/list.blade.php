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
    <div class="row">
        <div class="col-md">
            <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="user" jf-list="datatable" data-cy="table-user-list" :server_side="true" :default_order="[[2, 'asc']]">
                @slot('filter')
                    <div class="col-auto">
                        <label class="form-label form-label-sm mb-1">Role</label>
                        <select id="filterRole" class="form-select form-select-sm" data-control="select2"
                            data-allow-clear="true" data-placeholder="Semua Role" data-cy="select-filter-role-pengguna">
                            <option value="">Semua Role</option>
                            @foreach ($pageData->allRoles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endslot
                @slot('action')
                <x-btn type="primary" class="act-add w-100 w-md-auto" jf-add="user" data-cy="btn-tambah-user">
                    <i class="bi bi-plus fs-2"></i> Tambah data
                </x-btn>
                @endslot
            </x-table.dttable>
        </div>
    </div>
</div>

<x-modal id="modalForm" type="centered" :static="true" size="" jf-modal="user" title="Pengguna" data-cy="modal-user-form">
    <form id="formData" class="needs-validation" jf-form="user" data-cy="form-user">
        <input type="hidden" name="id" value="">
        <x-form.input type="text" class="mb-2" name="name" label="Nama" required data-cy="input-user-name" />
        <x-form.input type="email" class="mb-2" name="email" label="Email" required data-cy="input-user-email" />
        <div class="mb-4">
            <x-form.select name="roles[]" label="Role" required multiple=true data-cy="select-user-roles">
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
    <x-btn.form action="save" class="act-save" jf-save="user" data-cy="btn-simpan-user" />
    @endslot
</x-modal>
@endsection

@push('scripts')
<x-script.crud2></x-script.crud2>
<script>
    jForm.init({
        name: "user",
        base_url: `{{ route('app.user.index') }}`
    });

    $(document).ready(function() {
        $('#dataTableBuilder').on('preXhr.dt', function (e, settings, data) {
            var vals = {
                filter_role: $('#filterRole').val() || '',
            };
            $.extend(data, vals);

            var count = Object.values(vals).filter(v => v !== '').length;
            $(`.dataTableBuilder-trigger_filter #filter-count`).text(count > 0 ? '(' + count + ')' : '');
        });

        $(document).on('click', '.act-filter_reset[data-table="dataTableBuilder"]', function () {
            $('#filterRole').val('').trigger('change');
        });
    });
</script>
@endpush
