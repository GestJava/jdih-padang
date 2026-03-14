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
            <div class="glass-card shadow-premium mb-4 overflow-hidden">
                <div class="card-header-premium bg-soft-blue py-3 border-bottom">
                    <h6 class="m-0 fw-bold text-dark tracking-wider text-uppercase">
                        <i class="fas fa-info-circle text-blue-premium me-2"></i>Informasi Pengajuan
                    </h6>
                </div>
                <div class="card-body p-4">
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
            <div class="glass-card shadow-premium mb-4 overflow-hidden">
                <div class="card-header-premium bg-soft-green py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark tracking-wider text-uppercase">
                        <i class="fas fa-paperclip text-success me-2"></i>Dokumen Terlampir
                    </h6>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3"><?= count($dokumen) ?> file</span>
                </div>
                <div class="card-body p-4">
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
                <div class="glass-card shadow-premium mb-4 overflow-hidden" id="tteResultCard">
                    <div class="card-header-premium bg-soft-blue py-3 border-bottom">
                        <h6 class="m-0 fw-bold text-dark tracking-wider text-uppercase">
                            <i class="fas fa-stamp text-blue-premium me-2"></i>Hasil Tanda Tangan Elektronik (TTE)
                        </h6>
                    </div>
                    <div class="card-body p-4">
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
            <div class="glass-card shadow-premium mb-4 h-100 overflow-hidden">
                <div class="card-header-premium bg-soft-orange py-3 border-bottom">
                    <h6 class="m-0 fw-bold text-dark tracking-wider text-uppercase">
                        <i class="fas fa-history text-warning me-2"></i>Riwayat Proses
                    </h6>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($histori)): ?>
                        <ul class="stepper-timeline">
                            <?php foreach ($histori as $item) : ?>
                                <?php
                                    // Tentukan warna marker berdasarkan nama status
                                    $markerColor = '#6c757d'; // Default (Secondary/Draft)
                                    $statusName = strtolower($item['status_sekarang'] ?? '');
                                    
                                    if (strpos($statusName, 'validasi') !== false) {
                                        $markerColor = '#17a2b8'; // Info
                                    } elseif (strpos($statusName, 'paraf') !== false) {
                                        $markerColor = '#fd7e14'; // Orange
                                    } elseif (strpos($statusName, 'selesai') !== false || strpos($statusName, 'tte') !== false) {
                                        $markerColor = '#28a745'; // Success
                                    } elseif (strpos($statusName, 'revisi') !== false || strpos($statusName, 'ditolak') !== false) {
                                        $markerColor = '#dc3545'; // Danger
                                    }
                                ?>
                                <li class="stepper-item">
                                    <div class="stepper-marker" style="border-color: <?= $markerColor ?>;"></div>
                                    <div class="stepper-content">
                                        <h6 class="stepper-title" style="color: <?= $markerColor ?>;">
                                            <?= esc($item['status_sekarang']) ?>
                                        </h6>
                                        <p class="stepper-desc">
                                            <?= esc($item['keterangan'] ?: 'Tidak ada keterangan') ?>
                                        </p>
                                        <div class="stepper-meta">
                                            <span><i class="fas fa-calendar-alt text-muted me-1"></i><?= esc($item['tanggal_formatted']) ?></span>
                                            <span>&bull;</span>
                                            <span><i class="fas fa-user text-muted me-1"></i><?= esc($item['nama_user']) ?></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
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
            <div class="glass-card shadow-premium border-0 mb-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <!-- Back Button -->
                        <?php if (isset($from_penugasan) && $from_penugasan): ?>
                            <a href="<?= base_url('penugasan') ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-medium shadow-sm-hover">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Penugasan
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('harmonisasi') ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-medium shadow-sm-hover">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
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
                                <button type="submit" class="btn btn-blue-premium rounded-pill px-4 fw-medium shadow-hover"
                                    onclick="return confirm('Apakah Anda yakin ingin mengajukan draft ini?')">
                                    <i class="fas fa-paper-plane me-2"></i>Ajukan Draft
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (
                            isset(
                                $user_actions['can_edit']
                            ) && $user_actions['can_edit']
                            && !in_array($ajuan['id_status_ajuan'], [5, 14, 15])
                        ): ?>
                            <a href="<?= base_url('harmonisasi/edit/' . $ajuan['id']) ?>" class="btn btn-warning text-dark rounded-pill px-4 fw-medium shadow-sm-hover">
                                <i class="fas fa-edit me-2"></i>Edit Draft
                            </a>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_assign']) && $user_actions['can_assign']): ?>
                            <a href="<?= base_url('penugasan/tugaskan/' . $ajuan['id']) ?>" class="btn btn-info text-white rounded-pill px-4 fw-medium shadow-sm-hover">
                                <i class="fas fa-user-plus me-2"></i>Tugaskan Verifikator
                            </a>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_complete_verification']) && $user_actions['can_complete_verification']): ?>
                            <form action="<?= base_url('harmonisasi/verifikasi-selesai/' . $ajuan['id']) ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium shadow-hover"
                                    onclick="return confirm('Apakah Anda yakin ingin menandai verifikasi selesai?')">
                                    <i class="fas fa-check-circle me-2"></i>Verifikasi Selesai
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_submit_revisi']) && $user_actions['can_submit_revisi'] && ($ajuan['nama_status'] === 'Revisi' || $ajuan['id_status_ajuan'] == 5)): ?>
                            <a href="<?= base_url('harmonisasi/submitRevisi/' . $ajuan['id']) ?>" class="btn btn-warning text-dark rounded-pill px-4 fw-medium shadow-sm-hover">
                                <i class="fas fa-upload me-2"></i>Upload Revisi
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

 