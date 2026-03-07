<?php

namespace App\Controllers;

use App\Models\BeritaModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * @property IncomingRequest $request
 */
class Artikel_hukum extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new BeritaModel();
        $this->data['title'] = 'Manajemen Berita';

        // Assets for the form
        $this->addJs(base_url('vendors/jquery.select2/js/select2.full.min.js'));
        $this->addJs(base_url('vendors/tinymce/tinymce.min.js'));
        $this->addJs(base_url('vendors/flatpickr/dist/flatpickr.js'));

        $this->addStyle(base_url('vendors/flatpickr/dist/flatpickr.min.css'));
        $this->addStyle(base_url('vendors/jquery.select2/css/select2.min.css'));
    }

    public function index()
    {
        $this->data['title'] = 'Berita';
        $this->data['breadcrumb'] = ['Berita' => ''];
        $message = [];

        if ($this->request->getPost('delete')) {
            $result = $this->model->deleteData($this->request->getPost('id'));
            $message = $result;
        }

        $this->data['berita'] = $this->model->getAllBeritaForAdmin();
        $this->data['message'] = $message;

        return $this->view('berita-result.php', $this->data);
    }

    public function add()
    {
        // Skip permission check untuk testing
        // $this->hasPermission('create');

        $this->data['title'] = 'Tambah Berita';
        $this->data['breadcrumb'] = ['Berita' => base_url('artikel_hukum'), 'Tambah' => ''];
        $this->data['message'] = [];

        if ($this->request->getPost('submit')) {
            $this->data['message'] = $this->model->saveData($this->request);
            if ($this->data['message']['status'] == 'ok') {
                return redirect()->to(base_url('artikel_hukum'));
            }
        }

        // For the form
        $this->data['berita'] = [];
        $this->data['kategori_list'] = $this->model->getKategori();

        // Load assets
        $this->addJs(base_url('vendors/select2/js/select2.full.min.js'));
        $this->addStyle(base_url('vendors/select2/css/select2.min.css'));
        $this->addStyle(base_url('vendors/select2/css/select2-bootstrap-5-theme.min.css'));

        $this->addJs(base_url('vendors/tinymce/tinymce.min.js'));
        $this->addJs('
			document.addEventListener("DOMContentLoaded", function() {
				if (typeof tinymce !== "undefined") {
					tinymce.init({
						selector: "textarea.tinymce",
						plugins: "lists link code table paste",
						toolbar: "undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | table | code",
						height: 400,
						menubar: false,
						branding: false,
						relative_urls: false,
						remove_script_host: false,
						base_url: "' . base_url('vendors/tinymce/') . '",
						paste_data_images: true,
						paste_as_text: false,
						setup: function(editor) {
							editor.on("init", function() {
								console.log("TinyMCE initialized for Artikel");
							});
						}
					});
				}
			});
		', true);

        $this->addJs(base_url('vendors/flatpickr/dist/flatpickr.js'));
        $this->addStyle(base_url('vendors/flatpickr/dist/flatpickr.min.css'));

        return $this->view('berita-form.php', $this->data);
    }

    public function edit()
    {
        // Skip permission check untuk testing
        // $this->hasPermission('update');

        $id = $this->request->getGet('id');
        if (!$id) {
            return redirect()->to(base_url('artikel_hukum'));
        }

        $this->data['title'] = 'Edit Berita';
        $this->data['breadcrumb'] = ['Berita' => base_url('artikel_hukum'), 'Edit' => ''];
        $this->data['message'] = [];

        if ($this->request->getPost('submit')) {
            $this->data['message'] = $this->model->saveData($this->request);
            if ($this->data['message']['status'] == 'ok') {
                return redirect()->to(base_url('artikel_hukum'));
            }
        }

        // For the form
        $this->data['berita'] = $this->model->find($id);
        if (!$this->data['berita']) {
            // Optionally set a flash message for a better user experience
            return redirect()->to(base_url('artikel_hukum'))->with('message', ['status' => 'error', 'message' => 'Data berita tidak ditemukan']);
        }
        $this->data['kategori_list'] = $this->model->getKategori();

        // Load assets
        $this->addJs(base_url('vendors/select2/js/select2.full.min.js'));
        $this->addStyle(base_url('vendors/select2/css/select2.min.css'));
        $this->addStyle(base_url('vendors/select2/css/select2-bootstrap-5-theme.min.css'));

        $this->addJs(base_url('vendors/tinymce/tinymce.min.js'));
        $this->addJs('
			document.addEventListener("DOMContentLoaded", function() {
				if (typeof tinymce !== "undefined") {
					tinymce.init({
						selector: "textarea.tinymce",
						plugins: "lists link code table paste",
						toolbar: "undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | table | code",
						height: 400,
						menubar: false,
						branding: false,
						relative_urls: false,
						remove_script_host: false,
						base_url: "' . base_url('vendors/tinymce/') . '",
						paste_data_images: true,
						paste_as_text: false,
						setup: function(editor) {
							editor.on("init", function() {
								console.log("TinyMCE initialized for Artikel Edit");
							});
						}
					});
				}
			});
		', true);

        $this->addJs(base_url('vendors/flatpickr/dist/flatpickr.js'));
        $this->addStyle(base_url('vendors/flatpickr/dist/flatpickr.min.css'));

        return $this->view('berita-form.php', $this->data);
    }

    public function getDataDT()
    {
        // Bypass permission check untuk AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        // Skip permission check untuk testing
        // $this->hasPermission('update');

        $data = $this->model->getAllBeritaForAdmin();
        $result = [];
        $no = 1;
        foreach ($data as $row) {
            $result[] = [
                $no++,
                !empty($row['gambar']) ? '<img src="' . base_url('uploads/berita/' . $row['gambar']) . '" style="width:60px;height:auto;">' : '-',
                esc($row['judul']),
                esc($row['nama_kategori'] ?? '-'),
                esc($row['nama_penulis'] ?? '-'),
                esc($row['status']),
                esc($row['tanggal_publish']),
                '<a href="' . base_url('artikel_hukum/edit?id=' . $row['id']) . '" class="btn btn-primary btn-xs">Edit</a>'
            ];
        }

        return $this->response->setJSON([
            'data' => $result
        ]);
    }
}
