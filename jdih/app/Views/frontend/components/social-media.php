<!-- Social Media Section Component -->
<section class="jdih-section py-5 bg-light">
    <div class="container">
        <div class="section-heading text-center mb-5" data-aos="fade-up">
            <h2><?= esc($title ?? 'Terhubung Bersama Kami') ?></h2>
            <p class="lead text-muted"><?= esc($subtitle ?? 'Lihat aktivitas terbaru kami di Instagram dan YouTube.') ?></p>
        </div>
        <div class="row g-5 align-items-center">
            <!-- Instagram Feed -->
            <div class="col-lg-7" data-aos="fade-right">
                <h3 class="h4 mb-4 text-center text-lg-start">Galeri Instagram</h3>
                <div class="row g-3">
                    <!-- Instagram Post 1 -->
                    <div class="col-6 col-md-3">
                        <a href="https://www.instagram.com/p/DK8qKNXxRWt/" target="_blank" rel="noopener">
                            <img data-src="<?= base_url('assets/images/instagram/post1.png') ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23dee2e6'%3EIG%3C/text%3E%3C/svg%3E" class="img-fluid rounded shadow-sm lazy" alt="Postingan JDIH Kota Padang di Instagram" width="200" height="200" loading="lazy">
                        </a>
                    </div>
                    <!-- Instagram Post 2 -->
                    <div class="col-6 col-md-3">
                        <a href="https://www.instagram.com/p/DKbZbdeS18f/" target="_blank" rel="noopener">
                            <img data-src="<?= base_url('assets/images/instagram/post2.png') ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23dee2e6'%3EIG%3C/text%3E%3C/svg%3E" class="img-fluid rounded shadow-sm lazy" alt="Postingan JDIH Kota Padang di Instagram" width="200" height="200" loading="lazy">
                        </a>
                    </div>
                    <!-- Instagram Post 3 -->
                    <div class="col-6 col-md-3">
                        <a href="https://www.instagram.com/p/DKJVYSdpDhW/" target="_blank" rel="noopener">
                            <img data-src="<?= base_url('assets/images/instagram/post3.png') ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23dee2e6'%3EIG%3C/text%3E%3C/svg%3E" class="img-fluid rounded shadow-sm lazy" alt="Postingan JDIH Kota Padang di Instagram" width="200" height="200" loading="lazy">
                        </a>
                    </div>
                    <!-- Instagram Post 4 -->
                    <div class="col-6 col-md-3">
                        <a href="https://www.instagram.com/p/DK_ImiuxAKi/" target="_blank" rel="noopener">
                            <img data-src="<?= base_url('assets/images/instagram/post4.png') ?>" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23dee2e6'%3EIG%3C/text%3E%3C/svg%3E" class="img-fluid rounded shadow-sm lazy" alt="Postingan JDIH Kota Padang di Instagram" width="200" height="200" loading="lazy">
                        </a>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="https://www.instagram.com/jdih.padang/" target="_blank" class="btn btn-outline-dark">
                        <i class="fab fa-instagram me-2"></i> Ikuti di Instagram
                    </a>
                </div>
            </div>

            <!-- YouTube Embed -->
            <div class="col-lg-5" data-aos="fade-left">
                <h3 class="h4 mb-4 text-center text-lg-start">Video Unggulan</h3>
                <div class="ratio ratio-16x9 rounded shadow-sm">
                    <iframe 
                        src="https://www.youtube.com/embed/Ovy_wqgPqz0?si=tEjP34SCTKdXGf2s" 
                        title="YouTube video player" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen 
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>