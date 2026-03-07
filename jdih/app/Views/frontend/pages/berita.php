<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<?= $this->include('frontend/components/hero', [
    'title' => esc($title ?? 'Berita Terbaru'),
    'subtitle' => 'Kumpulan berita dan informasi terkini dari JDIH Kota Padang',
    'icon' => 'fa-newspaper',
    'badge' => 'Berita & Informasi'
]) ?>

<!-- Content Section -->
<div class="jdih-section bg-white py-5">
    <div class="container">
        <?= $this->include('frontend/components/breadcrumb', [
            'items' => [
                ['label' => 'Berita', 'url' => '']
            ]
        ]) ?>

        <div class="row">
            <!-- Konten utama -->
            <div class="col-lg-8">
                <div class="row gx-4 gy-4">
                    <?php if (!empty($berita) && is_array($berita)): ?>
                        <?php foreach ($berita as $item): ?>
                            <div class="col-md-6">
                                <article class="card news-card h-100 shadow-sm border-0">
                                    <?php
                                    // Default placeholder dari assets/img
                                    $gambar_url = base_url('assets/img/news1.jpg');
                                    
                                    // Jika ada gambar di database, gunakan dari uploads/berita/
                                    // Jika ada gambar di database, gunakan dari uploads/berita/
                                    if (!empty($item['gambar'])) {
                                        // Path relatif dari FCPATH (Server structure standard)
                                        $relativePath = 'uploads/berita/' . $item['gambar'];
                                        $fullPath = FCPATH . $relativePath;
                                        
                                        if (file_exists($fullPath)) {
                                            // Gunakan gambar dari uploads/berita/
                                            $gambar_url = base_url($relativePath);
                                        }
                                        // Jika file tidak ada, tetap gunakan placeholder dari assets/img
                                    }
                                    ?>
                                    <a href="<?= base_url('berita/' . esc($item['slug'])) ?>">
                                        <img src="<?= $gambar_url ?>" alt="<?= esc($item['judul']) ?>" class="news-card-img">
                                    </a>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="news-card-title mb-2">
                                            <a href="<?= base_url('berita/' . esc($item['slug'])) ?>" class="text-decoration-none link-dark">
                                                 <?= esc(mb_convert_case($item['judul'], MB_CASE_TITLE, "UTF-8")) ?>
                                             </a>
                                        </h5>
                                        <div class="entry-meta text-muted small mb-2">
                                            <i class="bi bi-clock"></i>
                                            <?php
                                            echo format_tanggal_indo($item['tanggal_publish'] ?? null);
                                            ?>
                                            <?php if (isset($item['nama_penulis'])): ?>
                                                <span class="ms-2"><i class="bi bi-person"></i> <?= esc(mb_convert_case($item['nama_penulis'], MB_CASE_TITLE, "UTF-8")) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="news-card-summary flex-grow-1">
                                            <?php
                                            $cuplikan = strip_tags($item['isi_berita']);
                                            echo esc(substr($cuplikan, 0, 140)) . (strlen($cuplikan) > 140 ? '...' : '');
                                            ?>
                                        </p>
                                        <a href="<?= base_url('berita/' . esc($item['slug'])) ?>" class="btn btn-primary btn-sm w-100 mt-auto">Baca Selengkapnya <i class="bi bi-arrow-right-short"></i></a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert">
                                Belum ada berita untuk ditampilkan saat ini.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Pagination (jika ada) -->
                <?php /* if (isset($pager) && $pager): ?>
                <div class="mt-5">
                    <?= $pager->links('default', 'bootstrap_template') // Ganti 'bootstrap_template' dengan template paginasi Anda jika ada ?>
                </div>
                <?php endif; */ ?>
            </div>
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Widget Pencarian -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="<?= base_url('berita') ?>" method="get">
                            <!-- Honeypot field untuk anti-bot -->
                            <input type="text" 
                                name="honeypot" 
                                style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0;"
                                tabindex="-1" 
                                autocomplete="off"
                                aria-hidden="true">
                            <div class="input-group">
                                <input type="text" 
                                    name="q" 
                                    class="form-control" 
                                    placeholder="Cari berita..." 
                                    value="<?= esc($search_keyword ?? '') ?>"
                                    maxlength="255"
                                    aria-label="Cari berita">
                                <?php if (isset($current_kategori_id) && $current_kategori_id): ?>
                                    <input type="hidden" name="kategori" value="<?= esc($current_kategori_id) ?>">
                                <?php endif; ?>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Widget Kategori -->
                <div class="card mb-4">
                    <div class="card-header fw-bold">Kategori</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($kategori as $kat): ?>
                            <li class="list-group-item<?= ((isset($current_kategori_id) && $current_kategori_id == $kat['id']) ? ' active' : '') ?>">
                                <a href="<?= base_url('berita?kategori=' . esc((string)$kat['id'], 'url')) ?>" class="text-decoration-none<?= ((isset($current_kategori_id) && $current_kategori_id == $kat['id']) ? ' text-white' : '') ?>">
                                    <?= esc(mb_convert_case($kat['nama_kategori'], MB_CASE_TITLE, "UTF-8")) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Widget Berita Terpopuler -->
                <div class="card mb-4">
                    <div class="card-header fw-bold">Berita Terpopuler</div>
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($berita_populer)): ?>
                            <?php foreach ($berita_populer as $pop): ?>
                                <li class="list-group-item">
                                    <a href="<?= base_url('berita/' . esc($pop['slug'])) ?>" class="text-decoration-none d-block">
                                        <span class="fw-semibold d-block mb-1" style="font-size: 1rem; line-height:1.2;">
                                            <?= esc(mb_convert_case($pop['judul'], MB_CASE_TITLE, "UTF-8")) ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= format_tanggal_indo($pop['tanggal_publish'] ?? null) ?>
                                            <?php if (!empty($pop['view_count'])): ?> &bull; <?= esc($pop['view_count']) ?>x dibaca<?php endif; ?>
                                        </small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">Belum ada data.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Widget Arsip Bulanan -->
                <div class="card mb-4">
                    <div class="card-header fw-bold">Arsip Bulanan</div>
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($arsip_bulan)): ?>
                            <?php foreach ($arsip_bulan as $arsip): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="<?= base_url('berita?arsip=' . esc($arsip['bulan'], 'url')) ?>" class="text-decoration-none">
                                        <?= esc($arsip['label']) ?>
                                    </a>
                                    <span class="badge bg-secondary rounded-pill ms-2"><?= esc($arsip['jumlah']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">Belum ada arsip.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pagination (jika ada) -->
        <?php /* if (isset($pager) && $pager): ?>
        <div class="mt-5">
            <?= $pager->links('default', 'bootstrap_template') // Ganti 'bootstrap_template' dengan template paginasi Anda jika ada ?>
        </div>
        <?php endif; */ ?>

    </div>

    <?= $this->endSection() ?>