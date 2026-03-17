<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiDokumenModel;
use App\Models\HarmonisasiHistoriModel;
use App\Config\HarmonisasiStatus;

class Validasi extends BaseController
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiDokumenModel;
    protected $harmonisasiHistoriModel;

    public function __construct()
    {
        die('Validasi controller reached');
        parent::__construct(); // Memanggil konstruktor BaseController

        // ============================================================
        // PERMISSION-BASED ACCESS CONTROL - VALIDASI MODULE
        // ============================================================
        $this->mustLoggedIn(); // Pastikan user sudah login

        // User harus punya minimal read_all atau read_own permission untuk module validasi
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Akses ditolak: Anda tidak memiliki permission untuk mengakses module validasi');
            exit;
        }

        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->harmonisasiDokumenModel = new HarmonisasiDokumenModel();
        $this->harmonisasiHistoriModel = new HarmonisasiHistoriModel();
        helper(['form', 'url', 'filesystem']);

        // Add Harmonisasi Module CSS for standardized V2 Ultra Premium Design System
        $this->addStyle(base_url('jdih/assets/css/harmonisasi-module.css?v=' . time()));

        // Add DataTables Buttons extension scripts (tanpa data-tables.js karena sudah ada inisialisasi manual di view)
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/JSZip/jszip.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/pdfmake.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/vfs_fonts.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.html5.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.print.min.js'));
        $this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css'));
    }

    // Menampilkan dashboard untuk validator
    public function index()
    {
        $user = session()->get('user');
        if (!$user || !isset($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        $this->data['title'] = 'Dashboard Validasi Harmonisasi';

        // Menampilkan ajuan dengan status = 4 (Proses Validasi) untuk semua user
        $ajuan = $this->harmonisasiAjuanModel->getAjuanWithDetailsByStatus(4);
        $this->data['ajuan_list'] = $ajuan;
        $this->data['user'] = $user;

        // --- Tambahan statistik tugas per validator ---
        $validatorModel = new \App\Models\UserModel();
        $validator_list = $validatorModel->getUsersByRoleId(8); // Role ID 8 untuk validator
        $validator_stats = [];
        foreach ($validator_list as $validator) {
            $jumlah = 0;
            foreach ($ajuan as $item) {
                // Untuk validasi, hitung semua ajuan dengan status 4 (Proses Validasi)
                // karena tidak ada field id_petugas_validasi dalam database
                if ($item['id_status_ajuan'] == 4) {
                    $jumlah++;
                }
            }
            $validator_stats[] = [
                'nama' => $validator['nama'],
                'jumlah' => $jumlah
            ];
        }
        // Urutkan desc berdasarkan jumlah tugas
        usort($validator_stats, function ($a, $b) {
            return $b['jumlah'] <=> $a['jumlah'];
        });
        $this->data['validator_stats'] = $validator_stats;
        // --- End statistik ---

        // ============================================================
        // STATISTIK TAHUN INI
        // ============================================================
        $current_year = date('Y');
        $year_stats = [];

        if ($this->hasPermission('read_all')) {
            // Admin/Supervisor: Statistik tahun ini untuk semua validator
            $year_stats = [
                'total_ajuan_tahun_ini' => $this->harmonisasiAjuanModel->getTotalAjuanByYear($current_year),
                'total_selesai_tahun_ini' => $this->harmonisasiAjuanModel->getTotalSelesaiByYear($current_year),
                'total_ditolak_tahun_ini' => $this->harmonisasiAjuanModel->getTotalDitolakByYear($current_year),
                'total_proses_tahun_ini' => $this->harmonisasiAjuanModel->getTotalProsesByYear($current_year)
            ];
        } elseif ($this->hasPermission('read_own')) {
            // Validator: Statistik tahun ini untuk semua ajuan yang masuk ke tahap validasi
            $year_stats = [
                'total_ajuan_tahun_ini' => $this->harmonisasiAjuanModel->getTotalAjuanByYear($current_year),
                'total_selesai_tahun_ini' => $this->harmonisasiAjuanModel->getTotalSelesaiByYear($current_year),
                'total_ditolak_tahun_ini' => $this->harmonisasiAjuanModel->getTotalDitolakByYear($current_year),
                'total_proses_tahun_ini' => $this->harmonisasiAjuanModel->getTotalProsesByYear($current_year)
            ];
        }

        $this->data['year_stats'] = $year_stats;
        $this->data['current_year'] = $current_year;
        $this->data['data_scope'] = $this->hasPermission('read_all') ? 'Semua Data Validasi' : 'Data Validasi';
        // --- End statistik tahun ini ---

        return $this->view('validasi/index', $this->data);
    }

    // Menampilkan halaman untuk memproses validasi
    public function proses($id)
    {
        $user = session()->get('user');
        if (!$user || !isset($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        $ajuan = $this->harmonisasiAjuanModel->getAjuanWithDetails($id);
        if (empty($ajuan)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Ajuan tidak ditemukan: ' . $id);
        }

        $this->data['title'] = 'Proses Validasi Ajuan';
        $this->data['ajuan'] = $ajuan;
        $this->data['dokumen'] = $this->harmonisasiDokumenModel->getDokumenByAjuan($id);
        
        // Format histori dates (consistent with Harmonisasi controller)
        $histori = $this->harmonisasiHistoriModel->getHistoryByAjuan($id);
        foreach ($histori as &$item) {
            $tanggal = $item['tanggal_aksi'] ?? null;
            if ($tanggal && strtotime($tanggal) !== false) {
                $item['tanggal_formatted'] = date('d F Y H:i', strtotime($tanggal));
            } else {
                $item['tanggal_formatted'] = 'Tanggal tidak valid';
            }
        }
        $this->data['histori'] = $histori;
        
        $this->data['validation'] = \Config\Services::validation();
        $this->data['user'] = $user;

        return $this->view('validasi/proses', $this->data);
    }

    // Menyimpan hasil aksi validasi (lanjutkan, revisi, atau tolak)
    public function submitAksi()
    {
        $id_ajuan = $this->request->getPost('id_ajuan');
        $aksi = $this->request->getPost('aksi');
        $catatan = $this->request->getPost('catatan');

        // Validasi Aksi & Catatan
        if (empty($aksi) || (in_array($aksi, ['tolak', 'revisi']) && empty(trim($catatan)))) {
            return redirect()->back()->withInput()->with('error', 'Aksi dan catatan wajib diisi untuk revisi/penolakan.');
        }

        $status_sebelum = $this->harmonisasiAjuanModel->find($id_ajuan)['id_status_ajuan'];
        $status_sesudah = 0;
        $pesan_histori = '';

        if ($aksi == 'lanjutkan') {
            $status_sesudah = HarmonisasiStatus::FINALISASI; // Status: Proses Finalisasi
            $pesan_histori = 'Dokumen telah divalidasi dan dilanjutkan ke tahap Finalisasi.';
        } elseif ($aksi == 'revisi') {
            $status_sesudah = HarmonisasiStatus::REVISI; // Status: Revisi (kembali ke pemohon)
            $pesan_histori = 'Dikembalikan ke pemohon untuk revisi substansi hukum.';
        } elseif ($aksi == 'tolak') {
            $status_sesudah = HarmonisasiStatus::DITOLAK; // Status: Ditolak
            $pesan_histori = 'Dokumen ditolak karena tidak memenuhi syarat substansi hukum.';
        }

        // Handle upload file revisi jika ada
        $file = $this->request->getFile('dokumen_revisi');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validasi file
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return redirect()->back()->withInput()->with('error', 'File revisi harus berformat PDF, DOC, atau DOCX.');
            }

            if ($file->getSize() > 25 * 1024 * 1024) { // 25MB
                return redirect()->back()->withInput()->with('error', 'Ukuran file revisi maksimal 25MB.');
            }
            $originalName = $file->getName();
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/harmonisasi_dokumen', $newName);

            // Validasi session untuk upload
            $user = session()->get('user');
            if (!$user || !isset($user['id_user'])) {
                return redirect()->to('/login')->with('error', 'Session tidak valid.');
            }

            $this->harmonisasiDokumenModel->insert([
                'id_ajuan' => $id_ajuan,
                'id_user_uploader' => $user['id_user'],
                'tipe_dokumen' => 'HASIL_VALIDASI',
                'nama_file_original' => $originalName,
                'path_file_storage' => 'harmonisasi_dokumen/' . $newName
            ]);
        }

        // Update status ajuan
        $this->harmonisasiAjuanModel->update($id_ajuan, ['id_status_ajuan' => $status_sesudah]);

        // Validasi session untuk histori
        $user = session()->get('user');
        if (!$user || !isset($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Session tidak valid.');
        }

        // Catat histori
        $this->harmonisasiHistoriModel->logHistory([
            'id_ajuan' => $id_ajuan,
            'id_user_aksi' => $user['id_user'],
            'id_status_sebelumnya' => $status_sebelum,
            'id_status_sekarang' => $status_sesudah,
            'keterangan' => $catatan ?: $pesan_histori
        ]);

        return redirect()->to('/validasi')->with('success', 'Aksi berhasil disimpan.');
    }

    // Method untuk mengunduh file dokumen
    public function download($id_dokumen)
    {
        // Validasi session user
        if (!session()->get('user')) {
            return redirect()->to('/login')->with('error', 'Silakan login untuk mengunduh file.');
        }

        $dokumen = $this->harmonisasiDokumenModel->find($id_dokumen);
        if (!$dokumen) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
        }

        // Validasi akses - user hanya bisa download dokumen yang terkait dengan ajuan mereka
        $user = session()->get('user');
        $ajuan = $this->harmonisasiAjuanModel->find($dokumen['id_ajuan']);
        if (!$ajuan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Ajuan tidak ditemukan.');
        }

        // Permission-based access control for document download
        $hasAccess = false;
        if ($this->hasPermission('read_all')) {
            // Users dengan read_all permission bisa download semua dokumen
            $hasAccess = true;
        } elseif ($this->hasPermission('read_own')) {
            // Users dengan read_own permission bisa download semua dokumen untuk validasi
            // Karena validator bertanggung jawab untuk semua ajuan yang masuk ke tahap validasi
            $hasAccess = true;
        }

        if (!$hasAccess) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        // Perbaiki path file - handle berbagai format path yang mungkin ada
        $pathFileStorage = $dokumen['path_file_storage'];

        // Jika path sudah dimulai dengan 'uploads/', hapus bagian tersebut
        if (strpos($pathFileStorage, 'uploads/') === 0) {
            $pathFileStorage = substr($pathFileStorage, 8); // Hapus 'uploads/' dari awal
        }

        // Jika path tidak dimulai dengan 'harmonisasi_dokumen/', tambahkan
        if (strpos($pathFileStorage, 'harmonisasi_dokumen/') !== 0) {
            $pathFileStorage = 'harmonisasi_dokumen/' . $pathFileStorage;
        }

        $filePath = WRITEPATH . 'uploads/' . $pathFileStorage;

        if (!file_exists($filePath)) {
            log_message('error', 'File tidak ditemukan: ' . $filePath);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak ditemukan di server.');
        }

        // Gunakan pendekatan manual untuk download yang lebih aman
        $fileName = $dokumen['nama_file_original'] ?: basename($pathFileStorage);

        // Set header untuk download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // Baca dan output file
        readfile($filePath);
        exit;
    }

    // Method untuk test/debugging ajuan tertentu
    public function test($id = null)
    {
        $user = session()->get('user');
        if (!$user || !isset($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        $test_id = $id ?: 8; // Default test ID 8

        $this->data['title'] = 'Test Validasi - Debug Ajuan ID ' . $test_id;
        $this->data['user'] = $user;
        $this->data['test_id'] = $test_id;

        // Test data retrieval
        $ajuan = $this->harmonisasiAjuanModel->getAjuanWithDetails($test_id);
        $this->data['ajuan'] = $ajuan;
        $this->data['ajuan_exists'] = !empty($ajuan);

        if ($ajuan) {
            $this->data['ajuan_status'] = $ajuan['id_status_ajuan'] ?? 'N/A';
            $this->data['ajuan_status_name'] = $ajuan['nama_status'] ?? 'N/A';
            $this->data['can_validate'] = ($ajuan['id_status_ajuan'] == 4); // Status 4 = Proses Validasi
        }

        $this->data['dokumen'] = $this->harmonisasiDokumenModel->getDokumenByAjuan($test_id);
        $this->data['histori'] = $this->harmonisasiHistoriModel->getHistoryByAjuan($test_id);

        // Test database connection
        $this->data['db_test'] = [
            'ajuan_count' => $this->harmonisasiAjuanModel->countAllResults(false),
            'dokumen_count' => $this->harmonisasiDokumenModel->countAllResults(false),
            'histori_count' => $this->harmonisasiHistoriModel->countAllResults(false)
        ];

        return $this->view('validasi/test', $this->data);
    }
}
