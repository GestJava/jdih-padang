<?php
helper('util');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm jdih-navbar">
    <div class="container">
        <!-- Logo and Brand -->
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url('/') ?>" aria-label="JDIH Kota Padang - Beranda">
            <img src="<?= base_url('assets/img/jdihkotapadang.png') ?>" alt="Logo JDIH Kota Padang" height="40" class="me-2" loading="lazy">
            <div class="brand-text">
                <span class="fw-bold text-primary d-block brand-title">JDIH KOTA PADANG</span>
                <small class="text-secondary d-none d-sm-block brand-subtitle">Jaringan Dokumentasi dan Informasi Hukum</small>
            </div>
        </a>

        <!-- Mobile Menu Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarJDIH" aria-controls="navbarJDIH" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarJDIH">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= url_is('/') ? 'active' : '' ?>" aria-current="page" href="<?= base_url('/') ?>">
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= url_is('tentang*') ? 'active' : '' ?>" href="<?= base_url('tentang') ?>">
                        Tentang
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= url_is('peraturan*') ? 'active' : '' ?>" href="<?= base_url('peraturan') ?>" id="produkHukumDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                        Jenis Dokumen
                    </a>
                    <ul class="dropdown-menu shadow border-0" aria-labelledby="produkHukumDropdown" role="menu">
                        <?php if (isset($global_jenis_peraturan) && is_array($global_jenis_peraturan) && !empty($global_jenis_peraturan)): ?>
                            <?php foreach ($global_jenis_peraturan as $kategori_nama => $jenis_list): ?>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#"><?= esc($kategori_nama) ?></a>
                                    <ul class="dropdown-menu shadow border-0">
                                        <?php foreach ($jenis_list as $jenis): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('peraturan/jenis/' . esc($jenis['slug_jenis'], 'url')) ?>">
                                                    <?= esc($jenis['nama_jenis']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?= base_url('peraturan') ?>">Semua Dokumen</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= url_is('peraturan*') ? 'active' : '' ?>" href="<?= base_url('peraturan') ?>" id="pembentukanPUUDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                        Pembentukan PUU
                    </a>
                    <ul class="dropdown-menu shadow border-0" aria-labelledby="pembentukanPUUDropdown" role="menu">
                        <?php if (isset($global_jenis_puu) && is_array($global_jenis_puu) && !empty($global_jenis_puu)): ?>
                            <?php foreach ($global_jenis_puu as $kategori_nama => $jenis_list): ?>
                                <?php foreach ($jenis_list as $jenis): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('peraturan/jenis/' . esc($jenis['slug_jenis'], 'url')) ?>">
                                            <?= esc($jenis['nama_jenis']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?= base_url('peraturan') ?>">Semua Dokumen</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= url_is('berita*') ? 'active' : '' ?>" href="<?= base_url('berita') ?>">
                        Berita
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= url_is('statistik*') ? 'active' : '' ?>" href="<?= base_url('statistik') ?>">
                        Statistik
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= url_is('kontak*') ? 'active' : '' ?>" href="<?= base_url('kontak') ?>">
                        Kontak
                    </a>
                </li>
            </ul>

            <!-- Login/Admin Button -->
            <div class="d-flex align-items-center mt-3 mt-lg-0 ms-lg-3">
                <?php
                // KEAMANAN: Gunakan session service untuk memastikan data selalu fresh
                // Jangan gunakan session() helper karena mungkin ter-cache
                $session = service('session');
                $user = $session->get('user');
                
                // Validasi user benar-benar ada dan memiliki data yang valid
                $isUserLoggedIn = !empty($user) && is_array($user) && !empty($user['id_user']) && !empty($user['nama']);
                ?>
                <?php if ($isUserLoggedIn): ?>
                    <!-- User sudah login - tampilkan tombol admin modern -->
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php
                            // Pastikan nama user selalu diambil dari session yang fresh
                            $userNama = $user['nama'] ?? 'Admin';
                            if (strlen($userNama) > 12) {
                                echo '<span title="' . esc($userNama) . '">' . esc(substr($userNama, 0, 9) . '...') . '</span>';
                            } else {
                                echo esc($userNama);
                            }
                            ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= base_url('dashboard') ?>">
                                    <i class="fas fa-desktop me-2"></i> Dashboard Admin
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= base_url('login/logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- User belum login - tampilkan tombol login yang mengarah ke halaman login -->
                    <a href="<?= base_url('login') ?>" class="btn btn-primary">
                        <i class="fas fa-shield-alt me-1"></i> Admin Panel
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>