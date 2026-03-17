<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiDokumenModel;
use App\Models\HarmonisasiHistoriModel;
use App\Config\HarmonisasiStatus;

class Verifikasi extends BaseController
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiDokumenModel;
    protected $harmonisasiHistoriModel;

    public function __construct()
    {
        parent::__construct(); // Memanggil konstruktor BaseController

        // ============================================================
        // PERMISSION-BASED ACCESS CONTROL - VERIFIKASI MODULE
        // ============================================================
        $this->mustLoggedIn(); // Pastikan user sudah login

        // User harus punya minimal read_all atau read_own permission untuk module verifikasi
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Akses ditolak: Anda tidak memiliki permission untuk mengakses module verifikasi');
            exit;
        }

        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->harmonisasiDokumenModel = new HarmonisasiDokumenModel();
        $this->harmonisasiHistoriModel = new HarmonisasiHistoriModel();
        helper(['form', 'url']);

        // Load harmonisasi premium CSS (shared design system)
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

    // Menampilkan dashboard untuk verifikator
    public function index()
    {
        $user = session()->get('user');
        if (!$user || !isset($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        $this->data['title'] = 'Dashboard Verifikasi Harmonisasi';

        // ============================================================
        // PERMISSION-BASED DATA FILTERING
        // ============================================================
        if ($this->hasPermission('read_all')) {
            // Users dengan read_all permission bisa melihat semua ajuan verifikasi
            $ajuan = $this->harmonisasiAjuanModel->getAjuanWithDetailsByStatus(3);
            $this->data['data_scope'] = 'Semua Data Verifikasi';
        } elseif ($this->hasPermission('read_own')) {
            // Users dengan read_own permission hanya melihat ajuan yang ditugaskan kepada mereka
            $ajuan = $this->harmonisasiAjuanModel->getAjuanForVerifier($user['id_user']);
            $this->data['data_scope'] = 'Data Tugas Saya';
        } else {
            // Tidak ada permission, tampilkan array kosong
            $ajuan = [];
            $this->data['data_scope'] = 'Tidak Ada Akses';
        }

        $this->data['ajuan'] = $ajuan;
        $this->data['user'] = $user;

        // ============================================================
        // PERMISSION-BASED STATISTICS
        // ============================================================
        $verifikator_stats = [];
        if ($this->hasPermission('read_all')) {
            // Admin/Supervisor bisa melihat statistik semua verifikator
            $verifikatorModel = new \App\Models\UserModel();
            $verifikator_list = $verifikatorModel->getUsersByRoleId(7);

            foreach ($verifikator_list as $verifikator) {
                $jumlah = 0;
                foreach ($ajuan as $item) {
                    if (!empty($item['id_petugas_verifikasi']) && $item['id_petugas_verifikasi'] == $verifikator['id']) {
                        $jumlah++;
                    }
                }
                $verifikator_stats[] = [
                    'nama' => $verifikator['nama'],
                    'jumlah' => $jumlah
                ];
            }
            // Urutkan desc berdasarkan jumlah tugas
            usort($verifikator_stats, function ($a, $b) {
                return $b['jumlah'] <=> $a['jumlah'];
            });
        } elseif ($this->hasPermission('read_own')) {
            // Verifikator biasa hanya melihat statistik pribadi mereka
            $verifikator_stats[] = [
                'nama' => $user['nama'] . ' (Anda)',
                'jumlah' => count($ajuan)
            ];
        }

        $this->data['verifikator_stats'] = $verifikator_stats;
        $this->data['show_statistics'] = !empty($verifikator_stats);

        // ============================================================
        // STATISTIK TAHUN INI
        // ============================================================
        $current_year = date('Y');
        $year_stats = [];

        if ($this->hasPermission('read_all')) {
            // Admin/Supervisor: Statistik tahun ini untuk semua verifikator
            $year_stats = [
                'total_tugas' => $this->harmonisasiAjuanModel->getTotalTugasByYear($current_year),
                'total_selesai_verifikasi' => $this->harmonisasiAjuanModel->getTotalSelesaiVerifikasiByYear($current_year),
                'total_revisi' => $this->harmonisasiAjuanModel->getTotalRevisiByYear($current_year),
                'total_antrean_aktif' => $this->harmonisasiAjuanModel->getAntreanAktif()
            ];
        } elseif ($this->hasPermission('read_own')) {
            // Verifikator: Statistik tahun ini untuk user ini saja
            $year_stats = [
                'total_tugas' => $this->harmonisasiAjuanModel->getTotalTugasByYearAndUser($current_year, $user['id_user']),
                'total_selesai_verifikasi' => $this->harmonisasiAjuanModel->getTotalSelesaiVerifikasiByYearAndUser($current_year, $user['id_user']),
                'total_revisi' => $this->harmonisasiAjuanModel->getTotalRevisiByYearAndUser($current_year, $user['id_user']),
                'total_antrean_aktif' => $this->harmonisasiAjuanModel->getAntreanAktif($user['id_user'])
            ];
        }

        $this->data['year_stats'] = $year_stats;
        $this->data['current_year'] = $current_year;
        // --- End statistik ---

        return $this->view('verifikasi/index', $this->data);
    }

    // Menampilkan halaman untuk memproses verifikasi
    public function proses($id)
    {
        // ============================================================
        // PERMISSION CHECK: User harus punya permission update
        // ============================================================
        if (!$this->hasPermission('update_all') && !$this->hasPermission('update_own')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Anda tidak memiliki permission untuk memproses verifikasi.');
        }

        $user = session()->get('user');
        if (!$user || !isset($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        $ajuan = $this->harmonisasiAjuanModel->getAjuanWithDetails($id);

        // Permission-based access control
        if (empty($ajuan)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Ajuan tidak ditemukan.');
        }

        // Cek akses berdasarkan permission
        $hasAccess = false;
        if ($this->hasPermission('update_all')) {
            // User dengan update_all bisa memproses semua ajuan
            $hasAccess = true;
        } elseif ($this->hasPermission('update_own') && $ajuan['id_petugas_verifikasi'] == $user['id_user']) {
            // User dengan update_own hanya bisa memproses ajuan yang ditugaskan kepadanya
            $hasAccess = true;
        }

        if (!$hasAccess) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Anda tidak memiliki akses untuk memproses ajuan ini.');
        }

        $this->data['title'] = 'Proses Verifikasi Ajuan';
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
        return $this->view('verifikasi/proses', $this->data);
    }

    // Menyimpan hasil aksi verifikasi (lanjutkan atau tolak)
    public function submitAksi()
    {
        $id_ajuan = $this->request->getPost('id_ajuan');
        $aksi = $this->request->getPost('aksi');
        $catatan = $this->request->getPost('catatan');

        // Validasi
        if (empty($aksi) || (in_array($aksi, ['tolak']) && empty($catatan))) {
            return redirect()->back()->withInput()->with('error', 'Aksi dan catatan wajib diisi untuk penolakan.');
        }

        $status_sebelum = $this->harmonisasiAjuanModel->find($id_ajuan)['id_status_ajuan'];
        $status_sesudah = 0;
        $pesan_histori = '';

        if ($aksi == 'lanjutkan') {
            $status_sesudah = HarmonisasiStatus::VALIDASI; // ID 4: Proses Validasi
            $pesan_histori = 'Dokumen telah diverifikasi dan dilanjutkan ke tahap Validasi.';
        } elseif ($aksi == 'tolak') {
            $status_sesudah = HarmonisasiStatus::DITOLAK; // ID 15: Ditolak
            $pesan_histori = 'Dokumen ditolak karena tidak memenuhi syarat administrasi.';
        }

        // Handle upload file koreksi verifikator (jika ada)
        $file_koreksi = $this->request->getFile('file_koreksi');
        if ($file_koreksi && $file_koreksi->isValid() && !$file_koreksi->hasMoved()) {
            // Validasi file
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($file_koreksi->getMimeType(), $allowedTypes)) {
                return redirect()->back()->withInput()->with('error', 'File koreksi harus berformat PDF, DOC, atau DOCX.');
            }

            if ($file_koreksi->getSize() > 25 * 1024 * 1024) { // 25MB
                return redirect()->back()->withInput()->with('error', 'Ukuran file koreksi maksimal 25MB.');
            }

            // Upload file
            $uploadPath = WRITEPATH . 'uploads/harmonisasi_dokumen';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $originalName = $file_koreksi->getName();
            $newName = $file_koreksi->getRandomName();

            if ($file_koreksi->move($uploadPath, $newName)) {
                // Simpan data dokumen koreksi
                $this->harmonisasiDokumenModel->insert([
                    'id_ajuan' => $id_ajuan,
                    'id_user_uploader' => session()->get('user')['id_user'],
                    'tipe_dokumen' => 'HASIL_VERIFIKASI',
                    'nama_file_original' => $originalName,
                    'path_file_storage' => 'harmonisasi_dokumen/' . $newName
                ]);

                $pesan_histori .= ' File koreksi telah diupload.';
            }
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

        return redirect()->to('/verifikasi')->with('success', 'Aksi berhasil disimpan.');
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
        } elseif ($this->hasPermission('read_own') && $ajuan['id_petugas_verifikasi'] == $user['id_user']) {
            // Users dengan read_own permission hanya bisa download dokumen ajuan yang ditugaskan kepadanya
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
}
