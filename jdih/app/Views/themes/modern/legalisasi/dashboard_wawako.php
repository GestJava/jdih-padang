<?php /* Standalone view without extending missing layout */ ?>

<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-signature text-info me-2"></i>Dashboard Wakil Walikota
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('legalisasi') ?>">Legalisasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Wakil Walikota</li>
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

        <!-- Pending Paraf Requests -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-clock me-2"></i>Dokumen Menunggu Paraf Wawako
                </h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-white"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Aksi:</div>
                        <a class="dropdown-item" href="<?= base_url('legalisasi/monitoring') ?>">
                            <i class="fas fa-chart-line fa-sm fa-fw me-2 text-gray-400"></i>Monitoring
                        </a>
                        <a class="dropdown-item" href="<?= base_url('legalisasi/laporan') ?>">
                            <i class="fas fa-file-alt fa-sm fa-fw me-2 text-gray-400"></i>Laporan
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($pending_paraf) && !empty($pending_paraf)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="pendingParafTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Peraturan</th>
                                    <th>Jenis</th>
                                    <th>Instansi</th>
                                    <th>Status</th>
                                    <th>Tanggal Finalisasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_paraf as $index => $ajuan): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= esc($ajuan['judul_peraturan']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= esc($ajuan['nama_jenis']) ?></span>
                                        </td>
                                        <td><?= esc($ajuan['nama_instansi']) ?></td>
                                        <td>
                                            <span class="badge bg-warning">Menunggu Paraf Wawako</span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($ajuan['tanggal_finalisasi'])) ?></td>
                                        <td>
                                            <a href="<?= base_url('legalisasi/detail/' . $ajuan['id']) ?>" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i> Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success">Tidak Ada Dokumen Menunggu Paraf</h5>
                        <p class="text-muted">Semua dokumen sudah diparaf atau sedang dalam proses TTE</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php /* Scripts section */ ?>
<script>
    // Initialize DataTable
    $(document).ready(function() {
        if ($('#pendingParafTable').length) {
            $('#pendingParafTable').DataTable({
                responsive: true,
                language: {
                    url: '<?= base_url('vendors/datatables/Indonesian.json') ?>'
                },
                order: [
                    [5, 'desc']
                ], // Sort by date descending
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Semua"]
                ]
            });
        }
    });
</script>
<?php /* End scripts */ ?>

<style>
    /* Legalisasi Module Styling */
    .legalisasi-module {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    /* Border left styling */
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    /* Card styling */
    .card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    /* Button enhancements */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Alert styling */
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Timeline styling */
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -35px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px #28a745;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #28a745;
    }

    .timeline-title {
        margin: 0 0 5px 0;
        color: #495057;
        font-weight: 600;
    }

    .timeline-text {
        margin: 0 0 10px 0;
        color: #6c757d;
    }

    /* Table enhancements */
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

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    /* Badge styling */
    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
        border-radius: 6px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .timeline {
            padding-left: 20px;
        }

        .timeline-marker {
            left: -25px;
        }

        .btn-group .btn {
            margin-bottom: 5px;
        }
    }
</style>