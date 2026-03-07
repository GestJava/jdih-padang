<?php

/**
 * Hero Section Component
 * Usage: <?= $this->include('frontend/components/hero', ['title' => $title, 'subtitle' => $subtitle, 'icon' => $icon]) ?>
 */
?>
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span>
                        <?php if (!empty($icon)): ?>
                            <i class="fas <?= esc($icon) ?> icon-sm me-1"></i>
                        <?php endif; ?>
                        <?= esc($badge ?? 'JDIH Kota Padang') ?>
                    </span>
                </div>
                <h1 class="hero-title"><?= esc($title ?? 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum') ?></h1>
                <p class="hero-subtitle"><?= esc($subtitle ?? 'Jaringan Dokumentasi dan Informasi Hukum') ?></p>

                <?php if (!empty($actions) && is_array($actions)): ?>
                    <div class="hero-actions mt-4">
                        <?php foreach ($actions as $action): ?>
                            <a href="<?= base_url($action['url']) ?>" class="btn <?= esc($action['class'] ?? 'btn-primary') ?> me-2">
                                <?php if (!empty($action['icon'])): ?>
                                    <i class="fas <?= esc($action['icon']) ?> me-1"></i>
                                <?php endif; ?>
                                <?= esc($action['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>