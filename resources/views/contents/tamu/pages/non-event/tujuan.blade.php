@extends('layouts.tamu.main')

@section('title', __('visitor.visit_purpose_title'))

@section('content')
    @php
        $tujuanCards = [
            [
                'tujuan' => 'instansi',
                'icon' => 'fa-building',
                'label' => __('visitor.institutional_visit'),
                'delay' => '0.2s',
            ],
            [
                'tujuan' => 'bisnis',
                'icon' => 'fa-handshake',
                'label' => __('visitor.business_partnership'),
                'delay' => '0.3s',
            ],
            [
                'tujuan' => 'ortu',
                'icon' => 'fa-users',
                'label' => __('visitor.parent_student'),
                'delay' => '0.4s',
            ],
            [
                'tujuan' => 'informasi_kampus',
                'icon' => 'fa-graduation-cap',
                'label' => __('visitor.campus_info_pmb'),
                'delay' => '0.5s',
            ],
            [
                'tujuan' => 'lainnya',
                'icon' => 'fa-calendar-alt',
                'label' => __('visitor.other'),
                'delay' => '0.6s',
            ],
        ];
    @endphp

    <div class="row d-flex align-items-center" style="min-height: 80vh">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="text-center">
                <div class="relative min-h-screen d-flex flex-column justify-content-center mx-auto">
                    <div class="position-relative" style="z-index: 10;">
                        <x-tamu.page-header title="{{ __('visitor.visit_purpose_title') }}"
                            question="{{ __('visitor.select_purpose') }}" />
                        <div class="d-flex flex-column flex-fill mt-4 mx-auto">
                            <div class="d-flex flex-column" style="gap: 1rem;">
                                @foreach ($tujuanCards as $card)
                                    <a href="{{ route('tamu.non-event.form-presensi', ['tujuan' => $card['tujuan']]) }}"
                                        class="tujuan-card wow fadeInUp" data-wow-delay="{{ $card['delay'] }}"
                                        data-cy="btn-tujuan-{{ $card['tujuan'] }}">
                                        <div class="tujuan-icon">
                                            <i class="fas {{ $card['icon'] }}"></i>
                                        </div>
                                        <span class="tujuan-label">{{ $card['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tujuan-card {
            display: flex;
            align-items: center;
            padding: 0.7rem 1.5rem;
            background: var(--white-color);
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
            gap: 1rem;
        }

        .tujuan-card:hover {
            border-color: var(--primary-color);
            background-color: var(--gray-200);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .tujuan-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(0, 75, 95, 0.1);
            border-radius: 0.5rem;
            flex-shrink: 0;
        }

        .tujuan-icon i {
            font-size: 1.2rem;
            color: rgb(0, 75, 95);
        }

        .tujuan-label {
            font-size: 0.9rem;
            font-weight: 500;
            flex-grow: 1;
            text-align: start;
        }

        @media (max-width: 576px) {
            .tujuan-card {
                padding: 1rem;
            }

            .tujuan-icon {
                width: 40px;
                height: 40px;
            }

            .tujuan-icon i {
                font-size: 1.25rem;
            }

            .tujuan-label {
                font-size: 0.9rem;
            }
        }
    </style>
@endsection
