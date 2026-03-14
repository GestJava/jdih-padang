<div class="container-fluid">
    <!-- Mesh Background -->
    <div class="mesh-background"></div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert" style="animation: fadeInDown 0.5s ease;">
            <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert" style="animation: fadeInDown 0.5s ease;">
            <i class="fas fa-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ============================================================
         HERO STATUS SECTION
         ============================================================ -->
    <div class="hero-status-card mb-4" style="animation: fadeInUp 0.4s ease;">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="<?= base_url('harmonisasi') ?>" class="text-decoration-none" style="color: #0061ff;">Harmonisasi</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Detail Ajuan</li>
                    </ol>
                </nav>
                <h2 class="hero-title mb-2"><?= esc($ajuan['judul_peraturan']) ?></h2>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: rgba(0,97,255,0.1); color: #0061ff; font-size: 0.85rem;">
                        <i class="fas fa-gavel me-1"></i><?= esc($ajuan['nama_jenis']) ?>
                    </span>
                    <?php if (!empty($ajuan['nama_status'])): ?>
                        <?php
                            $statusColor = '#28a745';
                            $statusIcon = 'fa-check-circle';
                            $statusLower = strtolower($ajuan['nama_status']);
                            if (strpos($statusLower, 'draft') !== false) { $statusColor = '#6c757d'; $statusIcon = 'fa-pencil-alt'; }
                            elseif (strpos($statusLower, 'validasi') !== false) { $statusColor = '#17a2b8'; $statusIcon = 'fa-clipboard-check'; }
                            elseif (strpos($statusLower, 'paraf') !== false) { $statusColor = '#fd7e14'; $statusIcon = 'fa-signature'; }
                            elseif (strpos($statusLower, 'revisi') !== false) { $statusColor = '#dc3545'; $statusIcon = 'fa-redo'; }
                            elseif (strpos($statusLower, 'ditolak') !== false) { $statusColor = '#dc3545'; $statusIcon = 'fa-times-circle'; }
                            elseif (strpos($statusLower, 'selesai') !== false || strpos($statusLower, 'tte') !== false) { $statusColor = '#28a745'; $statusIcon = 'fa-check-double'; }
                        ?>
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: <?= $statusColor ?>15; color: <?= $statusColor ?>; font-size: 0.85rem; border: 1px solid <?= $statusColor ?>30;">
                            <i class="fas <?= $statusIcon ?> me-1"></i><?= esc($ajuan['nama_status']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 mt-3 mt-lg-0">
                <div class="d-flex flex-column gap-2 align-items-lg-end">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(0,97,255,0.1);">
                            <i class="fas fa-calendar-alt" style="color: #0061ff;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Tanggal Pengajuan</small>
                            <span class="fw-bold" style="font-size: 0.95rem; color: #2d3748;"><?= esc($ajuan['tanggal_pengajuan_formatted']) ?></span>
                        </div>
                    </div>
                    <?php if (!empty($ajuan['nama_verifikator'])): ?>
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(40,167,69,0.1);">
                            <i class="fas fa-user-check" style="color: #28a745;"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">Verifikator</small>
                            <span class="fw-bold" style="font-size: 0.95rem; color: #2d3748;"><?= esc($ajuan['nama_verifikator']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ============================================================
             LEFT COLUMN
             ============================================================ -->
        <div class="col-lg-8">
            <!-- INFO SLOTS CARD -->
            <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.5s ease;">
                <h6 class="fw-bold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-info-circle me-2" style="color: #0061ff;"></i>Informasi Pengajuan
                </h6>
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="info-slot">
                            <div class="info-slot-icon"><i class="fas fa-gavel"></i></div>
                            <div class="info-slot-content">
                                <span class="label">Jenis Peraturan</span>
                                <span class="value"><?= esc($ajuan['nama_jenis']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-slot">
                            <div class="info-slot-icon" style="background: rgba(40,167,69,0.08); color: #28a745;"><i class="fas fa-building"></i></div>
                            <div class="info-slot-content">
                                <span class="label">Instansi Pemohon</span>
                                <span class="value"><?= esc($ajuan['nama_instansi']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-slot">
                            <div class="info-slot-icon" style="background: rgba(253,126,20,0.08); color: #fd7e14;"><i class="fas fa-user"></i></div>
                            <div class="info-slot-content">
                                <span class="label">User Pemohon</span>
                                <span class="value"><?= esc($ajuan['nama_pemohon']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-slot">
                            <div class="info-slot-icon" style="background: rgba(108,117,125,0.08); color: #6c757d;"><i class="fas fa-user-shield"></i></div>
                            <div class="info-slot-content">
                                <span class="label">Petugas Verifikasi</span>
                                <span class="value">
                                    <?php if (!empty($ajuan['nama_verifikator'])): ?>
                                        <?= esc($ajuan['nama_verifikator']) ?>
                                    <?php else: ?>
                                        <span style="color: #cbd5e0; font-style: italic;">Belum Ditugaskan</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DOKUMEN TERLAMPIR -->
            <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.6s ease;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                        <i class="fas fa-paperclip me-2" style="color: #28a745;"></i>Dokumen Terlampir
                    </h6>
                    <span class="badge rounded-pill px-3 py-1" style="background: rgba(40,167,69,0.1); color: #28a745; font-weight: 600;"><?= count($dokumen) ?> file</span>
                </div>
                <?php if (!empty($dokumen)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($dokumen as $doc) : ?>
                            <?php
                                // Ikon berdasarkan ekstensi file
                                $ext = strtolower(pathinfo($doc['nama_file_original'], PATHINFO_EXTENSION));
                                $fileIcon = 'fa-file-alt';
                                $fileColor = '#6c757d';
                                $fileBg = 'rgba(108,117,125,0.08)';
                                if ($ext === 'pdf') { $fileIcon = 'fa-file-pdf'; $fileColor = '#dc3545'; $fileBg = 'rgba(220,53,69,0.08)'; }
                                elseif (in_array($ext, ['doc', 'docx'])) { $fileIcon = 'fa-file-word'; $fileColor = '#0061ff'; $fileBg = 'rgba(0,97,255,0.08)'; }
                                elseif (in_array($ext, ['xls', 'xlsx'])) { $fileIcon = 'fa-file-excel'; $fileColor = '#28a745'; $fileBg = 'rgba(40,167,69,0.08)'; }
                                elseif (in_array($ext, ['zip', 'rar'])) { $fileIcon = 'fa-file-archive'; $fileColor = '#fd7e14'; $fileBg = 'rgba(253,126,20,0.08)'; }
                            ?>
                            <div class="d-flex align-items-center p-3 rounded-4" style="background: rgba(248,250,255,0.8); border: 1px solid rgba(0,0,0,0.04); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: <?= $fileBg ?>; flex-shrink: 0;">
                                    <i class="fas <?= $fileIcon ?> fa-lg" style="color: <?= $fileColor ?>;"></i>
                                </div>
                                <div class="flex-grow-1 me-3">
                                    <div class="fw-bold mb-0" style="font-size: 0.85rem; color: #2d3748;">
                                        <?= esc(ucwords(str_replace('_', ' ', $doc['tipe_dokumen']))) ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #718096;"><?= esc($doc['nama_file_original']) ?></div>
                                    <small style="font-size: 0.7rem; color: #a0aec0;">
                                        <i class="far fa-clock me-1"></i>Diunggah: <?= date('d M Y H:i', strtotime($doc['created_at'])) ?>
                                    </small>
                                </div>
                                <a href="<?= base_url('harmonisasi/download/' . $doc['id']) ?>"
                                   class="btn btn-sm rounded-pill px-3 fw-semibold"
                                   style="background: rgba(0,97,255,0.1); color: #0061ff; border: none; transition: all 0.3s ease;"
                                   onmouseover="this.style.background='#0061ff'; this.style.color='#fff';"
                                   onmouseout="this.style.background='rgba(0,97,255,0.1)'; this.style.color='#0061ff';"
                                   title="Unduh Dokumen">
                                    <i class="fas fa-download me-1"></i>Unduh
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x mb-3" style="color: #e2e8f0;"></i>
                        <p style="color: #a0aec0;">Belum ada dokumen yang diunggah</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Data TTE Result -->
            <?php if (isset($tte_data) && !empty($tte_data) && !empty($tte_data['tte_file_path'])): ?>
                <div class="glass-panel-light shadow-sm mb-4 p-4" id="tteResultCard" style="animation: fadeInUp 0.7s ease;">
                    <h6 class="fw-bold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                        <i class="fas fa-stamp me-2" style="color: #6f42c1;"></i>Hasil Tanda Tangan Elektronik (TTE)
                    </h6>
                    <div class="row g-0">
                        <div class="col-md-6">
                            <div class="info-slot">
                                <div class="info-slot-icon" style="background: rgba(111,66,193,0.08); color: #6f42c1;"><i class="fas fa-hashtag"></i></div>
                                <div class="info-slot-content">
                                    <span class="label">Nomor Peraturan</span>
                                    <span class="value"><?= esc($tte_data['nomor_peraturan'] ?? '-') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-slot">
                                <div class="info-slot-icon" style="background: rgba(111,66,193,0.08); color: #6f42c1;"><i class="fas fa-gavel"></i></div>
                                <div class="info-slot-content">
                                    <span class="label">Jenis Peraturan</span>
                                    <span class="value"><?= esc($tte_data['jenis_peraturan'] ?? '-') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-slot">
                                <div class="info-slot-icon" style="background: rgba(111,66,193,0.08); color: #6f42c1;"><i class="fas fa-calendar-check"></i></div>
                                <div class="info-slot-content">
                                    <span class="label">Tanggal Pengesahan</span>
                                    <span class="value"><?= isset($tte_data['tanggal_pengesahan']) ? date('d F Y', strtotime($tte_data['tanggal_pengesahan'])) : '-' ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-slot">
                                <div class="info-slot-icon" style="background: rgba(111,66,193,0.08); color: #6f42c1;"><i class="fas fa-clock"></i></div>
                                <div class="info-slot-content">
                                    <span class="label">TTE Completed At</span>
                                    <span class="value"><?= isset($tte_data['tte_completed_at']) ? date('d F Y H:i', strtotime($tte_data['tte_completed_at'])) : '-' ?></span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($tte_data['document_url'])): ?>
                        <div class="col-12 mt-3 px-3">
                            <a href="<?= esc($tte_data['document_url']) ?>" target="_blank" class="btn btn-sm rounded-pill px-4 fw-semibold" style="background: rgba(111,66,193,0.1); color: #6f42c1; border: none;">
                                <i class="fas fa-external-link-alt me-1"></i>Buka Dokumen TTE
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================================
             RIGHT COLUMN - RIWAYAT PROSES
             ============================================================ -->
        <div class="col-lg-4">
            <div class="glass-panel-light shadow-sm p-4 h-100" style="animation: fadeInUp 0.5s ease;">
                <h6 class="fw-bold text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-stream me-2" style="color: #fd7e14;"></i>Riwayat Proses
                </h6>
                <?php if (!empty($histori)): ?>
                    <?php
                        $totalItems = count($histori);
                        // Cek apakah status ajuan sudah final (Selesai/Ditolak)
                        $ajuanStatusLower = strtolower($ajuan['nama_status'] ?? '');
                        $isFinalized = (
                            strpos($ajuanStatusLower, 'selesai') !== false ||
                            strpos($ajuanStatusLower, 'ditolak') !== false ||
                            in_array($ajuan['id_status_ajuan'] ?? 0, [14, 15])
                        );
                    ?>
                    <ul class="stepper-timeline">
                        <?php foreach ($histori as $index => $item) : ?>
                            <?php
                                // Jika ajuan sudah selesai/ditolak, SEMUA tahap = completed
                                // Jika masih berjalan, item pertama (terbaru) = active, sisanya = completed
                                $stepState = $isFinalized ? 'completed' : (($index === 0) ? 'active' : 'completed');
                                
                                // Determine color based on status name
                                $markerColor = '#6c757d';
                                $statusName = strtolower($item['status_sekarang'] ?? '');
                                
                                if (strpos($statusName, 'validasi') !== false) {
                                    $markerColor = '#17a2b8';
                                } elseif (strpos($statusName, 'paraf') !== false) {
                                    $markerColor = '#fd7e14';
                                } elseif (strpos($statusName, 'selesai') !== false || strpos($statusName, 'tte') !== false) {
                                    $markerColor = '#28a745';
                                } elseif (strpos($statusName, 'revisi') !== false || strpos($statusName, 'ditolak') !== false) {
                                    $markerColor = '#dc3545';
                                } elseif (strpos($statusName, 'diajukan') !== false) {
                                    $markerColor = '#0061ff';
                                }
                            ?>
                            <li class="stepper-item <?= $stepState ?>">
                                <div class="stepper-marker" style="border-color: <?= $markerColor ?>; <?= $stepState === 'completed' ? 'background:' . $markerColor . ';' : '' ?>"></div>
                                <div class="stepper-content">
                                    <h6 class="stepper-title" style="color: <?= $markerColor ?>;">
                                        <?= esc($item['status_sekarang']) ?>
                                    </h6>
                                    <p class="stepper-desc">
                                        <?= esc($item['keterangan'] ?: 'Tidak ada keterangan') ?>
                                    </p>
                                    <div class="stepper-meta">
                                        <span><i class="far fa-calendar-alt me-1"></i><?= esc($item['tanggal_formatted']) ?></span>
                                        <span>&bull;</span>
                                        <span><i class="far fa-user me-1"></i><?= esc($item['nama_user']) ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-stream fa-3x mb-3" style="color: #e2e8f0;"></i>
                        <p style="color: #a0aec0;">Belum ada riwayat proses</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ============================================================
         ACTION BAR
         ============================================================ -->
    <div class="glass-panel-light shadow-sm p-4 mt-4" style="animation: fadeInUp 0.8s ease;">
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <!-- Back Button -->
            <?php if (isset($from_penugasan) && $from_penugasan): ?>
                <a href="<?= base_url('penugasan') ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-medium">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Penugasan
                </a>
            <?php else: ?>
                <a href="<?= base_url('harmonisasi') ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-medium">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            <?php endif; ?>

            <!-- User Action Buttons -->
            <?php if (
                isset($user_actions['can_submit']) && $user_actions['can_submit']
                && !in_array($ajuan['id_status_ajuan'], [5, 14, 15])
            ): ?>
                <form action="<?= base_url('harmonisasi/ajukan/' . $ajuan['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn rounded-pill px-4 fw-semibold text-white"
                        style="background: linear-gradient(135deg, #0061ff 0%, #60efff 100%); border: none; box-shadow: 0 4px 15px rgba(0,97,255,0.3);"
                        onclick="return confirm('Apakah Anda yakin ingin mengajukan draft ini?')">
                        <i class="fas fa-paper-plane me-2"></i>Ajukan Draft
                    </button>
                </form>
            <?php endif; ?>

            <?php if (
                isset($user_actions['can_edit']) && $user_actions['can_edit']
                && !in_array($ajuan['id_status_ajuan'], [5, 14, 15])
            ): ?>
                <a href="<?= base_url('harmonisasi/edit/' . $ajuan['id']) ?>" class="btn btn-warning text-dark rounded-pill px-4 fw-semibold" style="box-shadow: 0 4px 15px rgba(255,193,7,0.3);">
                    <i class="fas fa-edit me-2"></i>Edit Draft
                </a>
            <?php endif; ?>

            <?php if (isset($user_actions['can_assign']) && $user_actions['can_assign']): ?>
                <a href="<?= base_url('penugasan/tugaskan/' . $ajuan['id']) ?>" class="btn btn-info text-white rounded-pill px-4 fw-semibold" style="box-shadow: 0 4px 15px rgba(23,162,184,0.3);">
                    <i class="fas fa-user-plus me-2"></i>Tugaskan Verifikator
                </a>
            <?php endif; ?>

            <?php if (isset($user_actions['can_complete_verification']) && $user_actions['can_complete_verification']): ?>
                <form action="<?= base_url('harmonisasi/verifikasi-selesai/' . $ajuan['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white"
                        style="box-shadow: 0 4px 15px rgba(40,167,69,0.3);"
                        onclick="return confirm('Apakah Anda yakin ingin menandai verifikasi selesai?')">
                        <i class="fas fa-check-circle me-2"></i>Verifikasi Selesai
                    </button>
                </form>
            <?php endif; ?>

            <?php if (isset($user_actions['can_submit_revisi']) && $user_actions['can_submit_revisi'] && ($ajuan['nama_status'] === 'Revisi' || $ajuan['id_status_ajuan'] == 5)): ?>
                <a href="<?= base_url('harmonisasi/submitRevisi/' . $ajuan['id']) ?>" class="btn btn-warning text-dark rounded-pill px-4 fw-semibold" style="box-shadow: 0 4px 15px rgba(255,193,7,0.3);">
                    <i class="fas fa-upload me-2"></i>Upload Revisi
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>