@php
    $institutionTypeOptions = $opsiData['jenis_instansi'] ?? [
        __('visitor.central_government', [], 'id') => __('visitor.central_government'),
        __('visitor.regional_government', [], 'id') => __('visitor.regional_government'),
        __('visitor.state_enterprise', [], 'id') => __('visitor.state_enterprise'),
        __('visitor.private', [], 'id') => __('visitor.private'),
        __('visitor.university', [], 'id') => __('visitor.university'),
        __('visitor.foundation', [], 'id') => __('visitor.foundation'),
        __('visitor.others', [], 'id') => __('visitor.others'),
    ];

    $jabatanOptions = [
        __('visitor.job_top_management', [], 'id') => __('visitor.job_top_management'),
        __('visitor.job_middle_management', [], 'id') => __('visitor.job_middle_management'),
        __('visitor.job_operational_staff', [], 'id') => __('visitor.job_operational_staff'),
        __('visitor.job_educator_non_structural', [], 'id') => __('visitor.job_educator_non_structural'),
        __('visitor.job_support_team', [], 'id') => __('visitor.job_support_team'),
        __('visitor.job_university_student', [], 'id') => __('visitor.job_university_student'),
        __('visitor.job_school_student', [], 'id') => __('visitor.job_school_student'),
        __('visitor.job_other', [], 'id') => __('visitor.job_other'),
    ];
@endphp

<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header :title="__('visitor.institution_data')" icon="🏛️" />
    <x-form.input-field name="instansi" :label="__('visitor.institution_name')" :placeholder="__('visitor.institution_name_placeholder')" required="true" />
    <x-form.select-field name="jenis_instansi" :label="__('visitor.institution_type')" required="true" :options="$institutionTypeOptions" />
    <x-form.select-field name="jabatan" :label="__('visitor.position_job')" required="true" :options="$jabatanOptions" :placeholderDisabled="true" />
</div>

<div>
    <x-tamu.section-header :title="__('visitor.visit_data')" icon="🎯" />
    <x-form.select-field name="pihak_dituju" :label="__('visitor.visiting_party')" required="true" :options="$options['pihak_dituju']" />
    <x-tamu.partials.data-kunjungan />
</div>
