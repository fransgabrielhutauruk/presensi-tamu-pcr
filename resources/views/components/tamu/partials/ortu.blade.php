<x-tamu.partials.data-pengunjung />

<div>
    <x-form.select-field name="hubungan_dengan_mahasiswa" :label="__('visitor.relation_student')" required="true"
        :options="[
            __('visitor.parent') => __('visitor.parent'),
            __('visitor.guardian') => __('visitor.guardian'),
            __('visitor.sibling') => __('visitor.sibling'),
            __('visitor.others') => __('visitor.others'),
        ]" />
</div>

<div>
    <x-tamu.section-header :title="__('visitor.student_data')" icon="🎓" />
    <x-form.input-field name="nama_mahasiswa" :label="__('visitor.student_name')" :placeholder="__('visitor.student_name_placeholder')" required="true" />
    <x-form.select-field name="prodi_mahasiswa" :label="__('visitor.student_program')" required="true" :options="$options['prodi']" />
    <x-form.input-field name="nim_mahasiswa" :label="__('visitor.student_nim_optional')" :placeholder="__('visitor.student_nim_placeholder')"
        :validationRules="'pattern=\"[0-9]+\" data-pattern-error=\"' . __('visitor.nim_pattern_error') . '\"'" />
</div>

<div>
    <x-tamu.section-header :title="__('visitor.visit_data')" icon="🎯" />
    <x-form.select-field name="pihak_dituju" :label="__('visitor.visiting_party')" required="true" :options="[
        'BAAK' => 'BAAK',
        'Program Studi' => 'Program Studi',
        'Dosen Wali' => 'Dosen Wali',
        'Bagian Keuangan' => 'Bagian Keuangan',
        'Kemahasiswaan' => 'Kemahasiswaan',
        __('visitor.others') => __('visitor.others'),
    ]" />
    <x-tamu.partials.data-kunjungan />
</div>
