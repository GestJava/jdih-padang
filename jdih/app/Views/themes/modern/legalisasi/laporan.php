<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-bar text-primary me-2"></i><?= esc($title) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Laporan</li>
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

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= esc($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <div class="card shadow mb-4">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" href="<?= base_url('laporan') ?>" aria-current="page">
                            <i class="fas fa-chart-bar me-2"></i>Laporan Statistik
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="<?= base_url('laporan/monitoring') ?>">
                            <i class="fas fa-chart-line me-2"></i>Monitoring Penomoran
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="<?= base_url('laporan/riwayat-tte') ?>">
                            <i class="fas fa-history me-2"></i>Riwayat TTE
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-filter me-2"></i>Filter Laporan
                </h6>
            </div>
            <div class="card-body">
                <form method="get" action="<?= base_url('legalisasi/laporan') ?>" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= ($y == ($tahun ?? date('Y'))) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="">Semua Bulan</option>
                            <?php
                            $bulan_nama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($m = 1; $m <= 12; $m++):
                            ?>
                                <option value="<?= $m ?>" <?= ($m == ($bulan ?? '')) ? 'selected' : '' ?>><?= $bulan_nama[$m-1] ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Instansi</label>
                        <select name="instansi" class="form-select form-select-sm">
                            <option value="">Semua Instansi</option>
                            <?php foreach ($list_instansi ?? [] as $inst): ?>
                                <option value="<?= $inst['id'] ?>" <?= ($inst['id'] == ($id_instansi ?? '')) ? 'selected' : '' ?>><?= esc($inst['nama_instansi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Peraturan</label>
                        <select name="jenis" class="form-select form-select-sm">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($list_jenis ?? [] as $jenis): ?>
                                <option value="<?= $jenis['id'] ?>" <?= ($jenis['id'] == ($id_jenis ?? '')) ? 'selected' : '' ?>><?= esc($jenis['nama_jenis']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistik Umum -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Dokumen
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['total_dokumen'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Selesai
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['selesai'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Dalam Proses
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['dalam_proses'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Ditolak
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['ditolak'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik TTE -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-signature me-2"></i>Statistik TTE (Tanda Tangan Elektronik)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="h4 mb-0 text-info"><?= number_format($stats_tte['total_attempts'] ?? 0) ?></div>
                                <small class="text-muted">Total Attempts</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="h4 mb-0 text-success"><?= number_format($stats_tte['success'] ?? 0) ?></div>
                                <small class="text-muted">Berhasil</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="h4 mb-0 text-danger"><?= number_format($stats_tte['failed'] ?? 0) ?></div>
                                <small class="text-muted">Gagal</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="h4 mb-0 text-warning"><?= number_format($stats_tte['pending'] ?? 0) ?></div>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Per Jenis Peraturan -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-list me-2"></i>Statistik Per Jenis Peraturan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Jenis Peraturan</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Selesai</th>
                                        <th class="text-center">Proses</th>
                                        <th class="text-center">Ditolak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats_per_jenis ?? [] as $stat): ?>
                                        <tr>
                                            <td><?= esc($stat['nama_jenis']) ?></td>
                                            <td class="text-center"><span class="badge bg-primary"><?= $stat['total'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-success"><?= $stat['selesai'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-warning"><?= $stat['dalam_proses'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-danger"><?= $stat['ditolak'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stats_per_jenis)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Per Instansi (Top 10) -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-building me-2"></i>Top 10 Instansi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Instansi</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Selesai</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats_per_instansi ?? [] as $stat): ?>
                                        <tr>
                                            <td><?= esc($stat['nama_instansi']) ?></td>
                                            <td class="text-center"><span class="badge bg-primary"><?= $stat['total'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-success"><?= $stat['selesai'] ?></span></td>
                                            <td class="text-center">
                                                <?php 
                                                $persen = $stat['total'] > 0 ? round(($stat['selesai'] / $stat['total']) * 100, 1) : 0;
                                                echo $persen . '%';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stats_per_instansi)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Penomoran -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-hashtag me-2"></i>Statistik Penomoran
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="h4 mb-0 text-warning"><?= number_format($stats_penomoran['total_nomor'] ?? 0) ?></div>
                                <small class="text-muted">Total Nomor Diterbitkan</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="h4 mb-0 text-info"><?= number_format($stats_penomoran['sekda'] ?? 0) ?></div>
                                <small class="text-muted">Authority Sekda</small>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="h4 mb-0 text-danger"><?= number_format($stats_penomoran['walikota'] ?? 0) ?></div>
                                <small class="text-muted">Authority Walikota</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Per Bulan -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-gradient-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-chart-line me-2"></i>Tren Per Bulan (Tahun <?= $tahun ?? date('Y') ?>)
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartPerBulan" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // Chart Per Bulan
    const ctx = document.getElementById('chartPerBulan');
    if (ctx) {
        const chartData = <?= json_encode($stats_per_bulan ?? []) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.bulan),
                datasets: [{
                    label: 'Total Dokumen',
                    data: chartData.map(d => d.total),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Selesai',
                    data: chartData.map(d => d.selesai),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

</script>

<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .border-left-danger {
        border-left: 4px solid #e74a3b !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    /* Navigation Tabs Styling */
    .nav-tabs-custom {
        border-bottom: 2px solid #dee2e6;
    }
    .nav-tabs-custom .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #495057;
        border-bottom-color: #dee2e6;
        background-color: #f8f9fa;
    }
    .nav-tabs-custom .nav-link.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background-color: transparent;
        font-weight: 600;
    }
    .nav-tabs-custom .nav-link i {
        margin-right: 0.5rem;
    }
</style>

