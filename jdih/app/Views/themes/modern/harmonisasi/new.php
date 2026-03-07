<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i><?= esc($title) ?>
                    </h5>
                </div>

                <div class="card-body">
                    <!-- Flash Messages with better styling -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= esc(session()->getFlashdata('success')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= esc(session()->getFlashdata('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('validation')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Validation Error:</strong>
                            <?php
                            $validation = session()->getFlashdata('validation');
                            if ($validation instanceof \CodeIgniter\Validation\Validation) {
                                echo $validation->listErrors();
                            } elseif (is_array($validation)) {
                                echo '<ul class="mb-0">';
                                foreach ($validation as $field => $errors) {
                                    if (is_array($errors)) {
                                        foreach ($errors as $error) {
                                            echo '<li>' . esc($error) . '</li>';
                                        }
                                    } else {
                                        echo '<li>' . esc($errors) . '</li>';
                                    }
                                }
                                echo '</ul>';
                            } else {
                                echo esc($validation);
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Loading Overlay -->
                    <div id="loading-overlay" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-light bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 1000;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Memproses pengajuan...</div>
                        </div>
                    </div>

                    <?= form_open_multipart('harmonisasi/create', [
                        'class' => 'needs-validation',
                        'novalidate' => true,
                        'id' => 'harmonisasi-form'
                    ]) ?>

                    <!-- Form Fields with improved layout -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="judul_peraturan" class="form-label fw-semibold">
                                    Judul Rancangan Peraturan <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="judul_peraturan"
                                    name="judul_peraturan"
                                    value="<?= old('judul_peraturan') ?>"
                                    required
                                    minlength="10"
                                    placeholder="Masukkan judul rancangan peraturan (minimal 10 karakter)">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Judul harus jelas dan mencerminkan isi peraturan
                                </div>
                                <div class="invalid-feedback">
                                    Judul rancangan peraturan wajib diisi minimal 10 karakter.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="id_jenis_peraturan" class="form-label fw-semibold">
                                    Jenis Peraturan <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="id_jenis_peraturan" name="id_jenis_peraturan" required>
                                    <option value="">-- Pilih Jenis Peraturan --</option>
                                    <?php foreach ($jenis_peraturan as $jenis): ?>
                                        <option value="<?= esc($jenis['id']) ?>"
                                            <?= old('id_jenis_peraturan') == $jenis['id'] ? 'selected' : '' ?>>
                                            <?= esc($jenis['nama_jenis']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Jenis peraturan wajib dipilih.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="draft_peraturan" class="form-label fw-semibold">
                                    Upload Draft Peraturan <span class="text-danger">*</span>
                                </label>
                                <input type="file"
                                    class="form-control"
                                    id="draft_peraturan"
                                    name="draft_peraturan"
                                    accept=".doc,.docx"
                                    required>
                                <div class="form-text">
                                    <i class="fas fa-file-word me-1"></i>
                                    Format: Word (DOC, DOCX) | Maksimal: 25MB
                                </div>
                                <div class="invalid-feedback">
                                    File draft peraturan wajib diupload.
                                </div>
                                <!-- File preview area -->
                                <div id="file-preview" class="mt-2 p-2 bg-light rounded d-none">
                                    <small class="text-muted">
                                        <i class="fas fa-file me-1"></i>
                                        <span id="file-name"></span>
                                        <span id="file-size" class="ms-2 badge bg-info"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="keterangan" class="form-label fw-semibold">
                                    Keterangan Tambahan
                                </label>
                                <textarea class="form-control"
                                    id="keterangan"
                                    name="keterangan"
                                    rows="4"
                                    placeholder="Masukkan keterangan tambahan jika diperlukan"><?= old('keterangan') ?></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Informasi tambahan untuk memperjelas konteks pengajuan
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons with improved styling -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= base_url('harmonisasi') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>

                        <div class="btn-group">
                            <button type="reset" class="btn btn-outline-warning me-2" id="reset-btn">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-paper-plane me-1"></i> Kirim Pengajuan
                            </button>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styles for better UI consistency */
    .card {
        border: none;
        border-radius: 0.75rem;
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-bottom: none;
    }

    .form-label.fw-semibold {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    #file-preview {
        border: 1px dashed #dee2e6;
        transition: all 0.3s ease;
    }

    .loading-spinner {
        display: none;
    }

    .btn:disabled {
        cursor: not-allowed;
    }
</style>

<script>
    // Enhanced form validation and UX
    (function() {
        'use strict';

        const form = document.getElementById('harmonisasi-form');
        const submitBtn = document.getElementById('submit-btn');
        const resetBtn = document.getElementById('reset-btn');
        const loadingOverlay = document.getElementById('loading-overlay');
        const fileInput = document.getElementById('draft_peraturan');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');

        // Form submission with loading state
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
                loadingOverlay.classList.remove('d-none');
                loadingOverlay.classList.add('d-flex');
            }
            form.classList.add('was-validated');
        });

        // Reset form handler
        resetBtn.addEventListener('click', function() {
            form.classList.remove('was-validated');
            filePreview.classList.add('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Kirim Pengajuan';
        });

        // File input handler with preview and validation
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                // Validate file size (25MB = 25 * 1024 * 1024 bytes)
                const maxSize = 25 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar. Maksimal 25MB.');
                    fileInput.value = '';
                    filePreview.classList.add('d-none');
                    return;
                }

                // Validate file type
                const allowedTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Hanya file Word (DOC atau DOCX) yang diizinkan.');
                    fileInput.value = '';
                    filePreview.classList.add('d-none');
                    return;
                }

                // Show file preview
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                filePreview.classList.remove('d-none');
            } else {
                filePreview.classList.add('d-none');
            }
        });

        // Format file size for display
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Character counter for title field
        const titleInput = document.getElementById('judul_peraturan');
        titleInput.addEventListener('input', function() {
            const length = this.value.length;
            const minLength = 10;

            if (length > 0 && length < minLength) {
                this.classList.add('is-invalid');
            } else if (length >= minLength) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });

    })();
</script>