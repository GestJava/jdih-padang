<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum. Portal resmi dokumen hukum dan peraturan daerah Kota Padang.">
    <meta name="author" content="JDIH Kota Padang">
    <meta name="theme-color" content="#0d6efd">
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
    <title><?= $title ?? 'JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum' ?></title>

    <!-- CSS Libraries - Optimized loading dengan CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Google Fonts - Preload untuk performa -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS - Optimized loading dengan cache -->
    <?php
    // Hindari warning ketika file belum ada (misal pada environment baru)
    $cssPath = FCPATH . 'assets/css/jdih-custom.css';
    $navbarPath = FCPATH . 'assets/css/navbar-fix.css';
    $newsPath = FCPATH . 'assets/css/news-styles.css';

    $cssVersion = cache('css_version') ?: (is_file($cssPath) ? filemtime($cssPath) : time());
    $navbarVersion = cache('navbar_version') ?: (is_file($navbarPath) ? filemtime($navbarPath) : time());
    $newsVersion = cache('news_version') ?: (is_file($newsPath) ? filemtime($newsPath) : time());
    ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/jdih-custom.css') ?>?v=<?= $cssVersion ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/navbar-fix.css') ?>?v=<?= $navbarVersion ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/news-styles.css') ?>?v=<?= $newsVersion ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/accessibility.css') ?>?v=<?= time() ?>">

    <!-- Load AOS CSS only when needed (for pages with animations) -->
    <?php if (isset($useAOS) && $useAOS): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" integrity="sha384-1mUroY35o4tVwWcMmqnc0XvXACR4G3X4n5q10lK/QfcZxlBttJVyA6/20v9CRK/iq" crossorigin="anonymous">
    <?php endif; ?>

    <!-- Tambahan style dari section lain -->
    <?= $this->renderSection('styles') ?>
</head>

<body>
    <!-- Loading indicator -->
    <div id="loading" class="jdih-loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navbar -->
    <?= $this->include('frontend/components/navbar') ?>

    <!-- Main Content -->
    <main id="main">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <?= $this->include('frontend/components/footer') ?>

    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up icon-sm"></i>
    </a>

    <!-- Base Scripts - Optimized loading -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- Load AOS only when needed -->
    <?php if (isset($useAOS) && $useAOS): ?>
        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                AOS.init({
                    duration: 800,
                    once: true // Animasi hanya dijalankan sekali
                });
            });
        </script>
    <?php endif; ?>

    <!-- Load Chart.js only when needed -->
    <?php if (isset($useChart) && $useChart): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js" defer></script>
    <?php endif; ?>

    <!-- Core scripts with optimized loading -->
    <?php $mainJsPath = FCPATH . 'assets/js/main.js'; ?>
    <script src="<?= base_url('assets/js/main.js') ?>?v=<?= is_file($mainJsPath) ? filemtime($mainJsPath) : time() ?>" defer></script>
    <script src="<?= base_url('assets/js/accessibility.js') ?>?v=<?= time() ?>" defer></script>

    <!-- Tambahan script dari section lain -->
    <?= $this->renderSection('scripts') ?>

    <!-- Inisialisasi loading handler -->
    <script>
        // Persiapan untuk menangani loading dengan lebih baik
        document.addEventListener('readystatechange', function(event) {
            // Ketika dokumen HTML dimuat (belum semua resource)
            if (event.target.readyState === 'interactive') {
                document.getElementById('loading').classList.add('loaded');
            }
            // Ketika halaman sepenuhnya dimuat
            if (event.target.readyState === 'complete') {
                setTimeout(function() {
                    document.getElementById('loading').style.display = 'none';
                }, 500);
            }
        });
    </script>
</body>

</html>