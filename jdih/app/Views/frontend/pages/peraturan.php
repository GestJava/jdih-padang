<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-file-alt icon-sm me-1"></i> Produk Hukum</span>
                </div>
                <h1 class="hero-title"><?= isset($judulJenis) ? $judulJenis : 'Produk Hukum' ?></h1>
                <p class="hero-subtitle"><?= isset($judulJenis) ? 'Daftar peraturan dan produk hukum ' . strtolower($judulJenis) : 'Kumpulan produk hukum terbaru dan terlengkap dari Pemerintah Daerah' ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<div class="container py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="fas fa-filter me-2"></i>Filter Produk Hukum</h5>
            <form action="<?= base_url('peraturan') ?>" method="get">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="jenis">
                            <option value="">Semua Jenis</option>
                            <?php if (isset($jenis_peraturan) && is_array($jenis_peraturan)): ?>
                                <?php foreach ($jenis_peraturan as $jenis): ?>
                                    <option value="<?= esc($jenis['slug_jenis'], 'attr') ?>" <?= (isset($filters['jenis']) && $filters['jenis'] == $jenis['slug_jenis']) ? 'selected' : '' ?>>
                                        <?= esc(mb_convert_case($jenis['nama_jenis'], MB_CASE_TITLE, "UTF-8")) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="tahun">
                            <option value="">Semua Tahun</option>
                            <?php if (isset($list_tahun) && is_array($list_tahun) && !empty($list_tahun)):
                                foreach ($list_tahun as $item):
                            ?>
                                    <option value="<?= esc($item['tahun']) ?>" <?= (isset($filters['tahun']) && $filters['tahun'] == $item['tahun']) ? 'selected' : '' ?>>
                                        <?= esc($item['tahun']) ?> (<?= esc($item['jumlah']) ?>)
                                    </option>
                                <?php
                                endforeach;
                            else:
                                for ($i = date('Y'); $i >= 2000; $i--):
                                ?>
                                    <option value="<?= esc($i) ?>" <?= (isset($filters['tahun']) && $filters['tahun'] == $i) ? 'selected' : '' ?>>
                                        <?= esc($i) ?>
                                    </option>
                            <?php
                                endfor;
                            endif;
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="keyword" placeholder="Kata Kunci" value="<?= esc(isset($filters['keyword']) ? $filters['keyword'] : '') ?>">
                        <?php if (!empty($filters['tag'])): ?>
                            <input type="hidden" name="tag" value="<?= esc($filters['tag']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="row">
        <div class="col-md-12">
            <?php if (isset($filters) && (!empty($filters['jenis']) || !empty($filters['tahun']) || !empty($filters['keyword']) || !empty($filters['status']) || !empty($filters['tag']))): ?>
                <h4 class="mb-4">
                    Hasil Pencarian
                    <?= !empty($filters['keyword']) ? 'untuk "' . esc($filters['keyword']) . '"' : '' ?>
                    <?php if (!empty($filters['tag']) && isset($tag)): ?>
                        <span class="badge bg-info ms-2">Tag: <?= esc($tag['nama_tag']) ?></span>
                    <?php endif; ?>
                </h4>
            <?php else: ?>
                <h4 class="mb-4">Semua Produk Hukum</h4>
            <?php endif; ?>

            <?php if (empty($peraturan)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Tidak ada produk hukum yang ditemukan.
                </div>
            <?php else: ?>
                <!-- Peraturan Items from Database -->
                <?php foreach ($peraturan as $item): ?>
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-light rounded p-3">
                                        <i class="fas fa-file-pdf text-danger fa-3x"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title">
                                        <a href="<?= base_url('peraturan/' . esc($item['slug'], 'url')) ?>">
                                            <?= esc(mb_convert_case($item['nama_jenis'], MB_CASE_TITLE, "UTF-8")) ?> No. <?= esc($item['nomor']) ?> Tahun <?= esc($item['tahun']) ?> Tentang <?= esc(mb_convert_case($item['judul'], MB_CASE_TITLE, "UTF-8")) ?>
                                        </a>
                                    </h5>
                                    <div class="d-flex flex-wrap mb-2">
                                        <span class="badge bg-primary me-2 mb-1"><?= esc($item['nama_jenis']) ?></span>
                                        <span class="badge bg-secondary me-2 mb-1">Tahun <?= esc($item['tahun']) ?></span>
                                        <?php if (!empty($item['nama_status']) && $item['nama_status'] == 'Berlaku'): ?>
                                            <span class="badge bg-success me-2 mb-1">Berlaku</span>
                                        <?php elseif (!empty($item['nama_status']) && $item['nama_status'] == 'Dicabut'): ?>
                                            <span class="badge bg-danger me-2 mb-1">Dicabut</span>
                                        <?php elseif (!empty($item['nama_status']) && $item['nama_status'] == 'Diubah'): ?>
                                            <span class="badge bg-warning me-2 mb-1">Diubah</span>
                                        <?php endif; ?>
                                        <span class="badge bg-info me-2 mb-1"><i class="fas fa-eye me-1"></i> <?= esc($item['hits']) ?> views</span>
                                        <span class="badge bg-success me-2 mb-1"><i class="fas fa-download me-1"></i> <?= esc($item['downloads']) ?> downloads</span>
                                    </div>
                                    <p class="card-text text-muted">
<?php 
$abstrak = !empty($item['abstrak_teks']) ? strip_tags($item['abstrak_teks']) : 'Tidak ada abstrak';
$abstrak = strlen($abstrak) > 200 ? substr($abstrak, 0, 200) . '...' : $abstrak;
echo esc($abstrak);
?>
                                    </p>
                                    <div class="d-flex">
                                        <a href="<?= base_url('peraturan/' . esc($item['slug'], 'url')) ?>" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                        <?php if (!empty($item['file_dokumen'])): ?>
                                            <a href="<?= base_url('peraturan/download/' . esc($item['id_peraturan'], 'url')) ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination -->
            <div class="mt-4">
                <?php if (isset($pager) && is_object($pager)): ?>
                    <?php if ($pager->getTotal() > 0): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-muted">
                                Menampilkan <?= $pager->getCurrentPage() * $pager->getPerPage() - $pager->getPerPage() + 1 ?> 
                                sampai <?= min($pager->getCurrentPage() * $pager->getPerPage(), $pager->getTotal()) ?> 
                                dari <?= $pager->getTotal() ?> hasil
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($pager->getPageCount() > 1): ?>
                        <?= $pager->links('default', 'bootstrap_pagination') ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
