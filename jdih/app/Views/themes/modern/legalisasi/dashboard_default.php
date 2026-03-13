<div class="legalisasi-module pb-5">
    <!-- Premium Header Section -->
    <div class="header-premium-blue p-4 p-md-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 0 0 2rem 2rem;">
        <div class="position-relative z-1">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-light mb-2">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-white-50">Dashboard</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Legalisasi</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 fw-bold text-white mb-0 font-outfit">
                        <i class="fas fa-gavel me-2 opacity-75"></i>Modul Legalisasi
                    </h1>
                    <p class="text-white-50 mt-2 mb-0">Selamat datang di sistem manajemen legalisasi & Tanda Tangan Elektronik.</p>
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="position-absolute top-0 end-0 p-5 mt-5 opacity-10">
            <i class="fas fa-shield-alt fa-10x text-white rotate-12"></i>
        </div>
    </div>

    <div class="container-fluid px-md-4">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-soft-success border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-3 fs-4"></i>
                    <div><?= esc(session()->getFlashdata('success')) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Selection Card -->
        <div class="glass-card mb-4 border-top border-4 border-blue-premium">
            <div class="p-4 border-bottom bg-white d-sm-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold font-outfit mb-1 text-blue"><i class="fas fa-users-cog me-2"></i>Pilih Dashboard Role</h5>
                    <p class="text-muted small mb-0">Pilih dashboard yang sesuai dengan kewenangan Anda saat ini.</p>
                </div>
                <div class="mt-3 mt-sm-0 px-3 py-2 bg-light rounded-pill border d-flex align-items-center" style="min-width: 300px;">
                    <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" id="dashboardFilter" class="form-control form-control-sm border-0 bg-transparent p-0" placeholder="Cari kewenangan atau dashboard...">
                </div>
            </div>
            
            <div class="p-4">
                <?php
                $visible_dashboards = $visible_dashboards ?? (session()->get('visible_dashboards') ?? []);
                if (!is_array($visible_dashboards)) { $visible_dashboards = []; }
                $default_dashboard_url = $default_dashboard_url ?? (session()->get('default_page_url') ?? null);
                $show_all_dashboards = empty($visible_dashboards);
                $cardVisible = function (string $slug) use ($visible_dashboards, $show_all_dashboards): bool {
                    return $show_all_dashboards || in_array($slug, $visible_dashboards, true);
                };
                ?>

                <?php if ($default_dashboard_url): ?>
                    <div class="mb-4 text-center">
                        <a href="<?= esc($default_dashboard_url) ?>" class="btn btn-blue-premium py-2 px-4 rounded-pill shadow-blue transition-all">
                            <i class="fas fa-rocket me-2"></i>Masuk Ke Dashboard Utama Saya
                        </a>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <?php 
                    $roles = [
                        'opd' => ['title' => 'Dashboard Kepala OPD', 'icon' => 'fa-building', 'color' => 'blue', 'keywords' => 'opd instansi perangkat daerah paraf awal'],
                        'kabag' => ['title' => 'Dashboard Kabag Hukum', 'icon' => 'fa-user-shield', 'color' => 'indigo', 'keywords' => 'kabag hukum legal verifikasi paraf'],
                        'asisten' => ['title' => 'Dashboard Asisten Walikota', 'icon' => 'fa-user-tie', 'color' => 'cyan', 'keywords' => 'asisten walikota review paraf'],
                        'sekda' => ['title' => 'Dashboard Sekretaris Daerah', 'icon' => 'fa-stamp', 'color' => 'orange', 'keywords' => 'sekda tte final sekretaris daerah'],
                        'wawako' => ['title' => 'Dashboard Wakil Walikota', 'icon' => 'fa-signature', 'color' => 'teal', 'keywords' => 'wawako wakil walikota paraf review'],
                        'walikota' => ['title' => 'Dashboard Walikota', 'icon' => 'fa-crown', 'color' => 'red', 'keywords' => 'walikota tte final pimpinan']
                    ];
                    ?>

                    <?php foreach ($roles as $slug => $data): ?>
                        <?php if ($cardVisible($slug)): ?>
                            <div class="col-xl-4 col-md-6 dashboard-card" data-keywords="<?= $data['keywords'] ?>">
                                <div class="glass-card h-100 shadow-hover transition-all-slow border-0">
                                    <div class="p-4 text-center position-relative">
                                        <div class="role-icon-bg bg-soft-<?= $data['color'] ?> mx-auto mb-3">
                                            <i class="fas <?= $data['icon'] ?> text-<?= $data['color'] ?> fs-2"></i>
                                        </div>
                                        <h5 class="fw-bold font-outfit mb-2 text-dark"><?= $data['title'] ?></h5>
                                        <p class="text-muted small mb-4 px-2">Akses modul verifikasi, paraf, dan tanda tangan elektronik sesuai role <?= $data['title'] ?>.</p>
                                        <a href="<?= base_url('legalisasi/dashboard/' . $slug) ?>" class="btn btn-soft-<?= $data['color'] ?> w-100 rounded-pill fw-bold py-2">
                                            Masuk Dashboard <i class="fas fa-arrow-right ms-1 small"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --blue-premium: #2563eb;
        --blue-soft: #eff6ff;
        --indigo-premium: #4f46e5;
        --indigo-soft: #eef2ff;
        --cyan-premium: #0891b2;
        --cyan-soft: #ecfeff;
        --orange-premium: #ea580c;
        --orange-soft: #fff7ed;
        --teal-premium: #0d9488;
        --teal-soft: #f0fdfa;
        --red-premium: #dc2626;
        --red-soft: #fef2f2;
    }

    .font-outfit { font-family: 'Outfit', sans-serif; }
    .transition-all { transition: all 0.3s ease; }
    .transition-all-slow { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    .shadow-hover:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important; }
    .shadow-blue { box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    }

    .role-icon-bg { width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 20px; transition: all 0.3s ease; }
    .dashboard-card:hover .role-icon-bg { transform: scale(1.1) rotate(5deg); }

    .bg-soft-blue { background: var(--blue-soft); }
    .text-blue { color: var(--blue-premium); }
    .btn-soft-blue { background: var(--blue-soft); color: var(--blue-premium); border: 1px solid transparent; }
    .btn-soft-blue:hover { background: var(--blue-premium); color: white; }

    .bg-soft-indigo { background: var(--indigo-soft); }
    .text-indigo { color: var(--indigo-premium); }
    .btn-soft-indigo { background: var(--indigo-soft); color: var(--indigo-premium); border: 1px solid transparent; }
    .btn-soft-indigo:hover { background: var(--indigo-premium); color: white; }

    .bg-soft-cyan { background: var(--cyan-soft); }
    .text-cyan { color: var(--cyan-premium); }
    .btn-soft-cyan { background: var(--cyan-soft); color: var(--cyan-premium); border: 1px solid transparent; }
    .btn-soft-cyan:hover { background: var(--cyan-premium); color: white; }

    .bg-soft-orange { background: var(--orange-soft); }
    .text-orange { color: var(--orange-premium); }
    .btn-soft-orange { background: var(--orange-soft); color: var(--orange-premium); border: 1px solid transparent; }
    .btn-soft-orange:hover { background: var(--orange-premium); color: white; }

    .bg-soft-teal { background: var(--teal-soft); }
    .text-teal { color: var(--teal-premium); }
    .btn-soft-teal { background: var(--teal-soft); color: var(--teal-premium); border: 1px solid transparent; }
    .btn-soft-teal:hover { background: var(--teal-premium); color: white; }

    .bg-soft-red { background: var(--red-soft); }
    .text-red { color: var(--red-premium); }
    .btn-soft-red { background: var(--red-soft); color: var(--red-premium); border: 1px solid transparent; }
    .btn-soft-red:hover { background: var(--red-premium); color: white; }

    .btn-blue-premium { background: var(--blue-premium); color: white; border: none; font-weight: 600; }
    .btn-blue-premium:hover { background: #1e40af; color: white; transform: translateY(-3px); }

    .alert-soft-success { background-color: #f0fdf4; color: #166534; border-left: 5px solid #22c55e; }
    .breadcrumb-light .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.4); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('dashboardFilter');
        if (!input) return;
        const cards = Array.from(document.querySelectorAll('.dashboard-card'));
        input.addEventListener('input', function(e) {
            const term = (e.target.value || '').toLowerCase().trim();
            cards.forEach(function(el) {
                const text = (el.textContent || '').toLowerCase();
                const keywords = (el.getAttribute('data-keywords') || '').toLowerCase();
                const match = !term || text.includes(term) || keywords.includes(term);
                el.style.display = match ? '' : 'none';
                if (match) {
                    el.classList.add('animate__animated', 'animate__fadeIn');
                }
            });
        });
    });
</script>