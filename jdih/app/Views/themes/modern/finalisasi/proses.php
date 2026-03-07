<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-flag-checkered text-primary me-2"></i><?= esc($title) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('finalisasi') ?>">Finalisasi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Proses Finalisasi</li>
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

    <!-- Validation Errors -->
    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
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

    <div class="row">
        <div class="col-lg-8">
            <!-- Detail Ajuan -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white py-3">
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
                                    <td><?= esc($ajuan['judul_peraturan'] ?? 'Data tidak tersedia') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Jenis Peraturan</th>
                                    <td>
                                        <span class="badge bg-info text-white"><?= esc($ajuan['nama_jenis'] ?? 'Data tidak tersedia') ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Instansi Pemohon</th>
                                    <td><?= esc($ajuan['nama_instansi'] ?? 'Data tidak tersedia') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">User Pemohon</th>
                                    <td><?= esc($ajuan['nama_pemohon'] ?? 'Data tidak tersedia') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%" class="text-muted">Tanggal Pengajuan</th>
                                    <td>
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php
                                        $tanggal_tampil = !empty($ajuan['tanggal_pengajuan']) && $ajuan['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                            ? $ajuan['tanggal_pengajuan']
                                            : $ajuan['created_at'];
                                        echo date('d/m/Y H:i', strtotime($tanggal_tampil));
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Status Saat Ini</th>
                                    <td>
                                        <?php if (!empty($ajuan['nama_status'])): ?>
                                            <span class="badge bg-info text-white fs-6">
                                                <i class="fas fa-flag-checkered me-1"></i><?= esc($ajuan['nama_status']) ?>
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

            <!-- Form Aksi Finalisasi -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-tasks me-2"></i>Aksi Finalisasi
                    </h6>
                </div>
                <div class="card-body">
                    <?= form_open('finalisasi/submitAksi', ['id' => 'finalisasiForm', 'enctype' => 'multipart/form-data']) ?>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_ajuan" value="<?= esc($ajuan['id'] ?? '') ?>">

                    <div class="mb-4">
                        <label for="dokumen_final" class="form-label fw-bold">
                            <i class="fas fa-upload me-1"></i>Upload Dokumen Final atau Revisi
                        </label>
                        <input type="file" class="form-control" id="dokumen_final" name="dokumen_final" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Upload dokumen versi final (untuk selesai) atau dokumen revisi (untuk revisi). Format: PDF, DOC, DOCX. Maksimal 25MB.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="catatan" class="form-label fw-bold">
                            <i class="fas fa-comment me-1"></i>Catatan Finalisasi (Opsional)
                        </label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="4"
                            placeholder="Berikan catatan atau keterangan untuk finalisasi..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-play-circle me-1"></i>Pilih Aksi
                        </label>
                        <div class="d-grid gap-2">
                            <button type="submit" name="aksi" value="selesai" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-2"></i>
                                Selesaikan Finalisasi
                            </button>
                            <button type="submit" name="aksi" value="revisi" class="btn btn-warning btn-lg"
                                onclick="return confirm('Apakah Anda yakin ingin mengembalikan ajuan ini untuk revisi?');">
                                <i class="fas fa-undo me-2"></i>
                                Kembalikan untuk Revisi
                            </button>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
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
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title text-info">
                                            <?= esc($item['status_sekarang'] ?? $item['keterangan'] ?? 'Proses') ?>
                                        </h6>
                                        <p class="timeline-description">
                                            <?= esc($item['keterangan'] ?? $item['catatan'] ?? 'Tidak ada catatan') ?>
                                        </p>
                                        <small class="timeline-time text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php
                                            $tanggal_histori = !empty($item['tanggal_aksi']) ? $item['tanggal_aksi'] : $item['created_at'];
                                            echo date('d/m/Y H:i', strtotime($tanggal_histori));
                                            ?>
                                            oleh <?= esc($item['nama_user'] ?? 'Sistem') ?>
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

            <!-- Information Card -->
            <div class="card shadow">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-lightbulb me-2"></i>Panduan Finalisasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-info">
                            <i class="fas fa-flag-checkered me-1"></i>Proses Finalisasi
                        </h6>
                        <p class="small text-muted">
                            Sebagai finalisator, Anda bertanggung jawab untuk menyelesaikan proses harmonisasi dengan dokumen final yang siap ditandatangani.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="fas fa-check-circle me-1"></i>Kriteria Selesaikan
                        </h6>
                        <ul class="small text-muted">
                            <li>Dokumen sudah final dan siap ditandatangani</li>
                            <li>Semua tahap verifikasi dan validasi selesai</li>
                            <li>Tidak ada revisi yang diperlukan</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-warning">
                            <i class="fas fa-undo me-1"></i>Kriteria Revisi
                        </h6>
                        <ul class="small text-muted">
                            <li>Ada perbaikan final yang diperlukan</li>
                            <li>Dokumen belum siap untuk ditandatangani</li>
                            <li>Ada catatan dari finalisator</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-info">
                            <i class="fas fa-file-upload me-1"></i>Upload Dokumen Final
                        </h6>
                        <ul class="small text-muted">
                            <li>Format: PDF, DOC, DOCX</li>
                            <li>Maksimal ukuran: 5MB</li>
                            <li>Dokumen harus siap ditandatangani</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="d-grid">
                        <a href="<?= base_url('finalisasi') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Timeline styling */
    .timeline-container {
        position: relative;
        padding-left: 20px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -10px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #17a2b8;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border-left: 3px solid #17a2b8;
    }

    .timeline-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .timeline-description {
        font-size: 0.8rem;
        margin-bottom: 5px;
    }

    .timeline-time {
        font-size: 0.75rem;
    }

    /* Enhanced form styling */
    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }

    .btn-lg {
        padding: 12px 24px;
        font-size: 1.1rem;
    }

    /* Card enhancements */
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        border-bottom: none;
    }
</style>