<div class="legalisasi-module">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-gavel text-primary me-2"></i><?= esc($title) ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Legalisasi</li>
                </ol>
            </nav>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Info Message -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i><?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-users me-2"></i>Pilih Dashboard Legalisasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $visible_dashboards = $visible_dashboards ?? (session()->get('visible_dashboards') ?? []);
                        if (!is_array($visible_dashboards)) {
                            $visible_dashboards = [];
                        }
                        $default_dashboard_url = $default_dashboard_url ?? (session()->get('default_page_url') ?? null);
                        $show_all_dashboards = empty($visible_dashboards);
                        $cardVisible = function (string $slug) use ($visible_dashboards, $show_all_dashboards): bool {
                            return $show_all_dashboards || in_array($slug, $visible_dashboards, true);
                        };
                        ?>

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-column flex-md-row">
                            <div class="mb-2 mb-md-0">
                                <?php if ($default_dashboard_url): ?>
                                    <a href="<?= esc($default_dashboard_url) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i> Dashboard Saya
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div style="max-width: 320px; width: 100%;">
                                <input type="text" id="dashboardFilter" class="form-control form-control-sm" placeholder="Cari dashboard...">
                            </div>
                        </div>

                        <p class="mb-4">Pilih dashboard yang sesuai dengan role dan kewenangan Anda:</p>

                        <div class="row">
                            <?php if ($cardVisible('opd')): ?>
                                <div class="col-md-6 mb-4 dashboard-card" data-keywords="kepala opd building paraf awal">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary">
                                                <i class="fas fa-building me-2"></i>Dashboard Kepala OPD
                                            </h5>
                                            <p class="card-text">
                                                <strong>Kewenangan:</strong><br>
                                                • Paraf awal dokumen (sebelum Kabag)<br>
                                                • Filter: Menunggu Paraf OPD
                                            </p>
                                            <a href="<?= base_url('legalisasi/dashboard/opd') ?>" class="btn btn-primary">
                                                <i class="fas fa-arrow-right me-1"></i>Akses Dashboard OPD
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($cardVisible('kabag')): ?>
                                <div class="col-md-6 mb-4 dashboard-card" data-keywords="kabag hukum user shield paraf">
                                    <div class="card border-left-info">
                                        <div class="card-body">
                                            <h5 class="card-title text-info">
                                                <i class="fas fa-user-shield me-2"></i>Dashboard Kabag Hukum
                                            </h5>
                                            <p class="card-text">
                                                <strong>Kewenangan:</strong><br>
                                                • Paraf setelah OPD, sebelum Asisten<br>
                                                • Filter: Menunggu Paraf Kabag
                                            </p>
                                            <a href="<?= base_url('legalisasi/dashboard/kabag') ?>" class="btn btn-info">
                                                <i class="fas fa-arrow-right me-1"></i>Akses Dashboard Kabag
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($cardVisible('asisten')): ?>
                                <div class="col-md-6 mb-4 dashboard-card" data-keywords="asisten walikota user tie review">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary">
                                                <i class="fas fa-user-tie me-2"></i>Dashboard Asisten Walikota
                                            </h5>
                                            <p class="card-text">
                                                <strong>Kewenangan:</strong><br>
                                                • Paraf: Semua jenis dokumen (Group A & B)<br>
                                                • Review: Setelah paraf OPD dan Kabag
                                            </p>
                                            <a href="<?= base_url('legalisasi/dashboard/asisten') ?>" class="btn btn-primary">
                                                <i class="fas fa-arrow-right me-1"></i>Akses Dashboard Asisten
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($cardVisible('sekda')): ?>
                                <div class="col-md-6 mb-4 dashboard-card" data-keywords="sekda sekretaris tte stamp">
                                    <div class="card border-left-warning">
                                        <div class="card-body">
                                            <h5 class="card-title text-warning">
                                                <i class="fas fa-stamp me-2"></i>Dashboard Sekretaris Daerah
                                            </h5>
                                            <p class="card-text">
                                                <strong>Kewenangan:</strong><br>
                                                • TTE Final: Keputusan Sekda, Instruksi Sekda, SE Sekda<br>
                                                • Paraf: Peraturan Walikota (lanjut ke Wawako)
                                            </p>
                                            <a href="<?= base_url('legalisasi/dashboard/sekda') ?>" class="btn btn-warning">
                                                <i class="fas fa-arrow-right me-1"></i>Akses Dashboard Sekda
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($cardVisible('wawako')): ?>
                                <div class="col-md-6 mb-4 dashboard-card" data-keywords="wakil walikota wawako signature paraf">
                                    <div class="card border-left-info">
                                        <div class="card-body">
                                            <h5 class="card-title text-info">
                                                <i class="fas fa-signature me-2"></i>Dashboard Wakil Walikota
                                            </h5>
                                            <p class="card-text">
                                                <strong>Kewenangan:</strong><br>
                                                • Paraf: Peraturan Walikota (sebelum TTE Walikota)<br>
                                                • Review: Keputusan Walikota, Perda
                                            </p>
                                            <a href="<?= base_url('legalisasi/dashboard/wawako') ?>" class="btn btn-info">
                                                <i class="fas fa-arrow-right me-1"></i>Akses Dashboard Wawako
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($cardVisible('walikota')): ?>
                                <div class="col-md-6 mb-4 dashboard-card" data-keywords="walikota crown tte final">
                                    <div class="card border-left-danger">
                                        <div class="card-body">
                                            <h5 class="card-title text-danger">
                                                <i class="fas fa-crown me-2"></i>Dashboard Walikota
                                            </h5>
                                            <p class="card-text">
                                                <strong>Kewenangan:</strong><br>
                                                • TTE Final: Peraturan Walikota, Keputusan Walikota<br>
                                                • TTE Final: Instruksi Walikota, SE Walikota, Perda
                                            </p>
                                            <a href="<?= base_url('legalisasi/dashboard/walikota') ?>" class="btn btn-danger">
                                                <i class="fas fa-arrow-right me-1"></i>Akses Dashboard Walikota
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('dashboardFilter');
        if (!input) return;
        var cards = Array.prototype.slice.call(document.querySelectorAll('.dashboard-card'));
        input.addEventListener('input', function(e) {
            var term = (e.target.value || '').toLowerCase().trim();
            cards.forEach(function(el) {
                var text = (el.textContent || '').toLowerCase();
                var keys = (el.getAttribute('data-keywords') || '').toLowerCase();
                var match = !term || text.indexOf(term) !== -1 || keys.indexOf(term) !== -1;
                el.style.display = match ? '' : 'none';
            });
        });
    });
</script>

<style>
    /* Legalisasi Module Styling */
    .legalisasi-module {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    /* Border left styling */
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    /* Card styling */
    .card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    /* Button enhancements */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Alert styling */
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>