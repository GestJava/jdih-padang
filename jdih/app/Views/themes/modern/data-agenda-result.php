<?php
// Add body class for JavaScript detection
if (!empty($body_class)) {
    echo '<script>document.body.className += " ' . esc($body_class) . '";</script>';
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt text-primary me-2"></i>Agenda Kegiatan
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Agenda</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($msg)): ?>
        <?= show_alert($msg); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-list me-2"></i>Daftar Agenda
                        </h6>
                        <div>
                            <button type="button" class="btn btn-light btn-sm me-2" data-action="reload-table" title="Refresh Data">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <a href="<?= current_url() ?>/add" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i>Tambah Agenda
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-result" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Judul</th>
                                    <th>Tanggal</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($agenda_list)): ?>
                                    <?php $no = 1;
                                    foreach ($agenda_list as $agenda): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= esc($agenda['judul_agenda']) ?></td>
                                            <td><?= esc($agenda['tanggal_mulai']) ?></td>
                                            <td><?= esc($agenda['lokasi'] ?? '-') ?></td>
                                            <td>
                                                <?php
                                                $status = $agenda['status_agenda'] ?? '';
                                                $statusClass = '';
                                                $statusText = '';

                                                switch (strtolower($status)) {
                                                    case 'akan_datang':
                                                        $statusClass = 'bg-info';
                                                        $statusText = 'Akan Datang';
                                                        break;
                                                    case 'sedang_berlangsung':
                                                        $statusClass = 'bg-warning';
                                                        $statusText = 'Sedang Berlangsung';
                                                        break;
                                                    case 'selesai':
                                                        $statusClass = 'bg-success';
                                                        $statusText = 'Selesai';
                                                        break;
                                                    case 'dibatalkan':
                                                        $statusClass = 'bg-danger';
                                                        $statusText = 'Dibatalkan';
                                                        break;
                                                    case 'ditunda':
                                                        $statusClass = 'bg-secondary';
                                                        $statusText = 'Ditunda';
                                                        break;
                                                    default:
                                                        $statusClass = 'bg-secondary';
                                                        $statusText = $status ?: 'Belum Ditetapkan';
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('data_agenda/edit?id=' . $agenda['id']) ?>" class="btn btn-primary btn-xs">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada agenda kegiatan</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#table-result').DataTable({
            "processing": false,
            "serverSide": false,
            "pageLength": 25,
            "order": [
                [2, "desc"]
            ], // Sort by tanggal (column 2) descending
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });
</script>