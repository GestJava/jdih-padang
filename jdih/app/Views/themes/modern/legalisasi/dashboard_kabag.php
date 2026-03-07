<?php /* Standalone view for Dashboard Kabag Hukum */ ?>

<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-shield text-primary me-2"></i>Dashboard Kabag Hukum
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('legalisasi') ?>">Legalisasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kabag Hukum</li>
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
                <h6 class="m-0 font-weight-bold"><i class="fas fa-clock me-2"></i>Dokumen Menunggu Paraf Kabag</h6>
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
                                        <td><strong><?= esc($ajuan['judul_peraturan']) ?></strong></td>
                                        <td><span class="badge bg-info"><?= esc($ajuan['nama_jenis']) ?></span></td>
                                        <td><?= esc($ajuan['nama_instansi']) ?></td>
                                        <td><span class="badge bg-warning">Menunggu Paraf Kabag</span></td>
                                        <td><?= isset($ajuan['tanggal_finalisasi']) ? date('d/m/Y', strtotime($ajuan['tanggal_finalisasi'])) : '-' ?></td>
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

<style>
    .legalisasi-module {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    .border-left-primary {
        border-left: 4px solid #007bff !important;
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

    .card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        transition: all .2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
    }

    .table thead th {
        background-color: #e9ecef !important;
        color: #212529 !important;
        border-bottom: 3px solid #495057 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: .85rem;
        letter-spacing: .5px;
        padding: 15px 10px;
        vertical-align: middle;
    }
</style>
<script>
    $(function() {
        if ($('#pendingParafTable').length) {
            try {
                $('#pendingParafTable').DataTable({
                    responsive: true,
                    language: {
                        url: '<?= base_url('vendors/datatables/Indonesian.json') ?>'
                    },
                    order: [
                        [5, 'desc']
                    ],
                    pageLength: 10
                });
            } catch (e) {
                console.warn(e);
            }
        }
    });
</script>