<?php

/**
 * Pagination Component
 * Usage: <?= $this->include('frontend/components/pagination', ['pager' => $pager, 'baseUrl' => 'agenda']) ?>
 */

// Handle different pager formats
$currentPage = 1;
$totalPages = 1;
$hasPages = false;

if (is_object($pager) && method_exists($pager, 'getCurrentPage')) {
    // CodeIgniter 4 Pager object
    $currentPage = $pager->getCurrentPage();
    $totalPages = $pager->getPageCount();
    $hasPages = $totalPages > 1;
} elseif (is_array($pager)) {
    // Custom pager array
    $currentPage = $pager['page'] ?? 1;
    $totalPages = $pager['totalPages'] ?? 1;
    $hasPages = $totalPages > 1;
}

// Don't render if no pagination needed
if (!$hasPages) {
    return;
}

// Preserve current URL parameters - Validasi dan sanitasi $_GET
$urlParams = [];
if (isset($_GET) && is_array($_GET)) {
    foreach ($_GET as $key => $value) {
        // Validasi key dan value untuk mencegah injection
        if (is_string($key) && preg_match('/^[a-zA-Z0-9_\-]+$/', $key)) {
            // Sanitasi value - gunakan htmlspecialchars untuk keamanan
            if (is_string($value)) {
                $urlParams[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } elseif (is_numeric($value)) {
                $urlParams[$key] = $value;
            } else {
                $urlParams[$key] = '';
            }
        }
    }
}
$baseUrl = $baseUrl ?? '';
?>

<nav aria-label="<?= esc($ariaLabel ?? 'Page navigation') ?>" class="<?= esc($containerClass ?? 'mt-4') ?>">
    <div class="d-flex justify-content-center">
        <ul class="pagination <?= esc($paginationClass ?? 'justify-content-center') ?>">

            <?php if ($currentPage > 1): ?>
                <!-- First Page -->
                <?php if ($showFirst ?? true): ?>
                    <?php
                    $firstParams = $urlParams;
                    unset($firstParams['page']);
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url($baseUrl . '?' . http_build_query($firstParams)) ?>" aria-label="First">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Previous Page -->
                <?php
                $prevParams = $urlParams;
                $prevParams['page'] = $currentPage - 1;
                if ($prevParams['page'] == 1) {
                    unset($prevParams['page']);
                }
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= base_url($baseUrl . '?' . http_build_query($prevParams)) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php
            // Calculate page range
            $range = $range ?? 5; // Show 5 pages by default
            $startPage = max(1, $currentPage - floor($range / 2));
            $endPage = min($totalPages, $startPage + $range - 1);

            // Adjust start page if we're near the end
            if ($endPage - $startPage < $range - 1) {
                $startPage = max(1, $endPage - $range + 1);
            }

            // Show ellipsis at start
            if ($startPage > 1): ?>
                <?php
                $firstParams = $urlParams;
                unset($firstParams['page']);
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= base_url($baseUrl . '?' . http_build_query($firstParams)) ?>">1</a>
                </li>
                <?php if ($startPage > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            // Page numbers
            for ($i = $startPage; $i <= $endPage; $i++):
                $pageParams = $urlParams;
                if ($i == 1) {
                    unset($pageParams['page']);
                } else {
                    $pageParams['page'] = $i;
                }
            ?>
                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= base_url($baseUrl . '?' . http_build_query($pageParams)) ?>">
                        <?= $i ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="visually-hidden">(current)</span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php
            // Show ellipsis at end
            if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <?php
                $lastParams = $urlParams;
                $lastParams['page'] = $totalPages;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= base_url($baseUrl . '?' . http_build_query($lastParams)) ?>"><?= $totalPages ?></a>
                </li>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
                <!-- Next Page -->
                <?php
                $nextParams = $urlParams;
                $nextParams['page'] = $currentPage + 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= base_url($baseUrl . '?' . http_build_query($nextParams)) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>

                <!-- Last Page -->
                <?php if ($showLast ?? true): ?>
                    <?php
                    $lastParams = $urlParams;
                    $lastParams['page'] = $totalPages;
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url($baseUrl . '?' . http_build_query($lastParams)) ?>" aria-label="Last">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </div>

    <?php if ($showInfo ?? true): ?>
        <div class="text-center mt-2">
            <small class="text-muted">
                <?= sprintf(
                    'Halaman %d dari %d (Total %d item)',
                    $currentPage,
                    $totalPages,
                    $totalItems ?? ($totalPages * 10) // Estimate if not provided
                ) ?>
            </small>
        </div>
    <?php endif; ?>
</nav>