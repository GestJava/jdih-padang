<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-primary me-2"></i><?= esc($title) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('laporan') ?>">Laporan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Monitoring</li>
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
                        <a class="nav-link active" href="<?= base_url('laporan/monitoring') ?>" aria-current="page">
                            <i class="fas fa-chart-line me-2"></i>Monitoring Penomoran
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="<?= base_url('laporan/riwayat-tte') ?>">
                            <i class="fas fa-history me-2"></i>Riwayat TTE
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Year Filter -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-filter me-2"></i>Filter Tahun
                </h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <select class="form-select" id="tahunFilter" onchange="filterByYear()">
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($y == $tahun) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Menampilkan data penomoran untuk tahun <strong><?= $tahun ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Jenis Aktif
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= count($sequences ?? []) ?> Jenis
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-list fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Diterbitkan
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $total_issued = 0;
                                    foreach ($usage_stats ?? [] as $count) {
                                        $total_issued += $count;
                                    }
                                    echo $total_issued;
                                    ?> Nomor
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-certificate fa-2x text-info"></i>
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
                                    Authority Sekda
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $sekda_count = 0;
                                    foreach ($sequences ?? [] as $seq) {
                                        if ($seq['authority_level'] === 'sekda') {
                                            $sekda_count += $usage_stats[$seq['jenis_peraturan']] ?? 0;
                                        }
                                    }
                                    echo $sekda_count;
                                    ?> Nomor
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-stamp fa-2x text-warning"></i>
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
                                    Authority Walikota
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $walikota_count = 0;
                                    foreach ($sequences ?? [] as $seq) {
                                        if ($seq['authority_level'] === 'walikota') {
                                            $walikota_count += $usage_stats[$seq['jenis_peraturan']] ?? 0;
                                        }
                                    }
                                    echo $walikota_count;
                                    ?> Nomor
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-crown fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sequence Details per Jenis -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-list-ol me-2"></i>Detail Penomoran per Jenis Peraturan (Tahun <?= $tahun ?>)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="monitoring-table" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Jenis Peraturan</th>
                                <th>Prefix</th>
                                <th>Authority</th>
                                <th>Nomor Terakhir</th>
                                <th>Nomor Selanjutnya</th>
                                <th>Total Diterbitkan</th>
                                <th>Terakhir Diterbitkan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sequences)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-info-circle text-muted me-2"></i>
                                        <span class="text-muted">Tidak ada data penomoran untuk tahun <?= $tahun ?></span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1;
                                foreach ($sequences as $seq): ?>
                                    <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold"><?= esc($seq['jenis_peraturan']) ?></div>
                                        <small class="text-muted">Berdiri sendiri</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($seq['prefix_nomor']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($seq['authority_level'] === 'sekda'): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-stamp me-1"></i>Sekda
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white">
                                                <i class="fas fa-crown me-1"></i>Walikota
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= $seq['last_number'] ?></strong>
                                        <?php if (!empty($seq['nomor_peraturan_terakhir'])): ?>
                                            <br><small class="text-muted"><?= esc($seq['nomor_peraturan_terakhir']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            <?= ($seq['last_number'] + 1) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success text-white">
                                            <?= $usage_stats[$seq['jenis_peraturan']] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($seq['last_issued_at']): ?>
                                            <small><?= date('d/m/Y H:i', strtotime($seq['last_issued_at'])) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">Belum ada</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $gap = $seq['last_number'] - ($usage_stats[$seq['jenis_peraturan']] ?? 0);
                                        if ($gap === 0): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Sync
                                            </span>
                                        <?php elseif ($gap > 0): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-exclamation me-1"></i>Gap: <?= $gap ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Error
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Authority Breakdown -->
        <div class="row mb-4">
            <!-- Sekda Authority -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-stamp me-2"></i>Authority: Sekretaris Daerah
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Last</th>
                                        <th>Next</th>
                                        <th>Used</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sequences ?? [] as $seq): ?>
                                        <?php if ($seq['authority_level'] === 'sekda'): ?>
                                            <tr>
                                                <td><?= esc($seq['jenis_peraturan']) ?></td>
                                                <td><strong><?= $seq['last_number'] ?></strong></td>
                                                <td><span class="badge bg-info"><?= ($seq['last_number'] + 1) ?></span></td>
                                                <td><span class="badge bg-success"><?= $usage_stats[$seq['jenis_peraturan']] ?? 0 ?></span></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Walikota Authority -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-crown me-2"></i>Authority: Walikota
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Last</th>
                                        <th>Next</th>
                                        <th>Used</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sequences ?? [] as $seq): ?>
                                        <?php if ($seq['authority_level'] === 'walikota'): ?>
                                            <tr>
                                                <td><?= esc($seq['jenis_peraturan']) ?></td>
                                                <td><strong><?= $seq['last_number'] ?></strong></td>
                                                <td><span class="badge bg-info"><?= ($seq['last_number'] + 1) ?></span></td>
                                                <td><span class="badge bg-success"><?= $usage_stats[$seq['jenis_peraturan']] ?? 0 ?></span></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
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
    $(document).ready(function() {
        // Initialize DataTable
        $('#monitoring-table').DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [
                [1, 'asc']
            ], // Sort by jenis peraturan
            pageLength: 25,
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-1"></i>Export Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Monitoring Penomoran Legalisasi - Tahun <?= $tahun ?>'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-1"></i>Export PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Monitoring Penomoran Legalisasi - Tahun <?= $tahun ?>'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-1"></i>Print',
                    className: 'btn btn-info btn-sm',
                    title: 'Monitoring Penomoran Legalisasi - Tahun <?= $tahun ?>'
                }
            ]
        });
    });

    function filterByYear() {
        const tahun = $('#tahunFilter').val();
        window.location.href = '<?= base_url('legalisasi/monitoring') ?>?tahun=' + tahun;
    }

</script>

<style>
    /* Enhanced table styling */
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

    .table thead th:hover {
        background-color: #495057 !important;
        color: #ffffff !important;
        transition: all 0.3s ease;
    }

    /* Card styling */
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    /* Badge enhancements */
    .badge {
        font-weight: 500;
        border-radius: 6px;
        padding: 0.5em 0.75em;
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

    /* DataTable buttons */
    .dt-buttons {
        margin-bottom: 1rem;
    }

    .dt-buttons .btn {
        margin-right: 0.5rem;
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