<x-tamu.partials.data-pengunjung />

<div>
    <x-form.select-field
        name="hubungan_dengan_mahasiswa"
        label="Hubungan dengan Mahasiswa"
        required="true"
        :options="[
        'Orang Tua' => 'Orang Tua', 
        'Wali' => 'Wali', 
        'Saudara' => 'Saudara', 
        'Lainnya' => 'Lainnya'
        ]" />
</div>

<div>
    <x-tamu.section-header
        title="Data Mahasiswa"
        icon="🎓" />
    <x-form.input-field
        name="nama_mahasiswa"
        label="Nama Mahasiswa"
        placeholder="Nama lengkap mahasiswa"
        required="true" />
    <x-form.input-field
        name="nim_mahasiswa"
        label="NIM Mahasiswa"
        placeholder="Nomor Induk Mahasiswa"
        required="true"
        validationRules='pattern="[0-9]+" data-pattern-error="NIM harus berupa angka"' />
</div>

<div>
    <x-tamu.section-header
        title="Data Kunjungan"
        icon="🎯" />
    <x-form.select-field
        name="pihak_dituju"
        label="Pihak yang Dituju"
        required="true"
        :options="[
                'BAAK' => 'BAAK', 
                'Program Studi' => 'Program Studi', 
                'Dosen Wali' => 'Dosen Wali', 
                'Bagian Keuangan' => 'Bagian Keuangan', 
                'Kemahasiswaan' => 'Kemahasiswaan', 
                'Lainnya' => 'Lainnya'
            ]" />
    <x-tamu.partials.data-kunjungan />
</div>