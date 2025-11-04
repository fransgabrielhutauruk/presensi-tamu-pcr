<x-tamu.partials.data-pengunjung />

<div>
    <x-tamu.section-header :title="__('visitor.institution_data')" icon="🏛️" />
    <x-form.input-field name="instansi" :label="__('visitor.institution_name')" :placeholder="__('visitor.institution_name_placeholder')" required="true" />
    <x-form.select-field name="jenis_instansi" :label="__('visitor.institution_type')" required="true" :options="$opsiData['jenis_instansi'] ?? [
        __('visitor.central_government', [], 'id') => __('visitor.central_government'),
        __('visitor.regional_government', [], 'id') => __('visitor.regional_government'),
        __('visitor.state_enterprise', [], 'id') => __('visitor.state_enterprise'),
        __('visitor.private', [], 'id') => __('visitor.private'),
        __('visitor.university', [], 'id') => __('visitor.university'),
        __('visitor.foundation', [], 'id') => __('visitor.foundation'),
        __('visitor.others', [], 'id') => __('visitor.others'),
    ]" />
    <x-form.input-field name="jabatan" :label="__('visitor.position_job')" :placeholder="__('visitor.position_placeholder')" required="true" />
</div>

<div>
    <x-tamu.section-header :title="__('visitor.visit_data')" icon="🎯" />
    <x-form.select-field name="pihak_dituju" :label="__('visitor.visiting_party')" required="true" :options="$options['pihak_dituju']" />
    <x-tamu.partials.data-kunjungan />
</div>
