<!-- Modern Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">

<div class="container-fluid dashboard-penugasan">
    <!-- Header Page -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4 header-section">
        <div class="header-content">
            <h1 class="h3 mb-1 text-gray-800 fw-800">
                <i class="material-icons-outlined text-primary align-middle me-2">assignment</i><?= esc($title) ?>
            </h1>
            <p class="text-muted mb-0">Kelola distribusi beban kerja verifikator secara otomatis dan efisien</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('harmonisasi') ?>" class="text-decoration-none">Harmonisasi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard Penugasan</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-modern alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="material-icons me-3">check_circle</i>
                <div><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-modern alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="material-icons me-3">report_problem</i>
                <div><?= esc(session()->getFlashdata('error')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Verifier Load Cards -->
    <div class="row mb-5">
        <?php if (!empty($verifikator_stats)): ?>
            <?php 
                $gradients = [
                    'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)', // Indigo to Violet
                    'linear-gradient(135deg, #0891b2 0%, #0d9488 100%)', // Cyan to Teal
                    'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)', // Blue
                    'linear-gradient(135deg, #db2777 0%, #9333ea 100%)', // Pink to Purple
                    'linear-gradient(135deg, #ea580c 0%, #d97706 100%)', // Orange to Amber
                    'linear-gradient(135deg, #059669 0%, #10b981 100%)', // Emerald
                ];
                $i = 0;
            ?>
            <?php foreach (array_slice($verifikator_stats, 0, 6) as $stat): ?>
                <div class="col-xl-2 col-lg-4 col-md-6 mb-4">
                    <div class="verifier-card card border-0 shadow-sm overflow-hidden" style="background: <?= $gradients[$i % count($gradients)] ?>">
                        <div class="card-body p-4 text-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="avatar-circle">
                                    <span class="initials"><?= strtoupper(substr($stat['nama'], 0, 1)) ?></span>
                                </div>
                                <div class="badge-active">
                                    <span class="dot pulse"></span> Active
                                </div>
                            </div>
                            <div class="verifier-info">
                                <h6 class="text-white-50 mb-1 text-uppercase ls-1">Verifikator</h6>
                                <h5 class="fw-bold mb-3 text-truncate"><?= esc($stat['nama']) ?></h5>
                                <div class="workload-stats d-flex align-items-end">
                                    <div class="load-number h2 mb-0 fw-800 me-2"><?= esc($stat['jumlah']) ?></div>
                                    <div class="load-label text-white-50 mb-1">Tugas</div>
                                </div>
                            </div>
                            <!-- Background Decoration -->
                            <i class="material-icons decorator-icon">account_circle</i>
                        </div>
                    </div>
                </div>
                <?php $i++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Global Summary Section -->
    <div class="row mb-5">
        <div class="col-lg-6">
            <div class="summary-box card border-0 shadow-sm bg-premium-teal text-white overflow-hidden">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-shell me-4">
                        <i class="material-icons">fact_check</i>
                    </div>
                    <div class="content">
                        <h4 class="mb-1 fw-800"><?= isset($jumlah_validasi) ? esc($jumlah_validasi) : 0 ?> Tugas Validasi</h4>
                        <p class="mb-0 text-white-50">Draft yang telah disetor dan menunggu verifikasi mendalam</p>
                    </div>
                </div>
                <div class="glow-effect"></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="summary-box card border-0 shadow-sm bg-premium-indigo text-white overflow-hidden">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-shell me-4">
                        <i class="material-icons">grading</i>
                    </div>
                    <div class="content">
                        <h4 class="mb-1 fw-800"><?= isset($jumlah_finalisasi) ? esc($jumlah_finalisasi) : 0 ?> Tugas Finalisasi</h4>
                        <p class="mb-0 text-white-50">Tahap akhir sebelum dokumen disahkan dan dipublikasi</p>
                    </div>
                </div>
                <div class="glow-effect"></div>
            </div>
        </div>
    </div>


    <!-- Main Data Table -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <div class="header-title">
                <h5 class="fw-800 mb-1 text-dark">Daftar Menunggu Penugasan</h5>
                <span class="badge bg-soft-primary px-3 py-2 rounded-pill text-primary fw-bold">
                    <i class="material-icons fs-6 align-middle me-1">pending_actions</i> <?= count($ajuan_penugasan) ?> Ajuan Masuk
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($ajuan_penugasan)): ?>
                <div class="table-responsive">
                    <table id="data-tables" class="table table-striped table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Judul Rancangan</th>
                                <th width="12%">Jenis</th>
                                <th width="15%">Instansi Pemohon</th>
                                <th width="12%">Tgl. Diterima</th>
                                <th width="10%">Status</th>
                                <th width="16%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $status_colors = [
                                1 => 'bg-secondary text-white',
                                2 => 'bg-warning text-dark',
                                3 => 'bg-info text-white',
                                4 => 'bg-danger text-white',
                                5 => 'bg-primary text-white',
                                6 => 'bg-danger text-white',
                                7 => 'bg-success text-white',
                            ];

                            foreach ($ajuan_penugasan as $item):
                                $status_class = $status_colors[$item['id_status_ajuan']] ?? 'bg-secondary';
                                $ajuan_id = $item['id'] ?? '';
                            ?>
                                <tr>
                                    <td class="text-center font-monospace text-muted"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6 mb-1">
                                            <?= esc($item['judul_peraturan']) ?>
                                        </div>
                                        <?php if (!empty($item['nama_pemohon'])): ?>
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="material-icons fs-6 me-1">person_outline</i> <?= esc($item['nama_pemohon']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-primary text-primary border border-primary border-opacity-10 px-3 py-2 rounded-pill">
                                            <?= esc($item['nama_jenis']) ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary"><?= esc($item['nama_instansi']) ?></td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="fw-bold text-dark"><?= date('d/m/Y', strtotime(!empty($item['tanggal_pengajuan']) ? $item['tanggal_pengajuan'] : $item['created_at'])) ?></span>
                                            <span class="text-muted small"><?= date('H:i', strtotime(!empty($item['tanggal_pengajuan']) ? $item['tanggal_pengajuan'] : $item['created_at'])) ?> WIB</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= esc($status_class) ?> px-3 py-2 rounded-2 shadow-sm">
                                            <?= esc($item['nama_status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('penugasan/tugaskan/' . $ajuan_id) ?>"
                                            class="btn-modern-action d-inline-flex align-items-center text-decoration-none"
                                            title="Tugaskan Verifikator">
                                            <i class="material-icons fs-5 me-2">person_add_alt_1</i> Tugaskan
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 empty-state">
                    <div class="empty-icon-shell mb-4 mx-auto">
                        <i class="material-icons">task_alt</i>
                    </div>
                    <h4 class="text-dark fw-800">Semua Tugas Selesai!</h4>
                    <p class="text-muted mb-4">Tidak ada ajuan baru yang memerlukan penugasan verifikator saat ini.</p>
                    <a href="<?= base_url('harmonisasi') ?>" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="material-icons align-middle me-2">arrow_back</i> Kembali ke Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // DataTable initialization for penugasan dashboard
    $(document).ready(function() {
        if ($.fn.DataTable && $('#data-tables').length) {
            // Check if DataTable is already initialized
            if ($.fn.DataTable.isDataTable('#data-tables')) {
                console.log('Penugasan - DataTable already initialized, skipping...');
                return;
            }

            // Destroy existing instance if any (with error handling)
            try {
                if ($.fn.DataTable.isDataTable('#data-tables')) {
                    $('#data-tables').DataTable().destroy();
                }
            } catch (e) {
                console.warn('Error destroying existing DataTable:', e);
                // Remove any existing DataTable classes and data
                $('#data-tables').removeClass('dataTable').removeData();
            }

            $('#data-tables').DataTable({
                responsive: true,
                language: {
                    "processing": "Memproses...",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                    "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "search": "Cari:",
                    "loadingRecords": "Memuat...",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    },
                    "aria": {
                        "sortAscending": ": aktifkan untuk mengurutkan kolom ke atas",
                        "sortDescending": ": aktifkan untuk mengurutkan kolom ke bawah"
                    }
                },
                order: [
                    [4, 'desc']
                ], // Sort by date column
                columnDefs: [{
                        orderable: false,
                        targets: [6]
                    }, // Disable sorting for action column
                    {
                        className: 'text-center',
                        targets: [0, 5, 6]
                    } // Center align certain columns
                ],
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy me-1"></i>Copy',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-1"></i>Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i>Print',
                        className: 'btn btn-info btn-sm'
                    }
                ],
                drawCallback: function(settings) {
                    // Update row numbers after sorting/filtering/pagination
                    var api = this.api();
                    var pageInfo = api.page.info();

                    api.rows({
                        page: 'current'
                    }).nodes().each(function(cell, i) {
                        if (cell.cells && cell.cells[0]) {
                            cell.cells[0].innerHTML = pageInfo.start + i + 1;
                        }
                    });
                }
            });
        }
    });
</script>

<style>
    /* Premium Dashboard Styles */
    :root {
        --primary-font: 'Inter', system-ui, -apple-system, sans-serif;
        --heading-font: 'Outfit', sans-serif;
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    .dashboard-penugasan {
        font-family: var(--primary-font);
        background-color: #f8fafc;
        min-height: 100vh;
        padding-top: 2rem;
    }

    .fw-800 { font-weight: 800; }
    .ls-1 { letter-spacing: 1px; }

    .header-section h1 {
        font-family: var(--heading-font);
        color: #1e293b;
    }

    /* Verifier Cards */
    .verifier-card {
        border-radius: 1.5rem !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
    }

    .verifier-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }

    .avatar-circle {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .initials {
        font-family: var(--heading-font);
        font-weight: 700;
        font-size: 1.2rem;
    }

    .badge-active {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .dot {
        width: 6px;
        height: 6px;
        background: #4ade80;
        border-radius: 50%;
        margin-right: 6px;
    }

    .pulse {
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); }
        100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
    }

    .decorator-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 80px !important;
        opacity: 0.1;
        transform: rotate(-15deg);
    }

    /* Summary Boxes */
    .summary-box {
        border-radius: 1.25rem !important;
        min-height: 120px;
        position: relative;
    }

    .bg-premium-teal { background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%); }
    .bg-premium-indigo { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); }

    .icon-shell {
        width: 64px;
        height: 64px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }

    .icon-shell i { font-size: 32px; }

    .glow-effect {
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }

    /* Table Styling */
    .table thead th {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1.25rem 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    .bg-soft-primary { background-color: #eef2ff !important; }

    /* Custom Badges */
    .badge {
        font-weight: 600;
        letter-spacing: 0.3px;
        box-shadow: none !important;
    }

    .btn-modern-action {
        background: #3b82f6;
        color: white;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-modern-action:hover {
        background: #2563eb;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        color: white;
    }

    /* DataTable Overrides */
    .dt-buttons { margin-bottom: 1.5rem !important; margin-left: 1.5rem !important; }
    .dataTables_filter { padding: 1.5rem !important; }
    .dataTables_info { padding: 1.5rem !important; color: #64748b; font-weight: 500; }
    .dataTables_paginate { padding: 1.5rem !important; }

    /* Alert Styling */
    .alert-modern {
        border-radius: 12px;
        padding: 1rem 1.5rem;
        font-weight: 500;
    }

    /* Empty State */
    .empty-icon-shell {
        width: 100px;
        height: 100px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
    }

    .empty-icon-shell i { font-size: 48px; }

    .empty-state h4 { font-family: var(--heading-font); }

    /* Animations */
    .dashboard-penugasan > div {
        animation: fadeIn 0.6s ease-out forwards;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1200px) {
        .col-xl-2 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
    }

    @media (max-width: 768px) {
        .col-xl-2 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        .header-section {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .header-section nav { margin-top: 1rem; }
    }
</style>
