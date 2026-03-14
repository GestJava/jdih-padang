<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Premium Welcome Banner -->
        <div class="premium-welcome-banner mb-5">
            <div class="banner-content">
                <div class="banner-text">
                    <h1 class="banner-title">Dashboard Legalisasi Walikota</h1>
                    <p class="banner-subtitle">Otoritas Pengesahan Akhir & Tanda Tangan Elektronik Resmi JDIH Kota Padang</p>
                </div>
                <div class="banner-visual">
                    <div class="visual-circle"></div>
                    <i class="fas fa-crown banner-icon"></i>
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
                    <span>BSrE Verified</span>
                </div>
            </div>
        </div>

        <!-- Glassmorphism Statistics Cards -->
        <div class="row g-4 mb-5 animate__animated animate__fadeIn">
            <!-- Pending TTE -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card stat-card border-bottom-danger h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper bg-soft-danger">
                                <i class="fas fa-stamp text-danger"></i>
                            </div>
                            <?php if (($stats['pending_walikota_tte'] ?? 0) > 0): ?>
                                <span class="badge pulse-danger">Pending</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="stat-value"><?= number_format($stats['pending_walikota_tte'] ?? 0) ?></h3>
                        <p class="stat-label">Menunggu TTE Walikota</p>
                        <div class="stat-progress">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TTE Tahun Ini -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card stat-card border-bottom-success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper bg-soft-success">
                                <i class="fas fa-check-double text-success"></i>
                            </div>
                            <span class="badge bg-soft-success text-success">Tahun Ini</span>
                        </div>
                        <h3 class="stat-value"><?= number_format($stats['tte_tahun_ini'] ?? 0) ?></h3>
                        <p class="stat-label">Dokumen Disahkan (TTE)</p>
                        <div class="stat-progress">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Selesai -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card stat-card border-bottom-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper bg-soft-info">
                                <i class="fas fa-archive text-info"></i>
                            </div>
                            <span class="badge bg-soft-info text-info">Global</span>
                        </div>
                        <h3 class="stat-value"><?= number_format($stats['selesai'] ?? 0) ?></h3>
                        <p class="stat-label">Total Dokumen Selesai</p>
                        <div class="stat-progress">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Ajuan -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card stat-card border-bottom-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="stat-icon-wrapper bg-soft-primary">
                                <i class="fas fa-file-invoice text-primary"></i>
                            </div>
                            <span class="badge bg-soft-primary text-primary">2024</span>
                        </div>
                        <h3 class="stat-value"><?= number_format($stats['total_ajuan'] ?? 0) ?></h3>
                        <p class="stat-label">Total Pengajuan Masuk</p>
                        <div class="stat-progress">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-premium-success alert-dismissible fade show mb-4 animate__animated animate__fadeInDown" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon-wrapper me-3">
                        <i class="fas fa-check-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading mb-1">Berhasil!</h6>
                        <p class="mb-0"><?= esc(session()->getFlashdata('success')) ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-premium-danger alert-dismissible fade show mb-4 animate__animated animate__fadeInDown" role="alert">
                <div class="d-flex align-items-center">
                    <div class="alert-icon-wrapper me-3">
                        <i class="fas fa-exclamation-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading mb-1">Terjadi Kesalahan</h6>
                        <p class="mb-0"><?= esc(session()->getFlashdata('error')) ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Main Content Table -->
        <div class="glass-card mb-4 animate__animated animate__fadeInUp">
            <div class="card-header-premium">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="header-left">
                        <h5 class="mb-0">Daftar Dokumen Menunggu TTE Walikota</h5>
                        <p class="text-muted small mb-0">Verifikasi akhir dan pengesahan digital menggunakan sertifikat BSrE</p>
                    </div>
                    <div class="header-right">
                        <button class="btn btn-premium-refresh" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tte-walikota-table" class="table table-premium mb-0">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Informasi Dokumen</th>
                                <th class="text-center">Tanggal Pengajuan</th>
                                <th class="text-center">Status Keamanan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pending_tte)): ?>
                                <?php $no = 1; foreach ($pending_tte as $item): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="file-icon-wrapper me-3">
                                                    <i class="fas fa-file-pdf text-danger fs-4"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark mb-1"><?= esc($item['judul_peraturan']) ?></div>
                                                    <div class="text-muted small">
                                                        <span class="badge bg-soft-info text-info me-2"><?= esc($item['nama_jenis']) ?></span>
                                                        <i class="fas fa-building me-1"></i><?= esc($item['nama_instansi'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="text-dark font-outfit"><?= date('d M Y', strtotime($item['tanggal_pengajuan'])) ?></div>
                                            <div class="text-muted small"><?= date('H:i', strtotime($item['tanggal_pengajuan'])) ?> WIB</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-soft-danger text-danger">
                                                <i class="fas fa-shield-alt me-1"></i>Authority Pass Required
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group-premium">
                                                <a href="<?= base_url('legalisasi/detail/' . $item['id_ajuan']) ?>" class="btn btn-premium-action btn-view" title="Detail & Proses">
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-check-double fa-3x text-premium-muted mb-3"></i>
                                            <h6 class="text-muted">Semua Dokumen Telah Diproses</h6>
                                            <p class="text-premium-muted small mb-0">Tidak ada pengajuan legalisasi baru yang menunggu tanda tangan Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap');

    :root {
        --premium-primary: #dc3545; /* Walikota Red-Crimson theme */
        --premium-secondary: #ffc107;
        --premium-success: #198754;
        --premium-info: #0dcaf0;
        --premium-glass: rgba(255, 255, 255, 0.9);
        --premium-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --font-main: 'Inter', sans-serif;
        --font-heading: 'Outfit', sans-serif;
    }

    body {
        font-family: var(--font-main);
        background-color: #f6f9fc;
    }

    .font-outfit { font-family: var(--font-heading); }

    /* Welcome Banner */
    .premium-welcome-banner {
        background: linear-gradient(135deg, #8b0000 0%, #dc3545 100%);
        border-radius: 20px;
        padding: 40px;
        position: relative;
        overflow: hidden;
        color: white;
        box-shadow: 0 15px 35px rgba(220, 53, 69, 0.2);
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
        font-size: 2.2rem;
        margin-bottom: 10px;
        letter-spacing: -0.5px;
    }

    .banner-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 600px;
        line-height: 1.5;
    }

    .visual-circle {
        position: absolute;
        top: -50px;
        right: -50px;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        z-index: -1;
    }

    .banner-icon {
        font-size: 6rem;
        opacity: 0.2;
        transform: rotate(15deg);
    }

    .banner-footer {
        margin-top: 30px;
        display: flex;
        gap: 25px;
        padding-top: 25px;
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        z-index: 2;
    }

    .footer-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px 15px;
        border-radius: 30px;
    }

    /* Glass Cards */
    .glass-card {
        background: var(--premium-glass);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: var(--premium-shadow);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }

    .stat-card {
        padding: 10px;
    }

    .stat-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }

    .stat-value {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 1.8rem;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-weight: 500;
        font-size: 0.95rem;
        margin-bottom: 15px;
    }

    /* Table Styling */
    .table-premium thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-family: var(--font-heading);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 20px 15px;
    }

    .table-premium tbody td {
        padding: 18px 15px;
        vertical-align: middle;
        border-color: #f1f4f8;
    }

    .file-icon-wrapper {
        width: 45px;
        height: 45px;
        background: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .card-header-premium {
        padding: 25px 30px;
        border-bottom: 1px solid #f1f4f8;
    }

    .btn-premium-action {
        padding: 8px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .btn-view {
        background-color: var(--premium-primary);
        color: white;
        border: none;
    }

    .btn-view:hover {
        background-color: #8b0000;
        color: white;
        transform: scale(1.05);
    }

    /* Badges */
    .badge {
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 600;
    }

    /* Animations */
    .pulse-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        animation: pulse-danger 2s infinite;
    }

    @keyframes pulse-danger {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }

    .animate__animated {
        animation-duration: 0.8s;
    }

    /* Alert Enhancements */
    .alert-premium-success {
        background: #d1e7dd;
        border: none;
        border-left: 5px solid #198754;
        color: #0f5132;
    }

    .alert-premium-danger {
        background: #f8d7da;
        border: none;
        border-left: 5px solid #dc3545;
        color: #842029;
    }

    .alert-icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
    $(document).ready(function() {
        // Initialize DataTables
        if ($('#tte-walikota-table').length) {
            $('#tte-walikota-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                order: [[2, 'asc']],
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center p-3"<"length-menu"l><"search-box"f>>rt<"d-flex justify-content-between align-items-center p-3"<"info"i><"pagination"p>>'
            });
        }

        // Live Clock
        setInterval(function() {
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                          now.getMinutes().toString().padStart(2, '0');
            $('#live-clock').text(timeStr);
        }, 60000);
    });
</script>