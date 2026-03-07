<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-sitemap icon-sm me-1"></i> Organisasi</span>
                </div>
                <h1 class="hero-title">Struktur Organisasi</h1>
                <p class="hero-subtitle">JDIH Pemerintah Kota Padang</p>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('tentang') ?>">Tentang Kami</a></li>
            <li class="breadcrumb-item active" aria-current="page">Struktur Organisasi</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Bagan Struktur Organisasi</h2>
                    <p>Berikut adalah bagan yang mengilustrasikan struktur organisasi Jaringan Dokumentasi dan Informasi Hukum (JDIH) di lingkungan Pemerintah Kota Padang.</p>
                    <div class="text-center">
                        <img src="<?= base_url('assets/img/struktur-organisasi.png?v=' . date('YmdHis')) ?>" alt="Struktur Organisasi JDIH Kota Padang" class="img-fluid rounded shadow-sm border w-100">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Tim Pengelola JDIH</h2>
                    <p>Berikut adalah daftar tim pengelola Jaringan Dokumentasi dan Informasi Hukum (JDIH) Pemerintah Kota Padang.</p>
                    <div class="embed-responsive embed-responsive-1by1">
                        <iframe class="embed-responsive-item" src="<?= base_url('assets/uploads/files/tim-pengelola.pdf') ?>" allowfullscreen style="width: 100%; height: 800px;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>