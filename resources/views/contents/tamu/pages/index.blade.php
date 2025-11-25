@extends('layouts.tamu.main')

@section('title', Str::title(__('visitor.welcome_message')) )

@section('content')
    <div class="row d-flex align-items-center pt-5" style="min-height: 90vh">
        <div class="col-md-5 justify-content-center mx-auto">
            <div class="shapes position-fixed" style="pointer-events: none; user-select: none; inset: 0; z-index: 1;"
                aria-hidden="true" id="shapes-container">
            </div>

            <div class="position-relative text-center" style="z-index: 10;">
                <img src="{{ asset('theme/images/akreditasi-unggul.webp') }}" alt="Logo Akreditasi Unggul"
                    class="mx-auto d-block mb-3 img-fluid" style="width: 30%;" />

                <h1 class="wow fadeInOut" data-wow-delay="0.5s">
                    {{ __('visitor.welcome_message') }}
                </h1>
                <h5 class="wow fadeInOut" data-wow-delay="1s">
                    {{ __('visitor.at_institution') }}
                </h5>
                <p class="text-muted mb-4 fs-6 lh-base" id="typewriter-text">
                </p>
                <div class="mx-auto">
                    <div class="swiper campusSwiper mb-4 rounded shadow-sm overflow-hidden" style="height: 250px;">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img src="{{ asset('theme/images/pcr-depan.webp') }}" alt="Gedung utama kampus PCR"
                                    class="w-100 h-100 object-fit-cover" />
                            </div>
                            <div class="swiper-slide">
                                <img src="{{ asset('theme/images/main-hall.webp') }}" alt="Main Hall PCR"
                                    class="w-100 h-100 object-fit-cover" />
                            </div>
                            <div class="swiper-slide">
                                <img src="{{ asset('theme/images/gsg.webp') }}" alt="GSG PCR"
                                    class="w-100 h-100 object-fit-cover" />
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>

                    <a id="route" href="{{ route('tamu.event-or-non-event') }}" class="btn-default w-100 mt-2">
                        <span id="beforeSubmit">{{ __('visitor.fill_guest_book') }}</span>
                        <span id="loadingIndicator" style="display: none;">
                            <i class="fas fa-spinner fa-spin me-2"></i>{{ __('common.processing') }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .campusSwiper .swiper-pagination-bullet {
            background: rgb(0, 174, 201);
        }

        .campusSwiper .swiper-pagination-bullet-active {
            background: rgb(12, 90, 109);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.campusSwiper', {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 2000,
            });

            const routeLink = document.querySelector('#route');

            const beforeSubmit = document.querySelector('#beforeSubmit');
            const loadingIndicator = document.querySelector('#loadingIndicator');

            if (routeLink) {
                routeLink.addEventListener('click', function(e) {
                    beforeSubmit.style.display = 'none';
                    loadingIndicator.style.display = 'inline';

                    routeLink.style.pointerEvents = 'none';
                    window.location.href = this.getAttribute('href');
                });
            }

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    resetLinkState();
                }
            });

            function resetLinkState() {
                if (routeLink) {
                    beforeSubmit.style.display = 'inline';
                    loadingIndicator.style.display = 'none';
                    routeLink.style.pointerEvents = 'auto';
                }
            }

        });
    </script>

    <style>
        @keyframes drift {
            0% {
                transform: translate3d(0, 0, 0) rotate(0deg);
            }

            100% {
                transform: translate3d(var(--dx), var(--dy), 0) rotate(var(--rot));
            }
        }

        .shape {
            position: absolute;
            animation: drift linear infinite alternate;
            will-change: transform;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typewriterElement = document.getElementById('typewriter-text');
            typewriterElement.innerHTML = '';

            const fullText = @json(__('visitor.campus_slogan'));
            let index = 0;
            let currentText = '';

            setTimeout(() => {
                const interval = setInterval(() => {
                    if (index < fullText.length) {
                        const char = fullText[index] === '\n' ? '<br />' : fullText[index];
                        currentText += char;
                        typewriterElement.innerHTML = currentText;
                        index++;
                    } else {
                        clearInterval(interval);
                    }
                }, 60);
            }, 500);

            createShapes();
        });

        function createShapes() {
            const SHAPE_COUNT = 7;
            const SIZE_MIN = 22;
            const SIZE_MAX = 78;
            const palette = [
                'rgb(0, 174, 201)',
                'rgb(0, 138, 161)',
                'rgb(12, 90, 109)',
            ];

            function rand(min, max) {
                return Math.random() * (max - min) + min;
            }

            const shapesContainer = document.getElementById('shapes-container');
            shapesContainer.innerHTML = '';

            for (let i = 0; i < SHAPE_COUNT; i++) {
                const size = rand(SIZE_MIN, SIZE_MAX);
                const shape = document.createElement('div');
                shape.className = 'shape rounded-circle';

                const shapeData = {
                    size,
                    top: rand(-5, 105),
                    left: rand(-5, 105),
                    color: palette[Math.floor(Math.random() * palette.length)],
                    dur: rand(22, 48),
                    delay: rand(-50, 0),
                    dx: rand(-90, 90),
                    dy: rand(-140, 140),
                    rot: rand(-45, 45),
                };

                shape.style.cssText = `
                width: ${shapeData.size}px;
                height: ${shapeData.size}px;
                top: ${shapeData.top}%;
                left: ${shapeData.left}%;
                background: ${shapeData.color};
                animation-duration: ${shapeData.dur}s;
                animation-delay: ${shapeData.delay}s;
                --dx: ${shapeData.dx}px;
                --dy: ${shapeData.dy}px;
                --rot: ${shapeData.rot}deg;
                filter: blur(0.2px);
                opacity: 0.18;
            `;

                shapesContainer.appendChild(shape);
            }
        }
    </script>
@endsection
