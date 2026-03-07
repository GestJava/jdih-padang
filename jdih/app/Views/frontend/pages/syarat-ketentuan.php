<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="jdih-hero">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <div class="hero-badge mb-3">
                    <span><i class="fas fa-file-contract icon-sm me-1"></i> Syarat & Ketentuan</span>
                </div>
                <h1 class="hero-title">Syarat & Ketentuan</h1>
                <p class="hero-subtitle">Ketentuan penggunaan layanan JDIH</p>
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
            <li class="breadcrumb-item active" aria-current="page">Syarat & Ketentuan</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <article>
                        <h2 class="h4 mb-4">Ketentuan Umum</h2>
                        <p>Selamat datang di situs web Jaringan Dokumentasi dan Informasi Hukum (JDIH). Dengan mengakses atau menggunakan situs web ini, Anda setuju untuk terikat dengan syarat dan ketentuan penggunaan ini. Jika Anda tidak setuju dengan syarat dan ketentuan ini, harap tidak menggunakan situs web ini.</p>

                        <h2 class="h4 mt-5 mb-4">1. Penggunaan Situs Web</h2>
                        <ol type="a" class="ms-2">
                            <li class="mb-2">Anda setuju untuk menggunakan situs web ini hanya untuk tujuan yang sah dan sesuai dengan semua hukum dan peraturan yang berlaku.</li>
                            <li class="mb-2">Anda tidak akan menggunakan situs web ini dengan cara yang dapat merusak, menonaktifkan, membebani, atau mengganggu situs web atau mengganggu penggunaan dan kenikmatan situs web oleh pihak lain.</li>
                            <li class="mb-2">Anda tidak akan mencoba untuk mendapatkan akses tidak sah ke bagian manapun dari situs web, sistem komputer lain yang terhubung ke situs web, atau informasi apapun yang tidak disediakan untuk Anda.</li>
                            <li class="mb-2">Anda bertanggung jawab untuk menjaga kerahasiaan kredensial akun Anda dan bertanggung jawab atas semua aktivitas yang terjadi di bawah akun Anda.</li>
                        </ol>

                        <h2 class="h4 mt-5 mb-4">2. Konten Situs Web</h2>
                        <ol type="a" class="ms-2">
                            <li class="mb-2">Konten yang disediakan di situs web ini adalah untuk tujuan informasi umum saja. Meskipun kami berusaha untuk menyediakan informasi yang akurat dan terkini, kami tidak membuat jaminan apapun mengenai kelengkapan, keandalan, atau keakuratan informasi ini.</li>
                            <li class="mb-2">Semua konten, termasuk teks, grafik, logo, gambar, audio, dan materi lainnya yang terdapat di situs web ini, adalah milik JDIH atau pemberi lisensinya dan dilindungi oleh hukum hak cipta dan hak kekayaan intelektual lainnya.</li>
                            <li class="mb-2">Anda dapat mengunduh atau mencetak konten dari situs web ini hanya untuk penggunaan pribadi, non-komersial Anda, dengan ketentuan bahwa Anda tidak mengubah konten dan Anda menyertakan semua pemberitahuan hak cipta dan kepemilikan lainnya.</li>
                        </ol>

                        <h2 class="h4 mt-5 mb-4">3. Tautan ke Situs Web Lain</h2>
                        <ol type="a" class="ms-2">
                            <li class="mb-2">Situs web ini mungkin berisi tautan ke situs web pihak ketiga. Tautan-tautan ini disediakan hanya untuk kenyamanan Anda.</li>
                            <li class="mb-2">Kami tidak memiliki kendali atas isi dari situs web pihak ketiga tersebut dan tidak bertanggung jawab atas konten atau praktik privasi mereka.</li>
                            <li class="mb-2">Inklusi tautan tidak berarti dukungan oleh JDIH terhadap situs yang ditautkan atau asosiasi dengan operator mereka.</li>
                        </ol>

                        <h2 class="h4 mt-5 mb-4">4. Batasan Tanggung Jawab</h2>
                        <ol type="a" class="ms-2">
                            <li class="mb-2">Sejauh diizinkan oleh hukum yang berlaku, JDIH tidak bertanggung jawab atas kerugian langsung, tidak langsung, insidental, konsekuensial, atau khusus yang timbul dari atau sehubungan dengan penggunaan atau ketidakmampuan untuk menggunakan situs web ini.</li>
                            <li class="mb-2">JDIH tidak menjamin bahwa situs web akan bebas dari kesalahan atau bahwa akses ke situs web akan tidak terganggu.</li>
                            <li class="mb-2">JDIH tidak bertanggung jawab atas kerugian atau kerusakan yang disebabkan oleh virus atau bahan berbahaya lainnya yang mungkin menginfeksi peralatan komputer, program komputer, data, atau materi kepemilikan lainnya karena penggunaan atau akses Anda ke situs web ini atau pengunduhan materi apapun dari situs web.</li>
                        </ol>

                        <h2 class="h4 mt-5 mb-4">5. Privasi</h2>
                        <p>Pengumpulan dan penggunaan informasi pribadi sehubungan dengan situs web ini diatur oleh <a href="<?= base_url('kebijakan-privasi') ?>" class="text-decoration-none">Kebijakan Privasi</a> kami.</p>

                        <h2 class="h4 mt-5 mb-4">6. Perubahan pada Syarat dan Ketentuan</h2>
                        <p>Kami berhak untuk mengubah syarat dan ketentuan ini kapan saja. Perubahan akan berlaku segera setelah diposting di situs web. Penggunaan Anda terus-menerus pada situs web setelah perubahan tersebut akan merupakan persetujuan Anda terhadap syarat dan ketentuan yang telah diubah.</p>

                        <h2 class="h4 mt-5 mb-4">7. Hukum yang Berlaku</h2>
                        <p>Syarat dan ketentuan ini diatur oleh dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap perselisihan yang timbul dari atau sehubungan dengan syarat dan ketentuan ini akan diselesaikan melalui forum yang memiliki yurisdiksi di Indonesia.</p>

                        <h2 class="h4 mt-5 mb-4">8. Penafian</h2>
                        <p>Informasi yang disediakan di situs web ini dimaksudkan sebagai referensi umum dan tidak boleh dianggap sebagai nasihat hukum. Untuk nasihat hukum khusus yang sesuai dengan situasi Anda, silakan konsultasikan dengan penasihat hukum yang berkualifikasi.</p>

                        <h2 class="h4 mt-5 mb-4">9. Kontak</h2>
                        <p>Jika Anda memiliki pertanyaan atau kekhawatiran tentang syarat dan ketentuan ini, silakan hubungi kami di:</p>
                        <address>
                            <strong>Jaringan Dokumentasi dan Informasi Hukum (JDIH)</strong><br>
                            Jl. Bagindo Aziz Chan No. 1, Padang, Sumatera Barat<br>
                            Email: bagianhukum@padang.go.id<br>
                            Telepon: 081169112112
                        </address>

                        <p class="mt-5 text-muted small">Terakhir diperbarui: 1 Juni 2023</p>
                    </article>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Navigasi Dokumen -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Navigasi Dokumen</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?= base_url('kebijakan-privasi') ?>" class="list-group-item list-group-item-action">Kebijakan Privasi</a>
                        <a href="#" class="list-group-item list-group-item-action">Syarat & Ketentuan</a>
                        <a href="<?= base_url('panduan') ?>" class="list-group-item list-group-item-action">Panduan Pengguna</a>
                        <a href="<?= base_url('kontak') ?>" class="list-group-item list-group-item-action">Kontak Kami</a>
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Informasi Tambahan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Butuh bantuan?</h6>
                            <p class="mb-0 small">Jika Anda memiliki pertanyaan tentang syarat dan ketentuan ini, silakan hubungi tim kami.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-alt text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Unduh Dokumen</h6>
                            <p class="mb-0 small">Anda dapat <a href="#" class="text-decoration-none">mengunduh versi PDF</a> dari syarat dan ketentuan ini.</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-history text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Versi Sebelumnya</h6>
                            <p class="mb-0 small">Anda dapat melihat <a href="#" class="text-decoration-none">versi sebelumnya</a> dari syarat dan ketentuan kami.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>