<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt text-primary me-2"></i><?= esc($title) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('harmonisasi') ?>">Harmonisasi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail</li>
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

    <div class="row">
        <div class="col-lg-8">
            <!-- Detail Ajuan -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle me-2"></i>Informasi Pengajuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%" class="text-muted">Judul Peraturan</th>
                                    <td><?= esc($ajuan['judul_peraturan']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Jenis Peraturan</th>
                                    <td>
                                        <span class="badge bg-info text-white"><?= esc($ajuan['nama_jenis']) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Instansi Pemohon</th>
                                    <td><?= esc($ajuan['nama_instansi']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">User Pemohon</th>
                                    <td><?= esc($ajuan['nama_pemohon']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%" class="text-muted">Tanggal Pengajuan</th>
                                    <td>
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= esc($ajuan['tanggal_pengajuan_formatted']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Status Saat Ini</th>
                                    <td>
                                        <?php if (!empty($ajuan['nama_status'])): ?>
                                            <span class="badge bg-success text-white fs-6">
                                                <i class="fas fa-check-circle me-1"></i><?= esc($ajuan['nama_status']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-white fs-6">
                                                <i class="fas fa-question-circle me-1"></i>
                                                Status ID: <?= esc($ajuan['id_status_ajuan'] ?? 'Tidak diketahui') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Petugas Verifikasi</th>
                                    <td>
                                        <?php if (!empty($ajuan['nama_verifikator'])): ?>
                                            <i class="fas fa-user-check me-1 text-success"></i>
                                            <?= esc($ajuan['nama_verifikator']) ?>
                                        <?php else: ?>
                                            <i class="fas fa-user-times me-1 text-muted"></i>
                                            <span class="text-muted">Belum Ditugaskan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Dokumen -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-paperclip me-2"></i>Dokumen Terlampir
                        <span class="badge bg-light text-dark ms-2"><?= count($dokumen) ?> file</span>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($dokumen)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($dokumen as $doc) : ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold text-primary">
                                            <i class="fas fa-file-alt me-1"></i>
                                            <?= esc(ucwords(str_replace('_', ' ', $doc['tipe_dokumen']))) ?>
                                        </div>
                                        <p class="mb-1"><?= esc($doc['nama_file_original']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Diunggah: <?= date('d F Y H:i', strtotime($doc['created_at'])) ?>
                                        </small>
                                    </div>
                                    <a href="<?= base_url('harmonisasi/download/' . $doc['id']) ?>"
                                        class="btn btn-success btn-sm"
                                        title="Unduh Dokumen">
                                        <i class="fas fa-download"></i> Unduh
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada dokumen yang diunggah</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Data TTE Result -->
            <?php if (isset($tte_data) && !empty($tte_data) && !empty($tte_data['tte_file_path'])): ?>
                <div class="card shadow mb-4" id="tteResultCard">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-stamp me-2"></i>Hasil Tanda Tangan Elektronik (TTE)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%" class="text-muted">Nomor Peraturan</th>
                                        <td>
                                            <span class="badge bg-info text-white"><?= esc($tte_data['nomor_peraturan'] ?? '-') ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Jenis Peraturan</th>
                                        <td><?= esc($tte_data['jenis_peraturan'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Tanggal Pengesahan</th>
                                        <td>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= isset($tte_data['tanggal_pengesahan']) ? date('d F Y', strtotime($tte_data['tanggal_pengesahan'])) : '-' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">User Role</th>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($tte_data['tte_user_role'] ?? '-') ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%" class="text-muted">TTE Completed At</th>
                                        <td>
                                            <i class="fas fa-clock me-1"></i>
                                            <?= isset($tte_data['tte_completed_at']) ? date('d F Y H:i', strtotime($tte_data['tte_completed_at'])) : '-' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Document URL</th>
                                        <td>
                                            <?php if (!empty($tte_data['document_url'])): ?>
                                                <a href="<?= esc($tte_data['document_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i> Buka Dokumen
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">File Path</th>
                                        <td>
                                            <small class="text-muted"><?= esc($tte_data['tte_file_path'] ?? '-') ?></small>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Histori Ajuan -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-history me-2"></i>Riwayat Proses
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($histori)): ?>
                        <div class="timeline-container">
                            <?php foreach ($histori as $item) : ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title text-primary">
                                            <?= esc($item['status_sekarang']) ?>
                                        </h6>
                                        <p class="timeline-description">
                                            <?= esc($item['keterangan']) ?>
                                        </p>
                                        <small class="timeline-time text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= esc($item['tanggal_formatted']) ?>
                                            oleh <?= esc($item['nama_user']) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada riwayat proses</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <!-- Back Button -->
                        <?php if (isset($from_penugasan) && $from_penugasan): ?>
                            <a href="<?= base_url('penugasan') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Penugasan
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('harmonisasi') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                            </a>
                        <?php endif; ?>

                        <!-- User Action Buttons (Logic moved from view to controller) -->
                        <?php if (
                            isset(
                                $user_actions['can_submit']
                            ) && $user_actions['can_submit']
                            && !in_array($ajuan['id_status_ajuan'], [5, 14, 15])
                        ): ?>
                            <form action="<?= base_url('harmonisasi/ajukan/' . $ajuan['id']) ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Apakah Anda yakin ingin mengajukan draft ini?')">
                                    <i class="fas fa-paper-plane me-1"></i> Ajukan Draft
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (
                            isset(
                                $user_actions['can_edit']
                            ) && $user_actions['can_edit']
                            && !in_array($ajuan['id_status_ajuan'], [5, 14, 15])
                        ): ?>
                            <a href="<?= base_url('harmonisasi/edit/' . $ajuan['id']) ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Edit Draft
                            </a>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_assign']) && $user_actions['can_assign']): ?>
                            <a href="<?= base_url('penugasan/tugaskan/' . $ajuan['id']) ?>" class="btn btn-info">
                                <i class="fas fa-user-plus me-1"></i> Tugaskan Verifikator
                            </a>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_complete_verification']) && $user_actions['can_complete_verification']): ?>
                            <form action="<?= base_url('harmonisasi/verifikasi-selesai/' . $ajuan['id']) ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Apakah Anda yakin ingin menandai verifikasi selesai?')">
                                    <i class="fas fa-check-circle me-1"></i> Verifikasi Selesai
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_submit_revisi']) && $user_actions['can_submit_revisi'] && ($ajuan['nama_status'] === 'Revisi' || $ajuan['id_status_ajuan'] == 5)): ?>
                            <a href="<?= base_url('harmonisasi/submitRevisi/' . $ajuan['id']) ?>" class="btn btn-warning">
                                <i class="fas fa-upload me-1"></i> Upload Revisi
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
    }

    .timeline:before {
        top: 0;
        bottom: 0;
        position: absolute;
        content: " ";
        width: 3px;
        background-color: #eeeeee;
        left: 0;
        margin-left: -1.5px;
    }

    .timeline>li {
        margin-bottom: 20px;
        position: relative;
        padding-left: 20px;
    }

    .timeline>li:before,
    .timeline>li:after {
        content: " ";
        display: table;
    }

    .timeline>li:after {
        clear: both;
    }

    .timeline>li>.timeline-panel {
        width: 100%;
        float: left;
        border: 1px solid #d4d4d4;
        border-radius: 2px;
        padding: 20px;
        position: relative;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
    }

    .timeline>li>.timeline-badge {
        color: #fff;
        width: 24px;
        height: 24px;
        line-height: 50px;
        font-size: 1.4em;
        text-align: center;
        position: absolute;
        top: 16px;
        left: 0px;
        margin-left: -12px;
        background-color: #999999;
        z-index: 100;
        border-top-right-radius: 50%;
        border-top-left-radius: 50%;
        border-bottom-right-radius: 50%;
        border-bottom-left-radius: 50%;
    }
</style>