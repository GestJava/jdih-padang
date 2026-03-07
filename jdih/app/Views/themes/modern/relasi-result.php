<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= esc($current_module['judul_module']) ?></h5>
	</div>

	<div class="card-body">
		<!-- Tombol Kembali -->
		<a href="javascript:history.back()" class="btn btn-success btn-xs mb-3">
			<i class="fa fa-arrow-circle-left"></i> Kembali
		</a>

		<?php
		helper('html');
		$id_peraturan = (int) service('request')->getGet('id');
		?>

		<!-- Form Tambah Relasi -->
		<div class="card mb-4">
			<div class="card-header">
				<h6 class="card-title">Tambah Relasi Peraturan</h6>
			</div>
			<div class="card-body">
				<?= form_open('data_peraturan/save_relasi', ['id' => 'form-relasi']) ?>
				<?= csrf_field() ?>
				<input type="hidden" name="id_peraturan" value="<?= esc($id_peraturan) ?>">

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Jenis Relasi</label>
					<div class="col-sm-9">
						<select name="id_jenis_relasi" id="id_jenis_relasi" class="form-select" required>
							<option value="">Pilih Jenis Relasi</option>
							<?php if (isset($jenis_relasi_options)): ?>
								<?php foreach ($jenis_relasi_options as $id => $nama): ?>
									<option value="<?= esc($id) ?>"><?= esc($nama) ?></option>
								<?php endforeach ?>
							<?php endif ?>
						</select>
						<small class="form-text text-muted" id="jenis-relasi-info"></small>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Peraturan Terkait</label>
					<div class="col-sm-9">
						<select name="id_peraturan_terkait" class="form-control select2-peraturan" required>
							<option value="">Pilih Peraturan Terkait</option>
						</select>
						<div class="invalid-feedback" id="select2-error-message"></div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Keterangan</label>
					<div class="col-sm-9">
						<textarea name="keterangan" class="form-control" rows="2" placeholder="Tambahkan keterangan jika diperlukan (opsional)"></textarea>
					</div>
				</div>

				<!-- Warning untuk auto-update -->
				<div class="alert alert-warning" id="auto-update-warning" style="display: none;">
					<i class="fa fa-exclamation-triangle"></i>
					<strong>Perhatian:</strong> Jenis relasi ini akan mengubah status peraturan terkait secara otomatis menjadi "Tidak Berlaku".
				</div>

				<div class="row">
					<div class="col-sm-9 offset-sm-3">
						<button type="submit" class="btn btn-primary" id="btn-submit-relasi">
							<i class="fa fa-save"></i> Simpan Relasi
						</button>
					</div>
				</div>
				<?= form_close() ?>
			</div>
		</div>

		<!-- Tabel Relasi -->
		<?php if (!$result): ?>
			<?= show_message('Data tidak ditemukan', 'error', false) ?>
		<?php else: ?>
			<?php if (!empty($message)): ?>
				<?= show_alert($message) ?>
			<?php endif; ?>

			<div class="table-responsive">
				<?php
				$column = [
					'ignore_no_urut' => 'No.',
					'peraturan_sumber' => 'Peraturan Sumber',
					'peraturan_terkait' => 'Peraturan Terkait',
					'jenis_relasi' => 'Jenis Relasi',
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
				<span id="dataTables-url" style="display:none"><?= base_url('data_peraturan/getRelasiDataDT?id_peraturan=' . $id_peraturan) ?></span>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
	$(document).ready(function() {
		// Inisialisasi Select2 untuk peraturan terkait
		const idSumber = $('input[name="id_peraturan"]').val();

		$('.select2-peraturan').select2({
			ajax: {
				url: '<?= base_url('data_peraturan/ajaxSearchPeraturan') ?>',
				dataType: 'json',
				delay: 300,
				data: function(params) {
					return {
						q: params.term,
						id_sumber: idSumber
					};
				},
				processResults: function(data) {
					// Handle error response
					if (data.error) {
						$('#select2-error-message').text(data.message || 'Terjadi kesalahan saat memuat data');
						return {
							results: []
						};
					}

					// Clear error message
					$('#select2-error-message').text('');

					return {
						results: data.results || []
					};
				},
				cache: true,
				error: function(xhr, status, error) {
					console.error('AJAX Error:', error);
					$('#select2-error-message').text('Gagal memuat data: ' + error);
				}
			},
			placeholder: 'Ketik judul, nomor, atau tahun peraturan...',
			minimumInputLength: 3,
			language: {
				inputTooShort: function() {
					return "Ketik minimal 3 karakter untuk mencari peraturan";
				},
				searching: function() {
					return "Mencari...";
				},
				noResults: function() {
					return "Tidak ada hasil yang ditemukan";
				},
				errorLoading: function() {
					return "Gagal memuat hasil";
				}
			}
		});

		// Konfirmasi hapus relasi
		$('.btn-delete-relasi').on('click', function(e) {
			e.preventDefault();
			const href = $(this).attr('href');
			const confirmText = $(this).data('confirm');

			if (confirm(confirmText)) {
				window.location.href = href;
			}
		});

		// Enhanced: Jenis relasi change handler
		$('#id_jenis_relasi').on('change', function() {
			const jenisRelasiId = $(this).val();

			if (!jenisRelasiId) {
				$('#jenis-relasi-info').text('');
				$('#auto-update-warning').hide();
				return;
			}

			// Get jenis relasi info via AJAX
			$.ajax({
				url: '<?= base_url('data_peraturan/ajaxGetJenisRelasiInfo') ?>',
				type: 'GET',
				data: {
					id: jenisRelasiId
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						const data = response.data;

						// Update info text
						let infoText = data.deskripsi || '';
						$('#jenis-relasi-info').text(infoText);

						// Show/hide warning
						if (data.auto_update_status) {
							$('#auto-update-warning').show();
						} else {
							$('#auto-update-warning').hide();
						}
					}
				},
				error: function() {
					$('#jenis-relasi-info').text('');
					$('#auto-update-warning').hide();
				}
			});
		});

		// Enhanced form validation with confirmation
		$('#form-relasi').on('submit', function(e) {
			const jenisRelasi = $('select[name="id_jenis_relasi"]').val();
			const peraturanTerkait = $('select[name="id_peraturan_terkait"]').val();

			if (!jenisRelasi || !peraturanTerkait) {
				e.preventDefault();
				alert('Harap lengkapi semua field yang diperlukan');
				return false;
			}

			// Konfirmasi untuk auto-update status
			const isAutoUpdate = $('#auto-update-warning').is(':visible');
			if (isAutoUpdate) {
				if (!confirm('Jenis relasi ini akan mengubah status peraturan terkait secara otomatis. Apakah Anda yakin ingin melanjutkan?')) {
					e.preventDefault();
					return false;
				}
			}

			// Disable button to prevent double submit
			$('#btn-submit-relasi').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
		});

		// Inisialisasi DataTable untuk tabel relasi
		if ($('#table-result').length) {
			const columns = JSON.parse($('#dataTables-column').text());
			const settings = JSON.parse($('#dataTables-setting').text());
			const url = $('#dataTables-url').text();

			$('#table-result').DataTable({
				processing: settings.processing,
				serverSide: settings.serverSide,
				ajax: {
					url: url,
					type: 'GET',
					error: function(xhr, error, thrown) {
						console.error('DataTable Error:', error);
						alert('Terjadi kesalahan saat memuat data relasi');
					}
				},
				columns: columns,
				order: settings.order,
				pageLength: settings.pageLength,
				columnDefs: settings.columnDefs || [],
				language: {
					processing: "Memproses...",
					search: "Cari:",
					lengthMenu: "Tampilkan _MENU_ data per halaman",
					info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
					infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
					infoFiltered: "(difilter dari _MAX_ total data)",
					infoPostFix: "",
					loadingRecords: "Memuat...",
					zeroRecords: "Tidak ada data yang ditemukan",
					emptyTable: "Tidak ada data yang tersedia",
					paginate: {
						first: "Pertama",
						previous: "Sebelumnya",
						next: "Selanjutnya",
						last: "Terakhir"
					},
					aria: {
						sortAscending: ": aktifkan untuk mengurutkan kolom naik",
						sortDescending: ": aktifkan untuk mengurutkan kolom turun"
					}
				}
			});
		}
	});
</script>