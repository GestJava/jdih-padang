	</div><!-- cotent-wrapper -->
	</div><!-- cotent -->
	</div><!-- site-content -->
	<footer class="shadow">
		<div class="footer-copyright">
			<div class="wrapper">
				<?php
				$footerRaw = $settingAplikasi['footer_app'] ?? '© {{YEAR}} Pemerintah Kota Padang';
				$footer = str_replace('{{YEAR}}', date('Y'), $footerRaw);
				echo html_entity_decode($footer);
				?>
			</div>
		</div>
	</footer>

	<!-- Page-specific scripts -->
	<?php if (isset($page_scripts)): ?>
		<?php $ver = (ENVIRONMENT === 'production') ? (defined('APP_VERSION') ? constant('APP_VERSION') : '1.0.0') : time(); ?>
		<?php foreach ($page_scripts as $script): ?>
			<script src="<?= $script ?>?v=<?= $ver ?>"></script>
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
	<?php if (ENVIRONMENT !== 'production'): ?>
		<!-- Performance monitoring (development only) -->
		<script>
			window.addEventListener('load', function() {
				if ('performance' in window) {
					const perfData = performance.getEntriesByType('navigation')[0];
					const domReadyTime = perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart;
					const totalTime = perfData.loadEventEnd - perfData.fetchStart;
					console.log('=== JDIH Performance Metrics ===');
					console.log('Total Page Load:', totalTime.toFixed(1) + ' ms', '| DOMReady:', domReadyTime.toFixed(1) + ' ms');
					const resources = performance.getEntriesByType('resource');
					console.log('Res:', resources.length);
				}
			});
			window.addEventListener('error', e => console.error('JS Error:', e.message, e.filename + ':' + e.lineno));
			window.addEventListener('unhandledrejection', e => console.error('Promise Rejection:', e.reason));
		</script>
	<?php endif; ?>
	</body>

	</html>