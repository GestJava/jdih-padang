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

    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 1rem;">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fs-4 me-3 mt-1"></i>
                <div>
                    <strong class="d-block mb-1">Terdapat kesalahan input:</strong>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <li class="breadcrumb-item"><a href="<?= base_url('validasi') ?>" class="text-decoration-none" style="color: #0061ff;">Validasi</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Proses Validasi</li>
                    </ol>
                </nav>
                <h2 class="hero-title mb-2 text-uppercase">
                    <?= esc($ajuan['judul_peraturan']) ?>
                </h2>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="badge rounded-pill bg-white text-primary border px-3 py-2 shadow-sm">
                        <i class="fas fa-gavel me-1"></i> <?= esc($ajuan['nama_jenis']) ?>
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
                            elseif (strpos($statusLower, 'diajukan') !== false) { $statusColor = '#0061ff'; $statusIcon = 'fa-paper-plane'; }
                            elseif (strpos($statusLower, 'verifikasi') !== false) { $statusColor = '#fd7e14'; $statusIcon = 'fa-clipboard-check'; }
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
                            <span class="fw-bold" style="font-size: 0.95rem; color: #2d3748;">
                                <?php
                                $tanggal_tampil = !empty($ajuan['tanggal_pengajuan']) && $ajuan['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                    ? $ajuan['tanggal_pengajuan']
                                    : ($ajuan['created_at'] ?? '-');
                                echo date('d F Y H:i', strtotime($tanggal_tampil));
                                ?>
                            </span>
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
                        <div class="info-slot">
                            <div class="info-slot-icon bg-soft-orange text-warning"><i class="fas fa-user-tie"></i></div>
                            <div class="info-slot-content">
                                <span class="label">User Pemohon</span>
                                <span class="value"><?= esc($ajuan['nama_pemohon']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-slot">
                            <div class="info-slot-icon bg-soft-green text-success"><i class="far fa-building"></i></div>
                            <div class="info-slot-content">
                                <span class="label">Instansi Pemohon</span>
                                <span class="value"><?= esc($ajuan['nama_instansi']) ?></span>
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
            </div>

            <!-- DOKUMEN LIST -->
            <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.6s ease;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                        <i class="fas fa-paperclip me-2" style="color: #28a745;"></i>Dokumen Terlampir
                    </h6>
                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                        <?= count($dokumen) ?> file
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
                        <?php foreach ($dokumen as $dok) : ?>
                            <?php
                                $ext = strtolower(pathinfo($dok['nama_file_original'], PATHINFO_EXTENSION));
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
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: <?= $bgSoft ?>;">
                                    <i class="fas <?= $icon ?> fs-4" style="color: <?= $iconColor ?>;"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="mb-1 text-truncate fw-bold" style="font-size: 0.9rem; color: #2d3748;">
                                        <?= esc(ucwords(str_replace('_', ' ', $dok['tipe_dokumen']))) ?>
                                    </h6>
                                    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.8rem;">
                                        <span class="text-truncate" style="max-width: 200px;"><?= esc($dok['nama_file_original']) ?></span>
                                        <span>&bull;</span>
                                        <span><i class="far fa-clock me-1"></i>Diunggah: <?= date('d M Y H:i', strtotime($dok['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <a href="<?= base_url('validasi/download/' . $dok['id']) ?>" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold" style="background: rgba(0,97,255,0.1); color: #0061ff; border: none;">
                                        <i class="fas fa-download me-1"></i>Unduh
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============================================================
                 FORM KEPUTUSAN VALIDASI
                 ============================================================ -->
            <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.7s ease; border: 2px solid rgba(23, 162, 184, 0.1);">
                <h6 class="fw-bold text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-clipboard-check me-2" style="color: #17a2b8;"></i>Keputusan Validasi
                </h6>

                <?= form_open('validasi/submitAksi', ['id' => 'validasiForm', 'enctype' => 'multipart/form-data']) ?>
                <?= csrf_field() ?>
                <input type="hidden" name="id_ajuan" value="<?= esc($ajuan['id'] ?? '') ?>">

                <div class="mb-4">
                    <label for="catatan" class="form-label fw-semibold" style="color: #2d3748;">
                        <i class="fas fa-comment-dots me-1" style="color: #0061ff;"></i> Catatan Validasi
                    </label>
                    <textarea class="form-control rounded-4" id="catatan" name="catatan" rows="4"
                        placeholder="Tuliskan hasil review substansi, catatan perbaikan, atau alasan jika ditolak..."
                        style="border: 2px solid #e2e8f0; padding: 1rem; font-size: 0.95rem; transition: all 0.3s ease;"></textarea>
                </div>

                <div class="mb-4">
                    <label for="dokumen_revisi" class="form-label fw-semibold" style="color: #2d3748;">
                        <i class="fas fa-upload me-1" style="color: #6f42c1;"></i> File Hasil Revisi / Validasi <span class="text-muted fw-normal">(Opsional)</span>
                    </label>
                    <input type="file" class="form-control rounded-4" id="dokumen_revisi" name="dokumen_revisi" accept=".pdf,.doc,.docx"
                        style="border: 2px solid #e2e8f0; padding: 0.75rem 1rem;">
                    <div class="form-text mt-2" style="color: #a0aec0; font-size: 0.8rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Format: PDF, DOC, DOCX. Maksimal 25MB.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-3 pt-3" style="border-top: 1px solid rgba(0,0,0,0.05);">
                    <button type="submit" name="aksi" value="lanjutkan"
                        class="btn rounded-pill px-4 py-3 fw-semibold text-white flex-grow-1"
                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 4px 15px rgba(16,185,129,0.3); transition: all 0.3s ease;">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>Lanjutkan ke Finalisasi</span>
                    </button>
                    
                    <button type="submit" name="aksi" value="revisi"
                        class="btn rounded-pill px-4 py-3 fw-semibold"
                        style="background: #fff3cd; color: #856404; border: 1px solid #ffeeba; transition: all 0.3s ease;"
                        onclick="return confirm('Kembalikan ajuan ini untuk dilakukan revisi?');">
                        <i class="fas fa-undo me-2"></i>
                        <span>Revisi</span>
                    </button>

                    <button type="submit" name="aksi" value="tolak"
                        class="btn rounded-pill px-4 py-3 fw-semibold"
                        style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; transition: all 0.3s ease;"
                        onclick="return confirm('Apakah Anda yakin ingin MENOLAK ajuan ini secara permanen?');">
                        <i class="fas fa-times me-2"></i>
                        <span>Tolak</span>
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>

        <!-- ============================================================
             RIGHT COLUMN - RIWAYAT PROSES
             ============================================================ -->
        <div class="col-lg-4">
            <div class="glass-panel-light shadow-sm p-4 mb-4" style="animation: fadeInUp 0.5s ease;">
                <h6 class="fw-bold text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-stream me-2" style="color: #fd7e14;"></i>Riwayat Proses
                </h6>
                <?php if (!empty($histori)): ?>
                    <?php
                        $totalItems = count($histori);
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
                                $stepState = $isFinalized ? 'completed' : (($index === 0) ? 'active' : 'completed');
                                $markerColor = '#6c757d';
                                $statusName = strtolower($item['status_sekarang'] ?? '');
                                if (strpos($statusName, 'validasi') !== false) { $markerColor = '#17a2b8'; }
                                elseif (strpos($statusName, 'paraf') !== false) { $markerColor = '#fd7e14'; }
                                elseif (strpos($statusName, 'selesai') !== false || strpos($statusName, 'tte') !== false) { $markerColor = '#28a745'; }
                                elseif (strpos($statusName, 'revisi') !== false || strpos($statusName, 'ditolak') !== false) { $markerColor = '#dc3545'; }
                                elseif (strpos($statusName, 'diajukan') !== false) { $markerColor = '#0061ff'; }
                                elseif (strpos($statusName, 'verifikasi') !== false) { $markerColor = '#fd7e14'; }
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

            <!-- Panduan Cepat -->
            <div class="glass-panel-light shadow-sm p-4" style="animation: fadeInUp 0.6s ease;">
                <h6 class="fw-bold text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-lightbulb me-2" style="color: #f6ad55;"></i>Panduan Validator
                </h6>
                <div class="d-flex mb-3 gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 28px; height: 28px; background: #cbd5e0; font-size: 0.8rem; flex-shrink: 0;">1</div>
                    <p class="mb-0 text-muted" style="font-size: 0.85rem;">Pastikan substansi hukum telah sesuai dengan peraturan perundangan yang lebih tinggi.</p>
                </div>
                <div class="d-flex mb-3 gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 28px; height: 28px; background: #cbd5e0; font-size: 0.8rem; flex-shrink: 0;">2</div>
                    <p class="mb-0 text-muted" style="font-size: 0.85rem;">Periksa apakah poin-poin perbaikan dari tahap verifikasi telah diakomodasi (lihat riwayat).</p>
                </div>
                <div class="d-flex gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 28px; height: 28px; background: #cbd5e0; font-size: 0.8rem; flex-shrink: 0;">3</div>
                    <p class="mb-0 text-muted" style="font-size: 0.85rem;">Gunakan tombol <strong>Revisi</strong> bila draf masih memerlukan perbaikan teknis/substantif.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form submission loading effect
        const form = document.getElementById('validasiForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const btn = document.activeElement;
                if (btn && btn.tagName === 'BUTTON' && btn.type === 'submit') {
                    
                    // Client side validation
                    var catatanValue = document.getElementById('catatan').value.trim();
                    if ((btn.value === 'tolak' || btn.value === 'revisi') && catatanValue === '') {
                        e.preventDefault();
                        alert('Aksi penolakan atau revisi membutuhkan catatan/review. Silakan isikan catatan terlebih dahulu.');
                        document.getElementById('catatan').focus();
                        return false;
                    }

                    // Ensure the button value is submitted by adding a hidden input first
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = btn.name;
                    hiddenInput.value = btn.value;
                    form.appendChild(hiddenInput);

                    const originalContent = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><span>Memproses...</span>';
                    btn.classList.add('disabled');
                    btn.style.pointerEvents = 'none';
                    // Optional: restore content after timeout in case form validation fails client side
                    setTimeout(() => {
                        if(!form.checkValidity()) {
                            btn.innerHTML = originalContent;
                            btn.classList.remove('disabled');
                            btn.style.pointerEvents = 'auto';
                        }
                    }, 500);
                }
            });
        }
        
        // Add specific interaction for textareas
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(ta => {
            ta.addEventListener('focus', function() {
                this.style.borderColor = '#17a2b8';
                this.style.boxShadow = '0 0 0 0.25rem rgba(23, 162, 184, 0.25)';
            });
            ta.addEventListener('blur', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.boxShadow = 'none';
            });
        });
    });
</script>