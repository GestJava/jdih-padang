<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-stamp text-primary me-2"></i><?= esc($title) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Legalisasi Sekda</li>
                </ol>
            </nav>
        </div>


        <!-- TTE Sekda Section (Final Authority) -->
        <?php if (!empty($pending_tte)): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-stamp me-2"></i>Dokumen Menunggu TTE Sekda (Final Authority)
                        <span class="badge bg-light text-dark ms-2"><?= count($pending_tte) ?> dokumen</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tte-sekda-table" class="table table-striped table-hover">
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
                                            <div class="fw-bold text-success"><?= esc($item['judul_peraturan']) ?></div>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?= esc($item['nama_pemohon'] ?? 'N/A') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success text-white">
                                                <?= esc($item['nama_jenis']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($item['nama_instansi'] ?? 'N/A') ?></td>
                                        <td>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($item['tanggal_pengajuan'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i>Menunggu TTE Sekda
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
        <?php endif; ?>

        <!-- Paraf Sekda Section (Intermediate) -->
        <?php if (!empty($pending_paraf)): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-signature me-2"></i>Dokumen Menunggu Paraf Sekda (Lanjut ke Wawako)
                        <span class="badge bg-light text-dark ms-2"><?= count($pending_paraf) ?> dokumen</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="paraf-sekda-table" class="table table-striped table-hover">
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
                                foreach ($pending_paraf as $item): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold text-primary"><?= esc($item['judul_peraturan']) ?></div>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?= esc($item['nama_pemohon'] ?? 'N/A') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary text-white">
                                                <?= esc($item['nama_jenis']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($item['nama_instansi'] ?? 'N/A') ?></td>
                                        <td>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($item['tanggal_pengajuan'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white">
                                                <i class="fas fa-signature me-1"></i>Menunggu Paraf Sekda
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
        <?php endif; ?>

        <!-- Empty State -->
        <?php if (empty($pending_tte) && empty($pending_paraf)): ?>
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Dokumen</h5>
                    <p class="text-muted">Saat ini tidak ada dokumen yang memerlukan tindakan Sekretaris Daerah.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- TTE Modal -->
<div class="modal fade" id="tteModal" tabindex="-1" aria-labelledby="tteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="tteModalLabel">
                    <i class="fas fa-stamp me-2"></i>Proses TTE dengan BSRE
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="tteForm">
                    <div class="mb-3">
                        <label for="nik" class="form-label">NIK Penandatangan</label>
                        <input type="text" class="form-control" id="nik" name="nik"
                            maxlength="16" pattern="[0-9]{16}" required>
                        <div class="form-text">Masukkan 16 digit NIK untuk sertifikat digital</div>
                    </div>
                    <div class="mb-3">
                        <label for="passphrase" class="form-label">Passphrase Sertifikat</label>
                        <input type="password" class="form-control" id="passphrase" name="passphrase"
                            minlength="8" required>
                        <div class="form-text">Passphrase untuk sertifikat digital BSRE</div>
                    </div>
                    <div class="mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi TTE Sekda:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Sistem akan generate <strong>nomor peraturan otomatis</strong> untuk Keputusan Sekda</li>
                                <li>Nomor akan diurutkan berdasarkan <strong>waktu TTE</strong></li>
                                <li>Dokumen akan dikirim ke <strong>server BSRE</strong> untuk TTE</li>
                                <li>TTE Sekda akan menghasilkan <strong>dokumen FINAL</strong> dan <strong>SELESAI</strong></li>
                                <li>Proses ini <strong>tidak dapat dibatalkan</strong> setelah berhasil</li>
                            </ul>
                        </div>
                    </div>
                    <input type="hidden" id="ajuan_id" name="ajuan_id" value="">
                    <input type="hidden" id="tte_type" name="tte_type" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="submitTTE()">
                    <i class="fas fa-stamp me-1"></i>Proses TTE
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables untuk TTE
    let lastTTERequest = 0;
    const TTE_COOLDOWN = 2000; // 2 seconds
    let currentAjuanId = null;
    let currentBootbox = null;

    $(document).ready(function() {
        // Initialize DataTables
        if ($('#tte-sekda-table').length) {
            $('#tte-sekda-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                order: [
                    [4, 'asc']
                ], // Sort by tanggal pengajuan
                pageLength: 25
            });
        }

        if ($('#paraf-sekda-table').length) {
            $('#paraf-sekda-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                order: [
                    [4, 'asc']
                ], // Sort by tanggal pengajuan
                pageLength: 25
            });
        }
    });

    function processTTE(ajuan_id, type) {
        $('#ajuan_id').val(ajuan_id);
        $('#tte_type').val(type);
        $('#tteModal').modal('show');
    }

    function processTTESekda(ajuan_id) {
        console.log('processTTESekda called with ajuan_id:', ajuan_id);

        // Rate limiting check
        const now = Date.now();
        if (now - lastTTERequest < TTE_COOLDOWN) {
            alert('Tunggu sebentar sebelum melakukan aksi lagi.');
            return;
        }
        lastTTERequest = now;

        // Simpan ID ajuan untuk digunakan di bootbox
        currentAjuanId = ajuan_id;
        console.log('currentAjuanId set to:', currentAjuanId);

        // Get ajuan data untuk preview nomor
        console.log('Fetching ajuan data...');
        $.get('<?= base_url('legalisasi/getAjuanData') ?>/' + ajuan_id)
            .done(function(data) {
                console.log('Ajuan data received:', data);
                showTTESekdaBootbox(data);
            })
            .fail(function(xhr, status, error) {
                console.error('Failed to fetch ajuan data:', xhr, status, error);
                alert('Gagal mengambil data ajuan: ' + error);
            });
    }

    function showTTESekdaBootbox(data) {
        console.log('showTTESekdaBootbox called with data:', data);

        const tteContent = `
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>PERHATIAN:</strong> TTE Sekda akan menghasilkan nomor final dan pengesahan resmi.
                Proses ini tidak dapat dibatalkan dan dokumen akan langsung SELESAI.
            </div>

            <form id="tteSekdaForm">
                <div class="mb-3">
                    <label for="sekda_nik" class="form-label">
                        <i class="fas fa-id-card me-1"></i>NIK Sekretaris Daerah <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="sekda_nik" name="nik"
                        maxlength="16" pattern="[0-9]{16}" required>
                    <div class="form-text">Masukkan 16 digit NIK Sekretaris Daerah untuk sertifikat digital BSRE</div>
                </div>
                <div class="mb-3">
                    <label for="sekda_passphrase" class="form-label">
                        <i class="fas fa-lock me-1"></i>Passphrase Sertifikat Sekretaris Daerah <span class="text-danger">*</span>
                    </label>
                    <input type="password" class="form-control" id="sekda_passphrase" name="passphrase"
                        minlength="8" required>
                    <div class="form-text">Passphrase untuk sertifikat digital BSRE Sekretaris Daerah</div>
                </div>

                <div class="mb-3">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
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
                        <strong>Proses TTE BSRE Sekda:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Sistem akan generate <strong>nomor peraturan otomatis</strong></li>
                            <li>Dokumen akan diberi nomor dan dikirim ke <strong>server BSRE</strong></li>
                            <li>TTE akan menggunakan <strong>sertifikat digital resmi</strong></li>
                            <li>Dokumen akan <strong>LANGSUNG SELESAI</strong> setelah TTE</li>
                        </ul>
                    </div>
                </div>
            </form>
        `;

        console.log('Creating bootbox dialog...');
        currentBootbox = bootbox.dialog({
            title: '<i class="fas fa-stamp me-2"></i>TTE Sekretaris Daerah',
            message: tteContent,
            size: 'large',
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times me-1"></i>Batal',
                    className: 'btn-secondary'
                },
                confirm: {
                    label: '<i class="fas fa-stamp me-1"></i>Proses TTE Sekda',
                    className: 'btn-success',
                    callback: function() {
                        const form = document.getElementById('tteSekdaForm');
                        if (form.checkValidity()) {
                            const formData = new FormData(form);
                            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                            // Show loading
                            const confirmBtn = currentBootbox.find('.btn-success');
                            const originalText = confirmBtn.html();
                            confirmBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');
                            confirmBtn.prop('disabled', true);

                            // Process TTE Sekda
                            $.post('<?= base_url('legalisasi/processTTESekda') ?>/' + currentAjuanId, {
                                    nik: formData.get('nik'),
                                    passphrase: formData.get('passphrase'),
                                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                                })
                                .done(function(response) {
                                    if (response.success) {
                                        bootbox.alert({
                                            title: '<i class="fas fa-check-circle me-2"></i>TTE Sekda Berhasil',
                                            message: `TTE Sekda berhasil diproses!<br><br><strong>Nomor peraturan:</strong> ${response.nomor_peraturan || 'N/A'}<br><strong>Status:</strong> Dokumen telah SELESAI`,
                                            callback: function() {
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        bootbox.alert({
                                            title: '<i class="fas fa-exclamation-triangle me-2"></i>TTE Sekda Gagal',
                                            message: response.message || 'Terjadi kesalahan saat memproses TTE Sekda'
                                        });
                                    }
                                })
                                .fail(function(xhr) {
                                    const response = xhr.responseJSON;
                                    bootbox.alert({
                                        title: '<i class="fas fa-exclamation-triangle me-2"></i>TTE Sekda Gagal',
                                        message: response?.message || 'Terjadi kesalahan server'
                                    });
                                })
                                .always(function() {
                                    confirmBtn.html(originalText);
                                    confirmBtn.prop('disabled', false);
                                });
                        } else {
                            form.reportValidity();
                            return false;
                        }
                    }
                }
            }
        });
    }

    function processParaf(ajuan_id, type) {
        if (confirm('Apakah Anda yakin akan memberikan paraf untuk dokumen ini?')) {
            // Process paraf (non-TTE)
            $.post('<?= base_url('legalisasi/processParaf') ?>', {
                    ajuan_id: ajuan_id,
                    paraf_type: type,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                })
                .done(function(response) {
                    if (response.success) {
                        alert('Paraf berhasil diberikan!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                })
                .fail(function() {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
        }
    }

    function submitTTE() {
        const form = $('#tteForm');
        const ajuan_id = $('#ajuan_id').val();
        const nik = $('#nik').val();
        const passphrase = $('#passphrase').val();

        if (!nik || nik.length !== 16) {
            alert('NIK harus 16 digit!');
            return;
        }

        if (!passphrase || passphrase.length < 8) {
            alert('Passphrase minimal 8 karakter!');
            return;
        }

        if (confirm('Apakah Anda yakin akan memproses TTE? Nomor peraturan akan digenerate otomatis dan tidak dapat diubah.')) {
            // Show loading
            const submitBtn = event.target;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses TTE...';
            submitBtn.disabled = true;

            // Process TTE dengan BSRE
            $.post('<?= base_url('legalisasi/processTTE') ?>/' + ajuan_id, {
                    nik: nik,
                    passphrase: passphrase,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                })
                .done(function(response) {
                    if (response.success) {
                        alert('TTE berhasil! Nomor peraturan: ' + response.nomor_peraturan);
                        $('#tteModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON;
                    alert('TTE gagal: ' + (response?.message || 'Terjadi kesalahan server'));
                })
                .always(function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        }
    }

    function refreshData() {
        location.reload();
    }

    function showTTESekdaStatus() {
        // Show loading
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        button.disabled = true;

        // Get TTE Sekda status
        $.get('<?= base_url('legalisasi/getTTESekdaStatus') ?>')
            .done(function(response) {
                if (response.success) {
                    const data = response.data;
                    let message = '=== STATUS TTE SEKDDA ===\n\n';
                    message += `📋 Pending TTE: ${data.stats.pending_count} dokumen\n`;
                    message += `✅ Completed TTE: ${data.stats.completed_count} dokumen\n`;
                    message += `📊 Total Bulan Ini: ${data.stats.total_this_month} dokumen\n\n`;

                    if (data.pending_tte.length > 0) {
                        message += '📝 Dokumen Pending:\n';
                        data.pending_tte.forEach((item, index) => {
                            message += `${index + 1}. ${item.judul_peraturan} (${item.nama_jenis})\n`;
                        });
                        message += '\n';
                    }

                    if (data.completed_tte.length > 0) {
                        message += '✅ Dokumen Selesai (30 hari terakhir):\n';
                        data.completed_tte.slice(0, 5).forEach((item, index) => {
                            const date = new Date(item.tanggal_selesai).toLocaleDateString('id-ID');
                            message += `${index + 1}. ${item.judul_peraturan} - ${date}\n`;
                        });
                    }

                    alert(message);
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function() {
                alert('Terjadi kesalahan saat mengambil status TTE Sekda');
            })
            .always(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }
</script>

<style>
    /* Legalisasi Module Styling */
    .legalisasi-module {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    /* Enhanced table styling */
    .table thead th {
        background-color: #e9ecef !important;
        color: #212529 !important;
        border-bottom: 3px solid #495057 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 15px 10px;
        vertical-align: middle;
    }

    .table thead th:hover {
        background-color: #495057 !important;
        color: #ffffff !important;
        transition: all 0.3s ease;
    }

    .table tbody td {
        vertical-align: middle;
        padding: 10px 8px;
        border-top: 1px solid #f0f0f0;
    }

    /* Card styling */
    .card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    /* Border left styling */
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    /* Button enhancements */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Badge styling */
    .badge {
        font-weight: 500;
        border-radius: 6px;
        padding: 0.5em 0.75em;
    }

    /* Modal enhancements */
    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        border-bottom: none;
        border-radius: 10px 10px 0 0;
    }

    /* Alert styling */
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
        }
    }
</style>