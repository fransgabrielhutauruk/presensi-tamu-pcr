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
        @include('contents.admin.event.tabs')
        <div class="row">
            <div class="col-md">
                <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="event-kategori"
                    jf-list="datatable">
                    @slot('action')
                        <x-btn type="primary" class="act-add w-100 w-md-auto" jf-add="event-kategori">
                            <i class="bi bi-plus fs-2"></i> Tambah data
                        </x-btn>
                    @endslot
                </x-table.dttable>
            </div>
        </div>
    </div>

    <x-modal id="modalForm" type="centered" :static="true" size="" jf-modal="event-kategori"
        title="Event Kategori">
        <form id="formData" class="needs-validation" jf-form="event-kategori">
            <input type="hidden" name="id" value="">
            <div class="mb-4">
                <x-form.input name="nama_kategori" label="Nama Kategori" value="" required />
            </div>
            <div class="mb-4">
                <x-form.textarea name="deskripsi_kategori" label="Deskripsi Kategori" value="" />
            </div>
        </form>
        @slot('action')
            <x-btn.form action="save" class="act-save" jf-save="event-kategori" />
        @endslot
    </x-modal>
@endsection

@push('scripts')
    <x-script.crud2></x-script.crud2>
    <script>
        jForm.init({
            name: "event-kategori",
            url: {
                add: `{{ route('app.event.store', ['param1' => 'kategori']) }}`,
                edit: `{{ route('app.event.data', ['param1' => 'kategori-detail']) }}`,
                update: `{{ route('app.event.update', ['param1' => 'kategori']) }}`,
                delete: `{{ route('app.event.destroy', ['param1' => 'kategori']) }}`
            },
        })
    </script>
@endpush
