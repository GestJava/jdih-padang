<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>

<?= $this->include('frontend/components/hero', [
    'title' => 'Kebijakan Privasi',
    'subtitle' => 'Kebijakan pengelolaan dan perlindungan data pada situs JDIH',
    'icon' => 'fa-shield-alt',
    'badge' => 'Kebijakan Privasi'
]) ?>

<!-- Content Section -->
<div class="container py-5">
    <?= $this->include('frontend/components/breadcrumb', [
        'items' => [
            ['label' => 'Kebijakan Privasi', 'url' => '']
        ]
    ]) ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <article>
                        <h2 class="h4 mb-4">Pendahuluan</h2>
                        <p>Jaringan Dokumentasi dan Informasi Hukum (JDIH) berkomitmen untuk melindungi privasi pengguna. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, mengungkapkan, dan melindungi informasi pribadi Anda saat Anda menggunakan situs web, aplikasi, atau layanan kami.</p>
                        <p>Dengan mengakses atau menggunakan layanan kami, Anda menyetujui praktik yang dijelaskan dalam Kebijakan Privasi ini. Jika Anda tidak setuju dengan Kebijakan Privasi ini, harap tidak menggunakan layanan kami.</p>

                        <h2 class="h4 mt-5 mb-4">Informasi yang Kami Kumpulkan</h2>
                        <p>Kami dapat mengumpulkan jenis informasi berikut:</p>

                        <h3 class="h5 mt-4 mb-3">1. Informasi Pribadi</h3>
                        <p>Informasi yang dapat mengidentifikasi Anda secara pribadi, seperti:</p>
                        <ul>
                            <li>Nama lengkap</li>
                            <li>Alamat email</li>
                            <li>Nomor telepon</li>
                            <li>Alamat surat</li>
                            <li>Informasi kontak lainnya</li>
                        </ul>

                        <h3 class="h5 mt-4 mb-3">2. Informasi Non-Pribadi</h3>
                        <p>Informasi yang tidak mengidentifikasi Anda secara pribadi, seperti:</p>
                        <ul>
                            <li>Data demografis</li>
                            <li>Data penggunaan dan analitik</li>
                            <li>Alamat IP</li>
                            <li>Informasi browser</li>
                            <li>Informasi perangkat</li>
                        </ul>

                        <h2 class="h4 mt-5 mb-4">Bagaimana Kami Menggunakan Informasi Anda</h2>
                        <p>Kami dapat menggunakan informasi yang kami kumpulkan untuk tujuan berikut:</p>
                        <ul>
                            <li>Menyediakan, memelihara, dan meningkatkan layanan kami</li>
                            <li>Memproses permintaan dan menjawab pertanyaan Anda</li>
                            <li>Mengirimkan pemberitahuan, pembaruan, dan informasi terkait layanan</li>
                            <li>Memantau dan menganalisis tren, penggunaan, dan aktivitas</li>
                            <li>Mencegah, mendeteksi, dan mengatasi masalah teknis, keamanan, atau penipuan</li>
                            <li>Memenuhi kewajiban hukum kami</li>
                        </ul>

                        <h2 class="h4 mt-5 mb-4">Penyimpanan dan Perlindungan Data</h2>
                        <p>Kami mengimplementasikan langkah-langkah keamanan yang sesuai untuk melindungi data Anda terhadap akses, penggunaan, atau pengungkapan yang tidak sah. Namun, tidak ada metode transmisi melalui internet atau metode penyimpanan elektronik yang 100% aman. Oleh karena itu, meskipun kami berusaha menggunakan praktik yang dapat diterima secara komersial untuk melindungi informasi pribadi Anda, kami tidak dapat menjamin keamanan absolutnya.</p>

                        <h2 class="h4 mt-5 mb-4">Pengungkapan kepada Pihak Ketiga</h2>
                        <p>Kami dapat membagikan informasi Anda dengan pihak ketiga dalam situasi berikut:</p>
                        <ul>
                            <li>Jika diharuskan oleh hukum atau dalam menanggapi permintaan hukum yang sah</li>
                            <li>Untuk melindungi hak, properti, atau keselamatan kami, pengguna kami, atau publik</li>
                            <li>Dengan penyedia layanan yang membantu kami menjalankan situs web dan layanan kami</li>
                            <li>Dalam kaitannya dengan, atau selama negosiasi, merger, penjualan aset, pembiayaan, atau akuisisi seluruh atau sebagian bisnis kami oleh perusahaan lain</li>
                        </ul>

                        <h2 class="h4 mt-5 mb-4">Cookie dan Teknologi Pelacakan</h2>
                        <p>Kami menggunakan cookie dan teknologi pelacakan serupa untuk mengumpulkan dan melacak informasi serta untuk meningkatkan dan menganalisis layanan kami. Anda dapat mengatur browser Anda untuk menolak semua cookie atau untuk menunjukkan kapan cookie dikirim. Namun, jika Anda tidak menerima cookie, Anda mungkin tidak dapat menggunakan beberapa bagian dari layanan kami.</p>

                        <h2 class="h4 mt-5 mb-4">Hak Privasi Anda</h2>
                        <p>Tergantung pada lokasi Anda, Anda mungkin memiliki hak tertentu terkait dengan informasi pribadi Anda, termasuk:</p>
                        <ul>
                            <li>Hak untuk mengakses informasi pribadi yang kami miliki tentang Anda</li>
                            <li>Hak untuk meminta perbaikan atau pembaruan informasi yang tidak akurat atau tidak lengkap</li>
                            <li>Hak untuk meminta penghapusan informasi pribadi Anda</li>
                            <li>Hak untuk menolak pemrosesan informasi pribadi Anda</li>
                            <li>Hak untuk meminta pembatasan penggunaan dan pengungkapan informasi pribadi Anda</li>
                            <li>Hak untuk menarik persetujuan Anda untuk penggunaan informasi pribadi Anda</li>
                        </ul>

                        <h2 class="h4 mt-5 mb-4">Perubahan pada Kebijakan Privasi Ini</h2>
                        <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu untuk mencerminkan perubahan pada praktik kami atau untuk alasan operasional, hukum, atau peraturan lainnya. Kami akan memberi tahu Anda tentang perubahan apa pun dengan memposting Kebijakan Privasi baru di situs web kami. Perubahan tersebut akan berlaku segera setelah diposting.</p>

                        <h2 class="h4 mt-5 mb-4">Kontak Kami</h2>
                        <p>Jika Anda memiliki pertanyaan atau kekhawatiran tentang Kebijakan Privasi ini atau praktik privasi kami, silakan hubungi kami di:</p>
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
                        <a href="#" class="list-group-item list-group-item-action">Kebijakan Privasi</a>
                        <a href="<?= base_url('syarat-ketentuan') ?>" class="list-group-item list-group-item-action">Syarat & Ketentuan</a>
                        <a href="<?= base_url('panduan') ?>" class="list-group-item list-group-item-action">Panduan Pengguna</a>
                        <a href="<?= base_url('kontak') ?>" class="list-group-item list-group-item-action">Kontak Kami</a>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Pertanyaan Umum</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionFaq">
                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Bagaimana data saya dilindungi?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Kami menggunakan enkripsi dan langkah-langkah keamanan lainnya untuk melindungi data Anda dari akses yang tidak sah.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Apakah data saya dibagikan dengan pihak ketiga?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Kami hanya membagikan data Anda dalam situasi tertentu seperti yang dijelaskan dalam kebijakan privasi di atas.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Bagaimana cara menghapus akun saya?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFaq">
                                <div class="accordion-body">
                                    Untuk menghapus akun Anda, silakan hubungi tim dukungan kami melalui halaman kontak.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>