<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiDokumenModel;
use App\Models\HarmonisasiHistoriModel;
use App\Models\UserModel;

class Penugasan extends BaseController
{
    protected $harmonisasiAjuanModel;
    protected $harmonisasiDokumenModel;
    protected $harmonisasiHistoriModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();

        // ============================================================
        // PERMISSION-BASED ACCESS CONTROL - PENUGASAN MODULE
        // ============================================================
        $this->mustLoggedIn(); // Pastikan user sudah login

        // User harus punya minimal read_all atau permission untuk assignment untuk module penugasan
        if (!$this->hasPermission('update_all') && !$this->hasPermission('create')) {
            $this->printError('Akses ditolak: Anda tidak memiliki permission untuk mengakses module penugasan');
            exit;
        }

        $this->harmonisasiAjuanModel = model(HarmonisasiAjuanModel::class);
        $this->harmonisasiDokumenModel = model(HarmonisasiDokumenModel::class);
        $this->harmonisasiHistoriModel = model(HarmonisasiHistoriModel::class);
        $this->userModel = model(UserModel::class);
        helper(['form', 'url']);

        // Add DataTables Buttons extension scripts (tanpa data-tables.js karena akan ada inisialisasi manual di view)
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/JSZip/jszip.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/pdfmake.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/pdfmake/vfs_fonts.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.html5.min.js'));
        $this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.print.min.js'));
        $this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css'));
    }

    /**
     * Permission-based access validation for Penugasan operations
     */
    private function validatePenugasanAccess()
    {
        $user = session()->get('user');

        // User session validation sudah dilakukan di constructor
        // Hanya perlu validate permission untuk assignment tasks
        if (!$this->hasPermission('update_all')) {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda tidak memiliki permission untuk melakukan penugasan.');
        }

        return $user;
    }

    /**
     * Dashboard penugasan untuk Kabag
     */
    public function index()
    {
        $user = $this->validatePenugasanAccess();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Ambil ajuan menunggu penugasan
        $ajuan_penugasan = $this->harmonisasiAjuanModel->getAjuanForPenugasan();

        // Statistik beban verifikator (Top 5)
        // Menggunakan optimized query dari Model untuk menghindari N+1 query
        $verifikator_stats = $this->harmonisasiAjuanModel->getVerifikatorWorkloadStats(5);

        // Hitung jumlah tugas validasi
        $jumlah_validasi = $this->harmonisasiAjuanModel->where('id_status_ajuan', \App\Config\HarmonisasiStatus::VALIDASI)->countAllResults();

        // Hitung jumlah tugas finalisasi
        $jumlah_finalisasi = $this->harmonisasiAjuanModel->where('id_status_ajuan', \App\Config\HarmonisasiStatus::FINALISASI)->countAllResults();

        $data = [
            'title' => 'Dashboard Penugasan Harmonisasi',
            'breadcrumb' => ['Harmonisasi' => base_url('harmonisasi'), 'Dashboard Penugasan' => ''],
            'ajuan_penugasan' => $ajuan_penugasan,
            'verifikator_stats' => $verifikator_stats,
            'jumlah_validasi' => $jumlah_validasi,
            'jumlah_finalisasi' => $jumlah_finalisasi,        ];

        $this->data = array_merge($this->data, $data);
        return $this->view('harmonisasi/penugasan_dashboard', $this->data);
    }

    /**
     * Menampilkan form untuk menugaskan verifikator
     */
    public function tugaskan($id)
    {
        $user = $this->validatePenugasanAccess();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Validasi ID ajuan
        if (!$id || !is_numeric($id)) {
            return redirect()->to('/penugasan')->with('error', 'ID ajuan tidak valid.');
        }

        $ajuan = $this->harmonisasiAjuanModel->getAjuanDetail($id);
        if (!$ajuan) {
            return redirect()->to('/penugasan')->with('error', 'Ajuan tidak ditemukan.');
        }

        // Ambil dokumen terkait ajuan
        $dokumen = $this->harmonisasiDokumenModel->getDokumenByAjuan($id);

        // Prepare safe ajuan data
        $ajuan = $this->prepareAjuanData($ajuan);

        $data = [
            'title' => 'Tugaskan Verifikator',
            'breadcrumb' => [
                'Harmonisasi' => base_url('harmonisasi'),
                'Penugasan' => base_url('penugasan'),
                'Tugaskan Verifikator' => ''
            ],
            'ajuan' => $ajuan,
            'dokumen' => $dokumen,
            'verifikator_list' => $this->getVerifikatorList(),
        ];

        $this->data = array_merge($this->data, $data);
        return $this->view('harmonisasi/penugasan_form', $this->data);
    }

    /**
     * Prepare ajuan data with safe field handling
     */
    private function prepareAjuanData($ajuan)
    {
        // Ensure id_ajuan is available
        $ajuan['id_ajuan'] = $ajuan['id'] ?? $ajuan['id_ajuan'] ?? 0;

        // Ensure all required fields exist with fallbacks
        $ajuan['judul_peraturan'] = $ajuan['judul_peraturan'] ?? 'Tidak tersedia';
        $ajuan['nama_instansi'] = $ajuan['nama_instansi'] ?? 'Tidak tersedia';
        $ajuan['nama_pemohon'] = $ajuan['nama_pemohon'] ?? 'Tidak tersedia';
        $ajuan['nama_jenis'] = $ajuan['nama_jenis'] ?? 'Tidak tersedia';
        $ajuan['nama_status'] = $ajuan['nama_status'] ?? 'Tidak tersedia';

        return $ajuan;
    }

    /**
     * Get verifikator list with safe data handling
     */
    private function getVerifikatorList()
    {
        $verifikator_list = $this->userModel->getUsersByRoleId(7);

        foreach ($verifikator_list as &$verifikator) {
            // Ensure consistent field names
            $verifikator['id'] = $verifikator['id_user'] ?? $verifikator['id'] ?? 0;
            $verifikator['nama'] = $verifikator['nama'] ?? $verifikator['nama_lengkap'] ?? 'Nama tidak tersedia';
        }

        return $verifikator_list;
    }

    /**
     * Memproses penugasan verifikator (unified method)
     */
    public function assign()
    {
        $user = $this->validatePenugasanAccess();
        if ($user instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $user;
        }

        // Validate form inputs
        $validation = $this->validateAssignmentData();
        if (!$validation['success']) {
            return redirect()->back()->with('error', $validation['message']);
        }

        $id_ajuan = $this->request->getPost('id_ajuan');
        $id_verifikator = $this->request->getPost('id_user_verifikator');

        try {
            // Process assignment
            $result = $this->processAssignment($id_ajuan, $id_verifikator, $user);

            if ($result['success']) {
                return redirect()->to('/penugasan')->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in assignment process: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menugaskan verifikator.');
        }
    }

    /**
     * Validate assignment form data
     */
    private function validateAssignmentData()
    {
        $id_ajuan = $this->request->getPost('id_ajuan');
        $id_verifikator = $this->request->getPost('id_user_verifikator');

        if (!$id_ajuan || !$id_verifikator || !is_numeric($id_ajuan) || !is_numeric($id_verifikator)) {
            return ['success' => false, 'message' => 'Data tidak valid. Pastikan semua field terisi dengan benar.'];
        }

        // Validate ajuan exists
        $ajuan = $this->harmonisasiAjuanModel->find($id_ajuan);
        if (!$ajuan) {
            return ['success' => false, 'message' => 'Ajuan tidak ditemukan.'];
        }

        // Validate verifikator exists
        $verifikator = $this->userModel->find($id_verifikator);
        if (!$verifikator) {
            return ['success' => false, 'message' => 'Verifikator tidak ditemukan.'];
        }

        return ['success' => true, 'ajuan' => $ajuan, 'verifikator' => $verifikator];
    }

    /**
     * Process the assignment
     */
    private function processAssignment($id_ajuan, $id_verifikator, $user)
    {
        try {
            // Update ajuan with verifikator assignment
            $this->harmonisasiAjuanModel->update($id_ajuan, [
                'id_petugas_verifikasi' => $id_verifikator,
                'id_status_ajuan' => 3, // Status: Proses Verifikasi
            ]);

            // Log history
            $this->harmonisasiHistoriModel->logHistory([
                'id_ajuan' => $id_ajuan,
                'id_user_aksi' => $user['id_user'],
                'id_status_sebelumnya' => 2, // Diajukan ke Kabag
                'id_status_sekarang' => 3, // Proses Verifikasi
                'keterangan' => 'Telah ditugaskan kepada verifikator oleh ' . ($user['nama'] ?? 'Kabag')
            ]);

            return ['success' => true, 'message' => 'Verifikator berhasil ditugaskan.'];
        } catch (\Exception $e) {
            log_message('error', 'Error updating assignment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memproses penugasan verifikator.'];
        }
    }

    /**
     * Legacy method for backward compatibility (redirects to assign)
     */
    public function prosesTugaskan()
    {
        return $this->assign();
    }
}
