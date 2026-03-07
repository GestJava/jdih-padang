<?php
// Tambah body class untuk deteksi JS
if (!empty($body_class)) {
    echo '<script>document.body.className += " ' . esc($body_class) . '";</script>';
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools text-warning me-2"></i>Pengumuman Maintenance
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pengumuman</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($msg)) : ?>
        <?= show_alert($msg); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-list me-2"></i>Daftar Pengumuman</h6>
                        <div>
                            <button type="button" class="btn btn-light btn-sm me-2" data-action="reload-table" title="Refresh Data">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <a href="<?= base_url('maintenance-notice/add') ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i>Tambah Pengumuman
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php
                        $column = [
                            'ignore_no_urut' => 'No.',
                            'title' => 'Judul',
                            'status' => 'Status',
                            'updated_at' => 'Diupdate',
                            'ignore_action' => 'Aksi'
                        ];
                        $th = '';
                        foreach ($column as $val) {
                            $th .= '<th>' . $val . '</th>';
                        }
                        ?>
                        <table id="table-result" class="table display nowrap table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <?= $th ?>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <?= $th ?>
                            </tfoot>
                        </table>
                        <?php
                        $settings['order'] = [1, 'asc'];
                        $settings['pageLength'] = 25; // Optimal untuk data besar
                        $settings['processing'] = true; // Show processing indicator
                        $settings['serverSide'] = true; // Server-side processing untuk data besar
                        $index = 0;
                        foreach ($column as $key => $val) {
                            $column_dt[] = ['data' => $key];
                            if (strpos($key, 'ignore') !== false) {
                                $settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
                            }
                            $index++;
                        }
                        ?>
                        <span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
                        <span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
                        <span id="dataTables-url" style="display:none"><?= current_url() . '/getDataDT' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        window.csrf_token_name = '<?= csrf_token() ?>';
        window.csrf_token_value = '<?= csrf_hash() ?>';
        console.log('📋 Maintenance Notice: Page loaded with optimized DataTable for large datasets');
    });
</script>