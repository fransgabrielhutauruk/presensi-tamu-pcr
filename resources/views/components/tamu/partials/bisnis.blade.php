<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.partials.section-header title="Data Perusahaan" icon="🏢" />
    <x-form.input-field name="instansi" label="Nama Perusahaan" placeholder="Nama perusahaan" required="true" />
    <x-form.select-field name="bidang_usaha" label="Bidang Usaha" required="true" :options="[
        'Teknologi Informasi' => 'Teknologi Informasi',
        'Manufaktur' => 'Manufaktur',
        'Jasa Konsultasi' => 'Jasa Konsultasi',
        'Perdagangan' => 'Perdagangan',
        'Konstruksi' => 'Konstruksi',
        'Pendidikan' => 'Pendidikan',
        'Kesehatan' => 'Kesehatan',
        'Keuangan/Perbankan' => 'Keuangan/Perbankan',
        'Media & Komunikasi' => 'Media & Komunikasi',
        'Lainnya' => 'Lainnya',
    ]" />
    <x-form.select-field name="skala_perusahaan" label="Skala Perusahaan" required="true" :options="[
        'Startup' => 'Startup',
        'Perusahaan Kecil (< 50 karyawan)' => 'Perusahaan Kecil (< 50 karyawan)',
        'Perusahaan Menengah (50-250 karyawan)' => 'Perusahaan Menengah (50-250 karyawan)',
        'Perusahaan Besar (> 250 karyawan)' => 'Perusahaan Besar (> 250 karyawan)',
        'Multinational Corporation' => 'Multinational Corporation',
    ]" />
    <x-form.input-field name="jabatan" label="Jabatan/Posisi" placeholder="Jabatan/Posisi" required="true" />
</div>

<div>
    <x-tamu.partials.section-header title="Data Kunjungan" icon="🎯" />
    <x-form.select-field name="pihak_dituju" label="Pihak yang Dituju" required="true" :options="[
        'Direktur' => 'Direktur',
        'Wakil Direktur' => 'Wakil Direktur',
        'BP3M' => 'BP3M',
        'Sumatera Career Center (SCC)' => 'Sumatera Career Center (SCC)',
        'Lainnya' => 'Lainnya',
    ]" />
    <x-tamu.partials.data-kunjungan />
</div>
