<x-form.textarea-field name="keperluan" :label="__('visitor.visit_purpose_label')" :placeholder="__('visitor.visit_purpose_placeholder')" required="true" rows="3" />

<x-form.input-field name="jumlah_rombongan" :label="__('visitor.group_size_label')" type="number" min="1" max="50" required="true"
    :placeholder="__('visitor.group_size_placeholder')" />

<x-form.input-field name="waktu_keluar" :label="__('visitor.estimated_finish_time')" type="time" required="true" />

<x-form.select-field name="transportasi" :label="__('visitor.transportation_label')" required="true" :options="[
    __('visitor.car_option', [], 'id') => __('visitor.car_option'),
    __('visitor.motorcycle_option', [], 'id') => __('visitor.motorcycle_option'),
    __('visitor.bus_option', [], 'id') => __('visitor.bus_option'),
    __('visitor.travel_option', [], 'id') => __('visitor.travel_option'),
    __('visitor.online_ride_option', [], 'id') => __('visitor.online_ride_option'),
    __('visitor.walking_option', [], 'id') => __('visitor.walking_option'),
    __('visitor.other_option', [], 'id') => __('visitor.other_option'),
]" />
