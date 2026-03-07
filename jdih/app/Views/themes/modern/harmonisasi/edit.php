<div class="container-fluid">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light rounded-3 p-3 shadow-sm">
            <li class="breadcrumb-item">
                <a href="<?= base_url('harmonisasi'); ?>" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>
                    Dashboard Harmonisasi
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= base_url('harmonisasi/show/' . $ajuan['id']); ?>" class="text-decoration-none">
                    <i class="fas fa-eye me-1"></i>
                    Detail Ajuan
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-edit me-1"></i>
                Edit Draft
            </li>
        </ol>
    </nav>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Draft Harmonisasi
                    </h5>
                </div>
                <div class="card-body">
                    <?= form_open_multipart('harmonisasi/update/' . $ajuan['id'], ['class' => 'needs-validation', 'novalidate' => '']) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="judul_peraturan" class="form-label">Judul Peraturan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="judul_peraturan" name="judul_peraturan"
                                    value="<?= old('judul_peraturan', $ajuan['judul_peraturan']) ?>" required>
                                <div class="invalid-feedback">
                                    Judul peraturan wajib diisi (minimal 10 karakter).
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="id_jenis_peraturan" class="form-label">Jenis Peraturan <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_jenis_peraturan" name="id_jenis_peraturan" required>
                                    <option value="">Pilih Jenis Peraturan</option>
                                    <?php foreach ($jenis_peraturan as $jenis) : ?>
                                        <option value="<?= $jenis['id'] ?>" <?= old('id_jenis_peraturan', $ajuan['id_jenis_peraturan']) == $jenis['id'] ? 'selected' : '' ?>>
                                            <?= esc($jenis['nama_jenis']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Jenis peraturan wajib dipilih.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="draft_peraturan" class="form-label">Upload Draft Peraturan (Opsional)</label>
                                <input type="file" class="form-control" id="draft_peraturan" name="draft_peraturan"
                                    accept=".pdf,.doc,.docx">
                                <div class="form-text">
                                    Format file: PDF, DOC, DOCX. Maksimal ukuran: 25MB
                                </div>
                                <div class="invalid-feedback">
                                    File draft peraturan wajib diupload.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Dokumen Saat Ini</label>
                                <div class="border rounded p-3 bg-light">
                                    <?php if (!empty($dokumen)) : ?>
                                        <?php foreach ($dokumen as $doc) : ?>
                                            <?php if ($doc['tipe_dokumen'] === 'draft') : ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-word text-primary me-2"></i>
                                                    <div class="flex-grow-1">
                                                        <strong><?= esc($doc['nama_file_original']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">Uploaded: <?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?></small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p class="text-muted mb-0">Belum ada dokumen yang diupload</p>
                                    <?php endif; ?>
                                </div>
                                <small class="form-text text-muted">
                                    Upload file baru untuk mengganti dokumen yang ada
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                    placeholder="Masukkan keterangan tambahan jika diperlukan"><?= old('keterangan', $ajuan['keterangan']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('harmonisasi/show/' . $ajuan['id']) ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </a>

                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="fa fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Bootstrap form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // File input label update
    document.getElementById('draft_peraturan').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
        var label = document.querySelector('label[for="draft_peraturan"]');
        if (e.target.files[0]) {
            label.innerHTML = 'Upload Draft Peraturan (Opsional) - ' + fileName;
        } else {
            label.innerHTML = 'Upload Draft Peraturan (Opsional)';
        }
    });
</script>