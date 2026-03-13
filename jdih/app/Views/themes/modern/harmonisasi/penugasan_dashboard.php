<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks text-primary me-2"></i><?= esc($title) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('harmonisasi') ?>">Harmonisasi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Penugasan</li>
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
        <!-- 6 Kartu: Verifikator dengan tugas terbanyak -->
        <?php if (!empty($verifikator_stats)): ?>
            <?php foreach (array_slice($verifikator_stats, 0, 6) as $stat): ?>
                <div class="col-xl-2 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2 bg-info bg-opacity-10">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        <?= esc($stat['nama']) ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= esc($stat['jumlah']) ?> Tugas
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

   <!-- Card Tugas Verifikasi dan Finalisasi -->
    <div class="row mb-4">
        <!-- Card Tugas Validasi -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tugas Validasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($jumlah_validasi) ? esc($jumlah_validasi) : 0 ?> Tugas
                            </div>
                            <small class="text-muted">Sedang dalam proses verifikasi</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Tugas Finalisasi -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tugas Finalisasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($jumlah_finalisasi) ? esc($jumlah_finalisasi) : 0 ?> Tugas
                            </div>
                            <small class="text-muted">Menunggu finalisasi dokumen</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Data Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Daftar Ajuan Menunggu Penugasan
                <span class="badge bg-light text-dark ms-2"><?= count($ajuan_penugasan) ?> ajuan</span>
            </h6>
        </div>
        <div class="card-body">
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
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-primary">
                                            <?= esc($item['judul_peraturan']) ?>
                                        </div>
                                        <?php if (!empty($item['nama_pemohon'])): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?= esc($item['nama_pemohon']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            <?= esc($item['nama_jenis']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($item['nama_instansi']) ?></td>
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
                                            <?= esc($item['nama_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= base_url('penugasan/tugaskan/' . $ajuan_id) ?>"
                                                class="btn btn-primary btn-sm"
                                                title="Tugaskan Verifikator">
                                                <i class="fas fa-user-plus"></i> Tugaskan
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-check fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Ajuan</h5>
                    <p class="text-muted">Tidak ada ajuan yang menunggu penugasan verifikator saat ini.</p>
                    <a href="<?= base_url('harmonisasi') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard Harmonisasi
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

    /* Enhanced table styling */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }

    /* Card hover effects */
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }

    /* Button group enhancements */
    .btn-group .btn {
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
        z-index: 1;
    }

    /* Badge enhancements */
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


</style>
