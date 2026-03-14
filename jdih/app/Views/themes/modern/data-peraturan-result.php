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
	/* --- Premium Data Peraturan Styles --- */
	
	/* Glass Card Container */
	.glass-card {
		background: rgba(255, 255, 255, 0.95);
		backdrop-filter: blur(10px);
		-webkit-backdrop-filter: blur(10px);
		border: 1px solid rgba(255, 255, 255, 0.3);
		border-radius: 20px;
		overflow: hidden;
		transition: transform 0.3s ease, box-shadow 0.3s ease;
	}
	
	.shadow-premium {
		box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0, 0, 0, 0.03) !important;
	}

	/* Card Header */
	.card-header-premium {
		background: transparent;
		border-bottom: none;
	}

	.icon-box {
		width: 45px;
		height: 45px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 1.25rem;
	}

	/* Table Styling */
	.table-container-premium {
		background: #fcfcfd;
		border-radius: 15px;
		border: 1px solid #f1f3f9;
		overflow: hidden;
	}

	.custom-modern-table {
		margin-bottom: 0 !important;
		border-collapse: separate !important;
		border-spacing: 0 !important;
	}

	.custom-modern-table thead th {
		background-color: #f8faff !important;
		color: #5d6e82;
		font-weight: 700;
		text-transform: uppercase;
		font-size: 0.75rem;
		letter-spacing: 0.5px;
		padding: 18px 15px !important;
		border-bottom: 2px solid #edf2f9 !important;
		border-top: none !important;
	}

	.custom-modern-table tbody tr {
		transition: background-color 0.2s ease;
	}

	.custom-modern-table tbody tr:hover {
		background-color: #f8faff !important;
	}

	.custom-modern-table td {
		padding: 15px !important;
		border-bottom: 1px solid #f1f3f9 !important;
		color: #334155;
		font-size: 0.875rem;
	}

	/* Premium Buttons */
	.btn-blue-premium {
		background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
		color: white !important;
		border: none;
		font-weight: 600;
		letter-spacing: 0.3px;
	}

	.btn-blue-premium:hover {
		background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
		transform: translateY(-2px);
		box-shadow: 0 7px 14px rgba(37, 99, 235, 0.2) !important;
	}

	.btn-light-premium {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		color: #64748b;
	}

	.btn-light-premium:hover {
		background: #f8faff;
		color: #2563eb;
		border-color: #2563eb;
	}

	.btn-icon-round {
		width: 40px;
		height: 40px;
		padding: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
	}

	.shadow-hover:hover {
		box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
	}

	/* Utility */
	.bg-soft-blue {
		background-color: #f0f7ff;
	}

	.text-blue-premium {
		color: #2563eb;
	}

	.rounded-pill {
		border-radius: 50rem !important;
	}

	/* DataTables Override */
	.dataTables_wrapper .dataTables_length select,
	.dataTables_wrapper .dataTables_filter input {
		border-radius: 8px;
		border: 1px solid #e2e8f0;
		padding: 6px 12px;
	}

	.dataTables_wrapper .dataTables_paginate .paginate_button.current {
		background: #2563eb !important;
		color: white !important;
		border: none !important;
		border-radius: 8px !important;
	}

	@media (max-width: 768px) {
		.d-sm-flex {
			flex-direction: column;
			gap: 1.5rem;
		}
		
		.actions-group {
			width: 100%;
			justify-content: flex-start;
		}
	}
</style>