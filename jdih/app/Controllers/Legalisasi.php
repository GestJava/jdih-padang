<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiDokumenModel;
use App\Models\HarmonisasiHistoriModel;
use App\Models\HarmonisasiTteLogModel;
use App\Models\HarmonisasiJenisPeraturanModel;
use App\Config\HarmonisasiStatus;
use App\Services\LegalisasiTTEService;
use App\Services\TteService;

class Legalisasi extends BaseController
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiDokumenModel;
    protected $harmonisasiHistoriModel;
    protected $harmonisasiTteLogModel;
    protected $jenisPeraturanModel;
    protected $db;
    protected $tteService;

    public function __construct()
    {
        parent::__construct();

        // ============================================================
        // PERMISSION-BASED ACCESS CONTROL - LEGALISASI MODULE
        // ============================================================
        $this->mustLoggedIn();

        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->harmonisasiDokumenModel = new HarmonisasiDokumenModel();
        $this->harmonisasiHistoriModel = new HarmonisasiHistoriModel();
        $this->harmonisasiTteLogModel = new HarmonisasiTteLogModel();
        $this->jenisPeraturanModel = new HarmonisasiJenisPeraturanModel();
        $this->db = \Config\Database::connect();
        $this->tteService = new TteService();

        helper(['form', 'url', 'filesystem']);

        // Add DataTables extensions
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js'));
        $this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css'));

        // Load Standardized Ultra Premium CSS
        $this->addStyle(base_url('jdih/assets/css/harmonisasi-module.css?v=' . time()));

        // Setup visible dashboards per user (RBAC for views and guards)
        try {
            // Ensure user data is available
            if (!$this->user || !is_array($this->user)) {
                log_message('error', 'Legalisasi constructor: User data not available');
                $this->user = [];
            }
            $visibleDashboards = $this->getVisibleDashboardsForUser($this->user);
            session()->set('visible_dashboards', $visibleDashboards);
        } catch (\Throwable $t) {
            log_message('error', 'Legalisasi constructor error: ' . $t->getMessage());
            // no-op if session not ready
        }
    }

    /**
     * Dashboard utama legalisasi
     * - Admin: Tampilkan halaman pilihan dashboard (dashboard_default)
     * - User lain: Langsung redirect ke dashboard sesuai role
     */
    public function index()
    {
        try {
            $user = session()->get('user');
            if (!$user || !isset($user['id_user'])) {
                return redirect()->to('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
            }

            // Cek apakah user adalah admin (punya akses ke semua dashboard)
            $isAdmin = false;
            $roles = $user['role'] ?? [];
            foreach ($roles as $role) {
                $name = strtolower((string) ($role['nama_role'] ?? ''));
                if (strpos($name, 'admin') !== false) {
                    $isAdmin = true;
                    break;
                }
            }

            // Dapatkan dashboard yang bisa diakses user
            $visibleDashboards = $this->getVisibleDashboardsForUser($user);
            
            if (empty($visibleDashboards)) {
                // Jika tidak ada dashboard yang bisa diakses, tampilkan pesan error
                return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke modul legalisasi.');
            }

            // Jika admin, tampilkan halaman pilihan dashboard
            if ($isAdmin) {
                $this->data['title'] = 'Dashboard Legalisasi';
                $this->data['user'] = $user;
                return $this->view('legalisasi/dashboard_default', $this->data);
            }

            // Untuk user non-admin, langsung redirect ke dashboard sesuai prioritas role
            // Prioritas: walikota > sekda > wawako > asisten > kabag > opd
            $dashboardPriority = ['walikota', 'sekda', 'wawako', 'asisten', 'kabag', 'opd'];
            
            $defaultDashboard = null;
            foreach ($dashboardPriority as $dashboard) {
                if (in_array($dashboard, $visibleDashboards, true)) {
                    $defaultDashboard = $dashboard;
                    break;
                }
            }
            
            // Jika tidak ada yang match dengan priority, ambil yang pertama
            if (!$defaultDashboard && !empty($visibleDashboards)) {
                $defaultDashboard = $visibleDashboards[0];
            }

            // Redirect ke dashboard sesuai role
            switch ($defaultDashboard) {
                case 'walikota':
                    return redirect()->to('/legalisasi/dashboard-walikota');
                case 'sekda':
                    return redirect()->to('/legalisasi/dashboard-sekda');
                case 'wawako':
                    return redirect()->to('/legalisasi/dashboard-wawako');
                case 'asisten':
                    return redirect()->to('/legalisasi/dashboard-asisten');
                case 'kabag':
                    return redirect()->to('/legalisasi/dashboard-kabag');
                case 'opd':
                    return redirect()->to('/legalisasi/dashboard-opd');
                default:
                    // Fallback: tampilkan dashboard default jika tidak ada yang match
                    $this->data['title'] = 'Dashboard Legalisasi';
                    $this->data['user'] = $user;
                    return $this->view('legalisasi/dashboard_default', $this->data);
            }
        } catch (\Exception $e) {
            log_message('error', 'Legalisasi index error: ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'Terjadi kesalahan dalam memuat halaman legalisasi.');
        }
    }

    /**
     * Dashboard untuk Sekretaris Daerah
     */
    public function dashboardSekda()
    {
        try {
            if (!$this->isDashboardAllowed('sekda')) {
                return redirect()->to('/legalisasi')->with('error', 'Anda tidak berhak mengakses dashboard Sekda.');
            }
            $this->data['title'] = 'Dashboard Legalisasi - Sekretaris Daerah';
            $this->data['user_role'] = 'sekda';

            // Ambil data sesuai kewenangan:
            // - pending_tte: Keputusan Sekda (TTE langsung selesai)
            // - pending_paraf: Jenis lain (paraf lanjut ke Wawako)
            $this->data['pending_tte'] = $this->getAjuanForSekdaTTE();
            $this->data['pending_paraf'] = $this->getAjuanForSekdaParaf();

            // Debug: Log data untuk troubleshooting
            log_message('debug', 'Dashboard Sekda data: ' . json_encode([
                'pending_tte_count' => count($this->data['pending_tte']),
                'pending_paraf_count' => count($this->data['pending_paraf']),
                'pending_tte_types' => array_column($this->data['pending_tte'], 'nama_jenis'),
                'pending_paraf_types' => array_column($this->data['pending_paraf'], 'nama_jenis')
            ]));

            // Debug: Check all documents with status PARAF_SEKDA
            $allParafSekda = $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.id, ha.judul_peraturan, j.nama_jenis, ha.id_status_ajuan")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_SEKDA)
                ->get()
                ->getResultArray();

            log_message('debug', 'All PARAF_SEKDA documents: ' . json_encode($allParafSekda));

            // Hitung statistik untuk Sekda (global - semua instansi, semua jenis, tahun berjalan)
            $currentYear = date('Y');
            
            // 1. TTE Sekda (Final) - Group A: Keputusan Sekda, Instruksi Sekda, SE Sekda
            $pendingTteSekda = count($this->data['pending_tte']);
            
            // 2. Paraf Sekda - Group B: Perwal, Kepwal, dll yang lanjut ke Wawako
            $pendingParafSekda = count($this->data['pending_paraf']);
            
            // 3. Total Ajuan (semua status) di tahun ini - global
            $totalAjuan = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->countAllResults();
            
            // 4. Selesai (status 14, 15) di tahun ini - global
            $selesai = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::SELESAI,  // 14
                    HarmonisasiStatus::DITOLAK   // 15
                ])
                ->countAllResults();

            // Statistik untuk Sekda
            $this->data['stats'] = [
                'pending_tte_sekda' => $pendingTteSekda,
                'pending_paraf_sekda' => $pendingParafSekda,
                'total_ajuan' => $totalAjuan,
                'selesai' => $selesai
            ];

            return $this->view('legalisasi/dashboard_sekda', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Sekda error: ' . $e->getMessage());
            return redirect()->to('/legalisasi')->with('error', 'Terjadi kesalahan dalam memuat dashboard Sekda.');
        }
    }

    /**
     * Dashboard untuk Walikota
     */
    public function dashboardWalikota()
    {
        try {
            if (!$this->isDashboardAllowed('walikota')) {
                return redirect()->to('/legalisasi')->with('error', 'Anda tidak berhak mengakses dashboard Walikota.');
            }
            $this->data['title'] = 'Dashboard Legalisasi - Walikota';
            $this->data['user_role'] = 'walikota';

            // Ambil data sesuai kewenangan
            $this->data['pending_tte'] = $this->getAjuanForWalikotaTTE();   // Group B

            // Hitung statistik untuk Walikota (global - semua instansi, tahun berjalan)
            $currentYear = date('Y');
            
            // 1. Menunggu TTE Walikota (status 13) - Group B
            $pendingWalikotaTte = count($this->data['pending_tte']);
            
            // 2. Total Ajuan (semua status) di tahun ini - global
            $totalAjuan = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->countAllResults();
            
            $tteTahunIni = $this->db->table('harmonisasi_ajuan ha')
                ->join('harmonisasi_nomor_peraturan np', 'ha.id = np.id_ajuan', 'inner')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::SELESAI)
                ->where('YEAR(ha.updated_at)', $currentYear)
                ->where('np.tte_file_path IS NOT NULL')
                ->countAllResults();
            
            // 4. Selesai (status 14, 15) di tahun ini - global
            $selesai = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::SELESAI,  // 14
                    HarmonisasiStatus::DITOLAK   // 15
                ])
                ->countAllResults();

            // Statistik untuk Walikota
            $this->data['stats'] = [
                'pending_walikota_tte' => $pendingWalikotaTte,
                'total_ajuan' => $totalAjuan,
                'tte_tahun_ini' => $tteTahunIni,
                'selesai' => $selesai
            ];

            // Preview next numbering sequences
            $this->data['next_numbers'] = $this->getNextNumbersPreview();

            return $this->view('legalisasi/dashboard_walikota', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Walikota error: ' . $e->getMessage());
            return redirect()->to('/legalisasi')->with('error', 'Terjadi kesalahan dalam memuat dashboard Walikota.');
        }
    }

    /**
     * Dashboard untuk Kepala OPD
     */
    public function dashboardOpd()
    {
        try {
            if (!$this->isDashboardAllowed('opd')) {
                return redirect()->to('/legalisasi')->with('error', 'Anda tidak berhak mengakses dashboard OPD.');
            }

            // Initialize data array
            $this->data['title'] = 'Dashboard Legalisasi - Kepala OPD';
            $this->data['user_role'] = 'opd';

            // Check if user is admin
            $isAdmin = false;
            if ($this->user && isset($this->user['role']) && is_array($this->user['role'])) {
                foreach ($this->user['role'] as $role) {
                    if (isset($role['nama_role']) && strpos(strtolower($role['nama_role']), 'admin') !== false) {
                        $isAdmin = true;
                        break;
                    }
                }
            }

            // For admin: show all OPD data, for regular OPD: show only their instansi
            $instansiId = null;
            if (!$isAdmin) {
                // Ensure user data is available for non-admin
                if (!$this->user || !isset($this->user['id_instansi'])) {
                    log_message('error', 'Dashboard OPD: User data not available');
                    return redirect()->to('/legalisasi')->with('error', 'Data user tidak tersedia. Silakan login ulang.');
                }
                $instansiId = (int) $this->user['id_instansi'];
            }

            // Ambil data sesuai kewenangan
            if ($isAdmin) {
                // Admin: get all OPD paraf data
                $this->data['pending_paraf'] = $this->db->table('harmonisasi_ajuan ha')
                    ->select("ha.*, ha.id as id_ajuan, i.nama_instansi, u.nama as nama_pemohon, s.nama_status, j.nama_jenis, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
                    ->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left')
                    ->join('user u', 'u.id_user = ha.id_user_pemohon', 'left')
                    ->join('harmonisasi_status s', 's.id = ha.id_status_ajuan', 'left')
                    ->join('harmonisasi_jenis_peraturan j', 'j.id = ha.id_jenis_peraturan', 'left')
                    ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_OPD)
                    ->orderBy('ha.updated_at', 'DESC')
                    ->get()
                    ->getResultArray();
            } else {
                $this->data['pending_paraf'] = $this->getAjuanForOpdParaf($instansiId);
            }

            if (!empty($this->data['pending_paraf'])) {
                log_message('debug', 'Dashboard OPD first item keys: ' . json_encode(array_keys($this->data['pending_paraf'][0])));
            }

            // Hitung statistik untuk OPD berdasarkan instansi dan tahun berjalan
            $currentYear = date('Y');
            
            // 1. Menunggu Paraf OPD (status 7)
            $query1 = $this->harmonisasiAjuanModel->where('id_status_ajuan', HarmonisasiStatus::PARAF_OPD);
            if (!$isAdmin && $instansiId) {
                $query1->where('id_instansi_pemohon', $instansiId);
            }
            $pendingOpdParaf = $query1->countAllResults();
            
            // 2. Total Ajuan Instansi (semua status) di tahun ini
            $query2 = $this->harmonisasiAjuanModel->where('YEAR(created_at)', $currentYear);
            if (!$isAdmin && $instansiId) {
                $query2->where('id_instansi_pemohon', $instansiId);
            }
            $totalAjuanInstansi = $query2->countAllResults();
            
            // 3. Dalam Proses (status 8, 9, 11, 12, 13) - setelah paraf OPD
            $query3 = $this->harmonisasiAjuanModel->whereIn('id_status_ajuan', [
                HarmonisasiStatus::PARAF_KABAG,      // 8
                HarmonisasiStatus::PARAF_ASISTEN,    // 9
                HarmonisasiStatus::PARAF_SEKDA,      // 11
                HarmonisasiStatus::PARAF_WAWAKO,     // 12
                HarmonisasiStatus::TTE_WALIKOTA      // 13
            ]);
            if (!$isAdmin && $instansiId) {
                $query3->where('id_instansi_pemohon', $instansiId);
            }
            $dalamProses = $query3->countAllResults();
            
            // 4. Selesai (status 14, 15) di tahun ini
            $query4 = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::SELESAI,  // 14
                    HarmonisasiStatus::DITOLAK   // 15
                ]);
            if (!$isAdmin && $instansiId) {
                $query4->where('id_instansi_pemohon', $instansiId);
            }
            $selesai = $query4->countAllResults();

            // Statistik untuk OPD
            $this->data['stats'] = [
                'pending_opd_paraf' => $pendingOpdParaf,
                'total_ajuan_instansi' => $totalAjuanInstansi,
                'dalam_proses' => $dalamProses,
                'selesai' => $selesai
            ];

            // Debug: Log data being passed to view
            log_message('debug', 'Dashboard OPD data: ' . json_encode([
                'user_role' => $this->data['user_role'],
                'pending_count' => count($this->data['pending_paraf']),
                'user_id' => $this->user['id_user'] ?? 'N/A',
                'is_admin' => $isAdmin
            ]));

            return $this->view('legalisasi/dashboard_opd', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard OPD error: ' . $e->getMessage());
            log_message('error', 'Detail error: ' . $e->getTraceAsString());
            return redirect()->to('/legalisasi')->with('error', 'Terjadi kesalahan dalam memuat dashboard OPD.');
        }
    }

    /**
     * Dashboard untuk Kabag Hukum
     */
    public function dashboardKabag()
    {
        try {
            if (!$this->isDashboardAllowed('kabag')) {
                return redirect()->to('/legalisasi')->with('error', 'Anda tidak berhak mengakses dashboard Kabag.');
            }
            $this->data['title'] = 'Dashboard Legalisasi - Kabag Hukum';
            $this->data['user_role'] = 'kabag';

            // Ambil data sesuai kewenangan
            $this->data['pending_paraf'] = $this->getAjuanForKabagParaf();

            // Hitung statistik untuk Kabag (global - semua instansi, tahun berjalan)
            $currentYear = date('Y');
            
            // 1. Menunggu Paraf Kabag (status 8) - semua instansi
            $pendingKabagParaf = $this->harmonisasiAjuanModel
                ->where('id_status_ajuan', HarmonisasiStatus::PARAF_KABAG)
                ->countAllResults();
            
            // 2. Total Ajuan (semua status) di tahun ini - global
            $totalAjuan = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->countAllResults();
            
            // 3. Dalam Proses (status 9, 11, 12, 13) - setelah paraf Kabag
            $dalamProses = $this->harmonisasiAjuanModel
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::PARAF_ASISTEN,    // 9
                    HarmonisasiStatus::PARAF_SEKDA,      // 11
                    HarmonisasiStatus::PARAF_WAWAKO,     // 12
                    HarmonisasiStatus::TTE_WALIKOTA      // 13
                ])
                ->countAllResults();
            
            // 4. Selesai (status 14, 15) di tahun ini - global
            $selesai = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::SELESAI,  // 14
                    HarmonisasiStatus::DITOLAK   // 15
                ])
                ->countAllResults();

            // Statistik untuk Kabag
            $this->data['stats'] = [
                'pending_kabag_paraf' => $pendingKabagParaf,
                'total_ajuan' => $totalAjuan,
                'dalam_proses' => $dalamProses,
                'selesai' => $selesai
            ];

            return $this->view('legalisasi/dashboard_kabag', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Kabag error: ' . $e->getMessage());
            return redirect()->to('/legalisasi')->with('error', 'Terjadi kesalahan dalam memuat dashboard Kabag.');
        }
    }

    /**
     * Dashboard untuk Asisten Walikota
     */
    public function dashboardAsisten()
    {
        try {
            if (!$this->isDashboardAllowed('asisten')) {
                return redirect()->to('/legalisasi')->with('error', 'Anda tidak berhak mengakses dashboard Asisten.');
            }
            $this->data['title'] = 'Dashboard Legalisasi - Asisten Walikota';
            $this->data['user_role'] = 'asisten';

            // Ambil data sesuai kewenangan - SEMUA JENIS DOKUMEN
            $this->data['pending_paraf'] = $this->getAjuanForAsistenParaf();

            // Hitung statistik untuk Asisten (global - semua instansi, semua jenis, tahun berjalan)
            $currentYear = date('Y');
            
            // 1. Menunggu Paraf Asisten (status 9) - semua instansi, semua jenis
            $pendingAsistenParaf = $this->harmonisasiAjuanModel
                ->where('id_status_ajuan', HarmonisasiStatus::PARAF_ASISTEN)
                ->countAllResults();
            
            // 2. Total Ajuan (semua status) di tahun ini - global
            $totalAjuan = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->countAllResults();
            
            // 3. Dalam Proses (status 11, 12, 13) - setelah paraf Asisten
            $dalamProses = $this->harmonisasiAjuanModel
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::PARAF_SEKDA,      // 11
                    HarmonisasiStatus::PARAF_WAWAKO,     // 12
                    HarmonisasiStatus::TTE_WALIKOTA      // 13
                ])
                ->countAllResults();
            
            // 4. Selesai (status 14, 15) di tahun ini - global
            $selesai = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::SELESAI,  // 14
                    HarmonisasiStatus::DITOLAK   // 15
                ])
                ->countAllResults();

            // Statistik untuk Asisten
            $this->data['stats'] = [
                'pending_asisten_paraf' => $pendingAsistenParaf,
                'total_ajuan' => $totalAjuan,
                'dalam_proses' => $dalamProses,
                'selesai' => $selesai
            ];

            // Debug: Log data untuk troubleshooting
            log_message('debug', 'Dashboard Asisten - Stats: ' . json_encode($this->data['stats']));
            log_message('debug', 'Dashboard Asisten - Pending Paraf count: ' . count($this->data['pending_paraf'] ?? []));
            log_message('debug', 'Dashboard Asisten - Pending Paraf data: ' . json_encode(array_map(function($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'judul' => $item['judul_peraturan'] ?? null,
                    'jenis' => $item['nama_jenis'] ?? null,
                    'status' => $item['id_status_ajuan'] ?? null
                ];
            }, $this->data['pending_paraf'] ?? [])));

            return $this->view('legalisasi/dashboard_asisten', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Asisten error: ' . $e->getMessage());
            return redirect()->to('/legalisasi')->with('error', 'Terjadi kesalahan dalam memuat dashboard Asisten.');
        }
    }

    /**
     * Dashboard untuk Wakil Walikota
     */
    public function dashboardWawako()
    {
        try {
            if (!$this->isDashboardAllowed('wawako')) {
                return redirect()->to('/legalisasi')->with('error', 'Anda tidak berhak mengakses dashboard Wawako.');
            }
            $this->data['title'] = 'Dashboard Legalisasi - Wakil Walikota';
            $this->data['user_role'] = 'wawako';

            // Ambil data sesuai kewenangan
            $this->data['pending_paraf'] = $this->getAjuanForWawakoParaf(); // Group B

            // Hitung statistik untuk Wawako (global - semua instansi, tahun berjalan)
            $currentYear = date('Y');
            
            // 1. Menunggu Paraf Wawako (status 12) - Group B
            $pendingWawakoParaf = count($this->data['pending_paraf']);
            
            // 2. Total Ajuan (semua status) di tahun ini - global
            $totalAjuan = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->countAllResults();
            
            // 3. Dalam Proses (status 13 - TTE_WALIKOTA) - setelah paraf Wawako
            $dalamProses = $this->harmonisasiAjuanModel
                ->where('id_status_ajuan', HarmonisasiStatus::TTE_WALIKOTA)
                ->countAllResults();
            
            // 4. Selesai (status 14, 15) di tahun ini - global
            $selesai = $this->harmonisasiAjuanModel
                ->where('YEAR(created_at)', $currentYear)
                ->whereIn('id_status_ajuan', [
                    HarmonisasiStatus::SELESAI,  // 14
                    HarmonisasiStatus::DITOLAK   // 15
                ])
                ->countAllResults();

            // Statistik untuk Wawako
            $this->data['stats'] = [
                'pending_wawako_paraf' => $pendingWawakoParaf,
                'total_ajuan' => $totalAjuan,
                'dalam_proses' => $dalamProses,
                'selesai' => $selesai
            ];

            // Recent activities
            $this->data['recent_activities'] = $this->getRecentWawakoActivities();

            return $this->view('legalisasi/dashboard_wawako', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Wawako error: ' . $e->getMessage());
            return redirect()->to('/legalisasi')->with('error', 'Terjadi kesalahan dalam memuat dashboard Wawako.');
        }
    }


    /**
     * Process TTE dengan integrasi BSrE - Enhanced untuk TTE Sekda
     */
    public function processTTE($ajuan_id)
    {
        try {
            // Validasi akses
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ajuan tidak ditemukan']);
            }

            if (!$this->canAccessAjuan($ajuan)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak berhak mengakses ajuan tersebut']);
            }

            // Validasi user actions
            $user_actions = $this->getUserActionsForDetail($ajuan);
            if (!$user_actions['can_process_tte']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak dapat memproses TTE untuk ajuan ini']);
            }

            // Ambil data user yang akan melakukan TTE
            $user = session('user');
            $user_role = $user['nama_role'] ?? null;

            // Validasi khusus untuk TTE Sekda - Keputusan Sekda
            $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
            $namaJenis = $jenisPeraturan['nama_jenis'] ?? '';

            // Cek apakah ini adalah Keputusan Sekda yang memerlukan TTE final
            $isKeputusanSekda = $this->isSekdaFinalDocument($namaJenis);

            if ($user_role === 'sekda' && $isKeputusanSekda) {
                // Validasi tambahan untuk TTE Sekda - Keputusan Sekda
                if ($ajuan['id_status_ajuan'] != HarmonisasiStatus::PARAF_SEKDA) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Dokumen belum siap untuk TTE Sekda. Status harus "Menunggu Paraf/TTE Sekda"'
                    ]);
                }

                // Log khusus untuk TTE Sekda
                log_message('info', 'TTE Sekda dimulai untuk Keputusan Sekda - Ajuan ID: ' . $ajuan_id . ', Jenis: ' . $namaJenis);
            }

            // Validasi NIK dan password dari request
            $nik = $this->request->getPost('nik');
            // Support 'password' dan 'passphrase' untuk kompatibilitas
            $password = $this->request->getPost("password") ?? $this->request->getPost("passphrase");

            if (!$nik || !$password) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'NIK dan password TTE harus diisi',
                    'requires_tte_data' => true
                ]);
            }

            // Validasi format NIK
            if (!preg_match('/^\d{16}$/', $nik)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'NIK harus terdiri dari 16 digit angka'
                ]);
            }

            // Cek status sertifikat TTE user
            $certificateStatus = $this->tteService->checkUserStatus($nik);
            if ($certificateStatus === false) {
                $error = $this->tteService->getLastError();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal memverifikasi sertifikat TTE: ' . ($error['message'] ?? 'Unknown error')
                ]);
            }

            // Validasi status sertifikat
            if (isset($certificateStatus['status']) && $certificateStatus['status'] !== 'ACTIVE') {
                $statusMessage = 'Sertifikat TTE tidak aktif';
                if ($certificateStatus['status'] === 'EXPIRED') {
                    $statusMessage = 'Sertifikat TTE telah kadaluarsa';
                } elseif ($certificateStatus['status'] === 'REVOKED') {
                    $statusMessage = 'Sertifikat TTE telah dicabut';
                }

                return $this->response->setJSON([
                    'success' => false,
                    'message' => $statusMessage . '. Silakan perbarui sertifikat Anda di BSrE.'
                ]);
            }

            // Ambil dokumen yang akan ditandatangani (dokumen FINAL_PARAF yang sudah siap untuk TTE)
            $dokumen = $this->harmonisasiDokumenModel
                ->where('id_ajuan', $ajuan_id)
                ->where('tipe_dokumen', 'FINAL_PARAF')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$dokumen) {
                return $this->response->setJSON(['success' => false, 'message' => 'Dokumen FINAL_PARAF tidak ditemukan. Pastikan dokumen sudah diparaf dan siap untuk TTE.']);
            }

            // Path dokumen - perbaiki path sesuai dengan struktur yang benar
            $filePath = $dokumen['path_file_storage'];

            // Remove 'uploads/' prefix if it exists
            if (strpos($filePath, 'uploads/') === 0) {
                $filePath = substr($filePath, 8);
            }

            // Construct the full path
            $documentPath = WRITEPATH . 'uploads/' . $filePath;
            // Ensure path consistency
            $documentPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $documentPath);

            // Log the path for debugging
            log_message('info', 'Looking for document at path: ' . $documentPath);
            log_message('info', 'File path from database: ' . $dokumen['path_file_storage']);

            if (!file_exists($documentPath)) {
                log_message('error', 'File TTE tidak ditemukan: ' . $documentPath);
                log_message('error', 'WRITEPATH: ' . WRITEPATH);
                log_message('error', 'Constructed path: ' . $documentPath);
                return $this->response->setJSON(['success' => false, 'message' => 'File dokumen tidak ditemukan di server. Path: ' . $filePath]);
            }

            log_message('info', 'Document found successfully: ' . $documentPath);

            // Log request TTE
            $logId = $this->harmonisasiTteLogModel->logRequest([
                'id_ajuan' => $ajuan_id,
                'id_user' => $user['id_user'],
                'action' => 'TTE_REQUEST',
                'status' => 'PENDING',
                'document_number' => null,
                'signed_path' => null,
                'error_message' => null
            ]);

            // Generate metadata untuk enhancement - khusus untuk TTE Sekda
            $metadata = $this->generateTTEMetadata($ajuan, $user_role, $isKeputusanSekda);

            // Enhance PDF terlebih dahulu (seperti di modul test)
            $pdfEnhancementService = new \App\Services\PdfEnhancementService();
            $enhancedPath = $pdfEnhancementService->enhancePdf(
                $documentPath,
                $metadata['nomor_peraturan'],
                $metadata['tanggal_pengesahan'],
                $metadata['document_url'],
                80 // QR size
            );

            if (!$enhancedPath) {
                throw new \Exception('Gagal enhance PDF dengan nomor, tanggal, dan QR code');
            }

            // Kemudian proses TTE dengan dokumen yang sudah di-enhance
            // Jika testing mode, gunakan mock service
            $envConfig = new \Config\Environment();
            if ($envConfig->isTteTestingMode()) {
                $testingService = new \App\Services\TteTestingService();
                $tteResult = $testingService->mockSignDocument($nik, $password, $enhancedPath, $metadata);
            } else {
                $tteResult = $this->tteService->signDocument(
                    $nik,
                    $password,
                    $enhancedPath,
                    100, // x position
                    100, // y position
                    200, // width
                    100, // height
                    false // is specimen
                );
            }

            // Handle error untuk testing mode dan production mode
            if ($tteResult === false || (is_array($tteResult) && !($tteResult['success'] ?? true))) {
                $errorMessage = 'Unknown error';

                if (is_array($tteResult) && isset($tteResult['message'])) {
                    // Testing mode error
                    $errorMessage = $tteResult['message'];
                } else {
                    // Production mode error
                    $error = $this->tteService->getLastError();
                    $errorMessage = $error['message'] ?? 'Unknown error';
                }

                // Update log dengan error
                $this->harmonisasiTteLogModel->updateResponse($logId, [
                    'status' => 'FAILED',
                    'error_message' => $errorMessage
                ]);

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menandatangani dokumen: ' . $errorMessage
                ]);
            }

            // Update log dengan success
            $signedPath = null;
            if (is_array($tteResult)) {
                // Testing mode
                $signedPath = $tteResult['signed_document_path'] ?? $tteResult['enhanced_document_path'] ?? null;
            } else {
                // Production mode
                $signedPath = $tteResult['signed_document_path'] ?? null;
            }

            $this->harmonisasiTteLogModel->updateResponse($logId, [
                'status' => 'SUCCESS',
                'document_number' => $metadata['nomor_peraturan'] ?? null,
                'signed_path' => $signedPath,
                'error_message' => null
            ]);

            // Simpan dokumen hasil TTE jika ada (prioritaskan enhanced document)
            $signedDocumentPath = $tteResult['enhanced_document_path'] ?? $tteResult['signed_document_path'] ?? null;
            if ($signedDocumentPath && file_exists($signedDocumentPath)) {
                $this->saveSignedDocument($ajuan_id, $dokumen, $signedDocumentPath, $user['id_user'], $metadata);
            }

            // Update status ajuan berdasarkan role - Enhanced untuk TTE Sekda
            $new_status = null;
            $tte_message = '';

            switch ($user_role) {
                case 'sekda':
                    // Cek jenis peraturan untuk menentukan status berikutnya
                    $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
                    $namaJenis = $jenisPeraturan['nama_jenis'] ?? '';

                    if ($this->isSekdaFinalDocument($namaJenis)) {
                        $new_status = 14; // Keputusan Sekda: TTE langsung selesai
                        $tte_message = 'Dokumen ' . $namaJenis . ' telah ditandatangani elektronik (TTE) oleh Sekretaris Daerah dan SELESAI';

                        // Log khusus untuk TTE Sekda final
                        log_message('info', 'TTE Sekda FINAL berhasil - Ajuan ID: ' . $ajuan_id . ', Jenis: ' . $namaJenis . ', Nomor: ' . ($metadata['nomor_peraturan'] ?? 'N/A'));
                    } else {
                        $new_status = 12; // Jenis lain: lanjut ke paraf wawako
                        $tte_message = 'Dokumen telah ditandatangani elektronik (TTE) oleh Sekretaris Daerah (Lanjut ke Wakil Walikota)';
                    }
                    break;
                case 'walikota':
                    $new_status = 14; // Setelah TTE walikota, selesai
                    $tte_message = 'Dokumen telah ditandatangani elektronik (TTE) oleh Walikota dan SELESAI';
                    break;
            }

            if ($new_status) {
                // Update status ajuan
                $updateData = [
                    'id_status_ajuan' => $new_status,
                    'tte_signed_at' => date('Y-m-d H:i:s'),
                    'tte_file_path' => $signedPath // Local path of the signed document
                ];

                if ($new_status == HarmonisasiStatus::SELESAI) {
                    $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
                }
                $this->harmonisasiAjuanModel->update($ajuan_id, $updateData);

                // Tambahkan histori dengan pesan yang lebih spesifik
                $this->harmonisasiHistoriModel->insert([
                    'id_ajuan' => $ajuan_id,
                    'id_user_aksi' => $user['id_user'],
                    'id_status_sebelumnya' => $ajuan['id_status_ajuan'],
                    'id_status_sekarang' => $new_status,
                    'keterangan' => $tte_message,
                    'tanggal_aksi' => date('Y-m-d H:i:s')
                ]);

                // Buat summary metadata untuk response
                $pdfEnhancementService = new \App\Services\SimplePdfEnhancementService();
                $metadataSummary = $pdfEnhancementService->createMetadataSummary($metadata);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'TTE berhasil diproses dan dokumen telah ditandatangani',
                    'tte_result' => $tteResult,
                    'metadata' => $metadataSummary,
                    'nomor_peraturan' => $metadata['nomor_peraturan'],
                    'jenis_peraturan' => $metadata['jenis_peraturan'],
                    'urutan_dalam_jenis' => $metadata['urutan']
                ]);
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Role tidak valid untuk proses TTE']);
        } catch (\Exception $e) {
            log_message('error', 'Process TTE error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat memproses TTE']);
        }
    }

    /**
     * Verifikasi sertifikat TTE
     */
    public function checkTteCertificate()
    {
        try {
            $nik = $this->request->getPost('nik');

            if (!$nik || !preg_match('/^\d{16}$/', $nik)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'NIK tidak valid. Harus 16 digit angka.'
                ]);
            }

            // Cek status user di BSrE
            $result = $this->tteService->checkUserStatus($nik);

            if ($result === false) {
                $error = $this->tteService->getLastError();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal memeriksa status sertifikat: ' . ($error['message'] ?? 'Unknown error')
                ]);
            }

            // Log aktivitas (tanpa id_ajuan karena ini adalah verifikasi sertifikat umum)
            // Skip logging untuk checkTteCertificate karena tidak ada ajuan_id yang valid

            return $this->response->setJSON([
                'status' => 'success',
                'certificate_status' => $result['status'] ?? 'UNKNOWN',
                'message' => $result['message'] ?? 'Status sertifikat berhasil diperiksa',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', 'TTE Check Certificate Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Simpan dokumen hasil TTE dengan integrasi database yang lengkap
     */
    private function saveSignedDocument($ajuan_id, $originalDoc, $signedDocumentPath, $user_id, $metadata = [])
    {
        try {
            // Generate nama file baru untuk dokumen yang sudah ditandatangani
            $originalName = pathinfo($originalDoc['nama_file_original'], PATHINFO_FILENAME);
            $extension = pathinfo($originalDoc['nama_file_original'], PATHINFO_EXTENSION);

            // Gunakan nomor peraturan dalam nama file jika tersedia
            $fileNamePrefix = '';
            if (!empty($metadata['nomor_peraturan'])) {
                $fileNamePrefix = preg_replace('/[^a-zA-Z0-9]/', '_', $metadata['nomor_peraturan']) . '_';
            }

            $newFileName = $fileNamePrefix . $originalName . '_TTE_' . date('YmdHis') . '.' . $extension;

            // Path untuk menyimpan dokumen hasil TTE
            $tteUploadPath = WRITEPATH . 'uploads/harmonisasi/tte/';
            if (!is_dir($tteUploadPath)) {
                mkdir($tteUploadPath, 0755, true);
            }

            $newFilePath = $tteUploadPath . $newFileName;

            // Copy file hasil TTE ke lokasi baru
            if (copy($signedDocumentPath, $newFilePath)) {
                // Simpan record dokumen hasil TTE dengan metadata lengkap
                $documentData = [
                    'id_ajuan' => $ajuan_id,
                    'id_user_uploader' => $user_id,
                    'tipe_dokumen' => 'FINAL_TTE',
                    'nama_file_original' => $newFileName,
                    'path_file_storage' => 'harmonisasi/tte/' . $newFileName,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Tambahkan metadata lengkap jika tersedia
                if (!empty($metadata)) {
                    $documentData['metadata'] = json_encode($metadata);
                }

                $documentId = $this->harmonisasiDokumenModel->insert($documentData);

                // Update metadata dengan document URL yang benar
                $metadata['document_url'] = base_url('legalisasi/download/' . $ajuan_id);

                // Update tabel harmonisasi_nomor_peraturan dengan path dokumen TTE
                if (!empty($metadata['nomor_peraturan']) && !empty($metadata['jenis_peraturan'])) {
                    $this->updateNomorPeraturanWithTTE($ajuan_id, $metadata, $newFilePath);
                }

                // Log TTE activity ke harmonisasi_tte_log
                $this->logTTEActivity($ajuan_id, $user_id, $metadata, $newFilePath);

                log_message('info', 'Dokumen TTE berhasil disimpan: ' . $newFilePath);
                log_message('info', 'Metadata TTE: ' . json_encode($metadata));
            } else {
                log_message('error', 'Gagal menyimpan dokumen TTE: ' . $signedDocumentPath);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saving signed document: ' . $e->getMessage());
        }
    }

    /**
     * Update nomor peraturan dengan informasi TTE
     */
    private function updateNomorPeraturanWithTTE($ajuan_id, $metadata, $tteFilePath)
    {
        try {
            $db = \Config\Database::connect();

            // Update record yang sudah ada dengan informasi TTE
            // Cek apakah kolom TTE ada di tabel
            $columns = $db->getFieldNames('harmonisasi_nomor_peraturan');
            $updateData = ['updated_at' => date('Y-m-d H:i:s')];

            if (in_array('tte_file_path', $columns)) {
                $updateData['tte_file_path'] = $tteFilePath;
            }
            if (in_array('tte_completed_at', $columns)) {
                $updateData['tte_completed_at'] = date('Y-m-d H:i:s');
            }
            if (in_array('tte_user_role', $columns)) {
                $updateData['tte_user_role'] = $metadata['user_role'];
            }

            // Update document URL jika tersedia dalam metadata
            if (!empty($metadata['document_url'])) {
                $updateData['document_url'] = $metadata['document_url'];
            }

            $db->table('harmonisasi_nomor_peraturan')
                ->where('id_ajuan', $ajuan_id)
                ->where('jenis_peraturan', $metadata['jenis_peraturan'])
                ->update($updateData);

            log_message('info', 'Nomor peraturan updated dengan TTE info untuk ajuan: ' . $ajuan_id);
        } catch (\Exception $e) {
            log_message('error', 'Error updating nomor peraturan with TTE: ' . $e->getMessage());
        }
    }

    /**
     * Log TTE activity ke harmonisasi_tte_log
     */
    private function logTTEActivity($ajuan_id, $user_id, $metadata, $tteFilePath)
    {
        try {
            $db = \Config\Database::connect();

            // Cek apakah tabel harmonisasi_tte_log ada
            if ($db->tableExists('harmonisasi_tte_log')) {
                // Log TTE completion
                $db->table('harmonisasi_tte_log')->insert([
                    'id_ajuan' => $ajuan_id,
                    'id_user' => $user_id,
                    'action' => 'TTE_COMPLETED',
                    'status' => 'SUCCESS',
                    'document_number' => $metadata['nomor_peraturan'] ?? null,
                    'signed_path' => $tteFilePath,
                    'error_message' => null,
                    'metadata' => json_encode($metadata),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Fallback: log ke file jika tabel tidak ada
                log_message('info', 'TTE Activity - Ajuan: ' . $ajuan_id . ', User: ' . $user_id . ', Status: SUCCESS, Document: ' . ($metadata['nomor_peraturan'] ?? 'N/A'));
            }

            log_message('info', 'TTE activity logged untuk ajuan: ' . $ajuan_id);
        } catch (\Exception $e) {
            log_message('error', 'Error logging TTE activity: ' . $e->getMessage());
        }
    }

    /**
     * Simpan nomor peraturan ke database (legacy method - kept for compatibility)
     */
    private function saveNomorPeraturan($ajuan_id, $metadata)
    {
        try {
            // Simpan ke tabel khusus untuk tracking nomor peraturan
            $db = \Config\Database::connect();

            $db->table('harmonisasi_nomor_peraturan')->insert([
                'id_ajuan' => $ajuan_id,
                'jenis_peraturan' => $metadata['jenis_peraturan'],
                'nomor_peraturan' => $metadata['nomor_peraturan'],
                'urutan' => $metadata['urutan'],
                'tahun' => $metadata['tahun'],
                'tanggal_pengesahan' => $metadata['tanggal_pengesahan'],
                'user_role' => $metadata['user_role'],
                'document_url' => $metadata['document_url'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saving nomor peraturan: ' . $e->getMessage());
        }
    }


    /**
     * Get ajuan data untuk AJAX
     */
    public function getAjuanData($ajuan_id)
    {
        try {
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);

            if (!$ajuan) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ajuan tidak ditemukan'
                ]);
            }

            // Guard per-ajuan
            if (!$this->canAccessAjuan($ajuan)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses data ajuan ini'
                ]);
            }

            $jenis = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);

            return $this->response->setJSON([
                'success' => true,
                'jenis_peraturan' => $jenis['nama_jenis'] ?? 'Unknown',
                'preview_nomor' => 'Preview nomor akan diimplementasikan'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Detail ajuan untuk legalisasi
     */
    public function detail($ajuan_id)
    {
        try {
            // Query dengan JOIN untuk mendapatkan data yang user-friendly
            $builder = $this->harmonisasiAjuanModel
                ->select('harmonisasi_ajuan.*, 
                         harmonisasi_ajuan.id as id_ajuan,
                         harmonisasi_jenis_peraturan.nama_jenis,
                         instansi.nama_instansi,
                         user.nama as nama_pemohon,
                         verifikator.nama as nama_verifikator')
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan', 'left')
                ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
                ->join('user', 'user.id_user = harmonisasi_ajuan.id_user_pemohon', 'left')
                ->join('user as verifikator', 'verifikator.id_user = harmonisasi_ajuan.id_petugas_verifikasi', 'left')
                ->where('harmonisasi_ajuan.id', $ajuan_id);

            $ajuan = $builder->first();

            // Debug: Log ajuan data
            log_message('debug', 'Detail ajuan data: ' . json_encode([
                'ajuan_id' => $ajuan_id,
                'ajuan_found' => !empty($ajuan),
                'ajuan_data' => $ajuan ? [
                    'id' => $ajuan['id'] ?? 'N/A',
                    'judul' => $ajuan['judul_peraturan'] ?? 'N/A',
                    'status' => $ajuan['id_status_ajuan'] ?? 'N/A',
                    'jenis' => $ajuan['nama_jenis'] ?? 'N/A'
                ] : null
            ]));

            if (!$ajuan) {
                throw new \Exception('Ajuan tidak ditemukan');
            }

            // Guard per-ajuan
            $canAccess = $this->canAccessAjuan($ajuan);
            log_message('debug', 'Detail access check: ' . json_encode([
                'ajuan_id' => $ajuan_id,
                'can_access' => $canAccess,
                'user_data' => [
                    'id' => $this->user['id_user'] ?? 'N/A',
                    'role' => $this->user['nama_role'] ?? 'N/A',
                    'instansi' => $this->user['id_instansi'] ?? 'N/A'
                ]
            ]));

            if (!$canAccess) {
                return redirect()->to('/legalisasi')->with('error', 'Anda tidak berhak mengakses ajuan tersebut.');
            }

            // Format tanggal
            $ajuan['tanggal_pengajuan_formatted'] = isset($ajuan['tanggal_pengajuan'])
                ? date('d F Y H:i', strtotime($ajuan['tanggal_pengajuan']))
                : '-';

            // Get dokumen terlampir - hanya dokumen FINAL_PARAF (siap TTE) dan FINAL_TTE (hasil)
            $dokumen = $this->harmonisasiDokumenModel
                ->where('id_ajuan', $ajuan_id)
                ->whereIn('tipe_dokumen', ['FINAL_PARAF', 'FINAL_TTE'])
                ->orderBy('created_at', 'DESC')
                ->findAll();

            // Get data TTE dari harmonisasi_nomor_peraturan jika sudah ada
            $nomorPeraturanModel = new \App\Models\HarmonisasiNomorPeraturanModel();
            $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
            $namaJenis = $jenisPeraturan['nama_jenis'] ?? '';
            $tteData = null;
            if ($namaJenis) {
                $tteData = $nomorPeraturanModel->getByAjuanAndJenis($ajuan_id, $namaJenis);
            }

            // Fallback: Check harmonisasi_tte_log if FINAL_TTE is not in harmonisasi_dokumen
            $hasFinalTteInDocs = false;
            foreach ($dokumen as $doc) {
                if ($doc['tipe_dokumen'] === 'FINAL_TTE') {
                    $hasFinalTteInDocs = true;
                    break;
                }
            }

            $tteLogFallback = null;
            if (!$hasFinalTteInDocs) {
                $tteLogFallback = $this->db->table('harmonisasi_tte_log')
                    ->where('id_ajuan', $ajuan_id)
                    ->where('status', 'SUCCESS')
                    ->orderBy('created_at', 'DESC')
                    ->get()
                    ->getRowArray();
            }

            // Get histori proses - menggunakan method yang sama dengan harmonisasi
            $histori = $this->harmonisasiHistoriModel->getHistoryByAjuan($ajuan_id);

            // Format histori dengan safe date handling (sama seperti harmonisasi)
            foreach ($histori as &$item) {
                $tanggal = $item['tanggal_aksi'] ?? null;
                if ($tanggal && strtotime($tanggal) !== false) {
                    $item['tanggal_formatted'] = date('d F Y H:i', strtotime($tanggal));
                } else {
                    $item['tanggal_formatted'] = 'Tanggal tidak valid';
                }
            }

            // Determine user actions based on role and status
            $user_actions = $this->getUserActionsForDetail($ajuan);

            // Get user role for view
            $user_role = session('user')['nama_role'] ?? null;

            // Determine if this is a Keputusan Sekda document
            $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
            $namaJenis = $jenisPeraturan['nama_jenis'] ?? '';
            $isKeputusanSekda = in_array($namaJenis, ['Keputusan Sekda', 'Keputusan Sekretaris Daerah', 'Instruksi Sekda', 'Instruksi Sekretaris Daerah', 'Surat Edaran Sekda', 'Surat Edaran Sekretaris Daerah']);

            $this->data['title'] = 'Detail Ajuan Legalisasi';
            $this->data['ajuan'] = $ajuan;
            $this->data['dokumen'] = $dokumen;
            $this->data['histori'] = $histori;
            $this->data['user_actions'] = $user_actions;
            $this->data['user_role'] = $user_role;
            $this->data['isKeputusanSekda'] = $isKeputusanSekda;
            $this->data['tte_data'] = $tteData;
            $this->data['tte_log_fallback'] = $tteLogFallback;

            // Debug: Log data being passed to view
            log_message('debug', 'Detail view data: ' . json_encode([
                'ajuan_id' => $ajuan_id,
                'user_role' => $user_role,
                'user_actions' => $user_actions,
                'ajuan_status' => $ajuan['id_status_ajuan'] ?? 'N/A',
                'isKeputusanSekda' => $isKeputusanSekda,
                'nama_jenis' => $namaJenis,
                'ajuan_data' => [
                    'id' => $ajuan['id'] ?? 'N/A',
                    'judul' => $ajuan['judul_peraturan'] ?? 'N/A',
                    'status' => $ajuan['id_status_ajuan'] ?? 'N/A'
                ]
            ]));

            return $this->view('legalisasi/detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Detail error: ' . $e->getMessage());
            return redirect()->to('/legalisasi')->with('error', 'Ajuan tidak ditemukan.');
        }
    }

    /**
     * Get user actions for detail page based on role and status
     */
    private function getUserActionsForDetail($ajuan)
    {
        try {
            $user_actions = [
                'can_process_tte' => false,
                'can_process_paraf' => false,
                'can_revise_to_finalisasi' => false,
                'can_download' => false
            ];

            $user_role = session('user')['nama_role'] ?? null;
            $status_id = $ajuan['id_status_ajuan'] ?? null;

            // Validate required data
            if (!$user_role) {
                log_message('error', 'getUserActionsForDetail: User role not found in session');
                return $user_actions;
            }

            if (!$status_id) {
                log_message('error', 'getUserActionsForDetail: Status ID not found in ajuan data');
                return $user_actions;
            }

            // Logic untuk menentukan aksi yang bisa dilakukan user
            switch ($user_role) {
                case 'sekda':
                    if (in_array($status_id, [11])) {
                        // Cek jenis peraturan untuk menentukan tombol mana yang ditampilkan
                        $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
                        $namaJenis = $jenisPeraturan['nama_jenis'] ?? '';

                        if ($this->isSekdaFinalDocument($namaJenis)) {
                            $user_actions['can_process_tte'] = true; // TTE untuk Keputusan Sekda
                            $user_actions['can_process_paraf'] = false;
                        } else {
                            $user_actions['can_process_tte'] = false;
                            $user_actions['can_process_paraf'] = true; // Paraf untuk jenis lain
                        }
                        $user_actions['can_revise_to_finalisasi'] = true; // Sekda can revise when status is 11
                    }
                    $user_actions['can_download'] = true;
                    break;
                case 'walikota':
                    $user_actions['can_process_tte'] = in_array($status_id, [13]); // Status menunggu TTE walikota (TTE_WALIKOTA)
                    $user_actions['can_revise_to_finalisasi'] = in_array($status_id, [13]); // Walikota can revise when status is 13
                    $user_actions['can_download'] = true;
                    break;
                case 'kabag':
                    $user_actions['can_process_paraf'] = in_array($status_id, [8]); // Status menunggu paraf kabag (PARAF_KABAG)
                    $user_actions['can_revise_to_finalisasi'] = in_array($status_id, [8]); // Kabag can revise to finalisasi when status is 8
                    $user_actions['can_download'] = true;
                    break;
                case 'asisten':
                    // Cek kewenangan instansi untuk asisten
                    $instansiModel = new \App\Models\InstansiModel();
                    $id_instansi_ajuan = (int) ($ajuan['id_instansi_pemohon'] ?? 0);
                    $id_user_asisten = (int) (session('user')['id_user'] ?? 0);
                    
                    $hasAccess = false;
                    if ($id_instansi_ajuan > 0 && $id_user_asisten > 0) {
                        $hasAccess = $instansiModel->hasAccess($id_user_asisten, $id_instansi_ajuan);
                    }
                    
                    $user_actions['can_process_paraf'] = $hasAccess && in_array($status_id, [9]); // Status menunggu paraf asisten (PARAF_ASISTEN)
                    $user_actions['can_revise_to_finalisasi'] = $hasAccess && in_array($status_id, [9]); // Asisten can revise when status is 9
                    $user_actions['can_download'] = $hasAccess;
                    break;
                case 'wawako':
                    $user_actions['can_process_paraf'] = in_array($status_id, [12]); // Status menunggu paraf wawako (PARAF_WAWAKO)
                    $user_actions['can_revise_to_finalisasi'] = in_array($status_id, [12]); // Wawako can revise when status is 12
                    $user_actions['can_download'] = true;
                    break;
                case 'opd':
                    // OPD bisa paraf dokumen mereka sendiri untuk status tertentu
                    $condition1 = ($ajuan['id_instansi_pemohon'] == (session('user')['id_instansi'] ?? null));
                    $condition2 = in_array($status_id, [7]); // Status menunggu paraf OPD (PARAF_OPD)
                    $user_actions['can_process_paraf'] = $condition1 && $condition2;
                    // OPD can request revision if data exists on legalisasi page (any status except draft and selesai)
                    $user_actions['can_revise_to_finalisasi'] = $condition1 && !in_array($status_id, [1, 14, 15]); // Not DRAFT, SELESAI, or DITOLAK
                    $user_actions['can_download'] = ($ajuan['id_instansi_pemohon'] == (session('user')['id_instansi'] ?? null));
                    break;
                default:
                    $user_actions['can_download'] = true;
                    break;
            }

            return $user_actions;
        } catch (\Exception $e) {
            log_message('error', 'getUserActionsForDetail error: ' . $e->getMessage());
            // Return default actions if error occurs
            return [
                'can_process_tte' => false,
                'can_process_paraf' => false,
                'can_download' => true
            ];
        }
    }

    /**
     * Fallback untuk processParaf tanpa parameter
     */
    public function processParafFallback()
    {
        return redirect()->to(base_url('legalisasi/dashboard'))->with('error', 'ID ajuan tidak ditemukan');
    }

    /**
     * Proses paraf dokumen
     */
    public function processParaf($ajuan_id)
    {
        try {
            // Validasi akses
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ajuan tidak ditemukan']);
            }

            if (!$this->canAccessAjuan($ajuan)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak berhak mengakses ajuan tersebut']);
            }

            // Validasi user actions
            $user_actions = $this->getUserActionsForDetail($ajuan);
            if (!$user_actions['can_process_paraf']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak dapat memproses paraf untuk ajuan ini']);
            }

            // Update status ajuan berdasarkan role
            $user_role = session('user')['nama_role'] ?? null;
            $new_status = null;

            switch ($user_role) {
                case 'kabag':
                    $new_status = 9; // Setelah diparaf kabag, lanjut ke paraf asisten
                    break;
                case 'asisten':
                    $new_status = 11; // Setelah diparaf asisten, lanjut ke paraf sekda
                    break;
                case 'sekda':
                    $new_status = 12; // Setelah diparaf sekda, lanjut ke paraf wawako
                    break;
                case 'wawako':
                    $new_status = 13; // Setelah diparaf wawako, lanjut ke TTE walikota
                    break;
                case 'walikota':
                    $new_status = 14; // Setelah TTE walikota, selesai
                    break;
                case 'opd':
                    $new_status = 8; // Setelah diparaf OPD, lanjut ke paraf kabag
                    break;
            }

            if ($new_status) {
                // Update status ajuan
                $updateData = ['id_status_ajuan' => $new_status];
                if ($new_status == HarmonisasiStatus::SELESAI) {
                    $updateData['tanggal_selesai'] = date('Y-m-d H:i:s');
                }
                $this->harmonisasiAjuanModel->update($ajuan_id, $updateData);

                // Tambahkan histori
                $this->harmonisasiHistoriModel->insert([
                    'id_ajuan' => $ajuan_id,
                    'id_user_aksi' => session('user')['id_user'],
                    'id_status_sebelumnya' => $ajuan['id_status_ajuan'],
                    'id_status_sekarang' => $new_status,
                    'keterangan' => 'Dokumen telah diparaf oleh ' . (session('user')['nama_role'] ?? 'User'),
                    'tanggal_aksi' => date('Y-m-d H:i:s')
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Paraf berhasil diproses'
                ]);
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Role tidak valid untuk proses paraf']);
        } catch (\Exception $e) {
            log_message('error', 'Process paraf error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat memproses paraf']);
        }
    }

    /**
     * Proses revisi ke finalisasi (oleh Kabag, Asisten, Sekda, Wawako, Walikota)
     */
    public function revisiKeFinalisasi($ajuan_id)
    {
        try {
            $catatan = $this->request->getPost('catatan');
            
            // Validasi catatan wajib diisi
            if (empty($catatan)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Catatan revisi wajib diisi']);
            }

            // Validasi akses
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ajuan tidak ditemukan']);
            }

            if (!$this->canAccessAjuan($ajuan)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak berhak mengakses ajuan tersebut']);
            }

            // Validasi user actions - hanya Kabag dan status harus 8 (PARAF_KABAG)
            $user_actions = $this->getUserActionsForDetail($ajuan);
            if (!$user_actions['can_revise_to_finalisasi']) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak dapat memproses revisi untuk ajuan ini']);
            }

            $new_status = 10; // REVISI_FINALISASI

            // Update status ajuan
            $this->harmonisasiAjuanModel->update($ajuan_id, [
                'id_status_ajuan' => $new_status
            ]);

            // Tambahkan histori
            $this->harmonisasiHistoriModel->insert([
                'id_ajuan' => $ajuan_id,
                'id_user_aksi' => session('user')['id_user'],
                'id_status_sebelumnya' => $ajuan['id_status_ajuan'],
                'id_status_sekarang' => $new_status,
                'keterangan' => 'Revisi ke Finalisasi: ' . $catatan,
                'tanggal_aksi' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Berhasil mengirim revisi ke finalisasi'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Revisi ke finalisasi error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat memproses revisi']);
        }
    }

    /**
     * Preview dokumen (untuk iframe)
     */
    public function preview($id_dokumen)
    {
        try {
            // Validasi input
            if (!$id_dokumen || !is_numeric($id_dokumen)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('ID dokumen tidak valid.');
            }

            $dokumen = $this->harmonisasiDokumenModel->find($id_dokumen);
            if (!$dokumen) {
                log_message('error', 'Dokumen tidak ditemukan dengan ID: ' . $id_dokumen);
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
            }

            // Validasi akses
            $ajuan = $this->harmonisasiAjuanModel->find($dokumen['id_ajuan']);
            if (!$ajuan || !$this->canAccessAjuan($ajuan)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException('Akses ditolak.');
            }

            // Perbaiki path file
            $filePath = $dokumen['path_file_storage'];
            if (strpos($filePath, 'uploads/') === 0) {
                $filePath = substr($filePath, 8);
            }

            $path = WRITEPATH . 'uploads/' . $filePath;
            if (!file_exists($path)) {
                log_message('error', 'File tidak ditemukan: ' . $path);
                throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak ditemukan.');
            }

            // Set headers untuk PDF preview
            $this->response->setHeader('Content-Type', 'application/pdf');
            $this->response->setHeader('Content-Disposition', 'inline; filename="' . $dokumen['nama_file_original'] . '"');
            $this->response->setHeader('Cache-Control', 'public, max-age=3600');

            return $this->response->setBody(file_get_contents($path));
        } catch (\Exception $e) {
            log_message('error', 'Preview error: ' . $e->getMessage());
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak dapat ditampilkan.');
        }
    }

    /**
     * Download dokumen hasil TTE
     */
    public function download($ajuan_id)
    {
        try {
            // Guard per-ajuan
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$this->canAccessAjuan($ajuan)) {
                return redirect()->back()->with('error', 'Anda tidak berhak mengunduh dokumen ajuan ini.');
            }

            // Ambil dokumen hasil TTE terbaru
            $dokumen = $this->harmonisasiDokumenModel
                ->where('id_ajuan', $ajuan_id)
                ->where('tipe_dokumen', 'FINAL_TTE')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$dokumen) {
                // Fallback: Cek di harmonisasi_tte_log jika record dokumen tidak ditemukan
                $tteLog = $this->db->table('harmonisasi_tte_log')
                    ->where('id_ajuan', $ajuan_id)
                    ->where('status', 'SUCCESS')
                    ->orderBy('created_at', 'DESC')
                    ->get()
                    ->getRowArray();

                if ($tteLog && !empty($tteLog['signed_path'])) {
                    $filePath = $tteLog['signed_path'];
                    $fullPath = ROOTPATH . $filePath; // Log path usually starts from root or contains writable
                    
                    // Jika path tidak ditemukan, coba prefix dengan WRITEPATH uploads jika itu path relatif
                    if (!file_exists($fullPath)) {
                         // Coba bersihkan path jika ada prefix double
                         $cleanPath = str_replace('jdih/writable/uploads/', '', $filePath);
                         $fullPath = WRITEPATH . 'uploads/' . $cleanPath;
                    }
                    
                    $fileName = 'Hasil_TTE_' . $ajuan_id . '.pdf';
                } else {
                    return redirect()->back()->with('error', 'Dokumen hasil TTE tidak ditemukan.');
                }
            } else {
                // Path file dari record dokumen
                $filePath = $dokumen['path_file_storage'];
                if (strpos($filePath, 'uploads/') === 0) {
                    $filePath = substr($filePath, 8);
                }
                $fullPath = WRITEPATH . 'uploads/' . $filePath;
                $fileName = $dokumen['nama_file_original'];
            }

            if (!file_exists($fullPath)) {
                log_message('error', 'File download tidak ditemukan: ' . $fullPath);
                return redirect()->back()->with('error', 'File dokumen tidak ditemukan di server.');
            }

            // Set headers untuk download
            $this->response->setHeader('Content-Type', 'application/pdf');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            $this->response->setHeader('Content-Length', filesize($fullPath));

            return $this->response->setBody(file_get_contents($fullPath));
        } catch (\Exception $e) {
            log_message('error', 'Download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh file.');
        }
    }

    /**
     * Dashboard default untuk role yang tidak spesifik
     */
    private function dashboardDefault()
    {
        $this->data['title'] = 'Dashboard Legalisasi';
        $this->data['message'] = 'Selamat datang di modul legalisasi. Silakan pilih dashboard yang sesuai dengan role Anda.';

        return $this->view('legalisasi/dashboard_default', $this->data);
    }

    /**
     * Get ajuan untuk TTE Sekda (Group A: Kepda, Instruksi Sekda, SE Sekda)
     */
    private function getAjuanForSekdaTTE()
    {
        try {
            // Dokumen Keputusan Sekda yang menunggu TTE Sekda (langsung selesai)
            $result = $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi, u.nama as nama_pemohon, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->join('instansi i', 'ha.id_instansi_pemohon = i.id', 'left')
                ->join('user u', 'ha.id_user_pemohon = u.id_user', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_SEKDA)
                ->get()
                ->getResultArray();

            // Filter secara manual menggunakan helper untuk case-insensitivity dan fleksibilitas
            $result = array_filter($result, function($item) {
                return $this->isSekdaFinalDocument($item['nama_jenis'] ?? '');
            });
            $result = array_values($result);

            // Debug: Log data untuk troubleshooting
            log_message('debug', 'getAjuanForSekdaTTE result: ' . json_encode([
                'count' => count($result),
                'data' => array_map(function ($item) {
                    return [
                        'id' => $item['id'],
                        'judul' => $item['judul_peraturan'],
                        'jenis' => $item['nama_jenis'],
                        'status' => $item['id_status_ajuan']
                    ];
                }, $result)
            ]));

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'getAjuanForSekdaTTE error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ajuan untuk Paraf Sekda (Group B: Perwal, Kepwal, dll)
     */
    private function getAjuanForSekdaParaf()
    {
        try {
            // Dokumen selain Keputusan Sekda yang menunggu paraf Sekda (lanjut ke Wawako)
            $result = $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi, u.nama as nama_pemohon, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->join('instansi i', 'ha.id_instansi_pemohon = i.id', 'left')
                ->join('user u', 'ha.id_user_pemohon = u.id_user', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_SEKDA)
                ->get()
                ->getResultArray();

            // Filter secara manual untuk mengecualikan dokumen final Sekda
            $result = array_filter($result, function($item) {
                return !$this->isSekdaFinalDocument($item['nama_jenis'] ?? '');
            });
            $result = array_values($result);

            // Debug: Log data untuk troubleshooting
            log_message('debug', 'getAjuanForSekdaParaf result: ' . json_encode([
                'count' => count($result),
                'data' => array_map(function ($item) {
                    return [
                        'id' => $item['id'],
                        'judul' => $item['judul_peraturan'],
                        'jenis' => $item['nama_jenis'],
                        'status' => $item['id_status_ajuan']
                    ];
                }, $result)
            ]));

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'getAjuanForSekdaParaf error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ajuan untuk Paraf Asisten (Filter berdasarkan instansi kewenangan)
     */
    private function getAjuanForAsistenParaf()
    {
        try {
            $user = session()->get('user');
            $id_user_asisten = $user['id_user'] ?? null;
            
            if (!$id_user_asisten) {
                log_message('debug', 'getAjuanForAsistenParaf - User ID tidak ditemukan');
                return [];
            }

            // Get instansi yang menjadi kewenangan asisten
            $instansiModel = new \App\Models\InstansiModel();
            $instansiList = $instansiModel->getInstansiByAsisten($id_user_asisten);
            
            if (empty($instansiList)) {
                // Jika asisten tidak punya kewenangan instansi, return empty
                log_message('debug', 'getAjuanForAsistenParaf - Asisten tidak punya kewenangan instansi. User ID: ' . $id_user_asisten);
                return [];
            }

            // Extract id instansi
            $id_instansi_array = array_column($instansiList, 'id');

            // Query ajuan dengan filter instansi kewenangan
            $result = $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_ASISTEN)
                ->whereIn('ha.id_instansi_pemohon', $id_instansi_array) // FILTER BERDASARKAN INSTANSI KEWENANGAN
                ->orderBy('ha.updated_at', 'DESC')
                ->get()
                ->getResultArray();
            
            // Debug: Log data untuk troubleshooting
            log_message('debug', 'getAjuanForAsistenParaf - Result count: ' . count($result));
            log_message('debug', 'getAjuanForAsistenParaf - Status filter: ' . HarmonisasiStatus::PARAF_ASISTEN);
            log_message('debug', 'getAjuanForAsistenParaf - Instansi filter: ' . json_encode($id_instansi_array));
            if (!empty($result)) {
                log_message('debug', 'getAjuanForAsistenParaf - Sample data: ' . json_encode([
                    'id' => $result[0]['id'] ?? null,
                    'judul' => $result[0]['judul_peraturan'] ?? null,
                    'jenis' => $result[0]['nama_jenis'] ?? null,
                    'status' => $result[0]['id_status_ajuan'] ?? null,
                    'instansi' => $result[0]['nama_instansi'] ?? null
                ]));
            } else {
                log_message('debug', 'getAjuanForAsistenParaf - No data found for status: ' . HarmonisasiStatus::PARAF_ASISTEN . ' with instansi: ' . json_encode($id_instansi_array));
            }
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'getAjuanForAsistenParaf error: ' . $e->getMessage());
            log_message('error', 'getAjuanForAsistenParaf stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get ajuan untuk TTE Walikota (Group B)
     */
    private function getAjuanForWalikotaTTE()
    {
        try {
            // Group B: Dokumen yang sudah diparaf Wawako, siap TTE Walikota
            return $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->join('instansi i', 'ha.id_instansi_pemohon = i.id', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::TTE_WALIKOTA)
                ->orderBy('ha.updated_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'getAjuanForWalikotaTTE error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ajuan untuk Paraf Wawako (Group B)
     */
    private function getAjuanForWawakoParaf()
    {
        try {
            // Group B: Dokumen yang sudah diparaf Sekda, menunggu paraf Wawako
            return $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->join('instansi i', 'ha.id_instansi_pemohon = i.id', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_WAWAKO)
                ->orderBy('ha.updated_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'getAjuanForWawakoParaf error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ajuan untuk Paraf OPD
     */
    private function getAjuanForOpdParaf(int $instansiId = 0)
    {
        try {
            $builder = $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->join('instansi i', 'ha.id_instansi_pemohon = i.id', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_OPD);

            // Batasi hanya ajuan milik instansi user yang login
            if ($instansiId > 0) {
                $builder->where('ha.id_instansi_pemohon', $instansiId);
            }

            return $builder->orderBy('ha.updated_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'getAjuanForOpdParaf error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ajuan untuk Paraf Kabag
     */
    private function getAjuanForKabagParaf()
    {
        return $this->db->table('harmonisasi_ajuan ha')
            ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi, COALESCE(ha.tanggal_selesai, ha.updated_at) AS tanggal_finalisasi")
            ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
            ->join('instansi i', 'ha.id_instansi_pemohon = i.id', 'left')
            ->where('ha.id_status_ajuan', HarmonisasiStatus::PARAF_KABAG)
            ->orderBy('ha.updated_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get statistik legalisasi
     */
    private function getLegalisasiStatistics()
    {
        try {
            $currentYear = date('Y');

            // Gunakan helper di atas agar konsisten dan akurat
            $pendingAsisten = $this->getAjuanForAsistenParaf();
            $pendingSekdaTTE = $this->getAjuanForSekdaTTE();
            $pendingSekdaParaf = $this->getAjuanForSekdaParaf();
            $pendingWawako = $this->getAjuanForWawakoParaf();
            $pendingWalikotaTTE = $this->getAjuanForWalikotaTTE();

            return [
                'total_current_year' => $this->harmonisasiAjuanModel
                    ->where('YEAR(created_at)', $currentYear)
                    ->countAllResults(),
                'pending_asisten_paraf' => is_array($pendingAsisten) ? count($pendingAsisten) : 0,
                'pending_sekda_tte' => is_array($pendingSekdaTTE) ? count($pendingSekdaTTE) : 0,
                'pending_sekda_paraf' => is_array($pendingSekdaParaf) ? count($pendingSekdaParaf) : 0,
                'pending_wawako_paraf' => is_array($pendingWawako) ? count($pendingWawako) : 0,
                'pending_walikota_tte' => is_array($pendingWalikotaTTE) ? count($pendingWalikotaTTE) : 0,
            ];
        } catch (\Exception $e) {
            log_message('error', 'getLegalisasiStatistics error: ' . $e->getMessage());
            // Return default values if error occurs
            return [
                'total_current_year' => 0,
                'pending_asisten_paraf' => 0,
                'pending_sekda_tte' => 0,
                'pending_sekda_paraf' => 0,
                'pending_wawako_paraf' => 0,
                'pending_walikota_tte' => 0,
            ];
        }
    }

    /**
     * Get preview next numbering sequences
     */
    private function getNextNumbersPreview()
    {
        try {
            if ($this->db->tableExists('nomor_sequence')) {
                return $this->db->table('nomor_sequence')
                    ->where('tahun', date('Y'))
                    ->orderBy('jenis_peraturan')
                    ->limit(5)
                    ->get()
                    ->getResultArray();
            }
        } catch (\Exception $e) {
            log_message('warning', 'Error loading next numbers: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Get completed Wawako paraf count
     */
    private function getCompletedWawakoParafCount()
    {
        return $this->harmonisasiAjuanModel
            ->where('id_status_ajuan', HarmonisasiStatus::PARAF_WAWAKO)
            ->countAllResults(false);
    }

    /**
     * Get recent Wawako activities
     */
    private function getRecentWawakoActivities()
    {
        try {
            // Placeholder untuk recent activities
            // Akan diimplementasikan dengan tabel historis
            return [];
        } catch (\Exception $e) {
            log_message('warning', 'Error loading recent activities: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get completed Asisten paraf count
     */
    private function getCompletedAsistenParafCount()
    {
        return $this->harmonisasiAjuanModel
            ->where('id_status_ajuan', HarmonisasiStatus::PARAF_ASISTEN)
            ->countAllResults(false);
    }

    /**
     * Guard helper: cek akses ajuan per instansi/role
     */
    private function canAccessAjuan($ajuan): bool
    {
        try {
            if (!$ajuan || !is_array($ajuan)) {
                log_message('error', 'canAccessAjuan: Invalid ajuan data');
                return false;
            }

            // Ensure user data is available
            if (!$this->user || !is_array($this->user)) {
                log_message('error', 'canAccessAjuan: User data not available');
                return false;
            }

            // Admin bebas akses
            $roles = $this->user['role'] ?? [];
            foreach ($roles as $role) {
                $name = strtolower((string) ($role['nama_role'] ?? ''));
                if (strpos($name, 'admin') !== false) {
                    return true;
                }
            }

            // Role non-OPD (sekda/kabag/walikota/wawako) diizinkan akses global
            // Khusus asisten, perlu cek kewenangan instansi
            $visible = $this->getVisibleDashboardsForUser($this->user ?? []);
            $hasNonOpd = array_intersect($visible, ['sekda', 'kabag', 'walikota', 'wawako']);
            if (!empty($hasNonOpd)) {
                return true;
            }

            // Untuk asisten, cek kewenangan instansi
            if (in_array('asisten', $visible)) {
                $instansiModel = new \App\Models\InstansiModel();
                $id_instansi_ajuan = (int) ($ajuan['id_instansi_pemohon'] ?? 0);
                $id_user_asisten = (int) ($this->user['id_user'] ?? 0);
                
                if ($id_instansi_ajuan > 0 && $id_user_asisten > 0) {
                    return $instansiModel->hasAccess($id_user_asisten, $id_instansi_ajuan);
                }
                return false;
            }

            // Jika role OPD/Instansi, batasi ke ajuan miliknya
            $userInstansiId = (int) ($this->user['id_instansi'] ?? 0);
            if ($userInstansiId > 0) {
                return (int) ($ajuan['id_instansi_pemohon'] ?? 0) === $userInstansiId;
            }

            // Default deny jika tidak jelas hak aksesnya
            return false;
        } catch (\Exception $e) {
            log_message('error', 'canAccessAjuan error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Determine which dashboards are visible for current user based on roles/instansi
     */
    private function getVisibleDashboardsForUser(array $user): array
    {
        $visible = [];
        $allAccess = false;

        $roles = $user['role'] ?? [];
        foreach ($roles as $role) {
            $name = strtolower((string) ($role['nama_role'] ?? ''));
            if ($name === '') {
                continue;
            }
            if (strpos($name, 'admin') !== false) {
                $allAccess = true;
                break;
            }
            if (strpos($name, 'sekda') !== false) {
                $visible[] = 'sekda';
            }
            if (strpos($name, 'walikota') !== false && strpos($name, 'wakil') === false) {
                $visible[] = 'walikota';
            }
            if (strpos($name, 'wakil') !== false || strpos($name, 'wawako') !== false) {
                $visible[] = 'wawako';
            }
            if (strpos($name, 'asisten') !== false) {
                $visible[] = 'asisten';
            }
            if (strpos($name, 'kabag') !== false || strpos($name, 'hukum') !== false) {
                $visible[] = 'kabag';
            }
            if (strpos($name, 'opd') !== false || strpos($name, 'instansi') !== false) {
                $visible[] = 'opd';
            }
        }

        if ($allAccess) {
            return ['sekda', 'asisten', 'opd', 'kabag', 'walikota', 'wawako'];
        }

        // If user has id_instansi and no explicit role mapping, assume OPD access
        if (empty($visible) && !empty($user['id_instansi'])) {
            $visible[] = 'opd';
        }

        // Ensure unique values
        $visible = array_values(array_unique($visible));

        return $visible;
    }

    /**
     * Check if user is allowed to access certain dashboard slug
     */
    private function isDashboardAllowed(string $slug): bool
    {
        try {
            $visible = session()->get('visible_dashboards');
            if (!is_array($visible)) {
                // Ensure user data is available
                if (!$this->user || !is_array($this->user)) {
                    log_message('error', 'isDashboardAllowed: User data not available');
                    return false;
                }
                $visible = $this->getVisibleDashboardsForUser($this->user);
                session()->set('visible_dashboards', $visible);
            }
            return in_array($slug, $visible, true);
        } catch (\Exception $e) {
            log_message('error', 'isDashboardAllowed error: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Generate QR code untuk dokumen TTE
     */
    public function generateQRCode($ajuan_id)
    {
        try {
            // Ambil data ajuan
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ajuan tidak ditemukan']);
            }

            // Ambil metadata dari database
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT * FROM harmonisasi_nomor_peraturan 
                WHERE id_ajuan = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ", [$ajuan_id]);

            $nomorData = $query->getRow();

            if (!$nomorData) {
                return $this->response->setJSON(['success' => false, 'message' => 'Data nomor peraturan tidak ditemukan']);
            }

            // Generate QR code data
            $pdfEnhancementService = new \App\Services\SimplePdfEnhancementService();
            $qrData = $pdfEnhancementService->generateQRCodeData($nomorData->document_url);

            return $this->response->setJSON([
                'success' => true,
                'qr_data' => $qrData,
                'nomor_peraturan' => $nomorData->nomor_peraturan,
                'tanggal_pengesahan' => $nomorData->tanggal_pengesahan,
                'document_url' => $nomorData->document_url
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Generate QR code error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat generate QR code']);
        }
    }

    /**
     * Generate metadata untuk TTE enhancement menggunakan data dari database - Enhanced untuk TTE Sekda
     */
    private function generateTTEMetadata($ajuan, $user_role, $isKeputusanSekda = false)
    {
        try {
            // Ambil data jenis peraturan
            $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
            $namaJenis = $jenisPeraturan['nama_jenis'] ?? 'Peraturan';

            // Cek apakah sudah ada nomor peraturan di database
            $db = \Config\Database::connect();
            $existingNomor = $db->table('harmonisasi_nomor_peraturan')
                ->where('id_ajuan', $ajuan['id'])
                ->where('jenis_peraturan', $namaJenis)
                ->orderBy('created_at', 'DESC')
                ->get()
                ->getRow();

            if ($existingNomor) {
                // Gunakan data yang sudah ada di database
                $nomorPeraturan = $existingNomor->nomor_peraturan;
                $tanggalPengesahan = $existingNomor->tanggal_pengesahan;

                // Validasi dan perbaiki tanggal pengesahan
                $pdfEnhancementService = new \App\Services\PdfEnhancementService();
                $tanggalPengesahan = $pdfEnhancementService->validateAndFixTanggalPengesahan($tanggalPengesahan);

                $documentUrl = $existingNomor->document_url;
                $urutan = $existingNomor->urutan;
            } else {
                // Generate nomor peraturan baru dan simpan ke database
                $pdfEnhancementService = new \App\Services\PdfEnhancementService();
                $urutan = $pdfEnhancementService->getNextNumberForJenis($namaJenis);
                $nomorPeraturan = $pdfEnhancementService->generateNomorPeraturan($namaJenis, $urutan);
                $tanggalPengesahan = $pdfEnhancementService->formatTanggalIndonesia();
                // Document URL akan diupdate setelah dokumen disimpan dengan ID yang benar
                $documentUrl = base_url('legalisasi/download/' . $ajuan['id']);

                // Simpan ke database untuk tracking
                $db->table('harmonisasi_nomor_peraturan')->insert([
                    'id_ajuan' => $ajuan['id'],
                    'jenis_peraturan' => $namaJenis,
                    'nomor_peraturan' => $nomorPeraturan,
                    'urutan' => $urutan,
                    'tahun' => date('Y'),
                    'tanggal_pengesahan' => date('Y-m-d'), // Simpan dalam format DATE yang valid
                    'user_role' => $user_role,
                    'document_url' => $documentUrl,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Generate additional info - khusus untuk TTE Sekda
            if ($isKeputusanSekda && $user_role === 'sekda') {
                $additionalInfo = "Dokumen " . $namaJenis . " ini telah ditandatangani secara elektronik oleh Sekretaris Daerah " .
                    "pada tanggal " . $tanggalPengesahan . " dan memiliki kekuatan hukum yang mengikat sebagai dokumen resmi.";

                // Tambahkan informasi khusus untuk Keputusan Sekda
                $additionalInfo .= " Dokumen ini merupakan dokumen final yang tidak memerlukan persetujuan lebih lanjut.";
            } else {
                $additionalInfo = "Dokumen ini telah ditandatangani secara elektronik oleh " . ucfirst($user_role) .
                    " pada tanggal " . $tanggalPengesahan . " dan memiliki kekuatan hukum yang mengikat.";
            }

            return [
                'nomor_peraturan' => $nomorPeraturan,
                'tanggal_pengesahan' => $tanggalPengesahan,
                'document_url' => $documentUrl,
                'additional_info' => $additionalInfo,
                'jenis_peraturan' => $namaJenis,
                'urutan' => $urutan,
                'tahun' => date('Y'),
                'user_role' => $user_role,
                'ajuan_id' => $ajuan['id'],
                'is_keputusan_sekda' => $isKeputusanSekda,
                'tte_type' => $isKeputusanSekda ? 'FINAL' : 'INTERMEDIATE'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Generate TTE metadata error: ' . $e->getMessage());
            return [
                'nomor_peraturan' => '1',
                'tanggal_pengesahan' => date('d F Y'),
                'document_url' => '', // Akan diupdate setelah dokumen disimpan
                'additional_info' => 'Dokumen telah ditandatangani secara elektronik.',
                'jenis_peraturan' => 'Peraturan',
                'urutan' => 1,
                'tahun' => date('Y'),
                'user_role' => $user_role,
                'ajuan_id' => $ajuan['id']
            ];
        }
    }

    /**
     * Get testing credentials for TTE
     */
    public function getTestingCredentials()
    {
        try {
            $envConfig = new \Config\Environment();

            return $this->response->setJSON([
                'success' => true,
                'testing_mode' => $envConfig->isTteTestingMode(),
                'credentials' => $envConfig->getTestCredentials()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get testing credentials error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'testing_mode' => false,
                'message' => 'Error getting testing credentials'
            ]);
        }
    }

    /**
     * Process TTE Sekda khusus untuk Keputusan Sekda
     */
    public function processTTESekda($ajuan_id)
    {
        try {
            // Debug logging
            log_message('info', 'Process TTE Sekda called - Ajuan ID: ' . $ajuan_id);
            log_message('info', 'CSRF Token: ' . $this->request->getPost('csrf_test_name'));
            log_message('info', 'Session CSRF: ' . session('csrf_test_name'));

            // Validasi akses khusus untuk Sekda
            $user = session('user');
            if (($user['nama_role'] ?? '') !== 'sekda') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Hanya Sekretaris Daerah yang dapat mengakses fitur ini'
                ]);
            }

            // Ambil data ajuan
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ajuan tidak ditemukan']);
            }

            // Validasi jenis peraturan
            $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
            $namaJenis = $jenisPeraturan['nama_jenis'] ?? '';

            if (!in_array($namaJenis, ['Keputusan Sekda', 'Keputusan Sekretaris Daerah', 'Instruksi Sekda', 'Instruksi Sekretaris Daerah', 'Surat Edaran Sekda', 'Surat Edaran Sekretaris Daerah'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Fitur TTE Sekda hanya untuk Keputusan Sekda, Instruksi Sekda, dan Surat Edaran Sekda'
                ]);
            }

            // Validasi status
            if ($ajuan['id_status_ajuan'] != HarmonisasiStatus::PARAF_SEKDA) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dokumen belum siap untuk TTE Sekda. Status harus "Menunggu Paraf/TTE Sekda"'
                ]);
            }

            // Log aktivitas TTE Sekda
            log_message('info', 'TTE Sekda dimulai - Ajuan ID: ' . $ajuan_id . ', Jenis: ' . $namaJenis . ', User: ' . ($user['nama'] ?? 'Unknown'));

            // Redirect ke processTTE dengan parameter khusus
            return $this->processTTE($ajuan_id);
        } catch (\Exception $e) {
            log_message('error', 'Process TTE Sekda error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses TTE Sekda: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get status TTE Sekda untuk monitoring
     */
    public function getTTESekdaStatus()
    {
        try {
            $user = session('user');
            if (($user['nama_role'] ?? '') !== 'sekda') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ]);
            }

            // Ambil data TTE Sekda yang pending
            $pendingTTE = $this->getAjuanForSekdaTTE();

            // Ambil data TTE Sekda yang sudah selesai (dalam 30 hari terakhir)
            $completedTTE = $this->db->table('harmonisasi_ajuan ha')
                ->select("ha.*, ha.id as id_ajuan, j.nama_jenis, i.nama_instansi")
                ->join('harmonisasi_jenis_peraturan j', 'ha.id_jenis_peraturan = j.id', 'left')
                ->join('instansi i', 'ha.id_instansi_pemohon = i.id', 'left')
                ->where('ha.id_status_ajuan', HarmonisasiStatus::SELESAI)
                ->whereIn('j.nama_jenis', ['Keputusan Sekda', 'Instruksi Sekda', 'Surat Edaran Sekda'])
                ->where('ha.tanggal_selesai >=', date('Y-m-d', strtotime('-30 days')))
                ->orderBy('ha.tanggal_selesai', 'DESC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'pending_tte' => $pendingTTE,
                    'completed_tte' => $completedTTE,
                    'stats' => [
                        'pending_count' => count($pendingTTE),
                        'completed_count' => count($completedTTE),
                        'total_this_month' => count($completedTTE)
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get TTE Sekda status error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil status TTE Sekda'
            ]);
        }
    }

    /**
     * Validasi sertifikat TTE
     */
    public function validateTTE()
    {
        try {
            $nik = $this->request->getPost('nik');
            $password = $this->request->getPost('password');

            if (!$nik || !$password) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'NIK dan Password harus diisi'
                ]);
            }

            // Validasi format NIK
            if (!preg_match('/^[0-9]{16}$/', $nik)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format NIK tidak valid. NIK harus 16 digit angka'
                ]);
            }

            // Validasi password
            if (strlen($password) < 8) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Password minimal 8 karakter'
                ]);
            }

            // Cek apakah testing mode aktif
            $testingMode = env('TTE_TESTING_MODE', false);

            if ($testingMode) {
                // Mode testing - validasi dengan kredensial testing
                $testNik = env('TTE_TEST_NIK', '1234567890123456');
                $testPassword = env('TTE_TEST_PASSWORD', 'testpassword');

                if ($nik === $testNik && $password === $testPassword) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Sertifikat testing valid',
                        'testing_mode' => true
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Kredensial testing tidak valid. Gunakan NIK: ' . $testNik . ' dan Password: ' . $testPassword
                    ]);
                }
            } else {
                // Mode production - validasi dengan BSrE
                try {
                    $tteService = new \App\Services\TTEService();
                    $isValid = $tteService->validateCertificate($nik, $password);

                    if ($isValid) {
                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Sertifikat valid dan siap digunakan',
                            'testing_mode' => false
                        ]);
                    } else {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Sertifikat tidak valid atau tidak aktif di BSrE'
                        ]);
                    }
                } catch (\Exception $e) {
                    log_message('error', 'TTE certificate validation error: ' . $e->getMessage());
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Terjadi kesalahan saat memverifikasi sertifikat dengan BSrE'
                    ]);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Validate TTE error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memvalidasi sertifikat'
            ]);
        }
    }

    /**
     * Get fresh CSRF token
     */
    public function getCsrfToken()
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'csrf_hash' => csrf_hash(),
                'csrf_token' => csrf_token()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get CSRF token error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil CSRF token'
            ]);
        }
    }

    /**
     * Halaman testing eSign server
     */
    public function testESign()
    {
        try {
            $this->data['title'] = 'Test Koneksi eSign Server';
            return $this->view('legalisasi/test_esign', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Test eSign page error: ' . $e->getMessage());
            return redirect()->to('/legalisasi')->with('error', 'Terjadi kesalahan saat memuat halaman test eSign.');
        }
    }

    /**
     * Test koneksi dasar ke server eSign
     */
    public function testESignConnection()
    {
        try {
            $host = '103.141.74.94';
            $port = 80;

            $connection = @fsockopen($host, $port, $errno, $errstr, 10);
            if ($connection) {
                fclose($connection);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Koneksi ke server eSign berhasil',
                    'host' => $host,
                    'port' => $port,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Koneksi ke server eSign gagal: ' . $errstr,
                    'host' => $host,
                    'port' => $port,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Test autentikasi ke server eSign
     */
    public function testESignAuth()
    {
        try {
            $clientId = 'diskominfo';
            $clientSecret = 'diskominfo';

            $url = 'http://103.141.74.94/api/auth';
            $data = json_encode([
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $data,
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                $responseData = json_decode($response, true);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Autentikasi berhasil',
                    'client_id' => $clientId,
                    'response' => $responseData,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Autentikasi gagal',
                    'client_id' => $clientId,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Test autentikasi dengan client credentials yang salah
     */
    public function testESignAuthWrong()
    {
        try {
            $clientId = 'client_salah';
            $clientSecret = 'secret_salah';

            $url = 'http://103.141.74.94/api/auth';
            $data = json_encode([
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $data,
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                $responseData = json_decode($response, true);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Autentikasi dengan credentials salah masih berhasil - SERVER TIDAK MEMVALIDASI!',
                    'client_id' => $clientId,
                    'response' => $responseData,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Autentikasi dengan credentials salah gagal - SERVER MEMVALIDASI DENGAN BENAR',
                    'client_id' => $clientId,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Test validasi sertifikat
     */
    public function testESignCertificate()
    {
        try {
            $testNik = '1234567890123456';
            $testPassword = 'testpassword';

            $url = 'http://103.141.74.94/api/validate-certificate';
            $data = json_encode([
                'nik' => $testNik,
                'password' => $testPassword
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $data,
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                $responseData = json_decode($response, true);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Validasi sertifikat berhasil',
                    'test_nik' => $testNik,
                    'response' => $responseData,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validasi sertifikat gagal',
                    'test_nik' => $testNik,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Test TTEService
     */
    public function testESignTTEService()
    {
        try {
            $tteService = new \App\Services\TteService();
            $testNik = '1234567890123456';

            $result = $tteService->checkUserStatus($testNik);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'TTEService berfungsi',
                'test_nik' => $testNik,
                'result' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'TTEService error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Test semua sekaligus
     */
    public function testESignAll()
    {
        try {
            $results = [];

            // Test 1: Koneksi
            $connection = $this->testESignConnection();
            $results['connection'] = json_decode($connection->getBody(), true);

            // Test 2: Autentikasi
            $auth = $this->testESignAuth();
            $results['auth'] = json_decode($auth->getBody(), true);

            // Test 3: Sertifikat
            $cert = $this->testESignCertificate();
            $results['certificate'] = json_decode($cert->getBody(), true);

            // Test 4: TTEService
            $tte = $this->testESignTTEService();
            $results['tteservice'] = json_decode($tte->getBody(), true);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Semua test selesai',
                'results' => $results,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Test sertifikat dengan NIK dan password asli
     */
    public function testESignRealCertificate()
    {
        try {
            $nik = $this->request->getPost('nik');
            $password = $this->request->getPost('password');

            if (empty($nik) || empty($password)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'NIK dan password harus diisi',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }

            $url = 'http://103.141.74.94/api/validate-certificate';
            $data = json_encode([
                'nik' => $nik,
                'password' => $password
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $data,
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                $responseData = json_decode($response, true);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Validasi sertifikat asli berhasil',
                    'test_nik' => $nik,
                    'response' => $responseData,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validasi sertifikat asli gagal',
                    'test_nik' => $nik,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Get FINAL_PARAF document untuk TTE
     */
    public function getFinalParafDocument($ajuan_id)
    {
        try {
            // Validasi akses
            $ajuan = $this->harmonisasiAjuanModel->find($ajuan_id);
            if (!$ajuan) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ajuan tidak ditemukan']);
            }

            if (!$this->canAccessAjuan($ajuan)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak berhak mengakses ajuan tersebut']);
            }

            // Ambil dokumen FINAL_PARAF
            // Catatan: Dokumen lama otomatis diubah menjadi 'HISTORY' saat upload baru
            // di Finalisasi::submitAksi(), sehingga query ini selalu mendapatkan versi tunggal terbaru.
            $dokumen = $this->harmonisasiDokumenModel
                ->where('id_ajuan', $ajuan_id)
                ->where('tipe_dokumen', 'FINAL_PARAF')
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$dokumen) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dokumen FINAL_PARAF tidak ditemukan. Pastikan dokumen sudah diparaf dan siap untuk TTE.'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'id_dokumen' => $dokumen['id'],
                'nama_file' => $dokumen['nama_file_original'] ?? 'document.pdf',
                'path_file_storage' => $dokumen['path_file_storage']
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Get Final Paraf Document error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil dokumen'
            ]);
        }
    }

    /**
     * Helper untuk mengecek apakah dokumen adalah Keputusan Sekda (Final di Sekda)
     */
    private function isSekdaFinalDocument($namaJenis): bool
    {
        if (!$namaJenis) return false;
        
        $namaJenis = strtolower($namaJenis);
        $searchTerms = [
            'keputusan sekda',
            'keputusan sekretaris daerah',
            'instruksi sekda',
            'instruksi sekretaris daerah',
            'surat edaran sekda',
            'surat edaran sekretaris daerah'
        ];

        foreach ($searchTerms as $term) {
            if (strpos($namaJenis, $term) !== false) {
                return true;
            }
        }

        return false;
    }

}
