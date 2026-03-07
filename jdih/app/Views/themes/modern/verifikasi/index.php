<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-search text-primary me-2"></i><?= esc($title) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Verifikasi</li>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php if (!empty($verifikator_stats)): ?>
            <?php foreach (array_slice($verifikator_stats, 0, 4) as $stat): ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2 bg-primary bg-opacity-10">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        <?= esc($stat['nama']) ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= esc($stat['jumlah']) ?> Tugas
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Year Statistics Cards -->
    <?php if (!empty($year_stats)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="text-gray-800 mb-3">
                    <i class="fas fa-chart-line text-success me-2"></i>Statistik Tahun <?= $current_year ?>
                    <small class="text-muted">(<?= $data_scope ?? 'Data' ?>)</small>
                </h5>
            </div>

            <!-- Total Ajuan Tahun Ini -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2 bg-info bg-opacity-10">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Ajuan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($year_stats['total_ajuan_tahun_ini']) ?>
                                </div>
                                <div class="text-xs text-muted">
                                    Tahun <?= $current_year ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Selesai Tahun Ini -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 bg-success bg-opacity-10">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Selesai
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($year_stats['total_selesai_tahun_ini']) ?>
                                </div>
                                <div class="text-xs text-muted">
                                    <?php
                                    $persentase_selesai = $year_stats['total_ajuan_tahun_ini'] > 0
                                        ? round(($year_stats['total_selesai_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                                        : 0;
                                    echo $persentase_selesai . '% dari total';
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Ditolak Tahun Ini -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2 bg-danger bg-opacity-10">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Ditolak
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($year_stats['total_ditolak_tahun_ini']) ?>
                                </div>
                                <div class="text-xs text-muted">
                                    <?php
                                    $persentase_ditolak = $year_stats['total_ajuan_tahun_ini'] > 0
                                        ? round(($year_stats['total_ditolak_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                                        : 0;
                                    echo $persentase_ditolak . '% dari total';
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Proses Tahun Ini -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2 bg-warning bg-opacity-10">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Dalam Proses
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($year_stats['total_proses_tahun_ini']) ?>
                                </div>
                                <div class="text-xs text-muted">
                                    <?php
                                    $persentase_proses = $year_stats['total_ajuan_tahun_ini'] > 0
                                        ? round(($year_stats['total_proses_tahun_ini'] / $year_stats['total_ajuan_tahun_ini']) * 100, 1)
                                        : 0;
                                    echo $persentase_proses . '% dari total';
                                    ?>
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
    <?php endif; ?>

    <!-- Main Content Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Daftar Ajuan Menunggu Verifikasi
                <span class="badge bg-light text-dark ms-2"><?= count($ajuan) ?> ajuan</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="data-tables" class="table table-striped table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th width="35%">Judul Rancangan</th>
                            <th width="12%">Jenis</th>
                            <th width="15%">Instansi Pemohon</th>
                            <th width="12%">Tgl. Diterima</th>
                            <th width="10%">Status</th>
                            <th width="11%">Aksi</th>
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
                            8 => 'bg-dark text-white',
                            9 => 'bg-dark text-white',
                            10 => 'bg-dark text-white',
                            11 => 'bg-dark text-white',
                            12 => 'bg-dark text-white',
                            13 => 'bg-success text-white',
                            14 => 'bg-secondary text-white',
                            15 => 'bg-danger text-white',
                        ];

                        if (!empty($ajuan)) {
                            foreach ($ajuan as $item) {
                                $status_class = $status_colors[$item['id_status_ajuan']] ?? 'bg-secondary text-white';
                                $ajuan_id = $item['id'] ?? '';
                        ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-primary">
                                            <?= esc($item['judul_peraturan'] ?? 'Data tidak tersedia') ?>
                                        </div>
                                        <?php if (!empty($item['nama_pemohon'])): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?= esc($item['nama_pemohon']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            <?= esc($item['nama_jenis'] ?? 'Data tidak tersedia') ?>
                                        </span>
                                    </td>
                                    <td><?= esc($item['nama_instansi'] ?? 'Data tidak tersedia') ?></td>
                                    <td>
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php
                                        $tanggal_tampil = !empty($item['tanggal_pengajuan']) && $item['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                            ? $item['tanggal_pengajuan']
                                            : $item['created_at'];
                                        echo date('d/m/Y H:i', strtotime($tanggal_tampil));
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= esc($status_class) ?>">
                                            <?= esc($item['nama_status'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($ajuan_id): ?>
                                                <a href="<?= base_url('verifikasi/proses/' . esc($ajuan_id)) ?>"
                                                    class="btn btn-primary btn-sm"
                                                    title="Proses Verifikasi">
                                                    <i class="fas fa-tasks"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // DataTable initialization for verifikasi dashboard
    $(document).ready(function() {
        if ($.fn.DataTable && $('#data-tables').length) {
            // Check if DataTable is already initialized
            if ($.fn.DataTable.isDataTable('#data-tables')) {
                console.log('DataTable already initialized, skipping...');
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

            var table = $('#data-tables').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    emptyTable: 'Tidak ada ajuan yang perlu diverifikasi saat ini.'
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
    /* Enhanced styling */
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }

    .btn-group .btn {
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
        z-index: 1;
    }

    .badge {
        padding: 0.5em 0.75em;
        font-size: 0.75rem;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
        }

        .table-responsive .btn-group {
            min-width: 100px;
        }
    }

    /* Loading animation for buttons */
    .btn:active {
        transform: scale(0.98);
    }

    /* Enhanced statistics cards */
    .h5 {
        font-size: 1.75rem;
        font-weight: 700;
    }

    .text-xs {
        font-size: 0.7rem;
    }

    /* DataTable buttons styling */
    .dt-buttons {
        margin-bottom: 1rem;
    }

    .dt-buttons .btn {
        margin-right: 0.5rem;
    }

    /* Additional border-left styles for year statistics */
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    /* Year statistics card enhancements */
    .bg-info.bg-opacity-10 {
        background-color: rgba(23, 162, 184, 0.1) !important;
    }

    .bg-danger.bg-opacity-10 {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    .bg-warning.bg-opacity-10 {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-success.bg-opacity-10 {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    /* Hover effects for year statistics cards */
    .card.border-left-info:hover,
    .card.border-left-danger:hover,
    .card.border-left-warning:hover,
    .card.border-left-success:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15) !important;
    }
</style>