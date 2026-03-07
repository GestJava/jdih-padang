<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiTteLogModel;
use App\Models\HarmonisasiJenisPeraturanModel;
use App\Models\HarmonisasiNomorPeraturanModel;
use App\Config\HarmonisasiStatus;

/**
 * Controller untuk Laporan Legalisasi
 * Standalone controller tanpa dependensi ke module legalisasi
 */
class Laporan extends BaseController
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiTteLogModel;
    protected $jenisPeraturanModel;
    protected $nomorPeraturanModel;
    protected $db;

    public function __construct()
    {
        parent::__construct();

        // ============================================================
        // PERMISSION-BASED ACCESS CONTROL
        // ============================================================
        $this->mustLoggedIn();

        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->harmonisasiTteLogModel = new HarmonisasiTteLogModel();
        $this->jenisPeraturanModel = new HarmonisasiJenisPeraturanModel();
        $this->nomorPeraturanModel = new HarmonisasiNomorPeraturanModel();
        $this->db = \Config\Database::connect();

        helper(['form', 'url', 'filesystem']);

        // Add DataTables extensions
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js'));
        $this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css'));
    }

    /**
     * Halaman utama laporan
     */
    public function index()
    {
        try {
            $this->data['title'] = 'Laporan Legalisasi';

            // Get filter parameters
            $tahun = $this->request->getGet('tahun') ?? date('Y');
            $bulan = $this->request->getGet('bulan') ?? null;
            $id_instansi = $this->request->getGet('instansi') ?? null;
            $id_jenis = $this->request->getGet('jenis') ?? null;
            $status = $this->request->getGet('status') ?? null;

            $this->data['tahun'] = $tahun;
            $this->data['bulan'] = $bulan;
            $this->data['id_instansi'] = $id_instansi;
            $this->data['id_jenis'] = $id_jenis;
            $this->data['status'] = $status;

            // Build query dengan JOIN manual
            // Optimasi: hanya ambil field yang diperlukan untuk statistik
            $query = $this->db->table('harmonisasi_ajuan ha')
                ->select([
                    'ha.id',
                    'ha.id_status_ajuan',
                    'ha.created_at',
                    'i.nama_instansi',
                    'j.nama_jenis'
                ])
                ->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left')
                ->join('harmonisasi_jenis_peraturan j', 'j.id = ha.id_jenis_peraturan', 'left');
            
            // Apply filters
            if ($tahun) {
                $query->where('YEAR(ha.created_at)', $tahun);
            }
            if ($bulan) {
                $query->where('MONTH(ha.created_at)', $bulan);
            }
            if ($id_instansi) {
                $query->where('ha.id_instansi_pemohon', $id_instansi);
            }
            if ($id_jenis) {
                $query->where('ha.id_jenis_peraturan', $id_jenis);
            }
            if ($status) {
                $query->where('ha.id_status_ajuan', $status);
            }

            // Get all data dengan limit untuk mencegah timeout
            try {
                $allAjuan = $query->get()->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Query laporan timeout: ' . $e->getMessage());
                // Fallback: ambil data tanpa JOIN jika query terlalu berat
                $querySimple = $this->db->table('harmonisasi_ajuan ha')
                    ->select(['ha.id', 'ha.id_status_ajuan', 'ha.created_at', 'ha.id_instansi_pemohon', 'ha.id_jenis_peraturan']);
                if ($tahun) {
                    $querySimple->where('YEAR(ha.created_at)', $tahun);
                }
                if ($bulan) {
                    $querySimple->where('MONTH(ha.created_at)', $bulan);
                }
                if ($id_instansi) {
                    $querySimple->where('ha.id_instansi_pemohon', $id_instansi);
                }
                if ($id_jenis) {
                    $querySimple->where('ha.id_jenis_peraturan', $id_jenis);
                }
                if ($status) {
                    $querySimple->where('ha.id_status_ajuan', $status);
                }
                $allAjuan = $querySimple->get()->getResultArray();
                
                // Ambil nama instansi dan jenis secara terpisah
                foreach ($allAjuan as &$ajuan) {
                    if ($ajuan['id_instansi_pemohon']) {
                        $instansi = $this->db->table('instansi')->where('id', $ajuan['id_instansi_pemohon'])->get()->getRowArray();
                        $ajuan['nama_instansi'] = $instansi['nama_instansi'] ?? 'Tidak Diketahui';
                    } else {
                        $ajuan['nama_instansi'] = 'Tidak Diketahui';
                    }
                    if ($ajuan['id_jenis_peraturan']) {
                        $jenis = $this->db->table('harmonisasi_jenis_peraturan')->where('id', $ajuan['id_jenis_peraturan'])->get()->getRowArray();
                        $ajuan['nama_jenis'] = $jenis['nama_jenis'] ?? 'Tidak Diketahui';
                    } else {
                        $ajuan['nama_jenis'] = 'Tidak Diketahui';
                    }
                }
            }

            // 1. STATISTIK UMUM
            $selesai = 0;
            $ditolak = 0;
            $dalam_proses = 0;
            foreach ($allAjuan as $a) {
                if ($a['id_status_ajuan'] == HarmonisasiStatus::SELESAI) {
                    $selesai++;
                } elseif ($a['id_status_ajuan'] == HarmonisasiStatus::DITOLAK) {
                    $ditolak++;
                } else {
                    $dalam_proses++;
                }
            }
            $this->data['stats'] = [
                'total_dokumen' => count($allAjuan),
                'selesai' => $selesai,
                'ditolak' => $ditolak,
                'dalam_proses' => $dalam_proses,
            ];

            // 2. STATISTIK PER JENIS PERATURAN
            $statsPerJenis = [];
            foreach ($allAjuan as $ajuan) {
                $jenis = $ajuan['nama_jenis'] ?? 'Tidak Diketahui';
                if (!isset($statsPerJenis[$jenis])) {
                    $statsPerJenis[$jenis] = [
                        'nama_jenis' => $jenis,
                        'total' => 0,
                        'selesai' => 0,
                        'ditolak' => 0,
                        'dalam_proses' => 0
                    ];
                }
                $statsPerJenis[$jenis]['total']++;
                if ($ajuan['id_status_ajuan'] == HarmonisasiStatus::SELESAI) {
                    $statsPerJenis[$jenis]['selesai']++;
                } elseif ($ajuan['id_status_ajuan'] == HarmonisasiStatus::DITOLAK) {
                    $statsPerJenis[$jenis]['ditolak']++;
                } else {
                    $statsPerJenis[$jenis]['dalam_proses']++;
                }
            }
            $this->data['stats_per_jenis'] = array_values($statsPerJenis);

            // 3. STATISTIK PER INSTANSI
            $statsPerInstansi = [];
            foreach ($allAjuan as $ajuan) {
                $instansi = $ajuan['nama_instansi'] ?? 'Tidak Diketahui';
                if (!isset($statsPerInstansi[$instansi])) {
                    $statsPerInstansi[$instansi] = [
                        'nama_instansi' => $instansi,
                        'total' => 0,
                        'selesai' => 0
                    ];
                }
                $statsPerInstansi[$instansi]['total']++;
                if ($ajuan['id_status_ajuan'] == HarmonisasiStatus::SELESAI) {
                    $statsPerInstansi[$instansi]['selesai']++;
                }
            }
            // Sort by total descending
            usort($statsPerInstansi, function($a, $b) {
                return $b['total'] - $a['total'];
            });
            $this->data['stats_per_instansi'] = array_slice($statsPerInstansi, 0, 10); // Top 10

            // 4. STATISTIK TTE
            $tteQuery = $this->db->table('harmonisasi_tte_log');
            if ($tahun) {
                $tteQuery->where('YEAR(created_at)', $tahun);
            }
            $allTteLogs = $tteQuery->get()->getResultArray();
            
            $tte_success = 0;
            $tte_failed = 0;
            $tte_pending = 0;
            foreach ($allTteLogs as $l) {
                $status = $l['status_tte'] ?? $l['status'] ?? '';
                $statusUpper = strtoupper($status);
                if ($statusUpper == 'SUCCESS') {
                    $tte_success++;
                } elseif ($statusUpper == 'FAILED') {
                    $tte_failed++;
                } elseif ($statusUpper == 'PENDING') {
                    $tte_pending++;
                }
            }
            $this->data['stats_tte'] = [
                'total_attempts' => count($allTteLogs),
                'success' => $tte_success,
                'failed' => $tte_failed,
                'pending' => $tte_pending,
            ];

            // 5. STATISTIK PENOMORAN (dari harmonisasi_nomor_peraturan)
            $nomorQuery = $this->db->table('harmonisasi_nomor_peraturan');
            if ($tahun) {
                $nomorQuery->where('tahun', $tahun);
            }
            $allNomor = $nomorQuery->get()->getResultArray();
            
            $nomor_sekda = 0;
            $nomor_walikota = 0;
            foreach ($allNomor as $n) {
                $role = $n['user_role'] ?? $n['tte_user_role'] ?? '';
                if (stripos($role, 'sekda') !== false) {
                    $nomor_sekda++;
                } else {
                    $nomor_walikota++;
                }
            }
            $this->data['stats_penomoran'] = [
                'total_nomor' => count($allNomor),
                'sekda' => $nomor_sekda,
                'walikota' => $nomor_walikota,
            ];

            // 6. STATISTIK PER BULAN (untuk chart)
            $statsPerBulan = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthTotal = 0;
                $monthSelesai = 0;
                foreach ($allAjuan as $a) {
                    $month = (int)date('m', strtotime($a['created_at']));
                    if ($month == $m) {
                        $monthTotal++;
                        if ($a['id_status_ajuan'] == HarmonisasiStatus::SELESAI) {
                            $monthSelesai++;
                        }
                    }
                }
                $statsPerBulan[$m] = [
                    'bulan' => date('M', mktime(0, 0, 0, $m, 1)),
                    'total' => $monthTotal,
                    'selesai' => $monthSelesai
                ];
            }
            $this->data['stats_per_bulan'] = array_values($statsPerBulan);

            // 7. DATA UNTUK DROPDOWN FILTER
            // Get list instansi
            $instansiModel = model('InstansiModel');
            $this->data['list_instansi'] = $instansiModel->findAll();
            
            // Get list jenis peraturan
            $this->data['list_jenis'] = $this->jenisPeraturanModel->findAll();
            
            // Get list status (query langsung ke tabel)
            $this->data['list_status'] = $this->db->table('harmonisasi_status')->get()->getResultArray();

            return $this->view('legalisasi/laporan', $this->data);

        } catch (\Exception $e) {
            log_message('error', 'Laporan error: ' . $e->getMessage());
            $this->data['error'] = 'Terjadi kesalahan saat memuat laporan: ' . $e->getMessage();
            $this->data['stats'] = [
                'total_dokumen' => 0,
                'selesai' => 0,
                'ditolak' => 0,
                'dalam_proses' => 0
            ];
            $this->data['stats_per_jenis'] = [];
            $this->data['stats_per_instansi'] = [];
            $this->data['stats_tte'] = [
                'total_attempts' => 0,
                'success' => 0,
                'failed' => 0,
                'pending' => 0
            ];
            $this->data['stats_penomoran'] = [
                'total_nomor' => 0,
                'sekda' => 0,
                'walikota' => 0
            ];
            $this->data['stats_per_bulan'] = [];
            return $this->view('legalisasi/laporan', $this->data);
        }
    }

    /**
     * Monitoring penomoran peraturan berdasarkan tabel harmonisasi_nomor_peraturan
     */
    public function monitoring()
    {
        try {
            $this->data['title'] = 'Monitoring Penomoran';

            // Get tahun filter dari query string, default tahun sekarang
            $tahun = $this->request->getGet('tahun') ?? date('Y');
            $this->data['tahun'] = $tahun;

            // Ambil semua data nomor peraturan untuk tahun yang dipilih
            $allNomorPeraturan = $this->nomorPeraturanModel
                ->where('tahun', $tahun)
                ->orderBy('jenis_peraturan', 'ASC')
                ->orderBy('urutan', 'ASC')
                ->findAll();

            // Group by jenis_peraturan dan hitung statistik
            $sequences = [];
            $usage_stats = [];
            $jenis_list = [];

            foreach ($allNomorPeraturan as $nomor) {
                $jenis = $nomor['jenis_peraturan'];
                
                // Kumpulkan jenis peraturan unik
                if (!in_array($jenis, $jenis_list)) {
                    $jenis_list[] = $jenis;
                }

                // Hitung usage stats
                if (!isset($usage_stats[$jenis])) {
                    $usage_stats[$jenis] = 0;
                }
                $usage_stats[$jenis]++;

                // Tentukan authority level berdasarkan user_role atau tte_user_role
                $authority_level = 'walikota'; // default
                $user_role = $nomor['tte_user_role'] ?? $nomor['user_role'] ?? '';
                
                // Jenis peraturan yang authority-nya Sekda
                $sekda_jenis = ['Keputusan Sekda', 'Instruksi Sekda', 'Surat Edaran Sekda', 'Keputusan Sekretaris Daerah', 'Instruksi Sekretaris Daerah'];
                if (in_array($jenis, $sekda_jenis) || stripos($user_role, 'sekda') !== false) {
                    $authority_level = 'sekda';
                }

                // Simpan atau update sequence data
                if (!isset($sequences[$jenis])) {
                    $sequences[$jenis] = [
                        'jenis_peraturan' => $jenis,
                        'prefix_nomor' => $this->getPrefixNomor($jenis),
                        'authority_level' => $authority_level,
                        'last_number' => 0,
                        'last_issued_at' => null,
                        'first_issued_at' => null,
                        'nomor_peraturan_terakhir' => null
                    ];
                }

                // Update last number berdasarkan urutan (bukan nomor_peraturan)
                $urutan = (int)($nomor['urutan'] ?? 0);
                if ($urutan > $sequences[$jenis]['last_number']) {
                    $sequences[$jenis]['last_number'] = $urutan;
                    $sequences[$jenis]['nomor_peraturan_terakhir'] = $nomor['nomor_peraturan'] ?? null;
                }

                // Update tanggal terakhir diterbitkan
                $issued_at = $nomor['tte_completed_at'] ?? $nomor['tanggal_pengesahan'] ?? $nomor['created_at'] ?? null;
                if ($issued_at) {
                    if (!$sequences[$jenis]['last_issued_at'] || strtotime($issued_at) > strtotime($sequences[$jenis]['last_issued_at'])) {
                        $sequences[$jenis]['last_issued_at'] = $issued_at;
                    }
                    if (!$sequences[$jenis]['first_issued_at'] || strtotime($issued_at) < strtotime($sequences[$jenis]['first_issued_at'])) {
                        $sequences[$jenis]['first_issued_at'] = $issued_at;
                    }
                }
            }

            // Convert sequences array to indexed array untuk view
            $sequences = array_values($sequences);

            // Sort sequences by jenis_peraturan
            usort($sequences, function($a, $b) {
                return strcmp($a['jenis_peraturan'], $b['jenis_peraturan']);
            });

            $this->data['sequences'] = $sequences;
            $this->data['usage_stats'] = $usage_stats;

            return $this->view('legalisasi/monitoring', $this->data);

        } catch (\Exception $e) {
            log_message('error', 'Monitoring error: ' . $e->getMessage());
            $this->data['error'] = 'Terjadi kesalahan saat memuat data monitoring: ' . $e->getMessage();
            $this->data['sequences'] = [];
            $this->data['usage_stats'] = [];
            $this->data['tahun'] = $this->request->getGet('tahun') ?? date('Y');
            return $this->view('legalisasi/monitoring', $this->data);
        }
    }

    /**
     * History TTE - Menampilkan log aktivitas TTE
     */
    public function historyTte()
    {
        try {
            $this->data['title'] = 'Riwayat TTE (Tanda Tangan Elektronik)';

            // Cek apakah tabel harmonisasi_tte_log ada
            if (!$this->db->tableExists('harmonisasi_tte_log')) {
                $this->data['error'] = 'Tabel harmonisasi_tte_log tidak ditemukan di database.';
                $this->data['stats'] = [
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'pending' => 0
                ];
                $this->data['tte_logs'] = [];
                $this->data['list_users'] = [];
                $this->data['list_ajuan'] = [];
                return $this->view('legalisasi/history_tte', $this->data);
            }

            // Get filter parameters
            $tahun = $this->request->getGet('tahun') ?? date('Y');
            $bulan = $this->request->getGet('bulan') ?? null;
            $status = $this->request->getGet('status') ?? null;
            $id_user = $this->request->getGet('user') ?? null;
            $id_ajuan = $this->request->getGet('ajuan') ?? null;
            $jenis_aksi = $this->request->getGet('jenis_aksi') ?? null;
            $tanggal_dari = $this->request->getGet('tanggal_dari') ?? null;
            $tanggal_sampai = $this->request->getGet('tanggal_sampai') ?? null;

            $this->data['tahun'] = $tahun;
            $this->data['bulan'] = $bulan;
            $this->data['status'] = $status;
            $this->data['id_user'] = $id_user;
            $this->data['id_ajuan'] = $id_ajuan;
            $this->data['jenis_aksi'] = $jenis_aksi;
            $this->data['tanggal_dari'] = $tanggal_dari;
            $this->data['tanggal_sampai'] = $tanggal_sampai;

            // Struktur tabel harmonisasi_tte_log yang sebenarnya:
            // id, id_ajuan, id_user, action, status, document_number, signed_path, error_message, created_at, updated_at
            $userField = 'id_user';
            $actionField = 'action';
            $statusField = 'status';
            $signedPathField = 'signed_path';

            // OPTIMASI: Build query dengan JOIN yang efisien dan hanya field yang diperlukan
            $query = $this->db->table('harmonisasi_tte_log tte')
                ->select([
                    'tte.id',
                    'tte.id_ajuan',
                    'tte.id_user',
                    'tte.action',
                    'tte.status',
                    'tte.document_number',
                    'tte.signed_path',
                    'tte.error_message',
                    'tte.created_at',
                    'ha.judul_peraturan',
                    'ha.id as id_ajuan',
                    'u.nama as nama_penandatangan',
                    'u.email as email_penandatangan',
                    'j.nama_jenis',
                    'i.nama_instansi'
                ])
                ->join('harmonisasi_ajuan ha', 'ha.id = tte.id_ajuan', 'left')
                ->join('user u', 'u.id_user = tte.id_user', 'left')
                ->join('harmonisasi_jenis_peraturan j', 'j.id = ha.id_jenis_peraturan', 'left')
                ->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left')
                ->orderBy('tte.created_at', 'DESC');

            // Apply filters
            if ($tahun) {
                $query->where('YEAR(tte.created_at)', $tahun);
            }
            if ($bulan) {
                $query->where('MONTH(tte.created_at)', $bulan);
            }
            if ($tanggal_dari) {
                $query->where('DATE(tte.created_at) >=', $tanggal_dari);
            }
            if ($tanggal_sampai) {
                $query->where('DATE(tte.created_at) <=', $tanggal_sampai);
            }
            if ($status) {
                $statusUpper = strtoupper($status);
                $query->where('tte.status', $statusUpper);
            }
            if ($id_user) {
                $query->where('tte.id_user', $id_user);
            }
            if ($id_ajuan) {
                $query->where('tte.id_ajuan', $id_ajuan);
            }
            if ($jenis_aksi) {
                $query->where('tte.action', $jenis_aksi);
            }

            // OPTIMASI: Tambahkan pagination dan limit untuk performa
            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = 50;
            $offset = ($page - 1) * $perPage;
            
            // Hitung total records untuk pagination
            $totalRecords = 0;
            try {
                $countQuery = $this->db->table('harmonisasi_tte_log tte');
                if ($tahun) {
                    $countQuery->where('YEAR(tte.created_at)', $tahun);
                }
                if ($bulan) {
                    $countQuery->where('MONTH(tte.created_at)', $bulan);
                }
                if ($tanggal_dari) {
                    $countQuery->where('DATE(tte.created_at) >=', $tanggal_dari);
                }
                if ($tanggal_sampai) {
                    $countQuery->where('DATE(tte.created_at) <=', $tanggal_sampai);
                }
                if ($status) {
                    $statusUpper = strtoupper($status);
                    $countQuery->where('tte.status', $statusUpper);
                }
                if ($id_user) {
                    $countQuery->where('tte.id_user', $id_user);
                }
                if ($id_ajuan) {
                    $countQuery->where('tte.id_ajuan', $id_ajuan);
                }
                if ($jenis_aksi) {
                    $countQuery->where('tte.action', $jenis_aksi);
                }
                $totalRecords = $countQuery->countAllResults(false);
            } catch (\Exception $e) {
                log_message('error', 'History TTE count error: ' . $e->getMessage());
            }
            
            // Get data dengan limit dan offset
            try {
                $allTteLogs = $query->limit($perPage, $offset)->get()->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'History TTE query error: ' . $e->getMessage());
                $allTteLogs = [];
            }
            
            // Pagination data
            $this->data['pagination'] = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_records' => $totalRecords,
                'total_pages' => ceil($totalRecords / $perPage),
                'has_prev' => $page > 1,
                'has_next' => $page < ceil($totalRecords / $perPage)
            ];

            // Process data dan set field final
            foreach ($allTteLogs as &$log) {
                $log['status_final'] = $log['status'] ?? 'UNKNOWN';
                $log['action_final'] = $log['action'] ?? 'UNKNOWN';
                $log['id_user_final'] = $log['id_user'] ?? null;
                $log['signed_path_final'] = $log['signed_path'] ?? null;
                $log['document_number_final'] = $log['document_number'] ?? null;
            }

            // STATISTIK
            $stats = [
                'total' => count($allTteLogs),
                'success' => 0,
                'failed' => 0,
                'pending' => 0
            ];
            foreach ($allTteLogs as $log) {
                $stat = strtoupper($log['status_final'] ?? 'UNKNOWN');
                if ($stat == 'SUCCESS') {
                    $stats['success']++;
                } elseif ($stat == 'FAILED') {
                    $stats['failed']++;
                } elseif ($stat == 'PENDING') {
                    $stats['pending']++;
                }
            }
            $this->data['stats'] = $stats;
            $this->data['tte_logs'] = $allTteLogs;

            // DATA UNTUK DROPDOWN FILTER - DENGAN CACHING
            $cache = \Config\Services::cache();
            $cacheKeyUsers = 'tte_history_users_' . md5($tahun . $bulan);
            $cacheKeyAjuan = 'tte_history_ajuan_' . md5($tahun . $bulan);
            
            // Get list users yang pernah melakukan TTE (cached 1 jam)
            try {
                $this->data['list_users'] = $cache->get($cacheKeyUsers);
                if ($this->data['list_users'] === null) {
                    $usersQuery = $this->db->table('harmonisasi_tte_log tte')
                        ->select('u.id_user, u.nama', false)
                        ->join('user u', 'u.id_user = tte.id_user', 'left')
                        ->where('u.id_user IS NOT NULL')
                        ->groupBy('u.id_user, u.nama')
                        ->orderBy('u.nama', 'ASC')
                        ->limit(100);
                    $this->data['list_users'] = $usersQuery->get()->getResultArray();
                    $cache->save($cacheKeyUsers, $this->data['list_users'], 3600);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting list users: ' . $e->getMessage());
                $this->data['list_users'] = [];
            }

            // Get list ajuan yang pernah di-TTE (cached 1 jam)
            try {
                $this->data['list_ajuan'] = $cache->get($cacheKeyAjuan);
                if ($this->data['list_ajuan'] === null) {
                    $ajuanQuery = $this->db->table('harmonisasi_tte_log tte')
                        ->select('ha.id, ha.judul_peraturan', false)
                        ->join('harmonisasi_ajuan ha', 'ha.id = tte.id_ajuan', 'left')
                        ->where('ha.id IS NOT NULL')
                        ->groupBy('ha.id, ha.judul_peraturan')
                        ->orderBy('ha.judul_peraturan', 'ASC')
                        ->limit(100);
                    $this->data['list_ajuan'] = $ajuanQuery->get()->getResultArray();
                    $cache->save($cacheKeyAjuan, $this->data['list_ajuan'], 3600);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error getting list ajuan: ' . $e->getMessage());
                $this->data['list_ajuan'] = [];
            }

            return $this->view('legalisasi/history_tte', $this->data);

        } catch (\Exception $e) {
            log_message('error', 'History TTE error: ' . $e->getMessage());
            $this->data['error'] = 'Terjadi kesalahan saat memuat riwayat TTE: ' . $e->getMessage();
            $this->data['stats'] = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'pending' => 0
            ];
            $this->data['tte_logs'] = [];
            $this->data['list_users'] = [];
            $this->data['list_ajuan'] = [];
            return $this->view('legalisasi/history_tte', $this->data);
        }
    }

    /**
     * Helper function untuk mendapatkan prefix nomor berdasarkan jenis peraturan
     */
    private function getPrefixNomor($jenis_peraturan)
    {
        $prefixes = [
            'Peraturan Daerah' => 'PERDA',
            'Peraturan Walikota' => 'PERWAL',
            'Peraturan DPRD' => 'PERDPRD',
            'Keputusan Walikota' => 'KEPWAL',
            'Instruksi Walikota' => 'INWAL',
            'Surat Edaran Walikota' => 'SEWAL',
            'Keputusan Sekda' => 'KEPSEKDA',
            'Instruksi Sekda' => 'INSEKDA',
            'Surat Edaran Sekda' => 'SESEKDA',
            'Keputusan Sekretaris Daerah' => 'KEPSEKDA',
            'Instruksi Sekretaris Daerah' => 'INSEKDA',
        ];

        // Cek exact match
        if (isset($prefixes[$jenis_peraturan])) {
            return $prefixes[$jenis_peraturan];
        }

        // Cek partial match
        foreach ($prefixes as $key => $prefix) {
            if (stripos($jenis_peraturan, $key) !== false) {
                return $prefix;
            }
        }

        // Default: ambil 3-4 karakter pertama
        return strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $jenis_peraturan), 0, 4));
    }
}

