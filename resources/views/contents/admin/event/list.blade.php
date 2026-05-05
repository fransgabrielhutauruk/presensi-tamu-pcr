@extends(request()->query('snap') == true ? 'layouts.snap' : 'layouts.apps')

@section('toolbar')
    <x-theme.toolbar :breadCrump="$pageData->breadCrump" :title="$pageData->title">
        <x-slot:tools>
        </x-slot:tools>
    </x-theme.toolbar>
@endsection

@section('content')
    @php
        use App\Enums\UserRole;

        $kategoriOptions = $pageData->dataKategori ?? [];
        $hiddenFromCivitas = hasAnyActiveRole(UserRole::getAdminEksekutifSecurityRoles());
    @endphp

    <div id="kt_app_content_container" class="app-container container-fluid" data-cue="slideInLeft" data-duration="1000"
        data-delay="0">
        @include('contents.admin.event.tabs')
        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="event" jf-list="datatable">
            @slot('action')
                @if ($hiddenFromCivitas)
                    <select id="filterKategori" class="form-select form-select-sm me-2" style="max-width: 250px;">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriOptions as $row)
                            <option value="{{ $row['id'] }}">{{ $row['text'] }}</option>
                        @endforeach
                    </select>
                @endif
                <x-btn type="primary" class="act-add me-2" jf-add="event">
                    <i class="bi bi-plus fs-2"></i> Tambah Event
                </x-btn>
                @if ($hiddenFromCivitas)
                    <x-btn.refresh-datatable />
                @endif
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalForm" type="centered" :static="true" size="lg" jf-modal="event" title="Event">
        <form id="formData" class="needs-validation" jf-form="event">
            <input type="hidden" name="id" value="">
            <div class="mb-4">
                <x-form.input type="text" label="Nama Event" name="nama_event" value="" required></x-form.input>
            </div>
            <div class="mb-4">
                <x-form.select name="eventkategori_id" label="Kategori Event" required>
                    @foreach ($kategoriOptions as $row)
                        <option value="{{ $row['id'] }}">
                            {{ $row['text'] }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <x-form.input type="date" label="Tanggal Event" name="tanggal_event" value=""
                        required></x-form.input>
                </div>
                <div class="col-md-4 mb-4">
                    <x-form.input type="time" label="Waktu Mulai" name="waktu_mulai_event" value=""
                        required></x-form.input>
                </div>
                <div class="col-md-4 mb-4">
                    <x-form.input type="time" label="Waktu Selesai" name="waktu_selesai_event" value=""
                        required></x-form.input>
                </div>
            </div>
            <div class="mb-4">
                <x-form.input type="text" label="Lokasi Event" name="lokasi_event" value=""
                    required></x-form.input>
            </div>
            <div class="mb-4" id="field-link-dokumentasi" style="display: none;">
                <x-form.input type="url" label="Link Dokumentasi (Google Drive)" name="link_dokumentasi_event"
                    value="" placeholder="https://drive.google.com/..."></x-form.input>
                <div class="form-text">Link dokumentasi event. Contoh: https://drive.google.com/drive/folders/tes</div>
            </div>
            <div class="mb-4">
                <x-form.textarea label="Deskripsi Event" name="deskripsi_event" value=""
                    rows="4"></x-form.textarea>
            </div>
        </form>
        @slot('action')
            <x-btn.form action="save" class="act-save" jf-save="event" />
        @endslot
    </x-modal>
@endsection

@push('scripts')
    <x-script.crud2></x-script.crud2>
    <script>
        const eventListTableSelector = 'table[jf-list="datatable"]';
        const documentationFieldSelector = '#field-link-dokumentasi';

        function toggleDocumentationField(shouldShow) {
            $(documentationFieldSelector).toggle(Boolean(shouldShow));
        }

        function filterEventTable(kategoriId) {
            const table = $(eventListTableSelector).DataTable();

            if (!table) {
                return;
            }

            const currentUrl = table.ajax.url().split('?')[0];
            const nextUrl = kategoriId ? `${currentUrl}?kategori=${kategoriId}` : currentUrl;

            table.ajax.url(nextUrl).load();
        }

        jForm.init({
            name: "event",
            base_url: `{{ route('app.event.index') }}`
        });

        $(document).on('click', '[jf-edit]', function() {
            toggleDocumentationField(true);
        });

        $(document).on('click', '[jf-add]', function() {
            toggleDocumentationField(false);
        });

        $('#filterKategori').on('change', function() {
            filterEventTable($(this).val());
        });
    </script>
@endpush
