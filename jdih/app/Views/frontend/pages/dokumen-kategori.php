<?= $this->extend('layouts/frontend') ?>

<?= $this->section('styles') ?>
<style>
    .category-jenis-card {
        transition: transform .2s ease-out, box-shadow .2s ease-out;
        /* height: 100%; Dihapus karena card body sekarang flex dan akan mengisi tinggi */
    }

    .category-jenis-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .category-jenis-card .card-body {
        /* display: flex; flex-direction: column; sudah ada di inline class */
        min-height: 200px;
        /* Atur tinggi minimum kartu jika perlu */
    }

    .category-jenis-card .card-title {
        font-size: 1.15rem;
        /* Sedikit diperbesar */
        font-weight: 600;
    }

    .category-jenis-card .card-text.small {
        font-size: 0.85rem;
        /* Sedikit disesuaikan */
        /* flex-grow: 1; tidak diperlukan lagi karena struktur flex */
    }

    /* Ikon styling sudah di inline */
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Definisikan array warna seperti di home.php
$bg_colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary', 'bg-dark'];
$text_colors = ['text-white', 'text-white', 'text-dark', 'text-dark', 'text-white', 'text-white', 'text-white']; // Sesuaikan jika perlu kontras
$btn_colors = ['btn-outline-light', 'btn-outline-light', 'btn-outline-dark', 'btn-outline-dark', 'btn-outline-light', 'btn-outline-light', 'btn-outline-light'];
?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-folder-open icon-sm me-1"></i> Kategori Dokumen</span>
                </div>
                <h1 class="hero-title"><?= esc($kategori_nama ?? 'Kategori Dokumen') ?></h1>
                <p class="hero-subtitle">Jelajahi berbagai jenis dokumen dalam kategori <?= esc(strtolower($kategori_nama ?? 'ini')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-5 mb-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($kategori_nama ?? 'Kategori') ?></li>
            </ol>
        </nav>

        <?php if (isset($sub_kategori_list) && !empty($sub_kategori_list)) : ?>
            <div class="row g-4">
                <?php foreach ($sub_kategori_list as $index => $sub_kategori) : ?>
                    <?php
                    $bgColor = $bg_colors[$index % count($bg_colors)];
                    $textColor = $text_colors[$index % count($text_colors)];
                    $btnColor = $btn_colors[$index % count($btn_colors)];
                    ?>
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 category-jenis-card overflow-hidden <?= esc($bgColor) ?> <?= esc($textColor) ?>">
                            <a href="<?= base_url('dokumen/kategori/' . esc($sub_kategori['kategori_slug'], 'url')) ?>" class="text-decoration-none <?= esc($textColor) ?>">
                                <div class="card-body text-center p-4 d-flex flex-column align-items-center justify-content-center">
                                    <div class="mb-3">
                                        <i class="fas fa-folder-open fa-3x"></i>
                                    </div>
                                    <h5 class="card-title mb-2"><?= esc($sub_kategori['kategori_nama']) ?></h5>
                                    <span class="btn btn-sm <?= esc($btnColor) ?> mt-auto">Lihat Detail</span>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($jenis_list) && !empty($jenis_list)) : ?>
            <div class="row g-4">
                <?php foreach ($jenis_list as $index => $jenis) : ?>
                    <?php
                    $bgColor = $bg_colors[$index % count($bg_colors)];
                    $textColor = $text_colors[$index % count($text_colors)];
                    $btnColor = $btn_colors[$index % count($btn_colors)];
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 category-jenis-card overflow-hidden <?= esc($bgColor) ?> <?= esc($textColor) ?>">
                            <a href="<?= base_url('dokumen/jenis/' . esc($jenis['slug_jenis'], 'url')) ?>" class="text-decoration-none <?= esc($textColor) ?>">
                                <div class="card-body text-center p-4 d-flex flex-column align-items-center justify-content-center">
                                    <?php if (!empty($jenis['icon'])) : ?>
                                        <div class="mb-3">
                                            <i class="fas <?= esc($jenis['icon']) ?> fa-3x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="card-title mb-2"><?= esc($jenis['nama_jenis']) ?></h5>
                                    <?php if (!empty($jenis['deskripsi'])) : ?>
                                        <p class="card-text small opacity-75 mb-3 <?= esc($textColor) ?>"><?= esc($jenis['deskripsi']) ?></p>
                                    <?php endif; ?>
                                    <span class="btn btn-sm <?= esc($btnColor) ?> mt-auto">Lihat Dokumen</span>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="alert alert-info text-center" role="alert">
                Belum ada konten yang tersedia untuk kategori ini.
            </div>
        <?php endif; ?>

    </div>
</section>

<?= $this->endSection() ?>