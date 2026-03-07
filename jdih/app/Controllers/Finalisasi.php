<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiDokumenModel;
use App\Models\HarmonisasiHistoriModel;
use App\Config\HarmonisasiStatus;

class Finalisasi extends BaseController
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiDokumenModel;
    protected $harmonisasiHistoriModel;

    public function __construct()
    {
        parent::__construct(); // Memanggil konstruktor BaseController

        // ============================================================
        // PERMISSION-BASED ACCESS CONTROL - FINALISASI MODULE
        // ============================================================
        $this->mustLoggedIn(); // Pastikan user sudah login

        // User harus punya minimal read_all atau read_own permission untuk module finalisasi
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Akses ditolak: Anda tidak memiliki permission untuk mengakses module finalisasi');
            exit;
        }

        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        $this->harmonisasiDokumenModel = new HarmonisasiDokumenModel();
        $this->harmonisasiHistoriModel = new HarmonisasiHistoriModel();
        helper(['form', 'url', 'filesystem']);

        // Add DataTables Buttons extension scripts
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/JSZip/jszip.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/pdfmake.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/vfs_fonts.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.html5.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.print.min.js'));
        $this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css'));
    }

    // Menampilkan dashboard untuk finalisator
    public function index()
    {
        $user = session()->get('user');
        if (!$user || !isset($user['id_user'])) {
            return redirect()->to('/login')->with('error', 'Session tidak valid. Silakan login kembali.');
        }

        // Ambil data finalisator stats
        $finalisator_stats = $this->harmonisasiAjuanModel->getFinalisatorStats();

        // Ambil data ajuan untuk finalisasi
        $ajuan_list = $this->harmonisasiAjuanModel->getAjuanForFinalisasi();

        // Hitung jumlah revisi
        $revisi_count = 0;
        if (!empty($ajuan_list)) {
            foreach ($ajuan_list as $item) {
                if ($item['id_status_ajuan'] == HarmonisasiStatus::REVISI_FINALISASI) {
                    $revisi_count++;
                }
            }
        }

        // Debug: Log data untuk troubleshooting
        log_message('debug', 'Finalisasi - ajuan_list count: ' . (is_array($ajuan_list) ? count($ajuan_list) : 'not array'));
        log_message('debug', 'Finalisasi - ajuan_list type: ' . gettype($ajuan_list));

        $this->data['title'] = 'Dashboard Finalisasi Harmonisasi';
        $this->data['ajuan_list'] = $ajuan_list ?: [];
        $this->data['revisi_count'] = $revisi_count;
        $this->data['finalisator_stats'] = $finalisator_stats ?: [];
        $this->data['user'] = $user;

        // ============================================================
        // STATISTIK TAHUN INI
        // ============================================================
        $current_year = date('Y');
        $year_stats = [];

        if ($this->hasPermission('read_all')) {
            // Admin/Supervisor: Statistik tahun ini untuk semua finalisator
            $year_stats = [
                'total_ajuan_tahun_ini' => $this->harmonisasiAjuanModel->getTotalAjuanByYear($current_year),
                'total_selesai_tahun_ini' => $this->harmonisasiAjuanModel->getTotalSelesaiByYear($current_year),
                'total_ditolak_tahun_ini' => $this->harmonisasiAjuanModel->getTotalDitolakByYear($current_year),
                'total_proses_tahun_ini' => $this->harmonisasiAjuanModel->getTotalProsesByYear($current_year)
            ];
        } elseif ($this->hasPermission('read_own')) {
            // Finalisator: Statistik tahun ini untuk semua ajuan yang masuk ke tahap finalisasi
            $year_stats = [
                'total_ajuan_tahun_ini' => $this->harmonisasiAjuanModel->getTotalAjuanByYear($current_year),
                'total_selesai_tahun_ini' => $this->harmonisasiAjuanModel->getTotalSelesaiByYear($current_year),
                'total_ditolak_tahun_ini' => $this->harmonisasiAjuanModel->getTotalDitolakByYear($current_year),
                'total_proses_tahun_ini' => $this->harmonisasiAjuanModel->getTotalProsesByYear($current_year)
            ];
        }

        $this->data['year_stats'] = $year_stats;
        $this->data['current_year'] = $current_year;
        $this->data['data_scope'] = $this->hasPermission('read_all') ? 'Semua Data Finalisasi' : 'Data Finalisasi';
        // --- End statistik tahun ini ---

        return $this->view('finalisasi/index', $this->data);
    }

    // Menampilkan halaman untuk memproses finalisasi
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

        $this->data['title'] = 'Proses Finalisasi Ajuan';
        $this->data['ajuan'] = $ajuan;
        $this->data['dokumen'] = $this->harmonisasiDokumenModel->getDokumenByAjuan($id);
        $this->data['histori'] = $this->harmonisasiHistoriModel->getHistoryByAjuan($id);
        $this->data['validation'] = \Config\Services::validation();
        $this->data['user'] = $user;

        return $this->view('finalisasi/proses', $this->data);
    }

    // Menyimpan hasil aksi finalisasi
    public function submitAksi()
    {
        $id_ajuan = $this->request->getPost('id_ajuan');
        $aksi = $this->request->getPost('aksi');
        $catatan = $this->request->getPost('catatan');

        $file = $this->request->getFile('dokumen_final');
        // Validasi
        $rules = [];
        if ($aksi === 'selesai') {
            $rules['dokumen_final'] = 'uploaded[dokumen_final]|max_size[dokumen_final,25600]|ext_in[dokumen_final,pdf,docx,doc]';
        } elseif ($aksi === 'revisi') {
            $rules['catatan'] = 'required';
            // File revisi opsional, validasi hanya jika ada
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $rules['dokumen_final'] = 'max_size[dokumen_final,25600]|ext_in[dokumen_final,pdf,docx,doc]';
            }
        }

        if (!empty($rules) && !$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()));
        }

        $status_sebelum = $this->harmonisasiAjuanModel->find($id_ajuan)['id_status_ajuan'];
        $status_sesudah = 0;
        $pesan_histori = '';

        if ($aksi === 'selesai') {
            // Handle upload file final (wajib)
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $originalName = $file->getName();
                $newName = $file->getRandomName();
                $file->move(WRITEPATH . 'uploads/harmonisasi_dokumen', $newName);

                $user = session()->get('user');

                // Mark existing FINAL_PARAF as HISTORY to prevent duplicate conflict in TTE
                $this->harmonisasiDokumenModel->builder()
                    ->set(['tipe_dokumen' => 'HISTORY'])
                    ->where('id_ajuan', $id_ajuan)
                    ->where('tipe_dokumen', 'FINAL_PARAF')
                    ->update();

                $this->harmonisasiDokumenModel->insert([
                    'id_ajuan' => $id_ajuan,
                    'id_user_uploader' => $user['id_user'],
                    'tipe_dokumen' => 'FINAL_PARAF',
                    'nama_file_original' => $originalName,
                    'path_file_storage' => 'harmonisasi_dokumen/' . $newName
                ]);
            }

            $status_sesudah = HarmonisasiStatus::PARAF_OPD; // ID 7: Menunggu Paraf OPD
            $pesan_histori = 'Proses finalisasi telah selesai. Dokumen final telah diupload dan menunggu paraf OPD.';

            // Update status (tidak update tanggal_selesai karena masih dalam proses)
            $this->harmonisasiAjuanModel->update($id_ajuan, [
                'id_status_ajuan' => $status_sesudah
            ]);
        } elseif ($aksi === 'revisi') {
            $status_sesudah = HarmonisasiStatus::REVISI; // ID 5: Revisi
            $pesan_histori = 'Dikembalikan ke pemohon untuk revisi oleh Finalisator.';

            // Upload file revisi jika ada
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $originalName = $file->getName();
                $newName = $file->getRandomName();
                $file->move(WRITEPATH . 'uploads/harmonisasi_dokumen', $newName);

                $user = session()->get('user');
                $this->harmonisasiDokumenModel->insert([
                    'id_ajuan' => $id_ajuan,
                    'id_user_uploader' => $user['id_user'],
                    'tipe_dokumen' => 'REVISI_FINALISASI',
                    'nama_file_original' => $originalName,
                    'path_file_storage' => 'harmonisasi_dokumen/' . $newName
                ]);
            }

            // Update status saja
            $this->harmonisasiAjuanModel->update($id_ajuan, ['id_status_ajuan' => $status_sesudah]);
        } else {
            return redirect()->back()->with('error', 'Aksi tidak valid.');
        }

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

        $message = ($aksi === 'selesai') ?
            'Proses finalisasi telah selesai. Dokumen telah disimpan dan menunggu paraf OPD.' :
            'Dokumen dikembalikan untuk revisi.';

        return redirect()->to('/finalisasi')->with('success', $message);
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
            // Users dengan read_own permission bisa download semua dokumen untuk finalisasi
            // Karena finalisator bertanggung jawab untuk semua ajuan yang masuk ke tahap finalisasi
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
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        // Baca dan output file
        readfile($filePath);
        exit;
    }

    // Method untuk test/debugging
    public function test()
    {
        // Check if user is logged in
        if (!session()->get('user')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = session()->get('user');

        $this->data['title'] = 'Test Finalisasi - Debug';
        $this->data['user'] = $user;
        $this->data['session_data'] = session()->get();
        $this->data['ajuan_count'] = $this->harmonisasiAjuanModel->getAjuanForFinalisasi() ? count($this->harmonisasiAjuanModel->getAjuanForFinalisasi()) : 0;
        $this->data['finalisator_stats'] = $this->harmonisasiAjuanModel->getFinalisatorStats();

        return $this->view('finalisasi/test', $this->data);
    }
}
