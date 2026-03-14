<div class="legalisasi-module pb-5">
    <!-- Premium Header Section -->
    <div class="header-premium-blue p-4 p-md-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0 0 2rem 2rem;">
        <div class="position-relative z-1">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-light mb-2">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-white-50">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('laporan') ?>" class="text-white-50">Laporan</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Riwayat TTE</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 fw-bold text-white mb-0 font-outfit">
                        <i class="fas fa-history me-2 opacity-75"></i>Riwayat TTE
                    </h1>
                    <p class="text-white-50 mt-2 mb-0">Log aktivitas penandatanganan elektronik secara menyeluruh.</p>
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="position-absolute top-0 end-0 p-5 mt-5 opacity-10">
            <i class="fas fa-history fa-10x text-white rotate-12"></i>
        </div>
    </div>

    <div class="container-fluid px-md-4">
        <!-- Navigation Tabs Premium -->
        <div class="glass-card mb-4 p-1">
            <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link py-3 fw-bold transition-all" href="<?= base_url('laporan') ?>">
                        <i class="fas fa-chart-pie me-2"></i>Laporan Statistik
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 fw-bold transition-all" href="<?= base_url('laporan/monitoring') ?>">
                        <i class="fas fa-chart-line me-2"></i>Monitoring Penomoran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 fw-bold active transition-all shadow-sm" href="<?= base_url('laporan/riwayat-tte') ?>">
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
                <form method="get" action="<?= base_url('legalisasi/history/tte') ?>" class="row g-3">
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
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Status</label>
                        <select name="status" class="form-select border-0 bg-light rounded-pill px-3">
                            <option value="">Semua Status</option>
                            <option value="SUCCESS" <?= ($status ?? '') == 'SUCCESS' ? 'selected' : '' ?>>Success</option>
                            <option value="FAILED" <?= ($status ?? '') == 'FAILED' ? 'selected' : '' ?>>Failed</option>
                            <option value="PENDING" <?= ($status ?? '') == 'PENDING' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Action</label>
                        <input type="text" name="jenis_aksi" class="form-control border-0 bg-light rounded-pill px-3" placeholder="TTE_WALIKOTA, dll" value="<?= esc($jenis_aksi ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">User</label>
                        <select name="user" class="form-select border-0 bg-light rounded-pill px-3">
                            <option value="">Semua User</option>
                            <?php foreach ($list_users ?? [] as $u): ?>
                                <option value="<?= $u['id_user'] ?>" <?= ($u['id_user'] == ($id_user ?? '')) ? 'selected' : '' ?>><?= esc($u['nama']) ?></option>
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
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-blue-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-blue text-blue">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-blue text-blue px-3 py-2">Total</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1"><?= number_format($stats['total'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Aktivitas TTE</p>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-green-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-green text-green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-green text-green px-3 py-2">Berhasil</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1 text-green"><?= number_format($stats['success'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Tanda Tangan Sukses</p>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-red-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-red text-red">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-red text-red px-3 py-2">Gagal</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1 text-red"><?= number_format($stats['failed'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Tanda Tangan Gagal</p>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-orange-premium shadow-hover transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-orange text-orange">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-orange text-orange px-3 py-2">Pending</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1 text-orange"><?= number_format($stats['pending'] ?? 0) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Menunggu Proses</p>
                </div>
            </div>
        </div>

        <!-- Main History Table -->
        <div class="glass-card mb-5">
            <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h5 class="fw-bold font-outfit mb-0"><i class="fas fa-table me-2 text-blue"></i>Daftar Aktivitas Penandatanganan</h5>
                <div id="exportButtons"></div>
            </div>
            <div class="p-4">
                <div class="table-responsive">
                    <table id="history-tte-table" class="table table-premium table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Dokumen / Ajuan</th>
                                <th>Penandatangan</th>
                                <th>Jenis Aksi</th>
                                <th>Status</th>
                                <th>Nomor</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tte_logs as $log): ?>
                            <tr>
                                <td class="small">
                                    <div class="fw-bold text-dark"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                                    <div class="tiny text-muted mt-1 opacity-75"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                </td>
                                <td>
                                    <?php if ($log['id_ajuan']): ?>
                                        <a href="<?= base_url('legalisasi/detail/' . $log['id_ajuan']) ?>" class="text-decoration-none transition-all hover-blue">
                                            <div class="fw-bold text-dark font-outfit mb-1 limit-text-1" style="max-width: 250px;"><?= esc($log['judul_peraturan'] ?? 'N/A') ?></div>
                                        </a>
                                        <div class="tiny text-muted uppercase tracking-1"><?= esc($log['nama_jenis'] ?? 'No Category') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Informasi tidak tersedia</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['nama_penandatangan']): ?>
                                        <div class="fw-bold text-dark small"><?= esc($log['nama_penandatangan']) ?></div>
                                        <div class="tiny text-muted"><?= esc($log['email_penandatangan'] ?? '') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $action = $log['action_final'] ?? $log['action'] ?? 'UNKNOWN';
                                    if (stripos($action, 'WALIKOTA') !== false) {
                                        echo '<span class="status-pill status-red"><i class="fas fa-crown me-1 opacity-75"></i>'.esc($action).'</span>';
                                    } elseif (stripos($action, 'SEKDA') !== false) {
                                        echo '<span class="status-pill status-orange"><i class="fas fa-stamp me-1 opacity-75"></i>'.esc($action).'</span>';
                                    } else {
                                        echo '<span class="status-pill status-blue"><i class="fas fa-cog me-1 opacity-75"></i>'.esc($action).'</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $stat = strtoupper($log['status_final'] ?? $log['status'] ?? 'UNKNOWN');
                                    if ($stat == 'SUCCESS') {
                                        echo '<span class="badge rounded-pill bg-soft-green text-green px-3 py-2 small fw-bold"><i class="fas fa-check-circle me-1 opacity-75"></i>Success</span>';
                                    } elseif ($stat == 'FAILED') {
                                        echo '<span class="badge rounded-pill bg-soft-red text-red px-3 py-2 small fw-bold"><i class="fas fa-times-circle me-1 opacity-75"></i>Failed</span>';
                                    } else {
                                        echo '<span class="badge rounded-pill bg-soft-orange text-orange px-3 py-2 small fw-bold"><i class="fas fa-clock me-1 opacity-75"></i>Pending</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php $nomorPeraturan = $log['document_number_final'] ?? $log['document_number'] ?? null; ?>
                                    <?php if ($nomorPeraturan): ?>
                                        <code class="bg-light text-dark px-2 py-1 rounded small fw-bold"><?= esc($nomorPeraturan) ?></code>
                                    <?php else: ?>
                                        <span class="tiny text-muted fst-italic">No output</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-action-circle btn-soft-blue" onclick='showDetailModal(<?= htmlspecialchars(json_encode($log), ENT_QUOTES, 'UTF-8') ?>)' title="Lihat Detail"><i class="fas fa-eye"></i></button>
                                        <?php if (($log['signed_path_final'] ?? $log['signed_path']) && $stat == 'SUCCESS'): ?>
                                            <a href="<?= base_url('legalisasi/download/' . $log['id_ajuan']) ?>" class="btn btn-action-circle btn-soft-green" title="Download"><i class="fas fa-download"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Premium -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-4 border-top gap-3">
                        <div class="text-muted small">
                            Menampilkan <span class="fw-bold text-dark"><?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?></span> 
                            ke <span class="fw-bold text-dark"><?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?></span> 
                            dari <span class="fw-bold text-dark"><?= number_format($pagination['total_records']) ?></span> rekaman
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-rounded mb-0">
                                <li class="page-item <?= !$pagination['has_prev'] ? 'disabled' : '' ?>">
                                    <a class="page-link shadow-none border-0 bg-light me-1" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">
                                        <i class="fas fa-chevron-left tiny"></i>
                                    </a>
                                </li>
                                <?php
                                $startPage = max(1, $pagination['current_page'] - 1);
                                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 1);
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link shadow-none border-0 <?= $i == $pagination['current_page'] ? 'bg-blue-premium text-white' : 'bg-light text-dark' ?> rounded ms-1" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                                    <a class="page-link shadow-none border-0 bg-light ms-1" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">
                                        <i class="fas fa-chevron-right tiny"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Premium Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 2rem;">
            <div class="modal-header header-premium-blue text-white border-0 py-4 px-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                <div class="position-relative z-1">
                    <h5 class="modal-title font-outfit fw-bold"><i class="fas fa-info-circle me-3 opacity-75"></i>Detail Log Aktivitas</h5>
                </div>
                <button type="button" class="btn-close btn-close-white z-1 ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="position-absolute end-0 top-0 p-4 opacity-10"><i class="fas fa-fingerprint fa-6x rotate-12"></i></div>
            </div>
            <div class="modal-body p-0" id="detailModalBody">
                <!-- Content injected via JS -->
            </div>
            <div class="modal-footer border-0 p-4 px-5 bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
                <div id="modalFooterActions"></div>
            </div>
        </div>
    </div>
</div>

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
    .limit-text-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

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
    .border-blue-premium { border-color: var(--blue-premium) !important; }

    .bg-soft-green { background: var(--green-soft); }
    .text-green { color: var(--green-premium); }
    .border-green-premium { border-color: var(--green-premium) !important; }

    .bg-soft-red { background: var(--red-soft); }
    .text-red { color: var(--red-premium); }
    .border-red-premium { border-color: var(--red-premium) !important; }

    .bg-soft-orange { background: var(--orange-soft); }
    .text-orange { color: var(--orange-premium); }
    .border-orange-premium { border-color: var(--orange-premium) !important; }

    .table-premium thead th {
        background: #f8fafc;
        border-bottom: 2px solid #f1f5f9;
        color: #64748b;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1.25rem 1rem;
    }

    .table-premium tbody td { padding: 1.25rem 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }

    .status-pill { padding: 0.4rem 0.75rem; border-radius: 2rem; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; border: 1px solid transparent; }
    .status-red { background: var(--red-soft); color: var(--red-premium); border-color: rgba(239, 68, 68, 0.2); }
    .status-orange { background: var(--orange-soft); color: var(--orange-premium); border-color: rgba(245, 158, 11, 0.2); }
    .status-blue { background: var(--blue-soft); color: var(--blue-premium); border-color: rgba(37, 99, 235, 0.2); }

    .btn-action-circle { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; border: none; font-size: 0.9rem; }
    .btn-soft-blue { background: var(--blue-soft); color: var(--blue-premium); }
    .btn-soft-blue:hover { background: var(--blue-premium); color: white; transform: rotate(15deg); }
    .btn-soft-green { background: var(--green-soft); color: var(--green-premium); }
    .btn-soft-green:hover { background: var(--green-premium); color: white; transform: rotate(15deg); }

    .hover-blue:hover div { color: var(--blue-premium) !important; }

    /* Modal details */
    .detail-item { padding: 1rem 0; border-bottom: 1px solid #f1f5f9; }
    .detail-item:last-child { border-bottom: none; }
    .detail-label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 0.3rem; }
    .detail-value { font-size: 1rem; color: #1e293b; font-weight: 500; }

    /* DataTable Button positioning */
    .dt-buttons { margin-bottom: 1rem; }

    /* Modal Backdrop Fix */
    .modal-backdrop { z-index: 1040 !important; }
    #detailModal { z-index: 1050 !important; }
    #detailModal .modal-content { z-index: 1051 !important; }
    
    /* Ensure modal buttons are interactive */
    #detailModal .btn, #detailModal a {
        position: relative;
        z-index: 1060 !important;
        pointer-events: auto !important;
    }
</style>

<script>
    $(document).ready(function() {
        const table = $('#history-tte-table').DataTable({
            responsive: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
            order: [], // Pagination handled by backend
            paging: false,
            searching: true,
            info: false,
            dom: 'frtip'
        });

        new $.fn.dataTable.Buttons(table, {
            buttons: [
                { extend: 'excel', text: '<i class="fas fa-file-excel me-2"></i>Excel', className: 'btn btn-soft-green btn-sm rounded-pill px-3 shadow-none' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf me-2"></i>PDF', className: 'btn btn-soft-red btn-sm rounded-pill px-3 shadow-none' },
                { extend: 'print', text: '<i class="fas fa-print me-2"></i>Cetak', className: 'btn btn-soft-blue btn-sm rounded-pill px-3 shadow-none' }
            ]
        }).container().appendTo($('#exportButtons'));
    });

    function showDetailModal(log) {
        const modalBody = document.getElementById('detailModalBody');
        const footerActions = document.getElementById('modalFooterActions');
        
        let html = '<div class="px-5 py-4">';
        html += '<div class="row g-0">';
        
        // Document Info Section
        html += '<div class="col-md-7 pe-md-4">';
        html += renderDetailItem('Nama Ajuan / Dokumen', log.judul_peraturan || 'N/A', 'fas fa-file-signature');
        html += renderDetailItem('Jenis Peraturan', log.nama_jenis || 'N/A', 'fas fa-tag');
        html += renderDetailItem('Instansi', log.nama_instansi || 'N/A', 'fas fa-building');
        html += renderDetailItem('Waktu Eksekusi', formatDateTime(log.created_at), 'fas fa-clock');
        html += '</div>';

        // Process Info Section
        html += '<div class="col-md-5 ps-md-4 border-start">';
        html += renderDetailItem('Penandatangan', log.nama_penandatangan || 'N/A', 'fas fa-user-check');
        html += renderDetailItem('Konfigurasi Aksi', log.action_final || log.action || 'N/A', 'fas fa-tools');
        
        const stat = (log.status_final || log.status || 'UNKNOWN').toUpperCase();
        const statLabel = stat === 'SUCCESS' ? '<span class="status-pill status-blue ms-2">BERHASIL</span>' : '<span class="status-pill status-red ms-2">GAGAL</span>';
        html += renderDetailItem('Status Akhir', statLabel, 'fas fa-shield-alt');
        
        html += renderDetailItem('Nomor Dokumen', log.document_number_final || log.document_number || '<span class="text-muted italic small">Belum ditetapkan</span>', 'fas fa-hashtag');
        html += '</div>';

        // Error log if any
        if (log.error_message) {
            html += '<div class="col-12 mt-4 pt-4 border-top">';
            html += '<div class="detail-label text-red">Laporan Kesalahan (Stack Analysis)</div>';
            html += '<div class="bg-red-soft p-4 rounded-4 text-red font-monospace small" style="border: 1px dashed rgba(239, 68, 68, 0.3); overflow-x: auto;">';
            html += escapeHtml(log.error_message);
            html += '</div>';
            html += '</div>';
        }

        html += '</div></div>';
        modalBody.innerHTML = html;

        // Footer buttons
        let actionsHtml = '';
        if ((log.signed_path_final || log.signed_path) && stat === 'SUCCESS') {
            actionsHtml = '<a href="<?= base_url('legalisasi/download') ?>/' + log.id_ajuan + '" class="btn btn-blue-premium rounded-pill px-4 fw-bold shadow-hover"><i class="fas fa-download me-2"></i>Download File</a>';
        }
        footerActions.innerHTML = actionsHtml;
        
        let modalInstance = bootstrap.Modal.getInstance(document.getElementById('detailModal'));
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(document.getElementById('detailModal'));
        }
        modalInstance.show();
    }

    function renderDetailItem(label, value, icon) {
        return `
        <div class="detail-item border-bottom-0">
            <div class="detail-label"><i class="${icon} me-2 text-blue-muted opacity-50"></i>${label}</div>
            <div class="detail-value">${value}</div>
        </div>`;
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString('id-ID', {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    }

    function escapeHtml(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }
</script>

