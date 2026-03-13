<!-- Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<div class="container-fluid py-4">
    <!-- Premium Welcome Banner -->
    <div class="welcome-banner mb-5">
        <div class="banner-content">
            <div class="banner-text">
                <div class="badge-premium mb-2">
                    <span class="material-icons-round">gavel</span>
                    <span>Portal Finalisasi JDIH</span>
                </div>
                <h1 class="display-5 fw-800 text-white mb-2"><?= esc($title) ?></h1>
                <p class="lead text-white-50 mb-0">Tahap akhir tinjauan dan pengesahan dokumen hukum sebelum pengarsipan resmi.</p>
            </div>
            <div class="banner-visual">
                <div class="visual-circle"></div>
                <div class="visual-icons">
                    <span class="material-icons-round">description</span>
                    <span class="material-icons-round">draw</span>
                    <span class="material-icons-round">verified</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-premium alert-success mb-4 animate__animated animate__fadeInUp">
            <div class="alert-icon">
                <span class="material-icons-round">check_circle</span>
            </div>
            <div class="alert-content">
                <div class="alert-title">Berhasil!</div>
                <div class="alert-message"><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Finalisator Workload Cards (Glassmorphism) -->
    <div class="section-header mb-3">
        <h5 class="fw-700 text-dark-blue d-flex align-items-center">
            <span class="material-icons-round text-primary me-2">group</span>
            Beban Kerja Finalisator
        </h5>
    </div>
    
    <div class="finalisator-grid-scroll mb-5">
        <div class="finalisator-row">
            <?php if (!empty($finalisator_stats)): ?>
                <?php foreach ($finalisator_stats as $stat): ?>
                    <div class="finalisator-col">
                        <div class="glass-card stat-card-premium">
                            <div class="card-glow"></div>
                            <div class="stat-icon-wrapper bg-gradient-indigo">
                                <span class="material-icons-round">history_edu</span>
                            </div>
                            <div class="stat-content">
                                <span class="stat-label"><?= esc($stat['nama']) ?></span>
                                <h3 class="stat-value"><?= esc($stat['jumlah']) ?></h3>
                                <span class="stat-unit">Dokumen</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Year Statistics Section -->
    <?php if (!empty($year_stats)): ?>
        <div class="section-header mb-4">
            <h5 class="fw-700 text-dark-blue d-flex align-items-center">
                <span class="material-icons-round text-success me-2">query_stats</span>
                Statistik Validasi Tahun <?= $current_year ?>
                <span class="badge badge-soft-primary ms-3">Sistem JDIH</span>
            </h5>
        </div>

        <div class="row mb-5">
            <!-- Total Ajuan -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card h-100 p-4 border-0 shadow-sm relative overflow-hidden group">
                    <div class="card-accent bg-primary"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-shape bg-soft-primary rounded-circle">
                            <span class="material-icons-round text-primary">folder_shared</span>
                        </div>
                        <span class="badge badge-soft-primary">Total</span>
                    </div>
                    <h3 class="fw-800 mb-1 text-dark-blue"><?= number_format($year_stats['total_ajuan_tahun_ini']) ?></h3>
                    <p class="text-muted small mb-0">Total Ajuan Masuk</p>
                    <div class="progress mt-3 progress-thin">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>
            </div>

            <!-- Selesai (Paraf) -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card h-100 p-4 border-0 shadow-sm relative overflow-hidden group">
                    <div class="card-accent bg-success"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-shape bg-soft-success rounded-circle">
                            <span class="material-icons-round text-success">task_alt</span>
                        </div>
                        <span class="badge badge-soft-success">Selesai</span>
                    </div>
                    <h3 class="fw-800 mb-1 text-dark-blue"><?= number_format($year_stats['total_selesai_tahun_ini']) ?></h3>
                    <p class="text-muted small mb-0">
                        <?php
                        $persentase_selesai = $year_stats['total_ajuan_tahun_ini'] > 0
                            ? round(($year_stats['total_selesai_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                            : 0;
                        echo $persentase_selesai . '% dari total';
                        ?>
                    </p>
                    <div class="progress mt-3 progress-thin">
                        <div class="progress-bar bg-success" style="width: <?= $persentase_selesai ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Ditolak -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card h-100 p-4 border-0 shadow-sm relative overflow-hidden group">
                    <div class="card-accent bg-danger"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-shape bg-soft-danger rounded-circle">
                            <span class="material-icons-round text-danger">cancel_presentation</span>
                        </div>
                        <span class="badge badge-soft-danger">Ditolak</span>
                    </div>
                    <h3 class="fw-800 mb-1 text-dark-blue"><?= number_format($year_stats['total_ditolak_tahun_ini']) ?></h3>
                    <p class="text-muted small mb-0">
                        <?php
                        $persentase_ditolak = $year_stats['total_ajuan_tahun_ini'] > 0
                            ? round(($year_stats['total_ditolak_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                            : 0;
                        echo $persentase_ditolak . '% dari total';
                        ?>
                    </p>
                    <div class="progress mt-3 progress-thin">
                        <div class="progress-bar bg-danger" style="width: <?= $persentase_ditolak ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Antrean/Proses -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card h-100 p-4 border-0 shadow-sm relative overflow-hidden group">
                    <div class="card-accent bg-warning"></div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-shape bg-soft-warning rounded-circle">
                            <span class="material-icons-round text-warning">hourglass_top</span>
                        </div>
                        <span class="badge badge-soft-warning">Antrean</span>
                    </div>
                    <h3 class="fw-800 mb-1 text-dark-blue"><?= number_format($year_stats['total_proses_tahun_ini']) ?></h3>
                    <p class="text-muted small mb-0">
                        <?php
                        $persentase_proses = $year_stats['total_ajuan_tahun_ini'] > 0
                            ? round(($year_stats['total_proses_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                            : 0;
                        echo $persentase_proses . '% dari total';
                        ?>
                    </p>
                    <div class="progress mt-3 progress-thin">
                        <div class="progress-bar bg-warning" style="width: <?= $persentase_proses ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main List Section -->
    <div class="glass-card border-0 shadow-lg overflow-hidden animate__animated animate__fadeIn">
        <div class="card-header-premium d-flex justify-content-between align-items-center p-4">
            <div class="d-flex align-items-center">
                <div class="header-icon bg-gradient-indigo me-3">
                    <span class="material-icons-round">edit_document</span>
                </div>
                <div>
                    <h5 class="fw-800 mb-0 text-dark-blue">Daftar Antrean Finalisasi</h5>
                    <small class="text-muted">Kelola proses finalisasi dokumen hukum</small>
                </div>
            </div>
            <div class="header-action">
                <span class="badge bg-indigo rounded-pill px-3 py-2">
                    <span class="me-1"><?= count($ajuan_list) ?></span> Ajuan Aktif
                </span>
            </div>
        </div>
        
        <div class="card-body-premium p-4">
            <div class="table-responsive">
                <table id="data-tables" class="table table-premium w-100">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Informasi Dokumen</th>
                            <th>Jenis</th>
                            <th>Jadwal Masuk</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $status_colors = [
                            1 => ['primary', 'assignment'],
                            2 => ['warning', 'access_time'],
                            3 => ['info', 'find_in_page'],
                            4 => ['danger', 'report_problem'],
                            5 => ['primary', 'rate_review'],
                            6 => ['info', 'fact_check'],
                            7 => ['success', 'verified'],
                            8 => ['dark', 'inventory_2'],
                            9 => ['dark', 'history'],
                            10 => ['warning', 'replay'],
                            11 => ['danger', 'highlight_off'],
                        ];

                        foreach ($ajuan_list as $item):
                            $status_id = $item['id_status_ajuan'] ?? 1;
                            $status_cfg = $status_colors[$status_id] ?? ['secondary', 'help_outline'];
                            $tanggal_tampil = !empty($item['tanggal_pengajuan']) && $item['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                ? $item['tanggal_pengajuan']
                                : $item['created_at'];
                        ?>
                            <tr>
                                <td class="text-center align-middle">
                                    <span class="text-muted fw-600"><?= $no++ ?></span>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <div class="fw-800 text-dark-blue mb-1"><?= esc($item['judul_peraturan']) ?></div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-flex align-items-center text-muted small">
                                                <span class="material-icons-round font-size-14 me-1">business</span>
                                                <?= esc($item['nama_instansi']) ?>
                                            </span>
                                            <span class="text-muted">•</span>
                                            <span class="d-flex align-items-center text-muted small">
                                                <span class="material-icons-round font-size-14 me-1">person</span>
                                                <?= esc($item['nama_pemohon']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="badge-soft-info px-3 py-2 rounded-pill fw-700">
                                        <?= esc($item['nama_jenis']) ?>
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <span class="fw-700 text-dark-blue"><?= date('d M Y', strtotime($tanggal_tampil)) ?></span>
                                        <span class="text-muted small"><?= date('H:i', strtotime($tanggal_tampil)) ?> WIB</span>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="status-badge status-<?= $status_cfg[0] ?>">
                                        <span class="material-icons-round"><?= $status_cfg[1] ?></span>
                                        <?= esc($item['nama_status']) ?>
                                        <?php if ($status_cfg[0] == 'warning'): ?>
                                            <span class="pulse"></span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="<?= base_url('finalisasi/proses/' . esc($item['id'])) ?>" class="btn-action">
                                        <span class="material-icons-round">edit_note</span>
                                        Finalisasi
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Design System for Finalisasi Page */
    :root {
        --primary-font: 'Inter', sans-serif;
        --heading-font: 'Outfit', sans-serif;
        --primary-indigo: #4f46e5;
        --dark-blue: #0f172a;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --premium-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }

    body {
        font-family: var(--primary-font);
        background-color: #f1f5f9;
        color: #334155;
    }

    .fw-700 { font-weight: 700; }
    .fw-800 { font-weight: 800; }
    .text-dark-blue { color: var(--dark-blue); }

    /* Welcome Banner */
    .welcome-banner {
        background: var(--premium-gradient);
        border-radius: 24px;
        padding: 60px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
    }

    .banner-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .badge-premium {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 16px;
        border-radius: 100px;
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
        backdrop-filter: blur(8px);
    }

    .badge-premium .material-icons-round {
        font-size: 1.1rem;
        margin-right: 8px;
    }

    .banner-visual {
        position: relative;
        width: 150px;
        height: 150px;
    }

    .visual-circle {
        width: 150px;
        height: 150px;
        border: 4px dashed rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        animation: rotate 20s linear infinite;
    }

    .visual-icons {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .visual-icons .material-icons-round {
        font-size: 3rem;
        color: white;
        position: absolute;
        animation: float 4s ease-in-out infinite;
    }

    .visual-icons .material-icons-round:nth-child(1) { top: 10%; animation-delay: 0s; }
    .visual-icons .material-icons-round:nth-child(2) { bottom: 20%; left: 10%; animation-delay: 1s; }
    .visual-icons .material-icons-round:nth-child(3) { bottom: 10%; right: 10%; animation-delay: 2s; }

    /* Finalisator Scrollable Grid */
    .finalisator-grid-scroll {
        overflow-x: auto;
        padding: 10px 0 20px 0;
        scrollbar-width: none;
    }

    .finalisator-grid-scroll::-webkit-scrollbar { display: none; }

    .finalisator-row {
        display: flex;
        gap: 20px;
        width: max-content;
    }

    .finalisator-col {
        width: 260px;
    }

    /* Glass Cards */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 20px;
        position: relative;
    }

    .stat-card-premium {
        padding: 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .stat-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
    }

    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .stat-icon-wrapper .material-icons-round {
        color: white;
        font-size: 1.5rem;
    }

    .bg-gradient-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        display: block;
        margin-bottom: 4px;
    }

    .stat-value {
        font-family: var(--heading-font);
        font-weight: 800;
        font-size: 1.75rem;
        margin: 0;
        color: var(--dark-blue);
    }

    .stat-unit {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    /* Accents & Progress Bars */
    .card-accent {
        position: absolute;
        top: 0;
        left: 0;
        height: 4px;
        width: 100%;
    }

    .icon-shape {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .progress-thin { height: 6px; border-radius: 100px; background-color: #f1f5f9; }

    /* Tables */
    .table-premium { border-collapse: separate; border-spacing: 0 12px; margin-top: -12px; }
    .table-premium thead th {
        background: #f8fafc;
        border: none;
        padding: 16px;
        font-size: 0.75rem;
        text-uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: 700;
    }

    .table-premium tbody tr {
        background: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: all 0.2s;
    }

    .table-premium tbody tr:hover {
        transform: scale(1.005);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        z-index: 2;
    }

    .table-premium td {
        padding: 20px 16px;
        border: none;
        background: transparent;
    }

    .table-premium td:first-child { border-radius: 12px 0 0 12px; }
    .table-premium td:last-child { border-radius: 0 12px 12px 0; }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 800;
        gap: 6px;
        position: relative;
    }

    .status-badge .material-icons-round { font-size: 14px; }

    .status-primary { background: #eef2ff; color: #4f46e5; }
    .status-warning { background: #fffbeb; color: #d97706; }
    .status-danger { background: #fef2f2; color: #dc2626; }
    .status-success { background: #f0fdf4; color: #16a34a; }
    .status-info { background: #f0f9ff; color: #0284c7; }

    /* Action Button */
    .btn-action {
        display: inline-flex;
        align-items: center;
        background: var(--dark-blue);
        color: white !important;
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
        gap: 8px;
    }

    .btn-action:hover {
        background: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 23, 42, 0.2);
    }

    .btn-action .material-icons-round { font-size: 18px; }

    .badge-soft-primary { background: #e0e7ff; color: #4338ca; }
    .badge-soft-info { background: #e0f2fe; color: #0369a1; }

    .font-size-14 { font-size: 14px; }

    /* Animations */
    @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    .pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 100px;
        background: inherit;
        top: 0;
        left: 0;
        z-index: -1;
        animation: pulse-anim 2s infinite;
    }

    @keyframes pulse-anim {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(1.3); opacity: 0; }
    }

    /* DataTable Overrides */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        border: none !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--premium-gradient) !important;
        color: white !important;
        font-weight: 700;
    }
</style>

<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#data-tables')) {
        $('#data-tables').DataTable().destroy();
    }

    $('#data-tables').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: 25,
        dom: '<"d-flex justify-content-between align-items-center mb-4"Bf>rt<"d-flex justify-content-between align-items-center mt-4"ip>',
        buttons: [
            {
                extend: 'excel',
                text: '<span class="material-icons-round me-1" style="font-size:16px">file_download</span> Export Excel',
                className: 'btn btn-soft-success btn-sm border-0 px-3 fw-700'
            },
            {
                extend: 'pdf',
                text: '<span class="material-icons-round me-1" style="font-size:16px">picture_as_pdf</span> PDF',
                className: 'btn btn-soft-danger btn-sm border-0 px-3 fw-700'
            }
        ],
        drawCallback: function() {
            $('.paginate_button').addClass('btn btn-sm mx-1');
        }
    });

    // Custom CSS for export buttons to match theme
    $('.btn-soft-success').css({
        'background-color': '#dcfce7',
        'color': '#166534'
    });
    $('.btn-soft-danger').css({
        'background-color': '#fee2e2',
        'color': '#991b1b'
    });
});
</script>