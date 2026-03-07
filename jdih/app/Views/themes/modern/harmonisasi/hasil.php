<?php /*
Halaman hasil.php: Tabel ajuan status SELESAI & DITOLAK dengan fitur pencarian DataTables
*/ ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-table me-2"></i><?= esc($title) ?>
        </h1>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Daftar Ajuan Selesai & Ditolak
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="hasil-table" class="table table-striped table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Judul Rancangan</th>
                            <th>Jenis</th>
                            <th>Instansi Pemohon</th>
                            <th>Tgl. Pengajuan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan diisi oleh DataTables via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- 
    Catatan: DataTable untuk #hasil-table diinisialisasi oleh harmonisasi-complete.js
    Tidak perlu inisialisasi manual di sini untuk menghindari konflik
-->