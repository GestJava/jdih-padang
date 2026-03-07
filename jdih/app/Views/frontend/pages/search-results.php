<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-search icon-sm me-1"></i> Hasil Pencarian</span>
                </div>
                <h1 class="hero-title">Hasil Pencarian</h1>
                <?php if (!empty($keyword)): ?>
                    <p class="hero-subtitle">Menampilkan hasil pencarian untuk: "<?= esc($keyword) ?>"</p>
                <?php else: ?>
                    <p class="hero-subtitle">Temukan dokumen hukum yang Anda cari</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Hasil Pencarian</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Filter dan Pencarian -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3">Filter Pencarian</h5>
                    <form action="<?= base_url('cari') ?>" method="get">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="jenis" class="form-label">Jenis Dokumen</label>
                                <select class="form-select" id="jenis" name="jenis">
                                    <option value="">Semua Jenis</option>
                                    <option value="peraturan-daerah">Peraturan Daerah</option>
                                    <option value="peraturan-bupati">Peraturan Bupati</option>
                                    <option value="keputusan-bupati">Keputusan Bupati</option>
                                    <option value="instruksi-bupati">Instruksi Bupati</option>
                                    <option value="surat-edaran">Surat Edaran</option>
                                    <option value="mou">MOU/Perjanjian</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select class="form-select" id="tahun" name="tahun">
                                    <option value="">Semua Tahun</option>
                                    <?php for ($i = date('Y'); $i >= 2010; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="q" class="form-label">Kata Kunci</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="q" name="q" placeholder="Cari berdasarkan judul, nomor, atau kata kunci..." value="<?= esc($keyword ?? '') ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search me-1"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Hasil Pencarian -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Hasil Pencarian</h4>
                <span class="text-muted">Ditemukan <?= rand(5, 25) ?> dokumen</span>
            </div>

            <?php if (empty($keyword)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Silakan masukkan kata kunci untuk mencari dokumen.
                </div>
            <?php else: ?>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="document-card mb-4">
                        <div class="document-body">
                            <div class="document-meta mb-2">
                                <span class="badge bg-primary">Peraturan Daerah</span>
                                <span class="document-number">No. <?= rand(1, 20) ?> Tahun <?= rand(2019, 2023) ?></span>
                            </div>
                            <h5 class="document-title">
                                <a href="<?= base_url('peraturan/detail/' . rand(1, 100)) ?>">
                                    Peraturan Tentang <?= esc(mb_convert_case(str_replace(['-', '_'], ' ', $keyword), MB_CASE_TITLE, "UTF-8")) ?> dan Tata Kelola Pemerintahan Daerah
                                </a>
                            </h5>
                            <p class="document-excerpt">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla euismod, nisl eget ultricies ultricies,
                                nunc nisl ultricies nunc, <mark><?= esc($keyword) ?></mark> eget ultricies nisl nisl eget...
                            </p>
                            <div class="document-info">
                                <div class="document-info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Ditetapkan: <?= date('d M Y', strtotime('-' . rand(1, 365) . ' days')) ?></span>
                                </div>
                                <div class="document-info-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span>Status: Berlaku</span>
                                </div>
                            </div>
                        </div>
                        <div class="document-footer">
                            <a href="<?= base_url('peraturan/detail/' . rand(1, 100)) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> Lihat Detail
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> Unduh PDF
                            </a>
                        </div>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if (!empty($keyword)): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
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
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('peraturan/jenis/peraturan-daerah') ?>" class="text-decoration-none text-dark">
                                Peraturan Daerah
                            </a>
                            <span class="badge bg-primary rounded-pill">24</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('peraturan/jenis/peraturan-bupati') ?>" class="text-decoration-none text-dark">
                                Peraturan Bupati
                            </a>
                            <span class="badge bg-primary rounded-pill">42</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('peraturan/jenis/keputusan-bupati') ?>" class="text-decoration-none text-dark">
                                Keputusan Bupati
                            </a>
                            <span class="badge bg-primary rounded-pill">16</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('peraturan/jenis/instruksi-bupati') ?>" class="text-decoration-none text-dark">
                                Instruksi Bupati
                            </a>
                            <span class="badge bg-primary rounded-pill">8</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('peraturan/jenis/surat-edaran') ?>" class="text-decoration-none text-dark">
                                Surat Edaran
                            </a>
                            <span class="badge bg-primary rounded-pill">12</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Pencarian Populer -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i> Pencarian Populer</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('cari?q=perizinan') ?>" class="btn btn-sm btn-outline-secondary">
                            Perizinan
                        </a>
                        <a href="<?= base_url('cari?q=retribusi') ?>" class="btn btn-sm btn-outline-secondary">
                            Retribusi
                        </a>
                        <a href="<?= base_url('cari?q=pajak+daerah') ?>" class="btn btn-sm btn-outline-secondary">
                            Pajak Daerah
                        </a>
                        <a href="<?= base_url('cari?q=tata+ruang') ?>" class="btn btn-sm btn-outline-secondary">
                            Tata Ruang
                        </a>
                        <a href="<?= base_url('cari?q=lingkungan+hidup') ?>" class="btn btn-sm btn-outline-secondary">
                            Lingkungan Hidup
                        </a>
                        <a href="<?= base_url('cari?q=kesehatan') ?>" class="btn btn-sm btn-outline-secondary">
                            Kesehatan
                        </a>
                        <a href="<?= base_url('cari?q=pendidikan') ?>" class="btn btn-sm btn-outline-secondary">
                            Pendidikan
                        </a>
                        <a href="<?= base_url('cari?q=COVID-19') ?>" class="btn btn-sm btn-outline-secondary">
                            COVID-19
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bantuan Pencarian -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i> Bantuan Pencarian</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Tips Pencarian Efektif:</h6>
                    <ul class="mb-0">
                        <li>Gunakan kata kunci spesifik</li>
                        <li>Untuk mencari frasa tepat, gunakan tanda kutip ("contoh frasa")</li>
                        <li>Gunakan filter jenis dokumen dan tahun</li>
                        <li>Gunakan nomor peraturan untuk pencarian langsung</li>
                    </ul>
                    <hr>
                    <p class="mb-0">Butuh bantuan lebih lanjut? <a href="<?= base_url('kontak') ?>">Hubungi kami</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>