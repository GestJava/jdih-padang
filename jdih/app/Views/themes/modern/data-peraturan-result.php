<?php
// Add body class for JavaScript detection
if (!empty($body_class)) {
	echo '<script>document.body.className += " ' . esc($body_class) . '";</script>';
}
?>

<div class="container-fluid animate__animated animate__fadeIn">
	<!-- Modern Header Section -->
	<div class="d-sm-flex align-items-center justify-content-between mb-5">
		<div class="header-content">
			<h1 class="h2 fw-bold text-dark mb-1">
				<i class="fas fa-gavel text-blue-premium me-3 mb-2"></i><?= esc($current_module['judul_module'] ?? 'Data Peraturan') ?>
			</h1>
			<p class="text-muted mb-0">Kelola dan administrasi seluruh data peraturan daerah secara efisien.</p>
		</div>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb bg-soft-blue px-4 py-2 rounded-pill shadow-sm mb-0">
				<li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-blue-premium text-decoration-none"><i class="fas fa-home me-1"></i>Dashboard</a></li>
				<li class="breadcrumb-item active" aria-current="page">Data Peraturan</li>
			</ol>
		</nav>
	</div>

	<!-- Flash Messages -->
	<?php if (!empty($msg)): ?>
		<?php if (is_array($msg) && isset($msg['status'])): ?>
			<div class="alert alert-<?= $msg['status'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm animate__animated animate__slideInDown mb-4">
				<div class="d-flex align-items-center">
					<i class="fas fa-<?= $msg['status'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> fs-4 me-3"></i>
					<div><?= esc($msg['content'] ?? $msg['message'] ?? '') ?></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		<?php else: ?>
			<div class="animate__animated animate__slideInDown mb-4"><?= show_alert($msg); ?></div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="row">
		<div class="col-12">
			<!-- Glass Card Main Container -->
			<div class="glass-card shadow-premium mb-5">
				<div class="card-header-premium p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
					<div class="d-flex align-items-center">
						<div class="icon-box bg-blue-premium text-white rounded-3 me-3 p-2 shadow-sm">
							<i class="fas fa-database"></i>
						</div>
						<h5 class="m-0 fw-bold text-dark">Data Repository</h5>
					</div>
					<div class="actions-group d-flex gap-2">
						<button type="button" class="btn btn-light-premium btn-icon-round" data-action="reload-table" title="Refresh Data">
							<i class="fas fa-sync-alt"></i>
						</button>
						<a href="<?= current_url() ?>/add" class="btn btn-blue-premium px-4 rounded-pill shadow-hover">
							<i class="fas fa-plus-circle me-2"></i>Tambah Data Baru
						</a>
					</div>
				</div>
				
				<div class="card-body p-4 pt-0">
					<div class="table-container-premium">
						<div class="table-responsive">
							<table class="table table-hover align-middle custom-modern-table" id="data-tables">
								<thead>
									<tr>
										<th width="50">No</th>
										<th>Jenis</th>
										<th width="100">Nomor</th>
										<th width="80">Tahun</th>
										<th>Judul Peraturan</th>
										<th>Pemrakarsa</th>
										<th width="120">Status</th>
										<th width="100">File</th>
										<th width="150" class="text-center">Aksi</th>
									</tr>
								</thead>
								<tbody>
									<!-- DataTables dynamic content -->
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	// Debug info untuk troubleshooting
	console.log('🔍 Data Peraturan Debug Info:');
	console.log('📅 Page loaded at:', new Date().toISOString());
	console.log('🔗 Current URL:', window.location.href);
	console.log('👤 Session status:', <?= json_encode(is_logged_in()) ?>);
	console.log('👤 User info:', <?= json_encode(get_current_user()) ?>);
	console.log('🔑 CSRF Token:', '<?= csrf_token() ?>', '=', '<?= csrf_hash() ?>');
	console.log('📋 Body class:', document.body.className);
	console.log('📋 Data tables element exists:', $('#data-tables').length > 0);

	// DataTable initialization is now handled by data-peraturan-admin.js
	// This script only provides CSRF token variables for AJAX requests
	$(document).ready(function() {
		// Set global CSRF variables for AJAX requests
		window.csrf_token_name = '<?= csrf_token() ?>';
		window.csrf_token_value = '<?= csrf_hash() ?>';

		console.log('📋 Data Peraturan: Page loaded, DataTable will be initialized by data-peraturan-admin.js');
		console.log('🔑 CSRF Token:', window.csrf_token_name, '=', window.csrf_token_value);

		// Additional debug info
		console.log('📊 jQuery version:', $.fn.jquery);
		console.log('📊 DataTables available:', typeof $.fn.DataTable !== 'undefined');
		console.log('📊 SweetAlert2 available:', typeof Swal !== 'undefined');
		console.log('📊 Bootbox available:', typeof bootbox !== 'undefined');
	});
</script>

<style>
	/* Enhanced styling untuk konsistensi dengan modul harmonisasi */
	.card {
		border: none;
		box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
	}

	.card-header {
		border-bottom: none;
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

	/* Enhanced alert styling */
	.alert {
		border: none;
		border-radius: 0.375rem;
	}

	/* Responsive improvements */
	@media (max-width: 768px) {
		.d-sm-flex {
			flex-direction: column;
			align-items: flex-start !important;
		}

		.breadcrumb {
			margin-top: 1rem;
		}
	}
</style>