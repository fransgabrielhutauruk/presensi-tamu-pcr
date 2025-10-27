<x-tamu.partials.data-pengunjung />

<div>
    <x-form.section-header
        title="Data Instansi"
        icon="🏛️" />
    <x-form.input-field
        name="instansi"
        label="Nama Instansi"
        placeholder="Nama instansi"
        required="true" />
    <x-form.select-field
        name="jenis_instansi"
        label="Jenis Instansi"
        required="true"
        :options="[
                'Pemerintah Pusat' => 'Pemerintah Pusat',
                'Pemerintah Daerah' => 'Pemerintah Daerah', 
                'BUMN' => 'BUMN',
                'Swasta' => 'Swasta',
                'Perguruan Tinggi' => 'Perguruan Tinggi',
                'Yayasan' => 'Yayasan',
                'Lainnya' => 'Lainnya'
            ]" />
    <x-form.input-field
        name="jabatan"
        label="Jabatan/Posisi"
        placeholder="Jabatan/Posisi"
        required="true" />
</div>

<div>
    <x-form.section-header
        title="Data Kunjungan"
        icon="🎯" />
    <x-form.select-field
        name="pihak_dituju"
        label="Pihak yang Dituju"
        required="true"
        :options="[
            'Direktur' => 'Direktur', 
            'Wakil Direktur' => 'Wakil Direktur', 
            'BP3M' => 'BP3M', 
            'Lainnya' => 'Lainnya'
        ]" />
    <x-tamu.partials.data-kunjungan />
</div>