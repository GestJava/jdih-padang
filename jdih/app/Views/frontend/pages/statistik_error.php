<?php $this->extend('frontend/layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <h1 class="text-danger mb-4"><i class="fas fa-exclamation-triangle"></i></h1>
                    <h2 class="mb-4">Statistik Tidak Tersedia</h2>
                    <p class="lead mb-4"><?= $error_message ?? 'Maaf, terjadi kesalahan saat memuat data statistik. Silakan coba lagi nanti.' ?></p>
                    <div class="mt-4">
                        <a href="<?= base_url() ?>" class="btn btn-primary">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>