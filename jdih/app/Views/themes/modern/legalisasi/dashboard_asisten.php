<?php /* Standalone view without extending missing layout */ ?>

<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-tie text-primary me-2"></i>Dashboard Asisten Walikota
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('legalisasi') ?>">Legalisasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Asisten Walikota</li>
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
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-clock me-2"></i>Dokumen Menunggu Paraf Asisten (Semua Jenis)
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
                <?php 
                // Debug: Log data untuk troubleshooting
                if (isset($pending_paraf)) {
                    log_message('debug', 'Dashboard Asisten View - pending_paraf isset: true, count: ' . count($pending_paraf ?? []));
                } else {
                    log_message('debug', 'Dashboard Asisten View - pending_paraf NOT SET');
                }
                if (isset($stats)) {
                    log_message('debug', 'Dashboard Asisten View - stats: ' . json_encode($stats));
                } else {
                    log_message('debug', 'Dashboard Asisten View - stats NOT SET');
                }
                ?>
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
                                    <th>Workflow</th>
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
                                            <span class="badge bg-warning">Menunggu Paraf Asisten</span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($ajuan['tanggal_finalisasi'])) ?></td>
                                        <td>
                                            <?php
                                            // Daftar jenis peraturan yang menggunakan workflow TTE Sekda (Group A)
                                            // Harus sesuai dengan logika di controller Legalisasi.php (baris 1476, 1514, 554, 757)
                                            $jenisSekda = [
                                                'keputusan sekda',
                                                'keputusan sekretaris daerah',
                                                'instruksi sekda',
                                                'instruksi sekretaris daerah',
                                                'surat edaran sekda',
                                                'surat edaran sekretaris daerah'
                                            ];
                                            
                                            $jenis = strtolower(trim($ajuan['nama_jenis'] ?? ''));
                                            
                                            // Cek apakah jenis peraturan termasuk dalam Group A (TTE Sekda)
                                            if (in_array($jenis, $jenisSekda)) {
                                                echo '<span class="badge bg-warning">TTE Sekda</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">TTE Walikota</span>';
                                            }
                                            ?>
                                        </td>
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
                        <p class="text-muted">Semua dokumen sudah diparaf atau sedang dalam proses selanjutnya</p>
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
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
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
        .btn-group .btn {
            margin-bottom: 5px;
        }
    }
</style>