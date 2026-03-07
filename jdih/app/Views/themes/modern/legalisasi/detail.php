    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-alt text-primary me-2"></i>Detail Ajuan Legalisasi
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('legalisasi') ?>">Legalisasi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
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

        <!-- Safety check -->
        <?php if (!isset($ajuan) || !is_array($ajuan)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Data ajuan tidak tersedia.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <!-- Detail Ajuan -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-info-circle me-2"></i>Informasi Pengajuan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%" class="text-muted">ID Ajuan</th>
                                            <td>
                                                <span class="badge bg-secondary"><?= esc($ajuan['id'] ?? $ajuan['id_ajuan'] ?? '-') ?></span>
                                                <?php if ($user_role === 'sekda' && $isKeputusanSekda): ?>
                                                    <br><small class="text-success mt-1 d-block">
                                                        <i class="fas fa-stamp me-1"></i>
                                                        <strong>TTE Sekda:</strong> Dokumen ini akan langsung selesai setelah TTE
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Judul Peraturan</th>
                                            <td>
                                                <?= esc($ajuan['judul_peraturan'] ?? '-') ?>
                                                <?php if ($user_role === 'sekda' && $isKeputusanSekda): ?>
                                                    <br><small class="text-success mt-1 d-block">
                                                        <i class="fas fa-stamp me-1"></i>
                                                        <strong>TTE Sekda:</strong> Dokumen ini akan langsung selesai setelah TTE
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Jenis Peraturan</th>
                                            <td>
                                                <?php if (!empty($ajuan['nama_jenis'])): ?>
                                                    <?php
                                                    $badgeClass = 'bg-info';
                                                    $badgeText = $ajuan['nama_jenis'];

                                                    // Khusus untuk TTE Sekda
                                                    if ($user_role === 'sekda' && $isKeputusanSekda) {
                                                        $badgeClass = 'bg-success';
                                                        $badgeText = $ajuan['nama_jenis'] . ' (TTE Final)';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?> text-white"><?= esc($badgeText) ?></span>

                                                    <?php if ($user_role === 'sekda' && $isKeputusanSekda): ?>
                                                        <br><small class="text-success mt-1 d-block">
                                                            <i class="fas fa-stamp me-1"></i>
                                                            Dokumen ini akan langsung selesai setelah TTE Sekda
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">ID: <?= esc($ajuan['id_jenis_peraturan'] ?? '-') ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Instansi Pemohon</th>
                                            <td>
                                                <?php if (!empty($ajuan['nama_instansi'])): ?>
                                                    <i class="fas fa-building me-1 text-primary"></i>
                                                    <?= esc($ajuan['nama_instansi']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">ID: <?= esc($ajuan['id_instansi_pemohon'] ?? '-') ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">User Pemohon</th>
                                            <td>
                                                <?php if (!empty($ajuan['nama_pemohon'])): ?>
                                                    <i class="fas fa-user me-1 text-success"></i>
                                                    <?= esc($ajuan['nama_pemohon']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">ID: <?= esc($ajuan['id_user_pemohon'] ?? '-') ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%" class="text-muted">Tanggal Pengajuan</th>
                                            <td>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= esc($ajuan['tanggal_pengajuan_formatted'] ?? '-') ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Status Saat Ini</th>
                                            <td>
                                                <?php
                                                $statusId = $ajuan['id_status_ajuan'] ?? null;
                                                if ($statusId) {
                                                    $statusName = \App\Config\HarmonisasiStatus::getStatusName($statusId);
                                                    $badgeClass = 'bg-success';
                                                    $icon = 'fa-check-circle';

                                                    // Khusus untuk TTE Sekda
                                                    if ($user_role === 'sekda' && $isKeputusanSekda && $statusId == 11) {
                                                        $badgeClass = 'bg-info';
                                                        $icon = 'fa-stamp';
                                                        $statusName = 'Menunggu TTE Sekda (Final)';
                                                    }

                                                    echo '<span class="badge ' . $badgeClass . ' text-white fs-6">';
                                                    echo '<i class="fas ' . $icon . ' me-1"></i>' . esc($statusName);
                                                    echo '</span>';

                                                    // Tambahkan informasi khusus untuk TTE Sekda
                                                    if ($user_role === 'sekda' && $isKeputusanSekda && $statusId == 11) {
                                                        echo '<br><small class="text-info mt-1 d-block">';
                                                        echo '<i class="fas fa-exclamation-triangle me-1"></i>';
                                                        echo 'TTE Sekda akan menghasilkan dokumen FINAL dan SELESAI';
                                                        echo '</small>';
                                                    }
                                                } else {
                                                    echo '<span class="badge bg-secondary text-white fs-6">';
                                                    echo '<i class="fas fa-question-circle me-1"></i>Tidak diketahui';
                                                    echo '</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Tanggal Selesai</th>
                                            <td>
                                                <?php if (!empty($ajuan['tanggal_selesai'])): ?>
                                                    <i class="fas fa-check-circle me-1 text-success"></i>
                                                    <?= date('d F Y H:i', strtotime($ajuan['tanggal_selesai'])) ?>
                                                <?php else: ?>
                                                    <i class="fas fa-clock me-1 text-muted"></i>
                                                    <span class="text-muted">Belum selesai</span>
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

                            <?php if (!empty($ajuan['keterangan'])): ?>
                                <div class="mt-3">
                                    <h6 class="text-muted">Keterangan:</h6>
                                    <div class="alert alert-light">
                                        <?= nl2br(esc($ajuan['keterangan'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Daftar Dokumen -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white py-3">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-paperclip me-2"></i>Dokumen Final
                                <?php if (isset($dokumen) && is_array($dokumen)): ?>
                                    <span class="badge bg-light text-dark ms-2"><?= count($dokumen) ?> file</span>
                                <?php endif; ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (isset($dokumen) && !empty($dokumen)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($dokumen as $doc) : ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold text-primary">
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    <?= esc(ucwords(str_replace('_', ' ', $doc['tipe_dokumen'] ?? 'Dokumen'))) ?>
                                                </div>
                                                <p class="mb-1"><?= esc($doc['nama_file_original'] ?? $doc['file_dokumen'] ?? 'Unknown file') ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Diunggah: <?= isset($doc['created_at']) ? date('d F Y H:i', strtotime($doc['created_at'])) : 'Tidak diketahui' ?>
                                                </small>
                                            </div>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="previewPDF('<?= base_url('legalisasi/preview/' . ($doc['id'] ?? $ajuan['id'])) ?>', '<?= esc($doc['nama_file_original'] ?? 'Dokumen') ?>')"
                                                    title="Preview PDF">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <?php if ($doc['tipe_dokumen'] === 'FINAL_TTE'): ?>
                                                    <a href="<?= base_url('legalisasi/download/' . $ajuan['id']) ?>"
                                                        class="btn btn-success btn-sm" title="Download Dokumen TTE">
                                                        <i class="fas fa-download"></i> Download TTE
                                                    </a>
                                                    <?php if ($user_role === 'sekda' && $isKeputusanSekda): ?>
                                                        <span class="badge bg-success ms-1" title="Dokumen TTE Sekda Final">
                                                            <i class="fas fa-check-circle"></i> Final
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada dokumen final yang tersedia</p>
                                    <small class="text-muted">Dokumen final akan muncul setelah proses paraf atau TTE selesai</small>
                                    <?php if ($user_role === 'sekda' && $isKeputusanSekda): ?>
                                        <div class="mt-3">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>TTE Sekda:</strong> Dokumen akan langsung selesai setelah TTE dan tidak memerlukan persetujuan lebih lanjut.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Data TTE Result -->
                    <?php if (isset($tte_data) && !empty($tte_data) && !empty($tte_data['tte_file_path'])): ?>
                        <div class="card shadow mb-4" id="tteResultCard">
                            <div class="card-header bg-primary text-white py-3">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-stamp me-2"></i>Hasil Tanda Tangan Elektronik (TTE)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%" class="text-muted">Nomor Peraturan</th>
                                                <td>
                                                    <span class="badge bg-info text-white"><?= esc($tte_data['nomor_peraturan'] ?? '-') ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Jenis Peraturan</th>
                                                <td><?= esc($tte_data['jenis_peraturan'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Tanggal Pengesahan</th>
                                                <td>
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?= isset($tte_data['tanggal_pengesahan']) ? date('d F Y', strtotime($tte_data['tanggal_pengesahan'])) : '-' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">User Role</th>
                                                <td>
                                                    <span class="badge bg-secondary"><?= esc($tte_data['tte_user_role'] ?? '-') ?></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%" class="text-muted">TTE Completed At</th>
                                                <td>
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= isset($tte_data['tte_completed_at']) ? date('d F Y H:i', strtotime($tte_data['tte_completed_at'])) : '-' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Document URL</th>
                                                <td>
                                                    <?php if (!empty($tte_data['document_url'])): ?>
                                                        <a href="<?= esc($tte_data['document_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-external-link-alt me-1"></i> Buka Dokumen
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">File Path</th>
                                                <td>
                                                    <small class="text-muted"><?= esc($tte_data['tte_file_path'] ?? '-') ?></small>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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
                            <?php if (isset($histori) && !empty($histori)): ?>
                                <div class="timeline-container" id="timelineContainer">
                                    <?php
                                    // Data sudah dalam urutan DESC (terbaru ke terlama) dari controller
                                    $totalItems = count($histori);
                                    $showLimit = 5; // Tampilkan 5 item terbaru
                                    $showMore = $totalItems > $showLimit;
                                    ?>

                                    <?php foreach ($histori as $index => $item) : ?>
                                        <div class="timeline-item <?= $index >= $showLimit ? 'timeline-item-hidden' : '' ?>"
                                            data-index="<?= $index ?>">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title text-primary">
                                                    <?= esc($item['status_sekarang'] ?? 'Status Update') ?>
                                                </h6>
                                                <p class="timeline-description">
                                                    <?= esc($item['keterangan'] ?? 'Tidak ada keterangan') ?>
                                                </p>
                                                <small class="timeline-time text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= esc($item['tanggal_formatted']) ?>
                                                    oleh <?= esc($item['nama_user']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($showMore): ?>
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="showMoreBtn">
                                            <i class="fas fa-chevron-down me-1"></i>
                                            Tampilkan <?= $totalItems - $showLimit ?> riwayat sebelumnya
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="showLessBtn">
                                            <i class="fas fa-chevron-up me-1"></i>
                                            Sembunyikan riwayat lama
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada riwayat proses</p>
                                    <?php if ($user_role === 'sekda' && $isKeputusanSekda): ?>
                                        <div class="mt-3">
                                            <div class="alert alert-info" style="background-color: #20c997; border-color: #20c997; color: #fff;">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <strong>TTE Sekda:</strong> Setelah TTE, dokumen akan langsung selesai dan tidak ada tahap berikutnya.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <!-- Back Button -->
                                <a href="<?= base_url('legalisasi') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                                </a>

                                <!-- Debug Information -->
                                <?php
                                $user_role = session('user')['nama_role'] ?? 'unknown';
                                $status_id = $ajuan['id_status_ajuan'] ?? 'unknown';
                                echo "<!-- Debug: User role: $user_role, Status ID: $status_id -->";
                                echo "<!-- Debug: User actions: " . json_encode($user_actions ?? []) . " -->";
                                ?>

                                <!-- Action buttons based on user role and status -->
                                <?php if (isset($user_actions)): ?>

                                    <?php if (isset($user_actions['can_process_tte']) && $user_actions['can_process_tte']): ?>
                                        <?php
                                        $user_role = $user_role ?? session('user')['nama_role'] ?? '';
                                        // Use data already passed from controller
                                        $namaJenis = $ajuan['nama_jenis'] ?? '';
                                        $isKeputusanSekda = $isKeputusanSekda ?? false;

                                        $tte_text = 'Proses TTE';
                                        if ($user_role === 'sekda' && $isKeputusanSekda) {
                                            $tte_text = 'Proses TTE Sekda (Final)';
                                        } elseif ($user_role === 'sekda') {
                                            $tte_text = 'Proses TTE (Lanjut ke Wawako)';
                                        } elseif ($user_role === 'walikota') {
                                            $tte_text = 'Proses TTE (Final)';
                                        }
                                        ?>
                                        <?php if ($user_role === 'sekda' && $isKeputusanSekda): ?>
                                            <button type="button" class="btn btn-success" onclick="processTTESekda(<?= $ajuan['id'] ?>)">
                                                <i class="fas fa-stamp me-1"></i> <?= $tte_text ?>
                                            </button>
                                            <div class="mt-2">
                                                <small class="text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    TTE Sekda akan menghasilkan dokumen FINAL dan SELESAI
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-success" onclick="processTTE(<?= $ajuan['id'] ?>)">
                                                <i class="fas fa-stamp me-1"></i> <?= $tte_text ?>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (isset($user_actions['can_process_paraf']) && $user_actions['can_process_paraf']): ?>
                                        <?php
                                        $user_role = session('user')['nama_role'] ?? '';
                                        $paraf_text = 'Proses Paraf';
                                        if ($user_role === 'sekda') {
                                            $paraf_text = 'Proses Paraf (Lanjut ke Wawako)';
                                        } elseif ($user_role === 'wawako') {
                                            $paraf_text = 'Proses Paraf (Lanjut ke Walikota)';
                                        } elseif ($user_role === 'kabag') {
                                            $paraf_text = 'Proses Paraf (Lanjut ke Asisten)';
                                        } elseif ($user_role === 'asisten') {
                                            $paraf_text = 'Proses Paraf (Lanjut ke Sekda)';
                                        } elseif ($user_role === 'opd') {
                                            $paraf_text = 'Proses Paraf (Lanjut ke Kabag)';
                                        }
                                        ?>
                                        <button type="button" class="btn btn-primary" onclick="processParaf(<?= $ajuan['id'] ?>)">
                                            <i class="fas fa-signature me-1"></i> <?= $paraf_text ?>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (isset($user_actions['can_revise_to_finalisasi']) && $user_actions['can_revise_to_finalisasi']): ?>
                                        <button type="button" class="btn btn-warning" onclick="revisiKeFinalisasi(<?= $ajuan['id'] ?>)">
                                            <i class="fas fa-undo me-1"></i> Revisi ke Finalisasi
                                        </button>
                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- TTE Loading Overlay -->
    <div id="tteLoadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
        <div style="text-align: center; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem; border-width: 0.4rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <h4 class="mt-4 mb-2" style="color: #333;">Memproses Tanda Tangan Elektronik</h4>
            <p class="text-muted mb-0">Menghubungi server BSrE, mohon tunggu...</p>
            <small class="text-muted d-block mt-2">Proses ini membutuhkan beberapa detik</small>
        </div>
    </div>

    <style>
        .timeline-container {
            position: relative;
            padding-left: 30px;
        }

        .timeline-container:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #007bff;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #007bff;
        }

        .timeline-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .timeline-description {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .timeline-time {
            font-size: 12px;
        }

        .card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        }

        .table th {
            color: #6c757d;
            font-weight: 600;
        }

        .badge {
            font-size: 0.75em;
        }

        .list-group-item {
            border: none;
            border-bottom: 1px solid #e9ecef;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        /* Timeline optimization styles */
        .timeline-item-hidden {
            display: none !important;
        }

        .timeline-container {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .timeline-container::-webkit-scrollbar {
            width: 6px;
        }

        .timeline-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .timeline-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .timeline-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Smooth transition for show/hide */
        .timeline-item {
            transition: all 0.3s ease;
        }

        /* Compact timeline for better space usage */
        .timeline-item {
            margin-bottom: 15px;
        }

        .timeline-content {
            padding: 12px;
        }

        .timeline-title {
            font-size: 13px;
            margin-bottom: 4px;
        }

        .timeline-description {
            font-size: 12px;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .timeline-time {
            font-size: 11px;
        }

        /* PDF Preview Modal - Larger size for better readability */
        .pdf-preview-modal .modal-dialog {
            max-width: 95% !important;
            width: 95% !important;
            margin: 1.75rem auto;
        }

        @media (min-width: 1200px) {
            .pdf-preview-modal .modal-dialog {
                max-width: 1400px !important;
                width: 1400px !important;
            }
        }

        @media (min-width: 992px) and (max-width: 1199px) {
            .pdf-preview-modal .modal-dialog {
                max-width: 95% !important;
                width: 95% !important;
            }
        }

        .pdf-preview-container {
            width: 100%;
            height: 100%;
        }
    </style>

    <script>
        // Global variables for PDF preview
        let currentPdfUrl = '';
        let currentFileName = '';

        function previewPDF(url, fileName) {
            currentPdfUrl = url;
            currentFileName = fileName;

            // Show modal using bootbox (following system pattern)
            $bootbox = bootbox.dialog({
                title: '<i class="fas fa-file-pdf me-2"></i>Preview: ' + fileName,
                message: '<div class="text-center"><div class="spinner-border text-secondary" role="status"></div><p class="mt-2">Memuat dokumen...</p></div>',
                size: 'extra-large',
                className: 'pdf-preview-modal',
                buttons: {
                    cancel: {
                        label: 'Tutup',
                        className: 'btn-secondary'
                    }
                }
            });

            // Load PDF after modal is shown
            setTimeout(() => {
                loadPDFPreview(url, $bootbox);
            }, 300);
        }

        function loadPDFPreview(url, $bootbox) {
            // Create PDF viewer content with larger height
            const pdfContent = `
                <div class="pdf-preview-container">
                    <iframe id="pdfViewer" 
                            src="${url}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                            style="width: 100%; height: calc(100vh - 250px); min-height: 700px; border: 1px solid #dee2e6; border-radius: 5px;"
                            title="PDF Preview">
                    </iframe>
                </div>
            `;

            // Update modal content
            $bootbox.find('.modal-body').html(pdfContent);
        }

        // Clean up when modal is hidden
        $(document).on('hidden.bs.modal', '.bootbox', function() {
            // Clear variables
            currentPdfUrl = '';
            currentFileName = '';
        });

        // Timeline show/hide functionality
        $(document).ready(function() {
            $('#showMoreBtn').on('click', function() {
                // Show all hidden timeline items
                $('.timeline-item-hidden').removeClass('timeline-item-hidden').addClass('timeline-item-show');

                // Hide show more button, show show less button
                $(this).addClass('d-none');
                $('#showLessBtn').removeClass('d-none');

                // Smooth scroll to bottom of timeline (untuk melihat riwayat lama - index >= 5)
                setTimeout(() => {
                    const timelineContainer = document.getElementById('timelineContainer');
                    timelineContainer.scrollTop = timelineContainer.scrollHeight;
                }, 100);
            });

            $('#showLessBtn').on('click', function() {
                // Hide items beyond the limit (index >= 5) - ini adalah riwayat lama
                $('.timeline-item[data-index]').each(function() {
                    const index = parseInt($(this).data('index'));
                    if (index >= 5) {
                        $(this).removeClass('timeline-item-show').addClass('timeline-item-hidden');
                    }
                });

                // Hide show less button, show show more button
                $(this).addClass('d-none');
                $('#showMoreBtn').removeClass('d-none');

                // Scroll to top of timeline (karena yang terbaru di atas - index 0-4)
                const timelineContainer = document.getElementById('timelineContainer');
                timelineContainer.scrollTop = 0;
            });
        });

        // Rate limiting untuk TTE
        let lastTTERequest = 0;
        const TTE_COOLDOWN = 5000; // 5 detik cooldown (TTE lebih lama)

        // Global variables untuk TTE
        let currentAjuanId = null;
        let currentBootbox = null;

        function processTTE(ajuanId) {
            // Rate limiting check
            const now = Date.now();
            if (now - lastTTERequest < TTE_COOLDOWN) {
                alert('Tunggu sebentar sebelum melakukan aksi lagi.');
                return;
            }
            lastTTERequest = now;

            // Simpan ID ajuan untuk digunakan di bootbox
            currentAjuanId = ajuanId;

            // Tampilkan bootbox TTE
            showTTEBootbox();
        }

        function showTTEBootbox() {
            const tteContent = `
                <form id="tteForm" autocomplete="off">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tte_nik" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>NIK <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="tte_nik"
                                       placeholder="Masukkan 16 digit NIK" maxlength="16" required 
                                       inputmode="numeric" pattern="[0-9]{16}"
                                       autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                                       data-lpignore="true" data-form-type="other" readonly
                                       onfocus="this.removeAttribute('readonly');"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <div class="form-text">NIK yang terdaftar di sistem BSrE (16 digit angka)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tte_password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Passphrase TTE <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="tte_password" 
                                           placeholder="Masukkan passphrase TTE" required
                                           autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                                           data-lpignore="true" data-form-type="other" readonly
                                           onfocus="this.removeAttribute('readonly');">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" onclick="togglePasswordVisibility()">
                                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Passphrase untuk mengakses sertifikat TTE</div>
                            </div>
                        </div>
                    </div>

                    <div id="tteVerificationResult"></div>
                </form>
            `;

            currentBootbox = bootbox.dialog({
                title: '<i class="fas fa-stamp me-2"></i>Proses Tanda Tangan Elektronik (TTE)',
                message: tteContent,
                size: 'large',
                buttons: {
                    cancel: {
                        label: '<i class="fas fa-times me-1"></i>Batal',
                        className: 'btn-secondary'
                    },
                    verify: {
                        label: '<i class="fas fa-check-circle me-1"></i>Verifikasi Sertifikat',
                        className: 'btn-outline-primary',
                        callback: function() {
                            verifyTteCertificate();
                            return false; // Prevent dialog from closing
                        }
                    },
                    proceed: {
                        label: '<i class="fas fa-stamp me-1"></i>Proses TTE',
                        className: 'btn-success',
                        id: 'btnProceedTte',
                        style: 'display: none;',
                        callback: function() {
                            try {
                                proceedTTE();
                            } catch (e) {
                                alert('Error: ' + e.message);
                            }
                            return false; // Prevent dialog from closing
                        }
                    }
                }
            });
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('tte_password');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Fungsi verifikasi sertifikat TTE untuk bootbox
        function verifyTteCertificate() {
            const resultDiv = document.getElementById('tteVerificationResult');
            const tteNik = document.getElementById('tte_nik')?.value;

            // Validasi NIK
            if (!tteNik || !/^\d{16}$/.test(tteNik)) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>NIK Tidak Valid</h5>
                        <p class="mb-0">NIK harus terdiri dari 16 digit angka.</p>
                    </div>`;
                return false;
            }

            // Tampilkan loading
            resultDiv.innerHTML = `
                <div class="text-center my-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Memeriksa sertifikat...</span>
                    </div>
                    <p class="mt-3 mb-0">Sedang memeriksa status sertifikat TTE...</p>
                </div>`;

            // Sembunyikan tombol proceed di bootbox
            const proceedBtn = currentBootbox.find('#btnProceedTte');
            if (proceedBtn.length) {
                proceedBtn.hide();
            }

            // Kirim request ke TTEController->cekStatusUser
            fetch('<?= base_url('api/tte/check-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        nik: tteNik
                    }),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.data) {
                        const statusData = data.data;
                        const statusCode = statusData.status_code;
                        const message = statusData.message || 'Status sertifikat tidak diketahui';
                        const isActive = statusData.is_active === true || statusData.is_active === 'true';
                        
                        // Status code 1111 = Aktif, Status code 2011 = Tidak Aktif
                        if (isActive || statusCode === '1111' || statusCode === 1111) {
                            // Sertifikat aktif
                            resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i> Status User: <strong>Aktif</strong></h5>
                                <p class="mb-0">${message}</p>
                            </div>`;

                            // Tampilkan tombol proceed di bootbox
                            if (proceedBtn.length) {
                                proceedBtn.show();
                            }
                        } else {
                            // Sertifikat tidak aktif (status_code 2011 atau lainnya)
                            resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-times-circle me-2"></i> Status User: <strong>Tidak Aktif</strong></h5>
                                <p class="mb-2">${message}</p>
                                <p class="mb-0">
                                    <a href="https://bsre.bssn.go.id" target="_blank" class="alert-link">
                                        Kunjungi BSrE untuk memperbarui sertifikat
                                    </a>
                                </p>
                            </div>`;
                        }
                    } else {
                        // Error dari server
                        resultDiv.innerHTML = `
                        <div class="alert alert-info" style="background-color: #20c997; border-color: #20c997; color: #fff;">
                            <h5><i class="fas fa-exclamation-circle me-2"></i> Gagal Memeriksa Sertifikat</h5>
                            <p class="mb-0">${data.message || 'Terjadi kesalahan saat memeriksa status sertifikat. Silakan coba lagi.'}</p>
                        </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle me-2"></i> Kesalahan Jaringan</h5>
                        <p class="mb-0">Tidak dapat terhubung ke server TTE. Pastikan koneksi internet Anda stabil dan coba lagi.</p>
                    </div>`;
                });
        }

        // Fungsi untuk memproses TTE setelah verifikasi - Menggunakan TTEController->signDocument()
        function proceedTTE() {
            if (!currentAjuanId) {
                alert('ID ajuan tidak valid.');
                return;
            }

            const nik = document.getElementById('tte_nik').value;
            const password = document.getElementById('tte_password').value;

            if (!nik || nik.length !== 16) {
                alert('NIK harus 16 digit!');
                return;
            }

            if (!password || password.length < 8) {
                alert('Passphrase minimal 8 karakter!');
                return;
            }

            // Langsung proses tanpa confirm() karena browser memblokir confirm dari callback bootbox
            // User sudah mengkonfirmasi dengan menekan tombol "Proses TTE"
            
            // Show loading message in form
                const resultDiv = document.getElementById('tteVerificationResult');
                if (resultDiv) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-warning mt-3">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-warning me-3" role="status"></div>
                                <div>
                                    <strong>⏳ Memproses TTE...</strong><br>
                                    <small>Menghubungi server BSrE, mohon tunggu beberapa saat. Jangan tutup halaman ini.</small>
                                </div>
                            </div>
                        </div>`;
                }

                // Disable tombol jika ada
                if (currentBootbox) {
                    try {
                        currentBootbox.find('button').prop('disabled', true);
                    } catch (e) {
                        // Silent fail
                    }
                }

                // Tampilkan loading overlay di card TTE result jika ada
                const tteResultCard = document.getElementById('tteResultCard');
                if (tteResultCard) {
                    tteResultCard.style.opacity = '0.6';
                    tteResultCard.style.pointerEvents = 'none';
                }

                // Step 1: Ambil id_dokumen FINAL_PARAF terlebih dahulu
                fetch('<?= base_url('legalisasi/getFinalParafDocument') ?>/' + currentAjuanId, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(docData => {
                        if (!docData.success || !docData.id_dokumen) {
                            throw new Error(docData.message || 'Dokumen FINAL_PARAF tidak ditemukan');
                        }

                        // Step 2: Kirim request ke TTEController->signDocument
                        return fetch('<?= base_url('api/tte/sign') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                id_ajuan: currentAjuanId,
                                nik: nik,
                                password: password,
                                id_dokumen: docData.id_dokumen
                            })
                        });
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message dengan SweetAlert
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'TTE Berhasil!',
                                    html: `
                                        <div class="text-start">
                                            <p><strong>Nomor Peraturan:</strong> ${data.data?.document_number || 'N/A'}</p>
                                            <p><strong>ID Ajuan:</strong> ${data.data?.id_ajuan || 'N/A'}</p>
                                            <hr>
                                            <p class="mb-0"><strong>Dokumen telah disahkan secara resmi.</strong></p>
                                        </div>
                                    `,
                                    showConfirmButton: false,
                                    timer: 5000
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert('TTE berhasil! Nomor: ' + (data.data?.document_number || 'N/A'));
                                window.location.reload();
                            }
                        } else {
                            alert('Gagal memproses TTE: ' + (data.message || 'Terjadi kesalahan. Silakan coba lagi.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('TTE gagal: ' + (error.message || 'Terjadi kesalahan server BSRE'));
                    })
                    .finally(() => {
                        // Re-enable buttons jika ada
                        if (currentBootbox) {
                            try {
                                currentBootbox.find('button').prop('disabled', false);
                            } catch (e) {
                                // Silent fail
                            }
                        }

                        // Restore card TTE result jika ada
                        const tteResultCard = document.getElementById('tteResultCard');
                        if (tteResultCard) {
                            tteResultCard.style.opacity = '1';
                            tteResultCard.style.pointerEvents = 'auto';
                        }
                    });
        }

        // Fungsi khusus untuk TTE Sekda
        function processTTESekda(ajuanId) {
            // Rate limiting check
            const now = Date.now();
            if (now - lastTTERequest < TTE_COOLDOWN) {
                alert('Tunggu sebentar sebelum melakukan aksi lagi.');
                return;
            }
            lastTTERequest = now;

            // Simpan ID ajuan untuk digunakan di bootbox
            currentAjuanId = ajuanId;

            // Tampilkan bootbox TTE Sekda
            showTTESekdaBootbox();
        }

        function showTTESekdaBootbox() {
            // Langsung tampilkan bootbox normal
            showTTESekdaBootboxNormal();
        }

        function showTTESekdaBootboxNormal() {
            // Konsisten dengan dashboard_sekda.php - dengan verifikasi sertifikat
            const tteContent = `
                <div class="alert alert-info" role="alert" style="background-color: #20c997; border-color: #20c997; color: #fff;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>PERHATIAN:</strong> TTE Sekda akan menghasilkan nomor final dan pengesahan resmi.
                    Proses ini tidak dapat dibatalkan dan dokumen akan langsung SELESAI.
                </div>

                <form id="tteSekdaForm" autocomplete="off">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tte_sekda_nik" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>NIK Sekretaris Daerah <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="tte_sekda_nik"
                                    placeholder="Masukkan 16 digit NIK" maxlength="16" required 
                                    inputmode="numeric" pattern="[0-9]{16}"
                                    autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                                    data-lpignore="true" data-form-type="other" readonly
                                    onfocus="this.removeAttribute('readonly');"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <div class="form-text">NIK yang terdaftar di sistem BSrE (16 digit angka)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tte_sekda_password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Passphrase Sertifikat <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="tte_sekda_password"
                                        placeholder="Masukkan passphrase" minlength="8" required
                                        autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                                        data-lpignore="true" data-form-type="other" readonly
                                        onfocus="this.removeAttribute('readonly');">
                                    <button class="btn btn-outline-secondary" type="button" onclick="toggleSekdaPasswordVisibility()">
                                        <i class="fas fa-eye" id="toggleSekdaPasswordIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Passphrase untuk mengakses sertifikat TTE</div>
                            </div>
                        </div>
                    </div>

                    <div id="sekdaVerificationResult"></div>
                </form>
            `;

            currentBootbox = bootbox.dialog({
                title: '<i class="fas fa-stamp me-2"></i>TTE Sekretaris Daerah dengan BSRE',
                message: tteContent,
                size: 'large',
                buttons: {
                    cancel: {
                        label: '<i class="fas fa-times me-1"></i>Batal',
                        className: 'btn-secondary'
                    },
                    verify: {
                        label: '<i class="fas fa-check-circle me-1"></i>Verifikasi Sertifikat',
                        className: 'btn-outline-primary',
                        callback: function() {
                            verifySekdaCertificate();
                            return false; // Prevent dialog from closing
                        }
                    },
                    proceed: {
                        label: '<i class="fas fa-stamp me-1"></i>Proses TTE Sekda',
                        className: 'btn-success',
                        id: 'btnProceedTteSekda',
                        callback: function() {
                            try {
                                proceedTTESekda();
                            } catch (e) {
                                alert('Error: ' + e.message);
                            }
                            return false; // Prevent dialog from closing
                        }
                    }
                }
            });
        }

        // Toggle password visibility untuk Sekda
        function toggleSekdaPasswordVisibility() {
            const passwordInput = document.getElementById('tte_sekda_password');
            const toggleIcon = document.getElementById('toggleSekdaPasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Fungsi verifikasi sertifikat TTE Sekda
        function verifySekdaCertificate() {
            const resultDiv = document.getElementById('sekdaVerificationResult');
            const sekdaNik = document.getElementById('tte_sekda_nik')?.value;

            // Validasi NIK
            if (!sekdaNik || !/^\d{16}$/.test(sekdaNik)) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>NIK Tidak Valid</h5>
                        <p class="mb-0">NIK harus terdiri dari 16 digit angka.</p>
                    </div>`;
                return false;
            }

            // Tampilkan loading
            resultDiv.innerHTML = `
                <div class="text-center my-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Memeriksa sertifikat...</span>
                    </div>
                    <p class="mt-3 mb-0">Sedang memeriksa status sertifikat TTE Sekda...</p>
                </div>`;

            // Sembunyikan tombol proceed di bootbox
            const proceedBtn = currentBootbox.find('#btnProceedTteSekda');
            if (proceedBtn.length) {
                proceedBtn.hide();
            }

            // Kirim request ke API untuk check status sertifikat
            fetch('<?= base_url('api/tte/check-status') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        nik: sekdaNik
                    }),
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.data) {
                        const statusData = data.data;
                        const statusCode = statusData.status_code;
                        const message = statusData.message || 'Status sertifikat tidak diketahui';
                        const isActive = statusData.is_active === true || statusData.is_active === 'true';
                        
                        // Status code 1111 = Aktif, Status code 2011 = Tidak Aktif
                        if (isActive || statusCode === '1111' || statusCode === 1111) {
                            // Sertifikat aktif
                            resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i> Status Sertifikat: <strong>Aktif</strong></h5>
                                <p class="mb-0">${message}</p>
                            </div>`;

                            // Tampilkan tombol proceed di bootbox
                            if (proceedBtn.length) {
                                proceedBtn.show();
                            }
                        } else {
                            // Sertifikat tidak aktif (status_code 2011 atau lainnya)
                            resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-times-circle me-2"></i> Status Sertifikat: <strong>Tidak Aktif</strong></h5>
                                <p class="mb-2">${message}</p>
                                <p class="mb-0">
                                    <a href="https://bsre.bssn.go.id" target="_blank" class="alert-link">
                                        Kunjungi BSrE untuk memperbarui sertifikat
                                    </a>
                                </p>
                            </div>`;
                        }
                    } else {
                        // Error dari server
                        resultDiv.innerHTML = `
                        <div class="alert alert-info" style="background-color: #20c997; border-color: #20c997; color: #fff;">
                            <h5><i class="fas fa-exclamation-circle me-2"></i> Gagal Memeriksa Sertifikat</h5>
                            <p class="mb-0">${data.message || 'Terjadi kesalahan saat memeriksa status sertifikat. Silakan coba lagi.'}</p>
                        </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle me-2"></i> Kesalahan Jaringan</h5>
                        <p class="mb-0">Tidak dapat terhubung ke server TTE. Pastikan koneksi internet Anda stabil dan coba lagi.</p>
                    </div>`;
                });
        }

        // Fungsi untuk memproses TTE Sekda - Menggunakan TTEController->signDocument()
        function proceedTTESekda() {
            if (!currentAjuanId) {
                alert('ID ajuan tidak valid.');
                return;
            }

            const nik = document.getElementById('tte_sekda_nik').value;
            const password = document.getElementById('tte_sekda_password').value;

            if (!nik || nik.length !== 16) {
                alert('NIK harus 16 digit!');
                return;
            }

            if (!password || password.length < 8) {
                alert('Passphrase minimal 8 karakter!');
                return;
            }

            // Langsung proses tanpa confirm() karena browser memblokir confirm dari callback bootbox
            // User sudah mengkonfirmasi dengan menekan tombol "Proses TTE Sekda"
            
            // Show loading message in form
            const resultDiv = document.getElementById('sekdaVerificationResult');
            
            if (resultDiv) {
                resultDiv.innerHTML = `
                    <div class="alert alert-warning mt-3">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm text-warning me-3" role="status"></div>
                            <div>
                                <strong>⏳ Memproses TTE Sekda...</strong><br>
                                <small>Menghubungi server BSrE, mohon tunggu beberapa saat. Jangan tutup halaman ini.</small>
                            </div>
                        </div>
                    </div>`;
            }

            // Disable tombol jika ada
            if (currentBootbox) {
                try {
                    currentBootbox.find('button').prop('disabled', true);
                } catch (e) {
                    // Silent fail
                }
            }

            // Step 1: Ambil id_dokumen FINAL_PARAF terlebih dahulu
            fetch('<?= base_url('legalisasi/getFinalParafDocument') ?>/' + currentAjuanId, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(docData => {
                    if (!docData.success || !docData.id_dokumen) {
                        throw new Error(docData.message || 'Dokumen FINAL_PARAF tidak ditemukan');
                    }

                        // Step 2: Kirim request ke TTEController->signDocument
                        return fetch('<?= base_url('api/tte/sign') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                id_ajuan: currentAjuanId,
                                nik: nik,
                                password: password,
                                id_dokumen: docData.id_dokumen
                            })
                        });
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message dengan SweetAlert
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'TTE Sekda Berhasil!',
                                    html: `
                                        <div class="text-start">
                                            <p><strong>Nomor Peraturan:</strong> ${data.data?.document_number || 'N/A'}</p>
                                            <p><strong>ID Ajuan:</strong> ${data.data?.id_ajuan || 'N/A'}</p>
                                            <hr>
                                            <p class="mb-0"><strong>Dokumen telah disahkan secara resmi dan SELESAI.</strong></p>
                                        </div>
                                    `,
                                    showConfirmButton: false,
                                    timer: 5000
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert('TTE Sekda berhasil! Nomor: ' + (data.data?.document_number || 'N/A'));
                                window.location.reload();
                            }
                        } else {
                            alert('Gagal memproses TTE Sekda: ' + (data.message || 'Terjadi kesalahan. Silakan coba lagi.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('TTE gagal: ' + (error.message || 'Terjadi kesalahan server BSRE'));
                    })
                    .finally(() => {
                        // Re-enable buttons jika ada
                        if (currentBootbox) {
                            try {
                                currentBootbox.find('button').prop('disabled', false);
                            } catch (e) {
                                // Silent fail
                            }
                        }
                    });
        }

        // Rate limiting untuk mencegah spam
        let lastParafRequest = 0;
        const PARAF_COOLDOWN = 3000; // 3 detik cooldown

        function processParaf(ajuanId) {
            // Rate limiting check
            const now = Date.now();
            if (now - lastParafRequest < PARAF_COOLDOWN) {
                alert('Tunggu sebentar sebelum melakukan aksi lagi.');
                return;
            }
            lastParafRequest = now;

            if (confirm('Apakah Anda yakin ingin memproses paraf untuk ajuan ini?')) {
                // Disable button untuk mencegah double click
                const button = event.target;
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

                // Implement paraf processing
                const formData = new FormData();
                formData.append('ajuan_id', ajuanId);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                fetch('<?= base_url('legalisasi/processParaf') ?>/' + ajuanId, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Paraf berhasil diproses!');
                            // Kembali ke halaman sebelumnya (dashboard legalisasi)
                            window.history.back();
                        } else {
                            // Generic error message untuk keamanan
                            alert('Terjadi kesalahan. Silakan coba lagi atau hubungi administrator.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi atau hubungi administrator.');
                    })
                    .finally(() => {
                        // Re-enable button
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
            }
        }

        function revisiKeFinalisasi(ajuanId) {
            bootbox.prompt({
                title: "Catatan Revisi",
                message: "<p>Berikan catatan revisi untuk dikirim kembali ke tahap Finalisasi:</p>",
                inputType: 'textarea',
                buttons: {
                    confirm: {
                        label: 'Kirim Revisi',
                        className: 'btn-warning'
                    },
                    cancel: {
                        label: 'Batal',
                        className: 'btn-secondary'
                    }
                },
                callback: function (result) {
                    if (result === null) {
                        return;
                    }
                    
                    if (result.trim() === "") {
                        alert("Catatan revisi wajib diisi!");
                        return false;
                    }

                    const formData = new FormData();
                    formData.append('ajuan_id', ajuanId);
                    formData.append('catatan', result);
                    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                    fetch('<?= base_url('legalisasi/revisiKeFinalisasi') ?>/' + ajuanId, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Berhasil mengirim revisi ke finalisasi!');
                                window.history.back();
                            } else {
                                alert(data.message || 'Terjadi kesalahan.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan Jaringan.');
                        });
                }
            });
        }
    </script>