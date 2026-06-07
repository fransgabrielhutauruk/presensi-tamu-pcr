@extends('layouts.tamu.main')

@section('title', $event->nama_event)

@section('content')
    @php
        $participantOptions = [
            [
                'key' => 'non-civitas',
                'route' => route('tamu.event.form-presensi-non-civitas', $eventId),
                'title' => __('visitor.non_civitas'),
                'description' => __('visitor.general_visitor'),
            ],
            [
                'key' => 'civitas',
                'route' => route('tamu.event.form-presensi-civitas', $eventId),
                'title' => __('visitor.civitas_pcr'),
                'description' => __('visitor.lecturer_staff_student'),
            ],
        ];
    @endphp

    <div class="row align-items-center" style="min-height: 80vh">
        <div class="col-md-6 justify-content-center mx-auto">
            <div class="text-center">
                <x-tamu.page-header :title="$event->nama_event" :question="__('visitor.non_civitas_or_civitas')" />

                <div class="row g-4 mb-4 mt-1 px-3">
                    @foreach ($participantOptions as $option)
                        <a href="{{ $option['route'] }}" class="card border-0 shadow-sm participant-option wow fadeInUp pb-1"
                            style="cursor: pointer; transition: all 0.3s ease;"
                            data-cy="btn-identitas-{{ $option['key'] }}">
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bold mb-0">{{ $option['title'] }}</h5>
                                <p class="text-muted mb-0">{{ $option['description'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <style>
        .participant-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .participant-option:hover .fas {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }
    </style>
@endsection
