<div class="container-fluid py-4 harmonisasi-module">
    
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 1rem; background: #d4edda; color: #155724;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <div><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 1rem; background: #f8d7da; color: #721c24;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                <div><?= esc(session()->getFlashdata('error')) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    $documentTTE = null;
    if (isset($dokumen) && is_array($dokumen)) {
        foreach ($dokumen as $doc) {
            if ($doc['tipe_dokumen'] === 'FINAL_TTE') {
                $documentTTE = $doc;
                break;
            }
        }
    }
    ?>

    <?php if (!isset($ajuan) || !is_array($ajuan)): ?>
        <div class="glass-panel-light p-5 text-center mt-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-soft-danger text-danger rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 2.5rem;">
                <i class="fas fa-search"></i>
            </div>
            <h4 class="text-dark font-outfit mb-3">Data Tidak Ditemukan</h4>
            <p class="text-muted mb-4 max-w-md mx-auto">Maaf, data ajuan yang Anda cari tidak tersedia dalam sistem. Mungkin sudah dihapus atau Anda tidak memiliki akses.</p>
            <a href="<?= base_url('legalisasi') ?>" class="btn px-4 py-2 rounded-pill fw-semibold shadow-sm" style="background: rgba(0,97,255,0.1); color: #0061ff; border: none;">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
    <?php else: ?>

        <!-- TTE Result Alert (if available) -->
        <?php if ($documentTTE || (isset($tte_log_fallback) && $tte_log_fallback)): ?>
            <div class="glass-panel-light shadow-sm mb-4 p-4 border-start border-4" style="animation: zoomIn 0.3s ease; border-color: #28a745 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                    <div class="d-flex align-items-center gap-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem; background: rgba(40,167,69,0.1); color: #28a745;">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1" style="color: #2d3748;">Dokumen Telah Ditandatangani</h5>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Dokumen ini telah melalui proses TTE dan saat ini bersifat resmi/final.</p>
                            <?php 
                            $nomorPeraturan = $documentTTE['document_number'] ?? $ajuan['document_number'] ?? $ajuan['document_number_final'] ?? null;
                            if ($nomorPeraturan): ?>
                                <div class="mt-2">
                                    <span class="badge rounded-pill px-3 py-2 fw-semibold shadow-sm" style="background: #28a745; color: white;">
                                        <i class="fas fa-hashtag me-1"></i>Nomor: <?= esc($nomorPeraturan) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <a href="<?= base_url('legalisasi/download/' . $ajuan['id']) ?>" class="btn rounded-pill px-4 py-2 shadow-sm fw-semibold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none;">
                            <i class="fas fa-download me-2"></i>Unduh PDF (TTE)
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================
             HERO HEADER
             ============================================================ -->
        <div class="hero-status-card mb-4" style="animation: fadeInUp 0.4s ease;">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="<?= base_url('legalisasi') ?>" class="text-decoration-none" style="color: #0061ff;">Legalisasi</a></li>
                            <li class="breadcrumb-item active text-muted" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                    <h2 class="hero-title mb-2 text-uppercase">
                        <?= esc($ajuan['judul_peraturan']) ?>
                    </h2>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge rounded-pill bg-white text-primary border px-3 py-2 shadow-sm">
                            <i class="fas fa-gavel me-1"></i> <?= esc($ajuan['nama_jenis']) ?>
                        </span>
                        
                        <?php
                        // Status handling
                        $statusId = $ajuan['id_status_ajuan'] ?? null;
                        $statusName = 'Unknown Status';
                        $statusColor = '#6c757d'; // default gray
                        $statusIcon = 'fa-info-circle';
                        
                        if ($statusId) {
                            if ($user_role === 'sekda' && isset($isKeputusanSekda) && $isKeputusanSekda && $statusId == 11) {
                                $statusName = 'Menunggu TTE Sekda (Final)';
                                $statusColor = '#0061ff';
                                $statusIcon = 'fa-stamp';
                            } else {
                                $statusName = \App\Config\HarmonisasiStatus::getStatusName($statusId);
                                $statusLower = strtolower($statusName);
                                
                                if (strpos($statusLower, 'selesai') !== false || strpos($statusLower, 'tte') !== false) {
                                    $statusColor = '#28a745'; $statusIcon = 'fa-check-double';
                                } elseif (strpos($statusLower, 'ditolak') !== false) {
                                    $statusColor = '#dc3545'; $statusIcon = 'fa-times-circle';
                                } elseif (strpos($statusLower, 'paraf') !== false) {
                                    $statusColor = '#fd7e14'; $statusIcon = 'fa-signature';
                                } else {
                                    $statusColor = '#0061ff'; $statusIcon = 'fa-sync-alt';
                                }
                            }
                        }
                        ?>
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: <?= $statusColor ?>15; color: <?= $statusColor ?>; font-size: 0.85rem; border: 1px solid <?= $statusColor ?>30;">
                            <i class="fas <?= $statusIcon ?> me-1"></i><?= ($statusName) ?>
                        </span>
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
                                <span class="fw-bold" style="font-size: 0.95rem; color: #2d3748;">
                                    <?= esc($ajuan['tanggal_pengajuan_formatted'] ?? '-') ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(108,117,125,0.1);">
                                <i class="fas fa-hashtag" style="color: #6c757d;"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">ID Pengajuan</small>
                                <span class="fw-bold font-monospace" style="font-size: 0.95rem; color: #2d3748;">
                                    #<?= esc($ajuan['id'] ?? $ajuan['id_ajuan'] ?? '-') ?>
                                </span>
                            </div>
                        </div>
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
                                    <span class="value"><?= esc($ajuan['nama_jenis'] ?? '-') ?></span>
                                </div>
                            </div>
                            <div class="info-slot">
                                <div class="info-slot-icon bg-soft-orange text-warning"><i class="fas fa-user-tie"></i></div>
                                <div class="info-slot-content">
                                    <span class="label">User Pemohon</span>
                                    <span class="value"><?= esc($ajuan['nama_pemohon'] ?? '-') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-slot">
                                <div class="info-slot-icon bg-soft-green text-success"><i class="far fa-building"></i></div>
                                <div class="info-slot-content">
                                    <span class="label">Instansi Pemohon</span>
                                    <span class="value"><?= esc($ajuan['nama_instansi'] ?? '-') ?></span>
                                </div>
                            </div>
                            <div class="info-slot">
                                <div class="info-slot-icon bg-secondary bg-opacity-10 text-secondary"><i class="fas fa-user-shield"></i></div>
                                <div class="info-slot-content">
                                    <span class="label">Petugas Verifikasi</span>
                                    <span class="value <?= empty($ajuan['nama_verifikator']) ? 'text-muted fst-italic' : '' ?>">
                                        <?= esc($ajuan['nama_verifikator'] ?: 'Belum Ditugaskan') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($ajuan['keterangan'])): ?>
                        <div class="mt-3 p-3 rounded-3" style="background: rgba(255,193,7,0.1); border-left: 3px solid #ffc107;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-comment-dots text-warning mt-1"></i>
                                <div>
                                    <span class="d-block text-dark fw-bold mb-1" style="font-size: 0.85rem;">Catatan Pengajuan</span>
                                    <p class="text-muted mb-0 fst-italic" style="font-size: 0.9rem;">"<?= nl2br(esc($ajuan['keterangan'])) ?>"</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- DOKUMEN LIST -->
                <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.6s ease;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                            <i class="fas fa-paperclip me-2" style="color: #28a745;"></i>Dokumen Terlampir
                        </h6>
                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                            <?= isset($dokumen) ? count($dokumen) : 0 ?> file
                        </span>
                    </div>
                    
                    <?php if (empty($dokumen)): ?>
                        <div class="text-center py-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: rgba(108, 117, 125, 0.1);">
                                <i class="fas fa-folder-open fa-2x text-muted"></i>
                            </div>
                            <p class="text-muted mb-0">Tidak ada dokumen yang dilampirkan.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($dokumen as $doc) : ?>
                                <?php
                                    // Use 'file_dokumen' if 'nama_file_original' isn't available
                                    $fileName = $doc['nama_file_original'] ?? $doc['file_dokumen'] ?? 'Dokumen';
                                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                    $icon = 'fa-file-alt';
                                    $iconColor = '#6c757d';
                                    $bgSoft = 'rgba(108,117,125,0.1)';
                                    
                                    if (in_array($ext, ['pdf'])) {
                                        $icon = 'fa-file-pdf';
                                        $iconColor = '#dc3545';
                                        $bgSoft = 'rgba(220,53,69,0.1)';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        $icon = 'fa-file-word';
                                        $iconColor = '#0d6efd';
                                        $bgSoft = 'rgba(13,110,253,0.1)';
                                    }
                                ?>
                                <div class="d-flex align-items-center p-3 rounded-4" style="border: 1px solid rgba(0,0,0,0.05); background: #fdfdfe; transition: all 0.2s ease;">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px; background: <?= $bgSoft ?>;">
                                        <i class="fas <?= $icon ?> fs-4" style="color: <?= $iconColor ?>;"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="mb-1 text-truncate fw-bold" style="font-size: 0.9rem; color: #2d3748;">
                                            <?= esc(ucwords(str_replace('_', ' ', $doc['tipe_dokumen'] ?? 'Dokumen'))) ?>
                                        </h6>
                                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.8rem;">
                                            <span class="text-truncate" style="max-width: 200px;" title="<?= esc($fileName) ?>"><?= esc($fileName) ?></span>
                                            <span>&bull;</span>
                                            <span><i class="far fa-clock me-1"></i><?= isset($doc['created_at']) ? date('d M Y H:i', strtotime($doc['created_at'])) : '-' ?></span>
                                        </div>
                                    </div>
                                    <div class="ms-3 d-flex gap-2 flex-shrink-0">
                                        <button type="button" class="btn btn-sm rounded-circle px-2" style="background: rgba(108,117,125,0.1); color: #6c757d; border: none; width: 32px; height: 32px;"
                                            onclick="previewPDF('<?= base_url('legalisasi/preview/' . ($doc['id'] ?? $ajuan['id'])) ?>', '<?= esc($fileName) ?>')"
                                            title="Preview Dokumen">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($doc['tipe_dokumen'] === 'FINAL_TTE'): ?>
                                            <a href="<?= base_url('legalisasi/download/' . $ajuan['id']) ?>" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold" style="background: rgba(40,167,69,0.1); color: #28a745; border: none;" title="Unduh TTE">
                                                <i class="fas fa-download me-1"></i>Unduh
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ============================================================
                 RIGHT COLUMN
                 ============================================================ -->
            <div class="col-lg-4">
                
                <!-- ACTION FLOATING WRAPPER (Right Side Instead of Bottom) -->
                <?php if (isset($user_actions) && ($user_actions['can_process_tte'] || $user_actions['can_process_paraf'] || $user_actions['can_revise_to_finalisasi'])): ?>
                    <div class="glass-panel-light shadow-sm mb-4 p-4 d-flex flex-column gap-3" style="border: 2px solid rgba(0, 97, 255, 0.1); animation: fadeInUp 0.7s ease;">
                        <h6 class="fw-bold text-uppercase text-center mb-1" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #0061ff;">
                            <i class="fas fa-bolt me-2"></i>Aksi Tindakan
                        </h6>
                        <hr class="mt-0 mb-2 border-primary border-opacity-25">
                        
                        <?php if (isset($user_actions['can_process_paraf']) && $user_actions['can_process_paraf']): ?>
                            <?php
                            $paraf_text = 'Proses Paraf';
                            $roles_next = ['sekda' => 'Wawako', 'wawako' => 'Walikota', 'kabag' => 'Asisten', 'asisten' => 'Sekda', 'opd' => 'Kabag'];
                            if (isset($roles_next[$user_role])) $paraf_text = "Paraf (Lanjut ke " . $roles_next[$user_role] . ")";
                            ?>
                            <button type="button" class="btn rounded-pill px-3 py-3 fw-semibold text-white w-100" style="background: linear-gradient(135deg, #0061ff 0%, #0052cc 100%); border: none; box-shadow: 0 4px 15px rgba(0,97,255,0.3); transition: all 0.3s ease;" onclick="processParaf(<?= $ajuan['id'] ?>)">
                                <i class="fas fa-signature me-2"></i> <?= $paraf_text ?>
                            </button>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_process_tte']) && $user_actions['can_process_tte']): ?>
                            <?php
                            $tte_text = 'Proses TTE';
                            if ($user_role === 'sekda' && isset($isKeputusanSekda) && $isKeputusanSekda) $tte_text = 'Proses TTE Sekda (Final)';
                            elseif ($user_role === 'sekda') $tte_text = 'Proses TTE (Sekda)';
                            elseif ($user_role === 'walikota') $tte_text = 'Proses TTE (Walikota)';
                            ?>
                            <button type="button" class="btn rounded-pill px-3 py-3 fw-semibold text-white w-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 4px 15px rgba(16,185,129,0.3); transition: all 0.3s ease;" onclick="<?= ($user_role === 'sekda' && isset($isKeputusanSekda) && $isKeputusanSekda) ? 'processTTESekda' : 'processTTE' ?>(<?= $ajuan['id'] ?>)">
                                <i class="fas fa-stamp me-2"></i> <?= $tte_text ?>
                            </button>
                        <?php endif; ?>

                        <?php if (isset($user_actions['can_revise_to_finalisasi']) && $user_actions['can_revise_to_finalisasi']): ?>
                            <button type="button" class="btn rounded-pill px-3 py-2 fw-semibold w-100" style="background: #fff3cd; color: #856404; border: 1px solid #ffeeba; transition: all 0.3s ease;" onclick="revisiKeFinalisasi(<?= $ajuan['id'] ?>)">
                                <i class="fas fa-undo me-2"></i> Revisi
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- TIMELINE CARD -->
                <div class="glass-panel-light shadow-sm mb-4 p-4 h-100" style="animation: fadeInUp 0.8s ease;">
                    <h6 class="fw-bold text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                        <i class="fas fa-history me-2"></i>Riwayat Proses
                    </h6>
                    
                    <?php if (empty($histori)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-stream fa-2x text-muted opacity-50 mb-3"></i>
                            <p class="text-muted small">Belum ada riwayat proses.</p>
                        </div>
                    <?php else: ?>
                        <div class="stepper-timeline">
                            <?php 
                            $totalItems = count($histori);
                            $showLimit = 5;
                            ?>
                            <!-- Existing History Logic -->
                            <?php foreach ($histori as $index => $item): ?>
                                <?php 
                                $stepState = ($index === 0) ? 'active' : 'completed';
                                if ($index === 0 && in_array($ajuan['id_status_ajuan'], [\App\Config\HarmonisasiStatus::SELESAI, \App\Config\HarmonisasiStatus::DITOLAK])) {
                                    $stepState = 'completed';
                                }
                                
                                $statusNameLower = strtolower($item['status_sekarang'] ?? '');
                                $colorClass = 'primary';
                                if (strpos($statusNameLower, 'tolak') !== false || strpos($statusNameLower, 'revisi') !== false) {
                                    $colorClass = 'danger';
                                } elseif (strpos($statusNameLower, 'selesai') !== false || strpos($statusNameLower, 'tte') !== false) {
                                    $colorClass = 'success';
                                } elseif (strpos($statusNameLower, 'paraf') !== false) {
                                    $colorClass = 'warning';
                                } elseif (strpos($statusNameLower, 'validasi') !== false) {
                                    $colorClass = 'info';
                                }
                                ?>
                                <div class="stepper-item <?= $stepState ?> <?= $index >= $showLimit ? 'stepper-item-hidden d-none' : '' ?>" data-color="<?= $colorClass ?>">
                                    <div class="stepper-marker"></div>
                                    <div class="stepper-content ms-2 pb-3">
                                        <div class="stepper-header d-flex justify-content-between align-items-start mb-1">
                                            <span class="stepper-title fw-bold text-dark lh-sm" style="font-size: 0.9rem;"><?= esc($item['status_sekarang'] ?? 'Update') ?></span>
                                            <span class="stepper-date text-muted flex-shrink-0" style="font-size: 0.75rem;"><i class="fas fa-clock me-1"></i><?= esc($item['tanggal_formatted'] ?? 'N/A') ?></span>
                                        </div>
                                        <div class="stepper-desc text-muted mb-2" style="font-size: 0.85rem;"><?= esc($item['keterangan'] ?? 'Tanpa keterangan') ?></div>
                                        <div class="stepper-meta">
                                            <span class="badge rounded-pill" style="background: rgba(0,0,0,0.05); color: #4a5568; font-size: 0.75rem; border: 1px solid rgba(0,0,0,0.1);">
                                                <i class="fas fa-user-circle me-1"></i><?= esc($item['nama_user'] ?? 'Sistem') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($totalItems > $showLimit): ?>
                            <button class="btn btn-sm w-100 mt-3 rounded-pill fw-semibold" id="showMoreStepperBtn" style="background: rgba(0,97,255,0.1); color: #0061ff; border: none;">
                                <i class="fas fa-chevron-down me-2"></i>Tampilkan Semua (<?= $totalItems ?>)
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    <?php endif; ?>
</div>

<!-- TTE Loading Overlay -->
<div id="tteLoadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
    <div style="text-align: center; background: white; padding: 40px; border-radius: 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 400px; border: 1px solid rgba(255,255,255,0.2);">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem; border-width: 0.4rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <h4 class="mt-4 mb-2 font-outfit" style="color: #333;">Memproses TTE</h4>
        <p class="text-muted mb-0 small">Menghubungi server BSrE, mohon tunggu...</p>
        <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">Proses ini membutuhkan beberapa detik</small>
    </div>
</div>

<!-- Scripts -->
<script>
    // PDF Preview Modal
    function previewPDF(url, fileName) {
        let $bootbox = bootbox.dialog({
            title: '<i class="fas fa-file-pdf text-danger me-2"></i>Preview: ' + fileName,
            message: '<div class="text-center py-5"><div class="spinner-border text-blue" role="status"></div><p class="mt-3 text-muted">Memuat dokumen...</p></div>',
            size: 'extra-large',
            className: 'pdf-preview-modal animate__animated animate__zoomIn',
            buttons: {
                cancel: { label: 'Tutup', className: 'btn-soft-dark px-4 rounded-pill' }
            }
        });

        setTimeout(() => {
            $bootbox.find('.modal-body').html(`
                <div class="pdf-preview-container">
                    <iframe src="${url}#toolbar=1&view=FitH" 
                            style="width: 100%; height: calc(100vh - 250px); min-height: 700px; border: 1px solid #e2e8f0; border-radius: 0.5rem;"
                            title="PDF Preview">
                    </iframe>
                </div>
            `);
        }, 300);
    }

    $(document).ready(function() {
        // Toggle stepper history
        $('#showMoreStepperBtn').on('click', function() {
            $('.stepper-item-hidden').hide().removeClass('d-none').fadeIn(400);
            $(this).fadeOut(300);
        });
    });

    // TTE Processing with SWAL
    let lastTTERequest = 0;
    const TTE_COOLDOWN = 5000;
    let currentAjuanId = null;
    let currentBootbox = null;

    function processTTE(ajuanId) {
        const now = Date.now();
        if (now - lastTTERequest < TTE_COOLDOWN) {
            Swal.fire('Mohon Tunggu', 'Tunggu sebentar sebelum melakukan aksi lagi.', 'warning');
            return;
        }
        lastTTERequest = now;
        currentAjuanId = ajuanId;
        showTTEBootbox();
    }

    function processTTESekda(ajuanId) {
        processTTE(ajuanId);
    }

    function showTTEBootbox() {
        const tteContent = `
            <form id="tteForm" class="p-2">
                <div class="row g-3">
                    <div class="col-md-6 text-start">
                        <label class="form-label text-muted small fw-bold">NIK (16 Digit)</label>
                        <input type="text" class="form-control rounded-pill px-3" id="tte_nik" placeholder="0000000000000000" maxlength="16">
                    </div>
                    <div class="col-md-6 text-start">
                        <label class="form-label text-muted small fw-bold">Passphrase TTE</label>
                        <div class="input-group">
                            <input type="password" class="form-control rounded-pill-start px-3" style="border-right:0;" id="tte_password" placeholder="********">
                            <span class="input-group-text bg-white rounded-pill-end pe-3" style="cursor: pointer; border-left:0;" id="togglePasswordIcon" onclick="togglePasswordVisibility()">
                                <i class="fas fa-eye text-muted"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="tteVerificationResult" class="mt-3"></div>
            </form>
        `;

        currentBootbox = bootbox.dialog({
            title: '<h5 class="mb-0 font-outfit fw-bold"><i class="fas fa-stamp text-blue me-2"></i>Verifikasi Sertifikat BSrE</h5>',
            message: tteContent,
            size: 'large',
            className: 'animate__animated animate__zoomIn tte-auth-modal',
            buttons: {
                verify: {
                    label: '<i class="fas fa-search me-1"></i> Cek Sertifikat',
                    className: 'btn rounded-pill px-4 text-white',
                    style: 'background: #0061ff; border: none;',
                    callback: function() { verifyTteCertificate(); return false; }
                },
                proceed: {
                    label: '<i class="fas fa-signature me-1"></i> Tanda Tangani',
                    className: 'btn rounded-pill px-4 text-white',
                    style: 'background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: none;',
                    id: 'btnProceedTte',
                    callback: function() { proceedTTE(); return false; }
                }
            }
        });
    }

    function togglePasswordVisibility() {
        const pwd = document.getElementById('tte_password');
        const ico = document.querySelector('#togglePasswordIcon i');
        if (pwd.type === 'password') { pwd.type = 'text'; ico.className = 'fas fa-eye-slash text-muted'; }
        else { pwd.type = 'password'; ico.className = 'fas fa-eye text-muted'; }
    }

    function verifyTteCertificate() {
        const res = document.getElementById('tteVerificationResult');
        const nik = document.getElementById('tte_nik')?.value;
        if (!nik || !/^\d{16}$/.test(nik)) {
            res.innerHTML = '<div class="alert alert-danger bg-soft-danger border-0 small"><i class="fas fa-exclamation-circle me-2"></i>NIK harus 16 digit angka.</div>';
            return;
        }
        res.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-blue"></div><span class="ms-2 small text-muted">Memeriksa BSrE...</span></div>';
        
        fetch('<?= base_url('api/tte/check-status') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nik: nik })
        })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success' && d.data?.is_active) {
                res.innerHTML = '<div class="alert alert-success bg-soft-green text-green border-0 small"><i class="fas fa-check-circle me-2"></i>Sertifikat Aktif. Silakan masukkan passphrase.</div>';
                $('#btnProceedTte').fadeIn();
            } else {
                res.innerHTML = '<div class="alert alert-danger bg-soft-danger text-danger border-0 small"><i class="fas fa-times-circle me-2"></i>' + (d.message || 'Sertifikat tidak aktif.') + '</div>';
            }
        });
    }

    function proceedTTE() {
        const nik = $('#tte_nik').val();
        const pwd = $('#tte_password').val();
        if (!pwd) { Swal.fire('Error', 'Passphrase harus diisi!', 'error'); return; }

        Swal.fire({
            title: 'Konfirmasi TTE',
            text: 'Dokumen akan ditandatangani secara elektronik. Tindakan ini tidak dapat dibatalkan. Lanjutkan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: '<i class="fas fa-check me-2"></i>Ya, Jalankan TTE',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn rounded-pill px-4', cancelButton: 'btn btn-light px-4 rounded-pill border' },
            buttonsStyling: false
        }).then((res) => {
            if (res.isConfirmed) {
                $('#tteVerificationResult').html('<div class="alert alert-warning bg-soft-warning border-0 small animation-pulse"><i class="fas fa-spinner fa-spin me-2"></i>Menghubungi Server BSrE...</div>');
                $('#btnProceedTte').prop('disabled', true);
                currentBootbox.modal('hide');
                $('#tteLoadingOverlay').css('display', 'flex'); 

                fetch('<?= base_url('legalisasi/getFinalParafDocument') ?>/' + currentAjuanId)
                .then(r => r.json())
                .then(doc => {
                    return fetch('<?= base_url('api/tte/sign') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_ajuan: currentAjuanId, nik: nik, password: pwd, id_dokumen: doc.id_dokumen })
                    });
                })
                .then(r => r.json())
                .then(d => {
                    $('#tteLoadingOverlay').hide();
                    if (d.status === 'success') {
                        Swal.fire({
                            title: 'TTE Berhasil!', 
                            text: 'Dokumen telah sah ditandatangani secara elektronik.', 
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            customClass: { confirmButton: 'btn btn-success px-4 rounded-pill' }
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            title: 'TTE Gagal', 
                            text: d.message || 'Error server BSrE', 
                            icon: 'error',
                            customClass: { confirmButton: 'btn btn-danger px-4 rounded-pill' }
                        });
                        if(currentBootbox) currentBootbox.modal('show');
                        $('#btnProceedTte').prop('disabled', false);
                    }
                })
                .catch(err => {
                    $('#tteLoadingOverlay').hide();
                    Swal.fire('Error Sistim', 'Terjadi kegagalan koneksi. Silahkan coba lagi.', 'error');
                });
            }
        });
    }

    function processParaf(ajuanId) {
        Swal.fire({
            title: 'Proses Paraf',
            text: 'Apakah Anda yakin ingin memberikan paraf pada ajuan ini?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-pen-nib me-2"></i>Berikan Paraf',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-primary px-4 rounded-pill', cancelButton: 'btn btn-light px-4 border rounded-pill' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                const formData = new FormData();
                formData.append('ajuan_id', ajuanId);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                fetch('<?= base_url('legalisasi/processParaf') ?>/' + ajuanId, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire({
                            title: 'Berhasil!', 
                            text: 'Paraf telah diproses dan pengajuan diteruskan.', 
                            icon: 'success',
                            customClass: { confirmButton: 'btn btn-success px-4 rounded-pill' }
                        }).then(() => window.location.href = '<?= base_url('legalisasi') ?>');
                    } else {
                        Swal.fire('Gagal', d.message || 'Terjadi kesalahan sistem.', 'error');
                    }
                }).catch(() => {
                    Swal.fire('Error', 'Kesalahan komunikasi server.', 'error');
                });
            }
        });
    }

    function revisiKeFinalisasi(ajuanId) {
        Swal.fire({
            title: 'Catatan Revisi',
            html: `
                <p class="text-muted small mb-2 text-start">Ajuan akan dikembalikan ke tahap Finalisasi dengan catatan berikut:</p>
                <textarea id="swal-revisi-input" class="swal2-textarea" placeholder="Tuliskan poin-poin yang perlu diperbaiki..." style="min-height: 120px; font-size: 0.9rem; border-radius: 10px;"></textarea>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane me-2"></i>Kirim Revisi',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-warning px-4 rounded-pill fw-medium text-dark', cancelButton: 'btn btn-light px-4 border rounded-pill' },
            buttonsStyling: false,
            preConfirm: () => {
                const txt = document.getElementById('swal-revisi-input').value;
                if (!txt) { Swal.showValidationMessage('Catatan revisi wajib diisi!'); }
                return txt;
            }
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.showLoading();
                const formData = new FormData();
                formData.append('ajuan_id', ajuanId);
                formData.append('catatan', res.value);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                fetch('<?= base_url('legalisasi/revisiToFinalisasi') ?>/' + ajuanId, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire({
                            title: 'Berhasil!', 
                            text: 'Ajuan telah dikembalikan ke Finalisasi untuk direvisi.', 
                            icon: 'success',
                            customClass: { confirmButton: 'btn btn-success px-4 rounded-pill' }
                        }).then(() => window.location.href = '<?= base_url('legalisasi') ?>');
                    } else {
                        Swal.fire('Gagal', d.message || 'Error sistem', 'error');
                    }
                }).catch(() => {
                    Swal.fire('Error', 'Kesalahan komunikasi server.', 'error');
                });
            }
        });
    }

    // Modal CSS Enhancement
    const customModalCss = `
    <style>
        .tte-auth-modal .modal-content { border-radius: 1.5rem; overflow: hidden; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .tte-auth-modal .modal-header { background: #fdfdfe; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1.5rem; }
        .tte-auth-modal .modal-footer { border-top: 1px solid rgba(0,0,0,0.05); padding: 1.25rem 1.5rem; background: #fdfdfe; }
        .rounded-pill-start { border-top-left-radius: 50rem !important; border-bottom-left-radius: 50rem !important; }
        .rounded-pill-end { border-top-right-radius: 50rem !important; border-bottom-right-radius: 50rem !important; }
        .swal2-popup { border-radius: 1.25rem !important; }
        .animation-pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    </style>`;
    document.head.insertAdjacentHTML('beforeend', customModalCss);
</script>