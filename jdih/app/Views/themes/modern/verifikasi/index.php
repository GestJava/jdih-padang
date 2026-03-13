<!-- Modern Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">

<div class="verifikasi-premium-shell container-fluid">
    <!-- Premium Welcome Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="welcome-banner card border-0 overflow-hidden shadow-premium">
                <div class="card-body p-0">
                    <div class="row g-0 align-items-center">
                        <div class="col-lg-8 p-5 welcome-text">
                            <h6 class="text-uppercase ls-2 text-white-50 fw-bold mb-2">Portal Verifikasi</h6>
                            <h1 class="display-5 fw-800 text-white mb-3"><?= esc($title) ?></h1>
                            <p class="lead text-white-50 mb-4">Pastikan keabsahan dan kelengkapan dokumen hukum sebelum melanjutkan ke tahap validasi.</p>
                            <div class="d-flex gap-2">
                                <span class="badge glass-badge px-3 py-2 rounded-pill border border-white border-opacity-10 text-white">
                                    <i class="material-icons align-middle fs-6 me-1">assignment_turned_in</i> <?= count($ajuan) ?> Ajuan Menunggu
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-block text-center p-4">
                            <div class="banner-visual-shell">
                                <i class="material-icons banner-icon">verified_user</i>
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

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-modern alert-success slide-in-top mb-4">
            <div class="d-flex align-items-center">
                <i class="material-icons me-2">check_circle</i>
                <div><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-modern alert-danger slide-in-top mb-4">
            <div class="d-flex align-items-center">
                <i class="material-icons me-2">report_problem</i>
                <div><?= esc(session()->getFlashdata('error')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Verifikator Workload (Horizontal Glass Scroll) -->
    <?php if (!empty($verifikator_stats)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h5 class="section-title mb-4 fw-800 text-dark">
                <i class="material-icons align-middle me-2 text-primary">groups</i> Beban Kerja Verifikator
            </h5>
            <div class="row g-4">
                <?php 
                $gradients = [
                    'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)',
                    'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                    'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                    'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                    'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
                    'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)'
                ];
                foreach (array_slice($verifikator_stats, 0, 6) as $index => $stat): 
                    $gradient = $gradients[$index % count($gradients)];
                ?>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="stat-card card border-0 shadow-sm rounded-4 overflow-hidden h-100" style="background: <?= $gradient ?>;">
                        <div class="card-body p-3 text-center text-white">
                            <div class="avatar-circle-sm mx-auto mb-2 text-dark bg-white fw-bold">
                                <?= strtoupper(substr($stat['nama'], 0, 1)) ?>
                            </div>
                            <h6 class="text-white-50 small fw-bold text-uppercase mb-1"><?= esc($stat['nama']) ?></h6>
                            <h4 class="fw-800 mb-0"><?= esc($stat['jumlah']) ?> <small class="fw-normal">Tugas</small></h4>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Year Statistics (Glassmorphism) -->
    <?php if (!empty($year_stats)): ?>
        <div class="row mb-5 g-4">
            <div class="col-12">
                <h5 class="section-title mb-4 fw-800 text-dark">
                    <i class="material-icons align-middle me-2 text-success">analytics</i> Statistik Tahun <?= $current_year ?>
                    <small class="text-muted fw-normal fs-6 ms-2">(<?= $data_scope ?? 'Data' ?>)</small>
                </h5>
            </div>

            <!-- Total Ajuan Tahun Ini -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card card border-0 shadow-sm glass-bg-blue h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="stat-icon-premium">
                                <i class="material-icons">description</i>
                            </div>
                            <div class="glass-badge px-3 py-1 rounded-pill small fw-bold text-white">Total</div>
                        </div>
                        <div class="stat-content">
                            <h2 class="display-6 fw-800 text-white mb-1"><?= number_format($year_stats['total_ajuan_tahun_ini']) ?></h2>
                            <h6 class="text-white-50 text-uppercase ls-1 fw-bold small">Total Registrasi</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Selesai Tahun Ini -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card card border-0 shadow-sm glass-bg-emerald h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="stat-icon-premium">
                                <i class="material-icons">verified</i>
                            </div>
                            <div class="glass-badge px-3 py-1 rounded-pill small fw-bold text-white">
                                <?php
                                $persentase_selesai = $year_stats['total_ajuan_tahun_ini'] > 0
                                    ? round(($year_stats['total_selesai_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                                    : 0;
                                echo $persentase_selesai . '%';
                                ?>
                            </div>
                        </div>
                        <div class="stat-content">
                            <h2 class="display-6 fw-800 text-white mb-1"><?= number_format($year_stats['total_selesai_tahun_ini']) ?></h2>
                            <h6 class="text-white-50 text-uppercase ls-1 fw-bold small">Verifikasi Selesai</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Ditolak Tahun Ini -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card card border-0 shadow-sm glass-bg-rose h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="stat-icon-premium">
                                <i class="material-icons">cancel</i>
                            </div>
                            <div class="glass-badge px-3 py-1 rounded-pill small fw-bold text-white">
                                <?php
                                $persentase_ditolak = $year_stats['total_ajuan_tahun_ini'] > 0
                                    ? round(($year_stats['total_ditolak_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                                    : 0;
                                echo $persentase_ditolak . '%';
                                ?>
                            </div>
                        </div>
                        <div class="stat-content">
                            <h2 class="display-6 fw-800 text-white mb-1"><?= number_format($year_stats['total_ditolak_tahun_ini']) ?></h2>
                            <h6 class="text-white-50 text-uppercase ls-1 fw-bold small">Kembali / Ditolak</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Proses Tahun Ini -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card card border-0 shadow-sm glass-bg-amber h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="stat-icon-premium">
                                <i class="material-icons">schedule</i>
                            </div>
                            <div class="glass-badge px-3 py-1 rounded-pill small fw-bold text-white">
                                <?php
                                $persentase_proses = $year_stats['total_ajuan_tahun_ini'] > 0
                                    ? round(($year_stats['total_proses_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                                    : 0;
                                echo $persentase_proses . '%';
                                ?>
                            </div>
                        </div>
                        <div class="stat-content">
                            <h2 class="display-6 fw-800 text-white mb-1"><?= number_format($year_stats['total_proses_tahun_ini']) ?></h2>
                            <h6 class="text-white-50 text-uppercase ls-1 fw-bold small">Dalam Antrean</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Table (Premium) -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-premium rounded-4 overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-800 text-dark mb-1">Ajuan Menunggu Verifikasi</h5>
                        <p class="text-muted small mb-0">Total <?= count($ajuan) ?> dokumen perlu diperiksa hari ini</p>
                    </div>
                    <div class="table-actions">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                            Tugas: <?= $data_scope ?? 'Aktual' ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="data-tables" class="table table-premium mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Dokumen Hukum</th>
                                    <th>Informasi</th>
                                    <th>Waktu Diterima</th>
                                    <th>Status Antrean</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ajuan)): ?>
                                    <?php foreach ($ajuan as $index => $item): 
                                        $ajuan_id = $item['id'] ?? '';
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                        <td>
                                            <div class="doc-title-wrapper">
                                                <div class="doc-title fw-800 text-dark"><?= esc($item['judul_peraturan'] ?? 'N/A') ?></div>
                                                <div class="doc-meta text-muted small mt-1">
                                                    <span class="me-2"><i class="material-icons align-middle fs-6 me-1">apartment</i> <?= esc($item['nama_instansi'] ?? 'N/A') ?></span>
                                                    <span><i class="material-icons align-middle fs-6 me-1">person</i> <?= esc($item['nama_pemohon'] ?? 'N/A') ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-indigo-soft text-indigo px-3 py-2 rounded-pill small fw-bold">
                                                <?= esc($item['nama_jenis'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-wrapper text-muted small">
                                                <i class="material-icons align-middle fs-6 me-1 text-primary">event</i>
                                                <?php
                                                $tanggal = !empty($item['tanggal_pengajuan']) && $item['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                                    ? $item['tanggal_pengajuan']
                                                    : $item['created_at'];
                                                echo date('d M Y, H:i', strtotime($tanggal));
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= esc($status_colors[$item['id_status_ajuan']] ?? 'bg-secondary') ?> status-badge-modern">
                                                <span class="pulse-status"></span>
                                                <?= esc($item['nama_status'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($ajuan_id): ?>
                                                <a href="<?= base_url('verifikasi/proses/' . esc($ajuan_id)) ?>" 
                                                   class="btn btn-modern-action" 
                                                   title="Mulai Verifikasi">
                                                    <i class="material-icons">play_circle</i>
                                                    <span>Proses</span>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="material-icons display-1 text-muted opacity-25">description</i>
                                                <h5 class="text-muted mt-3">Tidak ada ajuan yang perlu diverifikasi.</h5>
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
</div>
</div>

<script>


<style>
    /* Premium Design System for Verifikasi Page */
    :root {
        --primary-font: 'Inter', sans-serif;
        --heading-font: 'Outfit', sans-serif;
        --shadow-premium: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .verifikasi-premium-shell {
        font-family: var(--primary-font);
        background-color: #f8fafc;
        min-height: 100vh;
        padding-bottom: 3rem;
    }

    .fw-800 { font-weight: 800; }
    .ls-2 { letter-spacing: 2px; }

    /* Welcome Banner */
    .welcome-banner { border-radius: 2rem !important; background: #1e293b; position: relative; }
    .banner-gradient { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, #4f46e5 0%, #1e1b4b 100%); z-index: 1; }
    .welcome-text { position: relative; z-index: 5; }
    .banner-visual-shell { position: relative; z-index: 5; height: 160px; display: flex; align-items: center; justify-content: center; }
    .banner-icon { font-size: 90px !important; color: rgba(255, 255, 255, 0.15); animation: float 4s ease-in-out infinite; }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

    /* Orbit animations */
    .orbit-1, .orbit-2 { position: absolute; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 50%; }
    .orbit-1 { width: 120px; height: 120px; animation: rotate 20s linear infinite; }
    .orbit-2 { width: 180px; height: 180px; animation: rotate 30s linear infinite reverse; }
    @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Glassmorphism Stats */
    .stat-card { border-radius: 1.5rem !important; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important; }
    .glass-bg-blue { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
    .glass-bg-emerald { background: linear-gradient(135deg, #065f46 0%, #10b981 100%); }
    .glass-bg-rose { background: linear-gradient(135deg, #9f1239 0%, #f43f5e 100%); }
    .glass-bg-amber { background: linear-gradient(135deg, #92400e 0%, #f59e0b 100%); }

    .stat-icon-premium { width: 45px; height: 45px; background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .stat-icon-premium i { font-size: 24px !important; color: white !important; }
    .glass-badge { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(4px); }

    /* Avatar UI */
    .avatar-circle-sm { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

    /* Table & Card UI */
    .shadow-premium { box-shadow: var(--shadow-premium) !important; }
    .section-title { font-family: var(--heading-font); }
    
    .table-premium thead th { background: #f8fafc; color: #64748b; font-family: var(--heading-font); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-size: 0.75rem; border: none; padding: 1.25rem 1rem; }
    .table-premium tbody tr { transition: all 0.2s ease; }
    .table-premium tbody tr:hover { background-color: #f1f5f9; }
    .table-premium td { vertical-align: middle; border-bottom: 1px solid #f1f5f9; padding: 1.25rem 1rem; }

    .doc-title { font-size: 1rem; line-height: 1.4; color: #1e293b; }
    .bg-indigo-soft { background: #eef2ff; color: #4f46e5; }
    .text-indigo { color: #4f46e5; }
    .text-emerald { color: #10b981; }
    .text-rose { color: #f43f5e; }
    .bg-emerald-soft { background: rgba(16, 185, 129, 0.1); }
    .bg-rose-soft { background: rgba(244, 63, 94, 0.1); }
    
    .status-badge-modern { padding: 8px 16px; border-radius: 30px; display: inline-flex; align-items: center; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .pulse-status { width: 8px; height: 8px; border-radius: 50%; background: currentColor; margin-right: 8px; animation: pulse 2s infinite; opacity: 0.6; }
    @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255,255,255,0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(255,255,255,0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255,255,255,0); } }

    .btn-modern-action { background: #4f46e5; color: white; border-radius: 12px; padding: 10px 20px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; border: none; transition: all 0.3s ease; }
    .btn-modern-action:hover { background: #3730a3; transform: scale(1.05); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }

    /* Animations */
    .slide-in-top { animation: slideInTop 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }
    @keyframes slideInTop { 0% { transform: translateY(-20px); opacity: 0; } 100% { transform: translateY(0); opacity: 1; } }

    /* DataTables Overrides */
    .dt-buttons { margin: 1.5rem; }
    .dt-buttons .btn { border-radius: 10px; font-weight: 600; font-size: 0.8rem; margin-right: 8px; padding: 8px 16px; border: none !important; }
    .dataTables_filter { margin: 1.5rem; }
    .dataTables_filter input { border-radius: 10px; border: 1px solid #e2e8f0; padding: 8px 16px; font-size: 0.9rem; }
</style>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable && $('#data-tables').length) {
            var table = $('#data-tables').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    emptyTable: 'Tidak ada dokumen dalam antrean verifikasi saat ini.'
                },
                order: [[3, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [5] },
                    { className: "text-center", targets: [0, 4, 5] }
                ],
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center"Bf>rt<"p-4 d-flex justify-content-between align-items-center"ip>',
                buttons: [
                    { extend: 'excel', text: '<i class="material-icons align-middle fs-6 me-1">file_download</i> Excel', className: 'btn bg-emerald-soft text-emerald' },
                    { extend: 'pdf', text: '<i class="material-icons align-middle fs-6 me-1">picture_as_pdf</i> PDF', className: 'btn bg-rose-soft text-rose' },
                    { extend: 'print', text: '<i class="material-icons align-middle fs-6 me-1">print</i> Print', className: 'btn bg-indigo-soft text-indigo' }
                ]
            });
        }
    });
</script>