<div class="legalisasi-module pb-5">
    <!-- Premium Header Section -->
    <div class="header-premium-blue p-4 p-md-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0 0 2rem 2rem;">
        <div class="position-relative z-1">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-light mb-2">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-white-50">Dashboard</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Laporan Statistik</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 fw-bold text-white mb-0 font-outfit">
                        <i class="fas fa-chart-pie me-2 opacity-75"></i>Laporan Statistik
                    </h1>
                    <p class="text-white-50 mt-2 mb-0">Dashboard komprehensif untuk performa dan statistik modul legalisasi.</p>
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="position-absolute top-0 end-0 p-5 mt-5 opacity-10">
            <i class="fas fa-chart-line fa-10x text-white rotate-12"></i>
        </div>
    </div>

    <div class="container-fluid px-md-4">
        <!-- Navigation Tabs Premium -->
        <div class="glass-card mb-4 p-1">
            <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link py-3 fw-bold active transition-all shadow-sm" href="<?= base_url('laporan') ?>">
                        <i class="fas fa-chart-pie me-2"></i>Laporan Statistik
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 fw-bold transition-all" href="<?= base_url('laporan/monitoring') ?>">
                        <i class="fas fa-chart-line me-2"></i>Monitoring Penomoran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 fw-bold transition-all" href="<?= base_url('laporan/riwayat-tte') ?>">
                        <i class="fas fa-history me-2"></i>Riwayat TTE
                    </a>
                </li>
            </ul>
        </div>

        <!-- Filter Glass Card -->
        <div class="glass-card mb-4">
            <div class="p-3 border-bottom bg-light d-flex align-items-center">
                <i class="fas fa-filter me-2 text-blue"></i>
                <h6 class="fw-bold font-outfit mb-0 small uppercase letter-spacing-1">Filter Laporan</h6>
            </div>
            <div class="p-4">
                <form method="get" action="<?= base_url('legalisasi/laporan') ?>" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Tahun</label>
                        <select name="tahun" class="form-select border-0 bg-light rounded-pill px-3">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= ($y == ($tahun ?? date('Y'))) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Bulan</label>
                        <select name="bulan" class="form-select border-0 bg-light rounded-pill px-3">
                            <option value="">Semua Bulan</option>
                            <?php $bulan_nama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($m == ($bulan ?? '')) ? 'selected' : '' ?>><?= $bulan_nama[$m-1] ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Instansi</label>
                        <select name="instansi" class="form-select border-0 bg-light rounded-pill px-3">
                            <option value="">Semua Instansi</option>
                            <?php foreach ($list_instansi ?? [] as $inst): ?>
                                <option value="<?= $inst['id'] ?>" <?= ($inst['id'] == ($id_instansi ?? '')) ? 'selected' : '' ?>><?= esc($inst['nama_instansi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Jenis Peraturan</label>
                        <select name="jenis" class="form-select border-0 bg-light rounded-pill px-3">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($list_jenis ?? [] as $jenis): ?>
                                <option value="<?= $jenis['id'] ?>" <?= ($jenis['id'] == ($id_jenis ?? '')) ? 'selected' : '' ?>><?= esc($jenis['nama_jenis']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-blue-premium w-100 rounded-pill transition-all">
                            <i class="fas fa-search me-1"></i>Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Overview Glass Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-blue-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-blue text-blue"><i class="fas fa-file-alt"></i></div>
                        <span class="badge rounded-pill bg-soft-blue text-blue px-3 py-2">Total</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1"><?= number_format($stats['total_dokumen'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Dokumen Ajuan</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-green-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-green text-green"><i class="fas fa-check-circle"></i></div>
                        <span class="badge rounded-pill bg-soft-green text-green px-3 py-2">Selesai</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1 text-green"><?= number_format($stats['selesai'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Dokumen Disetujui</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-orange-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-orange text-orange"><i class="fas fa-clock"></i></div>
                        <span class="badge rounded-pill bg-soft-orange text-orange px-3 py-2">Proses</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1 text-orange"><?= number_format($stats['dalam_proses'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Sedang Diproses</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-red-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-red text-red"><i class="fas fa-times-circle"></i></div>
                        <span class="badge rounded-pill bg-soft-red text-red px-3 py-2">Ditolak</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1 text-red"><?= number_format($stats['ditolak'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Dokumen Ditolak</p>
                </div>
            </div>
        </div>

        <!-- TTE and Penomoran Summary -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="glass-card h-100 shadow-hover transition-all border-top border-4 border-cyan-premium">
                    <div class="p-3 border-bottom d-flex align-items-center">
                        <i class="fas fa-signature me-2 text-cyan"></i>
                        <h6 class="fw-bold font-outfit mb-0 small uppercase letter-spacing-1">Statistik TTE</h6>
                    </div>
                    <div class="p-4">
                        <div class="row g-4 text-center">
                            <div class="col-4 border-end">
                                <h4 class="fw-bold text-cyan mb-1"><?= number_format($stats_tte['total_attempts'] ?? 0) ?></h4>
                                <div class="tiny text-muted uppercase tracking-1 fw-bold">Attempts</div>
                            </div>
                            <div class="col-4 border-end">
                                <h4 class="fw-bold text-green mb-1"><?= number_format($stats_tte['success'] ?? 0) ?></h4>
                                <div class="tiny text-muted uppercase tracking-1 fw-bold">Success</div>
                            </div>
                            <div class="col-4">
                                <h4 class="fw-bold text-red mb-1"><?= number_format($stats_tte['failed'] ?? 0) ?></h4>
                                <div class="tiny text-muted uppercase tracking-1 fw-bold">Failed</div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Akurasi Keberhasilan</span>
                            <?php $acc = $stats_tte['total_attempts'] > 0 ? round(($stats_tte['success'] / $stats_tte['total_attempts']) * 100, 1) : 0; ?>
                            <span class="badge bg-soft-cyan text-cyan px-3 py-2"><?= $acc ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card h-100 shadow-hover transition-all border-top border-4 border-orange-premium">
                    <div class="p-3 border-bottom d-flex align-items-center">
                        <i class="fas fa-hashtag me-2 text-orange"></i>
                        <h6 class="fw-bold font-outfit mb-0 small uppercase letter-spacing-1">Statistik Penomoran</h6>
                    </div>
                    <div class="p-4">
                        <div class="row g-4 text-center">
                            <div class="col-4 border-end">
                                <h4 class="fw-bold text-orange mb-1"><?= number_format($stats_penomoran['total_nomor'] ?? 0) ?></h4>
                                <div class="tiny text-muted uppercase tracking-1 fw-bold">Total Nomor</div>
                            </div>
                            <div class="col-4 border-end">
                                <h4 class="fw-bold text-blue mb-1"><?= number_format($stats_penomoran['sekda'] ?? 0) ?></h4>
                                <div class="tiny text-muted uppercase tracking-1 fw-bold">Sekda</div>
                            </div>
                            <div class="col-4">
                                <h4 class="fw-bold text-red mb-1"><?= number_format($stats_penomoran['walikota'] ?? 0) ?></h4>
                                <div class="tiny text-muted uppercase tracking-1 fw-bold">Walikota</div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Penyebaran Otoritas</span>
                            <div class="progress w-50" style="height: 6px; border-radius: 3px;">
                                <?php 
                                $total_nom = ($stats_penomoran['sekda'] ?? 0) + ($stats_penomoran['walikota'] ?? 0);
                                $p_sekda = $total_nom > 0 ? round(($stats_penomoran['sekda'] / $total_nom) * 100, 0) : 0;
                                ?>
                                <div class="progress-bar bg-blue" role="progressbar" style="width: <?= $p_sekda ?>%" title="Sekda: <?= $p_sekda ?>%"></div>
                                <div class="progress-bar bg-red" role="progressbar" style="width: <?= 100 - $p_sekda ?>%" title="Walikota: <?= 100 - $p_sekda ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Tables -->
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="glass-card h-100">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold font-outfit mb-0 uppercase tracking-1">Per Jenis Peraturan</h6>
                        <span class="badge bg-soft-blue text-blue rounded-pill px-3">Breakdown</span>
                    </div>
                    <div class="p-0 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-premium table-hover mb-0">
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
                                            <td class="fw-bold text-dark small"><?= esc($stat['nama_jenis']) ?></td>
                                            <td class="text-center"><span class="badge bg-soft-blue text-blue rounded-pill px-2"><?= $stat['total'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-soft-green text-green rounded-pill px-2"><?= $stat['selesai'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-soft-orange text-orange rounded-pill px-2"><?= $stat['dalam_proses'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-soft-red text-red rounded-pill px-2"><?= $stat['ditolak'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass-card h-100">
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold font-outfit mb-0 uppercase tracking-1">Top 10 Instansi Teraktif</h6>
                        <span class="badge bg-soft-green text-green rounded-pill px-3">Performa</span>
                    </div>
                    <div class="p-0 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-premium table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Instansi</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Diselesaikan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats_per_instansi ?? [] as $stat): ?>
                                        <tr>
                                            <td class="small fw-medium text-dark"><?= esc($stat['nama_instansi']) ?></td>
                                            <td class="text-center"><span class="fw-bold text-blue"><?= $stat['total'] ?></span></td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="fw-bold text-green me-2"><?= $stat['selesai'] ?></span>
                                                    <?php $persen = $stat['total'] > 0 ? round(($stat['selesai'] / $stat['total']) * 100, 0) : 0; ?>
                                                    <div class="progress" style="width: 40px; height: 4px; border-radius: 2px;">
                                                        <div class="progress-bar bg-green" role="progressbar" style="width: <?= $persen ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="glass-card mb-5 border-top border-4 border-indigo-premium">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold font-outfit mb-0 uppercase tracking-1"><i class="fas fa-chart-line me-2 text-indigo"></i>Tren Aktivitas Bulanan (<?= $tahun ?? date('Y') ?>)</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-pill border px-3" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu shadow border-0 rounded-4">
                        <li><a class="dropdown-item py-2" href="#" onclick="downloadChart()"><i class="fas fa-download me-2 text-muted"></i>Simpan Gambar</a></li>
                        <li><a class="dropdown-item py-2" href="#" onclick="window.print()"><i class="fas fa-print me-2 text-muted"></i>Cetak Laporan</a></li>
                    </ul>
                </div>
            </div>
            <div class="p-4">
                <div style="height: 350px;">
                    <canvas id="chartPerBulan"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>
    :root {
        --blue-premium: #2563eb;
        --blue-soft: #eff6ff;
        --green-premium: #10b981;
        --green-soft: #ecfdf5;
        --red-premium: #ef4444;
        --red-soft: #fef2f2;
        --orange-premium: #f59e0b;
        --orange-soft: #fffbeb;
        --cyan-premium: #06b6d4;
        --cyan-soft: #ecfeff;
        --indigo-premium: #6366f1;
        --indigo-soft: #eef2ff;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.4);
    }

    .font-outfit { font-family: 'Outfit', sans-serif; }
    .uppercase { text-transform: uppercase; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .tracking-1 { letter-spacing: 0.5px; }
    .tiny { font-size: 0.7rem; }
    .transition-all { transition: all 0.3s ease; }
    .shadow-hover:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important; }

    .header-premium-blue { box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15); }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .nav-pills .nav-link { color: #64748b; border-radius: 1.25rem; }
    .nav-pills .nav-link.active { background: white; color: var(--blue-premium); }

    .btn-blue-premium { background: var(--blue-premium); color: white; padding: 0.6rem 1.5rem; font-weight: 600; border: none; }
    .btn-blue-premium:hover { background: #1e40af; color: white; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25); }

    .mini-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.2rem; }
    .bg-soft-blue { background: var(--blue-soft); }
    .text-blue { color: var(--blue-premium); }
    .bg-blue { background-color: var(--blue-premium) !important; }
    .border-blue-premium { border-color: var(--blue-premium) !important; }

    .bg-soft-green { background: var(--green-soft); }
    .text-green { color: var(--green-premium); }
    .bg-green { background-color: var(--green-premium) !important; }
    .border-green-premium { border-color: var(--green-premium) !important; }

    .bg-soft-red { background: var(--red-soft); }
    .text-red { color: var(--red-premium); }
    .bg-red { background-color: var(--red-premium) !important; }
    .border-red-premium { border-color: var(--red-premium) !important; }

    .bg-soft-orange { background: var(--orange-soft); }
    .text-orange { color: var(--orange-premium); }
    .bg-orange { background-color: var(--orange-premium) !important; }
    .border-orange-premium { border-color: var(--orange-premium) !important; }

    .bg-soft-cyan { background: var(--cyan-soft); }
    .text-cyan { color: var(--cyan-premium); }
    .bg-cyan { background-color: var(--cyan-premium) !important; }
    .border-cyan-premium { border-color: var(--cyan-premium) !important; }

    .bg-soft-indigo { background: var(--indigo-soft); }
    .text-indigo { color: var(--indigo-premium); }
    .bg-indigo { background-color: var(--indigo-premium) !important; }
    .border-indigo-premium { border-color: var(--indigo-premium) !important; }

    .table-premium thead th {
        background: #f8fafc;
        border-bottom: 2px solid #f1f5f9;
        color: #64748b;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem;
    }

    .table-premium tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
    
    .breadcrumb-light .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.4); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartPerBulan').getContext('2d');
        const chartData = <?= json_encode($stats_per_bulan ?? []) ?>;
        
        const gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(37, 99, 235, 0.4)');
        gradientBlue.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        const gradientGreen = ctx.createLinearGradient(0, 0, 0, 400);
        gradientGreen.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
        gradientGreen.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        window.activityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.bulan),
                datasets: [
                    {
                        label: 'Total Dokumen',
                        data: chartData.map(d => d.total),
                        borderColor: '#2563eb',
                        backgroundColor: gradientBlue,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Dokumen Selesai',
                        data: chartData.map(d => d.selesai),
                        borderColor: '#10b981',
                        backgroundColor: gradientGreen,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { family: 'Outfit', size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { family: 'Outfit', size: 14, weight: 'bold' },
                        bodyFont: { family: 'Outfit', size: 13 },
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { family: 'Outfit', size: 11 }, padding: 10 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit', size: 11 }, padding: 10 }
                    }
                }
            }
        });
    });

    function downloadChart() {
        const link = document.createElement('a');
        link.download = 'Laporan-Statistik-Trend-<?= $tahun ?? date('Y') ?>.png';
        link.href = document.getElementById('chartPerBulan').toDataURL('image/png');
        link.click();
    }
</script>

