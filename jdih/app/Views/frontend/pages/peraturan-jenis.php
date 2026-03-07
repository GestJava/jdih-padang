<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>


<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-list-alt icon-sm me-1"></i> Jenis Peraturan</span>
                </div>
                <h1 class="hero-title"><?= $judulJenis ?? 'Produk Hukum' ?></h1>
                <p class="hero-subtitle">Daftar peraturan dan produk hukum <?= strtolower($judulJenis) ?? '' ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-5 mb-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('peraturan') ?>">Produk Hukum</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $judulJenis ?? 'Jenis Peraturan' ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Filter dan Pencarian -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Filter Peraturan</h5>
                        <form action="" method="get">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <select class="form-select" id="tahun" name="tahun">
                                        <option value="">Semua Tahun</option>
                                        <?php for ($i = date('Y'); $i >= 2010; $i--): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="berlaku">Berlaku</option>
                                        <option value="dicabut">Dicabut</option>
                                        <option value="diubah">Diubah</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="keyword" class="form-label">Kata Kunci</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Cari berdasarkan judul, nomor, atau kata kunci...">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search me-1"></i> Cari
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>



                <!-- Daftar Peraturan -->
                <h4 class="mb-3">Daftar <?= $judulJenis ?? 'Peraturan' ?></h4>

                <?php if (isset($peraturan) && !empty($peraturan)): ?>
                    <?php foreach ($peraturan as $item): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="card-subtitle mb-2">
                                    <span class="badge bg-primary"><?= mb_convert_case($item['jenis_peraturan'], MB_CASE_TITLE, "UTF-8") ?></span>
                                    <span class="text-muted">No. <?= $item['nomor_peraturan'] ?> Tahun <?= $item['tahun'] ?></span>
                                </div>
                                <h5 class="card-title">
                                    <a href="<?= base_url('peraturan/' . $item['slug']) ?>">
                                        <?= mb_convert_case($item['judul'], MB_CASE_TITLE, "UTF-8") ?>
                                    </a>
                                </h5>
                                <p class="card-text">
                                    <?php
                                    $raw_summary = !empty($item['ringkasan']) ? $item['ringkasan'] : (!empty($item['abstrak']) ? $item['abstrak'] : 'Tidak ada ringkasan tersedia');
                                    $plain_summary = strip_tags($raw_summary);

                                    if ($plain_summary !== 'Tidak ada ringkasan tersedia' && strlen($plain_summary) > 200) {
                                        $summary = substr($plain_summary, 0, 200) . '...';
                                    } else {
                                        $summary = $plain_summary;
                                    }
                                    echo esc($summary, 'html');
                                    ?>
                                </p>
                                <div class="card-info">
                                    <div class="card-info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= format_tanggal_indo($item['tanggal_penetapan'] ?? null) ?>
                                    </div>
                                    <?php if (!empty($item['hits'])): ?>
                                        <div class="card-info-item ms-3">
                                            <i class="fas fa-eye"></i> <?= $item['hits'] ?> kali dilihat
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if (isset($pager) && $pager['totalPages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php
                                $currentPage = $pager['page'];
                                $totalPages = $pager['totalPages'];
                                $urlParams = $_GET;

                                // Previous Page
                                if ($currentPage > 1):
                                    $urlParams['page'] = $currentPage - 1;
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query($urlParams) ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                // Calculate page range
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $startPage + 4);

                                if ($endPage - $startPage < 4) {
                                    $startPage = max(1, $endPage - 4);
                                }

                                for ($i = $startPage; $i <= $endPage; $i++):
                                    $urlParams['page'] = $i;
                                ?>
                                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query($urlParams) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Page -->
                                <?php if ($currentPage < $totalPages):
                                    $urlParams['page'] = $currentPage + 1;
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query($urlParams) ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0">Tidak ada peraturan <?= $judulJenis ?? '' ?> yang ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Kategori Peraturan -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i> Jenis Peraturan</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if (isset($jenis_peraturan) && !empty($jenis_peraturan)): ?>
                                <?php foreach ($jenis_peraturan as $jenis): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a href="<?= base_url('peraturan/jenis/' . urlencode($jenis['slug_jenis'])) ?>" class="text-decoration-none <?= ($judulJenis == $jenis['nama_jenis']) ? 'text-primary fw-bold' : 'text-dark' ?>">
                                             <?= esc(mb_convert_case($jenis['nama_jenis'], MB_CASE_TITLE, "UTF-8")) ?>
                                         </a>
                                        <?php if (isset($jenis_counts) && array_key_exists($jenis['nama_jenis'], $jenis_counts)): ?>
                                            <span class="badge bg-primary rounded-pill"><?= $jenis_counts[$jenis['nama_jenis']] ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item">Tidak ada jenis peraturan</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                </ul>
            </div>
        </div>

        <!-- Tahun Peraturan -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Tahun Peraturan</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (isset($tahun_peraturan) && !empty($tahun_peraturan)): ?>
                        <?php foreach ($tahun_peraturan as $tahun): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="<?= base_url('peraturan/jenis/' . urlencode($judulJenis) . '?tahun=' . $tahun) ?>" class="text-decoration-none <?= (isset($_GET['tahun']) && $_GET['tahun'] == $tahun) ? 'text-primary fw-bold' : 'text-dark' ?>">
                                    Tahun <?= $tahun ?>
                                </a>
                                <?php if (isset($tahun_counts) && array_key_exists($tahun, $tahun_counts)): ?>
                                    <span class="badge bg-primary rounded-pill"><?= $tahun_counts[$tahun] ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">Tidak ada data tahun</li>
                    <?php endif; ?>
                    <li class="list-group-item">
                        <a href="<?= base_url('peraturan/jenis/' . urlencode($judulJenis)) ?>" class="text-decoration-none text-dark">
                            <i class="fas fa-arrow-right me-1"></i> Lihat Semua Tahun
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>

<?= $this->endSection() ?>
