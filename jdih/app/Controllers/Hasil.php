<?php

namespace App\Controllers;

use App\Models\HarmonisasiAjuanModel;
use App\Config\HarmonisasiStatus;

class Hasil extends BaseController
{
    protected $harmonisasiAjuanModel;

    public function __construct()
    {
        parent::__construct();
        $this->mustLoggedIn();
        
        // Inisialisasi model
        $this->harmonisasiAjuanModel = new HarmonisasiAjuanModel();
        helper(['form', 'url']);
    }

    /**
     * Halaman utama menampilkan daftar ajuan dengan status Selesai dan Ditolak
     * Load data langsung tanpa AJAX untuk kesederhanaan
     */
    public function index()
    {
        // Permission check
        if (!$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
            $this->printError('Anda tidak memiliki permission untuk melihat data hasil harmonisasi');
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
            // Load data langsung dari database
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
                ->whereIn('harmonisasi_ajuan.id_status_ajuan', [HarmonisasiStatus::SELESAI, HarmonisasiStatus::DITOLAK]);

            // Filter berdasarkan permission
            if ($this->hasPermission('read_all')) {
                // User dengan read_all bisa lihat semua data
            } elseif ($this->hasPermission('read_own')) {
                // User dengan read_own hanya bisa lihat data milik sendiri
                $builder->where('harmonisasi_ajuan.id_user_pemohon', $user['id_user']);
            }

            // Filter pencarian dari GET parameter
            $search = $this->request->getGet('search');
            if ($search) {
                $builder->groupStart()
                    ->like('harmonisasi_ajuan.judul_peraturan', $search)
                    ->orLike('harmonisasi_jenis_peraturan.nama_jenis', $search)
                    ->orLike('instansi.nama_instansi', $search)
                    ->orLike('user.nama', $search)
                    ->groupEnd();
            }

            // Order
            $builder->orderBy('harmonisasi_ajuan.updated_at', 'DESC');

            // Pagination - 25 items per page
            $perPage = 25;
            $page = (int)($this->request->getGet('page') ?? 1);
            $offset = ($page - 1) * $perPage;

            // Get total count untuk pagination
            $totalRecords = $builder->countAllResults(false);
            
            // Apply limit untuk pagination
            $builder->limit($perPage, $offset);
            $ajuan_list = $builder->get()->getResultArray();

            // Process data untuk view
            $processed_data = [];
            $no = $offset + 1;
            foreach ($ajuan_list as $row) {
                $processed_data[] = [
                    'no' => $no++,
                    'id_ajuan' => $row['id_ajuan'],
                    'judul_peraturan' => esc($row['judul_peraturan']),
                    'nama_jenis' => esc($row['nama_jenis'] ?? '-'),
                    'nama_instansi' => esc($row['nama_instansi'] ?? '-'),
                    'tanggal_pengajuan' => !empty($row['tanggal_pengajuan']) ? date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])) : '-',
                    'id_status_ajuan' => $row['id_status_ajuan'],
                    'nama_status' => esc($row['nama_status'] ?? '-')
                ];
            }

            // Calculate pagination info
            $totalPages = ceil($totalRecords / $perPage);
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages,
                'prev_page' => $page > 1 ? $page - 1 : null,
                'next_page' => $page < $totalPages ? $page + 1 : null
            ];

            $data = [
                'title' => 'Daftar Ajuan Selesai & Ditolak',
                'breadcrumb' => ['Harmonisasi' => base_url('harmonisasi'), 'Hasil' => ''],
                'user_role' => $user['nama_role'] ?? '',
                'ajuan_list' => $processed_data,
                'search' => $search ?? '',
                'pagination' => $pagination
            ];

            $this->data = array_merge($this->data, $data);
            return $this->view('hasil/index', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Error in Hasil index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }


    /**
     * Validate user session and return user data
     */
    private function validateUserSession()
    {
        $user = session()->get('user');
        if (!$user || empty($user['id_user'])) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return null;
            }
            return redirect()->to('/login')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }
        return $user;
    }
    /**
     * Delete existing ajuan (Administrator only)
     */
    public function delete($id)
    {
        // 1. Permission Check - Use RBA OR Explicit Admin Role
        $user = session()->get('user');
        $role = strtolower($user['nama_role'] ?? '');
        $is_admin = ($role === 'administrator' || $role === 'admin');
        
        if (!$this->hasPermission('delete') && !$is_admin) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Akses ditolak. Anda tidak memiliki permission untuk menghapus data.'
            ])->setStatusCode(403);
        }

        // 2. Validate Request
        if (!$this->request->isAJAX()) {
             return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        // 3. Check Data Existence
        $data = $this->harmonisasiAjuanModel->find($id);
        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ])->setStatusCode(404);
        }

        // 4. Perform Deletion
        try {
            if ($this->harmonisasiAjuanModel->delete($id)) {
                 return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                 return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menghapus data'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}

