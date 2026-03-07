<?php

/**
 * Hero Search Component
 * 
 * Komponen reusable untuk menampilkan hero section dengan form pencarian dokumen hukum.
 * 
 * @param array $popular_tags Array of popular tags [['slug_tag' => string, 'nama_tag' => string]]
 * @param array $all_jenis Array of document types [['id_jenis_peraturan' => int, 'nama_jenis' => string]]
 * @param array $all_status Array of document statuses [['id' => int, 'nama_status' => string]]
 * @param string $heroTitle Hero section title (default: 'Jaringan Dokumentasi dan Informasi Hukum')
 * @param string $heroSubtitle Hero section subtitle (default: 'Akses cepat dan mudah...')
 * @param string $searchTitle Form search title (default: 'Pencarian Dokumen Hukum')
 * @param string $searchPlaceholder Input placeholder (default: 'Cari dokumen hukum...')
 * @param string $searchButtonText Submit button text (default: 'Cari Dokumen')
 * @param string $searchAction Form action URL (default: 'peraturan')
 * @param string $currentKeyword Current keyword for pre-fill (default: '')
 * @param bool $showAdvancedSearch Show/hide advanced search panel (default: true)
 * @param string $heroImage Hero image path (default: 'assets/img/hero-image.webp')
 * @param string $heroImageAlt Hero image alt text (default: 'JDIH Hero...')
 * @param int $yearStart Start year for year dropdown (default: 2000)
 * 
 * Usage: 
 * <?= view('frontend/components/hero-search', [
 *     'popular_tags' => $tags,
 *     'all_jenis' => $jenis,
 *     'all_status' => $status
 * ]) ?>
 */

// Validasi dan sanitasi data input
if (!is_array($popular_tags ?? null)) {
    $popular_tags = [];
}

if (!is_array($all_jenis ?? null)) {
    $all_jenis = [];
}

if (!is_array($all_status ?? null)) {
    $all_status = [];
}

// Logging untuk debugging - SELALU log di production untuk troubleshooting
if (empty($popular_tags)) {
    log_message('info', 'hero-search: popular_tags is empty');
} else {
    log_message('info', 'hero-search: popular_tags count = ' . count($popular_tags));
    log_message('info', 'hero-search: popular_tags = ' . json_encode($popular_tags));
}
if (empty($all_jenis)) {
    log_message('info', 'hero-search: all_jenis is empty');
}
if (empty($all_status)) {
    log_message('info', 'hero-search: all_status is empty');
}

// Validasi tahun
$currentYear = date('Y');
$startYear = $yearStart ?? 2000;
$startYear = max($startYear, 1990); // Minimum tahun 1990
$startYear = min($startYear, $currentYear); // Maksimum tahun sekarang
?>

<!-- Hero Section -->
<section class="jdih-hero jdih-hero-optimized">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6 text-start" data-aos="fade-right">
                <div class="hero-badge mb-3 text-start">
                    <span><i class="fas fa-check-circle icon-sm me-1"></i> Resmi Pemerintah Kota Padang</span>
                </div>
                <h1 class="hero-title text-start"><?= esc($heroTitle ?? 'Jaringan Dokumentasi dan Informasi Hukum') ?></h1>
                <p class="hero-subtitle text-start"><?= esc($heroSubtitle ?? 'Akses cepat dan mudah untuk seluruh dokumentasi dan informasi hukum yang Anda butuhkan dalam satu platform terintegrasi.') ?></p>

                <div class="hero-search-box">
                    <form action="<?= base_url($searchAction ?? 'peraturan') ?>" method="get" id="searchForm">
                        <!-- Honeypot field untuk anti-bot (hidden dengan CSS dan HTML) -->
                        <input type="text" 
                            name="honeypot" 
                            style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0;"
                            tabindex="-1" 
                            autocomplete="off"
                            aria-hidden="true">
                        <h2 class="h5 mb-3 text-primary">
                            <i class="fas fa-search me-2"></i><?= esc($searchTitle ?? 'Pencarian Dokumen Hukum') ?>
                        </h2>

                        <div class="input-group mb-3 search-input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-primary"></i>
                            </span>
                            <input type="text"
                                class="form-control border-start-0"
                                name="keyword"
                                placeholder="<?= esc($searchPlaceholder ?? 'Cari dokumen hukum...') ?>"
                                data-original-placeholder="<?= esc($searchPlaceholder ?? 'Cari dokumen hukum...') ?>"
                                aria-label="Search"
                                value="<?= esc($currentKeyword ?? '') ?>"
                                maxlength="255">
                            <button class="btn btn-primary search-btn" type="submit" aria-label="<?= esc($searchButtonText ?? 'Cari Dokumen') ?>">
                                <i class="fas fa-search me-1 d-md-none"></i>
                                <span class="d-none d-md-inline"><?= esc($searchButtonText ?? 'Cari Dokumen') ?></span>
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Popular Tags -->
                            <div class="popular-tags" role="list" aria-label="Tag populer">
                                <span class="me-2 small text-secondary">Populer:</span>
                                <?php 
                                // Debug: Log popular_tags untuk troubleshooting - SELALU log di production
                                log_message('info', 'hero-search render: popular_tags = ' . json_encode($popular_tags));
                                log_message('info', 'hero-search render: popular_tags empty = ' . (empty($popular_tags) ? 'yes' : 'no'));
                                log_message('info', 'hero-search render: popular_tags is_array = ' . (is_array($popular_tags) ? 'yes' : 'no'));
                                if (!empty($popular_tags) && is_array($popular_tags)) {
                                    log_message('info', 'hero-search render: popular_tags count = ' . count($popular_tags));
                                }
                                ?>
                                <?php if (!empty($popular_tags) && is_array($popular_tags)) : ?>
                                    <?php 
                                    $hasValidTags = false;
                                    foreach ($popular_tags as $tag) : 
                                        // Validasi struktur tag
                                        if (!isset($tag['slug_tag']) || !isset($tag['nama_tag'])) {
                                            continue; // Skip invalid tag
                                        }
                                        $hasValidTags = true;
                                    ?>
                                        <a href="<?= base_url(($searchAction ?? 'peraturan') . '?keyword=' . esc(urlencode($tag['nama_tag']), 'url')) ?>"
                                            class="badge rounded-pill popular-tag me-1"
                                            role="listitem"
                                            title="Cari: <?= esc($tag['nama_tag']) ?>">
                                            <?= esc(ucwords(strtolower($tag['nama_tag']))) ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (!$hasValidTags) : ?>
                                        <span class="badge rounded-pill popular-tag me-1">Tidak ada tag</span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="badge rounded-pill popular-tag me-1">Tidak ada tag</span>
                                <?php endif; ?>
                            </div>

                            <!-- Advanced Search Toggle -->
                            <?php if ($showAdvancedSearch ?? true): ?>
                                <button type="button"
                                    id="advancedSearchToggle"
                                    class="text-primary advanced-search-toggle btn btn-link p-0"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#advancedSearchPanel"
                                    aria-expanded="false"
                                    aria-controls="advancedSearchPanel">
                                    <i class="fas fa-sliders-h me-1" aria-hidden="true"></i> Advanced Search
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Advanced Search Panel -->
                        <?php if ($showAdvancedSearch ?? true): ?>
                            <div class="collapse advanced-search-panel bg-light rounded"
                                id="advancedSearchPanel"
                                role="region"
                                aria-label="Pencarian lanjutan">
                                <div class="row g-3">
                                    <!-- Jenis Peraturan -->
                                    <div class="col-md-6">
                                        <label for="jenis" class="form-label">Jenis Peraturan</label>
                                        <select name="jenis" id="jenis" class="form-select" aria-label="Pilih jenis peraturan">
                                            <option value="">Semua Jenis</option>
                                            <?php if (!empty($all_jenis) && is_array($all_jenis)) : ?>
                                                <?php foreach ($all_jenis as $jenis) : ?>
                                                    <?php 
                                                    // Validasi struktur jenis
                                                    if (!isset($jenis['id_jenis_peraturan']) || !isset($jenis['nama_jenis'])) {
                                                        continue; // Skip invalid jenis
                                                    }
                                                    ?>
                                                    <option value="<?= esc($jenis['id_jenis_peraturan']) ?>">
                                                        <?= esc($jenis['nama_jenis']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <!-- Tahun -->
                                    <div class="col-md-6">
                                        <label for="tahun" class="form-label">Tahun</label>
                                        <select name="tahun" id="tahun" class="form-select" aria-label="Pilih tahun peraturan">
                                            <option value="">Semua Tahun</option>
                                            <?php
                                            // Gunakan $startYear dan $currentYear yang sudah divalidasi di atas
                                            for ($year = $currentYear; $year >= $startYear; $year--) {
                                                // FIX: Escape tahun meskipun angka (best practice)
                                                echo '<option value="' . esc((string)$year) . '">' . esc((string)$year) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Status -->
                                    <?php if (!empty($all_status) && is_array($all_status)): ?>
                                        <div class="col-md-6">
                                            <label for="status" class="form-label">Status</label>
                                            <select name="status" id="status" class="form-select" aria-label="Pilih status peraturan">
                                                <option value="">Semua Status</option>
                                                <?php foreach ($all_status as $status) : ?>
                                                    <?php 
                                                    // Validasi struktur status
                                                    if (!isset($status['id']) || !isset($status['nama_status'])) {
                                                        continue; // Skip invalid status
                                                    }
                                                    ?>
                                                    <option value="<?= esc($status['id']) ?>">
                                                        <?= esc($status['nama_status']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Sort -->
                                    <div class="col-md-6">
                                        <label for="sort" class="form-label">Urutkan</label>
                                        <select name="sort" id="sort" class="form-select" aria-label="Pilih urutan hasil">
                                            <option value="terbaru">Terbaru</option>
                                            <option value="terlama">Terlama</option>
                                            <option value="populer">Terpopuler</option>
                                            <option value="abjad">A-Z</option>
                                        </select>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-12 text-end">
                                        <button type="reset"
                                            class="btn btn-outline-secondary me-2"
                                            aria-label="Reset form pencarian">
                                            <i class="fas fa-undo-alt me-1" aria-hidden="true"></i> Reset
                                        </button>
                                        <button type="submit"
                                            class="btn btn-primary"
                                            aria-label="Terapkan filter pencarian">
                                            <i class="fas fa-filter me-1" aria-hidden="true"></i> Terapkan Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Hero Image -->
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                <img src="<?= base_url($heroImage ?? 'assets/img/hero-image.webp') ?>"
                    alt="<?= esc($heroImageAlt ?? 'JDIH Hero - Jaringan Dokumentasi dan Informasi Hukum Kota Padang') ?>"
                    class="img-fluid hero-image"
                    width="600"
                    height="400"
                    loading="eager">
            </div>
        </div>
    </div>
</section>




<!-- Enhanced JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const advancedToggle = document.getElementById('advancedSearchToggle');
        const advancedPanel = document.getElementById('advancedSearchPanel');

        // Enhanced form submission with validation
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const keyword = this.querySelector('input[name="keyword"]').value.trim();

                // Basic validation
                if (!keyword && !hasAdvancedFilters()) {
                    e.preventDefault();
                    showSearchHint();
                    return false;
                }

                // Track search analytics (sanitize keyword untuk mencegah XSS)
                if (typeof gtag !== 'undefined') {
                    try {
                        // Sanitize keyword untuk Google Analytics (remove special chars)
                        const sanitizedKeyword = keyword.replace(/[<>'"]/g, '');
                        gtag('event', 'search', {
                            'search_term': sanitizedKeyword
                        });
                    } catch (e) {
                        // Silent fail - jangan break form submission jika tracking gagal
                        console.warn('Google Analytics tracking failed:', e);
                    }
                }
            });

            // Reset functionality
            const resetBtn = searchForm.querySelector('button[type="reset"]');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    setTimeout(() => {
                        const keywordInput = searchForm.querySelector('input[name="keyword"]');
                        if (keywordInput) keywordInput.focus();
                    }, 100);
                });
            }
        }

        // Advanced search panel toggle enhancement
        if (advancedToggle && advancedPanel) {
            advancedPanel.addEventListener('shown.bs.collapse', function() {
                advancedToggle.innerHTML = '<i class="fas fa-sliders-h me-1" aria-hidden="true"></i> Tutup Advanced Search';
            });

            advancedPanel.addEventListener('hidden.bs.collapse', function() {
                advancedToggle.innerHTML = '<i class="fas fa-sliders-h me-1" aria-hidden="true"></i> Advanced Search';
            });
        }

        // Helper functions
        function hasAdvancedFilters() {
            const selects = searchForm.querySelectorAll('select');
            return Array.from(selects).some(select => select.value !== '');
        }

        function showSearchHint() {
            const keywordInput = searchForm.querySelector('input[name="keyword"]');
            if (keywordInput) {
                keywordInput.focus();
                keywordInput.style.borderColor = '#dc3545';
                keywordInput.setAttribute('placeholder', 'Masukkan kata kunci pencarian...');
                
                setTimeout(() => {
                    keywordInput.style.borderColor = '';
                    // FIX: Gunakan data attribute untuk menghindari XSS
                    const originalPlaceholder = keywordInput.getAttribute('data-original-placeholder') || 'Cari dokumen hukum...';
                    keywordInput.setAttribute('placeholder', originalPlaceholder);
                }, 3000);
            }
        }
    });
</script>
