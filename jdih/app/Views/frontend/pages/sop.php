<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-clipboard-list icon-sm me-1"></i> SOP</span>
                </div>
                <h1 class="hero-title">SOP Pengelolaan JDIH</h1>
                <p class="hero-subtitle">Standar Operasional Prosedur</p>
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
            <li class="breadcrumb-item active" aria-current="page">SOP Pengelolaan JDIH</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Bagan SOP Pengelolaan</h2>
                    <p>Berikut adalah bagan yang mengilustrasikan Standar Operasional Prosedur (SOP) untuk pengelolaan Jaringan Dokumentasi dan Informasi Hukum (JDIH).</p>
                    <div class="text-center">
                        <img src="<?= base_url('assets/img/sop.jpg') ?>" alt="SOP Pengelolaan JDIH" class="img-fluid rounded shadow-sm border w-100">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>