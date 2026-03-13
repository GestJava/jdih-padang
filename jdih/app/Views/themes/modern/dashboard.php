<?php helper('html') ?>
<!-- Modern Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">

<div class="card-body main-dashboard-premium">
    <?php
    if ($message['status'] == 'error') {
        show_message($message);
    }
    ?>

    <!-- Premium Welcome Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="welcome-banner card border-0 overflow-hidden shadow-premium">
                <div class="card-body p-0">
                    <div class="row g-0 align-items-center">
                        <div class="col-lg-8 p-5 welcome-text">
                            <h6 class="text-uppercase ls-2 text-white-50 fw-bold mb-2">Portal Administrasi</h6>
                            <h1 class="display-5 fw-800 text-white mb-3">Selamat Datang, <?= esc(strtoupper($session->get('user')['nama'])) ?></h1>
                            <p class="lead text-white-50 mb-4">Kelola dan pantau seluruh ekosistem hukum Kota Padang dalam satu dashboard cerdas.</p>
                            <div class="d-flex gap-2">
                                <span class="badge bg-white bg-opacity-10 text-white px-3 py-2 rounded-pill border border-white border-opacity-10">
                                    <i class="material-icons align-middle fs-6 me-1">update</i> Last login: Today
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-block text-center p-4">
                            <div class="banner-visual-shell">
                                <i class="material-icons banner-icon">gavel</i>
                                <div class="orbit-1"></div>
                                <div class="orbit-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="banner-gradient"></div>
            </div>
        </div>
    </div>

    <!-- Dynamic Filters -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="glass-filter-card card border-0 shadow-sm rounded-4 text-white">
                <div class="card-body px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center">
                        <div class="filter-icon-shell me-3">
                            <i class="material-icons">filter_list</i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Konfigurasi Data</h6>
                            <small class="text-white-50">Filter statistik berdasarkan parameter cakupan</small>
                        </div>
                    </div>
                    <form method="GET" action="<?= base_url('dashboard') ?>" class="d-flex align-items-center gap-3 filter-form">
                        <div class="select-wrapper">
                            <i class="material-icons select-icon">calendar_today</i>
                            <select name="tahun" id="tahun" class="form-select-premium" onchange="this.form.submit()">
                                <?php
                                $tahun_sekarang = date('Y');
                                $tahun_options = [];
                                for ($i = 0; $i <= 10; $i++) { $tahun_options[] = $tahun_sekarang - $i; }
                                foreach ($list_tahun as $tahun_db) { if (!in_array($tahun_db, $tahun_options)) $tahun_options[] = $tahun_db; }
                                rsort($tahun_options);
                                foreach ($tahun_options as $tahun_option):
                                    $selected = ($tahun == $tahun_option) ? 'selected' : '';
                                    $label = $tahun_option == $tahun_sekarang ? $tahun_option . ' (Aktual)' : $tahun_option;
                                ?>
                                    <option value="<?= $tahun_option ?>" <?= $selected ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-premium-action">
                             Sinkronisasi Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary Statistics Cards (Glassmorphism) -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card card border-0 shadow-sm glass-bg-blue overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="stat-icon-premium">
                            <i class="material-icons">description</i>
                        </div>
                        <div class="growth-badge <?= ($total_dokumen['growth'] ?? 0) >= 0 ? 'bg-emerald-soft' : 'bg-rose-soft' ?>">
                            <i class="material-icons fs-6 me-1"><?= ($total_dokumen['growth'] ?? 0) >= 0 ? 'trending_up' : 'trending_down' ?></i> 
                            <?= abs(round($total_dokumen['growth'] ?? 0)) ?>%
                        </div>
                    </div>
                    <div class="stat-content">
                        <h2 class="display-6 fw-800 text-white mb-1"><?= !empty($total_dokumen['jml']) ? number_format($total_dokumen['jml']) : 0 ?></h2>
                        <h6 class="text-white-50 text-uppercase ls-1 fw-bold">Total Dokumen Hukum</h6>
                    </div>
                    <div class="stat-footer-text mt-3 text-white-50 small border-top border-white border-opacity-10 pt-3">
                        Total akumulasi peraturan & berita tahun <?= $tahun ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card card border-0 shadow-sm glass-bg-purple overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="stat-icon-premium">
                            <i class="material-icons">new_releases</i>
                        </div>
                        <div class="badge bg-white bg-opacity-20 text-white px-3 py-1 rounded-pill small">Aktual</div>
                    </div>
                    <div class="stat-content">
                        <h2 class="display-6 fw-800 text-white mb-1"><?= !empty($dokumen_bulan_ini ?? 0) ? number_format($dokumen_bulan_ini) : 0 ?></h2>
                        <h6 class="text-white-50 text-uppercase ls-1 fw-bold">Publikasi Terbaru</h6>
                    </div>
                    <div class="stat-footer-text mt-3 text-white-50 small border-top border-white border-opacity-10 pt-3">
                        Total dokumen yang dirilis pada <?= date('F Y') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="stat-card card border-0 shadow-sm glass-bg-teal overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="stat-icon-premium">
                            <i class="material-icons">people</i>
                        </div>
                        <div class="growth-badge <?= ($total_user['growth'] ?? 0) >= 0 ? 'bg-emerald-soft' : 'bg-rose-soft' ?>">
                            <i class="material-icons fs-6 me-1"><?= ($total_user['growth'] ?? 0) >= 0 ? 'trending_up' : 'trending_down' ?></i> 
                            <?= abs(round($total_user['growth'] ?? 0)) ?>%
                        </div>
                    </div>
                    <div class="stat-content">
                        <h2 class="display-6 fw-800 text-white mb-1"><?= !empty($total_user['jml']) ? number_format($total_user['jml']) : 0 ?></h2>
                        <h6 class="text-white-50 text-uppercase ls-1 fw-bold">Pengguna Terverifikasi</h6>
                    </div>
                    <div class="stat-footer-text mt-3 text-white-50 small border-top border-white border-opacity-10 pt-3">
                        Basis pengguna aktif dalam ekosistem sistem
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Statistics (Modern Profiles) -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <h5 class="section-title mb-4 fw-800 text-dark">
                <i class="material-icons align-middle me-2 text-primary">analytics</i> Statistik Layanan Digital
            </h5>
        </div>
        
        <?php 
            $service_stats = [
                ['label' => 'Total Harmonisasi', 'value' => $harm_total_ajuan, 'icon' => 'balance', 'color' => 'indigo'],
                ['label' => 'Harmonisasi Selesai', 'value' => $harm_selesai, 'icon' => 'verified', 'color' => 'emerald'],
                ['label' => 'Total Legalisasi', 'value' => $leg_total_ajuan, 'icon' => 'gavel', 'color' => 'amber'],
                ['label' => 'Legalisasi Selesai', 'value' => $leg_selesai, 'icon' => 'task_alt', 'color' => 'cyan'],
            ];
        ?>

        <?php foreach ($service_stats as $service): ?>
        <div class="col-xl-3 col-md-6">
            <div class="service-card card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="service-icon-box bg-<?= $service['color'] ?>-soft text-<?= $service['color'] ?> me-3">
                            <i class="material-icons"><?= $service['icon'] ?></i>
                        </div>
                        <h6 class="text-muted fw-bold mb-0 text-uppercase small ls-1"><?= $service['label'] ?></h6>
                    </div>
                    <div class="d-flex align-items-end justify-content-between">
                        <h3 class="fw-800 text-dark mb-0"><?= number_format($service['value'] ?? 0) ?></h3>
                        <div class="mini-graph bg-<?= $service['color'] ?>-soft rounded-pill px-2 py-1">
                            <i class="material-icons fs-6 align-middle text-<?= $service['color'] ?>">insights</i>
                        </div>
                    </div>
                </div>
                <div class="service-progress bg-<?= $service['color'] ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
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
    /* Premium Dashboard Overhaul styles */
    :root {
        --primary-font: 'Inter', sans-serif;
        --heading-font: 'Outfit', sans-serif;
        --glow-primary: rgba(30, 58, 138, 0.4);
    }

    .main-dashboard-premium {
        font-family: var(--primary-font);
        background-color: #f1f5f9;
        padding: 2rem;
        min-height: 100vh;
    }

    .fw-800 { font-weight: 800; }
    .ls-1 { letter-spacing: 1px; }
    .ls-2 { letter-spacing: 2px; }

    /* Welcome Banner */
    .welcome-banner {
        border-radius: 2rem !important;
        background: #1e293b;
        position: relative;
    }

    .banner-gradient {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: linear-gradient(135deg, #1e3a8a 0%, #172554 100%);
        z-index: 1;
    }

    .welcome-text { position: relative; z-index: 5; }
    .welcome-text h1 { font-family: var(--heading-font); }

    .banner-visual-shell {
        position: relative;
        z-index: 5;
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .banner-icon {
        font-size: 100px !important;
        color: rgba(255, 255, 255, 0.15);
        animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .orbit-1, .orbit-2 {
        position: absolute;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .orbit-1 { width: 140px; height: 140px; animation: rotate 20s linear infinite; }
    .orbit-2 { width: 220px; height: 220px; animation: rotate 30s linear infinite reverse; }

    @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Filters */
    .glass-filter-card {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4) !important;
    }

    .filter-icon-shell {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-select-premium {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        outline: none;
    }

    .form-select-premium option { color: #1e293b; }

    .btn-premium-action {
        background: white;
        color: #2563eb;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .btn-premium-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        color: #1d4ed8;
    }

    /* Glass Cards */
    .stat-card { border-radius: 1.5rem !important; transition: all 0.4s ease; }
    .stat-card:hover { transform: translateY(-10px); }

    .stat-icon-premium {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .stat-icon-premium .material-icons {
        color: white !important;
        font-size: 28px !important;
    }

    .glass-bg-blue { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
    .glass-bg-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .glass-bg-teal { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); }

    .stat-content h2 { font-family: var(--heading-font); letter-spacing: -1px; }

    .bg-emerald-soft { background-color: rgba(16, 185, 129, 0.2); color: #4ade80; }
    .bg-rose-soft { background-color: rgba(244, 63, 94, 0.2); color: #fb7185; }
    .growth-badge {
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    /* Service Stats */
    .service-card { transition: all 0.3s ease; border-radius: 1.25rem !important; }
    .service-card:hover { transform: scale(1.02); }

    .service-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-indigo-soft { background: #eef2ff; } .text-indigo { color: #4f46e5; } .bg-indigo { background: #4f46e5; }
    .bg-emerald-soft-alt { background: #ecfdf5; } .text-emerald { color: #10b981; } .bg-emerald { background: #10b981; }
    .bg-amber-soft { background: #fffbeb; } .text-amber { color: #f59e0b; } .bg-amber { background: #f59e0b; }
    .bg-cyan-soft { background: #ecfeff; } .text-cyan { color: #0891b2; } .bg-cyan { background: #0891b2; }

    .service-progress {
        height: 4px;
        width: 0;
        transition: width 1s ease-in-out;
    }
    .service-card:hover .service-progress { width: 100%; }

    /* Progress & Charts */
    .progress { border-radius: 10px; background-color: #e2e8f0; overflow: hidden; }
    .progress-bar { border-radius: 10px; }

    .section-title { font-family: var(--heading-font); }

    .card-header { background: transparent !important; padding: 1.5rem !important; }

    /* Animation Entry */
    .main-dashboard-premium > .row {
        animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        opacity: 0;
    }

    .main-dashboard-premium > .row:nth-child(1) { animation-delay: 0.1s; }
    .main-dashboard-premium > .row:nth-child(2) { animation-delay: 0.2s; }
    .main-dashboard-premium > .row:nth-child(3) { animation-delay: 0.3s; }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 992px) {
        .welcome- banner { text-align: center; }
        .welcome-text { padding: 3rem !important; }
    }
</style>