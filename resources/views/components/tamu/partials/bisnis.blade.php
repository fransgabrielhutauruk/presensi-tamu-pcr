<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header :title="__('visitor.company_data')" icon="🏢" />
    <x-form.input-field name="instansi" :label="__('visitor.company_name')" :placeholder="__('visitor.company_name_placeholder')" required="true" />
    <x-form.select-field name="kategori_instansi" :label="__('visitor.category')" required="true" :options="[
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
    ]" />
    <x-form.select-field name="skala_instansi" :label="__('visitor.company_scale')" required="true" :options="[
        __('visitor.startup', [], 'id') => __('visitor.startup'),
        __('visitor.small_company', [], 'id') => __('visitor.small_company'),
        __('visitor.medium_company', [], 'id') => __('visitor.medium_company'),
        __('visitor.large_company', [], 'id') => __('visitor.large_company'),
        __('visitor.multinational', [], 'id') => __('visitor.multinational'),
    ]" />
    <x-form.input-field name="jabatan" :label="__('visitor.position_job')" :placeholder="__('visitor.position_placeholder')" required="true" />
</div>

<div>
    <x-tamu.section-header :title="__('visitor.visit_data')" icon="🎯" />
    <x-form.select-field name="pihak_dituju" :label="__('visitor.visiting_party')" required="true" :options="$options['pihak_dituju']" />
    <x-tamu.partials.data-kunjungan />
</div>
