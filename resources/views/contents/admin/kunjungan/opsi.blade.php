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
        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" jf-data="opsi-kunjungan" jf-list="datatable" data-cy="table-opsi-kunjungan-list">
            @slot('action')
                <x-btn type="primary" class="act-add" jf-add="opsi-kunjungan" data-cy="btn-add-opsi-kunjungan">
                    <i class="bi bi-plus fs-2"></i> Tambah Opsi
                </x-btn>
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalForm" type="centered" :static="true" size="lg" jf-modal="opsi-kunjungan"
        title="Opsi Kunjungan" data-cy="modal-opsi-kunjungan-form">
        <form id="formData" class="needs-validation" jf-form="opsi-kunjungan" data-cy="form-opsi-kunjungan">
            <input type="hidden" name="id" value="">
            <div class="mb-4">
                <x-form.input type="text" label="Nama Opsi" name="nama_opsi" value="" required
                    placeholder="Masukkan nama opsi" data-cy="input-nama_opsi"></x-form.input>
            </div>
            <div class="mb-5">
                <x-form.textarea name="deskripsi_opsi" label="Deskripsi Opsi" value="" data-cy="textarea-deskripsi_opsi" />
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label mb-0 required">Daftar Nilai Opsi</label>
                    <button type="button" class="btn btn-sm btn-primary" id="addOptionItem" data-cy="btn-add-option-item">
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
                        <div id="optionsList" class="min-h-100px" data-cy="options-list-container">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="nilai_opsi" id="hiddenNilaiOpsi" value="" data-cy="input-hidden-nilai_opsi">
            </div>
        </form>
        @slot('action')
            <x-btn.form action="save" class="act-save" jf-save="opsi-kunjungan" data-cy="btn-save-opsi-kunjungan" />
        @endslot
    </x-modal>
@endsection

@push('scripts')
    <x-script.crud2></x-script.crud2>
    <script>
        const OPTION_TEMPLATE = {
            id: '',
            en: '',
        };

        const SELECTORS = {
            list: 'optionsList',
            hidden: 'hiddenNilaiOpsi',
            addButton: 'addOptionItem',
        };

        let optionsData = [];

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function normalizeOption(option = {}) {
            const fallback = option.label ?? '';

            return {
                id: option.id ?? fallback,
                en: option.en ?? fallback,
            };
        }

        function parseOptionsData(value) {
            if (!value) {
                return [];
            }

            if (Array.isArray(value)) {
                return value.map(normalizeOption);
            }

            if (typeof value === 'string') {
                try {
                    const parsed = JSON.parse(value);
                    return Array.isArray(parsed) ? parsed.map(normalizeOption) : [];
                } catch (error) {
                    return [];
                }
            }

            return [];
        }

        function getOptionsListElement() {
            return document.getElementById(SELECTORS.list);
        }

        function getHiddenOptionsField() {
            return document.getElementById(SELECTORS.hidden);
        }

        function setOptionsData(nextOptions) {
            optionsData = nextOptions.map(normalizeOption);
            renderOptionsList();
        }

        function renderEmptyState(container) {
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="ki-outline ki-information fs-2x mb-2"></i>
                    <p>Belum ada item opsi. Klik "Tambah Item" untuk menambah.</p>
                </div>
            `;
        }

        function renderOptionItem(option, index) {
            const normalizedOption = normalizeOption(option);

            return `
                <div class="mb-3 option-item" data-index="${index}" data-cy="option-item-row-${index}">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <input type="text" class="form-control form-control-sm option-id required"
                                   data-cy="input-option-id-${index}"
                                   value="${escapeHtml(normalizedOption.id)}" placeholder="e.g: Direktur">
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control form-control-sm option-en required"
                                   data-cy="input-option-en-${index}"
                                   value="${escapeHtml(normalizedOption.en)}" placeholder="e.g: Director">
                        </div>
                        <div class="col-2 text-center">
                            <button type="button" class="btn btn-sm btn-light-danger option-remove w-100" title="Hapus item" data-cy="btn-remove-option-item-${index}">
                                <i class="ki-outline ki-trash fs-7"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        jForm.init({
            name: "opsi-kunjungan",
            url: {
                add: `{{ route('app.kunjungan.store', ['param1' => 'opsi']) }}`,
                edit: `{{ route('app.kunjungan.data', ['param1' => 'opsi-detail']) }}`,
                update: `{{ route('app.kunjungan.update', ['param1' => 'opsi']) }}`,
                delete: `{{ route('app.kunjungan.destroy', ['param1' => 'opsi']) }}`,
            },
            onEdit: function(data) {
                setOptionsData(parseOptionsData(data.nilai_opsi));
            },
            onAdd: function() {
                setOptionsData([]);
            },
            beforeSave: function() {
                collectOptionsData();
            }
        });

        function renderOptionsList() {
            const container = getOptionsListElement();
            container.innerHTML = '';

            if (optionsData.length === 0) {
                renderEmptyState(container);
                return;
            }

            container.insertAdjacentHTML('beforeend', optionsData.map(renderOptionItem).join(''));

            collectOptionsData();
        }

        function collectOptionsData() {
            const options = Array.from(document.querySelectorAll('.option-item'))
                .map(function(item) {
                    const id = item.querySelector('.option-id').value.trim();
                    const en = item.querySelector('.option-en').value.trim();

                    if (!id && !en) {
                        return null;
                    }

                    return {
                        id: id,
                        en: en,
                    };
                })
                .filter(Boolean);

            getHiddenOptionsField().value = JSON.stringify(options);
            optionsData = options;
            return options;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById(SELECTORS.addButton).addEventListener('click', function() {
                setOptionsData([].concat(optionsData, [OPTION_TEMPLATE]));
            });
            getOptionsListElement().addEventListener('click', function(e) {
                const removeButton = e.target.closest('.option-remove');

                if (!removeButton) {
                    return;
                }

                const item = removeButton.closest('.option-item');
                const index = Number(item.dataset.index);

                setOptionsData(optionsData.filter(function(_, currentIndex) {
                    return currentIndex !== index;
                }));
            });

            getOptionsListElement().addEventListener('input', function(e) {
                if (e.target.classList.contains('option-id') ||
                    e.target.classList.contains('option-en')) {
                    collectOptionsData();
                }
            });
        });
    </script>
@endpush
