@extends('layouts.tamu.main')

@section('title', $event->nama_event)

@section('content')
    <div class="row align-items-center" style="min-height: 90vh">
        <div class="col-md-6 justify-content-center mx-auto">
            <div class="text-center">
                <x-tamu.page-header :title="$event->nama_event" :question="__('visitor.non_civitas_or_civitas')" />

                <div class="row g-4 mb-4 mt-1 px-3">
                    <a href="{{ route('tamu.event.form-presensi-non-civitas', $eventId) }}"
                        class="card border-0 shadow-sm py-1 participant-option wow fadeInUp"
                        style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bold mb-0">{{ __('visitor.non_civitas') }}</h5>
                            <p class="text-muted mb-0">{{ __('visitor.general_visitor') }}</p>
                        </div>
                    </a>

                    <a href="{{ route('tamu.event.form-presensi-civitas', $eventId) }}"
                        class="card border-0 shadow-sm participant-option wow fadeInUp"
                        style="cursor: pointer; transition: all 0.3s ease;">
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bold mb-0">{{ __('visitor.civitas_pcr') }}</h5>
                            <p class="text-muted mb-0">{{ __('visitor.lecturer_staff_student') }}</p>
                        </div>
                    </a>
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
