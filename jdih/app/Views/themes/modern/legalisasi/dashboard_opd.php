<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Premium Welcome Banner -->
        <div class="premium-welcome-banner mb-5">
            <div class="banner-content">
                <div class="banner-text">
                    <h1 class="banner-title">Dashboard Legalisasi OPD</h1>
                    <p class="banner-subtitle">Otoritas Verifikasi & Paraf Elektronik Kepala Perangkat Daerah JDIH Kota Padang</p>
                </div>
                <div class="banner-visual">
                    <div class="visual-circle"></div>
                    <i class="fas fa-building banner-icon"></i>
                </div>
            </div>
            <div class="banner-footer">
                <div class="footer-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?= date('d F Y') ?></span>
                </div>
                <div class="footer-item">
                    <i class="fas fa-clock"></i>
                    <span id="live-clock"><?= date('H:i') ?></span>
                </div>
                <div class="footer-item">
                    <i class="fas fa-shield-alt text-success"></i>
                    <span>Internal Secured</span>
                </div>
            </div>
        </div>

        <!-- Glassmorphism Statistics Cards -->
        <div class="row g-4 mb-5 animate__animated animate__fadeIn">
            <!-- Pending Paraf -->
            <div class="col-xl-4 col-md-6">
                <div class="glass-card stat-card border-bottom-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper bg-soft-warning">
                                <i class="fas fa-signature text-warning"></i>
                            </div>
                            <?php if (($stats['pending_opd_paraf'] ?? 0) > 0): ?>
                                <span class="badge pulse-warning">Pending</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="stat-value"><?= number_format($stats['pending_opd_paraf'] ?? 0) ?></h3>
                        <p class="stat-label">Menunggu Paraf Anda</p>
                        <div class="stat-progress">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Bulan Ini -->
            <div class="col-xl-4 col-md-6">
                <div class="glass-card stat-card border-bottom-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper bg-soft-info">
                                <i class="fas fa-chart-line text-info"></i>
                            </div>
                            <span class="badge bg-soft-info text-info">Bulan Ini</span>
                        </div>
                        <h3 class="stat-value"><?= number_format($stats['dalam_proses'] ?? 0) ?></h3>
                        <p class="stat-label">Dokumen Terproses</p>
                        <div class="stat-progress">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Terproses -->
            <div class="col-xl-4 col-md-6">
                <div class="glass-card stat-card border-bottom-dark h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper bg-soft-dark">
                                <i class="fas fa-history text-dark"></i>
                            </div>
                            <span class="badge bg-soft-dark text-dark">Total</span>
                        </div>
                        <h3 class="stat-value"><?= number_format($stats['selesai'] ?? 0) ?></h3>
                        <p class="stat-label">Riwayat Paraf</p>
                        <div class="stat-progress">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-dark" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Paraf Requests -->
        <div class="glass-card mb-5 animate__animated animate__fadeInUp">
            <div class="card-header-premium bg-gradient-primary-light">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="header-left">
                        <h5 class="mb-0 text-primary">Dokumen Menunggu Paraf OPD</h5>
                        <p class="text-muted small mb-0">Verifikasi dokumen dari perangkat daerah sebelum dilanjutkan ke Bagian Hukum</p>
                    </div>
                    <div class="header-right">
                        <span class="badge bg-soft-primary text-primary"><?= count($pending_paraf ?? []) ?> Dokumen</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="pending-paraf-table" class="table table-premium mb-0">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Informasi Dokumen</th>
                                <th class="text-center">Tgl Finalisasi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pending_paraf)): ?>
                                <?php $no = 1; foreach ($pending_paraf as $ajuan): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="file-icon-wrapper me-3">
                                                    <i class="fas fa-file-contract text-primary fs-4"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark mb-1"><?= esc($ajuan['judul_peraturan']) ?></div>
                                                    <div class="text-muted small">
                                                        <span class="badge bg-soft-primary text-primary me-2"><?= esc($ajuan['nama_jenis']) ?></span>
                                                        <i class="fas fa-building me-1"></i><?= esc($ajuan['nama_instansi']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="text-dark font-outfit"><?= isset($ajuan['tanggal_finalisasi']) ? date('d M Y', strtotime($ajuan['tanggal_finalisasi'])) : '-' ?></div>
                                            <div class="text-muted small"><?= isset($ajuan['tanggal_finalisasi']) ? date('H:i', strtotime($ajuan['tanggal_finalisasi'])) : '' ?> WIB</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-soft-warning text-dark border-dashed-warning">
                                                <i class="fas fa-signature me-1"></i>Menunggu Paraf
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('legalisasi/detail/' . $ajuan['id_ajuan']) ?>" class="btn btn-premium-action btn-primary-premium">
                                                <i class="fas fa-eye me-1"></i> Detail & Paraf
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <?php if (empty($pending_paraf)): ?>
            <div class="glass-card animate__animated animate__zoomIn">
                <div class="card-body text-center py-5">
                    <div class="empty-state-visual mb-4">
                        <i class="fas fa-check-circle fa-4x text-success opacity-25"></i>
                    </div>
                    <h5 class="text-dark fw-bold">Tidak Ada Dokumen Menunggu</h5>
                    <p class="text-muted">Semua dokumen sudah diparaf atau belum tersedia untuk verifikasi Anda.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap');

    :root {
        --premium-primary: #0d9488; /* Teal */
        --premium-secondary: #0891b2; /* Cyan */
        --premium-success: #059669; /* Emerald */
        --premium-warning: #d97706; /* Amber */
        --premium-glass: rgba(255, 255, 255, 0.9);
        --premium-shadow: 0 10px 30px rgba(13, 148, 136, 0.08);
        --font-main: 'Inter', sans-serif;
        --font-heading: 'Outfit', sans-serif;
    }

    body {
        font-family: var(--font-main);
        background-color: #f0fdfa; /* Soft Teal bg */
    }

    .font-outfit { font-family: var(--font-heading); }

    /* Welcome Banner */
    .premium-welcome-banner {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        border-radius: 24px;
        padding: 45px;
        position: relative;
        overflow: hidden;
        color: white;
        box-shadow: 0 20px 40px rgba(13, 148, 136, 0.2);
    }

    .banner-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .banner-title {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 2.4rem;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .banner-subtitle {
        font-size: 1.15rem;
        opacity: 0.85;
        max-width: 650px;
        line-height: 1.6;
    }

    .visual-circle {
        position: absolute;
        top: -60px;
        right: -60px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        border-radius: 50%;
        z-index: -1;
    }

    .banner-icon {
        font-size: 7rem;
        opacity: 0.15;
        transform: rotate(-15deg);
    }

    .banner-footer {
        margin-top: 35px;
        display: flex;
        gap: 25px;
        padding-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 2;
    }

    .footer-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
        background: rgba(255, 255, 255, 0.05);
        padding: 10px 18px;
        border-radius: 50px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Glass Cards */
    .glass-card {
        background: var(--premium-glass);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: var(--premium-shadow);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 45px rgba(13, 148, 136, 0.15);
    }

    .stat-icon-wrapper {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
    }

    .bg-soft-warning { background: rgba(217, 119, 6, 0.1); }
    .bg-soft-info { background: rgba(8, 145, 178, 0.1); }
    .bg-soft-dark { background: rgba(31, 41, 55, 0.1); }
    .bg-soft-primary { background: rgba(13, 148, 136, 0.1); }

    .stat-value {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 2rem;
        color: #1a202c;
        margin-bottom: 8px;
    }

    .stat-label {
        color: #718096;
        font-weight: 500;
        font-size: 1rem;
        margin-bottom: 18px;
    }

    /* Table & Headers */
    .card-header-premium {
        padding: 25px 35px;
        border-radius: 20px 20px 0 0;
    }

    .bg-gradient-primary-light { background: linear-gradient(to right, rgba(13, 148, 136, 0.05), rgba(13, 148, 136, 0.02)); border-bottom: 1px solid rgba(13, 148, 136, 0.1); }

    .table-premium thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.75px;
        padding: 22px 20px;
        border-top: none;
    }

    .table-premium tbody td {
        padding: 20px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .file-icon-wrapper {
        width: 48px;
        height: 48px;
        background: #fff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid #edf2f7;
    }

    /* Buttons */
    .btn-premium-action {
        padding: 10px 22px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary-premium { background: var(--premium-primary); color: white; }
    .btn-primary-premium:hover { background: #0f766e; transform: scale(1.05); color: white; }

    /* Badges */
    .badge {
        padding: 8px 15px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .pulse-warning {
        background: rgba(217, 119, 6, 0.1);
        color: #d97706;
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(217, 119, 6, 0); }
        100% { box-shadow: 0 0 0 0 rgba(217, 119, 6, 0); }
    }

    .border-dashed-warning {
        border: 1px dashed rgba(217, 119, 6, 0.4);
    }

    .animate__animated { animation-duration: 0.8s; }
</style>

<script>
    $(document).ready(function() {
        if ($('#pending-paraf-table').length) {
            $('#pending-paraf-table').DataTable({
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                order: [[2, 'desc']],
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center p-3"<"length-menu"l><"search-box"f>>rt<"d-flex justify-content-between align-items-center p-3"<"info"i><"pagination"p>>'
            });
        }

        setInterval(() => {
            const now = new Date();
            $('#live-clock').text(now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0'));
        }, 60000);
    });
</script>