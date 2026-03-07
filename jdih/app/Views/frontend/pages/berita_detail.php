<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-10 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-newspaper icon-sm me-1"></i> Detail Berita</span>
                </div>
                <h1 class="hero-title"><?= esc(mb_convert_case($berita['judul'], MB_CASE_TITLE, "UTF-8")); ?></h1>
                <p class="hero-subtitle">
                    <i class="fa-regular fa-clock me-1"></i> Dipublikasikan pada:
                    <?php
                    echo format_tanggal_indo($berita['tanggal_publish'] ?? null) . ', ' . date('H:i', strtotime($berita['tanggal_publish']));
                    ?> WIB
                    <?php if (isset($berita['nama_penulis'])): ?>
                        | <i class="fa-regular fa-user me-1"></i> Oleh: <?= esc(mb_convert_case($berita['nama_penulis'], MB_CASE_TITLE, "UTF-8")); ?>
                    <?php else: ?>
                        | <i class="fa-regular fa-user me-1"></i> Oleh: Admin
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<div class="container py-5">
    <?= $this->include('frontend/components/breadcrumb', [
        'items' => [
            ['label' => 'Berita', 'url' => 'berita'],
            ['label' => esc(mb_convert_case($berita['judul'], MB_CASE_TITLE, "UTF-8")), 'url' => '']
        ]
    ]) ?>

    <div class="row">
        <div class="col-lg-8">
            <article class="entry entry-single">
                <div class="entry-img mb-4">
                    <?php
                    // Default placeholder dari assets/img
                    $gambar_url = base_url('assets/img/news1.jpg');
                    
                    // Jika ada gambar di database, gunakan dari uploads/berita/
                    if (!empty($berita['gambar']) && trim($berita['gambar']) !== '') {
                        // Path relatif dan full path check
                        $relativePath = 'uploads/berita/' . $berita['gambar'];
                        $fullPath = FCPATH . $relativePath;
                        
                        if (file_exists($fullPath)) {
                            $gambar_url = base_url($relativePath);
                        }
                    }
                    ?>
                    <img src="<?= $gambar_url ?>" alt="<?= esc($berita['judul']); ?>" class="img-fluid rounded shadow-sm" onerror="this.onerror=null; this.src='<?= base_url('assets/img/news1.jpg') ?>';">
                </div>

                <div class="entry-meta mb-3 text-muted small">
                    <span><i class="fa-regular fa-eye me-1"></i> <?= esc($berita['view_count']); ?> Kali Dibaca</span>
                    <?php if (isset($berita['nama_kategori'])): ?>
                        <span class="ms-3"><i class="fa-solid fa-tag me-1"></i> Kategori: <a href="#"><?= esc(mb_convert_case($berita['nama_kategori'], MB_CASE_TITLE, "UTF-8")); ?></a></span>
                    <?php endif; ?>
                </div>

                <div class="entry-content">
                    <?= $berita['isi_berita']; // Pastikan konten ini aman (misalnya, di-sanitized saat input atau output) 
                    ?>
                </div>
                <!-- Tombol Share Sosial Media di bawah artikel -->
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 mt-4 mb-2">
                    <?php
                    $url = current_url();
                    $judul = isset($berita['judul']) ? $berita['judul'] : '';
                    $shareText = urlencode($judul . ' - ' . $url);
                    ?>
                    <span class="me-2 text-secondary small">Bagikan:</span>
                    <a href="https://wa.me/?text=<?= rawurlencode($judul . ' ' . $url) ?>" target="_blank" class="btn btn-light btn-sm px-2 py-1 rounded-circle" title="Bagikan ke WhatsApp"><i class="fa-brands fa-whatsapp text-success"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($url) ?>" target="_blank" class="btn btn-light btn-sm px-2 py-1 rounded-circle" title="Bagikan ke Facebook"><i class="fa-brands fa-facebook text-primary"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($url) ?>&text=<?= rawurlencode($judul) ?>" target="_blank" class="btn btn-light btn-sm px-2 py-1 rounded-circle" title="Bagikan ke Twitter"><i class="fa-brands fa-twitter text-info"></i></a>
                    <a href="https://t.me/share/url?url=<?= rawurlencode($url) ?>&text=<?= rawurlencode($judul) ?>" target="_blank" class="btn btn-light btn-sm px-2 py-1 rounded-circle" title="Bagikan ke Telegram"><i class="fa-brands fa-telegram text-primary"></i></a>
                    <button class="btn btn-light btn-sm px-2 py-1 rounded-circle" title="Salin Link" onclick="navigator.clipboard.writeText('<?= esc($url, 'js') ?>');this.innerHTML='<i class=\'fa-solid fa-clipboard-check text-success\'></i>'; setTimeout(()=>{this.innerHTML='<i class=\'fa-regular fa-clipboard\'></i>';},1500);"><i class="fa-regular fa-clipboard"></i></button>
                </div>
            </article>
        </div>

        <div class="col-lg-4">
            <div class="sidebar sticky-top" style="top: 20px;">
                <h3 class="sidebar-title h5 mb-3">Berita Lainnya</h3>
                <div class="sidebar-item recent-posts">
                    <?php if (!empty($beritaLainnya)): ?>
                        <?php foreach ($beritaLainnya as $itemLain): ?>
                            <div class="post-item d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <?php
                                    // Default placeholder dari assets/img
                                    $thumb_url = base_url('assets/img/news1.jpg');
                                    
                                    // Jika ada gambar di database, gunakan dari uploads/berita/
                                    // Cek dengan isset dan !empty untuk memastikan field ada dan tidak kosong
                                    if (isset($itemLain['gambar']) && !empty($itemLain['gambar']) && trim($itemLain['gambar']) !== '') {
                                        // Path relatif dan full path check
                                        $relativePath = 'uploads/berita/' . $itemLain['gambar'];
                                        $fullPath = FCPATH . $relativePath;
                                        
                                        if (file_exists($fullPath)) {
                                            $thumb_url = base_url($relativePath);
                                        }
                                    }
                                    ?>
                                    <img src="<?= $thumb_url ?>" alt="<?= esc($itemLain['judul']); ?>" class="img-fluid rounded" style="width: 80px; height: 60px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= base_url('assets/img/news1.jpg') ?>';">
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="h6 mb-1"><a href="<?= base_url('berita/' . esc($itemLain['slug'])); ?>" class="text-decoration-none"><?= esc(mb_convert_case($itemLain['judul'], MB_CASE_TITLE, "UTF-8")); ?></a></h4>
                                    <time datetime="<?= esc($itemLain['tanggal_publish']); ?>" class="text-muted small">
                                        <?php
                                        echo format_tanggal_indo($itemLain['tanggal_publish'] ?? null);
                                        ?>
                                    </time>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada berita lainnya untuk ditampilkan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>