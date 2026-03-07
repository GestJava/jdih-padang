<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= $title ?></h5>
    </div>
    <div class="card-body">
        <?php if (!empty($message)):
            echo show_alert($message);
        endif; ?>

        <!-- Form Tambah Lampiran -->
        <form action="<?= base_url('data_peraturan/save_lampiran') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id_peraturan" value="<?= $peraturan['id_peraturan'] ?>">
            <div class="row mb-3">
                <label for="judul_lampiran" class="col-sm-3 col-form-label">Judul Lampiran</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="judul_lampiran" name="judul_lampiran" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="file_lampiran" class="col-sm-3 col-form-label">File Lampiran</label>
                <div class="col-sm-9">
                    <input type="file" class="form-control" id="file_lampiran" name="file_lampiran" required>
                    <small class="text-muted">Tipe file yang diizinkan: pdf, doc, docx, jpg, png. Ukuran maks: 10MB.</small>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary">Simpan Lampiran</button>
                </div>
            </div>
        </form>
        <hr>

        <!-- Daftar Lampiran -->
        <h5>Daftar Lampiran</h5>
        <?php if (empty($lampiran)):
            echo show_message('Belum ada lampiran untuk peraturan ini.', 'info', false);
        else:
        ?>
            <div class="table-responsive">
                <?php
                $column = [
                    'ignore_no_urut' => 'No.',
                    'judul_lampiran' => 'Judul Lampiran',
                    'file_lampiran' => 'File',
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
                <span id="dataTables-url" style="display:none"><?= base_url('data_peraturan/getLampiranDataDT') ?></span>
            </div>
        <?php endif; ?>

        <hr>
        <a href="<?= base_url('data_peraturan') ?>" class="btn btn-secondary">Kembali ke Daftar Peraturan</a>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($('#table-result').length) {
            const columns = JSON.parse($('#dataTables-column').text());

            // Coba dengan static data dulu untuk memastikan DataTable berfungsi
            $('#table-result').DataTable({
                processing: false,
                serverSide: false,
                data: [], // Empty data untuk testing
                columns: columns,
                order: [
                    [1, 'asc']
                ],
                pageLength: 25,
                columnDefs: [{
                        targets: 0,
                        orderable: false
                    },
                    {
                        targets: 3,
                        orderable: false
                    }
                ],
                language: {
                    processing: "Memproses...",
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    loadingRecords: "Memuat...",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Tidak ada data yang tersedia",
                    paginate: {
                        first: "Pertama",
                        previous: "Sebelumnya",
                        next: "Selanjutnya",
                        last: "Terakhir"
                    }
                }
            });

            // Setelah DataTable terinisialisasi, load data via AJAX manual
            loadLampiranData();
        }
    });

    function loadLampiranData() {
        const url = $('#dataTables-url').text();
        const id_peraturan = '<?= $peraturan['id_peraturan'] ?>';

        console.log('Loading data from:', url);
        console.log('ID Peraturan:', id_peraturan);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            timeout: 10000, // 10 second timeout
            data: {
                id_peraturan: id_peraturan
            },
            beforeSend: function() {
                console.log('Sending AJAX request with id_peraturan:', id_peraturan);
            },
            success: function(response) {
                console.log('AJAX Success - Raw response:', response);

                if (response && response.data && Array.isArray(response.data)) {
                    console.log('Data array found with', response.data.length, 'items');

                    // Decode base64 content dan render HTML
                    const processedData = response.data.map(function(item) {
                        return {
                            ignore_no_urut: item.ignore_no_urut,
                            judul_lampiran: item.judul_lampiran,
                            file_lampiran: atob(item.file_lampiran), // Decode base64
                            ignore_action: atob(item.ignore_action) // Decode base64
                        };
                    });

                    // Clear existing data
                    $('#table-result').DataTable().clear();

                    // Add new data
                    $('#table-result').DataTable().rows.add(processedData).draw();

                    console.log('Data loaded successfully into DataTable');
                } else {
                    console.log('No valid data found in response');
                    console.log('Response structure:', response);
                }
            },
            error: function(xhr, error, thrown) {
                console.error('AJAX Error Details:');
                console.error('- Status:', xhr.status);
                console.error('- StatusText:', xhr.statusText);
                console.error('- Error:', error);
                console.error('- Thrown:', thrown);
                console.error('- ResponseText:', xhr.responseText);

                // Try to parse response as JSON for debugging
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.error('- Parsed Response:', response);
                } catch (e) {
                    console.error('- Could not parse response as JSON');
                }

                // Show user-friendly error
                $('#table-result').html('<div class="alert alert-danger">Gagal memuat data lampiran. Silakan refresh halaman.</div>');
            }
        });
    }
</script>