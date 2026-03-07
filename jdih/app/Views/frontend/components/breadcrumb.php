<?php

/**
 * Breadcrumbs Component
 * Usage: <?= view('frontend/components/breadcrumbs', ['breadcrumbs' => $breadcrumbs]) ?>
 */

$breadcrumbs = $breadcrumbs ?? [];
?>

<?php if (!empty($breadcrumbs)): ?>
    <nav aria-label="breadcrumb" class="jdih-breadcrumbs">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </li>

                <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                    <?php if ($index === count($breadcrumbs) - 1): ?>
                        <!-- Current page -->
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= esc($breadcrumb['title']) ?>
                        </li>
                    <?php else: ?>
                        <!-- Navigation link -->
                        <li class="breadcrumb-item">
                            <a href="<?= base_url($breadcrumb['url']) ?>" class="text-decoration-none">
                                <?= esc($breadcrumb['title']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </div>
    </nav>

    <!-- Structured Data for Breadcrumbs -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [{
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Beranda",
                    "item": <?= json_encode(base_url()) ?>
                }
                <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>, {
                        "@type": "ListItem",
                        "position": <?= $index + 2 ?>,
                        "name": <?= json_encode($breadcrumb['title']) ?>,
                        "item": <?= json_encode(base_url($breadcrumb['url'])) ?>
                    }
                <?php endforeach; ?>
            ]
        }
    </script>
<?php endif; ?>

<style>
    .jdih-breadcrumbs {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 0.75rem 0;
    }

    .jdih-breadcrumbs .breadcrumb {
        background: transparent;
        margin: 0;
        padding: 0;
    }

    .jdih-breadcrumbs .breadcrumb-item+.breadcrumb-item::before {
        content: "›";
        color: #6c757d;
        font-weight: bold;
    }

    .jdih-breadcrumbs .breadcrumb-item a {
        color: #0d6efd;
        transition: color 0.2s ease;
    }

    .jdih-breadcrumbs .breadcrumb-item a:hover {
        color: #0a58ca;
    }

    .jdih-breadcrumbs .breadcrumb-item.active {
        color: #6c757d;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .jdih-breadcrumbs {
            padding: 0.5rem 0;
        }

        .jdih-breadcrumbs .breadcrumb-item {
            font-size: 0.875rem;
        }
    }
</style>