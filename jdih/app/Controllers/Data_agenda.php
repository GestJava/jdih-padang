<?php

namespace App\Controllers;

use App\Models\AgendaModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Admin CRUD Agenda Kegiatan
 */
class Data_agenda extends \App\Controllers\BaseController
{
    /** @var AgendaModel */
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new AgendaModel();

        // Body class untuk deteksi JS
        $this->data['body_class'] = 'data-agenda-page';
    }

    /**
     * Halaman list agenda (DataTable AJAX)
     */
    public function index()
    {
        $this->hasPermission('update');
        $data = $this->data;

        // Flash messages
        if ($this->session->getFlashdata('message')) {
            $data['msg'] = $this->session->getFlashdata('message');
        }

        // Ambil data agenda langsung (tanpa AJAX)
        $data['agenda_list'] = $this->model->orderBy('tanggal_mulai', 'DESC')->findAll();

        $this->view('data-agenda-result.php', $data);
    }

    /**
     * DataTables server-side list
     */
    public function ajax_list()
    {
        if (!$this->request->isAJAX()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $draw = (int) $this->request->getPost('draw');
        $start = (int) $this->request->getPost('start');
        $length = (int) $this->request->getPost('length');

        $list = $this->model->getDatatables($this->request);
        $data = [];
        $no = $start;
        foreach ($list as $row) {
            $no++;
            $data[] = [
                $no,
                esc($row->judul_agenda),
                esc($row->tanggal_mulai),
                esc($row->waktu_mulai ? substr($row->waktu_mulai, 0, 5) : '-'),
                esc($row->lokasi ?? '-'),
                esc($row->status_agenda ?? '-'),
                '<div class="btn-group" role="group">'
                    . '<a href="' . base_url('data_agenda/edit?id=' . $row->id) . '" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-edit"></i></a>'
                    . '<button type="button" class="btn btn-danger btn-xs" data-action="delete-data" data-id="' . $row->id . '" data-delete-title="Hapus agenda: ' . esc($row->judul_agenda) . ' ?" title="Hapus"><i class="fa fa-trash"></i></button>'
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
     * Format status agenda untuk display
     */
    private function formatStatusAgenda($status)
    {
        if (empty($status)) {
            return '<span class="badge bg-secondary">Belum Ditetapkan</span>';
        }

        return match (strtolower($status)) {
            'akan_datang' => '<span class="badge bg-info">Akan Datang</span>',
            'sedang_berlangsung' => '<span class="badge bg-warning">Sedang Berlangsung</span>',
            'selesai' => '<span class="badge bg-success">Selesai</span>',
            'dibatalkan' => '<span class="badge bg-danger">Dibatalkan</span>',
            'ditunda' => '<span class="badge bg-secondary">Ditunda</span>',
            default => '<span class="badge bg-light text-dark">' . esc($status) . '</span>'
        };
    }

    /**
     * Generate action buttons untuk agenda
     */
    private function generateAgendaActionButtons($id, $judul)
    {
        return '<div class="btn-group btn-group-sm" role="group">
            <a href="' . base_url('data_agenda/edit?id=' . $id) . '" class="btn btn-success" title="Edit">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="#" class="btn btn-danger delete" data-id="' . $id . '" 
               data-delete-title="Hapus agenda: <strong>' . esc($judul) . '</strong> ?" title="Hapus">
                <i class="fa fa-times"></i> Hapus
            </a>
        </div>';
    }

    /**
     * Delete Agenda (simple)
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
        $data['title'] = 'Tambah Agenda';
        $data['breadcrumb'] = ['Agenda' => base_url('data_agenda'), 'Tambah' => ''];
        $data['message'] = [];

        if ($this->request->getPost('submit')) {
            $saveData = $this->saveAgenda();
            $data['message'] = $saveData;
            if ($saveData['status'] == 'ok') {
                return redirect()->to(base_url('data_agenda'));
            }
        }

        $data['agenda'] = [];
        $this->view('data-agenda-form.php', $data);
    }

    public function edit()
    {
        $this->hasPermission('update');
        $id = $this->request->getGet('id');
        if (!$id) {
            return redirect()->to(base_url('data_agenda'));
        }
        $data = $this->data;
        $data['title'] = 'Edit Agenda';
        $data['breadcrumb'] = ['Agenda' => base_url('data_agenda'), 'Edit' => ''];
        $data['message'] = [];

        if ($this->request->getPost('submit')) {
            $saveData = $this->saveAgenda($id);
            $data['message'] = $saveData;
            if ($saveData['status'] == 'ok') {
                return redirect()->to(base_url('data_agenda'));
            }
        }

        $data['agenda'] = $this->model->find($id);
        if (!$data['agenda']) {
            return redirect()->to(base_url('data_agenda'))->with('message', ['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        $this->view('data-agenda-form.php', $data);
    }

    private function saveAgenda($id = null)
    {
        $rules = [
            'judul_agenda' => 'required|min_length[3]',
            'tanggal_mulai' => 'required|valid_date[Y-m-d]'
        ];
        if (!$this->validate($rules)) {
            return ['status' => 'error', 'message' => $this->validator->listErrors()];
        }

        $slug = url_title($this->request->getPost('judul_agenda'), '-', true);

        $dataSave = [
            'judul_agenda' => $this->request->getPost('judul_agenda'),
            'slug' => $slug,
            'deskripsi_singkat' => $this->request->getPost('deskripsi_singkat'),
            'deskripsi_lengkap' => $this->request->getPost('deskripsi_lengkap'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'waktu_mulai' => $this->request->getPost('waktu_mulai'),
            'waktu_selesai' => $this->request->getPost('waktu_selesai'),
            'lokasi' => $this->request->getPost('lokasi'),
            'penyelenggara' => $this->request->getPost('penyelenggara'),
            'target_peserta' => $this->request->getPost('target_peserta'),
            'kontak_person_nama' => $this->request->getPost('kontak_person_nama'),
            'kontak_person_email' => $this->request->getPost('kontak_person_email'),
            'kontak_person_telepon' => $this->request->getPost('kontak_person_telepon'),
            'status_agenda' => $this->request->getPost('status_agenda'),
        ];

        if ($id) {
            $dataSave['id'] = $id;
        }

        // File upload (optional)
        $file = $this->request->getFile('gambar_agenda');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'uploads/agenda', $newName);
            $dataSave['gambar_agenda'] = $newName;
        }

        if ($this->model->save($dataSave)) {
            return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
        }

        // Ambil pesan error validasi model / DB
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
