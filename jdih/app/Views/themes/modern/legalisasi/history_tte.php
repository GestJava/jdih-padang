<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history text-primary me-2"></i><?= esc($title) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('laporan') ?>">Laporan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Riwayat TTE</li>
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
                        <a class="nav-link" href="<?= base_url('laporan') ?>">
                            <i class="fas fa-chart-bar me-2"></i>Laporan Statistik
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="<?= base_url('laporan/monitoring') ?>">
                            <i class="fas fa-chart-line me-2"></i>Monitoring Penomoran
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" href="<?= base_url('laporan/riwayat-tte') ?>" aria-current="page">
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
                    <i class="fas fa-filter me-2"></i>Filter History TTE
                </h6>
            </div>
            <div class="card-body">
                <form method="get" action="<?= base_url('legalisasi/history/tte') ?>" class="row g-3">
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
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="SUCCESS" <?= ($status ?? '') == 'SUCCESS' ? 'selected' : '' ?>>Success</option>
                            <option value="FAILED" <?= ($status ?? '') == 'FAILED' ? 'selected' : '' ?>>Failed</option>
                            <option value="PENDING" <?= ($status ?? '') == 'PENDING' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Action</label>
                        <input type="text" name="jenis_aksi" class="form-control form-control-sm" 
                               placeholder="TTE_WALIKOTA, dll" 
                               value="<?= esc($jenis_aksi ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Penandatangan</label>
                        <select name="user" class="form-select form-select-sm">
                            <option value="">Semua User</option>
                            <?php foreach ($list_users ?? [] as $u): ?>
                                <option value="<?= $u['id_user'] ?>" <?= ($u['id_user'] == ($id_user ?? '')) ? 'selected' : '' ?>><?= esc($u['nama']) ?></option>
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

        <!-- Statistik Overview -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Aktivitas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['total'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-list fa-2x text-primary"></i>
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
                                    Berhasil
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['success'] ?? 0) ?>
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
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Gagal
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['failed'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
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
                                    Pending
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($stats['pending'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-table me-2"></i>History Aktivitas TTE
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="history-tte-table" class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Tanggal & Waktu</th>
                                <th>Ajuan</th>
                                <th>Penandatangan</th>
                                <th>Jenis Aksi</th>
                                <th>Status</th>
                                <th>Nomor Peraturan</th>
                                <th>File TTE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tte_logs)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-info-circle text-muted me-2"></i>
                                        <span class="text-muted">Tidak ada data history TTE</span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($tte_logs as $log): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($log['created_at'])) ?><br>
                                                <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($log['id_ajuan']): ?>
                                                <a href="<?= base_url('legalisasi/detail/' . $log['id_ajuan']) ?>" class="text-decoration-none">
                                                    <strong><?= esc($log['judul_peraturan'] ?? 'N/A') ?></strong>
                                                </a>
                                                <?php if ($log['nama_jenis']): ?>
                                                    <br><small class="text-muted"><?= esc($log['nama_jenis']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log['nama_penandatangan']): ?>
                                                <strong><?= esc($log['nama_penandatangan']) ?></strong>
                                                <?php if ($log['email_penandatangan']): ?>
                                                    <br><small class="text-muted"><?= esc($log['email_penandatangan']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $action = $log['action_final'] ?? $log['action'] ?? 'UNKNOWN';
                                            // Badge berdasarkan action type
                                            $actionBadge = 'bg-primary';
                                            if (stripos($action, 'WALIKOTA') !== false) {
                                                $actionBadge = 'bg-danger';
                                            } elseif (stripos($action, 'SEKDA') !== false) {
                                                $actionBadge = 'bg-warning';
                                            }
                                            ?>
                                            <span class="badge <?= $actionBadge ?> text-white"><?= esc($action) ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $stat = strtoupper($log['status_final'] ?? $log['status'] ?? 'UNKNOWN');
                                            $statusBadge = [
                                                'SUCCESS' => 'bg-success',
                                                'FAILED' => 'bg-danger',
                                                'PENDING' => 'bg-warning'
                                            ];
                                            $statusLabel = [
                                                'SUCCESS' => 'Success',
                                                'FAILED' => 'Failed',
                                                'PENDING' => 'Pending'
                                            ];
                                            $badgeClass = $statusBadge[$stat] ?? 'bg-secondary';
                                            $label = $statusLabel[$stat] ?? $stat;
                                            ?>
                                            <span class="badge <?= $badgeClass ?> text-white"><?= $label ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $nomorPeraturan = $log['document_number_final'] ?? $log['document_number'] ?? null;
                                            ?>
                                            <?php if ($nomorPeraturan): ?>
                                                <strong><?= esc($nomorPeraturan) ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $filePath = $log['signed_path_final'] ?? $log['signed_path'] ?? null;
                                            ?>
                                            <?php if ($filePath && $stat == 'SUCCESS'): ?>
                                                <span class="badge bg-success">File Tersedia</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">Previous</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $pagination['current_page'] - 2);
                            $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">Next</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                Menampilkan <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> - 
                                <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?> 
                                dari <?= number_format($pagination['total_records']) ?> data
                            </small>
                        </div>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Detail History TTE
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Content akan diisi oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#history-tte-table').DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [[1, 'desc']], // Sort by tanggal descending
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-1"></i>Export Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'History TTE - <?= date('Y-m-d') ?>'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-1"></i>Export PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'History TTE - <?= date('Y-m-d') ?>'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1"></i>Print',
                    className: 'btn btn-info btn-sm',
                    title: 'History TTE - <?= date('Y-m-d') ?>'
                }
            ]
        });
    });

    function showDetailModal(log) {
        const modalBody = document.getElementById('detailModalBody');
        let html = '<div class="row">';
        
        // Informasi Umum
        html += '<div class="col-md-12 mb-3">';
        html += '<h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Informasi Umum</h6>';
        html += '<table class="table table-sm table-bordered">';
        html += '<tr><th width="30%">Tanggal & Waktu</th><td>' + formatDateTime(log.created_at) + '</td></tr>';
        html += '<tr><th>Ajuan</th><td>' + (log.judul_peraturan || 'N/A') + '</td></tr>';
        html += '<tr><th>Jenis Peraturan</th><td>' + (log.nama_jenis || 'N/A') + '</td></tr>';
        html += '<tr><th>Instansi</th><td>' + (log.nama_instansi || 'N/A') + '</td></tr>';
        html += '<tr><th>Penandatangan</th><td>' + (log.nama_penandatangan || 'N/A') + '</td></tr>';
        html += '<tr><th>Action</th><td><span class="badge bg-primary">' + (log.action_final || log.action || 'N/A') + '</span></td></tr>';
        html += '<tr><th>Status</th><td><span class="badge bg-' + (log.status_final == 'SUCCESS' ? 'success' : (log.status_final == 'FAILED' ? 'danger' : 'warning')) + '">' + (log.status_final || log.status || 'N/A') + '</span></td></tr>';
        html += '<tr><th>Document Number</th><td>' + (log.document_number_final || log.document_number || 'N/A') + '</td></tr>';
        html += '<tr><th>Signed Path</th><td>' + (log.signed_path_final || log.signed_path || 'N/A') + '</td></tr>';
        html += '</table>';
        html += '</div>';

        // Error Message (jika ada)
        if (log.error_message) {
            html += '<div class="col-md-12 mb-3">';
            html += '<h6 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error Message</h6>';
            html += '<div class="alert alert-danger">' + escapeHtml(log.error_message) + '</div>';
            html += '</div>';
        }

        html += '</div>';
        modalBody.innerHTML = html;
        
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        modal.show();
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }

</script>

<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-danger {
        border-left: 4px solid #e74a3b !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .table thead th {
        background-color: #e9ecef !important;
        color: #212529 !important;
        border-bottom: 3px solid #495057 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 15px 10px;
        vertical-align: middle;
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

