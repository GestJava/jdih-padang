<div class="container-fluid animate__animated animate__fadeIn">
    <!-- Modern Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-5">
        <div class="header-content">
            <h1 class="h2 fw-bold text-dark mb-1">
                <i class="fas fa-balance-scale text-blue-premium me-3 mb-2"></i><?= esc($title) ?>
            </h1>
            <p class="text-muted mb-0">Monitor dan kelola seluruh tahapan proses harmonisasi produk hukum daerah.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-soft-blue px-4 py-2 rounded-pill shadow-sm mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-blue-premium text-decoration-none"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Harmonisasi</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success border-0 shadow-sm animate__animated animate__slideInDown mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <div><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm animate__animated animate__slideInDown mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                <div><?= esc(session()->getFlashdata('error')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Summary Card -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="glass-card shadow-premium border-start border-blue-premium border-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-blue-premium text-white rounded-3 me-3 p-3 shadow-sm">
                                <i class="fas fa-chart-line fs-4"></i>
                            </div>
                            <div>
                                <h5 class="m-0 fw-bold text-dark">Ikhtisar Harmonisasi</h5>
                                <p class="text-muted mb-0 small">Data akumulasi tahun <?= date('Y') ?></p>
                            </div>
                        </div>
                        <div class="text-end bg-soft-blue px-4 py-3 rounded-4 shadow-sm">
                            <h2 class="mb-0 fw-black text-blue-premium"><?= number_format($statistics['total_ajuan'] ?? 0) ?></h2>
                            <span class="text-uppercase small fw-bold text-muted tracking-wider">Total Seluruh Ajuan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Statistics dengan Progress Bar -->
    <?php if (!empty($statistics['status_details'])): ?>
        <?php
        $totalAjuan = $statistics['total_ajuan'] ?? 1;
        
        $leftColumnStatuses = array_filter($statistics['status_details'], function($status) {
            return in_array($status['id'], [1, 2, 3, 4, 5, 6]);
        });
        
        $rightColumnStatuses = array_filter($statistics['status_details'], function($status) {
            return in_array($status['id'], [7, 8, 9, 10, 11, 12, 13]);
        });
        
        usort($leftColumnStatuses, function($a, $b) { return $a['id'] <=> $b['id']; });
        usort($rightColumnStatuses, function($a, $b) { return $a['id'] <=> $b['id']; });
        ?>
        
        <div class="row g-4 mb-5">
            <!-- Kolom Kiri: Draft sampai Finalisasi -->
            <div class="col-lg-6">
                <div class="glass-card shadow-premium h-100 overflow-hidden">
                    <div class="card-header-premium p-4 border-bottom bg-soft-blue">
                        <h6 class="mb-0 fw-bold text-dark text-uppercase tracking-wider">
                            <i class="fas fa-pen-nib text-blue-premium me-2"></i>Draft & Proses Validasi
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="status-list modern-scrollbar">
                            <?php 
                            $leftTotal = 0;
                            foreach ($leftColumnStatuses as $status): 
                                $leftTotal += $status['count'];
                                $percentage = $totalAjuan > 0 ? ($status['count'] / $totalAjuan) * 100 : 0;
                                $colorClass = 'bg-' . $status['color'];
                                $icon = $status['icon'] ?? 'circle';
                            ?>
                                <div class="status-item-premium mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="status-icon bg-light-premium text-<?= esc($status['color']) ?> rounded-circle me-3">
                                                <i class="fas fa-<?= esc($icon) ?>"></i>
                                            </div>
                                            <span class="fw-semibold text-dark small-plus"><?= esc($status['nama_status']) ?></span>
                                        </div>
                                        <span class="badge rounded-pill bg-<?= esc($status['color']) ?> px-3"><?= number_format($status['count']) ?></span>
                                    </div>
                                    <div class="progress progress-premium" style="height: 6px;">
                                        <div class="progress-bar <?= esc($colorClass) ?> shadow-sm" 
                                             role="progressbar" 
                                             style="width: <?= number_format($percentage, 1) ?>%" 
                                             aria-valuenow="<?= $status['count'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?= $totalAjuan ?>">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small class="text-muted x-small fw-bold"><?= number_format($percentage, 1) ?>% Proporsi</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if ($leftTotal > 0): ?>
                        <div class="card-footer bg-light border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                            <span class="x-small fw-bold text-muted text-uppercase tracking-wider">Subtotal Workflow</span>
                            <span class="badge bg-blue-premium rounded-pill px-3 py-2"><?= number_format($leftTotal) ?> Berkas</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kolom Kanan: Paraf & TTE -->
            <div class="col-lg-6">
                <div class="glass-card shadow-premium h-100 overflow-hidden">
                    <div class="card-header-premium p-4 border-bottom bg-soft-green">
                        <h6 class="mb-0 fw-bold text-dark text-uppercase tracking-wider">
                            <i class="fas fa-file-signature text-success me-2"></i>Tahap Penandatanganan
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="status-list modern-scrollbar">
                            <?php 
                            $rightTotal = 0;
                            foreach ($rightColumnStatuses as $status): 
                                $rightTotal += $status['count'];
                                $percentage = $totalAjuan > 0 ? ($status['count'] / $totalAjuan) * 100 : 0;
                                $colorClass = 'bg-' . $status['color'];
                                $icon = $status['icon'] ?? 'circle';
                            ?>
                                <div class="status-item-premium mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="status-icon bg-light-premium text-<?= esc($status['color']) ?> rounded-circle me-3">
                                                <i class="fas fa-<?= esc($icon) ?>"></i>
                                            </div>
                                            <span class="fw-semibold text-dark small-plus"><?= esc($status['nama_status']) ?></span>
                                        </div>
                                        <span class="badge rounded-pill bg-<?= esc($status['color']) ?> px-3"><?= number_format($status['count']) ?></span>
                                    </div>
                                    <div class="progress progress-premium" style="height: 6px;">
                                        <div class="progress-bar <?= esc($colorClass) ?> shadow-sm" 
                                             role="progressbar" 
                                             style="width: <?= number_format($percentage, 1) ?>%" 
                                             aria-valuenow="<?= $status['count'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?= $totalAjuan ?>">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small class="text-muted x-small fw-bold"><?= number_format($percentage, 1) ?>% Proporsi</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if ($rightTotal > 0): ?>
                        <div class="card-footer bg-light border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                            <span class="x-small fw-bold text-muted text-uppercase tracking-wider">Subtotal Workflow</span>
                            <span class="badge bg-success rounded-pill px-3 py-2"><?= number_format($rightTotal) ?> Berkas</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="glass-card shadow-premium p-5 mb-5 text-center">
            <div class="icon-box bg-soft-blue text-blue-premium rounded-circle m-auto mb-3" style="width: 80px; height: 80px;">
                <i class="fas fa-info-circle fs-1"></i>
            </div>
            <h5 class="fw-bold">Statistik Belum Tersedia</h5>
            <p class="text-muted">Data akan muncul setelah Anda memiliki ajuan dalam sistem.</p>
        </div>
    <?php endif; ?>

    <!-- Main Data Table Card -->
    <div class="glass-card shadow-premium mb-5 overflow-hidden">
        <div class="card-header-premium p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-box bg-blue-premium text-white rounded-3 me-3 p-2 shadow-sm">
                    <i class="fas fa-table"></i>
                </div>
                <div>
                    <h5 class="m-0 fw-bold text-dark tracking-tight">Daftar Ajuan Harmonisasi</h5>
                    <span class="badge bg-soft-blue text-blue-premium rounded-pill px-3 mt-1"><?= $statistics['total_ajuan'] ?? 0 ?> Berkas Terdata</span>
                </div>
            </div>

            <?php if (in_array('create', $user_actions ?? [])): ?>
                <a href="<?= base_url('harmonisasi/new') ?>" class="btn btn-blue-premium px-4 rounded-pill shadow-hover">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Ajuan Baru
                </a>
            <?php endif; ?>
        </div>

        <div class="card-body p-4 pt-0">
            <div class="table-container-premium">
                <div class="table-responsive">
                    <table id="harmonisasi-table" class="table table-hover align-middle custom-modern-table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th width="35%">Judul Rancangan</th>
                                <th width="150">Jenis</th>
                                <th>Instansi Pemohon</th>
                                <th width="150">Tgl. Pengajuan</th>
                                <th width="120">Status</th>
                                <th width="120" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Confirmation for submit action
    function confirmSubmit() {
        return confirm('Apakah Anda yakin ingin mengajukan draft ini? Proses ini tidak dapat dibatalkan.');
    }

</script>

<style>
	/* --- Premium Harmonisasi Styles --- */
	
    /* Typography & Hierarchy */
    .tracking-tight { letter-spacing: -0.5px; }
    .tracking-wider { letter-spacing: 0.5px; }
    .small-plus { font-size: 0.95rem; }
    .x-small { font-size: 0.75rem; }
    .fw-black { font-weight: 900; }

	/* Glass Card Container */
	.glass-card {
		background: rgba(255, 255, 255, 0.95);
		backdrop-filter: blur(10px);
		-webkit-backdrop-filter: blur(10px);
		border: 1px solid rgba(255, 255, 255, 0.3);
		border-radius: 20px;
		transition: transform 0.3s ease, box-shadow 0.3s ease;
	}
	
	.shadow-premium {
		box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0, 0, 0, 0.03) !important;
	}

	/* Card Header */
	.card-header-premium {
		background: transparent;
		border-bottom: none;
	}

	.icon-box {
		width: 48px;
		height: 48px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

    .status-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

	/* Table Styling */
	.table-container-premium {
		background: #fcfcfd;
		border-radius: 15px;
		border: 1px solid #f1f3f9;
		overflow: hidden;
	}

	.custom-modern-table {
		margin-bottom: 0 !important;
		border-collapse: separate !important;
		border-spacing: 0 !important;
	}

	.custom-modern-table thead th {
		background-color: #f8faff !important;
		color: #5d6e82;
		font-weight: 700;
		text-transform: uppercase;
		font-size: 0.75rem;
		letter-spacing: 0.5px;
		padding: 18px 15px !important;
		border-bottom: 2px solid #edf2f9 !important;
		border-top: none !important;
	}

	.custom-modern-table td {
		padding: 18px 15px !important;
		border-bottom: 1px solid #f1f3f9 !important;
		color: #334155;
		font-size: 0.875rem;
	}

	.custom-modern-table tbody tr:hover {
		background-color: #f8faff !important;
        transform: none; /* Disable old transform scale */
	}

	/* Premium Progress Bars */
    .progress-premium {
        background-color: #f1f5f9;
        border-radius: 50px;
        overflow: hidden;
    }

    .status-item-premium {
        transition: transform 0.2s ease;
    }

    .status-item-premium:hover {
        transform: translateX(5px);
    }

	/* Premium Buttons */
	.btn-blue-premium {
		background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
		color: white !important;
		border: none;
		font-weight: 600;
	}

	.btn-blue-premium:hover {
		background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
		transform: translateY(-2px);
		box-shadow: 0 7px 14px rgba(37, 99, 235, 0.2) !important;
	}

    .bg-light-premium {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
    }

	.shadow-hover:hover {
		box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
	}

	/* Utility Colors & Layout */
	.bg-soft-blue { background-color: #f0f7ff; }
    .bg-soft-green { background-color: #f0fdf4; }
	.text-blue-premium { color: #2563eb; }
    .border-blue-premium { border-color: #2563eb !important; }

    .rounded-4 { border-radius: 1rem !important; }

    /* Custom Scrollbar */
    .modern-scrollbar::-webkit-scrollbar { width: 4px; }
    .modern-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .modern-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

	@media (max-width: 768px) {
		.d-sm-flex {
			flex-direction: column;
			gap: 1.5rem;
		}
	}
</style>