<?php helper('html') ?>
<div class="card-body dashboard">
	<?php
	if ($message['status'] == 'error') {
		show_message($message);
	}
	?>

	<!-- Welcome Section -->
	<div class="row mb-4">
		<div class="col-12">
			<div class="welcome-card text-white rounded-3 p-4" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;">
				<div class="row align-items-center">
					<div class="col-lg-8">
						<h2 class="mb-2">Selamat Datang di Dashboard JDIH</h2>
						<p class="mb-0">Jaringan Dokumentasi dan Informasi Hukum Kota Padang</p>
						<small class="opacity-75">Kelola dan pantau dokumen hukum dengan mudah</small>
					</div>
					<div class="col-lg-4 text-end">
						<i class="material-icons" style="font-size: 80px; opacity: 0.3;">gavel</i>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Filter Tahun -->
	<div class="row mb-4">
		<div class="col-12">
			<div class="card shadow-sm">
				<div class="card-body">
					<form method="GET" action="<?= base_url('dashboard') ?>" class="d-flex align-items-center gap-3">
						<label for="tahun" class="form-label mb-0 fw-bold">
							<i class="fas fa-calendar-alt me-2"></i>Filter Tahun:
						</label>
						<select name="tahun" id="tahun" class="form-select" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
							<?php
							$tahun_sekarang = date('Y');
							// Generate list tahun dari tahun sekarang sampai 5 tahun ke belakang
							$tahun_options = [];
							for ($i = 0; $i <= 10; $i++) {
								$tahun_option = $tahun_sekarang - $i;
								$tahun_options[] = $tahun_option;
							}
							
							// Tambahkan tahun dari database jika ada
							foreach ($list_tahun as $tahun_db) {
								if (!in_array($tahun_db, $tahun_options)) {
									$tahun_options[] = $tahun_db;
								}
							}
							
							// Sort descending
							rsort($tahun_options);
							
							foreach ($tahun_options as $tahun_option):
								$selected = ($tahun == $tahun_option) ? 'selected' : '';
								$label = $tahun_option == $tahun_sekarang ? $tahun_option . ' (Tahun Ini)' : $tahun_option;
							?>
								<option value="<?= $tahun_option ?>" <?= $selected ?>><?= $label ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-filter me-1"></i>Filter
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Statistics Cards -->
	<div class="row mb-4">
		<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			<div class="card text-white bg-primary shadow-lg border-0 h-100 position-relative">
				<div class="icon-circle">
					<i class="material-icons">description</i>
				</div>
				<div class="card-body card-stats">
					<div class="d-flex flex-column justify-content-center">
						<div class="flex-grow-1">
							<h3 class="card-title mb-1 text-white fw-bold"><?= !empty($total_dokumen['jml']) ? number_format($total_dokumen['jml']) : 0 ?></h3>
							<p class="card-text mb-0 text-white">Total Dokumen</p>
							<small class="text-white-50">Dokumen Hukum</small>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white bg-opacity-10 border-0">
					<div class="d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<?php
							if (!empty($total_dokumen['growth'])) {
								$class = $total_dokumen['growth'] > 0 ? 'fa-arrow-trend-up text-white' : 'fa-arrow-trend-down text-white';
								echo '<i class="fas ' . $class . ' me-1"></i>';
							} else {
								$total_dokumen['growth'] = 0;
							}
							?>
							<small class="text-white"><?= round($total_dokumen['growth']) ?>%</small>
						</div>
						<small class="text-white-50">Tahun <?= $tahun ?></small>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			<div class="card text-white bg-secondary shadow-lg border-0 h-100 position-relative">
				<div class="icon-circle">
					<i class="material-icons">new_releases</i>
				</div>
				<div class="card-body card-stats">
					<div class="d-flex flex-column justify-content-center">
						<div class="flex-grow-1">
							<h3 class="card-title mb-1 text-white fw-bold"><?= !empty($dokumen_bulan_ini ?? 0) ? number_format($dokumen_bulan_ini) : 0 ?></h3>
							<p class="card-text mb-0 text-white">Dokumen Terbaru</p>
							<small class="text-white-50">Bulan Ini</small>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white bg-opacity-10 border-0">
					<div class="d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<i class="fas fa-calendar-alt text-white me-1"></i>
							<small class="text-white">Bulan Ini</small>
						</div>
						<small class="text-white-50"><?= date('M Y') ?></small>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-4 col-md-6 col-sm-12 mb-3">
			<div class="card text-white bg-info shadow-lg border-0 h-100 position-relative">
				<div class="icon-circle">
					<i class="material-icons">people</i>
				</div>
				<div class="card-body card-stats">
					<div class="d-flex flex-column justify-content-center">
						<div class="flex-grow-1">
							<h3 class="card-title mb-1 text-white fw-bold"><?= !empty($total_user['jml']) ? number_format($total_user['jml']) : 0 ?></h3>
							<p class="card-text mb-0 text-white">Pengguna Aktif</p>
							<small class="text-white-50">User Terdaftar</small>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white bg-opacity-10 border-0">
					<div class="d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<?php
							$class = $total_user['growth'] > 0 ? 'fa-arrow-trend-up text-white' : 'fa-arrow-trend-down text-white';
							echo '<i class="fas ' . $class . ' me-1"></i>';
							?>
							<small class="text-white"><?= round($total_user['growth']) ?>%</small>
						</div>
						<small class="text-white-50">Tahun <?= $tahun ?></small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Statistik Layanan Section -->
	<h5 class="mb-3 fw-bold text-dark border-bottom pb-2 mt-4"><i class="material-icons align-middle fs-5 me-1">balance</i> Statistik Layanan</h5>
	<div class="row mb-4">
		<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
			<div class="card text-white bg-success shadow-lg border-0 h-100 position-relative">
				<div class="icon-circle">
					<i class="material-icons">balance</i>
				</div>
				<div class="card-body card-stats">
					<div class="d-flex flex-column justify-content-center">
						<div class="flex-grow-1">
							<h3 class="card-title mb-1 text-white fw-bold"><?= number_format($harm_total_ajuan ?? 0) ?></h3>
							<p class="card-text mb-0 text-white">Total Harmonisasi</p>
							<small class="text-white-50">Tahun <?= $tahun ?></small>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white bg-opacity-10 border-0">
					<div class="d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<i class="fas fa-chart-line text-white me-1"></i>
							<small class="text-white">Harmonisasi</small>
						</div>
						<small class="text-white-50">Tahun <?= $tahun ?></small>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
			<div class="card text-white bg-primary shadow-lg border-0 h-100 position-relative">
				<div class="icon-circle">
					<i class="material-icons">check_circle</i>
				</div>
				<div class="card-body card-stats">
					<div class="d-flex flex-column justify-content-center">
						<div class="flex-grow-1">
							<h3 class="card-title mb-1 text-white fw-bold"><?= number_format($harm_selesai ?? 0) ?></h3>
							<p class="card-text mb-0 text-white">Harmonisasi Selesai</p>
							<small class="text-white-50">Tahun <?= $tahun ?></small>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white bg-opacity-10 border-0">
					<div class="d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<i class="fas fa-check-circle text-white me-1"></i>
							<small class="text-white">Selesai</small>
						</div>
						<small class="text-white-50">Tahun <?= $tahun ?></small>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
			<div class="card text-white bg-warning shadow-lg border-0 h-100 position-relative">
				<div class="icon-circle">
					<i class="material-icons">gavel</i>
				</div>
				<div class="card-body card-stats">
					<div class="d-flex flex-column justify-content-center">
						<div class="flex-grow-1">
							<h3 class="card-title mb-1 text-white fw-bold"><?= number_format($leg_total_ajuan ?? 0) ?></h3>
							<p class="card-text mb-0 text-white">Total Legalisasi</p>
							<small class="text-white-50">Tahun <?= $tahun ?></small>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white bg-opacity-10 border-0">
					<div class="d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<i class="fas fa-chart-line text-white me-1"></i>
							<small class="text-white">Legalisasi</small>
						</div>
						<small class="text-white-50">Tahun <?= $tahun ?></small>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
			<div class="card text-white bg-info shadow-lg border-0 h-100 position-relative">
				<div class="icon-circle">
					<i class="material-icons">check_circle</i>
				</div>
				<div class="card-body card-stats">
					<div class="d-flex flex-column justify-content-center">
						<div class="flex-grow-1">
							<h3 class="card-title mb-1 text-white fw-bold"><?= number_format($leg_selesai ?? 0) ?></h3>
							<p class="card-text mb-0 text-white">Legalisasi Selesai</p>
							<small class="text-white-50">Tahun <?= $tahun ?></small>
						</div>
					</div>
				</div>
				<div class="card-footer bg-white bg-opacity-10 border-0">
					<div class="d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<i class="fas fa-check-circle text-white me-1"></i>
							<small class="text-white">Selesai</small>
						</div>
						<small class="text-white-50">Tahun <?= $tahun ?></small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Progress Bar Status Harmonisasi -->
	<div class="row mb-4">
		<div class="col-12">
			<div class="card shadow-sm border-0">
				<div class="card-header bg-white border-0 pb-0">
					<h5 class="card-title mb-1">Distribusi Status Ajuan Harmonisasi</h5>
					<small class="text-muted">Persentase per status</small>
				</div>
				<div class="card-body">
					<?php
					$total = 0;
					foreach ($harm_per_status as $s) $total += $s['jumlah'];
					?>
					<?php if (!empty($harm_per_status) && $total > 0): ?>
						<?php foreach ($harm_per_status as $s): ?>
							<?php
							$percent = round(($s['jumlah'] / $total) * 100, 1);
							$color = '#007bff';
							if ($s['id_status_ajuan'] == 14) $color = '#28a745';
							elseif ($s['id_status_ajuan'] == 15) $color = '#dc3545';
							elseif ($s['id_status_ajuan'] == 5) $color = '#ffc107';
							?>
							<div class="mb-2">
								<div class="d-flex justify-content-between align-items-center">
									<span class="text-dark fw-medium"><?= htmlspecialchars($s['nama_status']) ?></span>
									<span class="text-dark fw-semibold"><?= $s['jumlah'] ?> (<?= $percent ?>%)</span>
								</div>
								<div class="progress" style="height: 18px;">
									<div class="progress-bar text-white fw-semibold" role="progressbar" style="width: <?= $percent ?>%; background-color: <?= $color ?>;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
										<?= $percent ?>%
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else: ?>
						<div class="text-center py-4">
							<i class="material-icons text-muted mb-3" style="font-size: 48px;">hourglass_empty</i>
							<p class="text-muted">Belum ada data harmonisasi</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Grafik Harmonisasi Per Bulan -->
	<div class="row mb-4">
		<div class="col-12 mb-4">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-header bg-white border-0 pb-0">
					<h5 class="card-title mb-1">Ajuan Harmonisasi Per Bulan</h5>
					<small class="text-muted">Grafik ajuan harmonisasi tahun <?= $tahun ?></small>
				</div>
				<div class="card-body">
					<div class="chart-responsive-wrapper">
						<canvas id="harmonisasi-bulan-chart" style="width:100%;height:300px;"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>


	<!-- Charts Section -->
	<div class="row mb-4">
		<div class="col-xl-8 col-lg-12 mb-4">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-header bg-white border-0 pb-0">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h5 class="card-title mb-1">Publikasi Dokumen Per Bulan</h5>
							<small class="text-muted">Grafik publikasi dokumen hukum tahun <?= $tahun ?></small>
						</div>
						<div class="dropdown">
							<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
								<i class="material-icons me-1" style="font-size: 14px;">date_range</i>
								<?= $tahun ?>
							</button>
							<ul class="dropdown-menu">
								<?php if (!empty($list_tahun)): ?>
									<?php foreach ($list_tahun as $thn): ?>
										<li><a class="dropdown-item" href="?tahun=<?= $thn ?>"><?= $thn ?></a></li>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="chart-responsive-wrapper">
						<canvas id="dokumen-chart" style="width:100%;height:300px;"></canvas>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xl-4 col-lg-12 mb-4">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-header bg-white border-0 pb-0">
					<h5 class="card-title mb-1">Jenis Dokumen</h5>
					<small class="text-muted">Distribusi berdasarkan kategori</small>
				</div>
				<div class="card-body d-flex align-items-center justify-content-center">
					<div class="chart-responsive-wrapper">
						<canvas id="dokumen-type-chart" style="width:100%;height:250px;"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Grafik Dokumen Peraturan Berdasarkan Jenis -->
	<div class="row mb-4">
		<div class="col-12 mb-4">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-header bg-white border-0 pb-0">
					<h5 class="card-title mb-1">Dokumen Peraturan Berdasarkan Jenis</h5>
					<small class="text-muted">Distribusi dokumen hukum berdasarkan jenis peraturan tahun <?= $tahun ?></small>
				</div>
				<div class="card-body">
					<div class="chart-responsive-wrapper">
						<canvas id="dokumen-jenis-chart" style="width:100%;height:300px;"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	// Make data available to JavaScript
	window.dokumenData = <?= json_encode($dokumen_per_bulan ?? []) ?>;
	window.typeData = <?= json_encode(array_column($dokumen_by_type ?? [], 'jumlah')) ?>;
	window.typeLabels = <?= json_encode(array_column($dokumen_by_type ?? [], 'jenis_peraturan')) ?>;

	window.harmBulanData = <?= json_encode($harm_per_bulan ?? []) ?>;

	// Initialize dashboard when document is ready
	$(document).ready(function() {
		// Check if required functions exist
		if (typeof initDashboardCharts === 'undefined') {
			console.error('initDashboardCharts function not found. Make sure dashboard.js is loaded.');
			return;
		}

		if (typeof getThemeColors === 'undefined') {
			console.error('getThemeColors function not found. Make sure chart-utils.js is loaded.');
			return;
		}

		try {
			// Initialize charts
			initDashboardCharts();

			// Initialize interactions
			if (typeof initDashboardInteractions === 'function') {
				initDashboardInteractions();
			}
		} catch (error) {
			console.error('Error initializing dashboard:', error);
		}
	});
</script>

<!-- Dashboard specific styles are loaded from external CSS file -->

<style>
	/* Additional dashboard card improvements */
	.card-stats {
		padding: 1.5rem !important;
		padding-top: 2rem !important;
		position: relative !important;
		overflow: hidden !important;
	}

	/* Fix text color consistency */
	.dashboard .card-body .text-dark {
		color: #212529 !important;
	}

	.dashboard .progress-bar {
		color: white !important;
		font-weight: 600 !important;
	}

	.dashboard .card-header .card-title {
		color: #212529 !important;
	}

	.dashboard .card-header small {
		color: #6c757d !important;
	}

	.dashboard .table th {
		color: #212529 !important;
	}

	.dashboard .table td {
		color: #212529 !important;
	}

	.dashboard .badge {
		color: inherit !important;
	}

	/* Ensure consistent text colors for all dashboard elements */
	.dashboard .text-muted {
		color: #6c757d !important;
	}

	.dashboard .text-white-50 {
		color: rgba(255, 255, 255, 0.5) !important;
	}

	.dashboard .text-white {
		color: #ffffff !important;
	}

	.dashboard .text-dark {
		color: #212529 !important;
	}

	.dashboard .fw-medium {
		font-weight: 500 !important;
	}

	.dashboard .fw-semibold {
		font-weight: 600 !important;
	}

	.dashboard .fw-bold {
		font-weight: 700 !important;
	}

	/* Fix any potential color conflicts */
	.dashboard .card * {
		color: inherit;
	}

	.dashboard .card.text-white * {
		color: white !important;
	}

	.dashboard .card.text-white .text-dark {
		color: #212529 !important;
	}

	.icon-circle {
		width: 50px !important;
		height: 50px !important;
		background: rgba(255, 255, 255, 0.2) !important;
		border-radius: 50% !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		position: absolute !important;
		top: 15px !important;
		right: 15px !important;
		z-index: 10 !important;
		transition: all 0.3s ease !important;
		backdrop-filter: blur(10px) !important;
		border: 1px solid rgba(255, 255, 255, 0.1) !important;
	}

	.icon-circle:hover {
		background: rgba(255, 255, 255, 0.3) !important;
		transform: scale(1.1) !important;
		box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
	}

	.icon-circle .material-icons {
		font-size: 24px !important;
		color: white !important;
	}

	.card-stats .card-title {
		font-size: 2.2rem !important;
		font-weight: 700 !important;
		line-height: 1.1 !important;
		margin-bottom: 0.5rem !important;
		color: white !important;
	}

	.card-stats .card-text {
		font-size: 1rem !important;
		font-weight: 600 !important;
		margin-bottom: 0.25rem !important;
		color: white !important;
	}

	.card-stats small {
		font-size: 0.875rem !important;
		font-weight: 400 !important;
		color: rgba(255, 255, 255, 0.7) !important;
	}

	/* Card hover effects */
	.card {
		transition: all 0.3s ease-in-out !important;
		border: none !important;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
	}

	.card:hover {
		transform: translateY(-4px) !important;
		box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
	}

	/* Ensure proper text colors */
	.bg-primary * {
		color: white !important;
	}

	.bg-success * {
		color: white !important;
	}

	.bg-warning * {
		color: white !important;
	}

	.bg-info * {
		color: white !important;
	}

	/* Responsive improvements */
	@media (max-width: 768px) {
		.card-stats {
			padding: 1rem !important;
			padding-top: 1.5rem !important;
		}

		.card-stats h3 {
			font-size: 1.8rem !important;
		}

		.icon-circle {
			width: 40px !important;
			height: 40px !important;
			top: 10px !important;
			right: 10px !important;
		}

		.icon-circle .material-icons {
			font-size: 20px !important;
		}
	}

	@media (max-width: 576px) {
		.card-stats {
			padding: 0.75rem !important;
			padding-top: 1.25rem !important;
		}

		.card-stats h3 {
			font-size: 1.5rem !important;
		}

		.icon-circle {
			width: 35px !important;
			height: 35px !important;
			top: 8px !important;
			right: 8px !important;
		}

		.icon-circle .material-icons {
			font-size: 18px !important;
		}
	}
</style>