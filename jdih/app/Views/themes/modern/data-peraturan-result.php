<?php
// Add body class for JavaScript detection
if (!empty($body_class)) {
	echo '<script>document.body.className += " ' . esc($body_class) . '";</script>';
}
?>

<div class="container-fluid">
	<div class="d-sm-flex align-items-center justify-content-between mb-4">
		<h1 class="h3 mb-0 text-gray-800">
			<i class="fas fa-gavel text-primary me-2"></i><?= esc($current_module['judul_module'] ?? 'Data Peraturan') ?>
		</h1>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
				<li class="breadcrumb-item active" aria-current="page">Data Peraturan</li>
			</ol>
		</nav>
	</div>

	<!-- Flash Messages -->
	<?php if (!empty($msg)): ?>
		<?php if (is_array($msg) && isset($msg['status'])): ?>
			<?php if ($msg['status'] === 'success'): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<i class="fas fa-check-circle me-2"></i><?= esc($msg['content'] ?? $msg['message'] ?? '') ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			<?php elseif ($msg['status'] === 'error'): ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<i class="fas fa-exclamation-triangle me-2"></i><?= esc($msg['content'] ?? $msg['message'] ?? '') ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			<?php endif; ?>
		<?php else: ?>
			<?= show_alert($msg); ?>
		<?php endif; ?>
	<?php endif; ?>

	<div class="row">
		<div class="col-12">
			<div class="card shadow mb-4">
				<div class="card-header bg-primary text-white py-3">
					<div class="d-flex justify-content-between align-items-center">
						<h6 class="m-0 font-weight-bold">
							<i class="fas fa-list me-2"></i>Daftar Peraturan
						</h6>
						<div>
							<button type="button" class="btn btn-light btn-sm me-2" data-action="reload-table" title="Refresh Data">
								<i class="fas fa-sync-alt"></i>
							</button>
							<a href="<?= current_url() ?>/add" class="btn btn-light btn-sm">
								<i class="fas fa-plus me-1"></i>Tambah Data
							</a>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="data-tables">
							<thead>
								<tr>
									<th>No</th>
									<th>Jenis Peraturan</th>
									<th>Nomor</th>
									<th>Tahun</th>
									<th>Judul</th>
									<th>Pemrakarsa</th>
									<th>Status</th>
									<th>File</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								<!-- Data akan dimuat oleh DataTables -->
							</tbody>
						</table>
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