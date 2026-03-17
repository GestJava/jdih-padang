<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-file-alt icon-sm me-1"></i> Detail Peraturan</span>
                </div>
                <h1 class="hero-title">Detail Peraturan</h1>
                <p class="hero-subtitle">Informasi lengkap tentang produk hukum</p>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row">
        <!-- Breadcrumb -->
        <div class="col-12 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('peraturan') ?>">Produk Hukum</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('peraturan/jenis/' . ($peraturan['nama_jenis'] ?? 'tidak-diketahui')) ?>"><?= mb_convert_case($peraturan['nama_jenis'] ?? 'Tidak Diketahui', MB_CASE_TITLE, "UTF-8") ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Peraturan</li>
                </ol>
            </nav>
        </div>

        <!-- Detail Peraturan -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <!-- Header Info Peraturan -->
                    <div class="mb-4">
                        <div class="mb-2">
                            <span class="badge bg-primary me-2"><?= mb_convert_case($peraturan['nama_jenis'] ?? 'Tidak Diketahui', MB_CASE_TITLE, "UTF-8") ?></span>
                            <span class="badge bg-secondary me-2">Nomor <?= $peraturan['nomor'] ?? '-' ?></span>
                            <span class="badge bg-info">Tahun <?= $peraturan['tahun'] ?></span>
                        </div>
                        <h1 class="h3 fw-bold mb-3 text-dark"><?= esc(mb_convert_case($peraturan['judul'], MB_CASE_TITLE, "UTF-8")) ?></h1>
                        <div class="d-flex flex-wrap align-items-center text-muted small mb-3">
                            <?php if (isset($peraturan['status']) && $peraturan['status'] == 'berlaku'): ?>
                                <span class="badge bg-success me-2">Berlaku</span>
                            <?php elseif (isset($peraturan['status']) && $peraturan['status'] == 'dicabut'): ?>
                                <span class="badge bg-danger me-2">Dicabut</span>
                            <?php elseif (isset($peraturan['status']) && $peraturan['status'] == 'diubah'): ?>
                                <span class="badge bg-warning text-dark me-2">Diubah</span>
                            <?php endif; ?>
                            <span class="me-3"><i class="fas fa-eye me-1"></i> <?= $peraturan['hits'] ?> views</span>
                            <span><i class="fas fa-download me-1"></i> <?= $peraturan['downloads'] ?> downloads</span>
                        </div>
                        
                        <!-- Tombol Download - Prominent -->
                        <?php if ($peraturan['file_dokumen']): ?>
                        <div class="mb-3">
                            <a href="<?= base_url('peraturan/download/' . $peraturan['id_peraturan']) ?>" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-download me-2"></i> Download Dokumen
                            </a>
                            <button type="button" class="btn btn-outline-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAbstrak">
                                <i class="fas fa-eye me-2"></i> Lihat Abstrak
                            </button>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <a href="javascript:void(0)" onclick="readPdfContent()" id="btn-read-pdf" class="btn btn-info btn-lg text-white me-2">
                                    <i class="fas fa-volume-up me-2"></i> Baca Dokumen PDF
                                </a>
                                <button class="btn btn-outline-secondary btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#audioSettings" aria-expanded="false" title="Pengaturan Audio">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                            
                            <div class="collapse" id="audioSettings">
                                <div class="card card-body bg-light border-0 shadow-sm mb-3">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-sliders-h me-2"></i> Pengaturan Suara</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Pilih Suara</label>
                                            <select id="tts-voice" class="form-select form-select-sm">
                                                <option value="">Default Browser (Id)</option>
                                                <optgroup label="Google AI Premium (Neural)">
                                                    <option value="google-A">Google AI - Wanita A (Wavenet)</option>
                                                    <option value="google-D">Google AI - Wanita B (Wavenet)</option>
                                                    <option value="google-B">Google AI - Pria A (Wavenet)</option>
                                                    <option value="google-C">Google AI - Pria B (Wavenet)</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">Kecepatan: <span id="rate-val">1.0</span>x</label>
                                            <input type="range" class="form-range" id="tts-rate" min="0.5" max="2" step="0.1" value="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">Nada: <span id="pitch-val">1.0</span></label>
                                            <input type="range" class="form-range" id="tts-pitch" min="0" max="2" step="0.1" value="1">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted italic">* Kualitas suara bergantung pada dukungan browser Anda.</small>
                                    </div>
                                </div>
                            </div>

                            <div id="pdf-speech-status" class="mt-2 text-muted small" style="display:none;">
                                <i class="fas fa-spinner fa-spin me-1"></i> <span id="pdf-speech-status-text">Memproses dokumen...</span>
                            </div>
                            <div id="pdf-speech-controls" class="mt-2" style="display:none;">
                                <button onclick="pauseSpeech()" class="btn btn-sm btn-warning me-1"><i class="fas fa-pause"></i> Pause</button>
                                <button onclick="resumeSpeech()" class="btn btn-sm btn-success me-1"><i class="fas fa-play"></i> Resume</button>
                                <button onclick="stopSpeech()" class="btn btn-sm btn-danger"><i class="fas fa-stop"></i> Stop</button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <?php
                    // Metadata labels and values are passed directly from the controller
                    // and processed via metadata_helper.php
                    ?>

                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Metadata Peraturan</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['tipe_dokumen'] ?? 'Tipe Dokumen' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['tipe_dokumen'] ?? $peraturan['kategori_nama'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['jenis_dokumen'] ?? 'Jenis Dokumen' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['jenis_dokumen'] ?? $peraturan['nama_jenis'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['nomor_dokumen'] ?? 'Nomor Dokumen' ?></small>
                                <span class="fw-semibold"><?= esc($metadata['nomor_dokumen'] ?? $peraturan['nomor'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['tahun_dokumen'] ?? 'Tahun Dokumen' ?></small>
                                <span class="fw-semibold"><?= esc($metadata['tahun_dokumen'] ?? $peraturan['tahun'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['tanggal_penetapan'] ?? 'Tanggal Penetapan' ?></small>
                                <span class="fw-semibold"><?= isset($metadata['tanggal_penetapan']) ? format_tanggal_indo($metadata['tanggal_penetapan']) : (isset($peraturan['tgl_penetapan']) ? format_tanggal_indo($peraturan['tgl_penetapan']) : '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['tanggal_pengundangan'] ?? 'Tanggal Pengundangan' ?></small>
                                <span class="fw-semibold"><?= isset($metadata['tanggal_pengundangan']) ? format_tanggal_indo($metadata['tanggal_pengundangan']) : (isset($peraturan['tgl_pengundangan']) ? format_tanggal_indo($peraturan['tgl_pengundangan']) : '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['tempat_penetapan'] ?? 'Tempat Penetapan' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['tempat_penetapan'] ?? $peraturan['tempat_penetapan'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['penandatangan'] ?? 'Penandatangan' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['penandatangan'] ?? $peraturan['penandatangan'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['teu'] ?? 'Tajuk Entri Utama (T.E.U)' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['teu'] ?? $peraturan['teu'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['bidang_hukum'] ?? 'Bidang Hukum' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['bidang_hukum'] ?? $peraturan['bidang_hukum'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['sumber'] ?? 'Sumber' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['sumber'] ?? $peraturan['sumber'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['pemrakarsa'] ?? 'Pemrakarsa' ?></small>
                                <span class="fw-semibold"><?= esc(mb_convert_case($metadata['pemrakarsa'] ?? $peraturan['nama_instansi'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['subjek'] ?? 'Subjek' ?></small>
                                <span class="fw-semibold">
                                    <?php
                                    if (!empty($tag)) {
                                        $tag_names = array_column($tag, 'nama_tag');
                                        echo esc(mb_convert_case(implode(' - ', $tag_names), MB_CASE_TITLE, "UTF-8"));
                                    } elseif (!empty($metadata['subjek'])) {
                                        echo esc(mb_convert_case($metadata['subjek'], MB_CASE_TITLE, "UTF-8"));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['status'] ?? 'Status' ?></small>
                                <span class="fw-semibold text-capitalize"><?= esc(mb_convert_case($metadata['status'] ?? $peraturan['status'] ?? '-', MB_CASE_TITLE, "UTF-8")) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['lokasi'] ?? 'Lokasi' ?></small>
                                <span class="fw-semibold"><?= esc($metadata['lokasi'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['bahasa'] ?? 'Bahasa' ?></small>
                                <span class="fw-semibold"><?= esc($metadata['bahasa'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border-bottom pb-2">
                                <small class="text-muted d-block"><?= $metadata_labels['singkatan'] ?? 'Singkatan Jenis' ?></small>
                                <span class="fw-semibold"><?= esc($metadata['singkatan'] ?? '-') ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($peraturan['abstrak_teks']) && $peraturan['abstrak_teks']): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold text-primary mb-3"><i class="fas fa-file-alt me-2"></i>MATERI POKOK PERATURAN</h5>
                            <div class="p-3 bg-light border-start border-primary border-4 rounded">
                                <p class="mb-0" style="line-height: 1.8; text-align: justify;"><?= nl2br(esc(strip_tags($peraturan['abstrak_teks']))) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($peraturan['catatan_teks']) && $peraturan['catatan_teks']): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-sticky-note me-2"></i>Catatan</h5>
                            <div class="alert alert-info">
                                <p class="mb-0"><?= nl2br(esc(strip_tags($peraturan['catatan_teks']))) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tag)): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-tags me-2"></i>Tag / Subjek</h5>
                            <div class="d-flex flex-wrap">
                                <?php foreach ($tag as $t): ?>
                                    <a href="<?= base_url('peraturan/search?tag=' . esc($t['slug_tag'], 'url')) ?>" class="badge bg-light text-dark me-2 mb-2 py-2 px-3 text-decoration-none border">
                                        <i class="fas fa-tag me-1"></i><?= esc($t['nama_tag']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tombol Bagikan -->
                    <div class="mt-4 pt-3 border-top">
                        <a href="javascript:void(0)" onclick="share()" class="btn btn-outline-primary">
                            <i class="fas fa-share-alt me-2"></i> Bagikan Halaman Ini
                        </a>
                    </div>
                </div>
            </div>


        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Enhanced Status Peraturan -->
            <?php if (!empty($relatedPeraturan)) : ?>
                <div class="card shadow-sm border-0 mb-4 relasi-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-sitemap me-2"></i>Relasi Peraturan</h5>
                        <span class="badge bg-info"><?= count($relatedPeraturan) ?> jenis</span>
                    </div>
                    <div class="card-body">
                        <?php
                        // Group relasi berdasarkan jenis_relasi
                        $grouped_relasi = [];
                        foreach ($relatedPeraturan as $relasi) {
                            $jenis_relasi = $relasi['jenis_relasi'];
                            if (!isset($grouped_relasi[$jenis_relasi])) {
                                $grouped_relasi[$jenis_relasi] = [];
                            }
                            $grouped_relasi[$jenis_relasi][] = $relasi;
                        }
                        ?>

                        <?php foreach ($grouped_relasi as $jenis_relasi => $list_peraturan) : ?>
                            <?php
                            // Determine badge color and icon based on relationship type
                            $badge_info = [
                                'Dicabut oleh' => ['class' => 'danger', 'icon' => 'fas fa-times-circle'],
                                'Diubah oleh' => ['class' => 'warning', 'icon' => 'fas fa-edit'],
                                'Diganti oleh' => ['class' => 'warning', 'icon' => 'fas fa-exchange-alt'],
                                'mencabut' => ['class' => 'danger', 'icon' => 'fas fa-ban'],
                                'mengubah' => ['class' => 'primary', 'icon' => 'fas fa-pencil-alt'],
                                'mengganti' => ['class' => 'primary', 'icon' => 'fas fa-sync-alt'],
                                'Dilaksanakan oleh' => ['class' => 'success', 'icon' => 'fas fa-cogs'],
                                'melaksanakan' => ['class' => 'success', 'icon' => 'fas fa-play-circle'],
                                'Diatur lebih lanjut oleh' => ['class' => 'info', 'icon' => 'fas fa-list-alt'],
                                'mengatur lebih lanjut' => ['class' => 'info', 'icon' => 'fas fa-plus-circle'],
                                'Menetapkan' => ['class' => 'secondary', 'icon' => 'fas fa-gavel'],
                                'ditetapkan oleh' => ['class' => 'secondary', 'icon' => 'fas fa-stamp'],
                                'terkait' => ['class' => 'dark text-white', 'icon' => 'fas fa-link']
                            ];
                            $current_badge = $badge_info[$jenis_relasi] ?? ['class' => 'dark text-white', 'icon' => 'fas fa-link'];
                            ?>

                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-<?= $current_badge['class'] ?> me-2 relasi-badge">
                                        <i class="<?= $current_badge['icon'] ?> me-1"></i>
                                        <?= ucfirst($jenis_relasi) ?>
                                    </span>
                                    <small class="text-muted">(<?= count($list_peraturan) ?> peraturan)</small>
                                </div>

                                <div class="ps-3">
                                    <?php foreach ($list_peraturan as $item) : ?>
                                        <div class="border-start border-2 border-<?= $current_badge['class'] ?> ps-3 mb-3 relasi-item">
                                            <div class="mb-2">
                                                <a href="<?= base_url('peraturan/' . $item['slug']) ?>" class="text-decoration-none fw-bold">
                                                    <?= $item['jenis_peraturan'] ?> No. <?= $item['nomor'] ?? 'Tidak Tersedia' ?> Tahun <?= $item['tahun'] ?>
                                                </a>
                                            </div>
                                            <div class="text-muted small mb-2">
                                                <?= esc($item['judul']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Informational footer -->
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Informasi relasi ini menunjukkan hubungan peraturan dengan dokumen hukum lainnya.
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Lampiran Section di Sidebar -->
            <?php if (!empty($lampiran)): ?>
                <div class="card shadow-sm border-0 mb-4 lampiran-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-paperclip me-2"></i>Lampiran</h5>
                        <span class="badge bg-success"><?= count($lampiran) ?> file</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($lampiran as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center lampiran-item">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="me-3">
                                            <?php
                                            $file_extension = strtolower(pathinfo($item['file_lampiran'], PATHINFO_EXTENSION));
                                            $icon_class = 'fas fa-file';
                                            $text_color = 'text-secondary';

                                            switch ($file_extension) {
                                                case 'pdf':
                                                    $icon_class = 'fas fa-file-pdf';
                                                    $text_color = 'text-danger';
                                                    break;
                                                case 'doc':
                                                case 'docx':
                                                    $icon_class = 'fas fa-file-word';
                                                    $text_color = 'text-primary';
                                                    break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                    $icon_class = 'fas fa-file-image';
                                                    $text_color = 'text-success';
                                                    break;
                                                case 'xls':
                                                case 'xlsx':
                                                    $icon_class = 'fas fa-file-excel';
                                                    $text_color = 'text-success';
                                                    break;
                                            }
                                            ?>
                                            <i class="<?= $icon_class ?> <?= $text_color ?> fa-lg"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold small"><?= esc($item['judul_lampiran']) ?></div>
                                            <div class="text-muted small">
                                                <?php if (!empty($item['original_name'])): ?>
                                                    <?= esc($item['original_name']) ?>
                                                <?php else: ?>
                                                    <?= esc($item['file_lampiran']) ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($item['file_size'])): ?>
                                                <div class="text-muted small">
                                                    <i class="fas fa-hdd me-1"></i>
                                                    <?php
                                                    $size = $item['file_size'];
                                                    $units = ['B', 'KB', 'MB', 'GB'];
                                                    $unit_index = 0;
                                                    while ($size >= 1024 && $unit_index < count($units) - 1) {
                                                        $size /= 1024;
                                                        $unit_index++;
                                                    }
                                                    echo number_format($size, 1) . ' ' . $units[$unit_index];
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <a href="<?= base_url('peraturan/download_lampiran/' . $item['id_lampiran']) ?>"
                                            class="btn btn-sm btn-outline-primary lampiran-download-btn"
                                            title="Download <?= esc($item['judul_lampiran']) ?>">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Informational footer -->
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Lampiran ini merupakan dokumen pendukung peraturan yang dapat diunduh.
                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($peraturan_populer)): ?>
                <!-- Peraturan Populer -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Peraturan Populer</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($peraturan_populer as $item): ?>
                                <li class="list-group-item px-0">
                                    <a href="<?= base_url('peraturan/' . $item['slug_peraturan']) ?>" class="text-decoration-none">
                                        <?= esc(mb_convert_case($item['judul'], MB_CASE_TITLE, "UTF-8")) ?>
                                    </a>
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-eye me-1"></i> <?= $item['hits'] ?> views
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($peraturan['file_dokumen']): ?>
        <!-- Simple PDF Preview -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="ratio ratio-16x9">
                    <iframe src="<?= base_url('uploads/peraturan/' . $peraturan['file_dokumen']) ?>"
                        title="Preview Dokumen PDF - <?= esc($peraturan['judul']) ?>"
                        allowfullscreen
                        style="border:none;">
                    </iframe>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Abstrak -->
<div class="modal fade" id="modalAbstrak" tabindex="-1" aria-labelledby="modalAbstrakLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalAbstrakLabel">ABSTRAK PERATURAN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <!-- Judul & Tahun -->
                <div class="bg-light p-3 mb-3 rounded">
                    <p class="mb-1 fw-bold text-uppercase"><?= esc($peraturan['judul']) ?></p>
                    <p class="mb-0"><?= esc($peraturan['tahun']) ?></p>
                </div>

                <!-- Identitas -->
                <div class="bg-light p-3 mb-3 rounded">
                     <p class="mb-0 fw-bold text-uppercase">
                        <?= esc(mb_convert_case($peraturan['nama_jenis'] ?? 'Peraturan', MB_CASE_UPPER, "UTF-8")) ?> NO. <?= esc($peraturan['nomor']) ?>, <?= esc($peraturan['sumber'] ?? '-') ?>
                     </p>
                </div>

                <!-- Judul Lengkap -->
                 <div class="mb-4">
                     <p class="fw-bold text-uppercase mb-0">
                        <?= esc(mb_convert_case($peraturan['nama_jenis'] ?? 'Peraturan', MB_CASE_UPPER, "UTF-8")) ?> TENTANG <?= esc($peraturan['judul']) ?>
                     </p>
                 </div>

                 <!-- Abstrak Content -->
                 <div class="mb-4">
                    <div class="row">
                        <div class="col-md-2 fw-bold">ABSTRAK:</div>
                        <div class="col-md-10 text-break">
                            <?= !empty($peraturan['abstrak_teks']) ? $peraturan['abstrak_teks'] : '-' ?>
                        </div>
                    </div>
                 </div>

                 <!-- Catatan Content -->
                 <div class="mb-4">
                    <div class="row">
                        <div class="col-md-2 fw-bold">CATATAN:</div>
                        <div class="col-md-10 text-break">
                             <?= !empty($peraturan['catatan_teks']) ? $peraturan['catatan_teks'] : '-' ?>
                        </div>
                    </div>
                 </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<style>
    /* Enhanced Relasi Peraturan Styles */
    .relasi-card {
        transition: all 0.3s ease;
    }

    .relasi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .relasi-item {
        position: relative;
        transition: all 0.2s ease;
    }

    .relasi-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
        border-radius: 8px;
        padding: 8px;
        margin: -8px;
    }

    .relasi-badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 20px;
    }

    .relasi-metadata .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .relasi-keterangan {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-left: 3px solid #007bff;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .relasi-metadata {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .relasi-metadata .badge {
            margin-bottom: 0.25rem;
        }
    }

    /* Animation for auto-update badge */
    .badge.bg-warning {
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }

        100% {
            opacity: 1;
        }
    }

    /* Fix badge contrast issues */
    .badge.bg-light {
        color: #212529 !important;
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
    }

    .badge.bg-white {
        color: #212529 !important;
        background-color: #ffffff !important;
        border: 1px solid #dee2e6;
    }

    /* Ensure good contrast for all badge types */
    .badge.bg-danger {
        color: #ffffff !important;
    }

    .badge.bg-warning {
        color: #212529 !important;
    }

    .badge.bg-primary {
        color: #ffffff !important;
    }

    .badge.bg-success {
        color: #ffffff !important;
    }

    .badge.bg-info {
        color: #ffffff !important;
    }

    .badge.bg-secondary {
        color: #ffffff !important;
    }

    .badge.bg-dark {
        color: #ffffff !important;
    }

    /* Special styling for relationship type badges */
    .badge.relasi-badge {
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    /* Lampiran Sidebar Styles */
    .lampiran-card {
        transition: all 0.3s ease;
    }

    .lampiran-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .lampiran-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .lampiran-item:hover {
        background-color: rgba(0, 123, 255, 0.05);
        border-left-color: #007bff;
        border-radius: 0 8px 8px 0;
    }

    .lampiran-download-btn {
        transition: all 0.2s ease;
        opacity: 0.7;
    }

    .lampiran-item:hover .lampiran-download-btn {
        opacity: 1;
        transform: scale(1.1);
    }

    .lampiran-download-btn:hover {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    /* File type icon animations */
    .lampiran-item:hover i.fa-file-pdf {
        animation: pulse-danger 1s infinite;
    }

    .lampiran-item:hover i.fa-file-word {
        animation: pulse-primary 1s infinite;
    }

    .lampiran-item:hover i.fa-file-image {
        animation: pulse-success 1s infinite;
    }

    @keyframes pulse-danger {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    @keyframes pulse-primary {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    @keyframes pulse-success {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }
</style>

<script>
    function share() {
        if (navigator.share) {
            navigator.share({
                    title: '<?= $peraturan["nama_jenis"] ?? "Tidak Diketahui" ?> No. <?= $peraturan["nomor"] ?? "Tidak Tersedia" ?> Tahun <?= $peraturan["tahun"] ?>',
                    text: '<?= $peraturan["judul"] ?>',
                    url: window.location.href,
                })
                .catch(console.error);
        } else {
            // Fallback untuk browser yang tidak mendukung Web Share API
            alert('Salin link berikut untuk membagikan: ' + window.location.href);
        }
    }
</script>

<!-- PDF.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    let speechUtterance = null;
    let isSpeaking = false;
    let isPaused = false;
    // Gunakan URL preview yang sudah ada
    const pdfUrl = '<?= base_url('uploads/peraturan/' . $peraturan['file_dokumen']) ?>';
    
    // Define explicitly on window to ensure visibility
    window.readPdfContent = async function() {
        const btn = document.getElementById('btn-read-pdf');
        const statusDiv = document.getElementById('pdf-speech-status');
        const statusText = document.getElementById('pdf-speech-status-text');
        const controlsDiv = document.getElementById('pdf-speech-controls');

        // WARM UP SPEECH ENGINE IMMEDIATELY (Fix for browser auto-play policy)
        if (window.speechSynthesis) {
            // Do NOT cancel here, just resume to ensure it's active.
            // Cancelling might cause race conditions if a speech is pending.
            window.speechSynthesis.resume();
        }

        // Check if PDF.js is loaded
        if (typeof pdfjsLib === 'undefined') {
            alert('Gagal memuat pustaka PDF.js. Pastikan Anda terhubung ke internet.');
            if (statusDiv) {
                statusDiv.style.display = 'block';
                 statusDiv.innerHTML = '<span class="text-danger">Gagal memuat PDF.js Library</span>';
            }
            return;
        }

        // Initialize worker if needed (lazy init)
        if (!pdfjsLib.GlobalWorkerOptions.workerSrc) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }

        if (isSpeaking) {
            stopSpeech();
            return;
        }

        try {
            // UI Update
            if (btn) btn.classList.add('disabled');
            if (statusDiv) statusDiv.style.display = 'block';
            if (statusText) statusText.innerText = 'Mengunduh dan memproses PDF...';
            
            // 1. Load PDF
            const loadingTask = pdfjsLib.getDocument(pdfUrl);
            const pdf = await loadingTask.promise;
            
            if (statusText) statusText.innerText = `Mengekstrak teks dari ${pdf.numPages} halaman...`;

            // 2. Extract Text
            let fullText = '';
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(' ');
                fullText += pageText + ' ';
                
                // Update progress
                if (statusText) statusText.innerText = `Memproses halaman ${i} dari ${pdf.numPages}...`;
            }

            if (!fullText.trim()) {
                throw new Error("Tidak ada teks yang dapat dibaca (kemungkinan PDF hasil scan).");
            }

            // 3. Start Speaking
            if (statusText) statusText.innerText = 'Membaca dokumen...';
            speakText(fullText);
            
            // Update UI to "Playing" state
            if (statusDiv) statusDiv.style.display = 'none';
            if (controlsDiv) controlsDiv.style.display = 'block';
            if (btn) {
                btn.innerHTML = '<i class="fas fa-stop me-2"></i> Stop Membaca';
                btn.classList.remove('btn-info');
                btn.classList.add('btn-danger');
                btn.classList.remove('disabled');
            }

        } catch (error) {
            console.error('Error extracting text:', error);
            if (statusText) statusText.innerText = 'Gagal membaca: ' + error.message;
            if (statusDiv) {
                statusDiv.style.display = 'block';
                statusDiv.classList.add('text-danger');
            }
            if (btn) btn.classList.remove('disabled');
        }
    };

    // Global variables for speech queue
    let speechQueue = [];
    let currentSentenceIndex = 0;
    let premiumAudio = null;

    window.speakText = function(text) {
        // Cancel previous
        window.speechSynthesis.cancel();
        if (premiumAudio) {
            premiumAudio.pause();
            premiumAudio = null;
        }
        speechQueue = [];
        currentSentenceIndex = 0;

        console.log("Starting speech. Text length: " + text.length);

        // Ensure we are not paused
        if (window.speechSynthesis.paused) {
            window.speechSynthesis.resume();
        }

        // Split text into proper chunks (sentences)
        const rawSentences = text.match(/[^.!?]+[.!?]+(\s+|$)/g) || [text];
        
        // Clean up sentences
        speechQueue = rawSentences.map(s => s.trim()).filter(s => s.length > 0);
        
        if (speechQueue.length === 0) {
            console.warn("No speakable text found.");
            resetUI();
            return;
        }

        console.log(`Split into ${speechQueue.length} chunks.`);
        
        // Start reading the first chunk with a slight delay to allow cancel() to finish
        // and avoid "interrupted" race conditions.
        isSpeaking = true;
        isPaused = false;
        setTimeout(() => {
            speakNextChunk();
        }, 100);
    };

    // --- Audio Settings Logic ---
    let ttsSettings = {
        voiceURI: localStorage.getItem('jdih_tts_voice') || '',
        rate: parseFloat(localStorage.getItem('jdih_tts_rate')) || 1.0,
        pitch: parseFloat(localStorage.getItem('jdih_tts_pitch')) || 1.0
    };

    function initAudioSettings() {
        const voiceSelect = document.getElementById('tts-voice');
        const rateInput = document.getElementById('tts-rate');
        const pitchInput = document.getElementById('tts-pitch');
        const rateVal = document.getElementById('rate-val');
        const pitchVal = document.getElementById('pitch-val');

        // Initial UI values
        rateInput.value = ttsSettings.rate;
        pitchInput.value = ttsSettings.pitch;
        rateVal.innerText = ttsSettings.rate.toFixed(1);
        pitchVal.innerText = ttsSettings.pitch.toFixed(1);

        const loadVoices = () => {
            const voices = window.speechSynthesis.getVoices();
            
            // Define Premium Options
            let optionsHtml = `
                <option value="">Default Browser (Id)</option>
                <optgroup label="Google AI Premium (Neural)">
                    <option value="google-A" ${ttsSettings.voiceURI === 'google-A' ? 'selected' : ''}>Google AI - Wanita A (Wavenet)</option>
                    <option value="google-D" ${ttsSettings.voiceURI === 'google-D' ? 'selected' : ''}>Google AI - Wanita B (Wavenet)</option>
                    <option value="google-B" ${ttsSettings.voiceURI === 'google-B' ? 'selected' : ''}>Google AI - Pria A (Wavenet)</option>
                    <option value="google-C" ${ttsSettings.voiceURI === 'google-C' ? 'selected' : ''}>Google AI - Pria B (Wavenet)</option>
                </optgroup>
                <optgroup label="Suara Sistem (Browser)">
            `;
            
            // Filter Indonesian voices
            const idVoices = voices.filter(v => v.lang.startsWith('id'));
            
            idVoices.forEach(voice => {
                optionsHtml += `<option value="${voice.voiceURI}" ${voice.voiceURI === ttsSettings.voiceURI ? 'selected' : ''}>${voice.name} (${voice.lang})</option>`;
            });

            if (idVoices.length === 0) {
                 optionsHtml += `<option disabled>Tidak ditemukan suara ID tambahan di browser</option>`;
            }

            optionsHtml += `</optgroup>`;
            voiceSelect.innerHTML = optionsHtml;
        };

        // Voices are loaded asynchronously
        window.speechSynthesis.onvoiceschanged = loadVoices;
        loadVoices();

        // Listeners
        voiceSelect.onchange = (e) => {
            ttsSettings.voiceURI = e.target.value;
            localStorage.setItem('jdih_tts_voice', ttsSettings.voiceURI);
        };
        rateInput.oninput = (e) => {
            ttsSettings.rate = parseFloat(e.target.value);
            rateVal.innerText = ttsSettings.rate.toFixed(1);
            localStorage.setItem('jdih_tts_rate', ttsSettings.rate);
        };
        pitchInput.oninput = (e) => {
            ttsSettings.pitch = parseFloat(e.target.value);
            pitchVal.innerText = ttsSettings.pitch.toFixed(1);
            localStorage.setItem('jdih_tts_pitch', ttsSettings.pitch);
        };
    }

    // Run init on DOM load
    document.addEventListener('DOMContentLoaded', initAudioSettings);

    function speakNextChunk() {
        if (currentSentenceIndex >= speechQueue.length) {
            console.log("Finished reading all chunks.");
            resetUI();
            return;
        }

        // If paused (manual stop), do not proceed.
        if (isPaused || !isSpeaking) return;

        const chunkText = speechQueue[currentSentenceIndex];

        // --- GOOGLE AI PREMIUM LOGIC ---
        if (ttsSettings.voiceURI && ttsSettings.voiceURI.startsWith('google-')) {
            const voiceType = ttsSettings.voiceURI.replace('google-', '');
            const encodedText = encodeURIComponent(chunkText);
            const audioUrl = `<?= base_url('tts/synthesize') ?>?text=${encodedText}&voice=${voiceType}&rate=${ttsSettings.rate}`;
            
            console.log("Playing Premium Audio for chunk " + currentSentenceIndex);
            
            const statusText = document.getElementById('pdf-speech-status-text');
            const statusDiv = document.getElementById('pdf-speech-status');
            
            if (statusDiv) statusDiv.style.display = 'block';
            if (statusText) statusText.innerText = 'Menyiapkan suara AI...';

            premiumAudio = new Audio(audioUrl);
            
            premiumAudio.oncanplaythrough = () => {
                if (statusDiv) statusDiv.style.display = 'none';
                premiumAudio.play().catch(e => {
                    console.error("Audio playback error:", e);
                    // Fallback to browser TTS if audio failed
                    speakWithBrowser(chunkText);
                });
            };

            premiumAudio.onended = () => {
                if (isSpeaking && !isPaused) {
                    currentSentenceIndex++;
                    speakNextChunk();
                }
            };

            premiumAudio.onerror = (e) => {
                console.error("Premium Audio Error:", e);
                if (statusText) {
                    statusText.innerText = 'Suara Premium (Google Cloud) gagal diakses. Pastikan API "Cloud Text-to-Speech" sudah diaktifkan di Google Console Anda.';
                    statusDiv.style.display = 'block';
                    statusDiv.classList.add('alert', 'alert-warning');
                }
                setTimeout(() => {
                    speakWithBrowser(chunkText);
                }, 3000);
            };

            return;
        }

        speakWithBrowser(chunkText);
    }

    function speakWithBrowser(chunkText) {
        speechUtterance = new SpeechSynthesisUtterance(chunkText);
        
        // --- Apply Custom Voice Settings ---
        const voices = window.speechSynthesis.getVoices();
        let selectedVoice = voices.find(v => v.voiceURI === ttsSettings.voiceURI);
        
        // Fallback to any Indonesian voice if selection is empty or not found
        if (!selectedVoice) {
            selectedVoice = voices.find(v => v.lang === 'id-ID' || v.lang === 'id_ID');
        }

        if (selectedVoice) {
            speechUtterance.voice = selectedVoice;
            speechUtterance.lang = selectedVoice.lang;
        }

        speechUtterance.rate = ttsSettings.rate;
        speechUtterance.pitch = ttsSettings.pitch;
        
        speechUtterance.onend = function() {
            // Natural finish: Move to next index
            if (isSpeaking && !isPaused) {
                currentSentenceIndex++;
                speakNextChunk();
            }
        };

        speechUtterance.onerror = function(event) {
            console.error('Speech error on chunk ' + currentSentenceIndex + ':', event);
            
            if (isPaused) return;

            if (event.error !== 'interrupted' || (isSpeaking && !isPaused)) {
                 currentSentenceIndex++;
                 speakNextChunk();
            } else {
                if (!isSpeaking) resetUI();
            }
        };

        window.speechSynthesis.speak(speechUtterance);
    }

    // Expose control functions to window
    // ROBUST STRATEGY: Pause = Cancel. Resume = Replay current sentence.
    window.pauseSpeech = function() {
        if (isSpeaking && !isPaused) {
            isPaused = true;
            console.log("Pausing...");
            window.speechSynthesis.cancel();
            if (premiumAudio) {
                premiumAudio.pause();
            }
        }
    };

    window.resumeSpeech = function() {
        if (isPaused) {
            console.log("Resuming...");
            isPaused = false;
            if (premiumAudio && ttsSettings.voiceURI.startsWith('google-')) {
                premiumAudio.play();
            } else {
                setTimeout(() => {
                    speakNextChunk();
                }, 50);
            }
        }
    };

    window.stopSpeech = function() {
        isSpeaking = false; 
        isPaused = false;
        speechQueue = [];
        currentSentenceIndex = 0;
        window.speechSynthesis.cancel();
        if (premiumAudio) {
            premiumAudio.pause();
            premiumAudio = null;
        }
        resetUI();
    };

    function resetUI() {
        isSpeaking = false;
        isPaused = false;
        const btn = document.getElementById('btn-read-pdf');
        const statusDiv = document.getElementById('pdf-speech-status');
        const controlsDiv = document.getElementById('pdf-speech-controls');

        if(btn) {
            btn.innerHTML = '<i class="fas fa-volume-up me-2"></i> Baca Dokumen PDF';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-info');
            btn.classList.remove('disabled');
        }
        if(controlsDiv) controlsDiv.style.display = 'none';
        if(statusDiv) {
            statusDiv.style.display = 'none';
            statusDiv.classList.remove('text-danger');
        }
    }

    // Handle page unload
    window.onbeforeunload = function() {
        window.speechSynthesis.cancel();
    };
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>

