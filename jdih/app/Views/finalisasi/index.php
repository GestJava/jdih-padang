<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= $title; ?></h5>
    </div>
    <div class="card-body">

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success" role="alert">
                <?= session()->getFlashdata('success'); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Ajuan Menunggu Finalisasi</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul Rancangan</th>
                                <th>Pemrakarsa</th>
                                <th>Status</th>
                                <th>Tanggal Diterima</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ajuan)) : ?>
                                <?php $i = 1; ?>
                                <?php foreach ($ajuan as $item) : ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= esc($item['judul']); ?></td>
                                        <td><?= esc($item['nama_instansi']); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= esc($item['nama_status']); ?></span>
                                        </td>
                                        <td><?= date('d F Y H:i', strtotime($item['updated_at'])); ?></td>
                                        <td>
                                            <?php $ajuan_id = isset($item['id_ajuan']) ? $item['id_ajuan'] : (isset($item['id']) ? $item['id'] : ''); ?>
                                            <a href="<?= base_url('finalisasi/proses/' . $ajuan_id); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-tasks"></i> Proses
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada ajuan yang perlu difinalisasi saat ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>