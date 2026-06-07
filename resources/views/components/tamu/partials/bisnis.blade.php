@php
    $companyCategoryOptions = [
        __('visitor.information_technology', [], 'id') => __('visitor.information_technology'),
        __('visitor.manufacturing', [], 'id') => __('visitor.manufacturing'),
        __('visitor.consulting_services', [], 'id') => __('visitor.consulting_services'),
        __('visitor.government', [], 'id') => __('visitor.government'),
        __('visitor.trade', [], 'id') => __('visitor.trade'),
        __('visitor.construction', [], 'id') => __('visitor.construction'),
        __('visitor.education', [], 'id') => __('visitor.education'),
        __('visitor.health', [], 'id') => __('visitor.health'),
        __('visitor.finance_banking', [], 'id') => __('visitor.finance_banking'),
        __('visitor.media_communication', [], 'id') => __('visitor.media_communication'),
        __('visitor.others', [], 'id') => __('visitor.others'),
    ];

    $companyScaleOptions = [
        __('visitor.micro_scale', [], 'id') => __('visitor.micro_scale'),
        __('visitor.small_scale', [], 'id') => __('visitor.small_scale'),
        __('visitor.medium_scale', [], 'id') => __('visitor.medium_scale'),
        __('visitor.large_scale', [], 'id') => __('visitor.large_scale'),
    ];

    $companyTypeOptions = [
        __('visitor.company_type_local_regional', [], 'id') => __('visitor.company_type_local_regional'),
        __('visitor.company_type_national', [], 'id') => __('visitor.company_type_national'),
        __('visitor.company_type_multi_national', [], 'id') => __('visitor.company_type_multi_national'),
    ];

    $jabatanOptions = [
        __('visitor.job_group_leadership_management') => [
            __('visitor.job_bisnis_executive_lead', [], 'id') => __('visitor.job_bisnis_executive_lead'),
            __('visitor.job_bisnis_management_supervisor', [], 'id') => __('visitor.job_bisnis_management_supervisor'),
        ],
        __('visitor.job_group_business_specialist') => [
            __('visitor.job_bisnis_sales_partnership', [], 'id') => __('visitor.job_bisnis_sales_partnership'),
            __('visitor.job_bisnis_technical_specialist', [], 'id') => __('visitor.job_bisnis_technical_specialist'),
        ],
        __('visitor.job_group_operational_other') => [
            __('visitor.job_bisnis_operational_support', [], 'id') => __('visitor.job_bisnis_operational_support'),
            __('visitor.job_other', [], 'id') => __('visitor.job_other'),
        ],
    ];
@endphp

<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header :title="__('visitor.company_data')" icon="🏢" />
    <x-form.input-field name="instansi" :label="__('visitor.company_name')" :placeholder="__('visitor.company_name_placeholder')" required="true" />
    <x-form.select-field name="kategori_instansi" :label="__('visitor.category')" required="true" :options="$companyCategoryOptions" />
    <x-form.select-field name="jenis_perusahaan" :label="__('visitor.company_type')" required="true" :options="$companyTypeOptions" />
    <x-form.select-field name="skala_instansi" :label="__('visitor.company_scale')" required="true" :options="$companyScaleOptions" />
    <x-form.select-field name="jabatan" :label="__('visitor.position_job')" required="true" :options="$jabatanOptions" :placeholderDisabled="true" />
</div>

<div>
    <x-tamu.section-header :title="__('visitor.visit_data')" icon="🎯" />
    <x-form.select-field name="pihak_dituju" :label="__('visitor.visiting_party')" required="true" :options="$options['pihak_dituju']" />
    <x-tamu.partials.data-kunjungan />
</div>
