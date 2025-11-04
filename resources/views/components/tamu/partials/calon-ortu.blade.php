<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header
        :title="__('visitor.prospective_student_data')"
        icon="🎓" />
    <x-form.input-field
        name="asal_sekolah"
        :label="__('visitor.school_origin')"
        :placeholder="__('visitor.school_origin_placeholder')"
        required="true" />
    <x-form.select-field
        name="prodi_diminati"
        :label="__('visitor.interested_program')"
        required="true"
        :options="$options['prodi']" />
</div>

<div>
    <x-tamu.section-header
        :title="__('visitor.visit_data')"
        icon="🎯" />
    <x-tamu.partials.data-kunjungan />
</div>