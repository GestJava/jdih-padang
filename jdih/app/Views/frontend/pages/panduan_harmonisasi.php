<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<?= $this->include('frontend/components/hero', [
    'title' => 'Panduan Harmonisasi Peraturan',
    'subtitle' => 'Cara mengajukan draft peraturan untuk proses harmonisasi',
    'icon' => 'fa-balance-scale',
    'badge' => 'Harmonisasi Peraturan'
]) ?>

<!-- Content Section -->
<div class="container py-5">
    <?= $this->include('frontend/components/breadcrumb', [
        'items' => [
            ['label' => 'Panduan', 'url' => base_url('panduan')],
            ['label' => 'Harmonisasi Peraturan', 'url' => '']
        ]
    ]) ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Panduan Harmonisasi Peraturan</h2>

                    <!-- Pengenalan Harmonisasi -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="pengenalan">1. Apa itu Harmonisasi Peraturan?</h3>
                        <p>Harmonisasi peraturan adalah proses penyesuaian dan sinkronisasi rancangan peraturan dengan peraturan perundang-undangan yang lebih tinggi. Proses ini memastikan bahwa peraturan daerah tidak bertentangan dengan peraturan yang lebih tinggi dan sesuai dengan asas-asas pembentukan peraturan perundang-undangan yang baik.</p>

                        <div class="alert alert-info">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle mt-1"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Tujuan Harmonisasi:</strong> Memastikan rancangan peraturan  tidak bertentangan dengan peraturan yang lebih tinggi dan sesuai dengan asas-asas pembentukan peraturan perundang-undangan yang baik.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Persyaratan Pengajuan -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="persyaratan">2. Persyaratan Pengajuan</h3>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Dokumen yang Diperlukan</h4>
                            <ul>
                                <li><strong>Draft Peraturan:</strong> File dalam format PDF, DOC, atau DOCX (maksimal 25MB)</li>
                                <li><strong>Judul Peraturan:</strong> Judul yang jelas dan mencerminkan isi peraturan</li>
                                <li><strong>Jenis Peraturan:</strong> Kategori peraturan yang sesuai</li>
                                <li><strong>Keterangan Tambahan:</strong> Penjelasan konteks dan latar belakang peraturan</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Syarat Akun Pengguna</h4>
                            <ul>
                                <li>Memiliki akun terdaftar di sistem JDIH</li>
                                <li>Akun harus terverifikasi dan aktif</li>
                                <li>Memiliki akses sebagai user OPD/Instansi</li>
                                <li>Terdaftar sebagai perwakilan instansi yang berwenang</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Langkah-langkah Pengajuan -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="langkah-pengajuan">3. Langkah-langkah Pengajuan Draft Peraturan</h3>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Langkah 1: Login ke Sistem</h4>
                            <ol>
                                <li>Kunjungi website JDIH Kota Padang</li>
                                <li>Klik tombol "Admin Panel" di pojok kanan atas</li>
                                <li>Masukkan username dan password Anda</li>
                                <li>Klik "Masuk" untuk mengakses dashboard</li>
                            </ol>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Langkah 2: Akses Menu Harmonisasi</h4>
                            <ol>
                                <li>Setelah login, Anda akan diarahkan ke dashboard</li>
                                <li>Klik menu "Harmonisasi" di sidebar kiri</li>
                                <li>Pilih "Dashboard Harmonisasi" untuk melihat ajuan yang ada</li>
                                <li>Klik tombol "Tambah Ajuan Baru" untuk membuat pengajuan baru</li>
                            </ol>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Langkah 3: Mengisi Form Pengajuan</h4>
                            <ol>
                                <li><strong>Judul Rancangan Peraturan:</strong> Masukkan judul yang jelas dan mencerminkan isi peraturan (minimal 10 karakter)</li>
                                <li><strong>Jenis Peraturan:</strong> Pilih jenis peraturan yang sesuai dari dropdown</li>
                                <li><strong>Upload Draft Peraturan:</strong> Pilih file draft dalam format PDF, DOC, atau DOCX (maksimal 25MB)</li>
                                <li><strong>Keterangan Tambahan:</strong> Jelaskan konteks, latar belakang, dan tujuan peraturan (opsional)</li>
                            </ol>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Langkah 4: Submit Pengajuan</h4>
                            <ol>
                                <li>Periksa kembali semua data yang telah diisi</li>
                                <li>Pastikan file draft telah terupload dengan benar</li>
                                <li>Klik tombol "Kirim Pengajuan"</li>
                                <li>Sistem akan menampilkan konfirmasi pengajuan</li>
                                <li>Status ajuan akan berubah menjadi "Draft"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Status dan Tracking -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="status-tracking">4. Status Pengajuan dan Tracking</h3>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                        <th>Aksi yang Dapat Dilakukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-secondary">Draft</span></td>
                                        <td>Ajuan baru dibuat, belum diajukan</td>
                                        <td>Edit, Submit, Hapus</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">Diajukan</span></td>
                                        <td>Ajuan telah disubmit ke sistem</td>
                                        <td>Lihat Detail</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-info">Verifikasi</span></td>
                                        <td>Sedang dalam proses verifikasi</td>
                                        <td>Lihat Detail</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary">Validasi</span></td>
                                        <td>Sedang dalam proses validasi</td>
                                        <td>Lihat Detail</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">Revisi</span></td>
                                        <td>Perlu revisi berdasarkan feedback</td>
                                        <td>Upload Revisi</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">Selesai</span></td>
                                        <td>Proses harmonisasi selesai</td>
                                        <td>Lihat Detail</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-danger">Ditolak</span></td>
                                        <td>Ajuan ditolak</td>
                                        <td>Lihat Detail</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Proses Revisi -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="proses-revisi">5. Proses Revisi</h3>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Ketika Ajuan Memerlukan Revisi</h4>
                            <ol>
                                <li>Status ajuan akan berubah menjadi "Revisi"</li>
                                <li>Anda akan menerima notifikasi melalui sistem</li>
                                <li>Klik tombol "Upload Revisi" pada detail ajuan</li>
                                <li>Upload dokumen revisi dalam format DOC atau DOCX</li>
                                <li>Tambahkan keterangan revisi yang dilakukan</li>
                                <li>Submit revisi untuk diproses kembali</li>
                            </ol>
                        </div>

                        <div class="alert alert-warning">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Penting:</strong> Pastikan dokumen revisi sudah sesuai dengan feedback yang diberikan oleh verifikator. Revisi yang tidak sesuai dapat menyebabkan penolakan ajuan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tips dan Best Practices -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="tips">6. Tips dan Best Practices</h3>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Persiapan Dokumen</h4>
                            <ul>
                                <li>Pastikan draft peraturan sudah final dan lengkap</li>
                                <li>Periksa format dokumen (PDF, DOC, DOCX)</li>
                                <li>Pastikan ukuran file tidak melebihi 25MB</li>
                                <li>Periksa ejaan dan tata bahasa</li>
                                <li>Pastikan nomor pasal dan ayat sudah benar</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Judul Peraturan</h4>
                            <ul>
                                <li>Gunakan judul yang jelas dan spesifik</li>
                                <li>Hindari judul yang terlalu panjang atau ambigu</li>
                                <li>Pastikan judul mencerminkan isi peraturan</li>
                                <li>Gunakan bahasa yang formal dan baku</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h4 class="h6 mb-2">Keterangan Tambahan</h4>
                            <ul>
                                <li>Jelaskan latar belakang dan urgensi peraturan</li>
                                <li>Sebutkan peraturan yang menjadi dasar hukum</li>
                                <li>Jelaskan dampak yang diharapkan</li>
                                <li>Mention stakeholder yang terlibat</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Troubleshooting -->
                    <div class="mb-5">
                        <h3 class="h5 mb-3" id="troubleshooting">7. Troubleshooting</h3>

                        <div class="accordion" id="accordionTroubleshooting">
                            <div class="accordion-item border-0 mb-3">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        File tidak bisa diupload
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Pastikan format file adalah PDF, DOC, atau DOCX</li>
                                            <li>Periksa ukuran file tidak melebihi 25MB</li>
                                            <li>Pastikan file tidak rusak atau terenkripsi</li>
                                            <li>Coba upload file yang berbeda untuk testing</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-3">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Form tidak bisa disubmit
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Pastikan semua field wajib sudah diisi</li>
                                            <li>Periksa koneksi internet Anda</li>
                                            <li>Coba refresh halaman dan isi ulang form</li>
                                            <li>Pastikan session login masih aktif</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-3">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Tidak bisa mengakses menu harmonisasi
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Pastikan akun Anda memiliki akses ke modul harmonisasi</li>
                                            <li>Hubungi administrator untuk memverifikasi role Anda</li>
                                            <li>Pastikan akun Anda terdaftar sebagai perwakilan instansi</li>
                                            <li>Logout dan login kembali untuk refresh session</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        Ajuan ditolak tanpa alasan jelas
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionTroubleshooting">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Periksa detail ajuan untuk melihat catatan penolakan</li>
                                            <li>Hubungi verifikator atau administrator untuk klarifikasi</li>
                                            <li>Periksa apakah ada dokumen pendukung yang kurang</li>
                                            <li>Pastikan draft peraturan sudah sesuai dengan standar yang berlaku</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Navigation -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Navigasi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#pengenalan" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Pengenalan Harmonisasi
                        </a>
                        <a href="#persyaratan" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-clipboard-list text-primary me-2"></i>
                            Persyaratan Pengajuan
                        </a>
                        <a href="#langkah-pengajuan" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-route text-primary me-2"></i>
                            Langkah-langkah Pengajuan
                        </a>
                        <a href="#status-tracking" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Status dan Tracking
                        </a>
                        <a href="#proses-revisi" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-edit text-primary me-2"></i>
                            Proses Revisi
                        </a>
                        <a href="#tips" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-lightbulb text-primary me-2"></i>
                            Tips dan Best Practices
                        </a>
                        <a href="#troubleshooting" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-tools text-primary me-2"></i>
                            Troubleshooting
                        </a>
                    </div>
                </div>
            </div>

            <!-- Video Tutorial Harmonisasi -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Video Tutorial Harmonisasi</h5>
                </div>
                <div class="card-body">
                    <div class="ratio ratio-16x9 mb-3">
                        <iframe src="https://www.youtube.com/embed/rSlo82v7-8g?si=zPfq4GXsOP5m_6ic" title="Tutorial Harmonisasi" allowfullscreen></iframe>
                    </div>
                    <h6 class="mb-2">Tutorial Harmonisasi Peraturan</h6>
                    <p class="text-muted small">Video tutorial lengkap tentang proses harmonisasi peraturan daerah.</p>
                    <hr>
                    <div class="list-group list-group-flush small">
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Pengenalan Harmonisasi
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Cara Mengajukan Draft
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Proses Verifikasi
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="fas fa-play-circle text-danger me-2"></i>
                            Upload Revisi
                        </a>
                    </div>
                </div>
            </div>

            <!-- Download Template -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Download Template</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-word text-primary fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Template Draft Peraturan</h6>
                            <p class="mb-2 small">Template standar untuk penyusunan draft peraturan daerah.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Unduh DOCX
                            </a>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-pdf text-danger fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Panduan Penyusunan</h6>
                            <p class="mb-2 small">Panduan lengkap penyusunan peraturan daerah.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Unduh PDF
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-excel text-success fs-3"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Checklist Harmonisasi</h6>
                            <p class="mb-2 small">Checklist untuk memastikan kelengkapan dokumen.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Unduh XLSX
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak Harmonisasi -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Kontak Harmonisasi</h5>
                </div>
                <div class="card-body">
                    <p>Untuk bantuan terkait harmonisasi peraturan:</p>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-tie text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Tim Harmonisasi</h6>
                                <p class="mb-0 small">Bagian Hukum Setda Kota Padang</p>
                            </div>
                        </li>
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
                                <p class="mb-0 small">081169112112</p>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Jam Kerja</h6>
                                <p class="mb-0 small">Senin-Jumat, 08.00-16.00 WIB</p>
                            </div>
                        </li>
                    </ul>
                    <hr>
                    <a href="<?= base_url('harmonisasi') ?>" class="btn btn-primary w-100">
                        <i class="fas fa-external-link-alt me-1"></i> Akses Sistem Harmonisasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Smooth scrolling for anchor links */
    html {
        scroll-behavior: smooth;
    }

    /* Enhanced card styling */
    .card {
        border-radius: 0.75rem;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }

    /* Enhanced accordion styling */
    .accordion-button:not(.collapsed) {
        background-color: #e7f3ff;
        color: #0d6efd;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Enhanced table styling */
    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    /* Enhanced list group styling */
    .list-group-item-action:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    /* Enhanced button styling */
    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>

<script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add active class to current section in navigation
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('h3[id]');
        const navLinks = document.querySelectorAll('.list-group-item-action');

        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 100) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
</script>

<?= $this->endSection() ?>