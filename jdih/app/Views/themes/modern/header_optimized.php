<!DOCTYPE html>
<html lang="id" data-bs-theme="<?= @$_COOKIE['jwd_adm_theme'] ?: 'light' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="JDIH Kota Padang - Jaringan Dokumentasi dan Informasi Hukum">
    <meta name="author" content="Agus Salim">
    <meta name="robots" content="noindex, nofollow">

    <title><?= $title ?? 'JDIH Kota Padang' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('images/Download-Logo-Kota-Padang-PNG.ico') ?>">

    <!-- Preload critical assets -->
    <link rel="preload" href="<?= base_url('vendors/jquery/jquery.min.js') ?>" as="script">
    <link rel="preload" href="<?= base_url('vendors/bootstrap/css/bootstrap.min.css') ?>" as="style">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('vendors/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/bootstrap-icons/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/fontawesome/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/bootstrap-datepicker/css/bootstrap-datepicker.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/datatables/dist/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/overlayscrollbars/OverlayScrollbars.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendors/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('themes/modern/builtin/css/style.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('jdih/assets/css/harmonisasi-admin.css') ?>?v=<?= time() ?>">

    <!-- Inline Critical CSS -->
    <style>
        /* Critical CSS untuk loading yang cepat */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Hide loading overlay when page is ready */
        .page-ready .loading-overlay {
            display: none;
        }
    </style>

    <!-- JavaScript Configuration -->
    <script>
        window.JDIH_CONFIG = {
            base_url: "<?= base_url() ?>",
            module_url: "<?= service('request')->uri->getSegment(1) ?? '' ?>",
            current_url: "<?= current_url() ?>",
            theme_url: "<?= base_url() ?>themes/modern/builtin/",
            csrf_token: "<?= csrf_hash() ?>",
            csrf_name: "<?= csrf_token() ?>"
        };
    </script>

    <!-- Core JavaScript -->
    <script src="<?= base_url('vendors/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('vendors/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('vendors/js.cookie/js.cookie.min.js') ?>"></script>
    <script src="<?= base_url('vendors/bootbox/bootbox.min.js') ?>"></script>
    <script src="<?= base_url('vendors/sweetalert2/sweetalert2.min.js') ?>"></script>
    <script src="<?= base_url('vendors/overlayscrollbars/jquery.overlayScrollbars.min.js') ?>"></script>
    <script src="<?= base_url('vendors/bootstrap-datepicker/js/bootstrap-datepicker.min.js') ?>"></script>
    <script src="<?= base_url('vendors/bootstrap-datepicker/locales/bootstrap-datepicker.id.min.js') ?>"></script>
    <script src="<?= base_url('vendors/jquery.select2/js/select2.full.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/dist/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('vendors/datatables/dist/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= base_url('themes/modern/builtin/js/functions.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= base_url('themes/modern/builtin/js/site.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= base_url('jdih/assets/js/harmonisasi-admin.js') ?>?v=<?= time() ?>"></script>

    <!-- Page-specific assets -->
    <?php if (isset($page_assets)): ?>
        <?= $page_assets['css'] ?? '' ?>
        <?= $page_assets['js'] ?? '' ?>
    <?php endif; ?>

    <!-- Dynamic styles -->
    <?php if (@$styles): ?>
        <?php foreach ($styles as $file): ?>
            <?php if (is_array($file)): ?>
                <?php
                $attr = '';
                if (key_exists('attr', $file)) {
                    foreach ($file['attr'] as $param => $val) {
                        $attr .= $param . '="' . $val . '"';
                    }
                }
                $file = $file['url'];
                ?>
                <link rel="stylesheet" <?= $attr ?> href="<?= $file ?>?r=<?= time() ?>">
            <?php else: ?>
                <link rel="stylesheet" href="<?= $file ?>?r=<?= time() ?>">
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Dynamic scripts -->
    <?php if (@$scripts): ?>
        <?php foreach ($scripts as $file): ?>
            <?php if (is_array($file)): ?>
                <?php if ($file['print']): ?>
                    <script>
                        <?= $file['script'] ?>
                    </script>
                <?php endif; ?>
            <?php else: ?>
                <script src="<?= $file ?>?r=<?= time() ?>"></script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="<?= @$_COOKIE['jwd_adm_mobile'] ? 'mobile-menu-show' : '' ?>">
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Main Content -->
    <div class="page-content">
        <header class="nav-header shadow">
            <div class="nav-header-logo pull-left">
                <a class="header-logo" href="<?= base_url() ?>" title="JDIH Kota Padang">
                    <img src="<?= base_url('images/' . ($settingAplikasi['logo_app'] ?? 'logo.png')) ?>" alt="Logo JDIH">
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
                    <!-- Theme Switcher -->
                    <li class="nav-item dropdown nav-theme-option">
                        <a class="icon-link nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

                    <!-- Settings -->
                    <li>
                        <a class="icon-link" href="<?= base_url('builtin/setting-layout') ?>">
                            <i class="bi bi-gear"></i>
                        </a>
                    </li>

                    <!-- User Account -->
                    <li class="ps-2 nav-account">
                        <?php
                        // KEAMANAN: Gunakan session service, bukan $_SESSION langsung
                        // Ini memastikan data session selalu fresh dan tidak ter-cache
                        $session = service('session');
                        $user = $session->get('user');
                        $img_url = !empty($user['avatar']) && file_exists(ROOTPATH . '/images/user/' . $user['avatar'])
                            ? base_url('images/user/' . $user['avatar'])
                            : base_url('images/user/default.png');
                        $account_link = base_url('user');
                        ?>

                        <?php if ($isloggedin): ?>
                            <a class="profile-btn" href="<?= $account_link ?>" data-bs-toggle="dropdown">
                                <img src="<?= $img_url ?>" alt="user_img">
                            </a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-profile px-4 pt-4 pb-2">
                                    <div class="avatar">
                                        <a href="<?= base_url('builtin/user/edit?id=' . $user['id_user']) ?>">
                                            <img src="<?= $img_url ?>" alt="user_img">
                                        </a>
                                    </div>
                                    <div class="card-content mt-3">
                                        <p><?= strtoupper($user['nama']) ?></p>
                                        <p><small>Email: <?= $user['email'] ?></small></p>
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= base_url('builtin/user/edit-password') ?>">
                                        Change Password
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= base_url('login/logout') ?>">Logout</a>
                                </li>
                            </ul>
                        <?php else: ?>
                            <div class="float-login">
                                <form method="post" action="<?= base_url('login') ?>">
                                    <input type="email" name="email" value="" placeholder="Email" required>
                                    <input type="password" name="password" value="" placeholder="Password" required>
                                    <div class="checkbox">
                                        <label style="font-weight:normal">
                                            <input name="remember" value="1" type="checkbox">&nbsp;&nbsp;Remember me
                                        </label>
                                    </div>
                                    <button type="submit" style="width:100%" class="btn btn-success" name="submit">Submit</button>
                                    <?php
                                    $form_token = $auth->generateFormToken('login_form_token_header');
                                    ?>
                                    <input type="hidden" name="form_token" value="<?= $form_token ?>" />
                                    <input type="hidden" name="login_form_header" value="login_form_header" />
                                </form>
                                <a href="<?= base_url('recovery') ?>">Lupa password?</a>
                            </div>
                        <?php endif; ?>
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
                        echo build_menu($current_module, $list_menu);
                    }
                    ?>
                </nav>
            </div>

            <div class="main-content">
                <div class="container-fluid">
                    <!-- Page content will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>