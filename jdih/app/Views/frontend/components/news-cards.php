<?php

/**
 * News Cards Component
 * Usage: <?= $this->include('frontend/components/news-cards', ['news' => $latest_berita, 'title' => 'Berita Terbaru']) ?>
 */

$news = $news ?? [];
$title = $title ?? 'Berita & Informasi Terbaru';
$subtitle = $subtitle ?? 'Update berita dan informasi hukum terkini dari JDIH';


$sectionClass = $sectionClass ?? 'jdih-section bg-white';
$showViewAll = $showViewAll ?? true;
$viewAllUrl = $viewAllUrl ?? 'berita';
$viewAllText = $viewAllText ?? 'Lihat Semua Berita';
$maxDisplay = $maxDisplay ?? 6;
$displayType = $displayType ?? 'grid'; // grid or list
?>

<!-- Berita Terbaru -->
<section class="<?= esc($sectionClass) ?>">
    <div class="container">
        <div class="section-heading mb-5 text-center" data-aos="fade-up">
            <h2 class="h3 mb-2"><?= esc($title) ?></h2>
            <?php if (!empty($subtitle)): ?>
                <p class="text-secondary mb-0"><?= esc($subtitle) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($showViewAll): ?>
            <div class="d-flex justify-content-end mb-3">
                <a href="<?= base_url($viewAllUrl) ?>" class="btn btn-outline-primary btn-sm">
                    <?= esc($viewAllText) ?> <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        <?php endif; ?>

        <!-- DEBUG INFO (remove in production) -->
        <?php if (ENVIRONMENT === 'development'): ?>
            <div class="alert alert-warning">
                <strong>DEBUG News Cards:</strong>
                Count: <?= count($news ?? []) ?> |
                Type: <?= gettype($news ?? null) ?> |
                Empty: <?= empty($news) ? 'YES' : 'NO' ?>
                <?php if (!empty($news) && is_array($news)): ?>
                    | First item keys: <?= !empty($news[0]) ? implode(', ', array_keys($news[0])) : 'No first item' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($news) && is_array($news)): ?>
            <div class="row g-4">
                <?php
                $displayedCount = 0;
                foreach ($news as $index => $item):
                    if ($displayedCount >= $maxDisplay) break;

                    // Prepare news data
                    $newsUrl = 'berita/' . esc($item['slug'] ?? $item['id'], 'url');
                    $newsTitle = esc(mb_convert_case($item['judul'] ?? 'Berita Tanpa Judul', MB_CASE_TITLE, "UTF-8"));
                    $newsDate = format_tanggal_indo($item['tanggal_publish'] ?? $item['created_at'] ?? date('Y-m-d'));
                    $newsAuthor = esc(mb_convert_case($item['nama_penulis'] ?? $item['author'] ?? '', MB_CASE_TITLE, "UTF-8"));

                    // Prepare image - use jdih/uploads/berita/ path (sesuai struktur server)
                    $newsImage = base_url('assets/img/news1.jpg'); // fallback placeholder
                    if (!empty($item['gambar'])) {
                        // Correct server path: /var/www/jdih/jdih/uploads/berita/
                        $imagePath = 'jdih/uploads/berita/' . $item['gambar'];
                        
                        // Check if file exists
                        if (file_exists(FCPATH . $imagePath)) {
                            $newsImage = base_url($imagePath);
                        }
                    }

                    // Prepare excerpt
                    $content = strip_tags($item['isi_berita'] ?? $item['content'] ?? '');
                    $excerpt = (mb_strlen($content) > 150) ? mb_substr($content, 0, 120) . '...' : $content;

                    // Column class based on display type and position
                    $colClass = 'col-lg-4 col-md-6';

                    $displayedCount++;
                ?>
                    <div class="<?= $colClass ?>" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <article class="news-card h-100 shadow-sm rounded overflow-hidden">
                            <div class="news-image-container">
                                <a href="<?= base_url($newsUrl) ?>">
                                    <img src="<?= $newsImage ?>"
                                        alt="<?= $newsTitle ?>"
                                        class="news-card-image"
                                        loading="lazy">
                                </a>
                            </div>
                            <div class="news-card-body p-4">
                                <div class="news-meta mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i> <?= $newsDate ?>
                                        <?php if (!empty($newsAuthor)): ?>
                                            <span class="ms-2"><i class="fas fa-user me-1"></i> <?= $newsAuthor ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <h3 class="h5 news-title mb-2">
                                    <a href="<?= base_url($newsUrl) ?>" class="text-decoration-none text-dark">
                                        <?= $newsTitle ?>
                                    </a>
                                </h3>
                                <p class="news-excerpt text-muted mb-3"><?= esc($excerpt) ?></p>
                                <a href="<?= base_url($newsUrl) ?>" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-newspaper me-1"></i> Baca Selengkapnya
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="alert alert-light border" role="alert">
                    <i class="fas fa-newspaper fa-3x text-primary mb-3"></i>
                    <h3 class="h5 alert-heading">Belum Ada Berita</h3>
                    <p class="mb-0 text-muted">Saat ini belum ada berita yang dipublikasikan. Silakan periksa kembali nanti.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($showViewAll && !empty($news)): ?>
            <div class="text-center mt-5">
                <a href="<?= base_url($viewAllUrl) ?>"
                    class="btn btn-primary">
                    <i class="fas fa-newspaper me-2"></i>
                    Baca Semua Berita
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Enhanced Styles -->
<style>
    /* Grid Layout Styles */
    .news-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        background: white;
        overflow: hidden;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
    }

    .news-image-container {
        position: relative;
        overflow: hidden;
    }

    .news-card-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .news-card:hover .news-card-image {
        transform: scale(1.05);
    }

    .news-featured-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        z-index: 2;
    }

    .news-title a {
        font-weight: 600;
        line-height: 1.3;
        transition: color 0.2s ease;
    }

    .news-title a:hover {
        color: #0d6efd !important;
    }

    .news-excerpt {
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .news-meta {
        font-size: 0.85rem;
    }

    /* List Layout Styles */
    .news-card-list {
        transition: box-shadow 0.3s ease;
        border: none;
        background: white;
    }

    .news-card-list:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    .news-list-image {
        width: 200px;
        height: 150px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .news-content-container {
        display: flex;
        flex-direction: column;
    }

    /* Featured layout adjustments */
    .news-card.featured {
        height: auto;
    }

    .news-card.featured .news-card-image {
        height: 300px;
    }

    .news-card.featured .news-title {
        font-size: 1.5rem;
    }

    .news-card.featured .news-excerpt {
        font-size: 1.1rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .news-list-image {
            width: 120px;
            height: 100px;
        }

        .news-content-container {
            padding: 1rem !important;
        }

        .news-card-list {
            flex-direction: column;
        }

        .news-list-image {
            width: 100%;
            height: 200px;
        }

        .news-card-image {
            height: 180px;
        }

        .news-card.featured .news-card-image {
            height: 220px;
        }
    }

    /* Loading states */
    .news-card.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .news-card.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
        animation: loading-shimmer 1.5s infinite;
    }

    @keyframes loading-shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    /* Accessibility improvements */
    .news-card:focus-within {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
    }

    .news-title a:focus {
        outline: none;
    }
</style>

<!-- Enhanced JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const newsCards = document.querySelectorAll('.news-card, .news-card-list');

        newsCards.forEach(card => {
            // Add loading state on navigation
            const links = card.querySelectorAll('a[href*="berita/"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    card.classList.add('loading');
                });
            });

            // Improve keyboard navigation
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    const titleLink = this.querySelector('.news-title a');
                    if (titleLink) {
                        e.preventDefault();
                        titleLink.click();
                    }
                }
            });

            // Add accessibility attributes
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'article');

            // Lazy loading for images
            const images = card.querySelectorAll('img');
            images.forEach(img => {
                if ('loading' in HTMLImageElement.prototype) {
                    img.loading = 'lazy';
                } else {
                    // Fallback for older browsers
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src || img.src;
                                observer.unobserve(img);
                            }
                        });
                    });
                    observer.observe(img);
                }
            });
        });

        // Analytics tracking
        const trackNewsClick = function(newsTitle, newsUrl) {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'news_click', {
                    'news_title': newsTitle,
                    'news_url': newsUrl
                });
            }
        };

        // Attach analytics to news links
        document.querySelectorAll('a[href*="berita/"]').forEach(link => {
            link.addEventListener('click', function() {
                const title = this.textContent.trim();
                const url = this.href;
                trackNewsClick(title, url);
            });
        });
    });
</script>