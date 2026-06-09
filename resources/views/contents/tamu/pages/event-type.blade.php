@extends('layouts.tamu.main')

@section('title', __('visitor.event_type_selection_title'))

@section('content')
    @php
        $eventTypeCards = [
            [
                'type' => 'dalam_kampus',
                'icon' => 'fa-building',
                'title' => __('visitor.internal_event'),
                'description' => __('visitor.event_inside_campus'),
                'route' => route('tamu.event.list', ['kategori_lokasi' => 'dalam_kampus']),
            ],
            [
                'type' => 'luar_kampus',
                'icon' => 'fa-globe',
                'title' => __('visitor.external_event'),
                'description' => __('visitor.event_outside_campus'),
                'route' => route('tamu.event.list', ['kategori_lokasi' => 'luar_kampus']),
            ],
        ];
    @endphp

    <div class="row d-flex align-items-center" style="min-height: 80vh">
        <div class="col-md-10 justify-content-center mx-auto">
            <div class="text-center">
                <x-tamu.page-header title="{{ __('visitor.event_type_selection_title') }}"
                    subtitle="{{ __('visitor.event_type_selection_desc') }}" />
                <div class="row g-4 mt-3">
                    @foreach ($eventTypeCards as $card)
                        <div class="col-md-6">
                            <a href="{{ $card['route'] }}" class="card border-0 shadow-sm h-100 position-relative overflow-hidden wow fadeInUp"
                                style="cursor: pointer; text-decoration: none; color: inherit;">
                                <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                                    <div class="mb-2">
                                        <h5><i class="fas {{ $card['icon'] }} fa-2x mb-1"></i></h5>
                                    </div>
                                    <h4 class="card-title mb-1 fw-bold">{{ $card['title'] }}</h4>
                                    <p class="card-text text-muted mb-2 flex-grow-1">{{ $card['description'] }}</p>
                                    <span class="btn btn-default w-100 mt-1 fs-6">{{ $card['title'] }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection