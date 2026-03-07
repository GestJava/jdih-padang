<?php

/**
 *	App Name	: Admin Template Codeigniter 4
 *	Developed by: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2020-2022
 */

// KEAMANAN: Gunakan session service untuk memastikan data selalu fresh
// Jangan gunakan $_SESSION langsung karena mungkin tidak konsisten
$session = service('session');
$sessionUser = $session->get('user');
$sessionLoggedIn = $session->get('logged_in');

// Check if user is logged in and session exists
if (empty($sessionUser) || !$sessionLoggedIn || !is_array($sessionUser) || empty($sessionUser['id_user'])) {
	$content = 'Layout halaman ini memerlukan login. Silakan login terlebih dahulu.';
	include('app/Views/themes/modern/header-error.php');
	exit;
}

// Check if required variables exist
if (!isset($current_module) || !isset($settingAplikasi) || !isset($config)) {
	$content = 'Data yang diperlukan tidak tersedia. Silakan login ulang.';
	include('app/Views/themes/modern/header-error.php');
	exit;
}
?>
<!DOCTYPE HTML>
<html lang="en" data-bs-theme="<?= $theme_mode ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $current_module['judul_module'] ?> | <?= $settingAplikasi['judul_web'] ?></title>
	<meta name="descrition" content="<?= $current_module['deskripsi'] ?>" />
	<link rel="shortcut icon" href="<?= $config->baseURL . 'images/favicon.png?r=' . time() ?>" />
	<link rel="stylesheet" type="text/css" href="<?= $config->baseURL . 'vendors/fontawesome/css/all.css' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'vendors/bootstrap/css/bootstrap.min.css?v=5.3.0' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'vendors/bootstrap-icons/bootstrap-icons.css?v=1.11.0' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'vendors/sweetalert2/sweetalert2.min.css?v=11.0.0' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'vendors/overlayscrollbars/OverlayScrollbars.min.css?v=1.13.0' ?>" />

	<!-- Data Tables -->
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'vendors/datatables/dist/css/dataTables.bootstrap5.min.css?v=1.13.0' ?>" />
	<!-- // Data Tables -->

	<!-- Select2 CSS: gunakan versi default -->
	<link href="<?= base_url('vendors/jquery.select2/css/select2.min.css') ?>" rel="stylesheet" />

	<link rel="stylesheet" id="style-switch-bootswatch" type="text/css"
		href="<?= $config->baseURL . 'vendors/bootswatch/' . ($theme_mode == 'light' ? $app_layout['bootswatch_theme'] : 'default') . '/bootstrap.min.css?v=5.3.0' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'themes/modern/builtin/css/site.css?v=1.0.1' ?>" />
	<link rel="stylesheet" id="font-switch" type="text/css"
		href="<?= $config->baseURL . 'themes/modern/builtin/css/fonts/' . $app_layout['font_family'] . '.css?v=1.0.1' ?>" />
	<link rel="stylesheet" id="style-switch" type="text/css"
		href="<?= $config->baseURL . 'themes/modern/builtin/css/color-schemes/' . $app_layout['color_scheme'] . '.css?v=1.0.1' ?>" />
	<link rel="stylesheet" id="style-switch-sidebar" type="text/css"
		href="<?= $config->baseURL . 'themes/modern/builtin/css/color-schemes/' . $app_layout['sidebar_color'] . '-sidebar.css?v=1.0.1' ?>" />
	<link rel="stylesheet" id="logo-background-color-switch" type="text/css"
		href="<?= $config->baseURL . 'themes/modern/builtin/css/color-schemes/' . $app_layout['logo_background_color'] . '-logo-background.css?v=1.0.1' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'themes/modern/builtin/css/bootstrap-custom.css?v=1.0.1' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'themes/modern/builtin/css/dark-theme.css?v=1.0.1' ?>" />
	<link rel="stylesheet" type="text/css"
		href="<?= $config->baseURL . 'jdih/assets/css/harmonisasi-admin.css?v=1.0.1' ?>" />

	<style type="text/css">
		html,
		body {
			font-size: <?= $app_layout['font_size'] ?>px;
		}

		/* Custom CSS for Tabs */
		.nav-tabs .nav-link {
			color: #495057;
			background-color: #f8f9fa;
			border-color: #dee2e6 #dee2e6 #fff;
			border-radius: .25rem .25rem 0 0;
			padding: 0.8rem 1.2rem;
			font-weight: 500;
			transition: all 0.3s ease-in-out;
		}

		.nav-tabs .nav-link.active {
			color: #fff;
			background-color: #007bff;
			border-color: #007bff #007bff #fff;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.tab-content {
			border: 1px solid #dee2e6;
			border-top: 0;
			padding: 1.5rem;
			border-radius: 0 0 .25rem .25rem;
			background-color: #fff;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}
	</style>

	<!-- Dynamic styles -->
	<?php
	if (@$styles) {
		foreach ($styles as $file) {
			if (is_array($file)) {
				$attr = '';
				if (key_exists('attr', $file)) {
					foreach ($file['attr'] as $param => $val) {
						$attr .= $param . '="' . $val . '"';
					}
				}
				$file = $file['url'];
				echo '<link rel="stylesheet" ' . $attr . ' type="text/css" href="' . $file . '?r=' . time() . '"/>' . "\n";
			} else {
				echo '<link rel="stylesheet" type="text/css" href="' . $file . '?r=' . time() . '"/>' . "\n";
			}
		}
	}

	?>
	<!-- Datepicker CSS -->
	<link rel="stylesheet" type="text/css" href="<?= $config->baseURL . 'vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css' ?>">
	<script type="text/javascript">
		let base_url = "<?= $config->baseURL ?>";
		let module_url = "<?= $module_url ?>";
		let current_url = "<?= current_url() ?>";
		let theme_url = "<?= $config->baseURL . '/themes/modern/builtin/' ?>";
		let current_bootswatch_theme = "<?= $app_layout['bootswatch_theme'] ?>";

		// JDIH Configuration
		window.JDIH_CONFIG = {
			base_url: "<?= $config->baseURL ?>",
			module_url: "<?= $module_url ?>",
			current_url: "<?= current_url() ?>",
			theme_url: "<?= $config->baseURL . '/themes/modern/builtin/' ?>",
			csrf_token: "<?= csrf_hash() ?>",
			csrf_name: "<?= csrf_token() ?>"
		};
	</script>
	<script type="text/javascript" src="<?= $config->baseURL . 'vendors/jquery/jquery.min.js' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'vendors/bootstrap/js/bootstrap.bundle.min.js' ?>"></script>
	<script type="text/javascript" src="<?= $config->baseURL . 'vendors/bootbox/bootbox.min.js' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'vendors/sweetalert2/sweetalert2.min.js' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'vendors/overlayscrollbars/jquery.overlayScrollbars.min.js' ?>"></script>
	<script type="text/javascript" src="<?= $config->baseURL . 'vendors/js.cookie/js.cookie.min.js' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'vendors/bootstrap-datepicker/locales/bootstrap-datepicker.id.min.js' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'themes/modern/builtin/js/functions.js?v=1.0.1' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'themes/modern/builtin/js/site.js?v=1.0.1' ?>"></script>
	<script src="<?= base_url('vendors/jquery.select2/js/select2.full.min.js') ?>"></script>

	<!-- Data Tables -->
	<script type="text/javascript"
		src="<?= $config->baseURL . 'vendors/datatables/dist/js/jquery.dataTables.min.js?v=1.13.0' ?>"></script>
	<script type="text/javascript"
		src="<?= $config->baseURL . 'vendors/datatables/dist/js/dataTables.bootstrap5.min.js?v=1.13.0' ?>"></script>
	<!-- // Data Tables -->

	<!-- Dynamic scripts -->
	<?php
	if (@$scripts) {
		foreach ($scripts as $file) {
			if (is_array($file)) {
				if ($file['print']) {
					echo '<script type="text/javascript">' . $file['script'] . '</script>' . "\n";
				}
			} else {
				echo '<script type="text/javascript" src="' . $file . '?v=1.0.1"></script>' . "\n";
			}
		}
	}

	$user = $_SESSION['user'];

	?>
	<!-- Harmonisasi Admin JS -->
	<script type="text/javascript" src="<?= $config->baseURL . 'jdih/assets/js/harmonisasi-admin.js?v=1.0.1' ?>"></script>

	<!-- JDIH Asset Manager -->
	<script type="text/javascript" src="<?= $config->baseURL . 'jdih/assets/js/jdih-asset-manager.js?v=1.0.1' ?>"></script>

	<!-- Custom CSS untuk user name truncation -->
	<style>
		.user-name-tooltip {
			cursor: help;
		}

		.user-name-tooltip:hover {
			text-decoration: underline;
		}

		/* Responsive text truncation */
		@media (max-width: 768px) {
			.btn .user-name-tooltip {
				max-width: 80px;
				display: inline-block;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
		}
	</style>

	<style>
		/* Fix tampilan Select2 multiple agar seragam dengan .form-control */
		.select2-container--default .select2-selection--multiple {
			min-height: 38px;
			border: 1px solid #ced4da;
			border-radius: 0.375rem;
			padding: 0.375rem 0.75rem;
			font-size: 1rem;
			background: #fff;
			box-shadow: none;
			transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
		}

		.select2-container--default.select2-container--focus .select2-selection--multiple {
			border-color: #86b7fe;
			box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25);
		}

		.select2-container--default .select2-selection--multiple .select2-search__field {
			height: 28px;
			margin: 0;
			font-size: 1rem;
			background: transparent;
		}
	</style>
</head>

<body class="<?= @$_COOKIE['jwd_adm_mobile'] ? 'mobile-menu-show' : '' ?>">
	<header class="nav-header shadow">
		<div class="nav-header-logo pull-left">
			<?php
			// Tentukan URL default user
			$redirect_url = $config->baseURL . 'harmonisasi'; // Default
			if (!empty($user['default_page_type']) && $user['default_page_type'] == 'id_module' && !empty($user['default_module']['nama_module'])) {
				$redirect_url = $config->baseURL . $user['default_module']['nama_module'];
			} else if (!empty($user['default_page_type']) && $user['default_page_type'] == 'url' && !empty($user['default_page_url'])) {
				$redirect_url = str_replace('{{BASE_URL}}', $config->baseURL, $user['default_page_url']);
			}
			?>
			<a class="header-logo" href="<?= $redirect_url ?>" title="JDIH Kota Padang">
				<img src="<?= $config->baseURL . '/images/' . $settingAplikasi['logo_app'] ?>" />
			</a>
		</div>
		<div class="pull-left nav-header-left">
			<ul class="nav-header">
				<li>
					<a href="#" id="mobile-menu-btn">
						<i class="fa fa-bars"></i>
					</a>
				</li>
			</ul>
		</div>
		<div class="pull-right mobile-menu-btn-right">
			<a href="#" id="mobile-menu-btn-right">
				<i class="fa fa-ellipsis-h"></i>
			</a>
		</div>
		<div class="pull-right nav-header nav-header-right">
			<ul class="d-flex align-items-center">
				<li class="nav-item dropdown nav-theme-option">
					<a class="icon-link nav-link" href="#" role="button" data-bs-toggle="dropdown"
						aria-expanded="false">
						<?php
						$theme_light = $theme_dark = $theme_system = '';

						if (@$_COOKIE['jwd_adm_theme_system'] == 'true') {
							$theme_system = 'active';
							$icon_class = 'bi-circle-half';
						} else {
							switch (@$_COOKIE['jwd_adm_theme']) {
								case 'dark':
									$theme_dark = 'active';
									$icon_class = 'bi bi-moon-stars';
									break;
								case 'light':
								default:
									$theme_light = 'active';
									$icon_class = 'bi bi-sun';
									break;
							}
						}
						?>
						<i class="<?= $icon_class ?>"></i>
					</a>
					<ul class="dropdown-menu">
						<li>
							<button class="dropdown-item <?= $theme_light ?>" data-theme-value="light">
								<i class="bi bi-sun me-2"></i>Light
								<i class="check bi bi-check2 float-end"></i>
							</button>
						</li>
						<li>
							<button class="dropdown-item <?= $theme_dark ?>" data-theme-value="dark">
								<i class="bi bi-moon-stars me-2"></i>Dark
								<i class="check bi bi-check2 float-end"></i>
							</button>
						</li>
						<li>
							<button class="dropdown-item <?= $theme_system ?>" data-theme-value="system">
								<i class="bi bi-circle-half me-2"></i>System
								<i class="check bi bi-check2 float-end"></i>
							</button>
						</li>
					</ul>
				</li>
				<li class="nav-item dropdown notification-dropdown">
					<a class="icon-link nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="bi bi-bell"></i>
						<?php if (($notifications['total'] ?? 0) > 0): ?>
						<span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 3px 5px;">
							<?= $notifications['total'] ?>
						</span>
						<?php endif; ?>
					</a>
					<ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 250px; max-height: 400px; overflow-y: auto;">
						<li class="dropdown-header border-bottom py-2">
							<h6 class="mb-0 text-primary">Daftar Tugas Pending</h6>
						</li>
						<?php if (empty($notifications['items'])): ?>
							<li class="p-3 text-center text-muted">
								<small>Tidak ada tugas pending</small>
							</li>
						<?php else: ?>
							<?php foreach ($notifications['items'] as $item): ?>
							<li>
								<a class="dropdown-item py-3 border-bottom" href="<?= $item['url'] ?>">
									<div class="d-flex align-items-center">
										<div class="bg-light p-2 rounded-circle me-3 text-center" style="width: 40px; height: 40px;">
											<i class="<?= $item['icon'] ?> text-primary"></i>
										</div>
										<div style="white-space: normal;">
											<div class="fw-bold mb-0" style="font-size: 13px;"><?= $item['title'] ?></div>
											<small class="text-danger fw-bold"><?= $item['count'] ?> dokumen</small>
										</div>
									</div>
								</a>
							</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</li>
				<li>
					<a class="icon-link" href="<?= $config->baseURL ?>builtin/setting-layout"><i
							class="bi bi-gear"></i></a>
				</li>
				<li class="ps-2 nav-account">
					<?php
					// KEAMANAN: Gunakan session service untuk memastikan data selalu fresh
					// Jangan bergantung pada variabel $user dari controller yang mungkin ter-cache
					$session = service('session');
					$currentUser = $session->get('user');
					
					// Validasi user benar-benar ada dan memiliki data yang valid
					$isUserValid = !empty($currentUser) && is_array($currentUser) && !empty($currentUser['id_user']) && !empty($currentUser['nama']);
					
					// Gunakan $currentUser jika valid, fallback ke $user dari controller jika ada
					$displayUser = $isUserValid ? $currentUser : ($user ?? []);
					$isUserLoggedIn = $isUserValid || ($isloggedin && !empty($displayUser['id_user']));
					
					$img_url = !empty($displayUser['avatar']) && file_exists(ROOTPATH . '/images/user/' . $displayUser['avatar']) 
						? $config->baseURL . '/images/user/' . $displayUser['avatar'] 
						: $config->baseURL . '/images/user/default.png';
					$account_link = $config->baseURL . 'user';
					?>
					<a class="profile-btn" href="<?= $account_link ?>" data-bs-toggle="dropdown"><img
							src="<?= $img_url ?>" alt="user_img"></a>
					<?php
					if ($isUserLoggedIn && !empty($displayUser['id_user'])) {
					?>
						<ul class="dropdown-menu">
							<li class="dropdown-profile px-4 pt-4 pb-2">
								<div class="avatar">
									<a href="<?= $config->baseURL . 'builtin/user/edit?id=' . $displayUser['id_user']; ?>">
										<img src="<?= $img_url ?>" alt="user_img">
									</a>
								</div>
								<div class="card-content mt-3">
									<p><?php
										// Pastikan nama user selalu diambil dari data yang valid
										$userNama = strtoupper($displayUser['nama'] ?? 'Admin');
										if (strlen($userNama) > 25) {
											echo '<span title="' . esc($userNama) . '">' . esc(substr($userNama, 0, 22) . '...') . '</span>';
										} else {
											echo esc($userNama);
										}
										?></p>
									<p><small>Email: <?= esc($displayUser['email'] ?? '') ?></small></p>
								</div>
							</li>
							<li>
								<a class="dropdown-item py-2"
									href="<?= $config->baseURL ?>builtin/user/edit-password">Change
									Password</a>
							</li>
							<li>
							<li><a class="dropdown-item py-2" href="<?= $config->baseURL ?>login/logout">Logout</a></li>
				</li>
			</ul>
		<?php } else { ?>
			<div class="float-login">
				<form method="post" action="<?= $config->baseURL ?>login">
					<input type="email" name="email" value="" placeholder="Email" required>
					<input type="password" name="password" value="" placeholder="Password" required>
					<div class="checkbox">
						<label style="font-weight:normal"><input name="remember" value="1"
								type="checkbox">&nbsp;&nbsp;Remember me</label>
					</div>
					<button type="submit" style="width:100%" class="btn btn-success" name="submit">Submit</button>
					<?php
						$form_token = $auth->generateFormToken('login_form_token_header');
					?>
					<input type="hidden" name="form_token" value="<?= $form_token ?>" />
					<input type="hidden" name="login_form_header" value="login_form_header" />
				</form>
				<a href="<?= $config->baseURL . 'recovery' ?>">Lupa password?</a>
			</div>
		<?php } ?>
		</li>
		</ul>
		</div>
	</header>
	<div class="site-content">
		<div class="sidebar-guide">
			<div class="arrow" style="font-size:18px">
				<i class="fa-solid fa-angles-right"></i>
			</div>
		</div>
		<div class="sidebar shadow">
			<nav>
				<?php
				foreach ($menu as $val) {
					$kategori = $val['kategori'];
					if ($kategori['show_title'] == 'Y') {
						echo '<div class="menu-kategori">
								<div class="menu-kategori-wrapper">
									<h6 class="title">' . $kategori['nama_kategori'] . '</h6>';
						if ($kategori['deskripsi']) {
							echo '<small class="description">' . $kategori['deskripsi'] . '</small>';
						}
						echo '</div>
							</div>';
					}
					$list_menu = menu_list($val['menu']);
					echo build_menu($current_module, $list_menu, false, $notifications ?? []);
				}
				?>
			</nav>
		</div>
		<div class="content">
			<?= !empty($breadcrumb) ? breadcrumb($breadcrumb) : '' ?>
			<div class="content-wrapper">