<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<?= $this->include('frontend/components/hero', [
    'title' => 'Kontak Kami',
    'subtitle' => 'Hubungi kami untuk informasi lebih lanjut tentang JDIH',
    'icon' => 'fa-phone',
    'badge' => 'Kontak Kami'
]) ?>

<!-- Content Section -->
<div class="container py-5">
    <?= $this->include('frontend/components/breadcrumb', [
        'items' => [
            ['label' => 'Kontak', 'url' => '']
        ]
    ]) ?>

    <div class="row">
        <!-- Form Kontak -->
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Kirim Pesan</h2>

                    <?php
                    // Inisialisasi validation. Jika dikirim dari controller, gunakan itu. Jika tidak, buat instance baru.
                    $validation = $validation ?? \Config\Services::validation();
                    $session = session();
                    ?>

                    <?php if ($session->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= esc($session->getFlashdata('success')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($session->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= esc($session->getFlashdata('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('/kontak') ?>" method="post" class="php-email-form">
                        <?= csrf_field() ?>

                        <!-- Honeypot field to catch bots (hidden dengan teknik yang lebih baik) -->
                        <input type="text" 
                            name="website" 
                            style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0;"
                            tabindex="-1" 
                            autocomplete="off"
                            aria-hidden="true">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" 
                                        class="form-control <?= ($validation->hasError('nama')) ? 'is-invalid' : '' ?>" 
                                        id="nama" 
                                        name="nama" 
                                        placeholder="Nama Lengkap" 
                                        value="<?= esc(set_value('nama', '', false)) ?>"
                                        maxlength="100">
                                    <label for="nama">Nama Lengkap</label>
                                    <div class="invalid-feedback"><?= esc($validation->getError('nama')) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" 
                                        class="form-control <?= ($validation->hasError('email')) ? 'is-invalid' : '' ?>" 
                                        id="email" 
                                        name="email" 
                                        placeholder="Email" 
                                        value="<?= esc(set_value('email', '', false)) ?>"
                                        maxlength="100">
                                    <label for="email">Email</label>
                                    <div class="invalid-feedback"><?= esc($validation->getError('email')) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="tel" 
                                        class="form-control <?= ($validation->hasError('telepon')) ? 'is-invalid' : '' ?>" 
                                        id="telepon" 
                                        name="telepon" 
                                        placeholder="Nomor Telepon" 
                                        value="<?= esc(set_value('telepon', '', false)) ?>"
                                        maxlength="20">
                                    <label for="telepon">Nomor Telepon</label>
                                    <div class="invalid-feedback"><?= esc($validation->getError('telepon')) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select <?= ($validation->hasError('subjek')) ? 'is-invalid' : '' ?>" id="subjek" name="subjek">
                                        <option value="">Pilih Subjek</option>
                                        <option value="Pertanyaan" <?= set_select('subjek', 'Pertanyaan') ?>>Pertanyaan</option>
                                        <option value="Informasi" <?= set_select('subjek', 'Informasi') ?>>Permintaan Informasi</option>
                                        <option value="Layanan" <?= set_select('subjek', 'Layanan') ?>>Layanan</option>
                                        <option value="Lainnya" <?= set_select('subjek', 'Lainnya') ?>>Lainnya</option>
                                    </select>
                                    <label for="subjek">Subjek</label>
                                    <div class="invalid-feedback"><?= esc($validation->getError('subjek')) ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control <?= ($validation->hasError('pesan')) ? 'is-invalid' : '' ?>" 
                                        id="pesan" 
                                        name="pesan" 
                                        style="height: 150px" 
                                        placeholder="Pesan"
                                        maxlength="1000"><?= esc(set_value('pesan', '', false)) ?></textarea>
                                    <label for="pesan">Pesan</label>
                                    <div class="invalid-feedback"><?= esc($validation->getError('pesan')) ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="privasi" name="privasi" required>
                                    <label class="form-check-label" for="privasi">
                                        Saya menyetujui <a href="<?= base_url('kebijakan-privasi') ?>" class="text-decoration-none">kebijakan privasi</a> yang berlaku
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="g-recaptcha" data-sitekey="<?= getenv('recaptcha.siteKey') ?>"></div>
                                <?php if ($validation->hasError('g-recaptcha-response')) : ?>
                                    <div class="text-danger small mt-1">
                                        <?= esc($validation->getError('g-recaptcha-response')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informasi Kontak -->
        <div class="col-lg-5">
            <!-- Info Kontak -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Informasi Kontak</h2>
                    <ul class="list-unstyled">
                        <li class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="h6 mb-1">Alamat</h5>
                                <p class="mb-0"><?= esc($config['alamat'] ?? 'Alamat tidak tersedia') ?></p>
                            </div>
                        </li>
                        <li class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-phone"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="h6 mb-1">Telepon</h5>
                                <p class="mb-0"><?= esc($config['telepon'] ?? 'Telepon tidak tersedia') ?></p>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="h6 mb-1">Email</h5>
                                <p class="mb-0"><?= esc($config['email'] ?? 'Email tidak tersedia') ?></p>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="h6 mb-1">Jam Operasional</h5>
                                <p class="mb-0">Senin - Jumat: 08.00 - 16.00 WIB</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Media Sosial -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Media Sosial</h2>
                    <div class="d-flex">
                        <a href="#" class="btn btn-outline-primary me-2" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-outline-info me-2" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-danger me-2" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="btn btn-outline-success me-2" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="btn btn-outline-secondary" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Peta Lokasi -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9">
                        <iframe src="<?= esc($config['maps'] ?? '') ?>" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>