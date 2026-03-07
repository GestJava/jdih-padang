<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= $title ?></h5>
    </div>
    <div class="card-body">
        <?php
        if (!empty($message)) {
            show_alert($message);
        } ?>
        <a href="<?= $config->baseURL ?>artikel_hukum/add" class="btn btn-success btn-xs"><i class="fas fa-plus pe-1"></i> Tambah Data</a>
        <hr />

        <div class="table-responsive">
            <table id="table-result" class="table table-bordered">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Gambar</th>
                        <th>Judul Berita</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#table-result').DataTable({
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": "<?= base_url('artikel_hukum/getDataDT') ?>",
                "type": "GET"
            }
        });
    });
</script>