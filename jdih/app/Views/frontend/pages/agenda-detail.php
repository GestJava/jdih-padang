<?= $this->extend('layouts/frontend') ?>

<?= $this->section('styles') ?>
<!-- Custom CSS untuk halaman detail agenda telah dipindahkan ke jdih-custom.css -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const shareButtons = document.querySelectorAll('.share-btn');
        const pageUrl = window.location.href;
        const pageTitle = document.title;

        shareButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const platform = this.dataset.platform;
                let shareUrl = '';

                switch (platform) {
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(pageUrl)}`;
                        break;
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(pageUrl)}&text=${encodeURIComponent(pageTitle)}`;
                        break;
                    case 'whatsapp':
                        shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(pageTitle + ' ' + pageUrl)}`;
                        break;
                    case 'email':
                        shareUrl = `mailto:?subject=${encodeURIComponent(pageTitle)}&body=${encodeURIComponent('Silakan lihat agenda ini: ' + pageUrl)}`;
                        break;
                }

                if (shareUrl) {
                    // Untuk WhatsApp di desktop, buka di tab baru. Untuk platform lain, buka popup.
                    if (platform === 'whatsapp' || platform === 'email') {
                        window.open(shareUrl, '_blank');
                    } else {
                        window.open(shareUrl, 'share-dialog', 'width=626,height=436');
                    }
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-calendar-check icon-sm me-1"></i> Detail Agenda</span>
                </div>
                <h1 class="hero-title"><?= esc(mb_convert_case($agenda['judul_agenda'] ?? 'Detail Agenda', MB_CASE_TITLE, "UTF-8")) ?></h1>
                <p class="hero-subtitle">Informasi lengkap tentang kegiatan ini</p>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <?= $this->include('frontend/components/breadcrumb', [
        'items' => [
            ['label' => 'Agenda', 'url' => 'agenda'],
            ['label' => esc(mb_convert_case($agenda['judul_agenda'] ?? 'Detail Agenda', MB_CASE_TITLE, "UTF-8")), 'url' => '']
        ]
    ]) ?>

    <div class="row mb-4">
        <div class="col-12 text-end">
            <a href="<?= base_url('agenda') ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Agenda
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="agenda-detail-card p-4 mb-4">
                    <div class="mb-4">

                        <span class="tag bg-<?= ($agenda['status_agenda'] ?? 'Akan Datang') === 'Selesai' ? 'secondary' : (($agenda['status_agenda'] ?? 'Akan Datang') === 'Berlangsung' ? 'success' : 'primary') ?>">
                            <?= esc($agenda['status_agenda'] ?? 'Akan Datang') ?>
                        </span>
                    </div>

                    <h3 class="mb-4"><?= esc(mb_convert_case($agenda['judul_agenda'] ?? 'Detail Agenda', MB_CASE_TITLE, "UTF-8")) ?></h3>

                    <div class="agenda-description mb-4">
                        <p><?= nl2br(esc($agenda['deskripsi_lengkap'] ?? 'Deskripsi tidak tersedia.')) ?></p>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="agenda-meta mb-4">
                    <h5 class="mb-4">Informasi Acara</h5>

                    <div class="agenda-meta-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-day text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Tanggal</h6>
                                <p class="mb-0"><?= esc($agenda['tanggal_display'] ?? 'Informasi tanggal tidak tersedia') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="agenda-meta-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Waktu</h6>
                                <p class="mb-0"><?= esc($agenda['waktu_display'] ?? 'Informasi waktu tidak tersedia') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="agenda-meta-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Lokasi</h6>
                                <p class="mb-0"><?= esc(mb_convert_case($agenda['lokasi'] ?? 'Informasi lokasi tidak tersedia', MB_CASE_TITLE, "UTF-8")) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="agenda-meta-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Peserta</h6>
                                <p class="mb-0"><?= esc(mb_convert_case($agenda['target_peserta'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="agenda-meta-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-building text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Penyelenggara</h6>
                                <p class="mb-0"><?= esc(mb_convert_case($agenda['penyelenggara'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="agenda-meta-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">Kontak</h6>
                                <p class="mb-0"><?= esc(mb_convert_case($agenda['kontak_person'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Bagikan</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-around">
                            <a href="#" class="btn btn-outline-primary share-btn" data-platform="facebook" title="Bagikan ke Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info share-btn" data-platform="twitter" title="Bagikan ke Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success share-btn" data-platform="whatsapp" title="Bagikan ke WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="btn btn-outline-secondary share-btn" data-platform="email" title="Bagikan via Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->endSection() ?>