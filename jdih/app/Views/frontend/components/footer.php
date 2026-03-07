<footer class="footer bg-dark pt-5">
    <div class="container">
        <!-- Footer Main Content -->
        <div class="row g-4 mb-4">
            <!-- About JDIH -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= base_url('assets/img/logo-jdih.png') ?>"
                        alt="Logo JDIH Kota Padang"
                        height="45"
                        class="me-2"
                        loading="lazy"
                        onerror="this.style.display='none'">
                </div>
                <p class="text-white-75 mb-3">
                    JDIH menyediakan akses informasi produk hukum yang komprehensif, akurat, dan terkini untuk memenuhi kebutuhan informasi hukum bagi masyarakat, pemerintah, dan para pemangku kepentingan.
                </p>

                <!-- Social Media & App Store -->
                <div class="footer-social-section mb-4">
                    <div class="social-media-container mb-3">
                        <h5 class="h6 text-white-75 mb-2 small text-uppercase">Ikuti Kami</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="https://facebook.com/jdihpadang"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="social-icon facebook"
                                aria-label="Facebook JDIH Kota Padang">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/jdihpadang"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="social-icon twitter"
                                aria-label="Twitter JDIH Kota Padang">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://instagram.com/jdihpadang"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="social-icon instagram"
                                aria-label="Instagram JDIH Kota Padang">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://youtube.com/@jdihpadang"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="social-icon youtube"
                                aria-label="YouTube JDIH Kota Padang">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="https://tiktok.com/@jdihpadang"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="social-icon tiktok"
                                aria-label="TikTok JDIH Kota Padang">
                                <i class="fab fa-tiktok"></i>
                            </a>
                            <a href="https://wa.me/6281169112112"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="social-icon whatsapp"
                                aria-label="WhatsApp JDIH Kota Padang">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="https://t.me/jdihpadang"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="social-icon telegram"
                                aria-label="Telegram JDIH Kota Padang">
                                <i class="fab fa-telegram-plane"></i>
                            </a>
                        </div>
                    </div>

                    <div class="app-store-container">
                        <h5 class="h6 text-white-75 mb-2 small text-uppercase">Download App</h5>
                        <div class="d-flex justify-content-center justify-content-md-start">
                            <a href="https://play.google.com/store/apps/details?id=com.kominfo.jdihkotapadangv2&pli=1"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Download JDIH Kota Padang di Google Play Store"
                                class="d-inline-block">
                                <img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png"
                                    alt="Get it on Google Play"
                                    class="google-play-badge"
                                    loading="lazy">
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Pengunjung -->
            <div class="col-lg-4 col-md-6">
                <h4 class="h5 text-white text-uppercase fs-6 fw-bold mb-4">Statistik Pengunjung</h4>
                <div class="stats-container">
                    <?php
                    // Load visitor stats jika belum tersedia dari global_data
                    if (!isset($stat_total)) {
                        helper('cache');
                        $cache = \Config\Services::cache();
                        $globalData = $cache->get('frontend_global_data');
                        
                        if ($globalData === null) {
                            // Jika cache tidak ada, load langsung dari model
                            $visitorModel = new \App\Models\VisitorStatsModel();
                            $visitorStats = $visitorModel->getVisitorStats();
                            $stat_total = $visitorStats['total'] ?? 0;
                            $stat_today = $visitorStats['today'] ?? 0;
                            $stat_week = $visitorStats['week'] ?? 0;
                            $stat_month = $visitorStats['month'] ?? 0;
                            $stat_year = $visitorStats['year'] ?? 0;
                            $stat_online = $visitorStats['online'] ?? 0;
                        } else {
                            // Gunakan data dari cache global_data
                            $stat_total = $globalData['stat_total'] ?? 0;
                            $stat_today = $globalData['stat_today'] ?? 0;
                            $stat_week = $globalData['stat_week'] ?? 0;
                            $stat_month = $globalData['stat_month'] ?? 0;
                            $stat_year = $globalData['stat_year'] ?? 0;
                            $stat_online = $globalData['stat_online'] ?? 0;
                        }
                    }
                    ?>
                    <?php if (isset($stat_total) && $stat_total > 0): ?>
                        <ul class="list-unstyled text-white-75 stats-list">
                            <li class="mb-1 d-flex justify-content-between">
                                <span>Total Pengunjung:</span>
                                <strong class="text-primary"><?= number_format($stat_total) ?></strong>
                            </li>
                            <li class="mb-1 d-flex justify-content-between">
                                <span>Hari Ini:</span>
                                <strong class="text-primary"><?= number_format($stat_today ?? 0) ?></strong>
                            </li>
                            <li class="mb-1 d-flex justify-content-between">
                                <span>Minggu Ini:</span>
                                <strong class="text-primary"><?= number_format($stat_week ?? 0) ?></strong>
                            </li>
                            <li class="mb-1 d-flex justify-content-between">
                                <span>Bulan Ini:</span>
                                <strong class="text-primary"><?= number_format($stat_month ?? 0) ?></strong>
                            </li>
                            <li class="mb-1 d-flex justify-content-between">
                                <span>Tahun Ini:</span>
                                <strong class="text-primary"><?= number_format($stat_year ?? 0) ?></strong>
                            </li>
                            <li class="mb-1 d-flex justify-content-between">
                                <span>Online:</span>
                                <strong class="text-success"><?= number_format($stat_online ?? 0) ?></strong>
                            </li>
                        </ul>
                    <?php else: ?>
                        <div class="stats-loading text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-white-75 small">Memuat statistik...</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kontak Kami -->
            <div class="col-lg-4 col-md-6">
                <h4 class="h5 text-white text-uppercase fs-6 fw-bold mb-4">Kontak Kami</h4>
                <div class="contact-info">
                    <div class="mb-3 d-flex">
                        <div class="me-3 flex-shrink-0">
                            <i class="fas fa-map-marker-alt icon-md icon-primary"></i>
                        </div>
                        <div class="text-white-75">
                            <strong>Alamat:</strong><br>
                            Jl. Bagindo Aziz Chan No. 1, Aie Pacah, Kota Padang, Sumatera Barat 25173
                        </div>
                    </div>
                    <div class="mb-3 d-flex">
                        <div class="me-3 flex-shrink-0">
                            <i class="fas fa-phone-alt icon-md icon-primary"></i>
                        </div>
                        <div class="text-white-75">
                            <strong>Telepon:</strong><br>
                            <a href="tel:+6281169112112" class="text-white-75 text-decoration-none hover-text-primary">
                                081169112112
                            </a><br>
                            <small class="text-white-50">Senin - Jumat, 08:00 - 16:00 WIB</small>
                        </div>
                    </div>
                    <div class="mb-3 d-flex">
                        <div class="me-3 flex-shrink-0">
                            <i class="fas fa-envelope icon-md icon-primary"></i>
                        </div>
                        <div class="text-white-75">
                            <strong>Email:</strong><br>
                            <a href="mailto:bagianhukum@padang.go.id" class="text-white-75 text-decoration-none hover-text-primary">
                                bagianhukum@padang.go.id
                            </a>
                        </div>
                    </div>
                    <div class="mb-3 d-flex">
                        <div class="me-3 flex-shrink-0">
                            <i class="fas fa-globe icon-md icon-primary"></i>
                        </div>
                        <div class="text-white-75">
                            <strong>Website:</strong><br>
                            <a href="https://jdih.padang.go.id" class="text-white-75 text-decoration-none hover-text-primary" target="_blank" rel="noopener noreferrer">
                                jdih.padang.go.id
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <hr class="border-secondary my-4">

        <!-- Footer Bottom -->
        <div class="row py-3">
            <div class="col-md-6">
                <p class="mb-md-0 text-white-75 text-center text-md-start">
                    &copy; <?= date('Y') ?> JDIH Kota Padang. Hak Cipta Dilindungi.<br>
                    <small class="text-white-50">Dikembangkan oleh Tim IT Pemerintah Kota Padang</small>
                </p>
            </div>
            <div class="col-md-6">
                <ul class="list-inline mb-0 text-center text-md-end footer-bottom-links">
                    <li class="list-inline-item">
                        <a href="<?= base_url('kebijakan-privasi') ?>" class="text-white-75 text-decoration-none hover-text-primary">
                            <i class="fas fa-shield-alt icon-sm me-1"></i>Kebijakan Privasi
                        </a>
                    </li>
                    <li class="list-inline-item ms-3">
                        <a href="<?= base_url('syarat-ketentuan') ?>" class="text-white-75 text-decoration-none hover-text-primary">
                            <i class="fas fa-file-contract icon-sm me-1"></i>Syarat & Ketentuan
                        </a>
                    </li>
                    <li class="list-inline-item ms-3">
                        <a href="<?= base_url('panduan') ?>" class="text-white-75 text-decoration-none hover-text-primary">
                            <i class="fas fa-question-circle icon-sm me-1"></i>Bantuan
                        </a>
                    </li>
                    <li class="list-inline-item ms-3">
                        <a href="<?= base_url('panduan-harmonisasi') ?>" class="text-white-75 text-decoration-none hover-text-primary">
                            <i class="fas fa-balance-scale icon-sm me-1"></i>Harmonisasi
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?php
    // Load Google reCAPTCHA only on pages that need it
    $currentUri = uri_string();
    $recaptchaPages = ['kontak'];
    $needsRecaptcha = false;

    foreach ($recaptchaPages as $page) {
        if (strpos($currentUri, $page) !== false) {
            $needsRecaptcha = true;
            break;
        }
    }
    ?>

    <?php if ($needsRecaptcha): ?>
        <!-- Google reCAPTCHA - Loaded conditionally -->
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
</footer>