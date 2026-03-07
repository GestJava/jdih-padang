<?php

use App\Config\HarmonisasiStatus;
?>

<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<!-- Tombol aksi berdasarkan status -->
<div class="card-footer">
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?= base_url('harmonisasi') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

        <?php if ($ajuan['id_status_ajuan'] == HarmonisasiStatus::REVISI && $ajuan['id_user_pemohon'] == session()->get('id_user')): ?>
            <a href="<?= base_url('harmonisasi/showRevisiForm/' . $ajuan['id']) ?>" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Revisi Dokumen
            </a>
        <?php endif; ?>
    </div>
</div>