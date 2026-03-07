/**
 * JDIH News Interaction JS - Lightweight Version
 * Minimal functionality for news section to improve performance
 * Updated: June 2025
 */

// Gunakan event listener dengan opsi once:true untuk memastikan hanya dijalankan sekali
document.addEventListener('DOMContentLoaded', function() {
    // Jalankan inisialisasi hanya jika elemen berita ada di halaman
    if (document.querySelector('.card')) {
        // Handle image error loading - fungsi paling penting
        handleImageErrors();
    }
}, {once: true});

/**
 * Handle image errors by replacing with placeholder
 */
function handleImageErrors() {
    const newsImages = document.querySelectorAll('.card img');
    const placeholderImage = '/assets/img/news/placeholder-news.jpg';
    
    if (newsImages.length === 0) return;
    
    // Gunakan loop for tradisional untuk performa lebih baik
    for (let i = 0; i < newsImages.length; i++) {
        newsImages[i].addEventListener('error', function() {
            this.src = placeholderImage;
            this.alt = 'Placeholder Image';
        }, {once: true}); // Gunakan once:true untuk mengurangi memory leak
    }
}
