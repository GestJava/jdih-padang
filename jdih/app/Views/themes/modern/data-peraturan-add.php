<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $page_title ?></h5>
	</div>

	<div class="card-body">
		<?php
		helper('html');
		echo btn_link([
			'attr' => ['class' => 'btn btn-success btn-xs mb-3'],
			'url' => base_url($current_module['nama_module'] . '/add'),
			'icon' => 'fa fa-plus',
			'label' => 'Tambah Data'
		]);

		echo btn_link([
			'attr' => ['class' => 'btn btn-light btn-xs mb-3'],
			'url' => base_url($current_module['nama_module']),
			'icon' => 'fa fa-arrow-circle-left',
			'label' => $current_module['judul_module']
		]);
		?>
		<hr />
		<?php

		// Coba ambil pesan dari flashdata (setelah redirect)
		$session_message = session()->getFlashdata('message');

		// Jika tidak ada flashdata, coba ambil dari variabel $message yang di-pass langsung ke view
		// (misalnya saat validasi gagal tanpa redirect)
		// Asumsi $message sudah diekstrak dari $this->data oleh BaseController
		$display_this_message = $session_message ?: ($message ?? null);

		if (!empty($display_this_message) && !empty($display_this_message['message'])) {
			$alert_type = ($display_this_message['status'] == 'success') ? 'success' : 'danger';
			echo '<div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">';
			// Periksa apakah message adalah array dan konversi jika perlu
			if (is_array($display_this_message['message'])) {
				echo implode('<br>', $display_this_message['message']);
			} else {
				echo $display_this_message['message']; // Pesan sudah di-format (misalnya dengan <br>) di controller
			}
			echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			echo '</div>';
		}
		?>
		<?php
		if (!empty($msg['status'])) {
			$alert_type = ($msg['status'] == 'ok' || $msg['status'] == 'success') ? 'success' : 'danger';
			echo '<div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">';
			if (is_array($msg['content'])) {
				echo implode('<br>', $msg['content']);
			} else {
				echo $msg['content'];
			}
			echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			echo '</div>';
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<?= csrf_field() ?>
			<div class="tab-content" id="myTabContent">

				<!-- Jenis Peraturan - Field yang menentukan metadata -->
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Jenis Peraturan <span class="text-danger">*</span></label>
					<div class="col-sm-9">
						<select class="form-control select2" name="id_jenis_dokumen" id="id_jenis_dokumen" required="required">
							<option value="">Pilih Jenis Peraturan</option>
							<?php if (!empty($jenis_dokumen_list) && is_array($jenis_dokumen_list)): ?>
								<?php foreach ($jenis_dokumen_list as $val): ?>
									<option value="<?= esc($val['id_jenis_peraturan']) ?>" <?= set_select('id_jenis_dokumen', esc($val['id_jenis_peraturan']), (isset($peraturan['id_jenis_dokumen']) && $peraturan['id_jenis_dokumen'] == $val['id_jenis_peraturan'])) ?>>
										<?= esc($val['nama_jenis']) ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
						<small class="form-text text-muted">Pilihan ini akan menyesuaikan field yang ditampilkan</small>
					</div>
				</div>

				<!-- Metadata Fields Container -->
				<div id="metadata-fields-container">
					<!-- Field akan dimuat secara dinamis berdasarkan jenis peraturan -->
				</div>

				<!-- Common Fields yang selalu ditampilkan -->
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Judul Peraturan</label>
					<div class="col-sm-9">
						<textarea class="form-control" name="judul" required><?= set_value('judul', @$peraturan['judul']) ?></textarea>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Pemrakarsa/Pelaksana</label>
					<div class="col-sm-9">
						<select class="form-control select2" name="id_instansi" id="id_instansi" required="required">
							<?php if (!empty($peraturan['id_instansi']) && !empty($peraturan['nama_instansi'])): ?>
								<option value="<?= esc($peraturan['id_instansi']) ?>" selected><?= esc($peraturan['nama_instansi']) ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Status</label>
					<div class="col-sm-9">
						<select class="form-control select2" name="id_status" required="required">
							<option value="">Pilih Status</option>
							<?php if (!empty($status_dokumen_list) && is_array($status_dokumen_list)): ?>
								<?php foreach ($status_dokumen_list as $status): ?>
									<option value="<?= esc($status['id']) ?>" <?= set_select('id_status', esc($status['id']), (isset($peraturan['id_status']) && $peraturan['id_status'] == $status['id'])) ?>><?= esc($status['nama_status']) ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Abstrak</label>
					<div class="col-sm-9">
						<textarea class="form-control tinymce" id="abstrak_teks"
							name="abstrak_teks"><?= set_value('abstrak_teks', @$peraturan['abstrak_teks']) ?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Ringkasan</label>
					<div class="col-sm-9">
						<textarea class="form-control tinymce" id="catatan_teks"
							name="catatan_teks"><?= set_value('catatan_teks', @$peraturan['catatan_teks']) ?></textarea>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Subjek/Tag</label>
					<div class="col-sm-9">
						<div class="mb-2">
							<select class="form-control select2" name="tags[]" multiple="multiple" id="select-tags">
								<!-- Options will be loaded dynamically via AJAX -->
								<?php if (!empty($tags) && !empty($selected_tags)): ?>
									<?php foreach ($selected_tags as $selected_tag): ?>
										<option value="<?= $selected_tag['id_tag'] ?>" selected><?= $selected_tag['nama_tag'] ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>
						<div class="input-group mb-2">
							<input type="text" class="form-control" id="new-tag-input" placeholder="Ketik nama tag baru di sini..." autocomplete="off">
							<button class="btn btn-primary" type="button" id="add-new-tag">
								<i class="fas fa-plus-circle me-1"></i> Tambah Tag
							</button>
						</div>
						<div id="tag-feedback" class="mb-2" style="display:none;"></div>
						<small class="text-muted" style="display:block">
							<i class="fas fa-info-circle me-1"></i> Pilih tag yang sudah ada atau tambahkan tag baru jika belum tersedia. Tekan Enter setelah mengetik untuk menambahkan tag baru.
						</small>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">File Peraturan</label>
					<div class="col-sm-9">
						<?php if (!empty($peraturan['file_dokumen'])):
						?>
							<div class="mb-2">
								<p class="mb-1"><strong>File saat ini:</strong></p>
								<a href="<?= base_url('uploads/peraturan/' . $peraturan['file_dokumen']) ?>" target="_blank">
									<i class="fas fa-file-pdf"></i> <?= esc($peraturan['file_dokumen']) ?>
								</a>
							</div>
							<small class="form-text text-muted d-block mb-2">Untuk mengganti, silakan unggah file baru di bawah ini.</small>
						<?php endif; ?>
						<input type="file" class="file form-control" name="file_dokumen" accept="application/pdf" />
						<?php if (!empty($form_errors['file_dokumen'])): ?>
							<div class="alert alert-danger mt-2">
								<i class="fas fa-exclamation-triangle me-1"></i>
								<?= esc($form_errors['file_dokumen']) ?>
							</div>
						<?php endif; ?>
						<small class="small" style="display:block">Ekstensi file harus .pdf</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Status Publikasi</label>
					<div class="col-sm-9">
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="is_published" id="is_published_1" value="1" <?= set_radio('is_published', '1', (isset($peraturan['is_published']) && $peraturan['is_published'] == 1)) ?>>
							<label class="form-check-label" for="is_published_1">Dipublikasikan</label>
						</div>
						<div class="form-check form-check-inline">
							<input class="form-check-input" type="radio" name="is_published" id="is_published_0" value="0" <?= set_radio('is_published', '0', (isset($peraturan['is_published']) && $peraturan['is_published'] == 0) || !isset($peraturan['is_published'])) ?>>
							<label class="form-check-label" for="is_published_0">Draft</label>
						</div>
						<small class="small" style="display:block">Peraturan yang dipublikasikan akan muncul dalam pencarian</small>
					</div>
				</div>
						<button type="submit" name="submit" value="submit" class="btn btn-primary">Simpan</button>
						<input type="hidden" name="id_peraturan" value="<?= esc($peraturan['id_peraturan'] ?? '') ?>" />
						<input type="hidden" name="source_id" value="<?= esc($source_id ?? '') ?>" />
						<input type="hidden" name="source_file" value="<?= esc($source_file ?? '') ?>" />
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Optimized JavaScript - Consolidated and cleaned -->
<script>
	$(document).ready(function() {
		// ======================
		// INITIALIZATION
		// ======================

		// Initialize Select2 for Instansi
		$('#id_instansi').select2({
			placeholder: 'Ketik untuk mencari instansi...',
			minimumInputLength: 1,
			allowClear: true,
			ajax: {
				url: '<?= base_url('data_peraturan/ajaxGetInstansi') ?>',
				dataType: 'json',
				delay: 250,
				processResults: function(data) {
					return {
						results: data
					};
				},
				cache: true
			}
		});

		// Initialize Select2 for Tags
		$('#select-tags').select2({
			placeholder: "Pilih atau ketik untuk mencari tag...",
			allowClear: true,
			minimumInputLength: 0,
			ajax: {
				url: '<?= base_url('api/tags/search') ?>',
				dataType: 'json',
				delay: 250,
				data: function(params) {
					return {
						q: params.term || '',
						page: params.page || 1
					};
				},
				processResults: function(data, params) {
					return {
						results: data.results,
						pagination: data.pagination
					};
				},
				cache: true
			},
			language: {
				errorLoading: () => "Tidak dapat memuat hasil",
				inputTooShort: () => "Ketik untuk mencari tag...",
				noResults: () => "Tag tidak ditemukan. Gunakan tombol 'Tambah Tag' untuk membuat tag baru",
				searching: () => "Mencari...",
				loadingMore: () => "Memuat hasil lainnya..."
			}
		});

		// ======================
		// METADATA FIELD SYSTEM
		// ======================

		// Function untuk mendapatkan template berdasarkan jenis peraturan
		function getMetadataTemplate(jenisPeraturan) {
			// Gunakan API untuk mendapatkan template dari backend
			return $.ajax({
				url: '<?= base_url('data_peraturan/getMetadataTemplate') ?>',
				type: 'GET',
				data: {
					id_jenis_dokumen: $('#id_jenis_dokumen').val()
				},
				dataType: 'json'
			});
		}

		// Function untuk membuat field HTML
		function createFieldHTML(field) {
			const required = field.required ? 'required="required"' : '';
			const requiredLabel = field.required ? ' <span class="text-danger">*</span>' : '';

			// Get existing value from PHP or form data
			let existingValue = '';
			try {
				// Try to get value from PHP set_value function
				existingValue = `<?= set_value('${field.name}', @$peraturan['${field.name}']) ?>`;
			} catch (e) {
				// Fallback to empty string
				existingValue = '';
			}

			let inputHTML = '';
			if (field.type === 'date') {
				inputHTML = `<input class="form-control date-picker" type="text" name="${field.name}" value="${existingValue}" ${required} />`;
			} else {
				inputHTML = `<input class="form-control" type="${field.type}" name="${field.name}" value="${existingValue}" ${required} />`;
			}

			return `
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">${field.label}${requiredLabel}</label>
					<div class="col-sm-9">
						${inputHTML}
					</div>
				</div>
			`;
		}

		// Function untuk memuat field metadata
		function loadMetadataFields(jenisPeraturan, existingData = null) {
			const container = $('#metadata-fields-container');

			// Show loading indicator
			container.html(`
				<div class="row mb-3">
					<div class="col-12">
						<div class="alert alert-info">
							<i class="fas fa-spinner fa-spin me-2"></i>
							Memuat field untuk: <strong>${jenisPeraturan}</strong>
						</div>
					</div>
				</div>
			`);

			// Get template from backend
			getMetadataTemplate(jenisPeraturan)
				.then(function(response) {
					if (response.success && response.template) {
						let fieldsHTML = '';
						Object.keys(response.template).forEach(fieldName => {
							const field = response.template[fieldName];
							const fieldConfig = {
								name: fieldName,
								label: field.label,
								type: fieldName.includes('tgl_') ? 'date' : 'text',
								required: field.required
							};
							fieldsHTML += createFieldHTMLWithData(fieldConfig, existingData);
						});

						container.html(fieldsHTML);

						// Re-initialize date pickers
						if (typeof $.fn.datepicker !== 'undefined') {
							$('.date-picker').datepicker({
								format: 'yyyy-mm-dd',
								autoclose: true,
								todayHighlight: true
							});
						}
					} else {
						container.html(`
							<div class="row mb-3">
								<div class="col-12">
									<div class="alert alert-warning">
										<i class="fas fa-exclamation-triangle me-2"></i>
										Tidak dapat memuat template untuk jenis peraturan ini
									</div>
								</div>
							</div>
						`);
					}
				})
				.catch(function(error) {
					console.error('Error loading metadata template:', error);
					container.html(`
						<div class="row mb-3">
							<div class="col-12">
								<div class="alert alert-danger">
									<i class="fas fa-exclamation-circle me-2"></i>
									Terjadi kesalahan saat memuat template metadata
								</div>
							</div>
						</div>
					`);
				});
		}

		// Function untuk membuat field HTML dengan data existing
		function createFieldHTMLWithData(field, existingData = null) {
			const required = field.required ? 'required="required"' : '';
			const requiredLabel = field.required ? ' <span class="text-danger">*</span>' : '';

			// Get existing value
			let existingValue = '';

			// First try to get from existing metadata data (for backward compatibility)
			if (existingData && existingData[field.name]) {
				existingValue = existingData[field.name];
			}
			// Then try to get from PHP set_value (for form validation errors)
			else {
				<?php if (isset($peraturan)): ?>
					// For edit mode, try to get from peraturan data
					const peraturanData = <?= json_encode($peraturan ?? []) ?>;
					if (peraturanData && peraturanData[field.name]) {
						existingValue = peraturanData[field.name];
					}
					// Special handling for teu and bidang_hukum fields
					if (field.name === 'teu' && peraturanData && peraturanData.teu) {
						existingValue = peraturanData.teu;
					}
					if (field.name === 'bidang_hukum' && peraturanData && peraturanData.bidang_hukum) {
						existingValue = peraturanData.bidang_hukum;
					}
				<?php endif; ?>
			}

			// Escape HTML entities to prevent XSS
			existingValue = existingValue.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

			let inputHTML = '';
			if (field.type === 'date') {
				inputHTML = `<input class="form-control date-picker" type="text" name="${field.name}" value="${existingValue}" ${required} />`;
			} else {
				inputHTML = `<input class="form-control" type="${field.type}" name="${field.name}" value="${existingValue}" ${required} />`;
			}

			return `
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">${field.label}${requiredLabel}</label>
					<div class="col-sm-9">
						${inputHTML}
					</div>
				</div>
			`;
		}

		// Event handler untuk perubahan jenis peraturan
		$('#id_jenis_dokumen').on('change', function() {
			const selectedValue = $(this).val();
			const selectedText = $(this).find('option:selected').text();

			if (selectedValue) {
				// Get existing metadata data for edit mode
				let existingMetadataData = null;
				<?php if (isset($peraturan['metadata_json']) && !empty($peraturan['metadata_json'])): ?>
					try {
						existingMetadataData = <?= $peraturan['metadata_json'] ?>;
					} catch (e) {
						console.error('Error parsing metadata JSON:', e);
					}
				<?php endif; ?>

				// Merge with database fields for teu and bidang_hukum
				<?php if (isset($peraturan)): ?>
					const peraturanData = <?= json_encode($peraturan ?? []) ?>;
					if (peraturanData) {
						if (!existingMetadataData) existingMetadataData = {};
						// Add teu and bidang_hukum from database columns
						if (peraturanData.teu) existingMetadataData.teu = peraturanData.teu;
						if (peraturanData.bidang_hukum) existingMetadataData.bidang_hukum = peraturanData.bidang_hukum;
					}
				<?php endif; ?>

				// Load metadata fields berdasarkan jenis peraturan
				loadMetadataFields(selectedText, existingMetadataData);
			} else {
				$('#metadata-fields-container').html(`
					<div class="row mb-3">
						<div class="col-12">
							<div class="alert alert-info">
								<i class="fas fa-info-circle me-2"></i>
								Silakan pilih jenis peraturan untuk menampilkan field yang sesuai
							</div>
						</div>
					</div>
				`);
			}
		});

		// Load initial fields jika ada jenis peraturan yang sudah dipilih
		const initialJenis = $('#id_jenis_dokumen').val();
		if (initialJenis) {
			const selectedText = $('#id_jenis_dokumen option:selected').text();

			// Get existing metadata data for edit mode
			let existingMetadataData = null;
			<?php if (isset($peraturan['metadata_json']) && !empty($peraturan['metadata_json'])): ?>
				try {
					existingMetadataData = <?= $peraturan['metadata_json'] ?>;
				} catch (e) {
					console.error('Error parsing metadata JSON:', e);
				}
			<?php endif; ?>

			// Merge with database fields for teu and bidang_hukum
			<?php if (isset($peraturan)): ?>
				const peraturanData = <?= json_encode($peraturan ?? []) ?>;
				if (peraturanData) {
					if (!existingMetadataData) existingMetadataData = {};
					// Add teu and bidang_hukum from database columns
					if (peraturanData.teu) existingMetadataData.teu = peraturanData.teu;
					if (peraturanData.bidang_hukum) existingMetadataData.bidang_hukum = peraturanData.bidang_hukum;
				}
			<?php endif; ?>

			loadMetadataFields(selectedText, existingMetadataData);
		} else {
			// Tampilkan pesan untuk memilih jenis peraturan
			$('#metadata-fields-container').html(`
				<div class="row mb-3">
					<div class="col-12">
						<div class="alert alert-info">
							<i class="fas fa-info-circle me-2"></i>
							Silakan pilih jenis peraturan untuk menampilkan field yang sesuai
						</div>
					</div>
				</div>
			`);
		}

		// ======================
		// TAG MANAGEMENT
		// ======================

		let searchTimer;

		// Utility function for feedback
		function showFeedback(message, type, duration = 3000) {
			const feedbackDiv = $('#tag-feedback');
			feedbackDiv.html(`<div class="alert alert-${type} py-2">${message}</div>`).show();
			setTimeout(() => feedbackDiv.fadeOut('slow'), duration);
		}

		// Add new tag function
		function addNewTag() {
			const newTagName = $('#new-tag-input').val().trim();

			if (!newTagName) {
				showFeedback('Nama tag tidak boleh kosong!', 'warning');
				$('#new-tag-input').focus();
				return;
			}

			// Show loading state
			const $button = $('#add-new-tag');
			const originalText = $button.html();
			$button.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

			// Save new tag via AJAX
			$.ajax({
				url: '<?= base_url('data_peraturan/add_tag_ajax') ?>',
				type: 'POST',
				data: {
					nama_tag: newTagName,
					'<?= csrf_token() ?>': '<?= csrf_hash() ?>'
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success') {
						// Add to Select2 and select it
						const newOption = new Option(newTagName, response.id_tag, true, true);
						$('#select-tags').append(newOption).trigger('change');

						$('#new-tag-input').val('');
						showFeedback(`<i class="fas fa-check-circle"></i> Tag "${newTagName}" berhasil ditambahkan`, 'success');
					} else {
						showFeedback(`<i class="fas fa-exclamation-circle"></i> ${response.message}`, 'danger');
					}
				},
				error: function(xhr) {
					const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menambahkan tag baru.';
					showFeedback(`<i class="fas fa-exclamation-triangle"></i> ${errorMsg}`, 'danger');
				},
				complete: function() {
					$button.html(originalText).prop('disabled', false);
					$('#new-tag-input').focus();
				}
			});
		}

		// Tag input autocomplete with debouncing
		$('#new-tag-input').on('input', function() {
			const inputVal = $(this).val().toLowerCase().trim();
			const inputElement = $(this);

			clearTimeout(searchTimer);

			if (inputVal.length > 0) {
				$('#tag-feedback').html('<div class="text-muted small"><i class="fas fa-spinner fa-spin"></i> Mencari tag serupa...</div>').show();

				searchTimer = setTimeout(function() {
					$.ajax({
						url: '<?= base_url('api/tags/search') ?>',
						type: 'GET',
						data: {
							q: inputVal
						},
						dataType: 'json',
						success: function(data) {
							if (data.results?.length > 0) {
								let suggestionHtml = '<div class="text-muted small mt-1">Tag serupa: ';
								data.results.slice(0, 5).forEach((tag, index) => {
									suggestionHtml += `<a href="javascript:void(0)" class="tag-suggestion" data-id="${tag.id}">${tag.text}</a>`;
									if (index < Math.min(data.results.length, 5) - 1) suggestionHtml += ', ';
								});
								suggestionHtml += '</div>';

								$('#tag-feedback').html(suggestionHtml).show();
							} else {
								$('#tag-feedback').html('<div class="text-muted small">Tidak ada tag serupa. Tekan Enter atau klik "Tambah Tag" untuk membuat tag baru.</div>').show();
								setTimeout(() => $('#tag-feedback').fadeOut('slow'), 3000);
							}
							inputElement.focus();
						},
						error: function() {
							$('#tag-feedback').hide();
							inputElement.focus();
						}
					});
				}, 300);
			} else {
				$('#tag-feedback').hide();
			}
		});

		// ======================
		// EVENT HANDLERS
		// ======================

		// Add tag button click
		$('#add-new-tag').on('click', addNewTag);

		// Enter key on tag input
		$('#new-tag-input').on('keydown', function(e) {
			if (e.which === 13) {
				e.preventDefault();
				e.stopPropagation();
				addNewTag();
				return false;
			}
		});

		// Prevent form submission when Enter pressed in tag input
		$(document).on('submit', 'form', function(e) {
			if ($(document.activeElement).attr('id') === 'new-tag-input') {
				e.preventDefault();
				return false;
			}
		});

		// Tag suggestion click handler
		$(document).on('click', '.tag-suggestion', function(e) {
			e.preventDefault();
			const tagText = $(this).text();
			const tagId = $(this).data('id');

			if (tagId) {
				// Add to Select2 if not exists
				if ($('#select-tags option[value="' + tagId + '"]').length === 0) {
					const newOption = new Option(tagText, tagId, true, true);
					$('#select-tags').append(newOption);
				}

				// Select the tag
				const currentSelections = $('#select-tags').val() || [];
				if (!currentSelections.includes(tagId.toString())) {
					currentSelections.push(tagId.toString());
					$('#select-tags').val(currentSelections).trigger('change');
				}

				$('#new-tag-input').val('').focus();
				$('#tag-feedback').hide();
				showFeedback(`<i class="fas fa-check-circle"></i> Tag "${tagText}" telah dipilih`, 'success');
			}
			return false;
		});

		// ======================
		// TINYMCE INTEGRATION
		// ======================

		if (typeof tinymce !== 'undefined') {
			// Ensure TinyMCE content is saved before form submission
			$('form').on('submit', function(e) {
				tinymce.triggerSave();

				// Backup: manually set textarea values if needed
				const abstrakEditor = tinymce.get('abstrak_teks');
				const catatanEditor = tinymce.get('catatan_teks');

				if (abstrakEditor && abstrakEditor.getContent() && !$('textarea[name="abstrak_teks"]').val()) {
					$('textarea[name="abstrak_teks"]').val(abstrakEditor.getContent());
				}

				if (catatanEditor && catatanEditor.getContent() && !$('textarea[name="catatan_teks"]').val()) {
					$('textarea[name="catatan_teks"]').val(catatanEditor.getContent());
				}

				// Add hidden inputs as backup
				if (abstrakEditor?.getContent()) {
					$(this).append(`<input type="hidden" name="abstrak_teks_hidden" value="${abstrakEditor.getContent()}">`);
				}

				if (catatanEditor?.getContent()) {
					$(this).append(`<input type="hidden" name="catatan_teks_hidden" value="${catatanEditor.getContent()}">`);
				}
			});
		}
	});

	// ======================
	// CSS STYLES
	// ======================
	$(`<style>
.tag-suggestion {
    display: inline-block;
    padding: 2px 8px;
    margin: 2px;
    background-color: #f8f9fa;
    border-radius: 12px;
    color: #495057;
    text-decoration: none;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}
.tag-suggestion:hover {
    background-color: #e9ecef;
    color: #212529;
    text-decoration: none;
    border-color: #adb5bd;
}
.tag-suggestion:active {
    background-color: #dee2e6;
}

/* Metadata field animations */
#metadata-fields-container .row {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading indicator */
.alert-info {
    border-left: 4px solid #17a2b8;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}
</style>`).appendTo('head');
</script>

<?php if (!empty($msg) && $msg['status'] == 'ok'): ?>
	<script>
		window.location.href = "<?= base_url('data_peraturan') ?>";
	</script>
<?php endif; ?>