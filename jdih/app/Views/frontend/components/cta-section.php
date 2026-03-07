<!-- Call to Action Section Component -->
<section class="jdih-section jdih-cta my-5">
    <div class="container">
        <div class="section-heading text-center mb-4" data-aos="fade-up">
            <h2><?= esc($title ?? 'Gabung Bersama JDIH!') ?></h2>
            <p><?= esc($subtitle ?? 'Dukung keterbukaan informasi hukum dengan berkontribusi atau memberikan masukan kepada JDIH kami. Bersama kita wujudkan tata kelola pemerintahan yang transparan dan akuntabel.') ?></p>
        </div>
        <div class="text-center" data-aos="fade-up" data-aos-delay="100">
            <a href="<?= base_url('kontak') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-phone-alt me-2"></i> <?= esc($button_text ?? 'Hubungi Kami') ?>
            </a>
        </div>
    </div>
</section>