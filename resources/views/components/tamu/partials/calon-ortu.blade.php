<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header
        title="Data Calon Mahasiswa"
        icon="🎓" />
    <x-form.input-field
        name="asal_sekolah"
        label="Asal Sekolah"
        placeholder="Nama sekolah asal"
        required="true" />
    <x-form.select-field
        name="prodi_diminati"
        label="Program Studi yang Diminati"
        required="true"
        :options="[
            'Teknik Informatika' => 'Teknik Informatika',
            'Sistem Informasi' => 'Sistem Informasi',
            'Teknologi Rekayasa Komputer' => 'Teknologi Rekayasa Komputer',
            'Teknik Mesin' => 'Teknik Mesin',
            'Teknologi Rekayasa Sistem Elektronika' => 'Teknologi Rekayasa Sistem Elektronika',
            'Teknologi Rekayasa Mekatronika' => 'Teknologi Rekayasa Mekatronika',
            'Teknik Elektronika' => 'Teknik Elektronika',
            'Teknik Listrik' => 'Teknik Listrik',
            'Teknologi Rekayasa Jaringan Telekomunikasi' => 'Teknologi Rekayasa Jaringan Telekomunikasi',
            'Akuntansi Perpajakan' => 'Akuntansi Perpajakan',
            'Bisnis Digital' => 'Bisnis Digital',
            'Hubungan Masyarakat dan Komunikasi Digital' => 'Hubungan Masyarakat dan Komunikasi Digital'
            ]" />
</div>

<div>
    <x-tamu.section-header
        title="Data Kunjungan"
        icon="🎯" />
    <x-tamu.partials.data-kunjungan />
</div>