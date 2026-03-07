<?= $this->extend('layouts/frontend') ?>

<?= $this->section('styles') ?>
<!-- ✅ HOME PAGE SPECIFIC STYLES -->
<style>
    /* Lazy loading placeholder */
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }

    .lazy.loaded {
        opacity: 1;
    }

    /* Component spacing */
    .jdih-section {
        padding: 4rem 0;
    }

    @media (max-width: 768px) {
        .jdih-section {
            padding: 2rem 0;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- ✅ HOME PAGE SPECIFIC SCRIPTS -->

<!-- AOS Animation Library for Home Page -->
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS animations
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                offset: 100
            });
        }

        // Initialize lazy loading
        const lazyImages = document.querySelectorAll('img[data-src]');

        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.dataset.src;
                        if (src) {
                            img.src = src;
                            img.classList.add('loaded');
                            observer.unobserve(img);
                        }
                    }
                });
            });

            lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            lazyImages.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                }
            });
        }

        // Initialize satisfaction survey
        const feedbackButtons = document.querySelectorAll('.feedback-btn');
        const responseDiv = document.getElementById('satisfaction-response');

        feedbackButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const feedback = this.getAttribute('data-feedback');

                const buttonsContainer = document.getElementById('satisfaction-buttons');
                if (buttonsContainer) buttonsContainer.style.display = 'none';
                if (responseDiv) responseDiv.style.display = 'block';
            });
        });

        // Service card hover effects
        const serviceCards = document.querySelectorAll('.service-card');
        serviceCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Modal Popup -->
<?php if (!empty($maintenance_notice) && is_array($maintenance_notice)): ?>
    <?php
    // Validate maintenance notice data
    $title = isset($maintenance_notice['title']) ? esc($maintenance_notice['title']) : '';
    $heading = isset($maintenance_notice['heading']) ? esc($maintenance_notice['heading']) : '';
    $message = isset($maintenance_notice['message']) ? esc($maintenance_notice['message']) : '';
    $contact_name = isset($maintenance_notice['contact_name']) ? esc($maintenance_notice['contact_name']) : '';
    $contact_position = isset($maintenance_notice['contact_position']) ? esc($maintenance_notice['contact_position']) : '';

    // Only show modal if we have at least title and message
    if (!empty($title) && !empty($message)):
    ?>
        <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h2 class="h5 modal-title" id="welcomeModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= $title ?>
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="display-4 text-warning me-3">
                                <i class="fas fa-tools"></i>
                            </div>
                            <?php if (!empty($heading)): ?>
                                <h3 class="h5 mb-0 text-danger"><?= $heading ?></h3>
                            <?php endif; ?>
                        </div>
                        <div class="alert alert-light border">
                            <p class="mb-3">
                                <?= nl2br(esc($message)) ?>
                            </p>
                            <?php if (!empty($contact_name)): ?>
                                <p class="mb-0">Untuk informasi lebih lanjut, silakan menghubungi:</p>
                                <div class="mt-3 p-3 bg-white rounded border">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                                        <div>
                                            <h6 class="mb-1"><?= $contact_name ?></h6>
                                            <?php if (!empty($contact_position)): ?>
                                                <p class="mb-0 text-muted"><?= $contact_position ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dontShowAgain">
                            <label class="form-check-label" for="dontShowAgain">
                                Jangan tampilkan lagi untuk hari ini
                            </label>
                        </div>
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">
                            <i class="fas fa-check me-1"></i>Saya Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<main id="main-content">

    <!-- Hero Section with Search -->
    <?= view('frontend/components/hero-search', [
        'popular_tags' => $popular_tags ?? [],
        'all_jenis' => $all_jenis ?? [],
        'all_status' => $all_status ?? []
    ]) ?>

    <!-- Main Categories Section -->
    <section class="jdih-section jdih-main-categories" aria-labelledby="kategori-heading">
        <div class="container">
            <div class="section-heading text-center mb-5" data-aos="fade-up">
                <h2 id="kategori-heading">Jelajahi Dokumen Hukum</h2>
                <p>Temukan informasi hukum berdasarkan kategori utama JDIH sesuai Permenkumham No. 8 Tahun 2019</p>
            </div>
            <div class="row g-4 justify-content-center" role="list">
                <?php if (isset($kategori_list) && is_array($kategori_list) && !empty($kategori_list)): ?>
                    <?php
                    $colors = ['text-primary', 'text-success', 'text-info', 'text-warning', 'text-danger'];
                    foreach ($kategori_list as $index => $kategori):
                        // Validate kategori data
                        if (!isset($kategori['slug']) || !isset($kategori['nama']) || !isset($kategori['icon'])) {
                            continue; // Skip invalid data
                        }

                        $color = $colors[$index % count($colors)];
                        $count = isset($kategori_counts[$kategori['slug']]) ? (int)$kategori_counts[$kategori['slug']] : 0;
                    ?>
                        <div class="col-lg col-md-4 col-sm-6" data-aos="zoom-in" data-aos-delay="<?= ($index + 1) * 100 ?>" role="listitem">
                            <a href="<?= base_url('dokumen/kategori/' . esc($kategori['slug'], 'url')) ?>"
                                class="category-card main-category-card text-decoration-none d-block p-4 rounded shadow-sm text-center h-100"
                                title="<?= esc($kategori['nama']) ?> - <?= number_format($count) ?> dokumen">
                                <div class="category-icon mb-3">
                                    <i class="fas <?= esc($kategori['icon']) ?> fa-3x <?= $color ?>" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 mb-2"><?= esc(mb_convert_case($kategori['nama'], MB_CASE_TITLE, "UTF-8")) ?></h3>
                                <span class="category-count text-muted"><?= number_format($count) ?> Dokumen</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            Kategori tidak tersedia saat ini.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Latest Documents -->
    <?= view('frontend/components/document-cards', [
        'documents' => $latest_peraturan ?? [],
        'title' => 'Dokumen Terbaru',
        'subtitle' => 'Dokumen hukum yang baru ditambahkan'
    ]) ?>

    <!-- Latest News -->
    <?= view('frontend/components/news-cards', [
        'news' => $latest_berita ?? [],
        'title' => 'Berita & Informasi Terbaru',
        'subtitle' => 'Update berita dan informasi hukum terkini dari JDIH'
    ]) ?>

    <!-- Agenda -->
    <?= view('frontend/components/agenda-cards', [
        'agenda' => $agenda ?? [],
        'title' => 'Agenda Kegiatan',
        'subtitle' => 'Informasi agenda kegiatan terkini dan yang akan datang.'
    ]) ?>

    <!-- Layanan Unggulan -->
    <?= view('frontend/components/service-cards', [
        'title' => 'Layanan Unggulan JDIH',
        'subtitle' => 'Layanan-layanan utama yang kami sediakan untuk kemudahan akses informasi hukum'
    ]) ?>

    <!-- Mitra JDIH -->
    <?= view('frontend/components/mitra-section', [
        'title' => 'Mitra JDIH',
        'subtitle' => 'Terintegrasi dengan jaringan informasi hukum nasional dan sistem sertifikat elektronik BSrE'
    ]) ?>

    <!-- Media Sosial -->
    <?= view('frontend/components/social-media', [
        'title' => 'Terhubung Bersama Kami',
        'subtitle' => 'Lihat aktivitas terbaru kami di Instagram dan YouTube.'
    ]) ?>

    <!-- Call to Action -->
    <?= view('frontend/components/cta-section', [
        'title' => 'Gabung Bersama JDIH!',
        'subtitle' => 'Dukung keterbukaan informasi hukum dengan berkontribusi atau memberikan masukan kepada JDIH kami. Bersama kita wujudkan tata kelola pemerintahan yang transparan dan akuntabel.',
        'button_text' => 'Hubungi Kami'
    ]) ?>

    <!-- Satisfaction Survey -->
    <?= view('frontend/components/satisfaction-widget', [
        'title' => 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum',
        'subtitle' => 'Bantu kami meningkatkan layanan informasi hukum dengan memberikan penilaian Anda.'
    ]) ?>

</main>

<?= $this->endSection() ?>