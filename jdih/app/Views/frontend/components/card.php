<?php

/**
 * Card Component
 * Usage: <?= $this->include('frontend/components/card', ['title' => $title, 'content' => $content, 'footer' => $footer]) ?>
 */
?>
<div class="card <?= esc($cardClass ?? 'shadow-sm border-0 h-100') ?>">
    <?php if (!empty($header)): ?>
        <div class="card-header <?= esc($headerClass ?? 'bg-primary text-white') ?>">
            <?php if (is_string($header)): ?>
                <h5 class="card-title mb-0"><?= esc($header) ?></h5>
            <?php else: ?>
                <?php 
                // $header bisa berupa HTML atau plain text
                if (isset($allowHeaderHtml) && $allowHeaderHtml === true) {
                    echo $header; // HTML content (harus sudah di-sanitize di controller)
                } else {
                    echo esc($header); // Plain text - escape untuk keamanan
                }
                ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($image)): ?>
        <img src="<?= esc($image['src']) ?>"
            class="card-img-top <?= esc($image['class'] ?? '') ?>"
            alt="<?= esc($image['alt'] ?? '') ?>"
            <?php if (!empty($image['style'])): ?>style="<?= esc($image['style']) ?>" <?php endif; ?>
            loading="lazy">
    <?php endif; ?>

    <div class="card-body <?= esc($bodyClass ?? 'd-flex flex-column') ?>">
        <?php if (!empty($title)): ?>
            <h5 class="card-title <?= esc($titleClass ?? 'mb-2') ?>">
                <?php if (!empty($titleLink)): ?>
                    <a href="<?= base_url($titleLink) ?>" class="text-decoration-none link-dark">
                        <?= esc($title) ?>
                    </a>
                <?php else: ?>
                    <?= esc($title) ?>
                <?php endif; ?>
            </h5>
        <?php endif; ?>

        <?php if (!empty($subtitle)): ?>
            <p class="card-subtitle <?= esc($subtitleClass ?? 'text-muted mb-3') ?>">
                <?= esc($subtitle) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($content)): ?>
            <div class="card-text <?= esc($contentClass ?? 'flex-grow-1') ?>">
                <?php 
                // $content bisa berupa HTML (dari editor) atau plain text
                // Jika $content adalah HTML yang sudah di-sanitize, output langsung
                // Jika $content adalah plain text, gunakan esc($content)
                // Default: escape untuk keamanan, set $allowHtml = true jika memang HTML
                if (isset($allowHtml) && $allowHtml === true) {
                    echo $content; // HTML content (harus sudah di-sanitize di controller)
                } else {
                    echo esc($content); // Plain text - escape untuk keamanan
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($actions) && is_array($actions)): ?>
            <div class="card-actions mt-auto">
                <?php foreach ($actions as $action): ?>
                    <a href="<?= base_url($action['url']) ?>"
                        class="btn <?= esc($action['class'] ?? 'btn-primary btn-sm') ?> <?= esc($action['spacing'] ?? 'me-2') ?>">
                        <?php if (!empty($action['icon'])): ?>
                            <i class="fas <?= esc($action['icon']) ?> me-1"></i>
                        <?php endif; ?>
                        <?= esc($action['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($footer)): ?>
        <div class="card-footer <?= esc($footerClass ?? 'bg-white border-top-0') ?>">
            <?php 
            // $footer bisa berupa HTML atau plain text
            // Jika $footer adalah HTML yang sudah di-sanitize, output langsung
            // Jika $footer adalah plain text, gunakan esc($footer)
            if (isset($allowFooterHtml) && $allowFooterHtml === true) {
                echo $footer; // HTML content (harus sudah di-sanitize di controller)
            } else {
                echo esc($footer); // Plain text - escape untuk keamanan
            }
            ?>
        </div>
    <?php endif; ?>
</div>