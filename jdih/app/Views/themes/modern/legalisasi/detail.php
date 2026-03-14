<div class="legalisasi-module detail-view animate__animated animate__fadeIn">
    <div class="container-fluid">
        <!-- Premium Header -->
        <div class="detail-header mb-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left">
                    <div class="d-flex align-items-center mb-2">
                        <div class="back-link me-3">
                            <a href="<?= base_url('legalisasi') ?>" class="btn btn-soft-blue rounded-circle">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        <h1 class="h2 fw-bold text-dark font-outfit mb-0">Detail Ajuan Legalisasi</h1>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 ms-5 px-1">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('legalisasi') ?>">Legalisasi</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </nav>
                </div>
                <div class="header-right d-flex gap-2">
                    <div class="status-badge-premium">
                        <?php
                        $statusId = $ajuan['id_status_ajuan'] ?? null;
                        if ($statusId) {
                            $statusName = \App\Config\HarmonisasiStatus::getStatusName($statusId);
                            $badgeClass = 'status-green'; $icon = 'fa-check-circle';
                            
                            if ($user_role === 'sekda' && $isKeputusanSekda && $statusId == 11) {
                                $badgeClass = 'status-blue'; $icon = 'fa-stamp';
                                $statusName = 'Menunggu TTE Sekda (Final)';
                            }
                            ?>
                            <div class="badge-wrapper <?= $badgeClass ?>">
                                <i class="fas <?= $icon ?> me-2"></i><?= esc($statusName) ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-premium-success alert-dismissible fade show mb-4 animate__animated animate__slideInDown" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fs-4 me-3"></i>
                    <div><?= esc(session()->getFlashdata('success')) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-premium-danger alert-dismissible fade show mb-4 animate__animated animate__slideInDown" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                    <div><?= esc(session()->getFlashdata('error')) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

        <?php if ($documentTTE || (isset($tte_log_fallback) && $tte_log_fallback)): ?>
            <!-- TTE Result Card -->
            <div class="glass-card mb-5 border-start border-green border-4 animate__animated animate__fadeIn">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                        <div class="d-flex align-items-center gap-4">
                            <div class="tte-result-icon bg-soft-green text-green rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Dokumen Telah Ditandatangani</h4>
                                <p class="text-muted mb-0">Dokumen ini telah melalui proses TTE dan saat ini bersifat resmi/final.</p>
                                <?php 
                                $nomorPeraturan = $documentTTE['document_number'] ?? $ajuan['document_number'] ?? $ajuan['document_number_final'] ?? null;
                                if ($nomorPeraturan): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-green-premium px-3 py-2 rounded-pill shadow-sm">
                                            <i class="fas fa-hashtag me-2"></i>Nomor: <?= esc($nomorPeraturan) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="tte-result-actions">
                            <a href="<?= base_url('legalisasi/download/' . $ajuan['id']) ?>" class="btn btn-green-premium btn-lg px-5 rounded-pill shadow-hover">
                                <i class="fas fa-download me-2"></i>Download Dokumen Hasil TTE
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!isset($ajuan) || !is_array($ajuan)): ?>
            <div class="glass-card text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-dark">Data Tidak Ditemukan</h4>
                <p class="text-muted">Maaf, data ajuan yang Anda cari tidak tersedia dalam sistem.</p>
                <a href="<?= base_url('legalisasi') ?>" class="btn btn-blue-premium mt-3">Kembali ke Dashboard</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Main Information Card -->
                    <div class="glass-card info-section mb-4">
                        <div class="card-header-premium border-bottom">
                            <h5 class="mb-0 font-outfit text-blue">
                                <i class="fas fa-info-circle me-2"></i>Informasi Pengajuan
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="info-group mb-4">
                                        <label class="info-label">Judul Peraturan</label>
                                        <div class="info-value fw-bold fs-5 text-dark"><?= esc($ajuan['judul_peraturan'] ?? '-') ?></div>
                                    </div>
                                    <div class="info-group mb-4">
                                        <label class="info-label">Jenis Peraturan</label>
                                        <div class="info-value">
                                            <?php if (!empty($ajuan['nama_jenis'])): ?>
                                                <span class="badge bg-soft-blue text-blue fs-6 px-3 py-2 border">
                                                    <?= esc($ajuan['nama_jenis']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">ID: <?= esc($ajuan['id_jenis_peraturan'] ?? '-') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <label class="info-label">ID Ajuan</label>
                                        <div class="info-value text-muted font-monospace">#<?= esc($ajuan['id'] ?? $ajuan['id_ajuan'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group mb-4">
                                        <label class="info-label">Instansi Pemohon</label>
                                        <div class="info-value d-flex align-items-center">
                                            <div class="mini-icon bg-soft-blue text-blue me-2"><i class="fas fa-building"></i></div>
                                            <span class="text-dark fw-medium"><?= esc($ajuan['nama_instansi'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                    <div class="info-group mb-4">
                                        <label class="info-label">User Pemohon</label>
                                        <div class="info-value d-flex align-items-center">
                                            <div class="mini-icon bg-soft-green text-green me-2"><i class="fas fa-user-circle"></i></div>
                                            <span class="text-dark fw-medium"><?= esc($ajuan['nama_pemohon'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <label class="info-label">Tanggal Pengajuan</label>
                                        <div class="info-value d-flex align-items-center">
                                            <div class="mini-icon bg-soft-purple text-purple me-2"><i class="fas fa-calendar-alt"></i></div>
                                            <span class="text-muted"><?= esc($ajuan['tanggal_pengajuan_formatted'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($ajuan['keterangan'])): ?>
                                <div class="keterangan-box mt-4 p-3 rounded-3 bg-light border-start border-blue border-4">
                                    <label class="info-label mb-2 d-block">Catatan / Keterangan:</label>
                                    <p class="text-muted italic mb-0">"<?= nl2br(esc($ajuan['keterangan'])) ?>"</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Documents Table -->
                    <div class="glass-card mb-4 document-section">
                        <div class="card-header-premium border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 font-outfit text-indigo">
                                <i class="fas fa-paperclip me-2"></i>Dokumen Final
                            </h5>
                            <?php if (isset($dokumen) && is_array($dokumen)): ?>
                                <span class="badge bg-soft-indigo text-indigo border"><?= count($dokumen) ?> File</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0">
                            <?php if (isset($dokumen) && !empty($dokumen)): ?>
                                <div class="table-responsive">
                                    <table class="table table-premium mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tipe & Nama File</th>
                                                <th class="text-center">Tgl Unggah</th>
                                                <th class="text-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dokumen as $doc) : ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="file-icon bg-light rounded-3 p-2 me-3">
                                                                <i class="fas fa-file-pdf text-danger fs-4"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark mb-0"><?= esc(ucwords(str_replace('_', ' ', $doc['tipe_dokumen'] ?? 'Dokumen'))) ?></div>
                                                                <div class="text-muted small text-truncate" style="max-width: 250px;"><?= esc($doc['nama_file_original'] ?? $doc['file_dokumen'] ?? 'Unknown file') ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="small text-muted"><?= isset($doc['created_at']) ? date('d M Y', strtotime($doc['created_at'])) : '-' ?></div>
                                                        <div class="text-muted tiny mt-1"><?= isset($doc['created_at']) ? date('H:i', strtotime($doc['created_at'])) : '' ?> WIB</div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-flex justify-content-end gap-2 px-3">
                                                            <button type="button" class="btn btn-action-circle btn-soft-blue"
                                                                onclick="previewPDF('<?= base_url('legalisasi/preview/' . ($doc['id'] ?? $ajuan['id'])) ?>', '<?= esc($doc['nama_file_original'] ?? 'Dokumen') ?>')"
                                                                title="Preview PDF">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if ($doc['tipe_dokumen'] === 'FINAL_TTE'): ?>
                                                                <a href="<?= base_url('legalisasi/download/' . $ajuan['id']) ?>"
                                                                    class="btn btn-action-circle btn-soft-green" title="Download TTE">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 opacity-75">
                                    <div class="empty-docs-icon mb-3">
                                        <i class="fas fa-folder-open fa-3x text-muted"></i>
                                    </div>
                                    <p class="text-muted mb-0">Belum ada dokumen final yang diunggah.</p>
                                    <small class="text-muted">Dokumen ini akan tersedia setelah proses paraf selesai.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- History Timeline Card -->
                    <div class="glass-card history-section h-100">
                        <div class="card-header-premium border-bottom">
                            <h5 class="mb-0 font-outfit text-dark">
                                <i class="fas fa-history me-2 text-muted"></i>Riwayat Proses
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if (isset($histori) && !empty($histori)): ?>
                                <div class="premium-timeline">
                                    <?php
                                    $totalItems = count($histori);
                                    $showLimit = 5;
                                    ?>
                                    <?php foreach ($histori as $index => $item) : ?>
                                        <div class="timeline-box <?= $index >= $showLimit ? 'timeline-item-hidden' : '' ?>" data-index="<?= $index ?>">
                                            <div class="timeline-node"></div>
                                            <div class="timeline-info">
                                                <div class="timeline-header d-flex justify-content-between align-items-start mb-1">
                                                    <div class="timeline-status fw-bold text-dark small text-uppercase letter-spacing-1"><?= esc($item['status_sekarang'] ?? 'Update') ?></div>
                                                    <div class="timeline-date tiny text-muted"><?= date('d M', strtotime($item['tanggal_aksi'])) ?></div>
                                                </div>
                                                <div class="timeline-desc text-muted mb-2"><?= esc($item['keterangan'] ?? 'Tanpa keterangan') ?></div>
                                                <div class="timeline-footer d-flex align-items-center">
                                                    <div class="timeline-user badge bg-soft-dark text-dark tiny px-2 py-1">
                                                        <i class="fas fa-user-circle me-1"></i><?= esc($item['nama_user']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($totalItems > $showLimit): ?>
                                    <button class="btn btn-link btn-sm w-100 mt-3 text-blue" id="showMoreBtn">
                                        <i class="fas fa-chevron-down me-1"></i>Lihat Lebih Banyak
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-5 opacity-50">
                                    <i class="fas fa-stream fa-3x mb-3"></i>
                                    <p class="small">Belum ada data riwayat.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="action-footer-bar mt-5 animate__animated animate__fadeInUp">
                <div class="glass-card p-3 shadow-premium">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="footer-left">
                            <a href="<?= base_url('legalisasi') ?>" class="btn btn-outline-dark px-4 rounded-pill">
                                <i class="fas fa-arrow-left me-2"></i>Dashboard
                            </a>
                        </div>
                        <div class="footer-right d-flex gap-2">
                            <?php if (isset($user_actions)): ?>
                                <?php if (isset($user_actions['can_process_tte']) && $user_actions['can_process_tte']): ?>
                                    <?php
                                    $tte_text = 'Proses TTE';
                                    if ($user_role === 'sekda' && $isKeputusanSekda) $tte_text = 'Proses TTE Sekda (Final)';
                                    elseif ($user_role === 'sekda') $tte_text = 'Proses TTE (Sekda)';
                                    elseif ($user_role === 'walikota') $tte_text = 'Proses TTE (Walikota)';
                                    ?>
                                    <button type="button" class="btn btn-green-premium px-4 rounded-pill shadow-sm" onclick="<?= ($user_role === 'sekda' && $isKeputusanSekda) ? 'processTTESekda' : 'processTTE' ?>(<?= $ajuan['id'] ?>)">
                                        <i class="fas fa-stamp me-2"></i> <?= $tte_text ?>
                                    </button>
                                <?php endif; ?>

                                <?php if (isset($user_actions['can_process_paraf']) && $user_actions['can_process_paraf']): ?>
                                    <?php
                                    $paraf_text = 'Proses Paraf';
                                    $roles_next = ['sekda' => 'Wawako', 'wawako' => 'Walikota', 'kabag' => 'Asisten', 'asisten' => 'Sekda', 'opd' => 'Kabag'];
                                    if (isset($roles_next[$user_role])) $paraf_text = "Paraf (Lanjut ke " . $roles_next[$user_role] . ")";
                                    ?>
                                    <button type="button" class="btn btn-blue-premium px-4 rounded-pill shadow-sm" onclick="processParaf(<?= $ajuan['id'] ?>)">
                                        <i class="fas fa-signature me-2"></i> <?= $paraf_text ?>
                                    </button>
                                <?php endif; ?>

                                <?php if (isset($user_actions['can_revise_to_finalisasi']) && $user_actions['can_revise_to_finalisasi']): ?>
                                    <button type="button" class="btn btn-soft-warning px-4 rounded-pill border" onclick="revisiKeFinalisasi(<?= $ajuan['id'] ?>)">
                                        <i class="fas fa-undo me-2"></i> Revisi
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

    <!-- TTE Loading Overlay -->
    <div id="tteLoadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
        <div style="text-align: center; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem; border-width: 0.4rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <h4 class="mt-4 mb-2" style="color: #333;">Memproses Tanda Tangan Elektronik</h4>
            <p class="text-muted mb-0">Menghubungi server BSrE, mohon tunggu...</p>
            <small class="text-muted d-block mt-2">Proses ini membutuhkan beberapa detik</small>
        </div>
    </div>

    <style>
        :root {
            --blue-premium: #2563eb;
            --blue-soft: #eff6ff;
            --indigo-premium: #4f46e5;
            --green-premium: #10b981;
            --green-soft: #ecfdf5;
            --purple-premium: #8b5cf6;
            --purple-soft: #f5f3ff;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.4);
            --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
        }

        .font-outfit { font-family: 'Outfit', sans-serif; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .tiny { font-size: 0.7rem; }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 1.25rem;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card-header-premium {
            padding: 1.5rem;
            background: linear-gradient(to right, #ffffff, #f9fafb);
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.25rem;
        }

        .mini-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .bg-soft-blue { background-color: var(--blue-soft); }
        .text-blue { color: var(--blue-premium); }
        .bg-soft-green { background-color: var(--green-soft); }
        .text-green { color: var(--green-premium); }
        .bg-soft-purple { background-color: var(--purple-soft); }
        .text-purple { color: var(--purple-premium); }

        /* Timeline Premium */
        .premium-timeline {
            position: relative;
            padding-left: 1.5rem;
        }

        .premium-timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--blue-premium), #e2e8f0);
            border-radius: 2px;
        }

        .timeline-box {
            position: relative;
            margin-bottom: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .timeline-node {
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 12px;
            height: 12px;
            background: white;
            border: 2px solid var(--blue-premium);
            border-radius: 50%;
            transform: translateX(-50%);
            z-index: 1;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .timeline-info {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 1rem;
            border: 1px solid #f1f5f9;
        }

        .timeline-item-hidden {
            display: none !important;
            opacity: 0;
            transform: translateY(10px);
        }

        .timeline-item-show {
            display: block !important;
            animation: slideInUp 0.4s forwards;
        }

        /* Table Style */
        .table-premium thead th {
            background: #f8fafc;
            border-bottom: 2px solid #f1f5f9;
            color: #64748b;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 1rem;
        }

        .table-premium tbody td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .btn-action-circle {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .btn-soft-blue { background: var(--blue-soft); color: var(--blue-premium); border: none; }
        .btn-soft-green { background: var(--green-soft); color: var(--green-premium); border: none; }
        .btn-soft-blue:hover { background: var(--blue-premium); color: white; }
        .btn-soft-green:hover { background: var(--green-premium); color: white; }

        /* Action Bar */
        .action-footer-bar {
            position: sticky;
            bottom: 2rem;
            z-index: 100;
        }

        .shadow-premium {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }

        .btn-blue-premium {
            background: linear-gradient(135deg, var(--blue-premium), var(--indigo-premium));
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-green-premium {
            background: linear-gradient(135deg, var(--green-premium), #059669);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-blue-premium:hover, .btn-green-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .badge-wrapper {
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }

        .status-green { background: var(--green-soft); color: var(--green-premium); border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-blue { background: var(--blue-soft); color: var(--blue-premium); border: 1px solid rgba(37, 99, 235, 0.2); }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* PDF Preview Modal */
        .pdf-preview-modal .modal-content {
            border-radius: 1.5rem;
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .pdf-preview-modal .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 1.5rem 1.5rem 0 0;
            padding: 1.25rem 1.5rem;
        }
    </style>

    <script>
        // Global variables for PDF preview
        let currentPdfUrl = '';
        let currentFileName = '';

        function previewPDF(url, fileName) {
            currentPdfUrl = url;
            currentFileName = fileName;

            $bootbox = bootbox.dialog({
                title: '<i class="fas fa-file-pdf me-2"></i>Preview: ' + fileName,
                message: '<div class="text-center py-5"><div class="spinner-border text-blue" role="status"></div><p class="mt-3 text-muted">Memuat dokumen...</p></div>',
                size: 'extra-large',
                className: 'pdf-preview-modal animate__animated animate__zoomIn',
                buttons: {
                    cancel: { label: 'Tutup', className: 'btn-soft-dark px-4 rounded-pill' }
                }
            });

            setTimeout(() => { loadPDFPreview(url, $bootbox); }, 300);
        }

        function loadPDFPreview(url, $bootbox) {
            const pdfContent = `
                <div class="pdf-preview-container">
                    <iframe id="pdfViewer" src="${url}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                            style="width: 100%; height: calc(100vh - 250px); min-height: 700px; border: 1px solid #e2e8f0; border-radius: 1rem;"
                            title="PDF Preview">
                    </iframe>
                </div>
            `;
            $bootbox.find('.modal-body').html(pdfContent);
        }

        $(document).on('hidden.bs.modal', '.bootbox', function() {
            currentPdfUrl = ''; currentFileName = '';
        });

        $(document).ready(function() {
            $('#showMoreBtn').on('click', function() {
                $('.timeline-item-hidden').each(function(i) {
                    const el = $(this);
                    setTimeout(() => {
                        el.removeClass('timeline-item-hidden').addClass('timeline-item-show');
                    }, i * 100);
                });
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
                            <label class="info-label">NIK (16 Digit)</label>
                            <input type="text" class="form-control rounded-pill px-3" id="tte_nik" placeholder="0000000000000000" maxlength="16">
                        </div>
                        <div class="col-md-6 text-start">
                            <label class="info-label">Passphrase TTE</label>
                            <div class="input-group">
                                <input type="password" class="form-control rounded-pill-start px-3" id="tte_password" placeholder="********">
                                <button class="btn btn-outline-secondary rounded-pill-end" type="button" onclick="togglePasswordVisibility()"><i class="fas fa-eye" id="togglePasswordIcon"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="tteVerificationResult" class="mt-3"></div>
                </form>
            `;

            currentBootbox = bootbox.dialog({
                title: '<i class="fas fa-stamp me-2 text-blue"></i>Verifikasi Sertifikat BSrE',
                message: tteContent,
                size: 'large',
                className: 'animate__animated animate__fadeInDown',
                buttons: {
                    verify: {
                        label: 'Cek Sertifikat',
                        className: 'btn-blue-premium px-4 rounded-pill',
                        callback: function() { verifyTteCertificate(); return false; }
                    },
                    proceed: {
                        label: 'Tanda Tangani',
                        className: 'btn-green-premium px-4 rounded-pill',
                        id: 'btnProceedTte',
                        style: 'display: none;',
                        callback: function() { proceedTTE(); return false; }
                    }
                }
            });
        }

        function togglePasswordVisibility() {
            const pwd = document.getElementById('tte_password');
            const ico = document.getElementById('togglePasswordIcon');
            if (pwd.type === 'password') { pwd.type = 'text'; ico.className = 'fas fa-eye-slash'; }
            else { pwd.type = 'password'; ico.className = 'fas fa-eye'; }
        }

        function verifyTteCertificate() {
            const res = document.getElementById('tteVerificationResult');
            const nik = document.getElementById('tte_nik')?.value;
            if (!nik || !/^\d{16}$/.test(nik)) {
                res.innerHTML = '<div class="alert alert-soft-danger border-0 small"><i class="fas fa-exclamation-circle me-2"></i>NIK harus 16 digit angka.</div>';
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
                    res.innerHTML = '<div class="alert alert-soft-success border-0 small"><i class="fas fa-check-circle me-2"></i>Sertifikat Aktif. Silakan masukkan passphrase.</div>';
                    $('#btnProceedTte').fadeIn();
                } else {
                    res.innerHTML = '<div class="alert alert-soft-danger border-0 small"><i class="fas fa-times-circle me-2"></i>' + (d.message || 'Sertifikat tidak aktif.') + '</div>';
                }
            });
        }

        function proceedTTE() {
            const nik = $('#tte_nik').val();
            const pwd = $('#tte_password').val();
            if (!pwd) { Swal.fire('Error', 'Passphrase harus diisi!', 'error'); return; }

            Swal.fire({
                title: 'Konfirmasi TTE',
                text: 'Dokumen akan ditandatangani secara elektronik. Lanjutkan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $('#tteVerificationResult').html('<div class="alert alert-soft-warning border-0 small animation-pulse"><i class="fas fa-spinner fa-spin me-2"></i>Menghubungi Server BSrE...</div>');
                    $('#btnProceedTte').prop('disabled', true);

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
                        if (d.status === 'success') {
                            Swal.fire('Berhasil!', 'Dokumen telah ditandatangani.', 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Gagal', d.message || 'Error server BSrE', 'error');
                            $('#btnProceedTte').prop('disabled', false);
                        }
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
                confirmButtonText: 'Ya, Paraf!',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'btn btn-blue-premium px-4 rounded-pill' }
            }).then((result) => {
                if (result.isConfirmed) {
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
                            Swal.fire('Berhasil!', 'Paraf telah diproses.', 'success').then(() => window.location.href = '<?= base_url('legalisasi') ?>');
                        } else {
                            Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        }

        function revisiKeFinalisasi(ajuanId) {
            Swal.fire({
                title: 'Catatan Revisi',
                input: 'textarea',
                inputPlaceholder: 'Berikan alasan revisi...',
                showCancelButton: true,
                confirmButtonText: 'Kirim Revisi',
                confirmButtonColor: '#f59e0b',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed && res.value) {
                    const formData = new FormData();
                    formData.append('ajuan_id', ajuanId);
                    formData.append('catatan', res.value);
                    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                    fetch('<?= base_url('legalisasi/revisiKeFinalisasi') ?>/' + ajuanId, {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            Swal.fire('Terkirim!', 'Ajuan dikembalikan ke tahap finalisasi.', 'success').then(() => window.location.href = '<?= base_url('legalisasi') ?>');
                        } else {
                            Swal.fire('Gagal', d.message || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        }
    </script>