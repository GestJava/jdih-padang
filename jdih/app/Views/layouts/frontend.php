<?php
function fileWithVersion($path)
{
    // Normalize path - remove leading/trailing slashes
    $path = ltrim($path, '/\\');
    
    // Check if file exists in FCPATH (root)
    $fullPath = FCPATH . $path;
    
    // If file doesn't exist in root, try alternative paths
    if (!file_exists($fullPath)) {
        // Try without FCPATH prefix (in case FCPATH is already included)
        $altPath = $path;
        if (file_exists($altPath)) {
            $fullPath = $altPath;
        }
    }
    
    $version = file_exists($fullPath) ? filemtime($fullPath) : time();
    
    // Generate URL - ensure base_url() is called correctly
    $baseUrl = rtrim(base_url(), '/');
    $url = $baseUrl . '/' . $path;
    
    // Ensure URL doesn't have double slashes (except after protocol)
    $url = preg_replace('#([^:])//+#', '$1/', $url);
    
    return $url . '?v=' . $version;
}

// Detect environment - Validasi HTTP_HOST untuk keamanan
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
// Validasi format hostname (hanya alphanumeric, dot, dash, colon)
$is_valid_host = preg_match('/^[a-zA-Z0-9.\-:]+$/', $httpHost);
$is_localhost = $is_valid_host && (
    strpos($httpHost, 'localhost') !== false ||
    strpos($httpHost, '127.0.0.1') !== false ||
    strpos($httpHost, '::1') !== false
);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    <meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera=()">
    
    <?php
    // KEAMANAN: Set no-cache meta tags jika user sudah login
    // Ini mencegah browser cache halaman yang mengandung data session
    $session = service('session');
    $user = $session->get('user');
    if ($user) {
        echo '    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0, private">' . "\n";
        echo '    <meta http-equiv="Pragma" content="no-cache">' . "\n";
        echo '    <meta http-equiv="Expires" content="0">' . "\n";
    }
    ?>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= esc($description ?? 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum. Akses resmi dokumen hukum, peraturan daerah, dan informasi hukum Kota Padang dalam satu platform terintegrasi.', 'attr') ?>">
    <meta name="keywords" content="<?= esc($keywords ?? 'JDIH Kota Padang, peraturan daerah, perda, perwako, kepwal, dokumen hukum, informasi hukum, pemerintah kota padang, sumatera barat', 'attr') ?>">
    <meta name="author" content="JDIH Kota Padang">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">

    <!-- Additional Security & SEO Meta Tags -->
    <meta name="classification" content="Government, Legal, Information">
    <meta name="category" content="Government Website">
    <meta name="business_type" content="Government Organization">
    <meta name="service_type" content="Legal Documentation">
    <meta name="target_audience" content="Legal Professionals, Government Officials, Public">
    <meta name="coverage" content="Kota Padang, Sumatera Barat, Indonesia">
    <meta name="distribution" content="Global">
    <meta name="rating" content="General">
    <meta name="revisit-after" content="1 days">
    <meta name="language" content="id">
    <meta name="geo.region" content="ID-SB">
    <meta name="geo.placename" content="Kota Padang">
    <meta name="geo.position" content="-0.9444;100.4172">
    <meta name="ICBM" content="-0.9444, 100.4172">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#0d6efd">
    <meta name="msapplication-TileColor" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="JDIH Kota Padang">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Open Graph & Twitter Cards -->
    <meta property="og:title" content="<?= esc($og_title ?? 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum', 'attr') ?>">
    <meta property="og:description" content="<?= esc($og_description ?? 'Portal resmi JDIH Kota Padang. Akses dokumen hukum, peraturan daerah, dan informasi hukum terkini dari Pemerintah Kota Padang.', 'attr') ?>">
    <meta property="og:image" content="<?= esc($og_image ?? base_url('assets/img/hero-image.webp'), 'url') ?>">
    <meta property="og:url" content="<?= base_url() ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="JDIH Kota Padang">
    <meta property="og:locale" content="id_ID">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($og_title ?? 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum', 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($og_description ?? 'Portal resmi JDIH Kota Padang. Akses dokumen hukum, peraturan daerah, dan informasi hukum terkini dari Pemerintah Kota Padang.', 'attr') ?>">
    <meta name="twitter:image" content="<?= esc($og_image ?? base_url('assets/img/hero-image.webp'), 'url') ?>">
    <meta name="twitter:site" content="@jdihpadang">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= base_url() ?>">

    <!-- Favicon konsisten hanya .ico -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">

    <!-- Resource Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//unpkg.com">

    <title><?= esc($title ?? 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum') ?></title>

    <!-- Government Website Structured Data -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "GovernmentOrganization",
            "name": "JDIH Kota Padang",
            "alternateName": "Jaringan Dokumentasi dan Informasi Hukum Kota Padang",
            "description": "Portal resmi JDIH Kota Padang untuk akses dokumen hukum, peraturan daerah, dan informasi hukum terkini dari Pemerintah Kota Padang.",
            "url": <?= json_encode(base_url()) ?>,
            "logo": <?= json_encode(base_url('assets/img/hero-image.webp')) ?>,
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Kota Padang",
                "addressRegion": "Sumatera Barat",
                "addressCountry": "ID"
            },
            "contactPoint": {
                "@type": "ContactPoint",
                "contactType": "customer service",
                "areaServed": "ID-SB"
            },
            "areaServed": {
                "@type": "City",
                "name": "Kota Padang"
            },
            "parentOrganization": {
                "@type": "GovernmentOrganization",
                "name": "Pemerintah Kota Padang"
            }
        }
    </script>

    <!-- Critical CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= fileWithVersion('assets/css/jdih-custom.css') ?>">
    <link rel="stylesheet" href="<?= fileWithVersion('assets/css/navbar-fix.css') ?>">
    <link rel="stylesheet" href="<?= fileWithVersion('assets/css/news-styles.css') ?>">
    <link rel="stylesheet" href="<?= fileWithVersion('assets/css/accessibility.css') ?>">

    <!-- AOS Animation Library - Single Version -->
    <?php if (isset($useAOS) && $useAOS): ?>
        <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <?php endif; ?>

    <!-- Auto-load AOS for pages with animations -->
    <?php
    $current_page = $currentPage ?? '';
    $has_animations = in_array($current_page, ['home', 'home-optimized']) ||
        (isset($useAOS) && $useAOS) ||
        strpos($current_page, 'home') !== false;

    if ($has_animations): ?>
        <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <?php endif; ?>

    <?= $this->renderSection('styles') ?>
</head>

<body>
    <!-- Loading -->
    <div id="loading" class="jdih-loading">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <?= $this->include('frontend/components/navbar') ?>

    <main id="main">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('frontend/components/footer') ?>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up icon-sm"></i>
    </a>

    <!-- Google tag (gtag.js) -->
    <?php if (!$is_localhost): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-E51SBKG8WL"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-E51SBKG8WL');
        </script>
    <?php else: ?>
        <script>
            window.gtag = function() {
                // Development mode - Google Analytics disabled
            };
        </script>
    <?php endif; ?>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- AOS Animation Library - Single Version with Error Handling -->
    <?php if (isset($useAOS) && $useAOS): ?>
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 800,
                        easing: 'ease-in-out',
                        once: true,
                        offset: 100
                    });
                } else {
                    console.warn('AOS not loaded, animations disabled');
                }
            });
        </script>
    <?php endif; ?>

    <!-- Auto-load AOS for pages with animations -->
    <?php if ($has_animations): ?>
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 800,
                        easing: 'ease-in-out',
                        once: true,
                        offset: 100
                    });
                } else {
                    console.warn('AOS not loaded, animations disabled');
                }
            });
        </script>
    <?php endif; ?>

    <!-- Chart.js Library -->
    <?php if (isset($useChart) && $useChart): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" defer></script>
    <?php endif; ?>

    <!-- Core JavaScript -->
    <script src="<?= fileWithVersion('assets/js/main.js') ?>" defer></script>
    <script src="<?= fileWithVersion('assets/js/accessibility.js') ?>" defer></script>

    <!-- Conditional JavaScript -->
    <?php
    $newsJs = 'assets/js/news-interaction.js';
    if (file_exists(FCPATH . $newsJs) && isset($currentPage) && in_array($currentPage, ['home', 'berita'])):
    ?>
        <script src="<?= fileWithVersion($newsJs) ?>" defer></script>
    <?php endif; ?>

    <?= $this->renderSection('scripts') ?>

    <!-- Core Functionality -->
    <script>
        // Loading handler
        document.addEventListener('readystatechange', function(event) {
            if (event.target.readyState === 'interactive') {
                document.getElementById('loading').classList.add('loaded');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const loading = document.getElementById('loading');
                if (loading) {
                    loading.classList.add('loaded');
                    setTimeout(() => loading.style.display = 'none', 300);
                }
            }, 100);
        });

        // Survey functionality
        function submitSurvey(rating) {
            document.querySelectorAll('.survey-option').forEach(option => option.classList.remove('selected'));
            document.getElementById('survey' + rating.charAt(0).toUpperCase() + rating.slice(1)).classList.add('selected');
            document.getElementById('surveyFeedbackForm').style.display = 'block';
            localStorage.setItem('jdihSurveyRating', rating);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const submitFeedbackBtn = document.getElementById('submitFeedback');
            if (submitFeedbackBtn) {
                submitFeedbackBtn.addEventListener('click', function() {
                    const feedbackText = document.querySelector('.survey-feedback textarea').value;
                    const rating = localStorage.getItem('jdihSurveyRating');
                    document.getElementById('surveyFeedbackForm').style.display = 'none';
                    document.getElementById('surveyThankYou').style.display = 'block';
                    localStorage.setItem('jdihSurveySubmitted', 'true');
                });
            }

            if (localStorage.getItem('jdihSurveySubmitted')) {
                // Optional: document.querySelector('.jdih-survey').style.display = 'none';
            }
        });

    </script>
</body>

</html>