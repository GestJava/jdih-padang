<?php
?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= $title ?></h5>
    </div>
    <div class="card-body">
        <?php if (!empty($message)) show_alert($message); ?>
        <form method="post" enctype="multipart/form-data" action="">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Judul Agenda</label>
                    <input type="text" name="judul_agenda" class="form-control" value="<?= esc($agenda['judul_agenda'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="<?= esc($agenda['tanggal_mulai'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" value="<?= esc($agenda['tanggal_selesai'] ?? '') ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" value="<?= esc($agenda['lokasi'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status_agenda" class="form-select">
                        <?php
                        $status_list = ['Akan Datang', 'Berlangsung', 'Selesai'];
                        $current = $agenda['status_agenda'] ?? 'Akan Datang';
                        foreach ($status_list as $st) {
                            $sel = $st == $current ? 'selected' : '';
                            echo "<option value=\"$st\" $sel>$st</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gambar (opsional)</label>
                    <input type="file" name="gambar_agenda" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Singkat</label>
                <textarea name="deskripsi_singkat" class="form-control" rows="3"><?= esc($agenda['deskripsi_singkat'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Lengkap</label>
                <textarea name="deskripsi_lengkap" class="form-control tinymce" rows="6"><?= esc($agenda['deskripsi_lengkap'] ?? '') ?></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" class="form-control" value="<?= esc($agenda['waktu_mulai'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" class="form-control" value="<?= esc($agenda['waktu_selesai'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Penyelenggara</label>
                    <input type="text" name="penyelenggara" class="form-control" value="<?= esc($agenda['penyelenggara'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Target Peserta</label>
                    <input type="text" name="target_peserta" class="form-control" value="<?= esc($agenda['target_peserta'] ?? '') ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Kontak Person - Nama</label>
                    <input type="text" name="kontak_person_nama" class="form-control" value="<?= esc($agenda['kontak_person_nama'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kontak Person - Email</label>
                    <input type="email" name="kontak_person_email" class="form-control" value="<?= esc($agenda['kontak_person_email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kontak Person - Telepon</label>
                    <input type="text" name="kontak_person_telepon" class="form-control" value="<?= esc($agenda['kontak_person_telepon'] ?? '') ?>">
                </div>
            </div>
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
            <input type="hidden" name="id" value="<?= esc($agenda['id'] ?? '') ?>">
            <button type="submit" name="submit" value="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('data_agenda') ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>