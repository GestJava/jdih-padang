                </div>
                </div>
                </div>
                </div>

                <!-- Asset Manager Script -->
                <script src="<?= base_url('jdih/assets/js/jdih-asset-manager.js') ?>?v=<?= time() ?>"></script>

                <!-- Page-specific scripts -->
                <?php if (isset($page_scripts)): ?>
                    <?php foreach ($page_scripts as $script): ?>
                        <script src="<?= $script ?>?v=<?= time() ?>"></script>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Inline scripts -->
                <?php if (isset($inline_scripts)): ?>
                    <?php foreach ($inline_scripts as $script): ?>
                        <script>
                            <?= $script ?>
                        </script>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Performance monitoring -->
                <script>
                    // Performance monitoring
                    window.addEventListener('load', function() {
                        if ('performance' in window) {
                            const perfData = performance.getEntriesByType('navigation')[0];
                            console.log('Page Load Time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
                            console.log('DOM Content Loaded:', perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart, 'ms');
                        }
                    });

                    // Error tracking
                    window.addEventListener('error', function(e) {
                        console.error('JavaScript Error:', e.error);
                        // You can send this to your error tracking service
                    });

                    // Unhandled promise rejection tracking
                    window.addEventListener('unhandledrejection', function(e) {
                        console.error('Unhandled Promise Rejection:', e.reason);
                        // You can send this to your error tracking service
                    });
                </script>
                </body>

                </html>