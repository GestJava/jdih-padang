<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-balance-scale text-primary me-2"></i><?= esc($title) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Harmonisasi</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Summary Card -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="fas fa-chart-bar text-primary me-2"></i>Statistik Ajuan Harmonisasi
                            </h5>
                            <small class="text-muted">Tahun <?= date('Y') ?></small>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-primary"><?= number_format($statistics['total_ajuan'] ?? 0) ?></h3>
                            <small class="text-muted">Total Ajuan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Statistics dengan Progress Bar - Semua 15 Status -->
    <?php if (!empty($statistics['status_details'])): ?>
        <?php
        // Hitung total untuk progress bar
        $totalAjuan = $statistics['total_ajuan'] ?? 1; // Avoid division by zero
        
        // Group status berdasarkan kategori workflow untuk tampilan 2 kolom
        // Kolom 1: Draft sampai Finalisasi (ID 1-6)
        // Kolom 2: Paraf sampai TTE Walikota (ID 7-13), EXCLUDE Selesai (14) dan Ditolak (15)
        $leftColumnStatuses = array_filter($statistics['status_details'], function($status) {
            return in_array($status['id'], [1, 2, 3, 4, 5, 6]);
        });
        
        $rightColumnStatuses = array_filter($statistics['status_details'], function($status) {
            // Exclude Selesai (14) dan Ditolak (15)
            return in_array($status['id'], [7, 8, 9, 10, 11, 12, 13]);
        });
        
        // Sort berdasarkan ID untuk urutan yang benar
        usort($leftColumnStatuses, function($a, $b) { return $a['id'] <=> $b['id']; });
        usort($rightColumnStatuses, function($a, $b) { return $a['id'] <=> $b['id']; });
        ?>
        
        <div class="row mb-4">
            <!-- Kolom Kiri: Draft sampai Finalisasi -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-light border-0 py-2">
                        <h6 class="mb-0 fw-bold text-uppercase">
                            <i class="fas fa-file-edit text-primary me-2"></i>Draft & Proses Harmonisasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="status-list">
                            <?php 
                            $leftTotal = 0;
                            foreach ($leftColumnStatuses as $status): 
                                $leftTotal += $status['count'];
                                $percentage = $totalAjuan > 0 ? ($status['count'] / $totalAjuan) * 100 : 0;
                                $colorClass = 'bg-' . $status['color'];
                                $icon = $status['icon'] ?? 'circle';
                            ?>
                                <div class="status-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <i class="fas fa-<?= esc($icon) ?> text-<?= esc($status['color']) ?> me-2" style="width: 20px; text-align: center;"></i>
                                            <span class="status-name fw-medium"><?= esc($status['nama_status']) ?></span>
                                        </div>
                                        <span class="badge bg-<?= esc($status['color']) ?> ms-2"><?= number_format($status['count']) ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar <?= esc($colorClass) ?>" 
                                             role="progressbar" 
                                             style="width: <?= number_format($percentage, 1) ?>%" 
                                             aria-valuenow="<?= $status['count'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?= $totalAjuan ?>">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= number_format($percentage, 1) ?>% dari total</small>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($leftTotal > 0): ?>
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-muted">Subtotal:</span>
                                        <span class="badge bg-primary fs-6"><?= number_format($leftTotal) ?> ajuan</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Paraf & TTE (tanpa Selesai dan Ditolak) -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-light border-0 py-2">
                        <h6 class="mb-0 fw-bold text-uppercase">
                            <i class="fas fa-stamp text-success me-2"></i>Paraf & TTE
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="status-list">
                            <?php 
                            $rightTotal = 0;
                            foreach ($rightColumnStatuses as $status): 
                                $rightTotal += $status['count'];
                                $percentage = $totalAjuan > 0 ? ($status['count'] / $totalAjuan) * 100 : 0;
                                $colorClass = 'bg-' . $status['color'];
                                $icon = $status['icon'] ?? 'circle';
                            ?>
                                <div class="status-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <i class="fas fa-<?= esc($icon) ?> text-<?= esc($status['color']) ?> me-2" style="width: 20px; text-align: center;"></i>
                                            <span class="status-name fw-medium"><?= esc($status['nama_status']) ?></span>
                                        </div>
                                        <span class="badge bg-<?= esc($status['color']) ?> ms-2"><?= number_format($status['count']) ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar <?= esc($colorClass) ?>" 
                                             role="progressbar" 
                                             style="width: <?= number_format($percentage, 1) ?>%" 
                                             aria-valuenow="<?= $status['count'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?= $totalAjuan ?>">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= number_format($percentage, 1) ?>% dari total</small>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($rightTotal > 0): ?>
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-muted">Subtotal:</span>
                                        <span class="badge bg-success fs-6"><?= number_format($rightTotal) ?> ajuan</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>Data statistik belum tersedia.
        </div>
    <?php endif; ?>

    <!-- Main Data Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-table me-2"></i>Daftar Ajuan Harmonisasi
                <span class="badge bg-light text-dark ms-2"><?= $statistics['total_ajuan'] ?? 0 ?> ajuan</span>
            </h6>

            <?php if (in_array('create', $user_actions ?? [])): ?>
                <a href="<?= base_url('harmonisasi/new') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>Tambah Ajuan Baru
                </a>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <!-- Table selalu ditampilkan; DataTables server-side akan mengisi tbody via AJAX -->
            <div class="table-responsive">
                <table id="harmonisasi-table" class="table table-striped table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Judul Rancangan</th>
                            <th width="12%">Jenis</th>
                            <th width="15%">Instansi Pemohon</th>
                            <th width="12%">Tgl. Pengajuan</th>
                            <th width="10%">Status</th>
                            <th width="11%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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
    /* Enhanced styling for all harmonisasi pages */
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }

    .btn-group .btn {
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
        z-index: 1;
    }

    .badge {
        padding: 0.5em 0.75em;
        font-size: 0.75rem;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
        }

        .table-responsive .btn-group {
            min-width: 100px;
        }
    }

    /* Loading animation for buttons */
    .btn:active {
        transform: scale(0.98);
    }

    /* Enhanced statistics cards */
    .h5 {
        font-size: 1.75rem;
        font-weight: 700;
    }

    .text-xs {
        font-size: 0.7rem;
    }

    /* DataTable buttons styling */
    .dt-buttons {
        margin-bottom: 1rem;
    }

    .dt-buttons .btn {
        margin-right: 0.5rem;
    }

    /* Page-specific enhancements */
    .harmonisasi-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .statistics-card {
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .statistics-card .card-body {
        padding: 1.5rem;
    }

    .main-content-card {
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .main-content-card .card-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-bottom: none;
    }

    .table-dark {
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .empty-state h5 {
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }

    /* Status Statistics Styling */
    .status-list {
        max-height: 500px;
        overflow-y: auto;
    }

    .status-item {
        padding: 0.75rem;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }

    .status-item:hover {
        background-color: #f8f9fa;
    }

    .status-name {
        font-size: 0.9rem;
        color: #495057;
        flex: 1;
    }

    .status-item .progress {
        background-color: #e9ecef;
        border-radius: 4px;
    }

    .status-item .progress-bar {
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    /* Custom scrollbar untuk status list */
    .status-list::-webkit-scrollbar {
        width: 6px;
    }

    .status-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .status-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .status-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .status-list {
            max-height: 400px;
        }
    }
</style>