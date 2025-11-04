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
        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="opsi-kunjungan" jf-list="datatable">
            @slot('action')
                <x-btn type="primary" class="act-add" jf-add="opsi-kunjungan">
                    <i class="bi bi-plus fs-2"></i> Tambah Opsi
                </x-btn>
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalForm" type="centered" :static="true" size="lg" jf-modal="opsi-kunjungan"
        title="Opsi Kunjungan">
        <form id="formData" class="needs-validation" jf-form="opsi-kunjungan">
            <input type="hidden" name="id" value="">
            <div class="mb-4">
                <x-form.input type="text" label="Nama Opsi" name="nama_opsi" value="" required
                    placeholder="Masukkan nama opsi"></x-form.input>
            </div>
            <div class="mb-5">
                <x-form.textarea name="deskripsi_opsi" label="Deskripsi Opsi" value="" />
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label mb-0 required">Daftar Nilai Opsi</label>
                    <button type="button" class="btn btn-sm btn-primary" id="addOptionItem">
                        <i class="ki-outline ki-plus fs-6"></i> Tambah Opsi
                    </button>
                </div>
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-5 text-center">
                                <label class="form-label fs-7 fw-semibold text-muted">Indonesia</label>
                            </div>
                            <div class="col-5 text-center">
                                <label class="form-label fs-7 fw-semibold text-muted">English</label>
                            </div>
                            <div class="col-2 text-center">
                                <label class="form-label fs-7 fw-semibold text-muted text-center">Aksi</label>
                            </div>
                        </div>
                        <div id="optionsList" class="min-h-100px">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="nilai_opsi" id="hiddenNilaiOpsi" value="">
            </div>
        </form>
        @slot('action')
            <x-btn.form action="save" class="act-save" jf-save="opsi-kunjungan" />
        @endslot
    </x-modal>
@endsection

@push('scripts')
    <x-script.crud2></x-script.crud2>
    <script>
        let optionsData = [];

        jForm.init({
            name: "opsi-kunjungan",
            url: {
                add: `{{ route('app.kunjungan.store', ['param1' => 'opsi']) }}`,
                edit: `{{ route('app.kunjungan.data', ['param1' => 'opsi-detail']) }}`,
                update: `{{ route('app.kunjungan.update', ['param1' => 'opsi']) }}`,
                delete: `{{ route('app.kunjungan.destroy', ['param1' => 'opsi']) }}`,
            },
            onEdit: function(data) {
                if (data.nilai_opsi) {
                    if (typeof data.nilai_opsi === 'string') {
                        optionsData = JSON.parse(data.nilai_opsi);
                    } else {
                        optionsData = data.nilai_opsi;
                    }
                } else {
                    optionsData = [];
                }
                renderOptionsList();
            },
            onAdd: function() {
                optionsData = [];
                renderOptionsList();
            },
            beforeSave: function() {
                collectOptionsData();
            }
        });

        function renderOptionsList() {
            const container = document.getElementById('optionsList');
            container.innerHTML = '';

            if (optionsData.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="ki-outline ki-information fs-2x mb-2"></i>
                        <p>Belum ada item opsi. Klik "Tambah Item" untuk menambah.</p>
                    </div>
                `;
                return;
            }

            optionsData.forEach((option, index) => {
                const optionHtml = createOptionItemHtml(option, index);
                container.insertAdjacentHTML('beforeend', optionHtml);
            });

            collectOptionsData();
        }

        function createOptionItemHtml(option, index) {
            const idLabel = option.id || option.label || '';
            const enLabel = option.en || option.label || '';
            
            return `
                <div class="mb-3 option-item" data-index="${index}">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <input type="text" class="form-control form-control-sm option-id required" 
                                   value="${idLabel}" placeholder="e.g: Direktur">
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control form-control-sm option-en required" 
                                   value="${enLabel}" placeholder="e.g: Director">
                        </div>
                        <div class="col-2 text-center">
                            <button type="button" class="btn btn-sm btn-light-danger option-remove w-100" title="Hapus item">
                                <i class="ki-outline ki-trash fs-7"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function collectOptionsData() {
            const options = [];
            document.querySelectorAll('.option-item').forEach(function(item, index) {
                const id = item.querySelector('.option-id').value.trim();
                const en = item.querySelector('.option-en').value.trim();
                
                if (id || en) {
                    options.push({
                        id: id,
                        en: en
                    });
                }
            });
            
            document.getElementById('hiddenNilaiOpsi').value = JSON.stringify(options);
            optionsData = options;
            return options;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('addOptionItem').addEventListener('click', function() {
                optionsData.push({
                    id: '',
                    en: ''
                });
                renderOptionsList();
            });
            document.getElementById('optionsList').addEventListener('click', function(e) {
                const target = e.target.closest('.option-remove');
                if (!target) return;

                const item = target.closest('.option-item');
                const index = parseInt(item.dataset.index);

                if (target.classList.contains('option-remove')) {
                    optionsData.splice(index, 1);
                    renderOptionsList();
                }
            });

            document.getElementById('optionsList').addEventListener('input', function(e) {
                if (e.target.classList.contains('option-id') || 
                    e.target.classList.contains('option-en')) {
                    collectOptionsData();
                }
            });
        });
    </script>
@endpush
