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
                            <li class="breadcrumb-item active text-white" aria-current="page">Monitoring</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 fw-bold text-white mb-0 font-outfit">
                        <i class="fas fa-chart-line me-2 opacity-75"></i>Monitoring Penomoran
                    </h1>
                    <p class="text-white-50 mt-2 mb-0">Pemantauan urutan dan penggunaan nomor legalisasi secara real-time.</p>
                </div>
                
                <div class="glass-card p-3 d-flex align-items-center gap-3" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                    <div class="text-white-50 small pe-3 border-end border-white-10">Tahun<br>Monitoring</div>
                    <select class="form-select form-select-sm bg-transparent text-white border-0 fw-bold" id="tahunFilter" onchange="filterByYear()" style="width: 100px; cursor: pointer;">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= ($y == $tahun) ? 'selected' : '' ?> class="text-dark"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="position-absolute top-0 end-0 p-5 mt-5 opacity-10">
            <i class="fas fa-chart-line fa-10x text-white rotate-12"></i>
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
                    <a class="nav-link py-3 fw-bold active transition-all shadow-sm" href="<?= base_url('laporan/monitoring') ?>">
                        <i class="fas fa-chart-line me-2"></i>Monitoring Penomoran
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 fw-bold transition-all" href="<?= base_url('laporan/riwayat-tte') ?>">
                        <i class="fas fa-history me-2"></i>Riwayat TTE
                    </a>
                </li>
            </ul>
        </div>

        <!-- Statistics Overview Glass Cards -->
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-blue-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-blue text-blue">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-blue text-blue px-3 py-2">Aktif</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1"><?= count($sequences ?? []) ?></h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Jenis Peraturan</p>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-green-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-green text-green">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-green text-green px-3 py-2">Verified</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1">
                        <?php
                        $total_issued = 0;
                        foreach ($usage_stats ?? [] as $count) { $total_issued += $count; }
                        echo number_format($total_issued);
                        ?>
                    </h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Total Diterbitkan</p>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-purple-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-purple text-purple">
                            <i class="fas fa-stamp"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-purple text-purple px-3 py-2">Sekda</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1">
                        <?php
                        $sekda_count = 0;
                        foreach ($sequences ?? [] as $seq) {
                            if ($seq['authority_level'] === 'sekda') { $sekda_count += $usage_stats[$seq['jenis_peraturan']] ?? 0; }
                        }
                        echo number_format($sekda_count);
                        ?>
                    </h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Delegasi Sekda</p>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="glass-card h-100 p-4 border-start border-4 border-orange-premium">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="mini-icon bg-soft-orange text-orange">
                            <i class="fas fa-crown"></i>
                        </div>
                        <span class="badge rounded-pill bg-soft-orange text-orange px-3 py-2">Walikota</span>
                    </div>
                    <h3 class="fw-bold font-outfit mb-1">
                        <?php
                        $walikota_count = 0;
                        foreach ($sequences ?? [] as $seq) {
                            if ($seq['authority_level'] === 'walikota') { $walikota_count += $usage_stats[$seq['jenis_peraturan']] ?? 0; }
                        }
                        echo number_format($walikota_count);
                        ?>
                    </h3>
                    <p class="text-muted small mb-0 fw-medium uppercase letter-spacing-1">Delegasi Walikota</p>
                </div>
            </div>
        </div>

        <!-- Main Data Table -->
        <div class="glass-card mb-5">
            <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h5 class="fw-bold font-outfit mb-0"><i class="fas fa-list-ol me-2 text-blue"></i>Detail Penomoran per Jenis</h5>
                <div id="exportButtons"></div>
            </div>
            <div class="p-4">
                <div class="table-responsive">
                    <table id="monitoring-table" class="table table-premium table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis Peraturan</th>
                                <th>Prefix</th>
                                <th>Authority</th>
                                <th>Sequence</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($sequences ?? [] as $seq): ?>
                            <tr>
                                <td class="text-muted small"><?= $no++ ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= esc($seq['jenis_peraturan']) ?></div>
                                    <div class="tiny text-muted mt-1 fst-italic">Legalisasi <?= $tahun ?></div>
                                </td>
                                <td><span class="badge-soft-dark"><?= esc($seq['prefix_nomor']) ?></span></td>
                                <td>
                                    <?php if ($seq['authority_level'] === 'sekda'): ?>
                                        <span class="status-pill status-orange"><i class="fas fa-stamp me-1"></i>Sekda</span>
                                    <?php else: ?>
                                        <span class="status-pill status-red"><i class="fas fa-crown me-1"></i>Walikota</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-dark fw-bold"><?= $seq['last_number'] ?></div>
                                        <i class="fas fa-arrow-right tiny text-muted opacity-50"></i>
                                        <div class="text-blue fw-bold bg-soft-blue px-2 rounded small"><?= ($seq['last_number'] + 1) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= $usage_stats[$seq['jenis_peraturan']] ?? 0 ?></div>
                                    <div class="progress mt-1" style="height: 4px; width: 60px;">
                                        <div class="progress-bar bg-green" style="width: 100%"></div>
                                    </div>
                                </td>
                                <td>
                                    <?php $gap = $seq['last_number'] - ($usage_stats[$seq['jenis_peraturan']] ?? 0); ?>
                                    <?php if ($gap === 0): ?>
                                        <span class="badge rounded-pill bg-soft-green text-green small px-3"><i class="fas fa-sync me-1"></i>Sync</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-soft-orange text-orange small px-3"><i class="fas fa-exclamation-triangle me-1"></i>Gap <?= $gap ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-action-circle btn-soft-blue" title="History"><i class="fas fa-history"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Authority Breakdown Section -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="glass-card">
                    <div class="p-3 border-bottom bg-soft-orange d-flex align-items-center">
                        <i class="fas fa-user-tie me-3 fs-4 text-orange"></i>
                        <h6 class="fw-bold font-outfit mb-0">Rincian Penomoran: Sekretaris Daerah</h6>
                    </div>
                    <div class="p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="tiny text-muted uppercase letter-spacing-1">
                                        <th>Jenis Peraturan</th>
                                        <th>Last</th>
                                        <th>Next</th>
                                        <th class="text-end">Digunakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sequences ?? [] as $seq): if ($seq['authority_level'] === 'sekda'): ?>
                                    <tr class="border-bottom-faded">
                                        <td class="py-3 fw-medium"><?= esc($seq['jenis_peraturan']) ?></td>
                                        <td><span class="fw-bold"><?= $seq['last_number'] ?></span></td>
                                        <td><span class="text-blue"><?= ($seq['last_number'] + 1) ?></span></td>
                                        <td class="text-end"><span class="badge bg-soft-orange text-orange"><?= $usage_stats[$seq['jenis_peraturan']] ?? 0 ?></span></td>
                                    </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="glass-card">
                    <div class="p-3 border-bottom bg-soft-red d-flex align-items-center">
                        <i class="fas fa-crown me-3 fs-4 text-red"></i>
                        <h6 class="fw-bold font-outfit mb-0">Rincian Penomoran: Walikota</h6>
                    </div>
                    <div class="p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="tiny text-muted uppercase letter-spacing-1">
                                        <th>Jenis Peraturan</th>
                                        <th>Last</th>
                                        <th>Next</th>
                                        <th class="text-end">Digunakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sequences ?? [] as $seq): if ($seq['authority_level'] === 'walikota'): ?>
                                    <tr class="border-bottom-faded">
                                        <td class="py-3 fw-medium"><?= esc($seq['jenis_peraturan']) ?></td>
                                        <td><span class="fw-bold"><?= $seq['last_number'] ?></span></td>
                                        <td><span class="text-blue"><?= ($seq['last_number'] + 1) ?></span></td>
                                        <td class="text-end"><span class="badge bg-soft-red text-red"><?= $usage_stats[$seq['jenis_peraturan']] ?? 0 ?></span></td>
                                    </tr>
                                    <?php endif; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
        --purple-premium: #8b5cf6;
        --purple-soft: #f5f3ff;
        --orange-premium: #f59e0b;
        --orange-soft: #fffbeb;
        --red-premium: #ef4444;
        --red-soft: #fef2f2;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.4);
    }

    .font-outfit { font-family: 'Outfit', sans-serif; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .uppercase { text-transform: uppercase; }
    .tiny { font-size: 0.7rem; }
    .transition-all { transition: all 0.3s ease; }

    .header-premium-blue {
        box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .nav-pills .nav-link {
        color: #64748b;
        border-radius: 1.25rem;
    }

    .nav-pills .nav-link.active {
        background: white;
        color: var(--blue-premium);
    }

    .mini-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.2rem;
    }

    .bg-soft-blue { background: var(--blue-soft); }
    .text-blue { color: var(--blue-premium); }
    .border-blue-premium { border-color: var(--blue-premium) !important; }

    .bg-soft-green { background: var(--green-soft); }
    .text-green { color: var(--green-premium); }
    .border-green-premium { border-color: var(--green-premium) !important; }

    .bg-soft-purple { background: var(--purple-soft); }
    .text-purple { color: var(--purple-premium); }
    .border-purple-premium { border-color: var(--purple-premium) !important; }

    .bg-soft-orange { background: var(--orange-soft); }
    .text-orange { color: var(--orange-premium); }
    .border-orange-premium { border-color: var(--orange-premium) !important; }

    .bg-soft-red { background: var(--red-soft); }
    .text-red { color: var(--red-premium); }

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

    .table-premium tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }

    .badge-soft-dark {
        background: #f1f5f9;
        color: #475569;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .status-pill {
        padding: 0.4rem 0.75rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
    }

    .status-orange { background: var(--orange-soft); color: var(--orange-premium); border: 1px solid rgba(245, 158, 11, 0.2); }
    .status-red { background: var(--red-soft); color: var(--red-premium); border: 1px solid rgba(239, 68, 68, 0.2); }

    .btn-action-circle {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-soft-blue:hover { background: var(--blue-premium); color: white; transform: rotate(15deg); }

    .border-bottom-faded {
        border-bottom: 1px solid rgba(0,0,0,0.03);
    }
    .border-bottom-faded:last-child { border-bottom: none; }

    /* DataTable Overrides */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        padding: 0.4rem 1rem;
        margin-left: 0.5rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--blue-premium) !important;
        border: none !important;
        color: white !important;
        border-radius: 50% !important;
    }
</style>

<script>
    $(document).ready(function() {
        // Initialize DataTable with premium options
        const table = $('#monitoring-table').DataTable({
            responsive: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
            order: [[1, 'asc']],
            pageLength: 25,
            dom: 'frtip',
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded mt-3');
            }
        });

        // Initialize Export Buttons
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                { extend: 'excel', text: '<i class="fas fa-file-excel me-2"></i>Excel', className: 'btn btn-soft-green btn-sm rounded-pill px-3' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf me-2"></i>PDF', className: 'btn btn-soft-red btn-sm rounded-pill px-3' },
                { extend: 'print', text: '<i class="fas fa-print me-2"></i>Cetak', className: 'btn btn-soft-blue btn-sm rounded-pill px-3' }
            ]
        }).container().appendTo($('#exportButtons'));
    });

    function filterByYear() {
        const tahun = $('#tahunFilter').val();
        window.location.href = '<?= base_url('legalisasi/monitoring') ?>?tahun=' + tahun;
    }
</script>