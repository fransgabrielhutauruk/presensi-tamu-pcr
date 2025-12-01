@props([
    'table' => 'dataTableBuilder',
    'text' => 'Refresh',
    'icon' => 'bi bi-arrow-clockwise fs-2',
    'type' => 'primary',
    'class' => '',
    'id' => 'btn-refresh'
])

<x-btn :type="$type" class="btn text-nowrap btn-sm {{ $class }}" :id="$id">
    <i class="{{ $icon }}"></i> {{ $text }}
</x-btn>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#{{ $id }}').on('click', function() {
            @if(str_contains($table, 'LaravelDataTables'))
                window.{{ $table }}.ajax.reload();
            @else
                window.LaravelDataTables['{{ $table }}'].ajax.reload();
            @endif
        });
    });
</script>
@endpush
