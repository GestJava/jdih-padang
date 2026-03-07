<!-- Service Cards Component -->
<section class="jdih-section jdih-services bg-white">
    <div class="container">
        <div class="section-heading text-center mb-5" data-aos="fade-up">
            <h2><?= esc($title ?? 'Layanan Unggulan JDIH') ?></h2>
            <p><?= esc($subtitle ?? 'Layanan-layanan utama yang kami sediakan untuk kemudahan akses informasi hukum') ?></p>
        </div>
        <div class="row g-4 justify-content-center" data-aos="fade-up">
            <!-- Layanan 1: Pessan Wassi -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon bg-success rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-file-signature fa-2x"></i>
                    </div>
                    <h3 class="h5 service-title">Pessan Wassi</h3>
                    <p class="service-description">Layanan koreksi draf peraturan untuk OPD dan Bagian Hukum.</p>
                    <a href="<?= base_url('login') ?>" class="service-link">Masuk ke Layanan <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <!-- Layanan 2: Layanan Aksesibilitas -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon bg-info rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-universal-access fa-2x"></i>
                    </div>
                    <h3 class="h5 service-title">Layanan Aksesibilitas</h3>
                    <p class="service-description">Fitur pembacaan dokumen PDF untuk pengguna dengan disabilitas visual.</p>
                    <a href="<?= base_url('peraturan') ?>" class="service-link">Coba Fitur <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <!-- Layanan 3: Chatbot AI -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon bg-danger rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-robot fa-2x"></i>
                    </div>
                    <h3 class="h5 service-title">Chatbot AI</h3>
                    <p class="service-description">Dapatkan bantuan hukum dan informasi cepat melalui asisten AI kami.</p>
                    <a href="<?= base_url('chatbot') ?>" class="service-link">Coba Sekarang <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <!-- Layanan 4: Sertifikat Elektronik BSrE -->
            <div class="col-lg-3 col-md-6">
                <div class="service-card h-100">
                    <div class="service-icon bg-primary rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-lock fa-2x"></i>
                    </div>
                    <h3 class="h5 service-title">Sertifikat Elektronik BSrE</h3>
                    <p class="service-description">Dokumen harmonisasi terintegrasi dengan Tanda Tangan Elektronik (TTE) BSrE. Verifikasi keabsahan dokumen secara online.</p>
                    <div class="service-links">
                        <a href="<?= base_url('login') ?>" class="service-link">Akses Layanan <i class="fas fa-arrow-right"></i></a>
                        <a href="https://bsre.bssn.go.id/verify" target="_blank" class="service-link text-primary mt-2 d-block" style="font-size: 0.9em;">
                            <i class="fas fa-external-link-alt"></i> Verifikasi di BSrE
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>