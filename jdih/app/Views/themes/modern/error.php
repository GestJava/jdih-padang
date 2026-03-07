<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= $title ?? 'Error' ?> - JDIH Kota Padang</title>
	<link href="<?= base_url('vendors/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
	<style>
		body {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			min-height: 100vh;
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		}

		.error-container {
			max-width: 500px;
			margin: 100px auto;
		}

		.error-card {
			background: white;
			border-radius: 15px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
			padding: 40px;
			text-align: center;
		}

		.error-icon {
			font-size: 64px;
			color: #dc3545;
			margin-bottom: 20px;
		}

		.error-title {
			color: #333;
			font-weight: 600;
			margin-bottom: 15px;
		}

		.error-message {
			color: #666;
			margin-bottom: 30px;
		}

		.btn-back {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border: none;
			border-radius: 8px;
			padding: 12px 24px;
			font-weight: 600;
			color: white;
			text-decoration: none;
			transition: all 0.3s ease;
		}

		.btn-back:hover {
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
			color: white;
		}

		.alert {
			border-radius: 8px;
			border: none;
			margin-bottom: 20px;
		}
	</style>
</head>

<body>
	<div class="container">
		<div class="error-container">
			<div class="error-card">
				<div class="error-icon">⚠️</div>
				<h2 class="error-title"><?= $title ?? 'Error' ?></h2>

				<?php if (isset($msg) && is_array($msg)): ?>
					<div class="alert alert-<?= $msg['status'] == 'error' ? 'danger' : 'info' ?>">
						<?= $msg['message'] ?>
					</div>
				<?php elseif (isset($msg) && is_string($msg)): ?>
					<div class="alert alert-danger">
						<?= $msg ?>
					</div>
				<?php else: ?>
					<p class="error-message">Terjadi kesalahan yang tidak diketahui.</p>
				<?php endif; ?>

				<a href="<?= base_url() ?>" class="btn btn-back">
					<i class="fas fa-home"></i> Kembali ke Beranda
				</a>

				<div class="text-center mt-4">
					<small class="text-muted">© 2025 JDIH Kota Padang</small>
				</div>
			</div>
		</div>
	</div>

	<script src="<?= base_url('vendors/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>

</html>