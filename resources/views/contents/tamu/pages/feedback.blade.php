@extends('layouts.tamu.main')

@section('content')
<div class="container">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="checkout-success-container">
                <div class="text-center mb-4 wow fadeInUp" data-wow-delay="0.3s">
                    <img
                        src="{{ asset('theme/images/akreditasi-unggul.webp') }}"
                        alt="Logo Akreditasi Unggul"
                        class="img-fluid d-block mx-auto" style="width:6rem" />

                    <h1 class="success-title mt-2 mb-0">Terima Kasih!</h1>
                    <p class="success-subtitle my-0">Checkout berhasil disimpan</p>
                    <div class="success-divider mx-auto mt-2"></div>
                </div>

                <div class="feedback-card wow fadeInUp" data-wow-delay="0.5s">
                    <h3 class="feedback-title text-center mb-0">
                        Bagaimana penilaian Anda terhadap pelayanan kami?
                    </h3>

                    <form id="feedbackForm" method="POST" action="#">
                        @csrf
                        <input type="hidden" name="kode_kunjungan" value="{{ $kunjungan->kode_kunjungan ?? '' }}">

                        <div class="text-center my-2">
                            <div class="star-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" class="star-btn" data-rating="{{ $i }}">
                                    <svg class="star-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    </button>
                                    @endfor
                            </div>
                            <p class="rating-help-text">Pilih rating untuk memberikan penilaian</p>
                            <input type="hidden" name="rating" id="ratingInput" required>
                        </div>

                        <div class="mt-4">
                            <label for="feedback" class="feedback-label">
                                Saran dan Masukan (Opsional)
                            </label>
                            <textarea
                                name="feedback"
                                id="feedback"
                                class="feedback-textarea"
                                rows="4"
                                placeholder="Bagikan pengalaman atau saran Anda untuk membantu kami meningkatkan pelayanan..."></textarea>
                        </div>

                        <button type="submit" class="btn-default mt-3 w-100" id="submitBtn" disabled>
                            <span class="submit-text">Kirim</span>
                            <span class="submit-loading d-none">
                                <i class="fas fa-spinner fa-spin me-2"></i>Memproses...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .success-icon-wrapper {
        width: 80px;
        height: 80px;
        background-color: var(--primary-color);
        border-radius: 50%;
        color: var(--white-color);
    }

    .success-icon {
        width: 40px;
        height: 40px;
    }

    .success-title {
        font-size: 2rem;
        font-weight: bold;
        color: var(--dark-color);
    }

    .success-subtitle {
        color: var(--text-muted-color);
        font-size: 0.875rem;
    }

    .success-divider {
        height: 3px;
        width: 80px;
        background-color: var(--primary-color);
        border-radius: 10px;
    }

    .feedback-card {
        background: var(--white-color);
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-top: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .feedback-title {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 0.875rem;
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

    .feedback-label {
        display: block;
        font-weight: bold;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        color: var(--dark-color);
    }

    .feedback-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        font-size: 1rem;
        line-height: 1.5;
        resize: vertical;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .feedback-textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        outline: 0;
    }

    .submit-loading .spinner-border {
        width: 1rem;
        height: 1rem;
    }

    @media (max-width: 768px) {
        .success-title {
            font-size: 1.5rem;
        }

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

        let currentRating = 0;

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
            highlightStars(rating);
            updateSubmitButton();
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

        function updateSubmitButton() {
            if (currentRating > 0) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (currentRating === 0) {
                alert('Silakan pilih rating terlebih dahulu');
                return;
            }

            submitText.classList.add('d-none');
            submitLoading.classList.remove('d-none');
            submitBtn.disabled = true;

            // this.submit();
        });

        updateSubmitButton();
    });
</script>
@endsection