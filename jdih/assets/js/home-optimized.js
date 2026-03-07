/**
 * JDIH Home Optimized JavaScript
 * Optimized for performance and user experience
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Initialize all components
    initLazyLoading();
    initAnimations();
    initServiceCards();
    initSatisfactionSurvey();
    initPerformanceMonitoring();

    console.log('JDIH Home Optimized loaded successfully');
});

/**
 * Lazy Loading Implementation
 */
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if (!('IntersectionObserver' in window)) {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.add('loaded');
        });
        return;
    }

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                loadImage(img);
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.01
    });

    lazyImages.forEach(img => imageObserver.observe(img));
}

function loadImage(img) {
    const src = img.dataset.src;
    if (!src) return;

    // Create a new image to preload
    const tempImage = new Image();
    
    tempImage.onload = function() {
        img.src = src;
        img.classList.add('loaded');
        img.removeAttribute('data-src');
    };

    tempImage.onerror = function() {
        img.classList.add('error');
        console.warn('Failed to load image:', src);
    };

    tempImage.src = src;
}

/**
 * AOS Animations
 */
function initAnimations() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100,
            delay: 0,
            anchorPlacement: 'top-bottom'
        });
    }
}

/**
 * Service Cards Interactions
 */
function initServiceCards() {
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.07)';
        });

        // Add click tracking
        card.addEventListener('click', function() {
            const serviceName = this.querySelector('.service-title')?.textContent || 'Unknown Service';
            trackEvent('service_click', {
                service_name: serviceName,
                category: 'engagement'
            });
        });
    });
}

/**
 * Satisfaction Survey
 */
function initSatisfactionSurvey() {
    const feedbackButtons = document.querySelectorAll('.feedback-btn');
    const responseDiv = document.getElementById('satisfaction-response');
    const buttonsContainer = document.getElementById('satisfaction-buttons');

    if (!feedbackButtons.length || !responseDiv || !buttonsContainer) return;

    feedbackButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const feedback = this.getAttribute('data-feedback');
            const feedbackText = this.textContent.trim();

            // Hide buttons and show response
            buttonsContainer.style.display = 'none';
            responseDiv.style.display = 'block';

            // Update response message
            const responseText = responseDiv.querySelector('.response-text');
            if (responseText) {
                responseText.textContent = `Terima kasih atas feedback "${feedbackText}" Anda!`;
            }

            // Track feedback
            trackEvent('satisfaction_feedback', {
                feedback_type: feedback,
                feedback_text: feedbackText,
                category: 'user_feedback'
            });

            // Store in localStorage to avoid showing again
            localStorage.setItem('jdih_feedback_submitted', 'true');
            localStorage.setItem('jdih_feedback_type', feedback);
        });
    });

    // Check if user already submitted feedback
    if (localStorage.getItem('jdih_feedback_submitted') === 'true') {
        buttonsContainer.style.display = 'none';
        responseDiv.style.display = 'block';
    }
}

/**
 * Performance Monitoring
 */
function initPerformanceMonitoring() {
    if (!('performance' in window)) return;

    window.addEventListener('load', function() {
        setTimeout(function() {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (!perfData) return;

            const metrics = {
                loadTime: perfData.loadEventEnd - perfData.navigationStart,
                domContentLoaded: perfData.domContentLoadedEventEnd - perfData.navigationStart,
                firstPaint: 0,
                firstContentfulPaint: 0
            };

            // Get paint timing if available
            const paintEntries = performance.getEntriesByType('paint');
            paintEntries.forEach(entry => {
                if (entry.name === 'first-paint') {
                    metrics.firstPaint = entry.startTime;
                }
                if (entry.name === 'first-contentful-paint') {
                    metrics.firstContentfulPaint = entry.startTime;
                }
            });

            // Log performance metrics
            console.log('JDIH Performance Metrics:', metrics);

            // Track slow loading
            if (metrics.loadTime > 3000) {
                console.warn('Page load time exceeded 3 seconds:', metrics.loadTime + 'ms');
                trackEvent('performance_warning', {
                    load_time: metrics.loadTime,
                    category: 'performance'
                });
            }

            // Track performance data
            trackEvent('page_performance', {
                load_time: metrics.loadTime,
                dom_ready: metrics.domContentLoaded,
                first_paint: metrics.firstPaint,
                first_contentful_paint: metrics.firstContentfulPaint,
                category: 'performance'
            });

        }, 0);
    });
}

/**
 * Analytics Tracking
 */
function trackEvent(eventName, parameters = {}) {
    // Google Analytics 4
    if (typeof gtag !== 'undefined') {
        gtag('event', eventName, {
            custom_parameters: parameters,
            ...parameters
        });
    }

    // Facebook Pixel
    if (typeof fbq !== 'undefined') {
        fbq('track', eventName, parameters);
    }

    // Custom analytics
    console.log('Event tracked:', eventName, parameters);
}

/**
 * Utility Functions
 */
function debounce(func, wait) {
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

// Optimize scroll events
const optimizedScrollHandler = debounce(function() {
    // Handle scroll-based animations or interactions
}, 16);

window.addEventListener('scroll', optimizedScrollHandler);

// Handle visibility change for performance
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, pause heavy operations
        console.log('Page hidden, pausing heavy operations');
    } else {
        // Page is visible, resume operations
        console.log('Page visible, resuming operations');
    }
}); 