<!-- Modern Google Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

<div class="validasi-premium-shell detail-view animate__animated animate__fadeIn">
    <div class="container-fluid py-4">
        <!-- Premium Header -->
        <div class="detail-header mb-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left">
                    <div class="d-flex align-items-center mb-2">
                        <div class="back-link me-3">
                            <a href="<?= base_url('validasi') ?>" class="btn btn-soft-emerald rounded-circle">
                                <i class="material-icons-round align-middle">arrow_back</i>
                            </a>
                        </div>
                        <h1 class="h2 fw-bold text-dark font-outfit mb-0"><?= esc($title) ?></h1>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 ms-5 px-1">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('validasi') ?>">Validasi</a></li>
                            <li class="breadcrumb-item active">Proses</li>
                        </ol>
                    </nav>
                </div>
                <div class="header-right">
                    <div class="badge-premium-status status-emerald">
                        <span class="pulse-ring"></span>
                        <i class="material-icons-round fs-6 me-2">verified_user</i>
                        <span>Tahap Validasi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-premium-success alert-dismissible fade show mb-4 slide-in-top" role="alert">
                <div class="d-flex align-items-center">
                    <i class="material-icons-round me-3">check_circle</i>
                    <div><?= esc(session()->getFlashdata('success')) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-premium-danger alert-dismissible fade show mb-4 slide-in-top" role="alert">
                <div class="d-flex align-items-center">
                    <i class="material-icons-round me-3">report_problem</i>
                    <div><?= esc(session()->getFlashdata('error')) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Validation Errors -->
        <?php if (isset($validation) && $validation->getErrors()): ?>
            <div class="alert alert-premium-danger alert-dismissible fade show mb-4 slide-in-top" role="alert">
                <div class="d-flex align-items-start">
                    <i class="material-icons-round me-3 mt-1">error_outline</i>
                    <div>
                        <strong class="d-block mb-2">Terdapat kesalahan input:</strong>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($validation->getErrors() as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Detail Information Card -->
                <div class="glass-card mb-4">
                    <div class="card-header-premium border-bottom">
                        <h5 class="mb-0 font-outfit text-emerald-dark d-flex align-items-center">
                            <i class="material-icons-round me-2">info</i> Informasi Pengajuan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-group mb-4">
                                    <label class="info-label">Judul Peraturan</label>
                                    <div class="info-value fw-800 text-dark fs-5"><?= esc($ajuan['judul_peraturan'] ?? '-') ?></div>
                                </div>
                                <div class="info-group mb-4">
                                    <label class="info-label">Jenis Peraturan</label>
                                    <div class="info-value">
                                        <span class="badge-premium-tag bg-soft-emerald text-emerald">
                                            <?= esc($ajuan['nama_jenis'] ?? '-') ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <label class="info-label">Instansi Pemohon</label>
                                    <div class="info-value d-flex align-items-center">
                                        <div class="mini-icon-shell bg-soft-blue text-blue me-2"><i class="material-icons-round fs-6">apartment</i></div>
                                        <span class="text-dark fw-medium"><?= esc($ajuan['nama_instansi'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group mb-4">
                                    <label class="info-label">User Pemohon</label>
                                    <div class="info-value d-flex align-items-center">
                                        <div class="mini-icon-shell bg-soft-indigo text-indigo me-2"><i class="material-icons-round fs-6">person</i></div>
                                        <span class="text-dark fw-medium"><?= esc($ajuan['nama_pemohon'] ?? '-') ?></span>
                                    </div>
                                </div>
                                <div class="info-group mb-4">
                                    <label class="info-label">Tanggal Registrasi</label>
                                    <div class="info-value d-flex align-items-center">
                                        <div class="mini-icon-shell bg-soft-amber text-amber me-2"><i class="material-icons-round fs-6">event</i></div>
                                        <span class="text-muted">
                                            <?php
                                            $tanggal = !empty($ajuan['tanggal_pengajuan']) && $ajuan['tanggal_pengajuan'] != '0000-00-00 00:00:00'
                                                ? $ajuan['tanggal_pengajuan']
                                                : $ajuan['created_at'];
                                            echo date('d M Y, H:i', strtotime($tanggal));
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <label class="info-label">Petugas Verifikasi</label>
                                    <div class="info-value d-flex align-items-center">
                                        <div class="mini-icon-shell bg-soft-purple text-purple me-2"><i class="material-icons-round fs-6">how_to_reg</i></div>
                                        <span class="fw-bold text-dark"><?= esc($ajuan['nama_verifikator'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Card -->
                <div class="glass-card mb-4 document-section">
                    <div class="card-header-premium border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 font-outfit text-primary-indigo">
                            <i class="material-icons-round me-2">attachment</i> Berkas Pendukung
                        </h5>
                        <span class="badge bg-soft-indigo text-indigo border px-3 rounded-pill"><?= count($dokumen) ?> Files</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($dokumen)): ?>
                            <div class="table-responsive">
                                <table class="table table-premium mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tipe & Nama File</th>
                                            <th class="text-center">Waktu Unggah</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dokumen as $doc) : ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="file-premium-icon bg-light rounded-3 p-2 me-3">
                                                            <i class="material-icons-round text-danger fs-3">picture_as_pdf</i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-800 text-dark mb-0"><?= esc(ucwords(str_replace('_', ' ', $doc['tipe_dokumen']))) ?></div>
                                                            <div class="text-muted small text-truncate" style="max-width: 300px;"><?= esc($doc['nama_file_original']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="small fw-bold text-dark"><?= date('d M Y', strtotime($doc['created_at'])) ?></div>
                                                    <div class="text-muted tiny">Pukul <?= date('H:i', strtotime($doc['created_at'])) ?> WIB</div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('harmonisasi/download/' . $doc['id']) ?>" 
                                                       class="btn btn-action-circle btn-soft-primary me-2" 
                                                       title="Unduh Dokumen">
                                                        <i class="material-icons-round">file_download</i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 opacity-75">
                                <i class="material-icons-round display-1 text-muted mb-3">folder_open</i>
                                <h5 class="text-muted">Tidak ada dokumen yang dilampirkan</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Form Card -->
                <div class="glass-card mb-5 action-section overflow-hidden">
                    <div class="card-header-premium border-bottom bg-emerald-soft">
                        <h5 class="mb-0 font-outfit text-emerald-dark d-flex align-items-center">
                            <i class="material-icons-round me-2">rule</i> Keputusan Validasi
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?= form_open('validasi/submitAksi', ['id' => 'validasiForm', 'enctype' => 'multipart/form-data']) ?>
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_ajuan" value="<?= esc($ajuan['id'] ?? '') ?>">

                        <div class="mb-4">
                            <label for="catatan" class="form-label-premium">
                                <i class="material-icons-round fs-6 me-1">comment</i> Catatan Validasi
                            </label>
                            <textarea class="form-control premium-input" id="catatan" name="catatan" rows="4" 
                                      placeholder="Tuliskan hasil review substansi, catatan perbaikan, atau alasan penolakan..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="dokumen_revisi" class="form-label-premium">
                                <i class="material-icons-round fs-6 me-1">upload_file</i> Unggah Berkas Hasil Revisi/Validasi (Opsional)
                            </label>
                            <div class="premium-file-upload">
                                <input type="file" class="form-control" id="dokumen_revisi" name="dokumen_revisi" accept=".pdf,.doc,.docx">
                                <div class="upload-hint mt-2 text-muted small">
                                    <i class="material-icons-round fs-6 align-middle me-1">info</i>
                                    Format: PDF, DOC, DOCX. Maksimal 10MB.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-3 mt-5">
                            <button type="submit" name="aksi" value="lanjutkan" class="btn btn-premium-green flex-grow-1 py-3 rounded-4 shadow-sm hvr-grow">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <i class="material-icons-round">task_alt</i>
                                    <div class="text-start">
                                        <div class="fw-800 fs-5 mb-0">Lanjutkan</div>
                                        <div class="tiny opacity-75 fw-normal">Kirim ke Tahap Finalisasi</div>
                                    </div>
                                </div>
                            </button>
                            
                            <button type="submit" name="aksi" value="revisi" class="btn btn-premium-amber px-4 rounded-4 shadow-sm hvr-grow"
                                    onclick="return confirm('Kembalikan ajuan ini untuk dilakukan revisi oleh pemohon?');">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="material-icons-round">edit_note</i>
                                    <div class="text-start">
                                        <div class="fw-bold mb-0">Revisi</div>
                                        <div class="tiny opacity-75">Butuh Perbaikan</div>
                                    </div>
                                </div>
                            </button>

                            <button type="submit" name="aksi" value="tolak" class="btn btn-premium-danger px-4 rounded-4 shadow-sm hvr-grow" 
                                    onclick="return confirm('Apakah Anda yakin ingin MENOLAK ajuan ini secara permanen?');">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="material-icons-round">block</i>
                                    <div class="text-start">
                                        <div class="fw-bold mb-0">Tolak</div>
                                        <div class="tiny opacity-75">Batalkan Ajuan</div>
                                    </div>
                                </div>
                            </button>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- History Timeline -->
                <div class="glass-card mb-4 h-100">
                    <div class="card-header-premium border-bottom">
                        <h5 class="mb-0 font-outfit text-dark d-flex align-items-center">
                            <i class="material-icons-round me-2 text-muted">history</i> Riwayat Proses
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($histori)): ?>
                            <div class="premium-timeline">
                                <?php foreach ($histori as $item) : ?>
                                    <div class="timeline-box">
                                        <div class="timeline-node"></div>
                                        <div class="timeline-info">
                                            <div class="timeline-header d-flex justify-content-between align-items-start mb-1">
                                                <div class="timeline-status fw-800 text-dark small text-uppercase ls-1">
                                                    <?= esc($item['status_sekarang'] ?? 'Update') ?>
                                                </div>
                                                <div class="timeline-date tiny text-muted">
                                                    <?= date('d M Y', strtotime($item['tanggal_aksi'] ?? $item['created_at'])) ?>
                                                </div>
                                            </div>
                                            <div class="timeline-desc text-muted mb-2 small italic">
                                                "<?= esc($item['keterangan'] ?? 'Tanpa catatan tambahan') ?>"
                                            </div>
                                            <div class="timeline-footer">
                                                <span class="badge bg-soft-dark text-dark tiny px-2 py-1">
                                                    <i class="material-icons-round fs-6 align-middle me-1">account_circle</i>
                                                    <?= esc($item['nama_user'] ?? 'Sistem') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 opacity-50">
                                <i class="material-icons-round display-4 mb-3">timeline</i>
                                <p class="small">Belum ada riwayat proses terekam.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Guidance Card -->
                <div class="glass-card border-0 bg-gradient-emerald text-white mb-4">
                    <div class="card-body p-4">
                        <h5 class="font-outfit fw-bold mb-3 d-flex align-items-center">
                            <i class="material-icons-round me-2">verified</i> Panduan Validator
                        </h5>
                        <div class="guide-item d-flex mb-3">
                            <div class="guide-num">1</div>
                            <div class="small">Pastikan substansi hukum dalam draf peraturan telah sesuai dengan peraturan perundangan yang lebih tinggi.</div>
                        </div>
                        <div class="guide-item d-flex mb-3">
                            <div class="guide-num">2</div>
                            <div class="small">Periksa apakah poin-poin perbaikan dari tahap verifikasi telah diakomodasi (lihat riwayat).</div>
                        </div>
                        <div class="guide-item d-flex">
                            <div class="guide-num">3</div>
                            <div class="small">Gunakan tombol <strong>Revisi</strong> bila draf masih memerlukan perbaikan teknis/substantif oleh pemohon.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Design System for Validasi Process */
    :root {
        --primary-indigo: #6366f1;
        --indigo-soft: #f5f3ff;
        --emerald-premium: #10b981;
        --emerald-dark: #065f46;
        --emerald-soft: #ecfdf5;
        --amber-premium: #f59e0b;
        --amber-dark: #92400e;
        --amber-soft: #fffbeb;
        --rose-premium: #ef4444;
        --rose-soft: #fef2f2;
        --blue-premium: #3b82f6;
        --blue-soft: #eff6ff;
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.4);
        --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    }

    .validasi-premium-shell {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        min-height: 100vh;
    }

    .font-outfit { font-family: 'Outfit', sans-serif; }
    .fw-800 { font-weight: 800; }
    .ls-1 { letter-spacing: 1px; }
    .tiny { font-size: 0.72rem; }
    .italic { font-style: italic; }

    /* Glass Cards */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .card-header-premium {
        padding: 1.25rem 1.5rem;
        background: rgba(255, 255, 255, 0.5);
    }

    /* UI Components */
    .btn-soft-emerald { background: var(--emerald-soft); color: var(--emerald-premium); border: none; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
    .btn-soft-emerald:hover { transform: scale(1.1); background: var(--emerald-premium); color: white; }

    .badge-premium-status { display: inline-flex; align-items: center; padding: 0.6rem 1.25rem; border-radius: 30px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; position: relative; }
    .status-emerald { background: var(--emerald-soft); color: var(--emerald-dark); }
    .pulse-ring { position: absolute; left: 12px; top: 12px; width: 10px; height: 10px; border-radius: 50%; background: currentColor; animation: pulse-ring 2s infinite; opacity: 0.3; }
    @keyframes pulse-ring { 0% { transform: scale(0.5); opacity: 1; } 100% { transform: scale(3); opacity: 0; } }

    .info-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem; }
    .badge-premium-tag { padding: 0.4rem 1rem; border-radius: 30px; font-weight: 700; font-size: 0.75rem; border: 1px solid rgba(0,0,0,0.05); }
    .mini-icon-shell { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .bg-soft-indigo { background: #f5f3ff; color: #6366f1; }
    .bg-soft-emerald { background: var(--emerald-soft); color: var(--emerald-premium); }
    .bg-soft-primary { background: var(--blue-soft); color: var(--blue-premium); }

    /* Documents Table */
    .table-premium thead th { background: #f8fafc; border: none; padding: 1rem; font-weight: 700; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
    .table-premium td { vertical-align: middle; padding: 1.25rem 1rem; border-bottom: 1px solid #f1f5f9; }
    
    .btn-action-circle { width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: none; transition: all 0.3s; }
    .btn-soft-primary { background: var(--blue-soft); color: var(--blue-premium); }
    .btn-soft-primary:hover { background: var(--blue-premium); color: white; transform: rotate(15deg); }

    /* Form Styling */
    .premium-input { border-radius: 1rem; border: 1.5px solid #e2e8f0; padding: 1rem; font-size: 0.95rem; transition: all 0.3s; background: #fcfdfe; }
    .premium-input:focus { border-color: var(--emerald-premium); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); background: white; }
    .form-label-premium { font-weight: 700; color: #475569; margin-bottom: 0.75rem; display: block; font-size: 0.9rem; }

    .btn-premium-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; transition: all 0.3s; }
    .btn-premium-green:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3) !important; color: white; }
    
    .btn-premium-amber { background: var(--amber-soft); color: var(--amber-dark); border: 1px solid var(--amber-soft); transition: all 0.3s; }
    .btn-premium-amber:hover { background: var(--amber-premium); color: white; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2) !important; }

    .btn-premium-danger { background: var(--rose-soft); color: var(--rose-premium); border: 1px solid var(--rose-soft); transition: all 0.3s; }
    .btn-premium-danger:hover { background: var(--rose-premium); color: white; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2) !important; }

    /* Timeline Styling */
    .premium-timeline { position: relative; padding-left: 2rem; }
    .premium-timeline::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
    .timeline-box { position: relative; margin-bottom: 2rem; background: #f8fafc; padding: 1rem; border-radius: 1rem; border-left: 4px solid var(--emerald-premium); }
    .timeline-node { position: absolute; left: -2.35rem; top: 1.25rem; width: 10px; height: 10px; border-radius: 50%; background: var(--emerald-premium); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2); }

    /* Helper Card */
    .bg-gradient-emerald { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
    .guide-num { width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; margin-right: 12px; flex-shrink: 0; }

    /* Animation effects */
    .slide-in-top { animation: slideInTop 0.5s cubic-bezier(0.23, 1, 0.32, 1) both; }
    @keyframes slideInTop { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .hvr-grow { transition: all 0.3s; }
    .hvr-grow:hover { transform: scale(1.02); }
</style>

<script>
    $(document).ready(function() {
        // Form submission loading effect
        $('#validasiForm').on('submit', function() {
            var btn = $(this).find('button[type="submit"]:focus');
            btn.addClass('disabled').html('<div class="spinner-border spinner-border-sm me-2"></div> Memproses...');
        });
    });
</script>