<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<?= $this->include('frontend/components/hero', [
    'title' => 'Panduan Pengguna',
    'subtitle' => 'Cara menggunakan layanan JDIH dengan maksimal',
    'icon' => 'fa-book',
    'badge' => 'Panduan Pengguna'
]) ?>

<!-- Content Section -->
<div class="container py-5">
    <?= $this->include('frontend/components/breadcrumb', [
        'items' => [
            ['label' => 'Panduan Pengguna', 'url' => '']
        ]
    ]) ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Panduan Penggunaan JDIH</h2>

                    <!-- Pencarian Dokumen Hukum -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="pencarian-dokumen">1. Pencarian Dokumen Hukum</h3>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Pencarian Sederhana</h4>
                            <p>Anda dapat menggunakan fitur pencarian yang tersedia di halaman utama untuk mencari dokumen hukum berdasarkan kata kunci.</p>
                            <ol>
                                <li>Kunjungi halaman beranda JDIH</li>
                                <li>Masukkan kata kunci pada kotak pencarian</li>
                                <li>Klik tombol "Cari" atau tekan Enter</li>
                                <li>Sistem akan menampilkan hasil pencarian yang relevan</li>
                            </ol>
                            <div class="text-center my-4">
                                <img src="<?= base_url('/assets/img/pencarian-sederhana.jpg') ?>" alt="Pencarian Sederhana" class="img-fluid rounded border shadow-sm">
                                <p class="text-muted small mt-2">Contoh Pencarian Sederhana</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Pencarian Lanjutan</h4>
                            <p>Untuk pencarian yang lebih spesifik, gunakan fitur pencarian lanjutan:</p>
                            <ol>
                                <li>Klik link "Pencarian Lanjutan" di samping kotak pencarian</li>
                                <li>Isi parameter pencarian yang diinginkan (jenis peraturan, tahun, nomor, dll.)</li>
                                <li>Klik tombol "Cari"</li>
                                <li>Sistem akan menampilkan hasil yang sesuai dengan kriteria pencarian</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Melihat Dokumen Hukum -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="melihat-dokumen">2. Melihat Dokumen Hukum</h3>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Melihat Daftar Peraturan</h4>
                            <p>Untuk melihat daftar peraturan berdasarkan jenis:</p>
                            <ol>
                                <li>Klik menu "Produk Hukum" pada navigasi</li>
                                <li>Pilih jenis peraturan yang ingin dilihat</li>
                                <li>Sistem akan menampilkan daftar peraturan sesuai jenis yang dipilih</li>
                                <li>Gunakan filter di samping untuk menyaring hasil berdasarkan tahun atau status</li>
                            </ol>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Melihat Detail Peraturan</h4>
                            <p>Untuk melihat detail dan isi peraturan:</p>
                            <ol>
                                <li>Klik judul peraturan dari hasil pencarian atau daftar peraturan</li>
                                <li>Halaman detail akan menampilkan informasi lengkap seperti nomor, tahun, status, dan abstrak peraturan</li>
                                <li>Untuk melihat dokumen, klik tombol "Lihat PDF" atau "Unduh Dokumen"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Mengunduh Dokumen -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="mengunduh-dokumen">3. Mengunduh Dokumen</h3>
                        <p>Anda dapat mengunduh dokumen hukum dalam format PDF dengan cara:</p>
                        <ol>
                            <li>Buka halaman detail peraturan</li>
                            <li>Klik tombol "Unduh Dokumen" atau ikon unduh</li>
                            <li>Pilih lokasi penyimpanan di perangkat Anda</li>
                            <li>Dokumen akan tersimpan dan dapat dibuka menggunakan aplikasi pembaca PDF</li>
                        </ol>
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle mt-1"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Catatan:</strong> Pastikan perangkat Anda memiliki aplikasi pembaca PDF untuk membuka dokumen yang diunduh.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Melihat Berita Hukum -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3">4. Melihat Berita Hukum</h3>
                        <p>Untuk mengakses berita dan informasi hukum terkini:</p>
                        <ol>
                            <li>Klik menu "Berita" pada navigasi utama</li>
                            <li>Pilih kategori berita yang ingin dilihat atau scroll untuk melihat semua berita</li>
                            <li>Klik judul berita untuk membaca isi berita secara lengkap</li>
                            <li>Gunakan fitur pencarian atau filter kategori untuk menemukan berita tertentu</li>
                        </ol>
                    </div>

                    <!-- Menghubungi Administrator -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3">5. Menghubungi Administrator</h3>
                        <p>Jika Anda memiliki pertanyaan atau membutuhkan bantuan:</p>
                        <ol>
                            <li>Klik menu "Kontak" pada navigasi atau footer</li>
                            <li>Isi formulir kontak dengan informasi yang diminta</li>
                            <li>Tuliskan pesan atau pertanyaan Anda dengan jelas</li>
                            <li>Klik tombol "Kirim Pesan"</li>
                            <li>Administrator akan merespon melalui email atau telepon yang Anda berikan</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Pertanyaan yang Sering Diajukan (FAQ)</h2>

                    <div class="accordion" id="accordionFaq">
                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Apakah semua dokumen dapat diunduh secara gratis?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Ya, semua dokumen hukum yang tersedia di JDIH dapat diunduh secara gratis. Tidak ada biaya yang dikenakan untuk mengakses atau mengunduh dokumen.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Apakah saya perlu membuat akun untuk mengakses dokumen?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Tidak, Anda tidak perlu membuat akun untuk mengakses dan mengunduh dokumen umum. Namun, beberapa fitur khusus mungkin memerlukan login.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Bagaimana jika dokumen yang saya cari tidak ditemukan?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Jika dokumen yang Anda cari tidak ditemukan, coba gunakan kata kunci yang berbeda atau lebih umum. Jika tetap tidak ditemukan, silakan hubungi administrator melalui halaman Kontak untuk bantuan lebih lanjut.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Apakah dokumen di JDIH dapat dijadikan referensi resmi?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Ya, dokumen yang tersedia di JDIH adalah dokumen resmi dan dapat dijadikan referensi. Namun, untuk keperluan hukum formal, sebaiknya selalu merujuk pada dokumen fisik resmi yang dikeluarkan oleh instansi terkait.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    Bagaimana cara melaporkan kesalahan atau masalah teknis?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Untuk melaporkan kesalahan data atau masalah teknis, silakan hubungi tim dukungan kami melalui halaman Kontak dengan menjelaskan masalah yang Anda temui secara detail.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Panduan Khusus -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Panduan Khusus</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?= base_url('panduan-harmonisasi') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-balance-scale text-primary me-2"></i>
                            <div>
                                <h6 class="mb-1">Panduan Harmonisasi Peraturan</h6>
                                <p class="mb-0 small text-muted">Cara mengajukan draft peraturan untuk proses harmonisasi</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Video Tutorial -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Video Tutorial</h5>
                </div>
                <div class="card-body">
                    <div class="ratio ratio-16x9 mb-3">
                        <iframe src="https://www.youtube.com/embed/rSlo82v7-8g?si=zPfq4GXsOP5m_6ic" title="Tutorial JDIH" allowfullscreen></iframe>
                    </div>
                    <h6 class="mb-2">Tutorial JDIH</h6>
                    <p class="text-muted small">Video tutorial singkat tentang cara menggunakan fitur-fitur utama JDIH.</p>
                    <hr>
                    <div class="list-group list-group-flush small">
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Pengenalan JDIH
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Cara Mencari Dokumen
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Cara Mengunduh Peraturan
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Fitur Pencarian Lanjutan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Unduh Panduan -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Unduh Panduan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-pdf text-danger fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Panduan Pengguna JDIH</h6>
                            <p class="mb-2 small">Dokumen lengkap untuk panduan penggunaan website JDIH.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Unduh PDF
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-powerpoint text-warning fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Presentasi JDIH</h6>
                            <p class="mb-2 small">Slide presentasi pengenalan fitur-fitur JDIH.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Unduh PPT
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak Bantuan -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Butuh Bantuan?</h5>
                </div>
                <div class="card-body">
                    <p>Jika Anda membutuhkan bantuan lebih lanjut, silakan hubungi tim dukungan kami:</p>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Email</h6>
                                <p class="mb-0 small">bagianhukum@padang.go.id</p>
                            </div>
                        </li>
                        <li class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-phone text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Telepon</h6>
                                <p class="mb-0 small">081169112112 (Senin-Jumat, 08.00-16.00)</p>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-comment text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Chatbot Asisten AI</h6>
                                <p class="mb-0 small">Tersedia 24/7</p>
                                <a href="<?= base_url('chatbot') ?>" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-comments me-1"></i> Mulai Chat
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>