<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-crown text-info me-2"></i><?= esc($title) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Legalisasi Walikota</li>
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

        <!-- TTE Walikota Section (Final Authority) -->
        <?php if (!empty($pending_tte)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background-color: #20c997; color: #fff;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-crown me-2"></i>Dokumen Menunggu TTE Walikota (Final Authority)
                        <span class="badge bg-dark text-white ms-2"><?= count($pending_tte) ?> dokumen</span>
                    </h6>
                </div>
                <div class="card-body">
                    
                    <div class="table-responsive">
                        <table id="tte-walikota-table" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Peraturan</th>
                                    <th>Jenis</th>
                                    <th>Instansi</th>
                                    <th>Tgl Pengajuan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($pending_tte as $item): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold text-info"><?= esc($item['judul_peraturan']) ?></div>
                                            
                                        </td>
                                        <td>
                                            <span class="badge text-white" style="background-color: #20c997;">
                                                <?= esc($item['nama_jenis']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($item['nama_instansi'] ?? 'N/A') ?></td>
                                        <td>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($item['tanggal_pengajuan'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger text-white">
                                                <i class="fas fa-crown me-1"></i>Menunggu TTE Walikota
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('legalisasi/detail/' . $item['id']) ?>" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i> Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-crown fa-3x text-info mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Dokumen untuk TTE</h5>
                    <p class="text-muted">Saat ini tidak ada dokumen yang memerlukan TTE Walikota.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- TTE Processing menggunakan Bootbox -->

<script>
    $(document).ready(function() {
        // Initialize DataTables
        if ($('#tte-walikota-table').length) {
            $('#tte-walikota-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                order: [
                    [4, 'asc']
                ], // Sort by tanggal pengajuan (oldest first)
                pageLength: 25,
                columnDefs: [{
                        targets: [5, 6],
                        orderable: false
                    }, // Status dan aksi tidak bisa diurutkan
                    {
                        targets: [0, 5, 6],
                        className: 'text-center'
                    }
                ]
            });
        }
    });

    // Rate limiting untuk mencegah spam
    let lastTTERequest = 0;
    const TTE_COOLDOWN = 5000; // 5 detik cooldown

    // Global variables untuk TTE
    let currentAjuanId = null;
    let currentBootbox = null;

    function processTTE(ajuan_id, type) {
        // Rate limiting check
        const now = Date.now();
        if (now - lastTTERequest < TTE_COOLDOWN) {
            alert('Tunggu sebentar sebelum melakukan aksi lagi.');
            return;
        }
        lastTTERequest = now;

        // Simpan ID ajuan untuk digunakan di bootbox
        currentAjuanId = ajuan_id;

        // Get ajuan data untuk preview nomor
        $.get('<?= base_url('legalisasi/getAjuanData') ?>/' + ajuan_id)
            .done(function(data) {
                showTTEBootbox(data);
            })
            .fail(function() {
                alert('Gagal mengambil data ajuan');
            });
    }

    function showTTEBootbox(data) {
            const tteContent = `
            <div class="alert alert-info" role="alert" style="background-color: #20c997; border-color: #20c997; color: #fff;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>PERHATIAN:</strong> TTE Walikota akan menghasilkan nomor final dan pengesahan resmi.
                Proses ini tidak dapat dibatalkan.
            </div>

            <form id="tteWalikotaForm">
                <div class="mb-3">
                    <label for="walikota_nik" class="form-label">
                        <i class="fas fa-id-card me-1"></i>NIK Walikota <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="walikota_nik" name="nik"
                        maxlength="16" pattern="[0-9]{16}" required>
                    <div class="form-text">Masukkan 16 digit NIK Walikota untuk sertifikat digital BSRE</div>
                </div>
                <div class="mb-3">
                    <label for="walikota_passphrase" class="form-label">
                        <i class="fas fa-lock me-1"></i>Passphrase Sertifikat Walikota <span class="text-danger">*</span>
                    </label>
                    <input type="password" class="form-control" id="walikota_passphrase" name="passphrase"
                        minlength="8" required>
                    <div class="form-text">Passphrase untuk sertifikat digital BSRE Walikota</div>
                </div>

                <div class="mb-3">
                    <div class="card" style="border-color: #20c997;">
                        <div class="card-header text-white" style="background-color: #20c997;">
                            <h6 class="mb-0">
                                <i class="fas fa-list-ol me-2"></i>Nomor yang Akan Digenerate
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="nomor-preview">
                                <p class="mb-0"><strong>Jenis:</strong> <span id="preview-jenis">${data.jenis_peraturan}</span></p>
                                <p class="mb-0"><strong>Nomor:</strong> <code id="preview-nomor">${data.preview_nomor}</code></p>
                                <small class="text-muted">Nomor akan diurutkan berdasarkan waktu TTE</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Proses TTE BSRE:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Sistem akan generate <strong>nomor peraturan otomatis</strong></li>
                            <li>Dokumen akan diberi nomor dan dikirim ke <strong>server BSRE</strong></li>
                            <li>TTE akan menggunakan <strong>sertifikat digital resmi</strong></li>
                            <li>Hasil TTE akan disimpan dengan <strong>audit trail lengkap</strong></li>
                        </ul>
                    </div>
                </div>
            </form>
        `;

        currentBootbox = bootbox.dialog({
            title: '<i class="fas fa-crown me-2"></i>TTE Walikota dengan BSRE',
            message: tteContent,
            size: 'large',
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times me-1"></i>Batal',
                    className: 'btn-secondary'
                },
                proceed: {
                    label: '<i class="fas fa-crown me-1"></i>Proses TTE Walikota',
                    className: 'btn-info',
                    callback: function() {
                        submitTTEWalikota();
                        return false; // Prevent dialog from closing
                    }
                }
            }
        });
    }

    function submitTTEWalikota() {
        if (!currentAjuanId) {
            alert('ID ajuan tidak valid.');
            return;
        }

        const nik = document.getElementById('walikota_nik').value;
        const passphrase = document.getElementById('walikota_passphrase').value;

        if (!nik || nik.length !== 16) {
            alert('NIK harus 16 digit!');
            return;
        }

        if (!passphrase || passphrase.length < 8) {
            alert('Passphrase minimal 8 karakter!');
            return;
        }

        if (confirm('Apakah Anda yakin akan memproses TTE Walikota? Nomor peraturan akan digenerate otomatis dan dokumen akan disahkan secara resmi.')) {
            // Disable button untuk mencegah double click
            const proceedBtn = currentBootbox.find('.btn-info');
            const originalText = proceedBtn.html();
            proceedBtn.prop('disabled', true);
            proceedBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses TTE dengan BSRE...');

            // Process TTE dengan BSRE
            const formData = new FormData();
            formData.append('nik', nik);
            formData.append('passphrase', passphrase);
            formData.append('authority', 'walikota');
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('<?= base_url('legalisasi/processTTE') ?>/' + currentAjuanId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tutup bootbox dan reload halaman
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'TTE Walikota Berhasil!',
                            html: `
                                <div class="text-start">
                                    <p><strong>Nomor Peraturan:</strong> ${data.nomor_peraturan}</p>
                                    <p><strong>Jenis:</strong> ${data.jenis_peraturan}</p>
                                    <p><strong>Urutan:</strong> ${data.urutan_dalam_jenis}</p>
                                    <hr>
                                    <p class="mb-0"><strong>Dokumen telah disahkan secara resmi.</strong></p>
                                </div>
                            `,
                            showConfirmButton: false,
                            timer: 5000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('TTE gagal: Terjadi kesalahan server BSRE');
                })
                .finally(() => {
                    // Re-enable button
                    proceedBtn.prop('disabled', false);
                    proceedBtn.html(originalText);
                });
        }
    }

    function refreshData() {
        location.reload();
    }
</script>

<style>
    /* Walikota-specific styling */
    .progress-timeline {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
    }

    .progress-timeline .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    /* Enhanced card styling for walikota */
    .bg-gradient-info {
        background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
    }

    /* Authority-specific colors */
    .border-left-info {
        border-left: 4px solid #20c997 !important;
    }

    /* Bootbox enhancements */

    #nomor-preview code {
        background-color: #d1f2eb;
        color: #0c5460;
        padding: 0.5rem;
        border-radius: 4px;
        display: block;
        margin-top: 0.5rem;
    }

    /* Responsive timeline */
    @media (max-width: 768px) {
        .progress-timeline {
            justify-content: center;
        }

        .progress-timeline .badge {
            font-size: 0.6rem;
            padding: 0.2rem 0.4rem;
        }
    }
</style>