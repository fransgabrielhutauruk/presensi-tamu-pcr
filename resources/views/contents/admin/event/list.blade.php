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
        <x-table.dttable :builder="$pageData->dataTable" class="align-middle" :responsive="false" :server_side="true" :default_order="[[2, 'desc']]"
            jf-data="event" jf-list="datatable" data-cy="table-event-list">
            @slot('action')
                <x-btn type="primary" class="act-add me-2" jf-add="event" data-cy="btn-tambah-event">
                    <i class="bi bi-plus fs-2"></i> Tambah Event
                </x-btn>
                @if ($hiddenFromCivitas)
                    <x-btn.refresh-datatable />
                @endif
            @endslot
            @slot('filter')
                <div class="row g-4">
                    @if ($hiddenFromCivitas)
                        <div class="col-md-2">
                            <label class="form-label fs-7 fw-semibold">Kategori Event</label>
                            <select id="filterKategori" name="filter_kategori" class="form-select form-select-sm"
                                data-control="select2" data-placeholder="Semua Kategori" data-allow-clear="true"
                                data-cy="select-filter-kategori-event">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoriOptions as $row)
                                    <option value="{{ $row['id'] }}">{{ $row['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Kategori Lokasi</label>
                        <select id="filter_kategori_lokasi_event" name="filter_kategori_lokasi"
                            class="form-select form-select-sm" data-control="select2" data-placeholder="Semua Kategori Lokasi"
                            data-allow-clear="true" data-cy="select-filter-event-kategori-lokasi">
                            <option value="">Semua Kategori Lokasi</option>
                            <option value="dalam_kampus">Dalam Kampus</option>
                            <option value="luar_kampus">Luar Kampus</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold">Status</label>
                        <select id="filter_status_event" name="filter_status" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua Status" data-allow-clear="true"
                            data-cy="select-filter-event-status">
                            <option value="">Semua Status</option>
                            <option value="mendatang" {{ request('filter_status') == 'mendatang' ? 'selected' : '' }}>Mendatang
                            </option>
                            <option value="berlangsung">Berlangsung</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold">Dari Tanggal</label>
                        <input type="date" id="filter_date_from" name="filter_date_from" class="form-control form-control-sm"
                            data-cy="input-filter-event-date-from" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold">Sampai Tanggal</label>
                        <input type="date" id="filter_date_to" name="filter_date_to" class="form-control form-control-sm"
                            data-cy="input-filter-event-date-to" />
                    </div>
                </div>
            @endslot
        </x-table.dttable>
    </div>

    <x-modal id="modalForm" type="centered" :static="true" size="lg" jf-modal="event" title="Event"
        data-cy="modal-event-form">
        <form id="formData" class="needs-validation" jf-form="event" data-cy="form-create-event">
            <input type="hidden" name="id" value="">
            <div class="mb-4">
                <x-form.input type="text" label="Nama Event" name="nama_event" value="" required
                    data-cy="input-nama_event"></x-form.input>
            </div>
            <div class="mb-4">
                <x-form.select name="eventkategori_id" label="Kategori Event" required data-cy="select-eventkategori_id">
                    @foreach ($kategoriOptions as $row)
                        <option value="{{ $row['id'] }}">
                            {{ $row['text'] }}
                        </option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="row mb-3 mb-4">
                <div class="col-md-auto mb-0">
                    <x-form.radio-group name="jenis_kegiatan" label="Jenis Kegiatan" required :options="['pmb' => 'PMB', 'non_pmb' => 'Non PMB']"
                        data-cy-prefix="radio-jenis_kegiatan" />
                </div>
                <div class="col-md-auto d-flex align-items-center">
                    <div class="form-text text-muted fs-8 mt-0">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Opsi PMB akan memunculkan isian tambahan (Minat PCR & Pilihan Prodi yang Diminati) pada form
                        presensi tamu non-civitas.
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <x-form.radio-group name="kategori_lokasi" label="Kategori Lokasi" required :options="['dalam_kampus' => 'Dalam Kampus', 'luar_kampus' => 'Luar Kampus']"
                    data-cy-prefix="radio-kategori_lokasi" />
            </div>
            <div class="mb-4">
                <x-form.input type="text" label="Lokasi Event" name="lokasi_event" value="" required
                    data-cy="input-lokasi_event"></x-form.input>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fs-7 fw-semibold mb-0 required">Tanggal Event</label>
                        <div class="form-check form-switch form-check-custom form-check-solid form-check-sm">
                            <input class="form-check-input border border-gray-400" type="checkbox" id="is_range"
                                style="height: 1.9rem" value="1" />
                            <label class="form-check-label fs-8 text-black" for="is_range">Lebih dari 1 Hari?</label>
                        </div>
                    </div>
                    <input type="text" class="form-control form-control-sm" name="tanggal_event" required
                        data-cy="input-tanggal_event" placeholder="Pilih rentang tanggal" />
                </div>
                <div class="col-md-4 mb-4">
                    <x-form.input type="time" label="Waktu Mulai" name="waktu_mulai_event" value="" required
                        data-cy="input-waktu_mulai_event"></x-form.input>
                </div>
                <div class="col-md-4 mb-4">
                    <x-form.input type="time" label="Waktu Selesai" name="waktu_selesai_event" value=""
                        required data-cy="input-waktu_selesai_event"></x-form.input>
                </div>
            </div>
            <div class="mb-4" id="field-link-dokumentasi" style="display: none;">
                <x-form.input type="url" label="Link Dokumentasi (Google Drive)" name="link_dokumentasi_event"
                    value="" placeholder="https://drive.google.com/..."
                    data-cy="input-link_dokumentasi_event"></x-form.input>
                <div class="form-text">Link dokumentasi event. Contoh: https://drive.google.com/drive/folders/tes</div>
            </div>
            <div class="mb-4">
                <x-form.textarea label="Deskripsi Event" name="deskripsi_event" value="" rows="4"
                    data-cy="textarea-deskripsi_event"></x-form.textarea>
            </div>
        </form>
        @slot('action')
            <x-btn.form action="save" class="act-save" jf-save="event" data-cy="btn-simpan-event" />
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

        jForm.init({
            name: "event",
            base_url: `{{ route('app.event.index') }}`,
            onEdit: function(data) {
                if (data && data.tanggal_event) {
                    let isRange = typeof data.tanggal_event === 'string' && data.tanggal_event.includes(
                        ' s/d ');
                    $('#is_range').prop('checked', isRange).trigger('change');

                    let fp = document.querySelector('input[name="tanggal_event"]')._flatpickr;
                    if (fp) {
                        let dates = data.tanggal_event;
                        if (isRange) {
                            dates = dates.split(' s/d ');
                        }
                        fp.setDate(dates, true);
                    } else {
                        $('input[name="tanggal_event"]').val(data.tanggal_event);
                    }
                }
            }
        });

        $(document).on('click', '[jf-edit]', function() {
            toggleDocumentationField(true);
        });

        $(document).on('click', '[jf-add]', function() {
            toggleDocumentationField(false);
            $('input[name="kategori_lokasi"]').prop('checked', false);
            $('input[name="jenis_kegiatan"]').prop('checked', false);
            $('#is_range').prop('checked', false).trigger('change');
            let fp = document.querySelector('input[name="tanggal_event"]')._flatpickr;
            if (fp) fp.clear();
        });

        let fpInstance;

        function initFlatpickr(isRange) {
            if (fpInstance) {
                fpInstance.destroy();
            }
            fpInstance = $('input[name="tanggal_event"]').flatpickr({
                mode: isRange ? 'range' : 'single',
                altInput: true,
                altFormat: 'd F Y',
                dateFormat: 'Y-m-d',
                locale: {
                    rangeSeparator: ' s/d '
                },
                allowInput: true
            });
        }

        $(document).ready(function() {
            initFlatpickr(false);

            $('#is_range').on('change', function() {
                initFlatpickr(this.checked);
            });
        });

        $('#dataTableBuilder').on('preXhr.dt', function(e, settings, data) {
            var vals = {
                filter_kategori: $('#filterKategori').val() || '',
                filter_kategori_lokasi: $('#filter_kategori_lokasi_event').val() || '',
                filter_status: $('#filter_status_event').val() || '',
                filter_date_from: $('#filter_date_from').val() || '',
                filter_date_to: $('#filter_date_to').val() || '',
            };
            $.extend(data, vals);

            var activeCount = Object.values(vals).filter(function(v) {
                return v !== '';
            }).length;
            var $badge = $('#dataTableBuilder-filter-badge');
            if (activeCount > 0) {
                $badge.text(activeCount).removeClass('d-none');
            } else {
                $badge.addClass('d-none');
            }
        });
    </script>
@endpush
