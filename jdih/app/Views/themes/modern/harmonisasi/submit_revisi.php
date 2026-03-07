<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Submit Revisi Dokumen
                    </h5>
                </div>

                <div class="card-body">
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('harmonisasi/submitRevisi/' . $ajuan['id_ajuan']) ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Judul Peraturan</label>
                            <p class="form-control-static"><?= esc($ajuan['judul_peraturan'] ?? 'Tidak tersedia') ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Peraturan</label>
                            <p class="form-control-static"><?= esc($ajuan['nama_jenis'] ?? 'Tidak tersedia') ?></p>
                        </div>

                        <?php if (!empty($histori)) : ?>
                            <div class="mb-3">
                                <label class="form-label">Catatan Revisi</label>
                                <div class="alert alert-info">
                                    <?= nl2br(esc($histori['keterangan'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($dokumen_koreksi)) : ?>
                            <div class="mb-3">
                                <label class="form-label">Dokumen Koreksi</label>
                                <div class="d-grid gap-2 d-md-flex">
                                    <a href="<?= base_url('harmonisasi/download/' . $dokumen_koreksi['id']) ?>"
                                        class="btn btn-secondary">
                                        <i class="bi bi-download"></i> Download Dokumen Koreksi
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label required">Upload Dokumen Revisi</label>
                            <input type="file" class="form-control" name="dokumen" accept=".doc,.docx" required>
                            <div class="form-text">Format yang diperbolehkan: .doc, .docx</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan Revisi</label>
                            <textarea class="form-control" name="keterangan" rows="3"
                                placeholder="Jelaskan perubahan yang dilakukan pada dokumen revisi"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('harmonisasi/show/' . $ajuan['id_ajuan']) ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Submit Revisi
                            </button>
                        </div>
                </div>
            </div>
        </div>
    </div>