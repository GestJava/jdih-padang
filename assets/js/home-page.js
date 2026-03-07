/**
 * JDIH Home Page Optimized JavaScript
 * Performance-optimized and modular approach
 */

class JDIHHomePage {
    constructor() {
        this.init();
    }

    init() {
        this.initWelcomeModal();
        this.initSatisfactionSurvey();
        this.initImageOptimization();
        this.initPerformanceMetrics();
    }

    /**
     * Welcome Modal Handler - Optimized
     */
    initWelcomeModal() {
        const welcomeModalEl = document.getElementById('welcomeModal');
        if (!welcomeModalEl) return;

        // Use event delegation for better performance
        const modal = new bootstrap.Modal(welcomeModalEl);
        const today = new Date().toDateString();
        const lastShown = localStorage.getItem('welcomeModalLastShown');

        // Only show if not shown today
        if (lastShown !== today) {
            modal.show();
        }

        // Handle checkbox with debouncing
        const dontShowCheckbox = document.getElementById('dontShowAgain');
        if (dontShowCheckbox) {
            dontShowCheckbox.addEventListener('change', this.debounce((e) => {
                if (e.target.checked) {
                    localStorage.setItem('welcomeModalLastShown', today);
                } else {
                    localStorage.removeItem('welcomeModalLastShown');
                }
            }, 300));
        }
    }

    /**
     * Satisfaction Survey - Optimized with Rate Limiting
     */
    initSatisfactionSurvey() {
        const satisfactionButtons = document.getElementById('satisfaction-buttons');
        const satisfactionResponse = document.getElementById('satisfaction-response');
        
        if (!satisfactionButtons || !satisfactionResponse) return;

        // Check if already submitted
        if (sessionStorage.getItem('jdihFeedbackSubmitted')) {
            this.showThanksMessage(satisfactionButtons, satisfactionResponse);
            return;
        }

        // Use event delegation for better performance
        satisfactionButtons.addEventListener('click', this.handleFeedbackClick.bind(this));
    }

    /**
     * Handle feedback click with optimized AJAX
     */
    handleFeedbackClick(e) {
        const button = e.target.closest('.feedback-btn');
        if (!button) return;

        const feedback = button.dataset.feedback;
        const satisfactionButtons = document.getElementById('satisfaction-buttons');
        const satisfactionResponse = document.getElementById('satisfaction-response');

        // Prevent double submission
        if (button.disabled) return;
        button.disabled = true;

        // Optimized AJAX request
        this.submitFeedback(feedback)
            .then(response => {
                if (response.success) {
                    this.showThanksMessage(satisfactionButtons, satisfactionResponse);
                    sessionStorage.setItem('jdihFeedbackSubmitted', 'true');
                } else {
                    console.error('Feedback submission failed:', response.message);
                }
            })
            .catch(error => {
                console.error('Network error:', error);
            })
            .finally(() => {
                button.disabled = false;
            });
    }

    /**
     * Optimized AJAX feedback submission
     */
    async submitFeedback(feedback) {
        const csrfTokenName = document.querySelector('meta[name="csrf-token-name"]')?.content;
        const csrfTokenHash = document.querySelector('meta[name="csrf-token-hash"]')?.content;

        const formData = new FormData();
        formData.append('feedback', feedback);
        if (csrfTokenName && csrfTokenHash) {
            formData.append(csrfTokenName, csrfTokenHash);
        }

        try {
            const response = await fetch('/feedback/submit', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    }

    /**
     * Show thanks message
     */
    showThanksMessage(buttonsEl, responseEl) {
        if (buttonsEl && responseEl) {
            buttonsEl.style.display = 'none';
            responseEl.style.display = 'block';
        }
    }

    /**
     * Image Optimization - Lazy Loading Enhancement
     */
    initImageOptimization() {
        // Enhanced lazy loading for images
        const images = document.querySelectorAll('img[data-src]');
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            images.forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                img.classList.add('loaded');
            });
        }
    }

    /**
     * Performance Metrics Collection
     */
    initPerformanceMetrics() {
        if ('performance' in window) {
            window.addEventListener('load', () => {
                // Collect performance metrics
                const perfData = performance.getEntriesByType('navigation')[0];
                const metrics = {
                    loadTime: perfData.loadEventEnd - perfData.loadEventStart,
                    domReady: perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart,
                    firstPaint: performance.getEntriesByType('paint').find(entry => entry.name === 'first-paint')?.startTime,
                    firstContentfulPaint: performance.getEntriesByType('paint').find(entry => entry.name === 'first-contentful-paint')?.startTime
                };

                // Send to analytics (optional)
                console.log('Page Performance Metrics:', metrics);
            });
        }
    }

    /**
     * Utility: Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new JDIHHomePage();
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', () => {
        const loadTime = performance.now();
        console.log(`Home page loaded in ${loadTime.toFixed(2)}ms`);
    });
} 