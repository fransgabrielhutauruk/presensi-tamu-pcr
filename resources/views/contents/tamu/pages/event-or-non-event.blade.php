@extends('layouts.tamu.main')

@section('title', Str::title(__('visitor.choose_visit_type')) )

@section('content')
    <div class="row d-flex align-items-center" style="min-height: 90vh">
        <div class="col-md-10 justify-content-center mx-auto">
            <div class="text-center mt-5">
                <x-tamu.page-header title="{{ __('visitor.choose_visit_type') }}"
                    subtitle="{{ __('visitor.select_visit_type') }}" />
                <div class="row g-4 mt-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden wow fadeInUp">
                            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                                <div class="mb-2">
                                    <h5><i class="fas fa-user fa-2x mb-1"></i></h5>
                                </div>
                                <h4 class="card-title mb-1 fw-bold">{{ __('visitor.non_event_visit_title') }}</h4>
                                <p class="card-text text-muted mb-2 flex-grow-1">
                                    {{ __('visitor.non_event_visit_desc') }}
                                </p>
                                <a href="{{ route('tamu.non-event.tujuan') }}"
                                    class="btn-default w-100 mt- fs-6">{{ __('visitor.non_event_visit_title') }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden wow fadeInUp">
                            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                                <div class="mb-2">
                                    <h5><i class="fas fa-calendar-alt fa-2x mb-1"></i></h5>
                                </div>
                                <h4 class="card-title mb-1 fw-bold">{{ __('visitor.event_visit_title') }}</h4>
                                <p class="card-text text-muted mb-2 flex-grow-1">
                                    {{ __('visitor.event_visit_desc') }}
                                </p>
                                <a href="{{ route('tamu.event.list') }}"
                                    class="btn-default w-100 mt-1">{{ __('visitor.event_visit_title') }}</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
