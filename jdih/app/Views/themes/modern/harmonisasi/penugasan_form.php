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
         HERO HEADER
         ============================================================ -->
    <div class="hero-status-card mb-4" style="animation: fadeInUp 0.4s ease;">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="<?= base_url('harmonisasi') ?>" class="text-decoration-none" style="color: #0061ff;">Harmonisasi</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('penugasan') ?>" class="text-decoration-none" style="color: #0061ff;">Penugasan</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Tugaskan Verifikator</li>
                    </ol>
                </nav>
                <h2 class="hero-title mb-2">
                    <i class="fas fa-user-plus me-2" style="color: #0061ff;"></i><?= esc($title) ?>
                </h2>
                <p class="hero-subtitle mb-0"><?= esc($ajuan['judul_peraturan']) ?></p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="d-flex flex-column gap-2 align-items-lg-end">
                    <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: rgba(0,97,255,0.1); color: #0061ff; font-size: 0.85rem;">
                        <i class="fas fa-gavel me-1"></i><?= esc($ajuan['nama_jenis']) ?>
                    </span>
                    <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background: rgba(255,193,7,0.15); color: #d39e00; font-size: 0.85rem; border: 1px solid rgba(255,193,7,0.3);">
                        <i class="fas fa-hourglass-half me-1"></i><?= esc($ajuan['nama_status']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ============================================================
             LEFT COLUMN - FORM
             ============================================================ -->
        <div class="col-lg-8">
            <!-- INFORMASI AJUAN -->
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
                    <?php if (!empty($dokumen)): ?>
                        <span class="badge rounded-pill px-3 py-1" style="background: rgba(40,167,69,0.1); color: #28a745; font-weight: 600;"><?= count($dokumen) ?> file</span>
                    <?php endif; ?>
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
                            ?>
                            <div class="d-flex align-items-center p-3 rounded-4" style="background: rgba(248,250,255,0.8); border: 1px solid rgba(0,0,0,0.04); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                                <div class="rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px; background: <?= $fileBg ?>; flex-shrink: 0;">
                                    <i class="fas <?= $fileIcon ?> fa-lg" style="color: <?= $fileColor ?>;"></i>
                                </div>
                                <div class="flex-grow-1 me-3">
                                    <div class="fw-bold mb-0" style="font-size: 0.85rem; color: #2d3748;"><?= esc(ucwords(str_replace('_', ' ', $doc['tipe_dokumen']))) ?></div>
                                    <div style="font-size: 0.8rem; color: #718096;"><?= esc($doc['nama_file_original']) ?></div>
                                    <small style="font-size: 0.7rem; color: #a0aec0;"><i class="far fa-clock me-1"></i>Diunggah: <?= date('d M Y H:i', strtotime($doc['created_at'])) ?></small>
                                </div>
                                <a href="<?= base_url('harmonisasi/download/' . $doc['id']) ?>"
                                   class="btn btn-sm rounded-pill px-3 fw-semibold"
                                   style="background: rgba(0,97,255,0.1); color: #0061ff; border: none; transition: all 0.3s ease;"
                                   onmouseover="this.style.background='#0061ff'; this.style.color='#fff';"
                                   onmouseout="this.style.background='rgba(0,97,255,0.1)'; this.style.color='#0061ff';"
                                   target="_blank" title="Unduh Dokumen">
                                    <i class="fas fa-download me-1"></i>Unduh
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 rounded-4" style="background: rgba(255,193,7,0.05); border: 1px dashed rgba(255,193,7,0.3);">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2" style="color: #ffc107;"></i>
                        <p class="mb-1 fw-semibold" style="color: #856404; font-size: 0.9rem;">Dokumen Belum Tersedia</p>
                        <small style="color: #a0aec0;">Anda dapat melanjutkan penugasan atau menunggu pemohon mengunggah dokumen.</small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ============================================================
                 FORM PENUGASAN VERIFIKATOR
                 ============================================================ -->
            <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.7s ease; border: 2px solid rgba(0, 97, 255, 0.1);">
                <h6 class="fw-bold text-uppercase mb-4" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-users me-2" style="color: #6f42c1;"></i>Pilih Verifikator
                </h6>

                <?= form_open('penugasan/assign', ['id' => 'assignmentForm']) ?>
                <?= csrf_field() ?>
                <input type="hidden" name="id_ajuan" value="<?= esc($ajuan['id_ajuan']) ?>">

                <div class="mb-4">
                    <label for="id_user_verifikator" class="form-label fw-semibold" style="color: #2d3748;">
                        Petugas Verifikator <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-lg rounded-4"
                        id="id_user_verifikator"
                        name="id_user_verifikator"
                        required
                        style="border: 2px solid #e2e8f0; padding: 0.875rem 1.25rem; font-size: 1rem; transition: all 0.3s ease;">
                        <option value="">-- Pilih Petugas Verifikator --</option>
                        <?php if (!empty($verifikator_list)): ?>
                            <?php foreach ($verifikator_list as $verifikator): ?>
                                <option value="<?= esc($verifikator['id']) ?>">
                                    <?= esc($verifikator['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Tidak ada verifikator tersedia</option>
                        <?php endif; ?>
                    </select>
                    <div class="form-text mt-2" style="color: #a0aec0; font-size: 0.8rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        Pilih salah satu petugas verifikator yang akan menangani ajuan ini
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-3 justify-content-end pt-3" style="border-top: 1px solid rgba(0,0,0,0.05);">
                    <a href="<?= base_url('penugasan') ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-medium">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit"
                        class="btn rounded-pill px-4 fw-semibold text-white"
                        id="submitBtn"
                        style="background: linear-gradient(135deg, #0061ff 0%, #60efff 100%); border: none; box-shadow: 0 4px 15px rgba(0,97,255,0.3); transition: all 0.3s ease;"
                        <?= empty($verifikator_list) ? 'disabled' : '' ?>>
                        <i class="fas fa-user-plus me-2"></i>
                        <span class="btn-text">Tugaskan Sekarang</span>
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>

        <!-- ============================================================
             RIGHT COLUMN - SIDEBAR
             ============================================================ -->
        <div class="col-lg-4">
            <!-- Info Guide -->
            <div class="glass-panel-light shadow-sm mb-4 p-4" style="animation: fadeInUp 0.5s ease;">
                <h6 class="fw-bold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-lightbulb me-2" style="color: #ffc107;"></i>Panduan
                </h6>

                <div class="mb-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(0,97,255,0.1); font-size: 0.8rem; font-weight: 700; color: #0061ff;">1</div>
                        <div>
                            <p class="mb-0 fw-semibold" style="font-size: 0.85rem; color: #2d3748;">Pilih Verifikator</p>
                            <small style="color: #a0aec0;">Pilih petugas dari daftar dropdown di bawah</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(40,167,69,0.1); font-size: 0.8rem; font-weight: 700; color: #28a745;">2</div>
                        <div>
                            <p class="mb-0 fw-semibold" style="font-size: 0.85rem; color: #2d3748;">Klik "Tugaskan"</p>
                            <small style="color: #a0aec0;">Status akan berubah menjadi <strong>"Proses Verifikasi"</strong></small>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(253,126,20,0.1); font-size: 0.8rem; font-weight: 700; color: #fd7e14;">3</div>
                        <div>
                            <p class="mb-0 fw-semibold" style="font-size: 0.85rem; color: #2d3748;">Verifikator Bekerja</p>
                            <small style="color: #a0aec0;">Ajuan akan muncul di dashboard verifikator</small>
                        </div>
                    </div>
                </div>

                <?php if (!empty($dokumen)): ?>
                <div class="rounded-4 p-3" style="background: rgba(40,167,69,0.05); border: 1px solid rgba(40,167,69,0.15);">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        <span class="fw-semibold" style="font-size: 0.85rem; color: #28a745;">Dokumen Siap</span>
                    </div>
                    <small style="color: #718096;"><?= count($dokumen) ?> dokumen tersedia untuk dipilih dan di-review oleh verifikator.</small>
                </div>
                <?php else: ?>
                <div class="rounded-4 p-3" style="background: rgba(255,193,7,0.05); border: 1px solid rgba(255,193,7,0.15);">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
                        <span class="fw-semibold" style="font-size: 0.85rem; color: #856404;">Dokumen Belum Ada</span>
                    </div>
                    <small style="color: #718096;">Anda tetap bisa melanjutkan penugasan tanpa dokumen.</small>
                </div>
                <?php endif; ?>
            </div>

            <!-- Verifikator Stats -->
            <div class="glass-panel-light shadow-sm p-4" style="animation: fadeInUp 0.6s ease;">
                <h6 class="fw-bold text-uppercase mb-3" style="font-size: 0.8rem; letter-spacing: 1.5px; color: #a0aec0;">
                    <i class="fas fa-chart-pie me-2" style="color: #28a745;"></i>Statistik
                </h6>
                <div class="text-center py-3">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 64px; height: 64px; background: rgba(40,167,69,0.08);">
                        <i class="fas fa-users fa-lg" style="color: #28a745;"></i>
                    </div>
                    <h3 class="fw-black mb-0" style="color: #28a745;"><?= count($verifikator_list) ?></h3>
                    <small class="text-uppercase fw-bold" style="color: #a0aec0; letter-spacing: 1px; font-size: 0.7rem;">Verifikator Tersedia</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('assignmentForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');

        form.addEventListener('submit', function(e) {
            if (submitBtn.disabled) { e.preventDefault(); return false; }
            submitBtn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
            submitBtn.style.opacity = '0.7';
        });

        const verifikatorSelect = document.getElementById('id_user_verifikator');
        verifikatorSelect.addEventListener('change', function() {
            if (this.value) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
            }
        });

        verifikatorSelect.addEventListener('focus', function() {
            this.style.borderColor = '#0061ff';
            this.style.boxShadow = '0 0 0 3px rgba(0,97,255,0.1)';
        });

        verifikatorSelect.addEventListener('blur', function() {
            this.style.borderColor = '#e2e8f0';
            this.style.boxShadow = 'none';
        });
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

@media (max-width: 768px) {
    .d-flex.gap-3.justify-content-end {
        flex-direction: column;
    }
    .d-flex.gap-3.justify-content-end > * {
        width: 100%;
        text-align: center;
    }
}
</style>