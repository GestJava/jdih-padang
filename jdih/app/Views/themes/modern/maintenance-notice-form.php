<?php
// Tambah body class untuk JS
if (!empty($body_class)) {
    echo '<script>document.body.className += " ' . esc($body_class) . '";</script>';
}

$action_url = current_url();
$is_edit = !empty($announcement);

// Helper untuk sticky form value
function old_or_value($key, $announcement, $default = '')
{
    if (old($key) !== null) {
        return old($key);
    }
    if (!empty($announcement) && isset($announcement[$key])) {
        return $announcement[$key];
    }
    return $default;
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tools text-warning me-2"></i><?= $is_edit ? 'Edit' : 'Tambah' ?> Pengumuman
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('maintenance-notice') ?>">Pengumuman</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $is_edit ? 'Edit' : 'Tambah' ?></li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($message)) : ?>
        <?= show_alert($message); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-info-circle me-2"></i>Form Pengumuman</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= $action_url ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="title">Judul</label>
                            <input type="text" name="title" id="title" class="form-control" value="<?= esc(old_or_value('title', $announcement)) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="heading">Heading (opsional)</label>
                            <input type="text" name="heading" id="heading" class="form-control" value="<?= esc(old_or_value('heading', $announcement)) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="message">Pesan</label>
                            <textarea name="message" id="message" class="form-control" rows="4" required><?= esc(old_or_value('message', $announcement)) ?></textarea>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label class="form-label" for="contact_name">Kontak Nama</label>
                                <input type="text" name="contact_name" id="contact_name" class="form-control" value="<?= esc(old_or_value('contact_name', $announcement)) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contact_position">Posisi Kontak</label>
                                <input type="text" name="contact_position" id="contact_position" class="form-control" value="<?= esc(old_or_value('contact_position', $announcement)) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="status">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active" <?= old_or_value('status', $announcement, 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                <option value="inactive" <?= old_or_value('status', $announcement, 'active') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <button type="submit" name="submit" value="save" class="btn btn-warning"><i class="fas fa-save me-1"></i>Simpan</button>
                        <a href="<?= base_url('maintenance-notice') ?>" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>