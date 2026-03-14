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

    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert" style="animation: fadeInDown 0.5s ease;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($validation->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ============================================================
         HERO STATUS SECTION
         ============================================================ -->
    <div class="hero-status-card mb-4" style="animation: fadeInUp 0.4s ease;">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="<?= base_url('verifikasi') ?>" class="text-decoration-none" style="color: #0061ff;">Verifikasi</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Proses Verifikasi</li>
                    </ol>
                </nav>
                <h2 class="hero-title mb-2"><?= esc($ajuan['judul_peraturan']) ?></h2>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: rgba(0,97,255,0.1); color: #0061ff; font-size: 0.85rem;">
                        <i class="fas fa-gavel me-1"></i><?= esc($ajuan['nama_jenis']) ?>
                    </span>
                    <?php if (!empty($ajuan['nama_status'])):
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
                        elseif (strpos($statusLower, 'verifikasi') !== false) { $statusColor = '#fd7e14'; $statusIcon = 'fa-search'; }
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
                                $tanggal = !empty($ajuan['tanggal_pengajuan']) && $ajuan['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                    ? $ajuan['tanggal_pengajuan']
                                    : ($ajuan['created_at'] ?? '-');
                                echo date('d F Y H:i', strtotime($tanggal));
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
                                $ext = strtolower(pathinfo($doc['nama_file_original'], PATHINFO_EXTENSION));
                                $fileIcon = 'fa-file-alt'; $fileColor = '#6c757d'; $fileBg = 'rgba(108,117,125,0.08)';
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

            <!-- ============================================================
                 FORM KEPUTUSAN VERIFIKASI
                 ============================================================ -->
            <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.7s ease; border: 2px solid rgba(253, 126, 20, 0.1);">
                <h6 class="fw-bold text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-clipboard-check me-2" style="color: #fd7e14;"></i>Keputusan Verifikasi
                </h6>

                <?= form_open('verifikasi/submitAksi', ['id' => 'verifikasiForm', 'enctype' => 'multipart/form-data']) ?>
                <?= csrf_field() ?>
                <input type="hidden" name="id_ajuan" value="<?= esc($ajuan['id'] ?? '') ?>">

                <div class="mb-4">
                    <label for="catatan" class="form-label fw-semibold" style="color: #2d3748;">
                        <i class="fas fa-comment-dots me-1" style="color: #0061ff;"></i> Catatan / Review
                    </label>
                    <textarea class="form-control rounded-4" id="catatan" name="catatan" rows="4"
                        placeholder="Tuliskan catatan verifikasi, poin-poin yang perlu diperbaiki, atau alasan jika ditolak..."
                        style="border: 2px solid #e2e8f0; padding: 1rem; font-size: 0.95rem; transition: all 0.3s ease;"></textarea>
                </div>

                <div class="mb-4">
                    <label for="file_koreksi" class="form-label fw-semibold" style="color: #2d3748;">
                        <i class="fas fa-upload me-1" style="color: #6f42c1;"></i> File Catatan Koreksi <span class="text-muted fw-normal">(Opsional)</span>
                    </label>
                    <input type="file" class="form-control rounded-4" id="file_koreksi" name="file_koreksi" accept=".pdf,.doc,.docx"
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
                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; box-shadow: 0 4px 15px rgba(40,167,69,0.3); transition: all 0.3s ease;">
                        <i class="fas fa-check-circle me-2"></i>
                        <span>Lanjutkan ke Validasi</span>
                    </button>
                    <button type="submit" name="aksi" value="tolak"
                        class="btn rounded-pill px-4 py-3 fw-semibold"
                        style="background: rgba(220,53,69,0.08); color: #dc3545; border: 1px solid rgba(220,53,69,0.2); transition: all 0.3s ease;"
                        onclick="return confirm('Apakah Anda yakin ingin menolak ajuan ini? Tindakan ini tidak dapat dibatalkan.');">
                        <i class="fas fa-times-circle me-2"></i>Tolak
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
                <h6 class="fw-bold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-lightbulb me-2" style="color: #ffc107;"></i>Panduan
                </h6>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(0,97,255,0.1); font-size: 0.8rem; font-weight: 700; color: #0061ff;">1</div>
                    <div>
                        <p class="mb-0 fw-semibold" style="font-size: 0.85rem; color: #2d3748;">Periksa Dokumen</p>
                        <small style="color: #a0aec0;">Periksa kelengkapan berkas administrasi</small>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(40,167,69,0.1); font-size: 0.8rem; font-weight: 700; color: #28a745;">2</div>
                    <div>
                        <p class="mb-0 fw-semibold" style="font-size: 0.85rem; color: #2d3748;">Beri Catatan</p>
                        <small style="color: #a0aec0;">Tuliskan catatan review atau perbaikan</small>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(253,126,20,0.1); font-size: 0.8rem; font-weight: 700; color: #fd7e14;">3</div>
                    <div>
                        <p class="mb-0 fw-semibold" style="font-size: 0.85rem; color: #2d3748;">Ambil Keputusan</p>
                        <small style="color: #a0aec0;">Lanjutkan ke validasi atau tolak ajuan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('verifikasiForm');
        form.addEventListener('submit', function() {
            var btn = document.activeElement;
            if (btn && btn.type === 'submit') {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                btn.style.opacity = '0.7';
            }
        });

        // Textarea focus effect
        var catatan = document.getElementById('catatan');
        if (catatan) {
            catatan.addEventListener('focus', function() {
                this.style.borderColor = '#0061ff';
                this.style.boxShadow = '0 0 0 3px rgba(0,97,255,0.1)';
            });
            catatan.addEventListener('blur', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.boxShadow = 'none';
            });
        }
    });
</script>

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