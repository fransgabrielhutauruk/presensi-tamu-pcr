@extends('layouts.tamu.main')

@section('content')
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="checkout-success-container">
                    <x-tamu.page-header title="{{ __('visitor.visitor_feedback') }}" img=true />
                    <div class="feedback-card wow fadeInUp my-4" data-wow-delay="0.5s">
                        <h6 class="text-center mb-0">
                            {{ __('visitor.service_rating') }}
                        </h6>

                        <form id="feedbackForm" method="POST" action="{{ route('tamu.feedback-store', $kunjunganId) }}">
                            @csrf
                            <div class="text-center mt-2 mb-4">
                                <div class="star-rating">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <button type="button" class="star-btn" data-rating="{{ $i }}">
                                            <svg class="star-icon" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </button>
                                    @endfor
                                </div>
                                <p class="rating-help-text" id="defaultHelpText">{{ __('visitor.select_rating') }}</p>
                                <div class="rating-description" id="ratingDescription" style="display: none;">
                                    <span class="rating-label"></span>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" required>
                                <div id="rating-error" class="rating-error-message" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ __('visitor.rating_required') }}
                                </div>
                            </div>

                            <x-form.textarea-field name="komentar" label="{{ __('visitor.suggestions_optional') }}"
                                rows="4" placeholder="{{ __('visitor.share_experience') }}"
                                validationRules="data-maxlength=500" />

                            <button type="submit" class="btn-default mt-3 w-100" id="submitBtn">
                                <span class="submit-text">{{ __('visitor.submit') }}</span>
                                <span class="submit-loading d-none">
                                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('visitor.processing') }}
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .feedback-card {
            background: var(--white-color);
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .star-rating {
            display: flex;
            justify-content: center;
            gap: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .star-btn {
            width: 48px;
            height: 48px;
            border: none;
            background: none;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.2s ease;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .star-btn:hover,
        .star-btn.active {
            color: #fbbf24;
        }

        .star-icon {
            width: 40px;
            height: 40px;
        }

        .rating-help-text {
            font-size: 0.875rem;
            color: var(--text-muted-color);
            margin: 0;
        }

        .rating-description {
            margin-top: 0.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--primary-main);
            font-weight: bold;
        }

        .rating-error-message {
            font-size: 0.875rem;
            color: #e74c3c;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 0.375rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .star-btn {
                width: 40px;
                height: 40px;
            }

            .star-icon {
                width: 40px;
                height: 40px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const starBtns = document.querySelectorAll('.star-btn');
            const ratingInput = document.getElementById('ratingInput');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('feedbackForm');
            const submitText = document.querySelector('.submit-text');
            const submitLoading = document.querySelector('.submit-loading');
            const ratingError = document.getElementById('rating-error');
            const starRating = document.querySelector('.star-rating');
            const defaultHelpText = document.getElementById('defaultHelpText');
            const ratingDescription = document.getElementById('ratingDescription');
            const ratingLabel = document.querySelector('.rating-label');

            let currentRating = 0;
            const ratingDescriptions = {
                1: "{{ __('visitor.very_bad') }}",
                2: "{{ __('visitor.bad') }}",
                3: "{{ __('visitor.fair') }}",
                4: "{{ __('visitor.good') }}",
                5: "{{ __('visitor.very_good') }}"
            };

            starBtns.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    setRating(rating);
                });

                btn.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.dataset.rating);
                    highlightStars(rating);
                });
            });

            document.querySelector('.star-rating').addEventListener('mouseleave', function() {
                highlightStars(currentRating);
            });

            function setRating(rating) {
                currentRating = rating;
                ratingInput.value = rating;
                hideRatingError();
                highlightStars(rating);
                showRatingDescription(rating);
            }

            function highlightStars(rating) {
                starBtns.forEach((btn, index) => {
                    if (index < rating) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            function showRatingDescription(rating) {
                if (rating > 0) {
                    defaultHelpText.style.display = 'none';
                    ratingDescription.style.display = 'block';
                    ratingLabel.textContent = ratingDescriptions[rating];
                } else {
                    defaultHelpText.style.display = 'block';
                    ratingDescription.style.display = 'none';
                }
            }

            function showRatingError() {
                ratingError.style.display = 'block';
            }

            function hideRatingError() {
                ratingError.style.display = 'none';
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (currentRating === 0) {
                    showRatingError();
                    starRating.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return;
                }
                submitText.classList.add('d-none');
                submitLoading.classList.remove('d-none');
                this.submit();
            });
        });
    </script>
@endsection
