<?php /*
Halaman hasil/index.php: Tabel ajuan status SELESAI & DITOLAK dengan pencarian sederhana (tanpa AJAX)
*/ ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-table me-2"></i><?= esc($title) ?>
        </h1>
    </div>

    <!-- Form Pencarian -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="<?= base_url('hasil') ?>" class="row g-3">
                <div class="col-md-10">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Cari berdasarkan judul, jenis, instansi, atau nama pemohon..." 
                           value="<?= esc($search ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                </div>
                <?php if (!empty($search)): ?>
                <div class="col-12">
                    <a href="<?= base_url('hasil') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Hapus Filter
                    </a>
                    <span class="text-muted ms-2">Menampilkan hasil untuk: <strong><?= esc($search) ?></strong></span>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>Daftar Ajuan Selesai & Ditolak
                <span class="badge bg-light text-dark ms-2"><?= $pagination['total_records'] ?? count($ajuan_list ?? []) ?> ajuan</span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($ajuan_list)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php if (!empty($search)): ?>
                        Tidak ada data yang ditemukan untuk pencarian "<?= esc($search) ?>"
                    <?php else: ?>
                        Belum ada data ajuan dengan status Selesai atau Ditolak
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Judul Rancangan</th>
                                <th width="12%">Jenis</th>
                                <th width="15%">Instansi Pemohon</th>
                                <th width="12%">Tgl. Pengajuan</th>
                                <th width="10%">Status</th>
                                <th width="11%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ajuan_list as $row): ?>
                            <tr>
                                <td class="text-center"><?= $row['no'] ?></td>
                                <td><?= $row['judul_peraturan'] ?></td>
                                <td><?= $row['nama_jenis'] ?></td>
                                <td><?= $row['nama_instansi'] ?></td>
                                <td><?= $row['tanggal_pengajuan'] ?></td>
                                <td class="text-center">
                                    <?php if ($row['id_status_ajuan'] == 14): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('harmonisasi/show/' . $row['id_ajuan']) ?>" 
                                       class="btn btn-outline-info btn-sm" 
                                       title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php 
                                    // Check if 'delete' permission exists using RBA data passed from BaseController
                                    // Also allow if user is strictly 'Administrator' (failsafe)
                                    $can_delete = false;
                                    
                                    // 1. Check RBA
                                    if (isset($action_user) && is_array($action_user)) {
                                        if (in_array('delete', $action_user) || key_exists('delete', $action_user)) {
                                            $can_delete = true;
                                        }
                                    }
                                    
                                    // 2. Check Role (Fallback)
                                    $user_role_str = strtolower($user_role ?? '');
                                    if (!$can_delete && ($user_role_str === 'administrator' || $user_role_str === 'admin')) {
                                        $can_delete = true;
                                    }
                                    ?>
                                    <?php if ($can_delete): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm btn-delete" 
                                                data-id="<?= $row['id_ajuan'] ?>" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <?php if ($pagination['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('hasil?' . http_build_query(array_merge($_GET, ['page' => $pagination['prev_page']]))) ?>">
                                    <i class="fas fa-chevron-left"></i> Sebelumnya
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-left"></i> Sebelumnya</span>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $currentPage = $pagination['current_page'];
                        $totalPages = $pagination['total_pages'];
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        
                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('hasil?' . http_build_query(array_merge($_GET, ['page' => 1]))) ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?= $i ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= base_url('hasil?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('hasil?' . http_build_query(array_merge($_GET, ['page' => $totalPages]))) ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('hasil?' . http_build_query(array_merge($_GET, ['page' => $pagination['next_page']]))) ?>">
                                    Selanjutnya <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Selanjutnya <i class="fas fa-chevron-right"></i></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Pagination Info -->
                    <div class="text-center text-muted mt-2">
                        Menampilkan <?= $pagination['current_page'] * $pagination['per_page'] - $pagination['per_page'] + 1 ?> 
                        sampai <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total_records']) ?> 
                        dari <?= $pagination['total_records'] ?> data
                    </div>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</script>

<!-- SweetAlert2 & Delete Logic -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.btn-delete');
        // Get CSRF Token from meta tag or input if available, otherwise assume default
        // CodeIgniter 4 usually uses 'csrf_test_name' or similar. We can grab it from PHP.
        const csrfName = '<?= csrf_token() ?>';
        const csrfHash = '<?= csrf_hash() ?>';

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Mohon tunggu...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // JSON Payload with CSRF
                        const data = {};
                        data[csrfName] = csrfHash;

                        // Send AJAX request
                        fetch('<?= base_url('hasil/delete') ?>/' + id, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfHash // Also try header for some CI setups
                            },
                             body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire(
                                    'Terhapus!',
                                    data.message,
                                    'success'
                                ).then(() => {
                                    // Reload page to reflect changes
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan sistem/jaringan.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
    });
</script>
