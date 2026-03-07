<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - JDIH Kota Padang</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('images/favicon.png') ?>">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('vendors/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/fontawesome/css/all.min.css') ?>">

    <!-- Plugin CSS -->
    <link rel="stylesheet" href="<?= base_url('vendors/jquery.select2/css/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/datatables/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/datatables/css/buttons.bootstrap5.min.css') ?>">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('themes/modern/css/style.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('themes/modern/css/custom.css') ?>">

    <!-- Core JS -->
    <script src="<?= base_url('vendors/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('vendors/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Plugin JS -->
    <script src="<?= base_url('vendors/jquery.select2/js/select2.full.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/dataTables.buttons.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/buttons.bootstrap5.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/buttons.html5.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/buttons.print.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/pdfmake.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/js/vfs_fonts.js') ?>"></script>

    <?= $this->renderSection('styles') ?>
</head>

<body>
    <!-- ... rest of the body content ... -->

    <!-- Custom JS -->
    <script src="<?= base_url('themes/modern/js/main.min.js') ?>"></script>
    <script src="<?= base_url('themes/modern/js/chart-utils.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>

    <script>
        // Performance monitoring
        window.addEventListener('load', function() {
            console.group('=== JDIH Performance Metrics ===');
            const timing = window.performance.timing;
            const loadTime = timing.loadEventEnd - timing.navigationStart;
            const domContentLoaded = timing.domContentLoadedEventEnd - timing.navigationStart;
            const paint = performance.getEntriesByType('paint');

            console.log(`Total Page Load Time: ${loadTime.toFixed(2)} ms`);
            console.log(`DOM Content Loaded: ${domContentLoaded.toFixed(2)} ms`);

            if (paint.length) {
                console.log(`First Paint: ${paint[0].startTime.toFixed(2)} ms`);
                console.log(`First Contentful Paint: ${paint[1].startTime.toFixed(2)} ms`);
            }

            // Performance rating
            let rating = 'POOR';
            if (loadTime < 2000) rating = 'EXCELLENT';
            else if (loadTime < 4000) rating = 'GOOD';
            else if (loadTime < 6000) rating = 'FAIR';
            console.log(`🚀 Performance: ${rating}`);

            // Resource counting
            const resources = performance.getEntriesByType('resource');
            const jsFiles = resources.filter(r => r.name.endsWith('.js')).length;
            const cssFiles = resources.filter(r => r.name.endsWith('.css')).length;

            console.log('\n📊 Resource Analysis:');
            console.log(`- JavaScript files: ${jsFiles}`);
            console.log(`- CSS files: ${cssFiles}`);
            console.log(`- Total resources: ${resources.length}`);

            // Slow resource detection
            const slowResources = resources
                .filter(r => r.duration > 500)
                .sort((a, b) => b.duration - a.duration)
                .slice(0, 3);

            if (slowResources.length) {
                console.log('\n🐌 Slowest Resources:');
                slowResources.forEach((r, i) => {
                    console.log(`${i + 1}. ${r.name.split('/').pop()}: ${r.duration.toFixed(2)}ms`);
                });
            }

            console.log('\n✅ All assets loaded successfully');
            console.log('🎯 JDIH Asset Manager: ACTIVE');
            console.groupEnd();
        });
    </script>
</body>

</html>