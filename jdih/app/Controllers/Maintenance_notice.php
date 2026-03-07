<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;

/**
 * Admin CRUD untuk pengumuman (maintenance notice)
 */
class Maintenance_notice extends \App\Controllers\BaseController
{
    /** @var AnnouncementModel */
    protected $model;
    private string $slug = 'maintenance-notice';

    public function __construct()
    {
        parent::__construct();
        $this->model = new AnnouncementModel();
        $this->data['body_class'] = 'maintenance-notice-page';
    }

    /**
     * Halaman daftar pengumuman
     */
    public function index()
    {
        $this->hasPermission('read');
        $data = $this->data;
        $data['title'] = 'Pengumuman Maintenance';
        $data['breadcrumb'] = ['Pengumuman' => base_url($this->slug)];
        if ($this->session->getFlashdata('message')) {
            $data['msg'] = $this->session->getFlashdata('message');
        }
        $data['announcements'] = $this->model->orderBy('updated_at', 'DESC')->findAll();

        $this->view('maintenance-notice-result.php', $data);
    }

    /**
     * DataTables endpoint
     */
    public function ajax_list()
    {
        $this->hasPermission('read');
        if (!$this->request->isAJAX()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $draw   = (int) $this->request->getPost('draw');
        $start  = (int) $this->request->getPost('start');
        $length = (int) $this->request->getPost('length');

        $list  = $this->model->getDatatables($this->request);
        $data  = [];
        $no    = $start;
        foreach ($list as $row) {
            $no++;
            $data[] = [
                $no,
                esc($row->title),
                esc($row->status),
                esc(date('d-m-Y H:i', strtotime($row->updated_at))),
                '<div class="btn-group" role="group">'
                    . '<a href="' . base_url($this->slug . '/edit?id=' . $row->id) . '" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-edit"></i></a>'
                    . '<button type="button" class="btn btn-danger btn-xs" data-action="delete-data" data-id="' . $row->id . '" data-delete-title="Hapus pengumuman: ' . esc($row->title) . ' ?" title="Hapus"><i class="fa fa-trash"></i></button>'
                    . '</div>'
            ];
        }

        $output = [
            'draw' => $draw,
            'recordsTotal' => $this->model->countAll(),
            'recordsFiltered' => $this->model->countFiltered($this->request),
            'data' => $data,
        ];

        return $this->response->setJSON($output);
    }

    /**
     * DataTable endpoint untuk view yang sudah dioptimasi
     */
    public function getDataDT()
    {
        $this->hasPermission('read');

        // Validasi request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        // Ambil parameter DataTable
        $draw = $this->request->getGet('draw');
        $start = $this->request->getGet('start');
        $length = $this->request->getGet('length');
        $search = $this->request->getGet('search')['value'] ?? '';
        $order = $this->request->getGet('order')[0] ?? null;

        try {
            // Query dengan pagination dan search
            $builder = $this->model->builder();

            // Total records (tanpa filter)
            $totalRecords = $this->model->countAllResults();

            // Apply search filter
            if (!empty($search)) {
                $builder->groupStart()
                    ->like('title', $search)
                    ->orLike('message', $search)
                    ->orLike('status', $search)
                    ->groupEnd();
            }

            // Get total filtered records
            $totalFiltered = $builder->countAllResults(false);

            // Apply ordering
            if ($order && isset($order['column']) && isset($order['dir'])) {
                $columns = ['title', 'status', 'updated_at']; // Sesuaikan dengan kolom
                if (isset($columns[$order['column']])) {
                    $builder->orderBy($columns[$order['column']], $order['dir']);
                }
            } else {
                $builder->orderBy('updated_at', 'DESC');
            }

            // Apply pagination
            $builder->limit($length, $start);

            // Execute query
            $results = $builder->get()->getResultArray();

            // Format data untuk DataTable
            $data = [];
            foreach ($results as $row) {
                $data[] = [
                    'ignore_no_urut' => '', // Akan diisi otomatis oleh DataTable
                    'title' => esc($row['title']),
                    'status' => $this->formatStatus($row['status']),
                    'updated_at' => esc(date('d-m-Y H:i', strtotime($row['updated_at']))),
                    'ignore_action' => $this->generateActionButtons($row['id'], $row['title'])
                ];
            }

            return $this->response->setJSON([
                'draw' => (int)$draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Maintenance Notice DataTable Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
    }

    /**
     * Format status untuk display
     */
    private function formatStatus($status)
    {
        if ($status === 'active') {
            return '<span class="badge bg-success">Aktif</span>';
        } else {
            return '<span class="badge bg-secondary">Nonaktif</span>';
        }
    }

    /**
     * Generate action buttons
     */
    private function generateActionButtons($id, $title)
    {
        return '<div class="btn-group btn-group-sm" role="group">
            <a href="' . base_url($this->slug . '/edit?id=' . $id) . '" class="btn btn-success" title="Edit">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="#" class="btn btn-danger delete" data-id="' . $id . '" 
               data-delete-title="Hapus pengumuman: <strong>' . esc($title) . '</strong> ?" title="Hapus">
                <i class="fa fa-times"></i> Hapus
            </a>
        </div>';
    }

    /**
     * Hapus pengumuman
     */
    public function delete()
    {
        $this->hasPermission('delete');
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['error' => 'Invalid parameter']);
        }
        $this->model->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    public function add()
    {
        $this->hasPermission('create');
        $data = $this->data;
        $data['title'] = 'Tambah Pengumuman';
        $data['breadcrumb'] = ['Pengumuman' => base_url($this->slug), 'Tambah' => ''];
        $data['message'] = [];

        if ($this->request->getPost('submit')) {
            $saveData = $this->saveNotice();
            $data['message'] = $saveData;
            if ($saveData['status'] == 'ok') {
                return redirect()->to(base_url($this->slug));
            }
        }

        $data['announcement'] = [];
        $this->view('maintenance-notice-form.php', $data);
    }

    public function edit()
    {
        $this->hasPermission('update');
        $id = $this->request->getGet('id');
        if (!$id) {
            return redirect()->to(base_url($this->slug));
        }

        $data = $this->data;
        $data['title'] = 'Edit Pengumuman';
        $data['breadcrumb'] = ['Pengumuman' => base_url($this->slug), 'Edit' => ''];
        $data['message'] = [];

        if ($this->request->getPost('submit')) {
            $saveData = $this->saveNotice($id);
            $data['message'] = $saveData;
            if ($saveData['status'] == 'ok') {
                return redirect()->to(base_url($this->slug));
            }
        }

        $data['announcement'] = $this->model->find($id);
        if (!$data['announcement']) {
            return redirect()->to(base_url($this->slug))->with('message', ['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        $this->view('maintenance-notice-form.php', $data);
    }

    private function saveNotice($id = null)
    {
        $rules = [
            'title'   => 'required|min_length[3]',
            'message' => 'required|min_length[5]',
        ];

        if (!$this->validate($rules)) {
            return ['status' => 'error', 'message' => $this->validator->listErrors()];
        }

        $dataSave = [
            'title'            => $this->request->getPost('title'),
            'heading'          => $this->request->getPost('heading'),
            'message'          => $this->request->getPost('message'),
            'contact_name'     => $this->request->getPost('contact_name'),
            'contact_position' => $this->request->getPost('contact_position'),
            'status'           => $this->request->getPost('status'),
        ];

        if ($id) {
            $dataSave['id'] = $id;
        }

        if ($this->model->save($dataSave)) {
            return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
        }

        $errModel = $this->model->errors();
        if (!empty($errModel)) {
            $errText = implode('<br>', $errModel);
        } else {
            $dbError = $this->model->db->error();
            $errText = $dbError['message'] ?? 'Gagal menyimpan ke database';
        }

        return ['status' => 'error', 'message' => $errText];
    }
}
