<?php

/**
 * Satisfaction Survey Widget Component
 * Usage: <?= $this->include('frontend/components/satisfaction-widget') ?>
 */

$title = $title ?? 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum';
$subtitle = $subtitle ?? 'Bantu kami meningkatkan layanan informasi hukum dengan memberikan penilaian Anda.';
?>

<!-- Satisfaction Survey Section -->
<section id="satisfaction-survey" class="jdih-section bg-light py-5">
    <div class="container" data-aos="fade-up">
        <div class="section-heading text-center mb-5">
            <h2><?= esc($title) ?></h2>
            <p class="lead text-muted"><?= esc($subtitle) ?></p>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <div id="satisfaction-wrapper">
                    <div id="satisfaction-buttons" class="d-flex justify-content-center flex-wrap gap-3 gap-lg-4">
                        <button class="btn btn-lg btn-outline-success d-flex flex-column align-items-center p-4 rounded-3 shadow-sm feedback-btn"
                            data-feedback="puas"
                            style="min-width: 160px;">
                            <i class="far fa-laugh-beam fa-3x mb-2"></i>
                            <span class="fw-bold fs-5">Puas</span>
                        </button>

                        <button class="btn btn-lg btn-outline-primary d-flex flex-column align-items-center p-4 rounded-3 shadow-sm feedback-btn"
                            data-feedback="cukup"
                            style="min-width: 160px;">
                            <i class="far fa-meh fa-3x mb-2"></i>
                            <span class="fw-bold fs-5">Cukup Puas</span>
                        </button>

                        <button class="btn btn-lg btn-outline-danger d-flex flex-column align-items-center p-4 rounded-3 shadow-sm feedback-btn"
                            data-feedback="tidak"
                            style="min-width: 160px;">
                            <i class="far fa-frown fa-3x mb-2"></i>
                            <span class="fw-bold fs-5">Tidak Puas</span>
                        </button>
                    </div>

                    <div id="satisfaction-response" class="mt-4" style="display: none;">
                        <p class="fs-4 text-success fw-bold">
                            <i class="fas fa-check-circle me-2"></i>
                            Terima kasih atas masukan Anda!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced Styles -->
<style>
    .feedback-btn {
        transition: all 0.3s ease;
        border-width: 2px;
        position: relative;
        overflow: hidden;
        background-color: transparent;
    }
    
    /* Ensure buttons have visible colors by default */
    .feedback-btn.btn-outline-success {
        border-color: #198754 !important;
        color: #198754 !important;
    }
    
    .feedback-btn.btn-outline-primary {
        border-color: #0d6efd !important;
        color: #0d6efd !important;
    }
    
    .feedback-btn.btn-outline-danger {
        border-color: #dc3545 !important;
        color: #dc3545 !important;
    }

    .feedback-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s ease;
    }

    .feedback-btn:hover::before {
        left: 100%;
    }

    .feedback-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }

    .feedback-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.25);
    }

    .feedback-btn.btn-outline-success:hover {
        background: #198754 !important;
        border-color: #198754 !important;
        color: white !important;
    }

    .feedback-btn.btn-outline-primary:hover {
        background: #0d6efd !important;
        border-color: #0d6efd !important;
        color: white !important;
    }

    .feedback-btn.btn-outline-danger:hover {
        background: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
    }

    .feedback-btn i {
        transition: transform 0.3s ease;
    }

    .feedback-btn:hover i {
        transform: scale(1.1);
    }

    .feedback-btn:active {
        transform: translateY(-2px);
    }

    .feedback-btn.clicked {
        animation: pulse 0.6s ease-in-out;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    #satisfaction-response {
        animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .feedback-btn {
            min-width: 120px !important;
            padding: 1rem !important;
        }

        .feedback-btn i {
            font-size: 2rem !important;
        }

        .feedback-btn .fs-5 {
            font-size: 1rem !important;
        }
    }

    /* Accessibility improvements */
    .feedback-btn:focus-visible {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
    }

    /* Dark mode support - keep colors visible */
    @media (prefers-color-scheme: dark) {
        .feedback-btn.btn-outline-success {
            border-color: #198754 !important;
            color: #198754 !important;
            background-color: rgba(25, 135, 84, 0.1) !important;
        }
        
        .feedback-btn.btn-outline-primary {
            border-color: #0d6efd !important;
            color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.1) !important;
        }
        
        .feedback-btn.btn-outline-danger {
            border-color: #dc3545 !important;
            color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
    }
</style>

<!-- Enhanced JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const feedbackButtons = document.querySelectorAll('.feedback-btn');
        const responseDiv = document.getElementById('satisfaction-response');
        const buttonsContainer = document.getElementById('satisfaction-buttons');

        // Check if user has already provided feedback today
        const hasProvidedFeedback = localStorage.getItem('jdih_feedback_' + new Date().toDateString());

        if (hasProvidedFeedback) {
            showThankYouMessage();
            return;
        }

        feedbackButtons.forEach(button => {
            button.addEventListener('click', function() {
                const feedback = this.getAttribute('data-feedback');

                // Add clicked animation
                this.classList.add('clicked');

                // Disable all buttons
                feedbackButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                });

                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x mb-2"></i><span class="fw-bold">Mengirim...</span>';

                // Simulate API call
                setTimeout(() => {
                    submitFeedback(feedback);
                }, 1000);
            });

            // Keyboard navigation
            button.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        function submitFeedback(feedback) {
            // Here you would typically send to your backend
            // For now, we'll just simulate success

            // Store feedback to prevent duplicate submissions
            localStorage.setItem('jdih_feedback_' + new Date().toDateString(), feedback);

            // Track analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'satisfaction_feedback', {
                    'feedback_type': feedback,
                    'page_location': window.location.href
                });
            }

            // Show success message
            showThankYouMessage();

            // Optional: Send to server
            // fetch('/api/feedback', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token-hash"]').getAttribute('content')
            //     },
            //     body: JSON.stringify({
            //         feedback: feedback,
            //         page: window.location.pathname,
            //         timestamp: new Date().toISOString()
            //     })
            // });
        }

        function showThankYouMessage() {
            // Hide buttons
            buttonsContainer.style.display = 'none';

            // Show thank you message
            responseDiv.style.display = 'block';
            responseDiv.setAttribute('aria-live', 'polite');

            // Add confetti effect (optional)
            if (typeof confetti !== 'undefined') {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: {
                        y: 0.6
                    }
                });
            }
        }

        // Reset feedback at midnight
        function resetFeedbackAtMidnight() {
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(0, 0, 0, 0);

            const msUntilMidnight = tomorrow.getTime() - now.getTime();

            setTimeout(() => {
                localStorage.removeItem('jdih_feedback_' + new Date().toDateString());
                location.reload();
            }, msUntilMidnight);
        }

        resetFeedbackAtMidnight();
    });
</script>