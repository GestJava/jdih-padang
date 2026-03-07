<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus text-primary me-2"></i><?= esc($title) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('harmonisasi') ?>">Harmonisasi</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('penugasan') ?>">Penugasan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tugaskan Verifikator</li>
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
            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-edit me-2"></i>Formulir Penugasan Verifikator
                    </h6>
                </div>
                <div class="card-body">
                    <?= form_open('penugasan/assign', ['id' => 'assignmentForm']) ?>
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_ajuan" value="<?= esc($ajuan['id_ajuan']) ?>">

                    <!-- Ajuan Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-1"></i>Informasi Ajuan
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Judul Rancangan</label>
                                <div class="form-control-plaintext bg-light p-2 rounded">
                                    <strong><?= esc($ajuan['judul_peraturan']) ?></strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Jenis Peraturan</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-info text-white fs-6">
                                        <?= esc($ajuan['nama_jenis']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Instansi Pemohon</label>
                                <div class="form-control-plaintext">
                                    <i class="fas fa-building me-1 text-muted"></i>
                                    <?= esc($ajuan['nama_instansi']) ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">User Pemohon</label>
                                <div class="form-control-plaintext">
                                    <i class="fas fa-user me-1 text-muted"></i>
                                    <?= esc($ajuan['nama_pemohon']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Tanggal Pengajuan</label>
                                <div class="form-control-plaintext">
                                    <i class="fas fa-calendar me-1 text-muted"></i>
                                    <?php
                                    $tanggal_tampil = !empty($ajuan['tanggal_pengajuan']) && $ajuan['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                        ? $ajuan['tanggal_pengajuan']
                                        : $ajuan['created_at'];
                                    echo date('d/m/Y H:i', strtotime($tanggal_tampil));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Status Saat Ini</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-warning text-dark">
                                        <?= esc($ajuan['nama_status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Dokumen -->
                    <?php if (!empty($dokumen)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header bg-success text-white py-3">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-paperclip me-2"></i>Dokumen Terlampir
                                    <span class="badge bg-light text-dark ms-2"><?= count($dokumen) ?> file</span>
                                </h6>
                            </div>
                            <div class="card-body">
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
                                                title="Unduh Dokumen"
                                                target="_blank">
                                                <i class="fas fa-download"></i> Unduh
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow mb-4">
                            <div class="card-header bg-warning text-dark py-3">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Dokumen Belum Tersedia
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Perhatian:</strong> Belum ada dokumen yang diunggah oleh pemohon.
                                    Anda dapat melanjutkan penugasan atau menunggu pemohon mengunggah dokumen terlebih dahulu.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <!-- Verifikator Selection -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-users me-1"></i>Pilih Verifikator
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="mb-4">
                                <label for="id_user_verifikator" class="form-label">
                                    Petugas Verifikator <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg"
                                    id="id_user_verifikator"
                                    name="id_user_verifikator"
                                    required>
                                    <option value="">-- Pilih Petugas Verifikator --</option>
                                    <?php if (!empty($verifikator_list)): ?>
                                        <?php foreach ($verifikator_list as $verifikator): ?>
                                            <option value="<?= esc($verifikator['id']) ?>">
                                                <i class="fas fa-user me-1"></i>
                                                <?= esc($verifikator['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Tidak ada verifikator tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Pilih salah satu petugas verifikator yang akan menangani ajuan ini
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= base_url('penugasan') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Penugasan
                        </a>
                        <button type="submit"
                            class="btn btn-primary"
                            id="submitBtn"
                            <?= empty($verifikator_list) ? 'disabled' : '' ?>>
                            <i class="fas fa-user-plus me-1"></i>
                            <span class="btn-text">Tugaskan Sekarang</span>
                        </button>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-lightbulb me-2"></i>Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-info">Proses Penugasan</h6>
                        <p class="small text-muted">
                            Setelah memilih verifikator dan menekan tombol "Tugaskan Sekarang", status ajuan akan berubah menjadi <strong>"Proses Verifikasi"</strong> dan akan muncul di dashboard verifikator yang bersangkutan.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-info">Langkah Selanjutnya</h6>
                        <ol class="small text-muted">
                            <li>Verifikator akan menerima notifikasi penugasan</li>
                            <li>Verifikator dapat memproses ajuan di dashboard mereka</li>
                            <li>Status akan diperbarui sesuai dengan proses verifikasi</li>
                        </ol>
                    </div>

                    <?php if (!empty($dokumen)): ?>
                        <div class="mb-3">
                            <h6 class="text-success">
                                <i class="fas fa-file-alt me-1"></i>Dokumen Tersedia
                            </h6>
                            <p class="small text-muted mb-2">
                                <?= count($dokumen) ?> dokumen telah diunggah oleh pemohon dan dapat diunduh untuk review.
                            </p>
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Tips:</strong> Review dokumen sebelum menugaskan verifikator untuk memastikan kelengkapan berkas.
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <h6 class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>Dokumen Belum Tersedia
                            </h6>
                            <p class="small text-muted mb-2">
                                Belum ada dokumen yang diunggah oleh pemohon.
                            </p>
                            <div class="alert alert-warning small">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Catatan:</strong> Anda dapat melanjutkan penugasan atau menunggu pemohon mengunggah dokumen.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="card shadow">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-pie me-2"></i>Statistik Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12">
                            <div class="mb-2">
                                <i class="fas fa-users fa-2x text-success mb-2"></i>
                                <h4 class="text-success"><?= count($verifikator_list) ?></h4>
                                <small class="text-muted">Verifikator Tersedia</small>
                            </div>
                        </div>
                    </div>
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

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            submitBtn.disabled = true;
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';

            // Add visual feedback
            submitBtn.classList.add('disabled');
        });

        // Verifikator selection validation
        const verifikatorSelect = document.getElementById('id_user_verifikator');
        verifikatorSelect.addEventListener('change', function() {
            if (this.value) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('disabled');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
            }
        });

        // Enhanced select styling
        verifikatorSelect.addEventListener('focus', function() {
            this.classList.add('border-primary');
        });

        verifikatorSelect.addEventListener('blur', function() {
            this.classList.remove('border-primary');
        });
    });
</script>

<style>
    /* Enhanced form styling */
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .form-control-plaintext.bg-light {
        border: 1px solid #e9ecef;
    }

    /* Button enhancements */
    .btn {
        transition: all 0.2s ease;
    }

    .btn:hover:not(.disabled) {
        transform: translateY(-1px);
    }

    .btn.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Card enhancements */
    .card {
        border: none;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }

    /* Badge enhancements */
    .badge {
        padding: 0.5em 0.75em;
    }

    /* Loading animation */
    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .btn.disabled {
        animation: pulse 2s infinite;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .d-flex.gap-2 {
            flex-direction: column;
        }

        .d-flex.gap-2>* {
            margin-bottom: 0.5rem;
        }
    }
</style>