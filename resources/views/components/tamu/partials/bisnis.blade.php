<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header :title="__('visitor.company_data')" icon="🏢" />
    <x-form.input-field name="instansi" :label="__('visitor.company_name')" :placeholder="__('visitor.company_name_placeholder')" required="true" />
    <x-form.select-field name="kategori_instansi" :label="__('visitor.category')" required="true" :options="[
        __('visitor.information_technology') => __('visitor.information_technology'),
        __('visitor.manufacturing') => __('visitor.manufacturing'),
        __('visitor.consulting_services') => __('visitor.consulting_services'),
        __('visitor.government') => __('visitor.government'),
        __('visitor.trade') => __('visitor.trade'),
        __('visitor.construction') => __('visitor.construction'),
        __('visitor.education') => __('visitor.education'),
        __('visitor.health') => __('visitor.health'),
        __('visitor.finance_banking') => __('visitor.finance_banking'),
        __('visitor.media_communication') => __('visitor.media_communication'),
        __('visitor.others') => __('visitor.others'),
    ]" />
    <x-form.select-field name="skala_perusahaan" :label="__('visitor.company_scale')" required="true" :options="[
        __('visitor.startup') => __('visitor.startup'),
        __('visitor.small_company') => __('visitor.small_company'),
        __('visitor.medium_company') => __('visitor.medium_company'),
        __('visitor.large_company') => __('visitor.large_company'),
        __('visitor.multinational') => __('visitor.multinational'),
    ]" />
    <x-form.input-field name="jabatan" :label="__('visitor.position_job')" :placeholder="__('visitor.position_placeholder')" required="true" />
</div>

<div>
    <x-tamu.section-header :title="__('visitor.visit_data')" icon="🎯" />
    <x-form.select-field name="pihak_dituju" :label="__('visitor.visiting_party')" required="true" :options="$options['pihak_dituju']" />
    <x-tamu.partials.data-kunjungan />
</div>
