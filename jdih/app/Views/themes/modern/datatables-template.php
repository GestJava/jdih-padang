<?php

/**
 * Template Standar DataTable untuk JDIH Admin Panel
 * 
 * Penggunaan:
 * 1. Include template ini di view
 * 2. Set variabel $columns, $settings, $title, $add_url
 * 3. Template akan otomatis generate DataTable yang konsisten
 */

// Default values jika tidak diset
$title = $title ?? 'Data List';
$add_url = $add_url ?? current_url() . '/add';
$reload_action = $reload_action ?? 'reload-table';
$table_id = $table_id ?? 'table-result';

// Default settings jika tidak diset
if (!isset($settings)) {
    $settings = [
        'order' => [1, 'asc'],
        'columnDefs' => []
    ];
}

// Generate table headers
$th = '';
foreach ($columns as $val) {
    $th .= '<th>' . $val . '</th>';
}

// Generate DataTable columns
$column_dt = [];
$index = 0;
foreach ($columns as $key => $val) {
    $column_dt[] = ['data' => $key];
    if (strpos($key, 'ignore') !== false) {
        $settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
    }
    $index++;
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= $title ?></h5>
    </div>
    <div class="card-body">
        <?php if (!empty($message)): ?>
            <?= show_alert($message) ?>
        <?php endif; ?>

        <?php if (isset($buttons)): ?>
            <?php foreach ($buttons as $button): ?>
                <a href="<?= $button['url'] ?>" class="btn btn-<?= $button['class'] ?? 'success' ?> btn-xs">
                    <i class="<?= $button['icon'] ?> pe-1"></i> <?= $button['label'] ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <a href="<?= $add_url ?>" class="btn btn-success btn-xs">
                <i class="fas fa-plus pe-1"></i> Tambah Data
            </a>
        <?php endif; ?>

        <hr />

        <div class="table-responsive">
            <table id="<?= $table_id ?>" class="table display nowrap table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <?= $th ?>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <?= $th ?>
                    </tr>
                </tfoot>
            </table>

            <span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
            <span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
            <span id="dataTables-url" style="display:none"><?= current_url() . '/getDataDT' ?></span>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Set CSRF token untuk AJAX requests
        window.csrf_token_name = '<?= csrf_token() ?>';
        window.csrf_token_value = '<?= csrf_hash() ?>';

        console.log('📋 DataTable Template: Page loaded with standard DataTable support');
    });
</script>

<style>
    /* Standard DataTable styling */
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        border-bottom: none;
        background-color: #f8f9fa;
    }

    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        font-weight: 600;
        color: #495057;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* DataTable specific styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        margin: 0.5rem 0;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.25rem 0.5rem;
        margin: 0 0.125rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff;
        color: white !important;
        border-color: #007bff;
    }
</style>