<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiDokumenModel;
use App\Models\HarmonisasiHistoriModel;
use App\Models\HarmonisasiNomorPeraturanModel;
use App\Models\UserModel; // Asumsi ada UserModel untuk mengambil daftar verifikator
use App\Models\HarmonisasiJenisPeraturanModel;
use App\Config\HarmonisasiStatus;

class Harmonisasi extends BaseController
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiDokumenModel;
    protected $harmonisasiHistoriModel;
    protected $userModel;
    protected $jenisPeraturanModel;

    public function __construct()
    {
        parent::__construct();

        // ============================================================
        // CORS HEADERS FOR AJAX REQUESTS
        // ============================================================
        $this->setCorsHeaders();

        // ============================================================
        // ROLE-BASED ACCESS CONTROL - HANYA ADMIN DAN INSTANSI
        // ============================================================
        $this->mustLoggedIn(); // Pastikan user sudah login

        // Cek apakah user memiliki permission untuk mengakses module harmonisasi
        // User harus punya minimal read_all atau read_own permission
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Akses ditolak: Anda tidak memiliki permission untuk mengakses module harmonisasi');
            exit;
        }

        // Inisialisasi models
        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->harmonisasiDokumenModel = new HarmonisasiDokumenModel();
        $this->harmonisasiHistoriModel = new HarmonisasiHistoriModel();
        $this->userModel = new UserModel();
        $this->jenisPeraturanModel = new \App\Models\HarmonisasiJenisPeraturanModel();
        helper(['form', 'url']);

        // ============================================================
        // OPTIMIZED ASSET LOADING - CONDITIONAL & NAMESPACED
        // ============================================================
        $this->loadHarmonisasiAssets();
    }

    /**
     * Set CORS headers for AJAX requests
     */
    private function setCorsHeaders()
    {
        // Enhanced CORS for development - prevent redirects and protocol issues
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        // Allow specific origins to prevent CORS issues
        if ($origin === 'http://localhost' || $origin === 'https://localhost') {
            header('Access-Control-Allow-Origin: ' . $origin);
        } else {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-TOKEN, Authorization, Accept');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 hours cache for preflight

        // Prevent HTTPS redirects
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');

        // Handle preflight OPTIONS request immediately
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Load harmonisasi-specific assets with conflict prevention
     */
    private function loadHarmonisasiAssets()
    {
        // Get current method to determine which assets to load
        $currentMethod = $this->request->getUri()->getSegment(2) ?? 'index';

        // Load base harmonisasi CSS (always needed)
        $this->addStyle(base_url('jdih/assets/css/harmonisasi-module.css?v=' . time()));

        // Load DataTables extensions only for pages that need them
        if (in_array($currentMethod, ['index', 'result', 'hasil', 'penugasanDashboard'])) {
            $this->loadDataTableExtensions();
        }

        // Load page-specific assets
        switch ($currentMethod) {
            case 'new':
            case 'edit':
                // File harmonisasi-forms.css dan harmonisasi-forms.js tidak ada
                // Menggunakan harmonisasi-complete.js sebagai gantinya
                break;

            case 'show':
                // File harmonisasi-detail.css dan harmonisasi-detail.js tidak ada
                // Menggunakan harmonisasi-complete.js sebagai gantinya
                break;

            case 'penugasanDashboard':
            case 'tugaskan':
                // File harmonisasi-penugasan.css dan harmonisasi-penugasan.js tidak ada
                // Menggunakan harmonisasi-complete.js sebagai gantinya
                break;
        }

        // Load harmonisasi-complete.js (all-in-one solution) with simple cache busting
        if (in_array($currentMethod, ['index', 'new', 'edit', 'show', 'result', 'hasil', 'penugasanDashboard', 'verifikasi', 'validasi', 'finalisasi'])) {
            $this->addJs(base_url('jdih/assets/js/harmonisasi-complete.js?v=' . time()));
        }
    }

    /**
     * Load DataTables extensions with conflict prevention
     */
    private function loadDataTableExtensions()
    {
        // Load DataTables Buttons extensions with versioning
        $version = ENVIRONMENT === 'production' ? '1.0.0' : time();

        // CSS for DataTables Buttons
        $this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css?v=' . $version));

        // JavaScript extensions in correct order
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js?v=' . $version));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js?v=' . $version));
        $this->addJs(base_url('vendors/datatables/extensions/JSZip/jszip.min.js?v=' . $version));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/pdfmake.min.js?v=' . $version));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/vfs_fonts.js?v=' . $version));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.html5.min.js?v=' . $version));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.print.min.js?v=' . $version));
    }

    /**
     * Validate user session and return user data
     * Centralized session validation to eliminate duplication
     * CRITICAL SECURITY: Pastikan user benar-benar login dan session valid
     */
    private function validateUserSession()
    {
        // CRITICAL SECURITY FIX: Cek logged_in flag terlebih dahulu
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }
        
        $user = session()->get('user');
        if (!$user || empty($user['id_user'])) {
            // CRITICAL SECURITY FIX: Destroy session jika user data tidak valid
            $this->session->destroy();
            return redirect()->to('/login')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }
        
        // CRITICAL SECURITY FIX: Pastikan user status active
        if (empty($user['status']) || $user['status'] !== 'active') {
            $this->session->destroy();
            return redirect()->to('/login')->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        }
        
        return $user;
    }

    /**
     * Check if user has admin privileges using permission-based approach
     */
    private function isAdmin($user)
    {
        // Admin = user yang memiliki update_all dan delete_all permission
        return $this->hasPermission('update_all') && $this->hasPermission('delete_all');
    }

    // Menampilkan dashboard pengajuan untuk OPD
    public function index()
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission read_all atau read_own
        // ============================================================
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Anda tidak memiliki permission untuk melihat data harmonisasi');
            return;
        }

        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Prevent browser caching
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        try {
            $statistics = $this->getHarmonisasiStatistics($user);
            $data = [
                'title' => 'Dashboard Harmonisasi',
                'breadcrumb' => ['Harmonisasi' => ''],
                'ajuan' => [], // Data akan dimuat via DataTables SSD
                'user_role' => $user['nama_role'] ?? '',
                'user_actions' => $this->getUserAvailableActions($user),
                'statistics' => $statistics
            ];

            $this->data = array_merge($this->data, $data);
            return $this->view('harmonisasi/index', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Error in harmonisasi index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    // Menampilkan hasil (unified with index)
    public function result()
    {
        return $this->index(); // Redirect to index to eliminate duplication
    }

    /**
     * Get ajuan data based on user role with proper error handling and caching
     */
    private function getAjuanDataByRole($user)
    {
        $id_user = $user['id_user'];
        $role = $user['nama_role'] ?? '';

        // Nonaktifkan cache untuk semua role
        $useCache = false;

        // Cache key based on user and role
        $cacheKey = "ajuan_data_{$role}_{$id_user}";
        $cacheTime = 300; // 5 minutes

        // Hanya gunakan cache untuk role selain admin/superadmin
        if ($useCache) {
            $cachedData = cache($cacheKey);
            if ($cachedData !== null) {
                return $cachedData;
            }
        }

        try {
            // ============================================================
            // FILTER DATA BERDASARKAN PERMISSION
            // ============================================================
            if ($this->hasPermission('read_all')) {
                // User dengan read_all bisa lihat semua data
                $ajuan_list = $this->harmonisasiAjuanModel->getAjuanWithDetails();
            } else if ($this->hasPermission('read_own')) {
                // User dengan read_own: Untuk OPD/Instansi, lihat semua data di instansi tersebut
                // Jika tidak ada instansi, baru fallback ke data milik sendiri
                if (isset($user['id_instansi']) && $user['id_instansi']) {
                    $ajuan_list = $this->harmonisasiAjuanModel->getAjuanForInstansi($user['id_instansi']);
                } else {
                    $ajuan_list = $this->harmonisasiAjuanModel->getAjuanForUser($id_user);
                }
            } else {
                // Tidak ada permission, return array kosong
                $ajuan_list = [];
            }

            // Process and safe-guard the data
            $processedData = $this->processAjuanData($ajuan_list);

            // Simpan ke cache hanya jika diizinkan
            if ($useCache) {
                cache()->save($cacheKey, $processedData, $cacheTime);
            }

            return $processedData;
        } catch (\Exception $e) {
            log_message('error', 'Error getting ajuan data for role ' . $role . ': ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Process ajuan data with safe handling and additional fields
     */
    private function processAjuanData($ajuan_list)
    {
        foreach ($ajuan_list as &$ajuan) {
            // Ensure consistent ID field
            $ajuan['id_ajuan'] = $ajuan['id'] ?? $ajuan['id_ajuan'] ?? 0;

            // Safe field access with fallbacks
            $ajuan['judul_peraturan'] = $ajuan['judul_peraturan'] ?? 'Data tidak tersedia';
            $ajuan['nama_jenis'] = $ajuan['nama_jenis'] ?? 'Data tidak tersedia';
            $ajuan['nama_instansi'] = $ajuan['nama_instansi'] ?? 'Data tidak tersedia';
            $ajuan['nama_status'] = $ajuan['nama_status'] ?? 'N/A';
            $ajuan['nama_pemohon'] = $ajuan['nama_pemohon'] ?? 'Data tidak tersedia';

            // Safe date handling
            if (!empty($ajuan['tanggal_pengajuan']) && $ajuan['tanggal_pengajuan'] != '0000-00-00 00:00:00') {
                $ajuan['tanggal_formatted'] = date('d/m/Y H:i', strtotime($ajuan['tanggal_pengajuan']));
            } else {
                $ajuan['tanggal_formatted'] = !empty($ajuan['created_at'])
                    ? date('d/m/Y H:i', strtotime($ajuan['created_at']))
                    : 'Tanggal tidak tersedia';
            }

            // Add status styling class
            $ajuan['status_class'] = $this->getStatusBadgeClass($ajuan['id_status_ajuan'] ?? 0);

            // Add available actions for this ajuan
            $ajuan['available_actions'] = $this->getAjuanActions($ajuan);
        }

        // Ensure data is sorted by created_at DESC (double-check sorting)
        usort($ajuan_list, function ($a, $b) {
            $a_created = strtotime($a['created_at'] ?? '1970-01-01 00:00:00');
            $b_created = strtotime($b['created_at'] ?? '1970-01-01 00:00:00');
            return $b_created - $a_created; // DESC order
        });

        return $ajuan_list;
    }

    /**
     * Get status badge CSS class
     */
    private function getStatusBadgeClass($status_id)
    {
        $status_colors = [
            1 => 'bg-secondary text-white',
            2 => 'bg-warning text-dark',
            3 => 'bg-info text-white',
            4 => 'bg-danger text-white',
            5 => 'bg-primary text-white',
            6 => 'bg-danger text-white',
            7 => 'bg-success text-white',
            8 => 'bg-dark text-white',
            9 => 'bg-dark text-white',
            10 => 'bg-warning text-dark',
            11 => 'bg-dark text-white',
            12 => 'bg-dark text-white',
            13 => 'bg-success text-white',
            14 => 'bg-secondary text-white',
            15 => 'bg-danger text-white',
        ];

        return $status_colors[$status_id] ?? 'bg-secondary text-white';
    }

    /**
     * Get user available actions based on permissions (not role)
     */
    private function getUserAvailableActions($user)
    {
        $actions = [];

        // Check each permission dinamically
        if ($this->hasPermission('create')) {
            $actions[] = 'create';
        }
        if ($this->hasPermission('read_all') || $this->hasPermission('read_own')) {
            $actions[] = 'view';
        }
        if ($this->hasPermission('update_all') || $this->hasPermission('update_own')) {
            $actions[] = 'edit';
        }
        if ($this->hasPermission('delete_all') || $this->hasPermission('delete_own')) {
            $actions[] = 'delete';
        }
        // Admin with full permissions can assign
        if ($this->hasPermission('update_all') && $this->hasPermission('delete_all')) {
            $actions[] = 'assign';
        }

        return $actions;
    }

    /**
     * Get actions available for specific ajuan based on permissions
     */
    private function getAjuanActions($ajuan)
    {
        $actions = [];
        $status = $ajuan['id_status_ajuan'] ?? 0;
        $user = session()->get('user');

        // Detail action always available if user has read permission
        if ($this->hasPermission('read_all') || $this->hasPermission('read_own')) {
            $actions['detail'] = true;
        }

        // Permission-based actions
        if ($status == 1) { // Draft status
            if (
                $this->hasPermission('update_all') ||
                ($this->hasPermission('update_own') && (
                    $ajuan['id_user_pemohon'] == $user['id_user'] || 
                    (isset($user['id_instansi']) && $user['id_instansi'] && $ajuan['id_instansi_pemohon'] == $user['id_instansi'])
                ))
            ) {
                $actions['edit'] = true;
                $actions['submit'] = true;
            }
        }

        // Admin actions (users with full permissions)
        if ($this->hasPermission('update_all') && $this->hasPermission('delete_all')) {
            $actions['edit'] = true;
            if ($status == 2) { // Status submitted
                $actions['assign'] = true;
            }
        }

        return $actions;
    }



    /**
     * Get harmonisasi statistics with draft alerts
     */
    private function getHarmonisasiStatistics($user)
    {
        try {
            $statistics = [];

            // Get basic statistics
            if ($this->hasPermission('read_all')) {
                $statistics['total_ajuan'] = $this->harmonisasiAjuanModel->countAll();
                $statistics['draft'] = $this->harmonisasiAjuanModel->where('id_status_ajuan', 1)->countAllResults();
                $statistics['in_progress'] = $this->harmonisasiAjuanModel->whereIn('id_status_ajuan', [2, 3, 4])->countAllResults();
                $statistics['completed'] = $this->harmonisasiAjuanModel->whereIn('id_status_ajuan', [5, 6])->countAllResults();

                // Get detailed statistics for each status
                $statistics['status_details'] = $this->getDetailedStatusStatistics();

                // Add icon information to status details
                foreach ($statistics['status_details'] as &$status) {
                    $status['icon'] = $this->getStatusIcon($status['id']);
                }
            } else if ($this->hasPermission('read_own')) {
                $id_instansi = $user['id_instansi'] ?? null;
                
                if ($id_instansi) {
                    $statistics['total_ajuan'] = $this->harmonisasiAjuanModel->where('id_instansi_pemohon', $id_instansi)->countAllResults();
                    $statistics['draft'] = $this->harmonisasiAjuanModel->where('id_instansi_pemohon', $id_instansi)->where('id_status_ajuan', 1)->countAllResults();
                    $statistics['in_progress'] = $this->harmonisasiAjuanModel->where('id_instansi_pemohon', $id_instansi)->whereIn('id_status_ajuan', [2, 3, 4])->countAllResults();
                    $statistics['completed'] = $this->harmonisasiAjuanModel->where('id_instansi_pemohon', $id_instansi)->whereIn('id_status_ajuan', [5, 6])->countAllResults();

                    $statistics['status_details'] = $this->getDetailedStatusStatisticsByInstansi($id_instansi);
                } else {
                    $statistics['total_ajuan'] = $this->harmonisasiAjuanModel->where('id_user_pemohon', $user['id_user'])->countAllResults();
                    $statistics['draft'] = $this->harmonisasiAjuanModel->where('id_user_pemohon', $user['id_user'])->where('id_status_ajuan', 1)->countAllResults();
                    $statistics['in_progress'] = $this->harmonisasiAjuanModel->where('id_user_pemohon', $user['id_user'])->whereIn('id_status_ajuan', [2, 3, 4])->countAllResults();
                    $statistics['completed'] = $this->harmonisasiAjuanModel->where('id_user_pemohon', $user['id_user'])->whereIn('id_status_ajuan', [5, 6])->countAllResults();

                    $statistics['status_details'] = $this->getDetailedStatusStatistics($user['id_user']);
                }

                // Add icon information to status details
                foreach ($statistics['status_details'] as &$status) {
                    $status['icon'] = $this->getStatusIcon($status['id']);
                }
            }

            // Add draft alerts for user instansi
            if ($this->hasPermission('read_own') && !$this->hasPermission('read_all')) {
                $statistics['draft_alerts'] = $this->getDraftAlerts($user['id_user']);
            }

            return $statistics;
        } catch (\Exception $e) {
            log_message('error', 'Error getting harmonisasi statistics: ' . $e->getMessage());
            return [
                'total_ajuan' => 0,
                'draft' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'status_details' => [],
                'draft_alerts' => []
            ];
        }
    }

    /**
     * Get detailed statistics for each status filtered by instansi
     */
    private function getDetailedStatusStatisticsByInstansi($instansiId)
    {
        try {
            $db = \Config\Database::connect();

            // Get all status definitions
            $statuses = $db->table('harmonisasi_status')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            $statusDetails = [];

            foreach ($statuses as $status) {
                $count = $db->table('harmonisasi_ajuan')
                    ->where('id_status_ajuan', $status['id'])
                    ->where('id_instansi_pemohon', $instansiId)
                    ->countAllResults();

                $statusDetails[] = [
                    'id' => $status['id'],
                    'nama_status' => $status['nama_status'],
                    'count' => $count,
                    'color' => $this->getStatusColor($status['id'])
                ];
            }

            return $statusDetails;
        } catch (\Exception $e) {
            log_message('error', 'Error getting detailed status statistics for instansi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detailed statistics for each status
     */
    private function getDetailedStatusStatistics($userId = null)
    {
        try {
            $db = \Config\Database::connect();

            // Get all status definitions
            $statuses = $db->table('harmonisasi_status')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            $statusDetails = [];

            foreach ($statuses as $status) {
                $query = $db->table('harmonisasi_ajuan')
                    ->where('id_status_ajuan', $status['id']);

                // If userId provided, filter by user
                if ($userId) {
                    $query->where('id_user_pemohon', $userId);
                }

                $count = $query->countAllResults();

                $statusDetails[] = [
                    'id' => $status['id'],
                    'nama_status' => $status['nama_status'],
                    'count' => $count,
                    'color' => $this->getStatusColor($status['id'])
                ];
            }

            return $statusDetails;
        } catch (\Exception $e) {
            log_message('error', 'Error getting detailed status statistics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get color for status badge
     */
    private function getStatusColor($statusId)
    {
        $colors = [
            1 => 'secondary',   // Draft
            2 => 'warning',     // Diajukan ke Kabag
            3 => 'info',        // Ditugaskan ke Verifikator
            4 => 'primary',     // Proses Validasi
            5 => 'warning',     // Revisi
            6 => 'danger',      // Proses Finalisasi
            7 => 'success',     // Menunggu Paraf OPD
            8 => 'dark',        // Menunggu Paraf Kabag
            9 => 'dark',        // Menunggu Paraf Asisten
            10 => 'warning',    // Revisi ke Finalisasi
            11 => 'dark',       // Menunggu Paraf/TTE Sekda
            12 => 'dark',       // Menunggu Paraf Wawako
            13 => 'success',    // Menunggu TTE Walikota
            14 => 'success',    // Selesai
            15 => 'danger',     // Ditolak
        ];

        return $colors[$statusId] ?? 'secondary';
    }

    /**
     * Get icon for status
     */
    public function getStatusIcon($statusId)
    {
        $icons = [
            1 => 'edit',           // Draft
            2 => 'paper-plane',    // Diajukan ke Kabag
            3 => 'user-check',     // Ditugaskan ke Verifikator
            4 => 'clipboard-check', // Proses Validasi
            5 => 'redo',           // Revisi
            6 => 'file-signature', // Proses Finalisasi
            7 => 'stamp',          // Menunggu Paraf OPD
            8 => 'stamp',          // Menunggu Paraf Kabag
            9 => 'stamp',          // Menunggu Paraf Asisten
            10 => 'undo',          // Revisi ke Finalisasi
            11 => 'stamp',         // Menunggu Paraf/TTE Sekda
            12 => 'stamp',         // Menunggu Paraf Wawako
            13 => 'file-signature', // Menunggu TTE Walikota
            14 => 'check-circle',  // Selesai
            15 => 'times-circle',  // Ditolak
        ];

        return $icons[$statusId] ?? 'question-circle';
    }

    /**
     * Get draft alerts for user instansi
     */
    private function getDraftAlerts($userId)
    {
        try {
            $drafts = $this->harmonisasiAjuanModel
                ->select('id, judul_peraturan, tanggal_pengajuan, created_at')
                ->where('id_user_pemohon', $userId)
                ->where('id_status_ajuan', 1) // Draft status
                ->orderBy('created_at', 'DESC')
                ->findAll();

            $alerts = [];
            foreach ($drafts as $draft) {
                $createdDate = strtotime($draft['created_at']);
                $daysSinceCreated = floor((time() - $createdDate) / (60 * 60 * 24));

                $alert = [
                    'id' => $draft['id'],
                    'judul' => $draft['judul_peraturan'],
                    'days_old' => $daysSinceCreated,
                    'created_at' => $draft['created_at'],
                    'urgency' => $this->getUrgencyLevel($daysSinceCreated)
                ];

                $alerts[] = $alert;
            }

            return $alerts;
        } catch (\Exception $e) {
            log_message('error', 'Error getting draft alerts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get urgency level based on days since creation
     */
    private function getUrgencyLevel($days)
    {
        if ($days >= 30) return 'critical';
        if ($days >= 14) return 'warning';
        if ($days >= 7) return 'info';
        return 'normal';
    }

    /**
     * Get pending drafts for bulk action
     */
    public function getPendingDrafts()
    {
        if (!$this->hasPermission('read_own')) {
            return $this->response->setJSON(['error' => 'Permission denied'])->setStatusCode(403);
        }

        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        try {
            $drafts = $this->harmonisasiAjuanModel
                ->select('id, judul_peraturan, nama_jenis, nama_instansi, created_at')
                ->where('id_user_pemohon', $user['id_user'])
                ->where('id_status_ajuan', 1)
                ->orderBy('created_at', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'data' => $drafts
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting pending drafts: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Terjadi kesalahan saat mengambil data draft'])->setStatusCode(500);
        }
    }

    /**
     * Bulk submit drafts
     */
    public function bulkSubmitDrafts()
    {
        if (!$this->hasPermission('create')) {
            return $this->response->setJSON(['error' => 'Permission denied'])->setStatusCode(403);
        }

        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Handle both POST and JSON requests
        $input = $this->request->getJSON();
        if ($input) {
            $draftIds = $input->draft_ids ?? [];
        } else {
            $draftIds = $this->request->getPost('draft_ids');
        }

        if (empty($draftIds) || !is_array($draftIds)) {
            return $this->response->setJSON(['error' => 'Tidak ada draft yang dipilih'])->setStatusCode(400);
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($draftIds as $draftId) {
            try {
                // Verify ownership
                $draft = $this->harmonisasiAjuanModel
                    ->where('id', $draftId)
                    ->where('id_user_pemohon', $user['id_user'])
                    ->where('id_status_ajuan', 1)
                    ->first();

                if (!$draft) {
                    $failedCount++;
                    $errors[] = "Draft ID {$draftId}: Tidak ditemukan atau tidak dapat diajukan";
                    continue;
                }

                // Update status to submitted
                $this->harmonisasiAjuanModel->update($draftId, [
                    'id_status_ajuan' => 2, // Submitted status
                    'tanggal_pengajuan' => date('Y-m-d H:i:s')
                ]);

                // Add history
                $this->harmonisasiHistoriModel->insert([
                    'id_ajuan' => $draftId,
                    'id_status_sebelum' => 1,
                    'id_status_sekarang' => 2,
                    'keterangan' => 'Draft diajukan melalui bulk action',
                    'id_user' => $user['id_user'],
                    'tanggal' => date('Y-m-d H:i:s')
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "Draft ID {$draftId}: " . $e->getMessage();
                log_message('error', 'Error bulk submitting draft ' . $draftId . ': ' . $e->getMessage());
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Berhasil mengajukan {$successCount} draft" . ($failedCount > 0 ? ", {$failedCount} gagal" : ''),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ]);
    }

    // Menampilkan form pengajuan baru
    public function new()
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission create
        // ============================================================
        $this->hasPermission('create', true); // Exit jika tidak punya permission create

        $this->data['title'] = 'Form Pengajuan Harmonisasi Baru';
        $this->data['jenis_peraturan'] = $this->jenisPeraturanModel->findAll();

        return $this->view('harmonisasi/new', $this->data);
    }

    // Memproses pengajuan baru
    public function create()
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission create
        // ============================================================
        $this->hasPermission('create', true); // Exit jika tidak punya permission create

        // Validate form inputs
        if (!$this->validateCreateForm()) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Validate user session
        $user = $this->validateUserSession();
        if (!$this->validateUserInstansi($user)) {
            return redirect()->to('/login')->with('error', 'Sesi Anda tidak valid atau telah berakhir. Silakan login kembali.');
        }

        // Process the creation with proper transaction handling
        try {
            $id_ajuan = $this->saveAjuanData($user);
            $this->handleFileUpload($id_ajuan, $user);
            $this->logAjuanHistory($id_ajuan, $user);

            // Hapus cache list ajuan untuk user ini agar data baru langsung terlihat
            $cacheKey = "ajuan_data_{$user['nama_role']}_{$user['id_user']}";
            cache()->delete($cacheKey);

            return redirect()->to('/harmonisasi')->with('success', 'Pengajuan berhasil dikirim.');
        } catch (\Exception $e) {
            log_message('error', '[HARMONISASI CREATE FAILED] ' . $e->getMessage());

            // Rollback ajuan if exists
            if (!empty($id_ajuan)) {
                $this->harmonisasiAjuanModel->delete($id_ajuan);
            }

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Validate form data for create method
     */
    private function validateCreateForm()
    {
        $rules = [
            'judul_peraturan' => 'required|min_length[10]',
            'id_jenis_peraturan' => 'required',
            'draft_peraturan' => [
                'rules' => 'uploaded[draft_peraturan]|max_size[draft_peraturan,25600]|mime_in[draft_peraturan,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]|ext_in[draft_peraturan,doc,docx]',
                'errors' => [
                    'uploaded' => 'Anda wajib mengupload file draf peraturan.',
                    'max_size' => 'Ukuran file maksimal adalah 25 MB.',
                    'mime_in'  => 'Hanya file dengan format Word (DOC atau DOCX) yang diizinkan.',
                    'ext_in'   => 'Hanya file dengan ekstensi .doc atau .docx yang diizinkan.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'Validation failed: ' . print_r($this->validator->getErrors(), true));
            return false;
        }
        return true;
    }

    /**
     * Validate user instansi data
     */
    private function validateUserInstansi($user)
    {
        if (empty($user['id_user']) || empty($user['id_instansi'])) {
            log_message('error', 'Session user tidak valid saat mencoba membuat ajuan harmonisasi.');
            return false;
        }
        return true;
    }

    /**
     * Save ajuan data to database
     */
    private function saveAjuanData($user)
    {
        $dataAjuan = [
            'judul_peraturan'     => $this->request->getPost('judul_peraturan'),
            'id_jenis_peraturan'  => $this->request->getPost('id_jenis_peraturan'),
            'id_instansi_pemohon' => $user['id_instansi'],
            'id_user_pemohon'     => $user['id_user'],
            'id_status_ajuan'     => HarmonisasiStatus::DRAFT, // Default status: "Draft"
            'tanggal_pengajuan'   => date('Y-m-d H:i:s'),
            'keterangan'          => $this->request->getPost('keterangan'),
        ];

        $id_ajuan = $this->harmonisasiAjuanModel->insert($dataAjuan);

        if (!$id_ajuan) {
            $errors = $this->harmonisasiAjuanModel->errors();
            log_message('error', 'Gagal insert ajuan: ' . print_r($errors, true));
            throw new \Exception('Gagal menyimpan data ajuan: ' . implode(', ', $errors));
        }

        return $id_ajuan;
    }

    /**
     * Handle file upload and save document record
     */
    private function handleFileUpload($id_ajuan, $user)
    {
        $file = $this->request->getFile('draft_peraturan');

        if (!$file || !$file->isValid()) {
            $error = $file ? $file->getErrorString() . ' (' . $file->getError() . ')' : 'File tidak ditemukan dalam request.';
            throw new \Exception('File yang di-upload tidak valid. Error: ' . $error);
        }

        if ($file->hasMoved()) {
            throw new \Exception('File yang di-upload sudah pernah dipindahkan.');
        }

        $uploadPath = WRITEPATH . 'uploads/harmonisasi_dokumen';
        $this->ensureUploadDirectory($uploadPath);

        $originalName = $file->getName();
        $newName = $file->getRandomName();

        if (!$file->move($uploadPath, $newName)) {
            $error = $file->getErrorString() . ' (' . $file->getError() . ')';
            throw new \Exception('Gagal memindahkan file yang di-upload. Error: ' . $error);
        }

        $this->harmonisasiDokumenModel->insert([
            'id_ajuan' => $id_ajuan,
            'id_user_uploader' => $user['id_user'],
            'tipe_dokumen' => 'DRAFT_AWAL',
            'nama_file_original' => $originalName,
            'path_file_storage' => 'harmonisasi_dokumen/' . $newName
        ]);
    }

    /**
     * Ensure upload directory exists and is writable
     */
    private function ensureUploadDirectory($uploadPath)
    {
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true)) {
                throw new \Exception('Gagal membuat direktori upload. Periksa izin folder writable.');
            }
        }

        if (!is_writable($uploadPath)) {
            throw new \Exception('Direktori upload tidak dapat ditulis (permission denied). Path: ' . $uploadPath);
        }
    }

    /**
     * Log history for new ajuan
     */
    private function logAjuanHistory($id_ajuan, $user)
    {
        try {
            $dataHistori = [
                'id_ajuan' => $id_ajuan,
                'id_user_aksi' => $user['id_user'],
                'id_status_sebelumnya' => 1, // Draft
                'id_status_sekarang' => 2, // Diajukan ke Kabag
                'keterangan' => 'Pengajuan baru telah dibuat oleh pemohon.'
            ];

            $id_histori = $this->harmonisasiHistoriModel->logHistory($dataHistori);

            if (!$id_histori) {
                log_message('error', 'Gagal insert histori');
                throw new \Exception('Gagal menyimpan histori');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error saat insert histori: ' . $e->getMessage());
            // Histori gagal tidak perlu menggagalkan seluruh proses
        }
    }

    // Menampilkan detail ajuan
    // Method untuk mengajukan draft menjadi ajuan resmi
    public function submit($id)
    {
        $user = $this->validateUserSession();
        if (empty($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        try {
            $ajuan = $this->harmonisasiAjuanModel->find($id);

            // Pastikan ajuan ada
            if (!$ajuan) {
                return redirect()->to('/harmonisasi')->with('error', 'Ajuan tidak ditemukan.');
            }

            // Validasi akses: hanya pemilik atau admin yang boleh ajukan
            $isAdmin = $this->isAdmin($user);
            if ($ajuan['id_user_pemohon'] != $user['id_user'] && !$isAdmin) {
                return redirect()->to('/harmonisasi')->with('error', 'Anda tidak memiliki hak akses untuk mengajukan draft ini.');
            }

            // Pastikan statusnya adalah Draft
            if ($ajuan['id_status_ajuan'] != HarmonisasiStatus::DRAFT) {
                return redirect()->to('/harmonisasi')->with('error', 'Hanya ajuan dengan status Draft yang bisa diajukan.');
            }

            // Update status menjadi "Diajukan ke Kabag"
            $this->harmonisasiAjuanModel->update($id, ['id_status_ajuan' => HarmonisasiStatus::DIAJUKAN]);

            // Catat histori
            $this->harmonisasiHistoriModel->logHistory([
                'id_ajuan' => $id,
                'id_user_aksi' => $user['id_user'],
                'id_status_sebelumnya' => HarmonisasiStatus::DRAFT,
                'id_status_sekarang' => HarmonisasiStatus::DIAJUKAN,
                'keterangan' => 'Ajuan diajukan oleh ' . ($isAdmin ? 'admin' : 'pemohon') . '.'
            ]);

            return redirect()->to('/harmonisasi')->with('success', 'Ajuan berhasil diajukan ke Bagian Hukum.');
        } catch (\Exception $e) {
            log_message('error', 'Error saat mengajukan draft: ' . $e->getMessage());
            return redirect()->to('/harmonisasi')->with('error', 'Terjadi kesalahan sistem saat mencoba mengajukan draft.');
        }
    }

    // Method untuk menampilkan form edit draft
    public function edit($id)
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission update
        // ============================================================
        if (!$this->hasPermission('update_all') && !$this->hasPermission('update_own')) {
            $this->printError('Anda tidak memiliki permission untuk mengedit data harmonisasi');
            return;
        }

        $user = $this->validateUserSession();
        if (empty($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        try {
            $ajuan = $this->harmonisasiAjuanModel->find($id);

            // Pastikan ajuan ada
            if (!$ajuan) {
                return redirect()->to('/harmonisasi')->with('error', 'Ajuan tidak ditemukan.');
            }

            // ============================================================
            // VALIDASI OWNERSHIP BERDASARKAN PERMISSION
            // ============================================================
            if ($this->hasPermission('update_all')) {
                // User dengan update_all bisa edit semua data
                // No additional check needed
            } else if ($this->hasPermission('update_own')) {
                // User dengan update_own hanya bisa edit data milik sendiri
                if ($ajuan['id_user_pemohon'] != $user['id_user']) {
                    return redirect()->to('/harmonisasi')->with('error', 'Anda hanya bisa mengedit data milik Anda sendiri.');
                }
            } else {
                // Tidak ada permission edit
                return redirect()->to('/harmonisasi')->with('error', 'Anda tidak memiliki hak akses untuk mengedit data ini.');
            }

            // Pastikan statusnya adalah Draft (1) - hanya draft yang bisa diedit
            if ($ajuan['id_status_ajuan'] != 1) {
                return redirect()->to('/harmonisasi')->with('error', 'Hanya ajuan dengan status Draft yang bisa diedit.');
            }

            // Ambil data jenis peraturan untuk dropdown
            $jenisPeraturan = $this->jenisPeraturanModel->findAll();

            // Ambil dokumen yang sudah ada
            $dokumen = $this->harmonisasiDokumenModel->where('id_ajuan', $id)->findAll();

            $this->data['title'] = 'Edit Draft Harmonisasi';
            $this->data['ajuan'] = $ajuan;
            $this->data['jenis_peraturan'] = $jenisPeraturan;
            $this->data['dokumen'] = $dokumen;

            return $this->view('harmonisasi/edit', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menampilkan form edit: ' . $e->getMessage());
            return redirect()->to('/harmonisasi')->with('error', 'Terjadi kesalahan sistem saat mencoba menampilkan form edit.');
        }
    }

    // Method untuk memproses update draft
    public function update($id)
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission update
        // ============================================================
        if (!$this->hasPermission('update_all') && !$this->hasPermission('update_own')) {
            $this->printError('Anda tidak memiliki permission untuk mengedit data harmonisasi');
            return;
        }

        $user = $this->validateUserSession();
        if (empty($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        try {
            $ajuan = $this->harmonisasiAjuanModel->find($id);

            // Pastikan ajuan ada
            if (!$ajuan) {
                return redirect()->to('/harmonisasi')->with('error', 'Ajuan tidak ditemukan.');
            }

            // ============================================================
            // VALIDASI OWNERSHIP BERDASARKAN PERMISSION
            // ============================================================
            if ($this->hasPermission('update_all')) {
                // User dengan update_all bisa edit semua data
                $isAdmin = true;
            } else if ($this->hasPermission('update_own')) {
                // User dengan update_own hanya bisa edit data milik sendiri
                if ($ajuan['id_user_pemohon'] != $user['id_user']) {
                    return redirect()->to('/harmonisasi')->with('error', 'Anda hanya bisa mengedit data milik Anda sendiri.');
                }
                $isAdmin = false;
            } else {
                // Tidak ada permission edit
                return redirect()->to('/harmonisasi')->with('error', 'Anda tidak memiliki hak akses untuk mengedit data ini.');
            }

            // Pastikan statusnya adalah Draft (1)
            if ($ajuan['id_status_ajuan'] != 1) {
                return redirect()->to('/harmonisasi')->with('error', 'Hanya ajuan dengan status Draft yang bisa diedit.');
            }

            // Aturan validasi
            $rules = [
                'judul_peraturan' => 'required|min_length[10]',
                'id_jenis_peraturan' => 'required'
            ];

            // Jika ada file baru yang diupload
            $file = $this->request->getFile('draft_peraturan');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $rules['draft_peraturan'] = [
                    'rules' => 'uploaded[draft_peraturan]|max_size[draft_peraturan,25600]|mime_in[draft_peraturan,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]',
                    'errors' => [
                        'uploaded' => 'Anda wajib mengupload file draf peraturan.',
                        'max_size' => 'Ukuran file maksimal adalah 25 MB.',
                        'mime_in'  => 'Hanya file dengan format PDF, DOC, atau DOCX yang diizinkan.'
                    ]
                ];
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('validation', $this->validator);
            }

            // Update data ajuan
            $dataAjuan = [
                'judul_peraturan'     => $this->request->getPost('judul_peraturan'),
                'id_jenis_peraturan'  => $this->request->getPost('id_jenis_peraturan'),
                'keterangan'          => $this->request->getPost('keterangan'),
                'updated_at'          => date('Y-m-d H:i:s')
            ];

            $this->harmonisasiAjuanModel->update($id, $dataAjuan);

            // Handle upload file baru jika ada
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $uploadPath = WRITEPATH . 'uploads/harmonisasi_dokumen';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true); // SECURE PERMISSION
                }

                // Validasi file content
                if (!$this->scanDocumentForMalware($file)) {
                    return redirect()->back()->with('error', 'File contains malware');
                }

                $originalName = $file->getName();
                $newName = $file->getRandomName();

                if ($file->move($uploadPath, $newName)) {
                    // Hapus dokumen lama jika ada
                    $dokumenLama = $this->harmonisasiDokumenModel->where('id_ajuan', $id)->where('tipe_dokumen', 'DRAFT_AWAL')->first();
                    if ($dokumenLama) {
                        // Hapus file fisik lama
                        $oldFilePath = WRITEPATH . 'uploads/' . $dokumenLama['path_file_storage'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                        // Hapus record dari database
                        $this->harmonisasiDokumenModel->delete($dokumenLama['id']);
                    }

                    // Simpan dokumen baru
                    $this->harmonisasiDokumenModel->insert([
                        'id_ajuan' => $id,
                        'id_user_uploader' => $user['id_user'],
                        'tipe_dokumen' => 'DRAFT_AWAL',
                        'nama_file_original' => $originalName,
                        'path_file_storage' => 'harmonisasi_dokumen/' . $newName
                    ]);
                }
            }

            // Catat histori
            $this->harmonisasiHistoriModel->logHistory([
                'id_ajuan' => $id,
                'id_user_aksi' => $user['id_user'],
                'id_status_sebelumnya' => 1,
                'id_status_sekarang' => 1,
                'keterangan' => 'Draft diperbarui oleh ' . ($isAdmin ? 'admin' : 'pemohon') . '.'
            ]);

            return redirect()->to('/harmonisasi/show/' . $id)->with('success', 'Draft berhasil diperbarui.');
        } catch (\Exception $e) {
            log_message('error', 'Error saat update draft: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem saat mencoba memperbarui draft.');
        }
    }

    private function scanDocumentForMalware($file)
    {
        $content = file_get_contents($file->getTempName());

        // Check for embedded scripts
        $suspiciousPatterns = [
            'javascript:',
            'vbscript:',
            'data:text/html',
            '<?php',
            '<script',
            'eval\(',
            'base64_decode'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return false;
            }
        }

        return true;
    }

    // Menampilkan detail ajuan
    public function show($id)
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission read
        // ============================================================
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Anda tidak memiliki permission untuk melihat data harmonisasi');
            return;
        }

        // Validasi session user
        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $ajuan = $this->harmonisasiAjuanModel->getAjuanDetail($id);
        if (!$ajuan) {
            return redirect()->to('/harmonisasi')->with('error', 'Data ajuan tidak ditemukan.');
        }

        // ============================================================
        // VALIDASI OWNERSHIP BERDASARKAN PERMISSION
        // ============================================================
        if ($this->hasPermission('read_all')) {
            // User dengan read_all bisa lihat semua data
            // No additional check needed
        } else if ($this->hasPermission('read_own')) {
            // User dengan read_own hanya bisa lihat data milik sendiri
            if ($ajuan['id_user_pemohon'] != $user['id_user']) {
                return redirect()->to('/harmonisasi')->with('error', 'Anda hanya bisa melihat data milik Anda sendiri.');
            }
        } else {
            // Tidak ada permission view
            return redirect()->to('/harmonisasi')->with('error', 'Anda tidak memiliki akses untuk melihat data ini.');
        }

        // Check if coming from penugasan page
        $from_penugasan = $this->request->getGet('from') === 'penugasan';

        // Get data TTE dari harmonisasi_nomor_peraturan jika sudah ada
        $nomorPeraturanModel = new HarmonisasiNomorPeraturanModel();
        $jenisPeraturan = $this->jenisPeraturanModel->find($ajuan['id_jenis_peraturan']);
        $namaJenis = $jenisPeraturan['nama_jenis'] ?? '';
        $tteData = null;
        if ($namaJenis) {
            $tteData = $nomorPeraturanModel->getByAjuanAndJenis($id, $namaJenis);
        }

        // Prepare data with proper null handling
        $data = [
            'title' => 'Detail Pengajuan Harmonisasi',
            'ajuan' => $this->prepareAjuanData($ajuan),
            'dokumen' => $this->harmonisasiDokumenModel->getDokumenByAjuan($id, $ajuan['id_status_ajuan']),
            'histori' => $this->prepareHistoriData($id),
            'user_actions' => $this->getUserActions($user, $ajuan),
            'current_user' => $user,
            'from_penugasan' => $from_penugasan,
            'tte_data' => $tteData
        ];

        $this->data = array_merge($this->data, $data);
        return $this->view('harmonisasi/harmonisasi_detail', $this->data);
    }

    /**
     * Centralized access control for ajuan detail
     */
    private function hasAccessToAjuan($user, $ajuan)
    {
        $userId = $user['id_user'] ?? 0;

        // Permission-based access control
        if ($this->hasPermission('read_all')) {
            // Users with read_all permission can access everything
            return true;
        } elseif ($this->hasPermission('read_own')) {
            // Users with read_own permission can only access their own data
            return $ajuan['id_user_pemohon'] == $userId;
        }

        // No access by default
        return false;
    }

    /**
     * Prepare ajuan data with safe date handling
     */
    private function prepareAjuanData($ajuan)
    {
        // Safe date formatting
        if (!empty($ajuan['tanggal_pengajuan'])) {
            $ajuan['tanggal_pengajuan_formatted'] = date('d F Y H:i', strtotime($ajuan['tanggal_pengajuan']));
        } else {
            $ajuan['tanggal_pengajuan_formatted'] = 'Tanggal tidak tersedia';
        }

        if (!empty($ajuan['tanggal_selesai'])) {
            $ajuan['tanggal_selesai_formatted'] = date('d F Y H:i', strtotime($ajuan['tanggal_selesai']));
        } else {
            $ajuan['tanggal_selesai_formatted'] = 'Belum selesai';
        }

        return $ajuan;
    }

    /**
     * Prepare histori data with safe date handling
     */
    private function prepareHistoriData($id_ajuan)
    {
        $histori = $this->harmonisasiHistoriModel->getHistoryByAjuan($id_ajuan);

        foreach ($histori as &$item) {
            $tanggal = $item['tanggal_aksi'] ?? null;
            if ($tanggal && strtotime($tanggal) !== false) {
                $item['tanggal_formatted'] = date('d F Y H:i', strtotime($tanggal));
            } else {
                $item['tanggal_formatted'] = 'Tanggal tidak valid';
            }
        }

        return $histori;
    }

    /**
     * Get available user actions based on permissions and status
     */
    private function getUserActions($user, $ajuan)
    {
        $actions = [];
        $currentStatus = $ajuan['id_status_ajuan'];
        $userId = $user['id_user'] ?? 0;

        // Permission-based actions
        if ($currentStatus == 1) { // Draft status
            if (
                $this->hasPermission('update_all') ||
                ($this->hasPermission('update_own') && $ajuan['id_user_pemohon'] == $userId)
            ) {
                $actions['can_submit'] = true;
                $actions['can_edit'] = true;
            }
        }

        // Admin-level actions (full permissions)
        if ($this->hasPermission('update_all') && $this->hasPermission('delete_all')) {
            $actions['can_submit'] = true;
            $actions['can_edit'] = true;
            if ($currentStatus == 2) { // Status submitted
                $actions['can_assign'] = true;
            }
        }

        // Verification actions (this would need specific permission)
        if ($currentStatus == 3 && $ajuan['id_petugas_verifikasi'] == $userId) {
            $actions['can_complete_verification'] = true;
        }

        // Revisi actions - Admin dan Pemohon bisa upload revisi saat status Revisi
        if ($currentStatus == 5) { // Status Revisi (id_status_ajuan = 5)
            if (
                $this->hasPermission('update_all') ||
                ($this->hasPermission('update_own') && $ajuan['id_user_pemohon'] == $userId)
            ) {
                $actions['can_submit_revisi'] = true;
            }
        }

        return $actions;
    }

    // Method untuk mengunduh file dokumen
    public function download($id_dokumen)
    {
        // Validasi session user
        $user = $this->validateUserSession();

        // Validasi input
        if (!$id_dokumen || !is_numeric($id_dokumen)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('ID dokumen tidak valid.');
        }

        $dokumen = $this->harmonisasiDokumenModel->find($id_dokumen);
        if (!$dokumen) {
            log_message('error', 'Dokumen tidak ditemukan dengan ID: ' . $id_dokumen);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
        }

        // Validasi akses - user hanya bisa download dokumen yang terkait dengan ajuan mereka
        $ajuan = $this->harmonisasiAjuanModel->find($dokumen['id_ajuan']);
        if (!$ajuan) {
            log_message('error', 'Ajuan tidak ditemukan untuk dokumen ID: ' . $id_dokumen);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Ajuan tidak ditemukan.');
        }

        // Permission-based access control for document download
        $hasAccess = false;
        if ($this->hasPermission('read_all')) {
            // Users with read_all permission can download all documents
            $hasAccess = true;
        } elseif ($this->hasPermission('read_own') && $ajuan['id_user_pemohon'] == $user['id_user']) {
            // Users with read_own permission can only download their own documents
            $hasAccess = true;
        }

        if (!$hasAccess) {
            return redirect()->to('/harmonisasi')->with('error', 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        // Perbaiki path file - hapus 'uploads/' dari path_file_storage karena sudah ada di WRITEPATH
        $filePath = $dokumen['path_file_storage'];
        if (strpos($filePath, 'uploads/') === 0) {
            $filePath = substr($filePath, 8); // Hapus 'uploads/' dari awal
        }

        $path = WRITEPATH . 'uploads/' . $filePath;
        if (!file_exists($path)) {
            log_message('error', 'File tidak ditemukan: ' . $path . ' untuk dokumen ID: ' . $id_dokumen);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak ditemukan di server.');
        }

        return $this->response->download($path, null)->setFileName($dokumen['nama_file_original']);
    }

    // --- Fungsi untuk Kabag ---

    // Menampilkan dashboard penugasan untuk users dengan permission assign
    public function penugasanDashboard()
    {
        // Permission-based access control
        if (!$this->hasPermission('update_all') || !$this->hasPermission('delete_all')) {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda tidak memiliki permission untuk mengakses halaman ini.');
        }
        $this->data['title'] = 'Dashboard Penugasan Harmonisasi';
        $this->data['breadcrumb'] = ['Harmonisasi' => base_url('harmonisasi'), 'Dashboard Penugasan' => ''];
        $this->data['ajuan_penugasan'] = $this->harmonisasiAjuanModel->getAjuanForPenugasan();
        // Override current_module agar menu navigasi yang benar menjadi aktif.
        // Jika modul 'penugasan' tidak ada, menu 'harmonisasi' akan tetap aktif sebagai fallback.
        $module = $this->model->getModule('penugasan');
        if ($module) {
            $this->data['current_module'] = $module;
        }
        return $this->view('harmonisasi/penugasan_dashboard', $this->data);
    }

    // Menampilkan form untuk menugaskan verifikator
    public function tugaskan($id)
    {
        // Permission-based access control
        if (!$this->hasPermission('update_all') || !$this->hasPermission('delete_all')) {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda tidak memiliki permission untuk mengakses halaman ini.');
        }
        // Validasi ID ajuan
        if (!$id || !is_numeric($id)) {
            return redirect()->to('/harmonisasi/penugasan')->with('error', 'ID ajuan tidak valid.');
        }
        $ajuan = $this->harmonisasiAjuanModel->getAjuanDetail($id);
        if (!$ajuan) {
            return redirect()->to('/harmonisasi/penugasan')->with('error', 'Ajuan tidak ditemukan.');
        }
        // Fix: Ensure id_ajuan is available for the view, as the model uses 'id' as the primary key.
        if (isset($ajuan['id'])) {
            $ajuan['id_ajuan'] = $ajuan['id'];
        }
        if (!$ajuan) {
            return redirect()->to('/harmonisasi')->with('error', 'Data ajuan tidak ditemukan.');
        }
        $data = [
            'title' => 'Tugaskan Verifikator',
            'ajuan' => $ajuan,
            'verifikator_list' => $this->userModel->getUsersByRoleId(7),
        ];
        $this->data = array_merge($this->data, $data);
        return $this->view('harmonisasi/penugasan_form', $this->data);
    }

    // --- Fungsi untuk Verifikator ---

    public function verifikasiSelesai($id)
    {
        // Permission-based access control
        if (!$this->hasPermission('update_all') && !$this->hasPermission('update_own')) {
            return redirect()->to('/harmonisasi/show/' . $id)->with('error', 'Akses ditolak. Anda tidak memiliki permission untuk melakukan tindakan ini.');
        }

        $user = $this->validateUserSession();
        try {
            $ajuan = $this->harmonisasiAjuanModel->find($id);
            if (!$ajuan) {
                return redirect()->to('/harmonisasi')->with('error', 'Ajuan tidak ditemukan.');
            }
            // Simpan status sebelumnya untuk histori
            $status_sebelumnya = $ajuan['id_status_ajuan'];
            // Update status menjadi "Verifikasi Selesai"
            $this->harmonisasiAjuanModel->update($id, ['id_status_ajuan' => HarmonisasiStatus::VALIDASI]);
            // Catat histori
            $this->harmonisasiHistoriModel->logHistory([
                'id_ajuan' => $id,
                'id_user_aksi' => $user['id_user'],
                'id_status_sebelumnya' => $status_sebelumnya,
                'id_status_sekarang' => HarmonisasiStatus::VALIDASI,
                'keterangan' => 'Status diubah menjadi Verifikasi Selesai oleh ' . $user['nama_lengkap']
            ]);
            return redirect()->to('/harmonisasi/show/' . $id)->with('success', 'Status ajuan berhasil diubah menjadi Verifikasi Selesai.');
        } catch (\Exception $e) {
            log_message('error', 'Error saat verifikasi selesai: ' . $e->getMessage());
            return redirect()->to('/harmonisasi/show/' . $id)->with('error', 'Terjadi kesalahan sistem saat mencoba mengubah status.');
        }
    }

    // Memproses penugasan verifikator
    public function assign()
    {
        // Permission-based access control
        if (!$this->hasPermission('update_all') || !$this->hasPermission('delete_all')) {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda tidak memiliki permission untuk mengakses halaman ini.');
        }

        $user = $this->validateUserSession();
        $id_ajuan = $this->request->getPost('id_ajuan');
        $id_verifikator = $this->request->getPost('id_user_verifikator');

        // Validasi input
        if (!$id_ajuan || !$id_verifikator || !is_numeric($id_ajuan) || !is_numeric($id_verifikator)) {
            return redirect()->back()->with('error', 'Data tidak valid. Pastikan semua field terisi dengan benar.');
        }

        // Validasi ajuan exists
        $ajuan = $this->harmonisasiAjuanModel->find($id_ajuan);
        if (!$ajuan) {
            return redirect()->to('/harmonisasi/penugasan')->with('error', 'Ajuan tidak ditemukan.');
        }

        // Validasi verifikator exists
        $verifikator = $this->userModel->find($id_verifikator);
        if (!$verifikator) {
            return redirect()->back()->with('error', 'Verifikator tidak ditemukan.');
        }

        try {
            // 1. Update ajuan dengan petugas verifikasi dan status baru
            $this->harmonisasiAjuanModel->update($id_ajuan, [
                'id_petugas_verifikasi' => $id_verifikator,
                'id_status_ajuan' => HarmonisasiStatus::VERIFIKASI, // Status: Proses Verifikasi
            ]);

            // 2. Catat histori
            $this->harmonisasiHistoriModel->logHistory([
                'id_ajuan' => $id_ajuan,
                'id_user_aksi' => $user['id_user'],
                'id_status_sebelumnya' => HarmonisasiStatus::DIAJUKAN, // Diajukan ke Kabag
                'id_status_sekarang' => HarmonisasiStatus::VERIFIKASI, // Proses Verifikasi
                'keterangan' => 'Telah ditugaskan kepada verifikator oleh Kabag.'
            ]);

            return redirect()->to('/penugasan')->with('success', 'Verifikator berhasil ditugaskan.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating assignment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memproses penugasan verifikator.'];
        }
    }

    /**
     * Method untuk memproses submit revisi
     */
    public function submitRevisi($id)
    {
        $user = $this->validateUserSession();
        if (empty($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        try {
            $ajuan = $this->harmonisasiAjuanModel->getAjuanWithDetails($id);
            if (!$ajuan) {
                return redirect()->to('/harmonisasi')->with('error', 'Ajuan tidak ditemukan.');
            }

            // Validasi akses dan status - Admin bisa akses semua, pemohon hanya milik sendiri
            if (!$this->hasPermission('update_all') && $ajuan['id_user_pemohon'] != $user['id_user']) {
                return redirect()->to('/harmonisasi')->with('error', 'Anda tidak memiliki hak akses untuk merevisi ajuan ini.');
            }
            if ($ajuan['id_status_ajuan'] != HarmonisasiStatus::REVISI) {
                return redirect()->to('/harmonisasi')->with('error', 'Hanya ajuan dengan status Revisi yang bisa direvisi.');
            }

            // Cek apakah ini adalah POST request (dari form) atau GET request (akses langsung)
            if ($this->request->getMethod() !== 'post') {
                // Jika akses langsung, redirect ke form revisi
                return redirect()->to('/harmonisasi/showRevisiForm/' . $id);
            }

            // Validasi upload file
            $file = $this->request->getFile('dokumen');
            if (!$file || !$file->isValid()) {
                return redirect()->back()->with('error', 'File dokumen revisi wajib diupload.');
            }
            if (!in_array($file->getExtension(), ['doc', 'docx'])) {
                return redirect()->back()->with('error', 'Format file harus DOC atau DOCX.');
            }

            // Ambil histori terakhir untuk mendapatkan status sebelum revisi
            $histori = $this->harmonisasiHistoriModel->where('id_ajuan', $id)
                ->where('id_status_sekarang', HarmonisasiStatus::REVISI)
                ->orderBy('tanggal_aksi', 'DESC')
                ->first();

            // Tentukan status yang akan dikembalikan berdasarkan histori
            $status_sebelum = $histori['id_status_sebelumnya'] ?? HarmonisasiStatus::VALIDASI;

            // Log untuk debugging
            log_message('info', 'Submit Revisi - Ajuan ID: ' . $id . ', Status Sebelum: ' . $status_sebelum);

            // Jika tidak ada histori atau status_sebelumnya tidak valid, gunakan logika fallback
            if (!$histori || !$status_sebelum || $status_sebelum == HarmonisasiStatus::REVISI) {
                // Coba ambil histori terakhir yang bukan status revisi
                $histori_terakhir = $this->harmonisasiHistoriModel->where('id_ajuan', $id)
                    ->where('id_status_sekarang !=', HarmonisasiStatus::REVISI)
                    ->orderBy('tanggal_aksi', 'DESC')
                    ->first();

                if ($histori_terakhir) {
                    $status_sebelum = $histori_terakhir['id_status_sekarang'];
                    log_message('info', 'Submit Revisi - Menggunakan histori terakhir, Status: ' . $status_sebelum);
                } else {
                    // Fallback ke status yang masuk akal berdasarkan workflow
                    $status_sebelum = HarmonisasiStatus::VALIDASI;
                    log_message('info', 'Submit Revisi - Menggunakan fallback VALIDASI');
                }
            }

            // Upload file revisi
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/harmonisasi_dokumen', $newName);

            // Simpan dokumen revisi
            $this->harmonisasiDokumenModel->insert([
                'id_ajuan'           => $id,
                'id_user_uploader'   => $user['id_user'],
                'tipe_dokumen'       => 'REVISI_OPD',
                'nama_file_original' => $file->getClientName(),
                'path_file_storage'  => 'harmonisasi_dokumen/' . $newName
            ]);

            // Update status ajuan
            $this->harmonisasiAjuanModel->update($id, [
                'id_status_ajuan' => $status_sebelum
            ]);

            // Catat histori
            $this->harmonisasiHistoriModel->logHistory([
                'id_ajuan' => $id,
                'id_status_sebelumnya' => HarmonisasiStatus::REVISI,
                'id_status_sekarang' => $status_sebelum,
                'id_user_aksi' => $user['id_user'],
                'keterangan' => $this->request->getPost('keterangan') ?? 'Dokumen telah direvisi sesuai permintaan.'
            ]);

            return redirect()->to('/harmonisasi/show/' . $id)->with('success', 'Dokumen revisi berhasil disubmit.');
        } catch (\Exception $e) {
            log_message('error', 'Error saat submit revisi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Method untuk menampilkan form revisi
     */
    public function showRevisiForm($id)
    {
        $user = $this->validateUserSession();
        if (empty($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Sesi Anda tidak valid. Silakan login kembali.');
        }

        try {
            $ajuan = $this->harmonisasiAjuanModel->getAjuanWithDetails($id);
            if (!$ajuan) {
                return redirect()->to('/harmonisasi')->with('error', 'Ajuan tidak ditemukan.');
            }

            // Validasi akses: Admin bisa akses semua, pemohon hanya milik sendiri
            if (!$this->hasPermission('update_all') && $ajuan['id_user_pemohon'] != $user['id_user']) {
                return redirect()->to('/harmonisasi')->with('error', 'Anda tidak memiliki hak akses untuk merevisi ajuan ini.');
            }

            // Pastikan statusnya adalah Revisi
            if ($ajuan['id_status_ajuan'] != HarmonisasiStatus::REVISI) {
                return redirect()->to('/harmonisasi')->with('error', 'Hanya ajuan dengan status Revisi yang bisa direvisi.');
            }

            // Ambil histori revisi terakhir untuk mendapatkan catatan revisi
            $histori = $this->harmonisasiHistoriModel->where('id_ajuan', $id)
                ->where('id_status_sekarang', HarmonisasiStatus::REVISI)
                ->orderBy('tanggal_aksi', 'DESC')
                ->first();

            // Ambil dokumen koreksi jika ada
            $dokumen_koreksi = $this->harmonisasiDokumenModel->where('id_ajuan', $id)
                ->groupStart()
                ->where('tipe_dokumen', 'hasil_validasi')
                ->orWhere('tipe_dokumen', 'hasil_finalisasi')
                ->orWhere('tipe_dokumen', 'hasil_verifikasi')
                ->groupEnd()
                ->orderBy('created_at', 'DESC')
                ->first();

            $this->data['title'] = 'Form Revisi Dokumen';
            $this->data['ajuan'] = $ajuan;
            $this->data['histori'] = $histori;
            $this->data['dokumen_koreksi'] = $dokumen_koreksi;

            return $this->view('harmonisasi/submit_revisi', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Error saat menampilkan form revisi: ' . $e->getMessage());
            return redirect()->to('/harmonisasi')->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * AJAX method untuk DataTable utama harmonisasi
     */
    public function ajax()
    {
        // Handle OPTIONS request for CORS preflight
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }

        $request = service('request');
        $module = $request->getPost('module') ?? 'harmonisasi';
        $customFilters = $request->getPost('custom_filters') ?? [];

        // Handle different modules
        if ($module === 'penugasan') {
            return $this->ajaxPenugasan();
        }

        // If custom filters are present, use the new method
        if (!empty($customFilters)) {
            return $this->ajaxWithFilters();
        }

        // ============================================================
        // PERMISSION CHECK: User harus punya permission read_all atau read_own
        // ============================================================
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            return $this->response->setJSON([
                'error' => 'Anda tidak memiliki permission untuk melihat data harmonisasi'
            ])->setStatusCode(403);
        }

        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON([
                'error' => 'Sesi tidak valid. Silakan login kembali.'
            ])->setStatusCode(401);
        }

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        try {
            // Query dasar dengan join
            $builder = $this->harmonisasiAjuanModel->select([
                'harmonisasi_ajuan.*',
                'harmonisasi_ajuan.id as id_ajuan',
                'user.nama as nama_pemohon',
                'instansi.nama_instansi',
                'harmonisasi_jenis_peraturan.nama_jenis',
                'harmonisasi_status.nama_status'
            ])
                ->join('user', 'user.id_user = harmonisasi_ajuan.id_user_pemohon', 'left')
                ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan', 'left')
                ->join('harmonisasi_status', 'harmonisasi_status.id = harmonisasi_ajuan.id_status_ajuan', 'left');

            // Filter berdasarkan permission
            if ($this->hasPermission('read_all')) {
                // User dengan read_all bisa lihat semua data
                // No additional filter needed
            } elseif ($this->hasPermission('read_own')) {
                // User dengan read_own hanya bisa lihat data milik sendiri
                $builder->where('harmonisasi_ajuan.id_user_pemohon', $user['id_user']);
            } else {
                // Tidak ada permission, return empty data
                return $this->response->setJSON([
                    "draw" => intval($draw),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => []
                ]);
            }

            // Filter pencarian
            if ($search) {
                $builder->groupStart()
                    ->like('harmonisasi_ajuan.judul_peraturan', $search)
                    ->orLike('harmonisasi_jenis_peraturan.nama_jenis', $search)
                    ->orLike('instansi.nama_instansi', $search)
                    ->orLike('user.nama', $search)
                    ->groupEnd();
            }

            $totalFiltered = $builder->countAllResults(false);
            $builder->orderBy('harmonisasi_ajuan.tanggal_pengajuan', 'DESC');
            $builder->limit($length, $start);

            $data = [];
            $no = $start + 1;
            foreach ($builder->get()->getResultArray() as $row) {
                // Determine available actions
                $can_edit = false;
                $can_submit = false;

                if ($row['id_status_ajuan'] == 1) { // Draft status
                    if (
                        $this->hasPermission('update_all') ||
                        ($this->hasPermission('update_own') && $row['id_user_pemohon'] == $user['id_user'])
                    ) {
                        $can_edit = true;
                        $can_submit = true;
                    }
                }

                // DataTables mengharapkan array dengan index numerik sesuai urutan kolom
                $data[] = [
                    '', // 0: No (akan di-render oleh DataTables)
                    esc($row['judul_peraturan']), // 1: Judul Rancangan
                    esc($row['nama_jenis']), // 2: Jenis
                    esc($row['nama_instansi']), // 3: Instansi Pemohon
                    !empty($row['tanggal_pengajuan']) ? date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) : '-', // 4: Tgl. Pengajuan
                    $row['id_status_ajuan'], // 5: Status ID (untuk render badge)
                    $row['id_ajuan'] // 6: ID untuk action buttons
                ];
            }

            $output = [
                "draw" => intval($draw),
                "recordsTotal" => $this->harmonisasiAjuanModel->countAllResults(),
                "recordsFiltered" => $totalFiltered,
                "data" => $data
            ];

            return $this->response->setJSON($output);
        } catch (\Exception $e) {
            log_message('error', 'Error in harmonisasi ajax: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Halaman khusus menampilkan tabel status SELESAI dan DITOLAK (id_status_ajuan 14, 15)
     * Bisa diakses semua user, khusus OPD hanya data miliknya
     */
    public function hasil()
    {
        // CRITICAL SECURITY FIX: Pastikan user sudah login
        $this->mustLoggedIn();
        
        // CRITICAL SECURITY FIX: Pastikan user memiliki permission
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Akses ditolak: Anda tidak memiliki permission untuk mengakses halaman ini');
            exit;
        }
        
        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        $role = $user['nama_role'] ?? '';
        $id_user = $user['id_user'] ?? 0;

        $db = db_connect();
        $builder = $db->table('harmonisasi_ajuan ha')
            ->select([
                'ha.*',
                'ha.id as id_ajuan',
                'u.nama as nama_pemohon',
                'i.nama_instansi',
                'j.nama_jenis',
                's.nama_status'
            ])
            ->join('user u', 'u.id_user = ha.id_user_pemohon', 'left')
            ->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left')
            ->join('harmonisasi_jenis_peraturan j', 'j.id = ha.id_jenis_peraturan', 'left')
            ->join('harmonisasi_status s', 's.id = ha.id_status_ajuan', 'left');
        $builder->whereIn('ha.id_status_ajuan', [14, 15]);
        // Filter hanya untuk OPD/pemohon, admin bisa melihat semua data
        if ((strtolower($role) === 'opd' || strtolower($role) === 'pemohon') && !empty($id_user)) {
            $builder->where('ha.id_user_pemohon', $id_user);
        }
        $builder->orderBy('ha.updated_at', 'DESC');
        $data = $builder->get()->getResultArray();

        $this->data['title'] = 'Daftar Ajuan Selesai & Ditolak';
        $this->data['ajuan_list'] = $data;
        $this->data['user'] = $user;
        $this->data['role'] = $role;
        return $this->view('harmonisasi/hasil', $this->data);
    }

    public function hasilAjax()
    {
        // Handle OPTIONS request for CORS preflight
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }

        // CRITICAL SECURITY FIX: Pastikan user sudah login
        $this->mustLoggedIn();
        
        // CRITICAL SECURITY FIX: Pastikan user memiliki permission
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            return $this->response->setJSON([
                'error' => 'Akses ditolak: Anda tidak memiliki permission untuk mengakses data ini'
            ])->setStatusCode(403);
        }

        $request = service('request');
        $model = new \App\Models\HarmonisasiAjuanModel();

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        // CRITICAL SECURITY FIX: Validasi user session dengan proper
        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON([
                'error' => 'Sesi tidak valid. Silakan login kembali.'
            ])->setStatusCode(401);
        }

        // Query dasar: status 14 (SELESAI) atau 15 (DITOLAK)
        $builder = $model->select('harmonisasi_ajuan.*, harmonisasi_ajuan.id as id_ajuan, harmonisasi_jenis_peraturan.nama_jenis as nama_jenis, instansi.nama_instansi')
            ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan', 'left')
            ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
            ->whereIn('harmonisasi_ajuan.id_status_ajuan', [14, 15]);

        // Filter khusus OPD/pemohon: hanya data instansi sendiri
        // Admin bisa melihat semua data (tidak terfilter)
        $role = strtolower($user['nama_role'] ?? '');
        if (($role === 'opd' || $role === 'pemohon') && !empty($user['id_instansi'])) {
            $builder->where('harmonisasi_ajuan.id_instansi_pemohon', $user['id_instansi']);
        }

        // Filter pencarian
        if ($search) {
            $builder->groupStart()
                ->like('harmonisasi_ajuan.judul_peraturan', $search)
                ->orLike('harmonisasi_jenis_peraturan.nama_jenis', $search)
                ->orLike('instansi.nama_instansi', $search)
                ->groupEnd();
        }

        $totalFiltered = $builder->countAllResults(false);
        $builder->orderBy('harmonisasi_ajuan.tanggal_pengajuan', 'DESC');
        $builder->limit($length, $start);

        $data = [];
        $no = $start + 1;
        foreach ($builder->get()->getResultArray() as $row) {
            // DataTables mengharapkan array dengan index numerik sesuai urutan kolom
            // Kirim data mentah (bukan HTML) karena JS akan render ulang
            $data[] = [
                '', // 0: No (akan di-render oleh DataTables)
                esc($row['judul_peraturan']), // 1: Judul Rancangan
                esc($row['nama_jenis'] ?? '-'), // 2: Jenis
                esc($row['nama_instansi'] ?? '-'), // 3: Instansi Pemohon
                !empty($row['tanggal_pengajuan']) ? date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) : '-', // 4: Tgl. Pengajuan
                $row['id_status_ajuan'], // 5: Status ID (akan di-render oleh JS)
                $row['id_ajuan'] // 6: ID untuk action buttons (akan di-render oleh JS)
            ];
        }

        $output = [
            "draw" => intval($draw),
            "recordsTotal" => $model->whereIn('id_status_ajuan', [14, 15])->countAllResults(),
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ];

        return $this->response->setJSON($output);
    }

    /**
     * AJAX method untuk DataTable penugasan
     */
    private function ajaxPenugasan()
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission read_all untuk penugasan
        // ============================================================
        if (!$this->hasPermission('read_all')) {
            return $this->response->setJSON([
                'error' => 'Anda tidak memiliki permission untuk melihat data penugasan'
            ])->setStatusCode(403);
        }

        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON([
                'error' => 'Sesi tidak valid. Silakan login kembali.'
            ])->setStatusCode(401);
        }

        $request = service('request');

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';

        try {
            // Query untuk ajuan yang menunggu penugasan (status 2 = Submitted)
            $builder = $this->harmonisasiAjuanModel->select([
                'harmonisasi_ajuan.*',
                'harmonisasi_ajuan.id as id_ajuan',
                'user.nama as nama_pemohon',
                'instansi.nama_instansi',
                'harmonisasi_jenis_peraturan.nama_jenis',
                'harmonisasi_status.nama_status'
            ])
                ->join('user', 'user.id_user = harmonisasi_ajuan.id_user_pemohon', 'left')
                ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan', 'left')
                ->join('harmonisasi_status', 'harmonisasi_status.id = harmonisasi_ajuan.id_status_ajuan', 'left')
                ->where('harmonisasi_ajuan.id_status_ajuan', 2); // Status Submitted

            // Filter pencarian
            if ($search) {
                $builder->groupStart()
                    ->like('harmonisasi_ajuan.judul_peraturan', $search)
                    ->orLike('harmonisasi_jenis_peraturan.nama_jenis', $search)
                    ->orLike('instansi.nama_instansi', $search)
                    ->orLike('user.nama', $search)
                    ->groupEnd();
            }

            $totalFiltered = $builder->countAllResults(false);
            $builder->orderBy('harmonisasi_ajuan.tanggal_pengajuan', 'DESC');
            $builder->limit($length, $start);

            $data = [];
            foreach ($builder->get()->getResultArray() as $row) {
                // DataTables mengharapkan array dengan index numerik sesuai urutan kolom
                $data[] = [
                    '', // 0: No (akan di-render oleh DataTables)
                    esc($row['judul_peraturan']), // 1: Judul Rancangan
                    esc($row['nama_jenis']), // 2: Jenis
                    esc($row['nama_instansi']), // 3: Instansi Pemohon
                    !empty($row['tanggal_pengajuan']) ? date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) : '-', // 4: Tgl. Pengajuan
                    $row['id_status_ajuan'], // 5: Status ID (untuk render badge)
                    $row['id_ajuan'] // 6: ID untuk action buttons
                ];
            }

            $totalRecords = $this->harmonisasiAjuanModel->where('id_status_ajuan', 2)->countAllResults();

            return $this->response->setJSON([
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalFiltered,
                "data" => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in ajaxPenugasan: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Terjadi kesalahan saat memuat data penugasan'
            ])->setStatusCode(500);
        }
    }

    /**
     * AJAX method untuk mendapatkan jenis peraturan
     */
    public function getJenisPeraturan()
    {
        // Handle OPTIONS request for CORS preflight
        if ($this->request->getMethod() === 'options') {
            return $this->response->setStatusCode(200);
        }

        // ============================================================
        // PERMISSION CHECK: User harus punya permission read_all atau read_own
        // ============================================================
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            return $this->response->setJSON([
                'error' => 'Anda tidak memiliki permission untuk mengakses data jenis peraturan'
            ])->setStatusCode(403);
        }

        try {
            $jenisPeraturan = $this->jenisPeraturanModel->findAll();

            return $this->response->setJSON([
                'success' => true,
                'data' => $jenisPeraturan
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting jenis peraturan: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Terjadi kesalahan saat memuat data jenis peraturan'
            ])->setStatusCode(500);
        }
    }

    /**
     * AJAX method untuk DataTable dengan custom filters
     */
    private function ajaxWithFilters()
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission read_all atau read_own
        // ============================================================
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            return $this->response->setJSON([
                'error' => 'Anda tidak memiliki permission untuk mengakses data harmonisasi'
            ])->setStatusCode(403);
        }

        $user = $this->validateUserSession();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $this->response->setJSON([
                'error' => 'Sesi tidak valid. Silakan login kembali.'
            ])->setStatusCode(401);
        }

        $request = service('request');

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';
        $customFilters = $request->getPost('custom_filters') ?? [];

        try {
            // Base query dengan JOIN
            $builder = $this->harmonisasiAjuanModel->select([
                'harmonisasi_ajuan.*',
                'harmonisasi_ajuan.id as id_ajuan',
                'user.nama as nama_pemohon',
                'instansi.nama_instansi',
                'harmonisasi_jenis_peraturan.nama_jenis',
                'harmonisasi_status.nama_status'
            ])
                ->join('user', 'user.id_user = harmonisasi_ajuan.id_user_pemohon', 'left')
                ->join('instansi', 'instansi.id = harmonisasi_ajuan.id_instansi_pemohon', 'left')
                ->join('harmonisasi_jenis_peraturan', 'harmonisasi_jenis_peraturan.id = harmonisasi_ajuan.id_jenis_peraturan', 'left')
                ->join('harmonisasi_status', 'harmonisasi_status.id = harmonisasi_ajuan.id_status_ajuan', 'left');

            // Apply permission-based filtering
            if ($this->hasPermission('read_all')) {
                // Admin can see all data
            } else if ($this->hasPermission('read_own')) {
                // User can only see their own data
                $builder->where('harmonisasi_ajuan.id_user_pemohon', $user['id_user']);
            }

            // Apply custom filters
            if (!empty($customFilters)) {
                // Status filter
                if (!empty($customFilters['status'])) {
                    $builder->where('harmonisasi_ajuan.id_status_ajuan', $customFilters['status']);
                }

                // Jenis peraturan filter
                if (!empty($customFilters['jenis'])) {
                    $builder->where('harmonisasi_ajuan.id_jenis_peraturan', $customFilters['jenis']);
                }

                // Date range filter
                if (!empty($customFilters['start_date'])) {
                    $builder->where('DATE(harmonisasi_ajuan.tanggal_pengajuan) >=', $customFilters['start_date']);
                }

                if (!empty($customFilters['end_date'])) {
                    $builder->where('DATE(harmonisasi_ajuan.tanggal_pengajuan) <=', $customFilters['end_date']);
                }
            }

            // Apply search filter
            if ($search) {
                $builder->groupStart()
                    ->like('harmonisasi_ajuan.judul_peraturan', $search)
                    ->orLike('harmonisasi_jenis_peraturan.nama_jenis', $search)
                    ->orLike('instansi.nama_instansi', $search)
                    ->orLike('user.nama', $search)
                    ->groupEnd();
            }

            // Get total filtered count
            $totalFiltered = $builder->countAllResults(false);

            // Apply ordering and pagination
            $builder->orderBy('harmonisasi_ajuan.tanggal_pengajuan', 'DESC');
            $builder->limit($length, $start);

            // Get data
            $data = [];
            foreach ($builder->get()->getResultArray() as $row) {
                // DataTables mengharapkan array dengan index numerik sesuai urutan kolom
                $data[] = [
                    '', // 0: No (akan di-render oleh DataTables)
                    esc($row['judul_peraturan']), // 1: Judul Rancangan
                    esc($row['nama_jenis']), // 2: Jenis
                    esc($row['nama_instansi']), // 3: Instansi Pemohon
                    !empty($row['tanggal_pengajuan']) ? date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) : '-', // 4: Tgl. Pengajuan
                    $row['id_status_ajuan'], // 5: Status ID (untuk render badge)
                    $row['id_ajuan'] // 6: ID untuk action buttons
                ];
            }

            // Get total records count
            $totalRecords = $this->harmonisasiAjuanModel->countAll();

            return $this->response->setJSON([
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalFiltered,
                "data" => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in ajaxWithFilters: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Terjadi kesalahan saat memuat data harmonisasi'
            ])->setStatusCode(500);
        }
    }
}
