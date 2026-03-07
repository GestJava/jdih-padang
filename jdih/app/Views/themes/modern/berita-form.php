<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= $title ?></h5>
    </div>
    <div class="card-body">
        <?php
        if (!empty($message)) {
            show_alert($message);
        }
        ?>
        <form method="post" action="" class="form-container" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Berita</label>
                        <input type="text" class="form-control" id="judul" name="judul" value="<?= esc($berita['judul'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="isi_berita" class="form-label">Isi Berita</label>
                        <textarea class="form-control tinymce" id="isi_berita" name="isi_berita" rows="10"><?= esc($berita['isi_berita'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select select2" id="kategori_id" name="kategori_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($kategori_list as $kategori) : ?>
                                <option value="<?= $kategori['id'] ?>" <?= ($berita['kategori_id'] ?? '') == $kategori['id'] ? 'selected' : '' ?>>
                                    <?= esc($kategori['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_publish" class="form-label">Tanggal Publish</label>
                        <input type="text" class="form-control flatpickr" id="tanggal_publish" name="tanggal_publish" value="<?= esc($berita['tanggal_publish'] ?? date('Y-m-d H:i:s')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <?php
                            $status_options = ['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'];
                            foreach ($status_options as $key => $value) {
                                $selected = ($berita['status'] ?? 'draft') == $key ? 'selected' : '';
                                echo "<option value='{$key}' {$selected}>{$value}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Unggulan</label>
                        <input class="form-control" type="file" id="gambar" name="gambar">
                        <?php if (!empty($berita['gambar'])) : ?>
                            <div class="mt-2">
                                <img src="<?= base_url('uploads/berita/' . $berita['gambar']) ?>" style="width:150px;height:auto;">
                                <p><small>Gambar saat ini. Upload baru untuk mengganti.</small></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <input type="hidden" name="id" value="<?= esc($berita['id'] ?? '') ?>" />
                    <button type="submit" name="submit" value="submit" class="btn btn-primary">Simpan</button>
                    <a href="<?= base_url('artikel_hukum') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '.tinymce',
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | image link',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            height: 400
        });
    });
</script>