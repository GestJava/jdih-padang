<?php

/**
 * Document Cards Component
 * Usage: <?= $this->include('frontend/components/document-cards', ['documents' => $latest_peraturan, 'title' => 'Dokumen Terbaru']) ?>
 */

$documents = $documents ?? [];
$title = $title ?? 'Dokumen Terbaru';
$subtitle = $subtitle ?? 'Dokumen hukum yang baru ditambahkan';


$sectionClass = $sectionClass ?? 'jdih-section jdih-documents bg-light';
$showViewAll = $showViewAll ?? true;
$viewAllUrl = $viewAllUrl ?? 'peraturan';
$viewAllText = $viewAllText ?? 'Lihat Semua Dokumen';
$maxDisplay = $maxDisplay ?? 8;
$bgColors = $bgColors ?? ['bg-primary', 'bg-success', 'bg-info', 'bg-warning'];
?>

<!-- Dokumen Terbaru -->
<section class="<?= esc($sectionClass) ?>">
    <div class="container">
        <div class="section-heading text-center mb-5" data-aos="fade-up">
            <h2><?= esc($title) ?></h2>
            <?php if (!empty($subtitle)): ?>
                <p><?= esc($subtitle) ?></p>
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
            <div class="alert alert-info">
                <strong>DEBUG Document Cards:</strong>
                Count: <?= count($documents ?? []) ?> |
                Type: <?= gettype($documents ?? null) ?> |
                Empty: <?= empty($documents) ? 'YES' : 'NO' ?>
                <?php if (!empty($documents) && is_array($documents)): ?>
                    | First item keys: <?= !empty($documents[0]) ? implode(', ', array_keys($documents[0])) : 'No first item' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (!empty($documents) && is_array($documents)): ?>
                <?php
                $displayedCount = 0;
                foreach ($documents as $index => $item):
                    if ($displayedCount >= $maxDisplay) break;

                    $color_index = $index % count($bgColors);
                    $bg_color = $bgColors[$color_index];

                    // Format tanggal
                    if (isset($item['tgl_penetapan']) && !empty($item['tgl_penetapan'])) {
                        $tanggal_formatted = format_tanggal_indo($item['tgl_penetapan']);
                    } else {
                        $tanggal_formatted = '-';
                    }

                    // Prepare document URL
                    $documentUrl = 'peraturan/' . esc($item['slug'] ?? $item['id_peraturan'], 'url');

                    // Format title with number and year
                    $documentTitle = '';
                    if (isset($item['nomor'])) {
                        $documentTitle .= 'No. ' . esc($item['nomor']);
                    }
                    if (isset($item['tahun'])) {
                        $documentTitle .= ' Tahun ' . esc($item['tahun']);
                    }

                    // Format judul
                    $judul = mb_convert_case($item['judul'] ?? '', MB_CASE_TITLE, "UTF-8");
                    $judulTrimmed = (mb_strlen($judul) > 100) ? mb_substr($judul, 0, 75) . '...' : $judul;

                    $displayedCount++;
                ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="card h-100 shadow-sm document-card">
                            <div class="card-header <?= $bg_color ?> text-white d-flex justify-content-between align-items-center">
                                <span class="document-type text-truncate me-2">
                                     <?= esc(mb_convert_case($item['nama_jenis'] ?? 'Umum', MB_CASE_TITLE, "UTF-8")) ?>
                                 </span>
                                <small class="document-date"><?= $tanggal_formatted ?></small>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <h3 class="h5 card-title mb-2">
                                    <a href="<?= base_url($documentUrl) ?>"
                                        class="text-decoration-none text-dark document-title-link"
                                        title="<?= esc($documentTitle) ?>">
                                        <?= $documentTitle ?>
                                    </a>
                                </h3>

                                <p class="card-text flex-grow-1 document-description">
                                    <?= esc($judulTrimmed) ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?= $tanggal_formatted ?>
                                    </small>
                                    <div class="document-file-indicator">
                                        <?php if (isset($item['file_dokumen']) && !empty($item['file_dokumen'])): ?>
                                            <i class="fas fa-file-pdf text-danger" title="Dokumen PDF tersedia"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file-alt text-secondary" title="Dokumen teks"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-top-0">
                                <a href="<?= base_url($documentUrl) ?>"
                                    class="btn btn-sm btn-outline-primary w-100 document-detail-btn">
                                    <i class="fas fa-eye me-1"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-5" role="alert">
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h3 class="h5 alert-heading">Belum Ada Dokumen</h3>
                        <p class="mb-0">Saat ini belum ada dokumen hukum yang ditampilkan. Silakan periksa kembali nanti.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($showViewAll && !empty($documents)): ?>
            <div class="text-center mt-4">
                <a href="<?= base_url($viewAllUrl) ?>"
                    class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Jelajahi Semua Dokumen
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Enhanced Styles -->
<style>
    .document-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        overflow: hidden;
    }

    .document-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .document-card .card-header {
        border-bottom: none;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .document-type {
        flex-grow: 1;
    }

    .document-date {
        white-space: nowrap;
        font-size: 0.8rem;
    }

    .document-title-link {
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .document-title-link:hover {
        color: #0d6efd !important;
    }

    .document-description {
        font-size: 0.95rem;
        line-height: 1.5;
        color: #6c757d;
    }

    .document-file-indicator i {
        font-size: 1.1rem;
    }

    .document-detail-btn {
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .document-detail-btn:hover {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
        transform: translateY(-1px);
    }

    .card-title {
        font-size: 1.1rem;
        line-height: 1.3;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .document-card .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .document-date {
            font-size: 0.75rem;
        }
    }

    /* Loading animation */
    .document-card.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .document-card.loading::after {
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
</style>

<!-- Enhanced JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced card interactions
        const documentCards = document.querySelectorAll('.document-card');

        documentCards.forEach(card => {
            // Add loading state on click
            const detailBtn = card.querySelector('.document-detail-btn');
            if (detailBtn) {
                detailBtn.addEventListener('click', function(e) {
                    // Add loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memuat...';
                    this.disabled = true;
                    card.classList.add('loading');
                });
            }

            // Improve accessibility
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    const titleLink = this.querySelector('.document-title-link');
                    if (titleLink) {
                        e.preventDefault();
                        titleLink.click();
                    }
                }
            });

            // Add focus management
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'article');
        });

        // Lazy loading for performance
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('loaded');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });

        documentCards.forEach(card => {
            observer.observe(card);
        });
    });
</script>