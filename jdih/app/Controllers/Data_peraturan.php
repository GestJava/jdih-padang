<?php

/**
 *	App Name	: JDIH Kota Padang
 *	Author		: Agus Salim
 *	Website		: https://jdih.padang.go.id
 *	Year		: 2025
 */

namespace App\Controllers;

use App\Models\WebPeraturanModel;
use App\Models\WebPeraturanRelasiModel;
use App\Models\WebLampiranModel;
use App\Models\JenisDokumenModel;
use App\Models\WebTagModel;
use App\Models\WebPeraturanTagModel;
use App\Models\StatusDokumenModel;
use App\Models\InstansiModel;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * @property IncomingRequest $request
 */
class Data_peraturan extends \App\Controllers\BaseController
{
	protected $model;
	protected $relasiModel;
	protected $lampiranModel;
	protected $tagModel;
	protected $peraturanTagModel;
	protected $statusDokumenModel;
	protected $instansiModel;
	protected $jenisRelasiModel;
	protected $statusLogModel;
	protected $db;

	public function __construct()
	{
		parent::__construct();

		// Lazy loading: Load models only when needed
		$this->model = null; // Will be loaded in methods that need it
		$this->relasiModel = null;
		$this->lampiranModel = null;
		$this->tagModel = null;
		$this->peraturanTagModel = null;
		$this->statusDokumenModel = null;
		$this->jenisRelasiModel = null;
		$this->statusLogModel = null;
		$this->db = \Config\Database::connect();
		$this->instansiModel = null;

		$this->data['site_title'] = 'Data Tables';
		$this->data['body_class'] = 'data-peraturan-page';

		// Load only essential assets in constructor
		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js'));
		$this->addStyle(base_url('vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css'));

		// Load custom data-peraturan admin JavaScript with cache busting
		$this->addJs(base_url('jdih/assets/js/data-peraturan-admin.js?v=' . time()));

		$this->addJs(base_url('vendors/bootstrap-datepicker/js/bootstrap-datepicker.js'));
		$this->addJs(base_url('themes/modern/js/date-picker.js'));
		$this->addJs(base_url('themes/modern/js/image-upload.js'));

		// Data Tables - Script utama ada di app/Views/themes/modern/header.php
		$this->addJs(base_url('vendors/datatables/extensions/JSZip/jszip.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/pdfmake/pdfmake.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/pdfmake/vfs_fonts.js'));
		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.html5.min.js'));
		$this->addJs(base_url('vendors/datatables/extensions/Buttons/js/buttons.print.min.js'));

		$this->addStyle(base_url('vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css?v=' . time()));
		$this->addJs(base_url('vendors/jquery.select2/js/select2.full.min.js?v=' . time()));
		// Select2 CSS sudah di-load di header.php, tidak perlu duplikasi

		// Tambahkan konfigurasi TinyMCE
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
								console.log("TinyMCE initialized with enhanced features");
							});
						}
					});
				} else {
					console.error("TinyMCE not loaded");
				}
			});
		', true);
	}

	public function index()
	{
		$this->hasPermissionPrefix('update', 'peraturan');
		$data = $this->data;

		if ($this->session->getFlashdata('message')) {
			$data['msg'] = $this->session->getFlashdata('message');
		}

		$this->view('data-peraturan-result.php', $data);
	}

	public function ajax_list()
	{
		if (!$this->request->isAJAX()) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		try {
			helper('html');

			// Validasi input untuk keamanan
			$draw = $this->request->getPost('draw');
			$start = $this->request->getPost('start');
			$length = $this->request->getPost('length');

			// Validasi tipe data
			if (!is_numeric($draw) || !is_numeric($start) || !is_numeric($length)) {
				throw new \InvalidArgumentException('Invalid DataTables parameters');
			}

			// Batasi nilai untuk mencegah abuse
			$start = max(0, min((int)$start, 100000));
			$length = max(1, min((int)$length, 1000));
			$draw = (int)$draw;

			$model = $this->loadModel('model');
			$list = $model->getDatatables($this->request);
			
			// Cek apakah kolom File (index 7) yang di-sort
			$order = $this->request->getPost('order');
			$orderColumnIndex = null;
			$orderDirection = 'asc';
			
			if (!empty($order) && is_array($order) && isset($order[0])) {
				$orderColumnIndex = (int)$order[0]['column'];
				$orderDirection = strtolower($order[0]['dir']) === 'desc' ? 'desc' : 'asc';
			}
			
			// Tambahkan field file_exists ke setiap peraturan untuk sorting
			$peraturanList = [];
			foreach ($list as $peraturan) {
				$fileExists = 0; // Default: file tidak ada
				
				if (!empty($peraturan->file_dokumen)) {
					$fileName = $peraturan->file_dokumen;
					
					// Cek beberapa kemungkinan path
					$filePath2 = ROOTPATH . 'uploads/peraturan/' . $fileName;
					$filePath3 = FCPATH . 'jdih/uploads/peraturan/' . $fileName;
					$filePath1 = FCPATH . 'uploads/peraturan/' . $fileName;
					
					if (is_file($filePath2) || is_file($filePath3) || is_file($filePath1)) {
						$fileExists = 1; // File ada
					}
				}
				
				// Tambahkan field file_exists ke object peraturan
				$peraturan->file_exists = $fileExists;
				$peraturanList[] = $peraturan;
			}
			
			// Sorting kolom File (index 7) kini sudah diproses di Model menggunakan query database.
			
			$data = [];
			$no = $start;

			foreach ($peraturanList as $peraturan) {
				try {
				$no++;
				$row = [];
				$row[] = $no;
				$row[] = esc($peraturan->nama_jenis ?? '-');
				$row[] = esc($peraturan->nomor);
				$row[] = esc($peraturan->tahun);
				$row[] = esc(ucwords(strtolower($peraturan->judul)));
				$row[] = esc($peraturan->nama_instansi ?? '-');
				$row[] = esc($peraturan->nama_status ?? '-');

				// File link - Pastikan semua data ditampilkan termasuk yang file-nya hilang
				$file_link = '';
				$fileExists = 0; // Default: file tidak ada
				
				if (!empty($peraturan->file_dokumen)) {
					$fileName = $peraturan->file_dokumen;
					
					// Cek beberapa kemungkinan path (untuk kompatibilitas struktur folder)
					// Prioritas: filePath2 (subfolder jdih) diutamakan karena struktur umum
					// Path 2: ROOTPATH/uploads/peraturan/ (jika uploads di subfolder jdih) - PRIORITAS UTAMA
					$filePath2 = ROOTPATH . 'uploads/peraturan/' . $fileName;
					// Path 3: FCPATH/jdih/uploads/peraturan/ (alternatif struktur)
					$filePath3 = FCPATH . 'jdih/uploads/peraturan/' . $fileName;
					// Path 1: FCPATH/uploads/peraturan/ (jika uploads di root) - fallback
					$filePath1 = FCPATH . 'uploads/peraturan/' . $fileName;
					
					// Gunakan path yang pertama ditemukan dan tentukan URL yang sesuai
					// Prioritas: filePath2 > filePath3 > filePath1
					$filePath = null;
					$file_url = '';
					
					if (is_file($filePath2)) {
						// File di subfolder jdih: jdih/uploads/peraturan/ - PRIORITAS UTAMA
						$filePath = $filePath2;
						$file_url = base_url('jdih/uploads/peraturan/' . esc($fileName, 'url'));
						$fileExists = 1;
					} elseif (is_file($filePath3)) {
						// File di alternatif: jdih/uploads/peraturan/
						$filePath = $filePath3;
						$file_url = base_url('jdih/uploads/peraturan/' . esc($fileName, 'url'));
						$fileExists = 1;
					} elseif (is_file($filePath1)) {
						// File ada di root: uploads/peraturan/
						$filePath = $filePath1;
						$file_url = base_url('uploads/peraturan/' . esc($fileName, 'url'));
						$fileExists = 1;
					} else {
						// File TIDAK DITEMUKAN di semua lokasi
						$fileExists = 0;
						$file_url = base_url('jdih/uploads/peraturan/' . esc($fileName, 'url'));
					}

					// Generate HTML berdasarkan status file
					if ($fileExists === 1 && $filePath !== null && is_file($filePath)) {
						// File ADA → Tampilkan tombol hijau "Lihat" dengan data attribute untuk sorting
						$file_link = '<span data-file-exists="1">' . '<a href="' . $file_url . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-file-pdf"></i> Lihat</a>' . '</span>';
					} else {
						// File TIDAK ADA → Tampilkan badge merah "File hilang" dengan data attribute untuk sorting
						$file_link = '<span data-file-exists="0">' . '<span class="badge bg-danger"><i class="fa fa-exclamation-triangle"></i> File hilang</span>' . '</span>';
					}
				} else {
					// Tidak ada file dokumen di database
					$fileExists = 0;
					$file_link = '<span data-file-exists="0">' . '<span class="text-muted">Tidak ada file</span>' . '</span>';
				}
				
				// Pastikan file_link selalu ada sebelum ditambahkan ke row
				if (empty($file_link)) {
					$file_link = '<span data-file-exists="0">' . '<span class="text-muted">Tidak ada file</span>' . '</span>';
				}
				
				$row[] = $file_link;

				// Action buttons - Generate HTML manually to avoid encoding issues
				$action_buttons = '<div class="btn-group" role="group">';

				// Edit button
				$action_buttons .= '<a href="' . base_url('data-peraturan/edit?id=' . $peraturan->id_peraturan) . '" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-edit"></i></a>';

				// Delete button
				$delete_title = 'Hapus data peraturan: ' . esc($peraturan->judul) . ' ?';
				$action_buttons .= '<button type="button" class="btn btn-danger btn-xs" data-action="delete-data" data-delete-title="' . $delete_title . '" data-id="' . $peraturan->id_peraturan . '" title="Hapus"><i class="fa fa-trash"></i></button>';

				// Relasi button
				$action_buttons .= '<a href="' . base_url('data-peraturan/relasi_peraturan?id=' . $peraturan->id_peraturan) . '" class="btn btn-info btn-xs" title="Relasi"><i class="fa fa-link"></i></a>';

				// Lampiran button
				$action_buttons .= '<a href="' . base_url('data-peraturan/lampiran?id=' . $peraturan->id_peraturan) . '" class="btn btn-secondary btn-xs" title="Lampiran"><i class="fa fa-paperclip"></i></a>';

				$action_buttons .= '</div>';
				$row[] = $action_buttons;

				$data[] = $row;
				} catch (\Exception $e) {
					// Log error tapi tetap tampilkan data (dengan file status default)
					log_message('error', 'Error processing peraturan ID ' . ($peraturan->id_peraturan ?? 'unknown') . ': ' . $e->getMessage());
					
					// Tetap tambahkan data dengan file status default
					if (!isset($row) || empty($row)) {
						$row = [];
						$row[] = $no;
						$row[] = esc($peraturan->nama_jenis ?? '-');
						$row[] = esc($peraturan->nomor ?? '-');
						$row[] = esc($peraturan->tahun ?? '-');
						$row[] = esc(ucwords(strtolower($peraturan->judul ?? '-')));
						$row[] = esc($peraturan->nama_instansi ?? '-');
						$row[] = esc($peraturan->nama_status ?? '-');
						$row[] = '<span data-file-exists="0">' . '<span class="badge bg-danger"><i class="fa fa-exclamation-triangle"></i> File hilang</span>' . '</span>';
						$row[] = '<div class="btn-group" role="group">' .
								 '<a href="' . base_url('data-peraturan/edit?id=' . $peraturan->id_peraturan) . '" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-edit"></i></a>' .
								 '<button type="button" class="btn btn-danger btn-xs" data-action="delete-data" data-delete-title="Hapus data?" data-id="' . $peraturan->id_peraturan . '" title="Hapus"><i class="fa fa-trash"></i></button>' .
								 '</div>';
					}
					$data[] = $row;
				}
			}

			// Optimasi: Gunakan single query untuk mendapatkan total dan filtered count
			$totalRecords = $model->countAll();
			$filteredRecords = $model->countFiltered($this->request);

			$output = [
				"draw" => $draw,
				"recordsTotal" => $totalRecords,
				"recordsFiltered" => $filteredRecords,
				"data" => $data,
			];
			// Tambahkan CSRF token untuk AJAX DataTable
			$output['csrf_token_name'] = csrf_token();
			$output['csrf_token_value'] = csrf_hash();

			// Debug logging
			log_message('debug', 'Data_peraturan::getDataDT() response: ' . json_encode([
				'totalRecords' => $totalRecords,
				'filteredRecords' => $filteredRecords,
				'dataCount' => count($data)
			]));

			return $this->response->setJSON($output);
		} catch (\InvalidArgumentException $e) {
			log_message('warning', 'Invalid DataTables request: ' . $e->getMessage());

			$error_output = [
				"draw" => 1,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => [],
				"error" => "Parameter tidak valid"
			];

			return $this->response->setStatusCode(400)->setJSON($error_output);
		} catch (\Exception $e) {
			// Log error untuk debugging
			log_message('error', 'DataTables AJAX Error: ' . $e->getMessage());

			// Return JSON error response yang aman
			$error_output = [
				"draw" => (int)($this->request->getPost('draw') ?? 1),
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => [],
				"error" => "Terjadi kesalahan saat memuat data. Silakan coba lagi."
			];

			return $this->response->setStatusCode(500)->setJSON($error_output);
		}
	}

	public function add()
	{
		try {
			log_message('debug', '=== ADD METHOD CALLED ===');
			$this->hasPermissionPrefix('create', 'peraturan');

			$this->data['title'] = 'Tambah ' . $this->currentModule['judul_module'];
			$this->data['page_title'] = $this->data['title'];
			$data = $this->data;

			// Ambil data untuk dropdowns menggunakan lazy loading
			$data['jenis_dokumen_list'] = (new JenisDokumenModel())->orderBy('nama_jenis', 'ASC')->findAll();
			$data['instansi_list'] = $this->loadModel('instansiModel')->orderBy('nama_instansi', 'ASC')->findAll();
			$data['status_dokumen_list'] = $this->loadModel('statusDokumenModel')->orderBy('nama_status', 'ASC')->findAll();

			// Ambil data tag untuk dropdown
			$data['tags'] = $this->loadModel('tagModel')->getAllTags();
			$data['selected_tags'] = [];

			// Submit
			if ($this->request->getMethod() === 'post') {
				log_message('debug', '=== POST REQUEST DETECTED ===');

				// Debug: cek data yang diterima
				log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
				log_message('debug', 'Files data: ' . json_encode($this->request->getFiles()));

				// Validasi file dokumen untuk add - file wajib
				$file = $this->request->getFile('file_dokumen');
				$form_errors = false;

				if ($file->getError() !== 0) { // File harus diupload untuk add
					$form_errors['file_dokumen'] = 'File dokumen wajib diunggah untuk peraturan baru.';
				} else if ($file->getError() === 0) { // File diupload
					if ($file->getExtension() !== 'pdf') {
						$form_errors['file_dokumen'] = 'File harus berformat PDF';
					}

					if ($file->getSize() > 25 * 1024 * 1024) { // 25MB
						$form_errors['file_dokumen'] = 'Ukuran file maksimal 25MB';
					}
				}

				if ($form_errors) {
					$data['message'] = ['status' => 'error', 'message' => $form_errors];
				} else {
					// Validasi data yang diterima
					$validationData = [
						'id_jenis_dokumen' => $this->request->getVar('id_jenis_dokumen'),
						'nomor' => $this->request->getVar('nomor'),
						'tahun' => $this->request->getVar('tahun'),
						'judul' => $this->request->getVar('judul'),
						'tgl_penetapan' => $this->request->getVar('tgl_penetapan'),
						'tgl_pengundangan' => $this->request->getVar('tgl_pengundangan'),
						'id_instansi' => $this->request->getVar('id_instansi'),
						'id_status' => $this->request->getVar('id_status'),
						'penandatangan' => $this->request->getVar('penandatangan'),
						'sumber' => $this->request->getVar('sumber'),
						'tempat_penetapan' => $this->request->getVar('tempat_penetapan'),
						'teu' => $this->request->getVar('teu'),
						'bidang_hukum' => $this->request->getVar('bidang_hukum')
					];

					// Dapatkan jenis peraturan untuk validasi yang sesuai
					$jenis_peraturan = $this->getJenisPeraturanById($validationData['id_jenis_dokumen']);
					$validationRules = $this->getValidationRulesByJenis($jenis_peraturan, false);

					if ($this->validateData($validationData, $validationRules)) {
						log_message('debug', '=== VALIDATION PASSED ===');
						$this->db->transBegin();

						try {
							log_message('debug', '=== TRANSACTION STARTED ===');
							// Siapkan data peraturan dengan metadata
							$peraturan_data = [
								'id_jenis_dokumen' => $validationData['id_jenis_dokumen'],
								'nomor' => trim($validationData['nomor']),
								'tahun' => trim($validationData['tahun']),
								'judul' => trim($validationData['judul']),
								'tgl_penetapan' => $validationData['tgl_penetapan'],
								'tgl_pengundangan' => $validationData['tgl_pengundangan'] ?: null,
								'id_instansi' => $validationData['id_instansi'],
								'id_status' => $validationData['id_status'],
								'abstrak_teks' => $this->request->getVar('abstrak_teks') ?: $this->request->getVar('abstrak_teks_hidden') ?: null,
								'sumber' => trim($validationData['sumber'] ?? ''),
								'tempat_penetapan' => trim($validationData['tempat_penetapan'] ?? ''),
								'penandatangan' => trim($validationData['penandatangan'] ?? ''),
								'catatan_teks' => $this->request->getVar('catatan_teks') ?: $this->request->getVar('catatan_teks_hidden') ?: null,
								'is_published' => $this->request->getVar('is_published') ? 1 : 0,
								'teu' => trim($validationData['teu'] ?? ''),
								'bidang_hukum' => trim($validationData['bidang_hukum'] ?? '')
							];

							// Siapkan metadata JSON berdasarkan jenis peraturan
							$metadata_json = $this->prepareMetadataJson($validationData, $jenis_peraturan);
							if ($metadata_json) {
								$peraturan_data['metadata_json'] = $metadata_json;
							}

							// Log data untuk debugging
							log_message('debug', 'Add Peraturan Data: ' . json_encode($peraturan_data));

							// File upload - kembali ke sistem normal
							$file = $this->request->getFile('file_dokumen');
							log_message('debug', 'File object: ' . ($file ? 'exists' : 'null'));

							// Update data peraturan menggunakan fungsi saveData()
							$save = $this->loadModel('model')->saveData($peraturan_data, $file);

							// Tambahkan log untuk debugging
							log_message('debug', 'Hasil saveData: ' . json_encode($save));

							// Cek apakah ada error
							if (!empty($save['error']['message'])) {
								$data['message'] = ['status' => 'error', 'message' => $save['error']['message']];
								throw new \Exception($save['error']['message']);
							}

							$id_peraturan = $save['id_peraturan'] ?? null;
							if (!$id_peraturan) {
								$errorMessage = 'Gagal mendapatkan ID peraturan setelah insert.';
								$data['message'] = ['status' => 'error', 'message' => $errorMessage];
								throw new \Exception($errorMessage);
							}

							// Simpan tags
							$tag_ids = $this->request->getVar('tags') ?: [];
							log_message('debug', 'Tags yang akan disimpan: ' . json_encode($tag_ids));

							// Filter tag_ids untuk memastikan hanya ID yang valid
							$valid_tag_ids = array_filter($tag_ids, function ($id) {
								return !empty($id) && is_numeric($id);
							});

							$this->loadModel('peraturanTagModel')->addTagToPeraturan($id_peraturan, $valid_tag_ids);

							if ($this->db->transStatus() === FALSE) {
								$this->db->transRollback();
								$data['message'] = ['status' => 'error', 'message' => 'Gagal menyimpan data karena ada masalah database.'];
							} else {
								$this->db->transCommit();
								$this->session->setFlashdata('message', ['status' => 'success', 'message' => 'Data Peraturan berhasil ditambahkan']);
								return redirect()->to(base_url() . '/data_peraturan');
							}
						} catch (\Exception $e) {
							$this->db->transRollback();
							log_message('error', '[ERROR] ' . $e->getMessage() . ' ' . $e->getTraceAsString());
							$data['message'] = ['status' => 'error', 'message' => 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage()];
						}
					} else {
						$data['message'] = ['status' => 'error', 'message' => $this->validator->getErrors()];
					}
				}
			}

			$data['form_errors'] = $form_errors ?? [];
			return $this->view('data-peraturan-add', $data);
		} catch (\Exception $e) {
			log_message('error', 'Error in add method: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Mendapatkan jenis peraturan berdasarkan ID
	 */
	private function getJenisPeraturanById($id_jenis_dokumen)
	{
		if (!$id_jenis_dokumen) {
			return null;
		}

		$jenisModel = new JenisDokumenModel();
		$jenis = $jenisModel->find($id_jenis_dokumen);
		return $jenis ? $jenis['nama_jenis'] : null;
	}



	/**
	 * Mendapatkan aturan validasi berdasarkan jenis peraturan
	 */
	private function getValidationRulesByJenis($jenis_peraturan, $isEdit = false)
	{
		$baseRules = [
			'id_jenis_dokumen' => [
				'label' => 'Jenis Dokumen',
				'rules' => 'required',
				'errors' => ['required' => '{field} wajib diisi.']
			],
			'nomor' => [
				'label' => 'Nomor Dokumen',
				'rules' => 'required',
				'errors' => ['required' => '{field} wajib diisi.']
			],
			'tahun' => [
				'label' => 'Tahun Dokumen',
				'rules' => 'required|numeric|exact_length[4]',
				'errors' => [
					'required' => '{field} wajib diisi.',
					'numeric' => '{field} harus berupa angka.',
					'exact_length' => '{field} harus 4 digit.'
				]
			],
			'judul' => [
				'label' => 'Judul Dokumen',
				'rules' => 'required',
				'errors' => ['required' => '{field} wajib diisi.']
			],
			'tgl_penetapan' => [
				'label' => 'Tanggal Penetapan',
				'rules' => 'required|valid_date[Y-m-d]',
				'errors' => [
					'required' => '{field} wajib diisi.',
					'valid_date' => '{field} tidak valid (format YYYY-MM-DD).'
				]
			],
			'tgl_pengundangan' => [
				'label' => 'Tanggal Pengundangan',
				'rules' => 'permit_empty|valid_date[Y-m-d]',
				'errors' => [
					'valid_date' => '{field} tidak valid (format YYYY-MM-DD).'
				]
			],
			'id_instansi' => [
				'label' => 'Pemrakarsa',
				'rules' => 'required',
				'errors' => ['required' => '{field} wajib diisi.']
			],
			'id_status' => [
				'label' => 'Status Dokumen',
				'rules' => 'required',
				'errors' => ['required' => '{field} wajib diisi.']
			],
			'file_dokumen' => [
				'label' => 'File Dokumen',
				'rules' => $isEdit ? 'permit_empty|max_size[file_dokumen,25600]|mime_in[file_dokumen,application/pdf]|ext_in[file_dokumen,pdf]' : 'uploaded[file_dokumen]|max_size[file_dokumen,25600]|mime_in[file_dokumen,application/pdf]|ext_in[file_dokumen,pdf]',
				'errors' => [
					'uploaded' => '{field} wajib diunggah.',
					'max_size' => '{field} maksimal 25MB.',
					'mime_in' => '{field} harus berupa PDF.',
					'ext_in' => '{field} harus berupa PDF.'
				]
			],
			'tags' => [
				'label' => 'Tags',
				'rules' => 'permit_empty',
				'errors' => [
					'permit_empty' => '{field} bersifat opsional.'
				]
			]
		];

		// Sesuaikan label berdasarkan jenis peraturan
		if ($jenis_peraturan) {
			switch ($jenis_peraturan) {
				case 'Peraturan Daerah':
				case 'Peraturan Walikota':
					$baseRules['nomor']['label'] = 'Nomor Peraturan';
					$baseRules['tahun']['label'] = 'Tahun Peraturan';
					$baseRules['judul']['label'] = 'Judul Peraturan';
					break;
				case 'Keputusan Walikota':
					$baseRules['nomor']['label'] = 'Nomor Keputusan';
					$baseRules['tahun']['label'] = 'Tahun Keputusan';
					$baseRules['judul']['label'] = 'Judul Keputusan';
					break;
				case 'Naskah Akademik & Kajian':
					$baseRules['nomor']['label'] = 'Nomor Naskah';
					$baseRules['tahun']['label'] = 'Tahun Penyusunan';
					$baseRules['judul']['label'] = 'Judul Naskah';
					$baseRules['tgl_penetapan']['label'] = 'Tanggal Penyusunan';
					break;
				case 'Karya Ilmiah & Buku Hukum':
					$baseRules['nomor']['label'] = 'Nomor Katalog';
					$baseRules['tahun']['label'] = 'Tahun Terbit';
					$baseRules['judul']['label'] = 'Judul Karya';
					$baseRules['tgl_penetapan']['label'] = 'Tanggal Terbit';
					break;
				case 'Opini dan Gagasan Hukum':
					$baseRules['nomor']['label'] = 'Nomor Artikel';
					$baseRules['tahun']['label'] = 'Tahun Publikasi';
					$baseRules['judul']['label'] = 'Judul Artikel';
					$baseRules['tgl_penetapan']['label'] = 'Tanggal Publikasi';
					break;
				case 'Putusan Mahkamah Agung (MA)':
				case 'Putusan Mahkamah Konstitusi (MK)':
					$baseRules['nomor']['label'] = 'Nomor Perkara';
					$baseRules['tahun']['label'] = 'Tahun Putusan';
					$baseRules['judul']['label'] = 'Judul Putusan';
					$baseRules['tgl_penetapan']['label'] = 'Tanggal Putusan';
					break;
			}
		}

		return $baseRules;
	}

	/**
	 * Menyiapkan metadata JSON berdasarkan jenis peraturan
	 */
	private function prepareMetadataJson($data, $jenis_peraturan)
	{
		$metadata = [];

		// Metadata khusus berdasarkan jenis peraturan (sesuai dengan data existing)
		if ($jenis_peraturan) {
			switch ($jenis_peraturan) {
				case 'Putusan Mahkamah Agung (MA)':
				case 'Putusan Mahkamah Konstitusi (MK)':
					$metadata['kategori'] = 'Yurisprudensi';
					$metadata['pengadilan'] = $data['tempat_penetapan'] ?? '';
					$metadata['hakim_ketua'] = $data['penandatangan'] ?? '';
					$metadata['amar_putusan'] = $data['amar_putusan'] ?? null;
					$metadata['nomor_perkara'] = $data['nomor'] ?? '';
					$metadata['pokok_perkara'] = $data['pokok_perkara'] ?? '-';
					$metadata['tanggal_putusan'] = $data['tgl_penetapan'] ?? '';
					break;
				case 'Naskah Akademik & Kajian':
					$metadata['kategori'] = 'Naskah Akademik';
					$metadata['penulis'] = $data['penandatangan'] ?? '';
					$metadata['penerbit'] = $data['sumber'] ?? '';
					$metadata['isbn_issn'] = $data['nomor'] ?? '';
					$metadata['halaman'] = $data['halaman'] ?? '';
					$metadata['tempat_terbit'] = $data['tempat_penetapan'] ?? '';
					$metadata['tanggal_terbit'] = $data['tgl_penetapan'] ?? '';
					break;
				case 'Karya Ilmiah & Buku Hukum':
					$metadata['kategori'] = 'Karya Ilmiah';
					$metadata['penulis'] = $data['penandatangan'] ?? '';
					$metadata['penerbit'] = $data['sumber'] ?? '';
					$metadata['isbn_issn'] = $data['nomor'] ?? '';
					$metadata['halaman'] = $data['halaman'] ?? '';
					$metadata['tempat_terbit'] = $data['tempat_penetapan'] ?? '';
					$metadata['tanggal_terbit'] = $data['tgl_penetapan'] ?? '';
					break;
				case 'Opini dan Gagasan Hukum':
					$metadata['kategori'] = 'Opini Hukum';
					$metadata['penulis'] = $data['penandatangan'] ?? '';
					$metadata['tanggal_publikasi'] = $data['tgl_penetapan'] ?? '';
					$metadata['media_publikasi'] = $data['sumber'] ?? '';
					$metadata['judul_artikel'] = $data['judul'] ?? '';
					break;
				case 'Peraturan Daerah':
				case 'Peraturan Walikota':
				case 'Keputusan Walikota':
				default:
					// Metadata standar untuk peraturan
					$metadata['kategori'] = $jenis_peraturan;
					$metadata['nomor_peraturan'] = $data['nomor'] ?? '';
					$metadata['tahun_peraturan'] = $data['tahun'] ?? '';
					$metadata['tanggal_penetapan'] = $data['tgl_penetapan'] ?? '';
					$metadata['tanggal_pengundangan'] = $data['tgl_pengundangan'] ?? '';
					$metadata['tempat_penetapan'] = $data['tempat_penetapan'] ?? '';
					$metadata['penandatangan'] = $data['penandatangan'] ?? '';
					$metadata['teu'] = $data['teu'] ?? '';
					$metadata['bidang_hukum'] = $data['bidang_hukum'] ?? '';
					$metadata['sumber'] = $data['sumber'] ?? '';
					break;
			}
		}

		// Jangan filter metadata yang kosong - simpan semua field untuk konsistensi
		return json_encode($metadata, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * API untuk mendapatkan metadata template berdasarkan jenis peraturan
	 */
	public function getMetadataTemplate()
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
		}

		$id_jenis_dokumen = $this->request->getGet('id_jenis_dokumen');
		if (!$id_jenis_dokumen) {
			return $this->response->setJSON(['success' => false, 'message' => 'ID jenis dokumen tidak valid']);
		}

		try {
			$jenis_peraturan = $this->getJenisPeraturanById($id_jenis_dokumen);
			if (!$jenis_peraturan) {
				return $this->response->setJSON(['success' => false, 'message' => 'Jenis peraturan tidak ditemukan']);
			}

			// Template metadata berdasarkan jenis peraturan (sesuai dengan struktur metadata existing)
			$metadataTemplates = [
				'Peraturan Daerah' => [
					'nomor' => ['label' => 'Nomor Peraturan', 'required' => true],
					'tahun' => ['label' => 'Tahun Peraturan', 'required' => true],
					'penandatangan' => ['label' => 'Penandatangan', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Penetapan', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Pengundangan', 'required' => false],
					'tempat_penetapan' => ['label' => 'Tempat Penetapan', 'required' => false],
					'teu' => ['label' => 'Tajuk Entri Utama (T.E.U)', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				],
				'Peraturan Walikota' => [
					'nomor' => ['label' => 'Nomor Peraturan', 'required' => true],
					'tahun' => ['label' => 'Tahun Peraturan', 'required' => true],
					'penandatangan' => ['label' => 'Penandatangan', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Penetapan', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Pengundangan', 'required' => false],
					'tempat_penetapan' => ['label' => 'Tempat Penetapan', 'required' => false],
					'teu' => ['label' => 'Tajuk Entri Utama (T.E.U)', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				],
				'Keputusan Walikota' => [
					'nomor' => ['label' => 'Nomor Keputusan', 'required' => true],
					'tahun' => ['label' => 'Tahun Keputusan', 'required' => true],
					'penandatangan' => ['label' => 'Penandatangan', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Penetapan', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Pengundangan', 'required' => false],
					'tempat_penetapan' => ['label' => 'Tempat Penetapan', 'required' => false],
					'teu' => ['label' => 'Tajuk Entri Utama (T.E.U)', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				],
				'Naskah Akademik & Kajian' => [
					'nomor' => ['label' => 'Nomor Naskah', 'required' => true],
					'tahun' => ['label' => 'Tahun Penyusunan', 'required' => true],
					'penandatangan' => ['label' => 'Penyusun/Peneliti', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Penyusunan', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Selesai', 'required' => false],
					'tempat_penetapan' => ['label' => 'Tempat Penyusunan', 'required' => false],
					'halaman' => ['label' => 'Jumlah Halaman', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				],
				'Karya Ilmiah & Buku Hukum' => [
					'nomor' => ['label' => 'Nomor Katalog', 'required' => true],
					'tahun' => ['label' => 'Tahun Terbit', 'required' => true],
					'penandatangan' => ['label' => 'Penulis', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Terbit', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Publikasi', 'required' => false],
					'tempat_penetapan' => ['label' => 'Tempat Terbit', 'required' => false],
					'halaman' => ['label' => 'Jumlah Halaman', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				],
				'Opini dan Gagasan Hukum' => [
					'nomor' => ['label' => 'Nomor Artikel', 'required' => true],
					'tahun' => ['label' => 'Tahun Publikasi', 'required' => true],
					'penandatangan' => ['label' => 'Penulis', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Publikasi', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Terbit', 'required' => false],
					'tempat_penetapan' => ['label' => 'Media Publikasi', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				],
				'Putusan Mahkamah Agung (MA)' => [
					'nomor' => ['label' => 'Nomor Perkara', 'required' => true],
					'tahun' => ['label' => 'Tahun Putusan', 'required' => true],
					'penandatangan' => ['label' => 'Hakim Ketua', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Putusan', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Registrasi', 'required' => false],
					'tempat_penetapan' => ['label' => 'Pengadilan', 'required' => false],
					'pokok_perkara' => ['label' => 'Pokok Perkara', 'required' => false],
					'amar_putusan' => ['label' => 'Amar Putusan', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				],
				'Putusan Mahkamah Konstitusi (MK)' => [
					'nomor' => ['label' => 'Nomor Perkara', 'required' => true],
					'tahun' => ['label' => 'Tahun Putusan', 'required' => true],
					'penandatangan' => ['label' => 'Hakim Ketua', 'required' => false],
					'sumber' => ['label' => 'Sumber', 'required' => false],
					'tgl_penetapan' => ['label' => 'Tanggal Putusan', 'required' => true],
					'tgl_pengundangan' => ['label' => 'Tanggal Registrasi', 'required' => false],
					'tempat_penetapan' => ['label' => 'Pengadilan', 'required' => false],
					'pokok_perkara' => ['label' => 'Pokok Perkara', 'required' => false],
					'amar_putusan' => ['label' => 'Amar Putusan', 'required' => false],
					'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
				]
			];

			// Default template untuk jenis yang tidak terdaftar
			$defaultTemplate = [
				'nomor' => ['label' => 'Nomor Dokumen', 'required' => true],
				'tahun' => ['label' => 'Tahun Dokumen', 'required' => true],
				'penandatangan' => ['label' => 'Penandatangan', 'required' => false],
				'sumber' => ['label' => 'Sumber', 'required' => false],
				'tgl_penetapan' => ['label' => 'Tanggal Penetapan', 'required' => true],
				'tgl_pengundangan' => ['label' => 'Tanggal Pengundangan', 'required' => false],
				'tempat_penetapan' => ['label' => 'Tempat Penetapan', 'required' => false],
				'teu' => ['label' => 'Tajuk Entri Utama (T.E.U)', 'required' => false],
				'bidang_hukum' => ['label' => 'Bidang Hukum', 'required' => false]
			];

			$template = $metadataTemplates[$jenis_peraturan] ?? $defaultTemplate;

			return $this->response->setJSON([
				'success' => true,
				'jenis_peraturan' => $jenis_peraturan,
				'template' => $template
			]);
		} catch (\Exception $e) {
			log_message('error', 'Error getting metadata template: ' . $e->getMessage());
			return $this->response->setJSON([
				'success' => false,
				'message' => 'Terjadi kesalahan: ' . $e->getMessage()
			]);
		}
	}

	public function edit()
	{
		$this->hasPermissionPrefix('update', 'peraturan');

		$this->data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		$this->data['page_title'] = $this->data['title'];
		$data = $this->data;

		// Ambil id_peraturan dari POST (submit) atau GET (buka halaman)
		$id_peraturan = $this->request->getPost('id_peraturan') ?: $this->request->getGet('id');
		if (empty($id_peraturan)) {
			$this->errorDataNotFound();
			return;
		}

		// Ambil data peraturan berdasarkan ID
		$peraturan = $this->loadModel('model')->getPeraturanDetail($id_peraturan);

		if (empty($peraturan)) {
			$this->errorDataNotFound();
			return;
		}

		// Ambil data untuk dropdowns menggunakan lazy loading
		$data['jenis_dokumen_list'] = (new JenisDokumenModel())->orderBy('nama_jenis', 'ASC')->findAll();
		$data['instansi_list'] = $this->loadModel('instansiModel')->orderBy('nama_instansi', 'ASC')->findAll();
		$data['status_dokumen_list'] = $this->loadModel('statusDokumenModel')->orderBy('nama_status', 'ASC')->findAll();

		// Ambil data tag untuk dropdown
		$data['tags'] = $this->loadModel('tagModel')->getAllTags();
		$data['selected_tags'] = $this->loadModel('peraturanTagModel')->getTagByPeraturanId($id_peraturan);

		// Submit
		$data['msg'] = [];
		if ($this->request->getMethod() === 'post') {
			$form_errors = false;

			// Validasi file dokumen untuk edit - file opsional
			$file = $this->request->getFile('file_dokumen');
			log_message('debug', 'Edit - File validation: Error=' . $file->getError() . ', ErrorString=' . $file->getErrorString());

			if ($file->getError() !== 0 && $file->getError() !== 4) { // 4 = UPLOAD_ERR_NO_FILE (tidak ada file yang diupload)
				$form_errors['file_dokumen'] = 'Error saat upload file: ' . $file->getErrorString();
				log_message('debug', 'Edit - File upload error: ' . $file->getErrorString());
			} else if ($file->getError() === 0) { // File diupload
				log_message('debug', 'Edit - File uploaded: ' . $file->getName() . ', Size: ' . $file->getSize() . ', Extension: ' . $file->getExtension());

				if ($file->getExtension() !== 'pdf') {
					$form_errors['file_dokumen'] = 'File harus berformat PDF';
					log_message('debug', 'Edit - File format error: not PDF');
				}

				if ($file->getSize() > 25 * 1024 * 1024) { // 25MB sesuai dengan validasi di model
					$form_errors['file_dokumen'] = 'Ukuran file maksimal 25MB';
					log_message('debug', 'Edit - File size error: too large');
				}
			} else {
				log_message('debug', 'Edit - No file uploaded (Error=4), using existing file');
			}

			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
				$data['form_errors'] = $form_errors;
			} else {
				// Validasi data yang diterima
				$validationData = [
					'id_jenis_dokumen' => $this->request->getVar('id_jenis_dokumen'),
					'nomor' => $this->request->getVar('nomor'),
					'tahun' => $this->request->getVar('tahun'),
					'judul' => $this->request->getVar('judul'),
					'tgl_penetapan' => $this->request->getVar('tgl_penetapan'),
					'tgl_pengundangan' => $this->request->getVar('tgl_pengundangan'),
					'id_instansi' => $this->request->getVar('id_instansi'),
					'id_status' => $this->request->getVar('id_status'),
					'penandatangan' => $this->request->getVar('penandatangan'),
					'sumber' => $this->request->getVar('sumber'),
					'tempat_penetapan' => $this->request->getVar('tempat_penetapan'),
					'teu' => $this->request->getVar('teu'),
					'bidang_hukum' => $this->request->getVar('bidang_hukum')
				];

				// Dapatkan jenis peraturan untuk validasi yang sesuai
				$jenis_peraturan = $this->getJenisPeraturanById($validationData['id_jenis_dokumen']);
				$validationRules = $this->getValidationRulesByJenis($jenis_peraturan, true);

				log_message('debug', 'Edit - Validation data: ' . json_encode($validationData));
				log_message('debug', 'Edit - Validation rules for file_dokumen: ' . $validationRules['file_dokumen']['rules']);

				if ($this->validateData($validationData, $validationRules)) {
					$this->db->transBegin();

					try {
						// Siapkan data peraturan dengan metadata
						$peraturan_data = [
							'id_peraturan' => $id_peraturan,
							'id_jenis_dokumen' => $validationData['id_jenis_dokumen'],
							'nomor' => trim($validationData['nomor']),
							'tahun' => trim($validationData['tahun']),
							'judul' => trim($validationData['judul']),
							'tgl_penetapan' => $validationData['tgl_penetapan'],
							'tgl_pengundangan' => $validationData['tgl_pengundangan'] ?: null,
							'id_instansi' => $validationData['id_instansi'],
							'id_status' => $validationData['id_status'],
							'abstrak_teks' => $this->request->getVar('abstrak_teks') ?: $this->request->getVar('abstrak_teks_hidden') ?: null,
							'sumber' => trim($validationData['sumber'] ?? ''),
							'tempat_penetapan' => trim($validationData['tempat_penetapan'] ?? ''),
							'penandatangan' => trim($validationData['penandatangan'] ?? ''),
							'catatan_teks' => $this->request->getVar('catatan_teks') ?: $this->request->getVar('catatan_teks_hidden') ?: null,
							'is_published' => $this->request->getVar('is_published') ? 1 : 0,
							'teu' => trim($validationData['teu'] ?? ''),
							'bidang_hukum' => trim($validationData['bidang_hukum'] ?? '')
						];

						// Siapkan metadata JSON berdasarkan jenis peraturan
						$metadata_json = $this->prepareMetadataJson($validationData, $jenis_peraturan);
						if ($metadata_json) {
							$peraturan_data['metadata_json'] = $metadata_json;
						}

						// Log data untuk debugging
						log_message('debug', 'Edit Peraturan Data: ' . json_encode($peraturan_data));

						// File upload untuk edit - kembali ke sistem normal
						$file = $this->request->getFile('file_dokumen');
						if ($file->getError() === 4) {
							$file = null; // Tidak ada file baru diupload
						}
						// Update data peraturan menggunakan fungsi saveData()
						$save = $this->loadModel('model')->saveData($peraturan_data, $file);

						// Tambahkan log untuk debugging
						log_message('debug', 'Hasil saveData: ' . json_encode($save));

						// Cek apakah ada error
						if (!empty($save['error']['message'])) {
							$data['msg']['status'] = 'error';
							$data['msg']['content'] = $save['error']['message'];
							throw new \Exception($save['error']['message']);
						}

						// Simpan tag peraturan
						$tag_ids = $this->request->getVar('tags') ?? [];
						log_message('debug', 'Edit - Tags yang akan disimpan: ' . json_encode($tag_ids));

						// Filter tag_ids untuk memastikan hanya ID yang valid
						$valid_tag_ids = array_filter($tag_ids, function ($id) {
							return !empty($id) && is_numeric($id);
						});

						$this->loadModel('peraturanTagModel')->addTagToPeraturan($id_peraturan, $valid_tag_ids);

						if ($this->db->transStatus() === FALSE) {
							$this->db->transRollback();
							$data['msg']['status'] = 'error';
							$data['msg']['content'] = 'Gagal menyimpan data karena ada masalah database.';
						} else {
							$this->db->transCommit();
							$data['msg']['status'] = 'ok';
							$data['msg']['content'] = 'Data Peraturan berhasil disimpan';

							// Refresh data peraturan
							$peraturan = $this->loadModel('model')->find($id_peraturan);
						}
					} catch (\Exception $e) {
						$this->db->transRollback();
						log_message('error', '[ERROR] Edit Peraturan: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
						$data['msg']['status'] = 'error';
						$data['msg']['content'] = 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage();
					}
				} else {
					$validation_errors = $this->validator->getErrors();
					log_message('debug', 'Edit - Validation failed: ' . json_encode($validation_errors));
					$data['msg']['status'] = 'error';
					$data['msg']['content'] = $validation_errors;
				}
			}
		}

		$data['breadcrumb']['Edit'] = '';
		$data['peraturan'] = $peraturan;
		$data['form_errors'] = $form_errors ?? [];

		$this->view('data-peraturan-add.php', $data);
	}

	// REMOVED: peraturan_terkait methods - functionality moved to enhanced relasi system
	// Users can now use "Melaksanakan" relationship type in relasi_peraturan

	public function ajaxGetPeraturan()
	{
		try {
			$search = $this->request->getGet('search');

			// Log parameter untuk debugging
			log_message('debug', 'ajaxGetPeraturan (Admin) - Parameter: search=' . $search);

			// Gunakan method pencarian khusus admin
			$peraturanModel = new \App\Models\WebPeraturanModel();
			$result = $peraturanModel->searchPeraturanForAdmin(['keyword' => $search]);

			// Format data untuk Select2
			$data = [];
			$data['results'] = [];

			if (!empty($result['data'])) {
				foreach ($result['data'] as $row) {
					$data['results'][] = [
						'id'   => $row['id_peraturan'],
						'text' => $row['judul'] . ' (No: ' . $row['nomor'] . ' Tahun: ' . $row['tahun'] . ')'
					];
				}
			}

			// Untuk pencarian admin, kita tidak menggunakan paginasi 'load more' dari Select2
			// karena kita ingin semua hasil yang cocok ditampilkan.
			$data['pagination'] = ['more' => false];

			return $this->response->setJSON($data);
		} catch (\Exception $e) {
			// Log error
			log_message('error', 'ajaxGetPeraturan - Error: ' . $e->getMessage());

			// Kembalikan respons error dalam format yang diharapkan Select2
			return $this->response->setJSON([
				'error'   => true,
				'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
				'results' => []
			])->setStatusCode(500);
		}
	}

	/**
	 * Endpoint AJAX untuk pencarian instansi (Select2).
	 * 
	 * @return JSON
	 */
	public function ajaxGetInstansi()
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setStatusCode(403, 'Forbidden');
		}

		$keyword = $this->request->getGet('q');
		$data = $this->loadModel('instansiModel')->searchInstansi($keyword);

		return $this->response->setJSON($data);
	}

	/**
	 * Helper function.
	 * 
	 * @return bool
	 */
	private function validateCSRF()
	{
		$csrf_name = csrf_token();
		$csrf_value = csrf_hash();

		$posted_csrf_value = $this->request->getPost($csrf_name);

		return $posted_csrf_value === $csrf_value;
	}

	/**
	 * Validasi nama file untuk mencegah path traversal
	 */
	private function validateFileName($filename)
	{
		// Cegah path traversal
		if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
			return false;
		}

		// Validasi karakter yang diizinkan
		if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
			return false;
		}

		return true;
	}

	/**
	 * Mendapatkan ID user yang sedang login
	 */
	private function getUserId()
	{
		return $this->session->get('user_id') ?? 'unknown';
	}

	/**
	 * Validasi file signature untuk mencegah upload file berbahaya
	 */
	private function validateFileSignature($file)
	{
		$allowedSignatures = [
			'pdf' => ['25504446'], // %PDF
			'doc' => ['D0CF11E0A1B11AE1'],
			'docx' => ['504B0304'], // ZIP signature for DOCX
			'jpg' => ['FFD8FF'],
			'jpeg' => ['FFD8FF'],
			'png' => ['89504E47']
		];

		try {
			$fileHandle = fopen($file->getTempName(), 'rb');
			if (!$fileHandle) {
				return false;
			}

			$fileSignature = bin2hex(fread($fileHandle, 8));
			fclose($fileHandle);

			$extension = strtolower($file->getClientExtension());

			if (!isset($allowedSignatures[$extension])) {
				return false;
			}

			foreach ($allowedSignatures[$extension] as $signature) {
				if (stripos($fileSignature, $signature) === 0) {
					return true;
				}
			}

			return false;
		} catch (\Exception $e) {
			log_message('error', 'Error validating file signature: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Mendapatkan data tag untuk AJAX request (digunakan oleh Select2 dan autocomplete)
	 * 
	 * @return JSON
	 */
	public function ajaxGetTag()
	{
		// Ambil parameter dari request
		$keyword = $this->request->getGet('q');
		$page = $this->request->getGet('page') ?? 1;
		$limit = 10; // Jumlah item per halaman
		$offset = ($page - 1) * $limit;

		// Ambil data tag dari model
		$tags = $this->loadModel('tagModel')->getTagsByKeyword($keyword, $limit, $offset);
		$total = $this->loadModel('tagModel')->countTagsByKeyword($keyword);

		// Format data untuk Select2
		$results = [];
		foreach ($tags as $tag) {
			$results[] = [
				'id' => $tag['id_tag'],
				'text' => $tag['nama_tag']
			];
		}

		// Kembalikan response dalam format yang diharapkan oleh Select2
		return $this->response->setJSON([
			'results' => $results,
			'pagination' => [
				'more' => ($page * $limit) < $total
			]
		]);
	}

	public function relasi_peraturan()
	{
		$id_peraturan = (int) $this->request->getGet('id');
		if (!$id_peraturan) {
			$this->errorDataNotFound();
			return;
		}

		// Pastikan peraturan ada
		$peraturan_sumber = $this->loadModel('model')->getPeraturanDetail($id_peraturan);
		if (!$peraturan_sumber) {
			$this->errorDataNotFound();
			return;
		}

		$data = $this->data;

		// Enhanced: Load data untuk sistem relasi baru
		$data['result'] = $this->loadModel('relasiModel')->getRelasiWithJenisRelasi($id_peraturan);
		$data['peraturan_sumber'] = $peraturan_sumber;
		$data['jenis_relasi_options'] = $this->loadModel('jenisRelasiModel')->getDropdownOptions();
		$data['jenis_relasi_config'] = $this->loadModel('jenisRelasiModel')->getActiveJenisRelasi();

		$this->view('relasi-result.php', $data);
	}

	public function save_relasi()
	{
		$id_peraturan = (int) $this->request->getPost('id_peraturan');
		$id_jenis_relasi = (int) $this->request->getPost('id_jenis_relasi');
		$id_peraturan_terkait = (int) $this->request->getPost('id_peraturan_terkait');
		$keterangan = trim($this->request->getPost('keterangan') ?? '');

		// Validasi input
		if (!$id_peraturan || !$id_jenis_relasi || !$id_peraturan_terkait) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Semua field harus diisi dengan benar']);
			return redirect()->to(base_url('data_peraturan/relasi_peraturan?id=' . $id_peraturan));
		}

		// Validasi circular reference
		if ($this->loadModel('relasiModel')->hasCircularReference($id_peraturan, $id_peraturan_terkait)) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Tidak dapat membuat relasi karena sudah ada relasi sebaliknya']);
			return redirect()->to(base_url('data_peraturan/relasi_peraturan?id=' . $id_peraturan));
		}

		try {
			// Simpan relasi dengan enhanced functionality
			$relasiId = $this->loadModel('relasiModel')->saveRelasiWithStatusUpdate([
				'id_peraturan_sumber' => $id_peraturan,
				'id_peraturan_terkait' => $id_peraturan_terkait,
				'id_jenis_relasi' => $id_jenis_relasi,
				'keterangan' => $keterangan
			]);

			if ($relasiId) {
				// Ambil info jenis relasi untuk pesan
				$jenisRelasi = $this->loadModel('jenisRelasiModel')->find($id_jenis_relasi);
				$message = 'Relasi peraturan berhasil ditambahkan';

				if ($jenisRelasi && $jenisRelasi['auto_update_status']) {
					$message .= '. Status peraturan terkait telah diperbarui secara otomatis.';
				}

				$this->session->setFlashdata('message', ['status' => 'success', 'message' => $message]);
			} else {
				throw new \Exception('Gagal menyimpan relasi');
			}
		} catch (\Exception $e) {
			log_message('error', 'Error saving relasi: ' . $e->getMessage());
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Gagal menyimpan relasi: ' . $e->getMessage()]);
		}

		return redirect()->to(base_url('data_peraturan/relasi_peraturan?id=' . $id_peraturan));
	}

	public function delete_relasi()
	{
		$id_sumber = (int) $this->request->getGet('id_sumber');
		$id_terkait = (int) $this->request->getGet('id_terkait');

		if (!$id_sumber || !$id_terkait) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Parameter tidak valid untuk menghapus relasi.']);
			return redirect()->back()->withInput();
		}

		try {
			// Hapus relasi dengan rollback status otomatis
			$result = $this->loadModel('relasiModel')->deleteRelasiWithStatusRollback($id_sumber, $id_terkait);

			if ($result) {
				$this->session->setFlashdata('message', ['status' => 'success', 'message' => 'Relasi peraturan berhasil dihapus. Status peraturan terkait telah dikembalikan jika diperlukan.']);
			} else {
				throw new \Exception('Gagal menghapus relasi');
			}
		} catch (\Exception $e) {
			log_message('error', 'Error deleting relasi: ' . $e->getMessage());
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Gagal menghapus relasi: ' . $e->getMessage()]);
		}

		return redirect()->to(base_url('data_peraturan/relasi_peraturan?id=' . $id_sumber));
	}

	public function lampiran()
	{
		$this->hasPermission('update');
		$id_peraturan = $this->request->getGet('id');
		if (empty($id_peraturan)) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Parameter tidak valid.']);
			return redirect()->to(base_url('data_peraturan'));
		}

		$peraturan = $this->loadModel('model')->find($id_peraturan);
		if (empty($peraturan)) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Data peraturan tidak ditemukan.']);
			return redirect()->to(base_url('data_peraturan'));
		}

		$this->data['peraturan'] = $peraturan;
		$this->data['lampiran'] = $this->loadModel('lampiranModel')
			->where('id_peraturan', $id_peraturan)
			->orderBy('urutan', 'ASC')
			->orderBy('created_at', 'DESC')
			->findAll();
		$this->data['title'] = 'Kelola Lampiran: ' . $peraturan['judul'];
		$this->data['message'] = $this->session->getFlashdata('message');

		$this->view('data-peraturan-lampiran.php', $this->data);
	}

	public function save_lampiran()
	{
		$this->hasPermission('update');

		$id_peraturan = $this->request->getPost('id_peraturan');
		$judul_lampiran = $this->request->getPost('judul_lampiran');
		$file = $this->request->getFile('file_lampiran');

		if (empty($id_peraturan) || empty($judul_lampiran) || !$file->isValid()) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Semua field harus diisi.']);
			return redirect()->to(base_url('data_peraturan/lampiran?id=' . $id_peraturan));
		}

		// PERBAIKAN: Enhanced file validation
		$validationRule = [
			'file_lampiran' => [
				'label' => 'File Lampiran',
				'rules' => 'uploaded[file_lampiran]'
					. '|mime_in[file_lampiran,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png]'
					. '|ext_in[file_lampiran,pdf,doc,docx,jpg,jpeg,png]'
					. '|max_size[file_lampiran,25600]', // 25MB
			],
		];

		if (!$this->validate($validationRule)) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => $this->validator->getErrors()['file_lampiran']]);
			return redirect()->to(base_url('data_peraturan/lampiran?id=' . $id_peraturan));
		}

		// PERBAIKAN: Validasi file signature
		if (!$this->validateFileSignature($file)) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'File tidak valid atau berbahaya.']);
			return redirect()->to(base_url('data_peraturan/lampiran?id=' . $id_peraturan));
		}

		try {
			$this->db->transBegin();

			$newName = $file->getRandomName();
			if (!$file->move(ROOTPATH . 'uploads/lampiran', $newName)) {
				throw new \Exception('Gagal mengupload file: ' . $file->getErrorString());
			}

			$data = [
				'id_peraturan' => $id_peraturan,
				'judul_lampiran' => trim($judul_lampiran),
				'file_lampiran' => $newName,
				'original_name' => $file->getClientName(),
				'file_size' => $file->getSize(),
				'mime_type' => $file->getClientMimeType()
			];

			if (!$this->loadModel('lampiranModel')->save($data)) {
				throw new \Exception('Gagal menyimpan data lampiran ke database');
			}

			$this->db->transCommit();
			$this->session->setFlashdata('message', ['status' => 'success', 'message' => 'Lampiran berhasil disimpan.']);
		} catch (\Exception $e) {
			$this->db->transRollback();

			// Cleanup uploaded file on error
			if (isset($newName) && file_exists(ROOTPATH . 'uploads/lampiran/' . $newName)) {
				unlink(ROOTPATH . 'uploads/lampiran/' . $newName);
			}

			log_message('error', 'Error saving lampiran: ' . $e->getMessage());
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Gagal menyimpan lampiran: ' . $e->getMessage()]);
		}

		return redirect()->to(base_url('data_peraturan/lampiran?id=' . $id_peraturan));
	}

	public function delete()
	{
		log_message('info', 'Delete method called with POST data: ' . json_encode($this->request->getPost()));

		$this->hasPermissionPrefix('delete', 'peraturan');

		// Validasi CSRF token
		if (!$this->validateCSRF()) {
			log_message('error', 'CSRF validation failed in delete method');
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Token keamanan tidak valid. Silakan refresh halaman.']);
			return redirect()->to(base_url('data_peraturan'));
		}

		$id = $this->request->getPost('id');
		log_message('info', 'Delete request for ID: ' . $id);

		if (empty($id)) {
			log_message('error', 'Empty ID parameter in delete method');
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Parameter tidak valid.']);
			return redirect()->to(base_url('data_peraturan'));
		}

		// Get regulation data to find the file name
		$peraturan = $this->loadModel('model')->find($id);
		if (!$peraturan) {
			log_message('error', 'Regulation data not found for ID: ' . $id);
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Data peraturan tidak ditemukan.']);
			return redirect()->to(base_url('data_peraturan'));
		}

		log_message('info', 'Found regulation data: ' . json_encode($peraturan));

		// 1. Delete main document file
		if (!empty($peraturan['file_dokumen'])) {
			$file_path = ROOTPATH . 'uploads/peraturan/' . $peraturan['file_dokumen'];
			log_message('info', 'Attempting to delete main file: ' . $file_path);
			if (file_exists($file_path)) {
				if (unlink($file_path)) {
					log_message('info', 'Main file deleted successfully: ' . $file_path);
				} else {
					log_message('error', 'Failed to delete main file: ' . $file_path);
				}
			} else {
				log_message('warning', 'Main file not found: ' . $file_path);
			}
		} else {
			log_message('info', 'No main file to delete for regulation ID: ' . $id);
		}

		// 2. Delete associated attachments
		$lampiran_list = $this->loadModel('lampiranModel')->where('id_peraturan', $id)->findAll();
		log_message('info', 'Found ' . count($lampiran_list) . ' attachments for regulation ID: ' . $id);

		if ($lampiran_list && !empty($lampiran_list)) {
			foreach ($lampiran_list as $lampiran) {
				// Delete attachment file
				$lampiran_path = ROOTPATH . 'uploads/lampiran/' . $lampiran['file_lampiran'];
				log_message('info', 'Attempting to delete attachment: ' . $lampiran_path);
				if (file_exists($lampiran_path)) {
					if (unlink($lampiran_path)) {
						log_message('info', 'Attachment deleted successfully: ' . $lampiran_path);
					} else {
						log_message('error', 'Failed to delete attachment: ' . $lampiran_path);
					}
				} else {
					log_message('warning', 'Attachment file not found: ' . $lampiran_path);
				}
			}
			// Delete attachment records from DB for this regulation
			$deleted_attachments = $this->loadModel('lampiranModel')->where('id_peraturan', $id)->delete();
			log_message('info', 'Deleted ' . $deleted_attachments . ' attachment records from database');
		} else {
			log_message('info', 'No attachments found for regulation ID: ' . $id);
		}

		// 3. Delete related data
		$deleted_relasi = $this->loadModel('relasiModel')->where('id_peraturan_sumber', $id)->orWhere('id_peraturan_terkait', $id)->delete();
		log_message('info', 'Deleted ' . $deleted_relasi . ' relation records for regulation ID: ' . $id);

		$deleted_tags = $this->loadModel('peraturanTagModel')->where('id_peraturan', $id)->delete();
		log_message('info', 'Deleted ' . $deleted_tags . ' tag records for regulation ID: ' . $id);

		// 4. Delete the regulation record itself
		try {
			if ($this->loadModel('model')->delete($id)) {
				log_message('info', 'Data peraturan berhasil dihapus: ID ' . $id);
				$this->session->setFlashdata('message', ['status' => 'success', 'message' => 'Data peraturan dan semua file terkait berhasil dihapus.']);
			} else {
				log_message('error', 'Gagal menghapus data peraturan dari database: ID ' . $id);
				$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Gagal menghapus data peraturan dari database.']);
			}
		} catch (\Exception $e) {
			log_message('error', 'Exception saat menghapus data peraturan: ' . $e->getMessage());
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()]);
		}

		// Redirect dengan parameter untuk refresh DataTable
		return redirect()->to(base_url('data_peraturan?refresh=1'));
	}

	/**
	 * AJAX endpoint untuk hapus data peraturan
	 */
	public function delete_ajax()
	{
		// Validasi AJAX request
		if (!$this->request->isAJAX()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
		}

		$this->hasPermissionPrefix('delete', 'peraturan');

		// Validasi CSRF token (temporary disabled for debugging)
		// if (!$this->validateCSRF()) {
		// 	return $this->response->setJSON(['success' => false, 'message' => 'Token keamanan tidak valid']);
		// }

		$id = $this->request->getPost('id');
		if (empty($id)) {
			return $this->response->setJSON(['success' => false, 'message' => 'Parameter tidak valid']);
		}

		// Get regulation data to find the file name
		$peraturan = $this->loadModel('model')->find($id);
		if (!$peraturan) {
			return $this->response->setJSON(['success' => false, 'message' => 'Data peraturan tidak ditemukan']);
		}

		try {
			// 1. Delete main document file
			if (!empty($peraturan['file_dokumen'])) {
				$file_path = ROOTPATH . 'uploads/peraturan/' . $peraturan['file_dokumen'];
				if (file_exists($file_path)) {
					unlink($file_path);
				}
			}

			// 2. Delete associated attachments
			$lampiran_list = $this->loadModel('lampiranModel')->where('id_peraturan', $id)->findAll();
			if ($lampiran_list && !empty($lampiran_list)) {
				foreach ($lampiran_list as $lampiran) {
					// Delete attachment file
					$lampiran_path = ROOTPATH . 'uploads/lampiran/' . $lampiran['file_lampiran'];
					if (file_exists($lampiran_path)) {
						unlink($lampiran_path);
					}
				}
				// Delete attachment records from DB for this regulation
				$this->loadModel('lampiranModel')->where('id_peraturan', $id)->delete();
			}

			// 3. Delete related data
			$this->loadModel('relasiModel')->where('id_peraturan_sumber', $id)->orWhere('id_peraturan_terkait', $id)->delete();
			$this->loadModel('peraturanTagModel')->where('id_peraturan', $id)->delete();

			// 4. Delete the regulation record itself
			if ($this->loadModel('model')->delete($id)) {
				log_message('info', 'Data peraturan berhasil dihapus via AJAX: ID ' . $id);

				// Debug: Log response data
				$response_data = [
					'success' => true,
					'message' => 'Data peraturan dan semua file terkait berhasil dihapus',
					'csrf_token_name' => csrf_token(),
					'csrf_token_value' => csrf_hash(),
					'deleted_id' => $id,
					'timestamp' => date('Y-m-d H:i:s')
				];

				log_message('info', 'AJAX Delete Response: ' . json_encode($response_data));

				return $this->response->setJSON($response_data);
			} else {
				log_message('error', 'Gagal menghapus data peraturan dari database: ID ' . $id);
				return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus data peraturan dari database']);
			}
		} catch (\Exception $e) {
			log_message('error', 'Exception saat menghapus data peraturan via AJAX: ' . $e->getMessage());
			return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()]);
		}
	}

	public function ajaxSearchPeraturan()
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setStatusCode(403, 'Forbidden');
		}

		$search = $this->request->getGet('q');
		$id_sumber = $this->request->getGet('id_sumber'); // ID peraturan saat ini, untuk dikecualikan dari hasil

		$data = $this->loadModel('model')->searchPeraturanForAdmin($search, $id_sumber);

		$results = [];
		foreach ($data as $item) {
			$results[] = [
				'id' => $item['id_peraturan'],
				'text' => $item['nama_jenis'] . ' No. ' . $item['nomor'] . ' Tahun ' . $item['tahun'] . ' tentang ' . $item['judul']
			];
		}

		return $this->response->setJSON(['results' => $results]);
	}

	/**
	 * AJAX endpoint untuk mendapatkan info jenis relasi
	 */
	public function ajaxGetJenisRelasiInfo()
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setStatusCode(403, 'Forbidden');
		}

		$id_jenis_relasi = (int) $this->request->getGet('id');
		if (!$id_jenis_relasi) {
			return $this->response->setJSON(['error' => 'Parameter tidak valid']);
		}

		$jenisRelasi = $this->loadModel('jenisRelasiModel')->find($id_jenis_relasi);
		if (!$jenisRelasi) {
			return $this->response->setJSON(['error' => 'Jenis relasi tidak ditemukan']);
		}

		return $this->response->setJSON([
			'success' => true,
			'data' => [
				'nama_jenis' => $jenisRelasi['nama_jenis'],
				'deskripsi' => $jenisRelasi['deskripsi'],
				'auto_update_status' => (bool) $jenisRelasi['auto_update_status'],
				'status_target' => $jenisRelasi['status_target']
			]
		]);
	}

	public function delete_lampiran()
	{
		$this->hasPermission('update');

		$id_lampiran = $this->request->getPost('id_lampiran');
		$id_peraturan = $this->request->getPost('id_peraturan');

		if (empty($id_lampiran) || empty($id_peraturan)) {
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Parameter tidak valid.']);
			return redirect()->to(base_url('data_peraturan/lampiran?id=' . $id_peraturan));
		}

		try {
			$this->db->transBegin();

			// Get attachment data
			$lampiran = $this->loadModel('lampiranModel')->find($id_lampiran);
			if (!$lampiran || $lampiran['id_peraturan'] != $id_peraturan) {
				throw new \Exception('Data lampiran tidak ditemukan atau tidak valid.');
			}

			// Delete file
			$file_path = ROOTPATH . 'uploads/lampiran/' . $lampiran['file_lampiran'];
			if (file_exists($file_path)) {
				unlink($file_path);
			}

			// Delete database record
			if (!$this->loadModel('lampiranModel')->delete($id_lampiran)) {
				throw new \Exception('Gagal menghapus data lampiran dari database.');
			}

			$this->db->transCommit();
			$this->session->setFlashdata('message', ['status' => 'success', 'message' => 'Lampiran berhasil dihapus.']);
		} catch (\Exception $e) {
			$this->db->transRollback();
			log_message('error', 'Error deleting lampiran: ' . $e->getMessage());
			$this->session->setFlashdata('message', ['status' => 'error', 'message' => 'Gagal menghapus lampiran: ' . $e->getMessage()]);
		}

		return redirect()->to(base_url('data_peraturan/lampiran?id=' . $id_peraturan));
	}

	/**
	 * Menambahkan tag baru melalui AJAX
	 * 
	 * @return JSON
	 */
	public function add_tag_ajax()
	{
		// Periksa apakah request adalah AJAX
		if (!$this->request->isAJAX()) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Invalid request method'
			]);
		}

		// Validasi CSRF
		if (!$this->validateCSRF()) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Invalid security token'
			]);
		}

		// Ambil nama tag dari POST
		$nama_tag = trim($this->request->getPost('nama_tag'));

		// Validasi nama tag
		if (empty($nama_tag)) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Nama tag tidak boleh kosong'
			]);
		}

		// Cek apakah tag sudah ada
		$existing_tag = $this->loadModel('tagModel')->where('nama_tag', $nama_tag)->first();
		if ($existing_tag) {
			return $this->response->setJSON([
				'status' => 'success',
				'id_tag' => $existing_tag['id_tag'],
				'message' => 'Tag sudah ada'
			]);
		}

		// Buat slug dari nama tag
		$slug = $this->loadModel('tagModel')->createSlug($nama_tag);

		// Simpan tag baru
		$data = [
			'nama_tag' => $nama_tag,
			'slug_tag' => $slug
		];

		try {
			$this->loadModel('tagModel')->save($data);
			$id_tag = $this->loadModel('tagModel')->getInsertID();

			return $this->response->setJSON([
				'status' => 'success',
				'id_tag' => $id_tag,
				'message' => 'Tag berhasil ditambahkan'
			]);
		} catch (\Exception $e) {
			log_message('error', '[ERROR] Gagal menambahkan tag: ' . $e->getMessage());
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Gagal menyimpan tag: ' . $e->getMessage()
			]);
		}
	}

	/**
	 * DataTable endpoint untuk lampiran peraturan
	 */
	public function getDataDT()
	{
		// Debug logging
		log_message('debug', 'Data_peraturan::getDataDT() called');
		log_message('debug', 'Request method: ' . $this->request->getMethod());
		log_message('debug', 'User session: ' . json_encode($this->session->get('user')));

		// Validate AJAX session with retry mechanism
		$retryCount = 0;
		$maxRetries = 3;

		while ($retryCount < $maxRetries) {
			if ($this->validateAjaxSession()) {
				break;
			}

			$retryCount++;
			if ($retryCount < $maxRetries) {
				// Wait a bit before retry
				usleep(100000); // 0.1 second
				// Refresh session
				$this->session->regenerate();
			}
		}

		if ($retryCount >= $maxRetries) {
			log_message('error', 'Data_peraturan::getDataDT() - Session validation failed after ' . $maxRetries . ' retries');
			return $this->ajaxResponse([
				'error' => 'Session validation failed. Please refresh the page.',
				'data' => [],
				'recordsTotal' => 0,
				'recordsFiltered' => 0
			], 401);
		}

		// Permission check - gunakan read permission, bukan update
		// Karena ini hanya untuk menampilkan data, bukan mengubah
		if (!$this->hasPermission('read') && !$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
			log_message('error', 'Data_peraturan::getDataDT() - User has no read permission');
			return $this->ajaxResponse([
				'error' => 'Access denied: No read permission',
				'data' => [],
				'recordsTotal' => 0,
				'recordsFiltered' => 0
			], 403);
		}

		// Validasi request
		if (!$this->request->isAJAX()) {
			return $this->ajaxResponse(['error' => 'Invalid request'], 400);
		}

		// Ambil parameter DataTable
		$draw = $this->request->getGet('draw');
		$start = $this->request->getGet('start');
		$length = $this->request->getGet('length');
		$search = $this->request->getGet('search')['value'] ?? '';
		$order = $this->request->getGet('order')[0] ?? null;

		try {
			// Query peraturan utama dengan pagination dan search
			$builder = $this->loadModel('model')->builder();

			// Join dengan tabel jenis peraturan untuk mendapatkan nama_jenis
			$builder->select('p.*, jp.nama_jenis')
				->from('web_peraturan p')
				->join('web_jenis_peraturan jp', 'jp.id_jenis_peraturan = p.id_jenis_dokumen', 'left');

			// Total records (tanpa filter)
			$totalRecords = $this->loadModel('model')->countAllResults();

			// Apply search filter
			if (!empty($search)) {
				$builder->groupStart()
					->like('p.judul', $search)
					->orLike('p.nomor', $search)
					->orLike('p.tahun', $search)
					->orLike('jp.nama_jenis', $search)
					->groupEnd();
			}

			// Get total filtered records
			$totalFiltered = $builder->countAllResults(false);

			// Apply ordering
			if ($order && isset($order['column']) && isset($order['dir'])) {
				$columns = ['p.judul', 'jp.nama_jenis', 'p.nomor', 'p.tahun', 'p.created_at']; // Sesuaikan dengan kolom
				if (isset($columns[$order['column']])) {
					$builder->orderBy($columns[$order['column']], $order['dir']);
				}
			} else {
				$builder->orderBy('p.created_at', 'DESC');
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
					'judul' => esc($row['judul']),
					'jenis_peraturan' => esc($row['nama_jenis'] ?? 'Tidak ada jenis'),
					'nomor' => esc($row['nomor']),
					'tahun' => esc($row['tahun']),
					'file_dokumen' => $this->formatFileLink($row['file_dokumen'], $row['file_dokumen']),
					'ignore_action' => $this->generatePeraturanActionButtons($row['id_peraturan'])
				];
			}

			// Return JSON response
			$output = [
				'draw' => $draw,
				'recordsTotal' => $totalRecords,
				'recordsFiltered' => $totalFiltered,
				'data' => $data
			];

			// Debug logging
			log_message('debug', 'Data_peraturan::getDataDT() response: ' . json_encode([
				'totalRecords' => $totalRecords,
				'filteredRecords' => $totalFiltered,
				'dataCount' => count($data)
			]));

			return $this->response->setJSON($output);
		} catch (\Exception $e) {
			log_message('error', 'Peraturan DataTable Error: ' . $e->getMessage());
			return $this->response->setJSON([
				'error' => 'Terjadi kesalahan saat memuat data peraturan'
			]);
		}
	}

	/**
	 * Format file link untuk display
	 */
	private function formatFileLink($filename, $originalName)
	{
		if (empty($filename)) {
			return '<span class="text-muted">Tidak ada file</span>';
		}

		$file_url = base_url('uploads/lampiran/' . esc($filename, 'url'));
		$file_icon = $this->getFileIcon($filename);

		return '<a href="' . $file_url . '" target="_blank" class="btn btn-sm btn-outline-primary">
			<i class="fa ' . $file_icon . '"></i> ' . esc($originalName ?: $filename) . '
		</a>';
	}

	/**
	 * Get file icon berdasarkan extension
	 */
	private function getFileIcon($filename)
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		switch ($extension) {
			case 'pdf':
				return 'fa-file-pdf';
			case 'doc':
			case 'docx':
				return 'fa-file-word';
			case 'jpg':
			case 'jpeg':
			case 'png':
				return 'fa-file-image';
			default:
				return 'fa-file';
		}
	}

	/**
	 * Generate action buttons untuk peraturan
	 */
	private function generatePeraturanActionButtons($id_peraturan)
	{
		$action_buttons = '<div class="btn-group" role="group">';

		// Edit button
		$action_buttons .= '<a href="' . base_url('data-peraturan/edit?id=' . $id_peraturan) . '" class="btn btn-primary btn-xs" title="Edit"><i class="fa fa-edit"></i></a>';

		// Relasi button
		$action_buttons .= '<a href="' . base_url('data-peraturan/relasi_peraturan?id=' . $id_peraturan) . '" class="btn btn-info btn-xs" title="Relasi"><i class="fa fa-link"></i></a>';

		// Lampiran button
		$action_buttons .= '<a href="' . base_url('data-peraturan/lampiran?id=' . $id_peraturan) . '" class="btn btn-secondary btn-xs" title="Lampiran"><i class="fa fa-paperclip"></i></a>';

		// Delete button
		$action_buttons .= '<form method="post" action="' . base_url('data_peraturan/delete') . '" style="display:inline;">
			' . csrf_field() . '
			<input type="hidden" name="id" value="' . $id_peraturan . '">
			<button type="submit" class="btn btn-danger btn-xs" 
				onclick="return confirm(\'Apakah Anda yakin ingin menghapus peraturan ini?\')" title="Hapus">
				<i class="fa fa-trash"></i>
			</button>
		</form>';

		$action_buttons .= '</div>';

		return $action_buttons;
	}

	/**
	 * DataTable endpoint untuk relasi peraturan
	 */
	public function getRelasiDataDT()
	{
		// Validate AJAX session with retry mechanism
		$retryCount = 0;
		$maxRetries = 3;

		while ($retryCount < $maxRetries) {
			if ($this->validateAjaxSession()) {
				break;
			}

			$retryCount++;
			if ($retryCount < $maxRetries) {
				// Wait a bit before retry
				usleep(100000); // 0.1 second
				// Refresh session
				$this->session->regenerate();
			}
		}

		if ($retryCount >= $maxRetries) {
			log_message('error', 'Data_peraturan::getRelasiDataDT() - Session validation failed after ' . $maxRetries . ' retries');
			return $this->ajaxResponse([
				'error' => 'Session validation failed. Please refresh the page.',
				'data' => [],
				'recordsTotal' => 0,
				'recordsFiltered' => 0
			], 401);
		}

		// Permission check - gunakan read permission, bukan update
		if (!$this->hasPermission('read') && !$this->hasPermission('read_all') && !$this->hasPermission('read_own')) {
			log_message('error', 'Data_peraturan::getRelasiDataDT() - User has no read permission');
			return $this->ajaxResponse([
				'error' => 'Access denied: No read permission',
				'data' => [],
				'recordsTotal' => 0,
				'recordsFiltered' => 0
			], 403);
		}

		// Validasi request
		if (!$this->request->isAJAX()) {
			return $this->ajaxResponse(['error' => 'Invalid request'], 400);
		}

		// Ambil parameter DataTable
		$draw = $this->request->getGet('draw');
		$start = $this->request->getGet('start');
		$length = $this->request->getGet('length');
		$search = $this->request->getGet('search')['value'] ?? '';
		$order = $this->request->getGet('order')[0] ?? null;
		$id_peraturan = $this->request->getGet('id_peraturan');

		// Validasi id_peraturan
		if (empty($id_peraturan)) {
			return $this->response->setJSON(['error' => 'ID peraturan tidak valid']);
		}

		try {
			// Get peraturan sumber untuk display
			$peraturan_sumber = $this->loadModel('model')->getPeraturanDetail($id_peraturan);
			if (!$peraturan_sumber) {
				return $this->response->setJSON(['error' => 'Peraturan sumber tidak ditemukan']);
			}

			// Format judul peraturan sumber
			$judul_sumber = esc($peraturan_sumber['nama_jenis']) . ' No. ' .
				esc($peraturan_sumber['nomor']) . ' Tahun ' .
				esc($peraturan_sumber['tahun']) . ' tentang ' .
				esc($peraturan_sumber['judul']);

			// Query relasi dengan pagination dan search
			$builder = $this->db->table('web_peraturan_relasi r')
				->select('r.*, p.nomor, p.tahun, p.judul as judul_peraturan_terkait, jp.nama_jenis as nama_jenis_peraturan, jr.nama_jenis as nama_jenis_relasi, jr.auto_update_status')
				->join('web_peraturan p', 'p.id_peraturan = r.id_peraturan_terkait')
				->join('web_jenis_peraturan jp', 'jp.id_jenis_peraturan = p.id_jenis_dokumen', 'left')
				->join('web_jenis_relasi jr', 'jr.id_jenis_relasi = r.id_jenis_relasi', 'left')
				->where('r.id_peraturan_sumber', $id_peraturan);

			// Total records (tanpa filter)
			$totalRecords = $builder->countAllResults(false);

			// Apply search filter
			if (!empty($search)) {
				$builder->groupStart()
					->like('p.judul', $search)
					->orLike('p.nomor', $search)
					->orLike('p.tahun', $search)
					->orLike('jr.nama_jenis', $search)
					->groupEnd();
			}

			// Get total filtered records
			$totalFiltered = $builder->countAllResults(false);

			// Apply ordering
			if ($order && isset($order['column']) && isset($order['dir'])) {
				$columns = ['p.judul', 'jr.nama_jenis', 'r.created_at']; // Sesuaikan dengan kolom
				if (isset($columns[$order['column']])) {
					$builder->orderBy($columns[$order['column']], $order['dir']);
				}
			} else {
				$builder->orderBy('r.created_at', 'DESC');
			}

			// Apply pagination
			$builder->limit($length, $start);

			// Execute query
			$results = $builder->get()->getResultArray();

			// Format data untuk DataTable
			$data = [];
			foreach ($results as $row) {
				// Format judul peraturan terkait
				$peraturan_terkait = esc($row['nama_jenis_peraturan']) . ' No. ' .
					esc($row['nomor']) . ' Tahun ' .
					esc($row['tahun']) . ' tentang ' .
					esc($row['judul_peraturan_terkait']);

				// Badge color berdasarkan jenis relasi
				$badge_class = $this->getRelasiBadgeClass($row['nama_jenis_relasi']);

				// Icon untuk auto-update
				$auto_update_icon = '';
				if (isset($row['auto_update_status']) && $row['auto_update_status']) {
					$auto_update_icon = ' <i class="fa fa-refresh text-warning" title="Auto-update status"></i>';
				}

				$data[] = [
					'ignore_no_urut' => '', // Akan diisi otomatis oleh DataTable
					'peraturan_sumber' => $judul_sumber,
					'peraturan_terkait' => $peraturan_terkait,
					'jenis_relasi' => '<span class="badge bg-' . $badge_class . '">' .
						esc($row['nama_jenis_relasi']) . '</span>' . $auto_update_icon,
					'ignore_action' => $this->generateRelasiActionButtons($row['id_peraturan_sumber'], $row['id_peraturan_terkait'])
				];
			}

			return $this->response->setJSON([
				'draw' => (int)$draw,
				'recordsTotal' => $totalRecords,
				'recordsFiltered' => $totalFiltered,
				'data' => $data
			]);
		} catch (\Exception $e) {
			log_message('error', 'Relasi DataTable Error: ' . $e->getMessage());
			return $this->response->setJSON([
				'error' => 'Terjadi kesalahan saat memuat data relasi'
			]);
		}
	}

	/**
	 * Get badge class berdasarkan jenis relasi
	 */
	private function getRelasiBadgeClass($jenis_relasi)
	{
		return match (strtolower($jenis_relasi)) {
			'mengubah' => 'primary',
			'mengganti' => 'warning',
			'mencabut' => 'danger',
			'ditetapkan_oleh' => 'info',
			'melaksanakan' => 'success',
			'mengatur_lanjut' => 'secondary',
			default => 'light'
		};
	}

	/**
	 * Generate action buttons untuk relasi
	 */
	private function generateRelasiActionButtons($id_sumber, $id_terkait)
	{
		return '<a href="' . base_url('data_peraturan/delete_relasi?id_sumber=' .
			esc($id_sumber, 'url') . '&id_terkait=' . esc($id_terkait, 'url')) .
			'" class="btn btn-danger btn-sm btn-delete-relasi" 
			data-confirm="Anda yakin ingin menghapus relasi ini? Status peraturan terkait akan dikembalikan jika ada perubahan otomatis." 
			title="Hapus Relasi">
			<i class="fa fa-trash"></i>
		</a>';
	}

	/**
	 * Lazy loading untuk models
	 */
	private function loadModel($modelName)
	{
		switch ($modelName) {
			case 'model':
				if (!$this->model) {
					$this->model = new WebPeraturanModel;
				}
				return $this->model;
			case 'relasiModel':
				if (!$this->relasiModel) {
					$this->relasiModel = new WebPeraturanRelasiModel;
				}
				return $this->relasiModel;
			case 'lampiranModel':
				if (!$this->lampiranModel) {
					$this->lampiranModel = new WebLampiranModel;
				}
				return $this->lampiranModel;
			case 'tagModel':
				if (!$this->tagModel) {
					$this->tagModel = new WebTagModel;
				}
				return $this->tagModel;
			case 'peraturanTagModel':
				if (!$this->peraturanTagModel) {
					$this->peraturanTagModel = new WebPeraturanTagModel;
				}
				return $this->peraturanTagModel;
			case 'statusDokumenModel':
				if (!$this->statusDokumenModel) {
					$this->statusDokumenModel = new StatusDokumenModel();
				}
				return $this->statusDokumenModel;
			case 'jenisRelasiModel':
				if (!$this->jenisRelasiModel) {
					$this->jenisRelasiModel = new \App\Models\WebJenisRelasiModel();
				}
				return $this->jenisRelasiModel;
			case 'statusLogModel':
				if (!$this->statusLogModel) {
					$this->statusLogModel = new \App\Models\WebPeraturanStatusLogModel();
				}
				return $this->statusLogModel;
			case 'instansiModel':
				if (!$this->instansiModel) {
					$this->instansiModel = new InstansiModel();
				}
				return $this->instansiModel;
			default:
				throw new \Exception('Unknown model: ' . $modelName);
		}
	}

	public function getLampiranDataDT()
	{
		try {
			// Pastikan tidak ada output sebelum JSON
			ob_clean();

			// Validasi AJAX request
			if (!$this->request->isAJAX()) {
				return $this->response->setJSON([
					'error' => 'Invalid request method'
				]);
			}

			// Ambil parameter DataTable server-side
			$draw = (int)($this->request->getGet('draw') ?? 1);
			$start = (int)($this->request->getGet('start') ?? 0);
			$length = (int)($this->request->getGet('length') ?? 10);
			$search = $this->request->getGet('search')['value'] ?? '';
			$order = $this->request->getGet('order')[0] ?? null;
			$id_peraturan = $this->request->getGet('id_peraturan');

			if (!$id_peraturan) {
				return $this->response->setJSON([
					'draw' => $draw,
					'recordsTotal' => 0,
					'recordsFiltered' => 0,
					'data' => [],
					'error' => 'ID peraturan tidak ditemukan'
				]);
			}

			// Query dengan pagination dan search
			$builder = $this->loadModel('lampiranModel')->builder();
			$builder->where('id_peraturan', $id_peraturan);

			// Total records (tanpa filter) - hanya untuk id_peraturan ini
			$totalRecords = $this->loadModel('lampiranModel')->where('id_peraturan', $id_peraturan)->countAllResults();

			// Apply search filter
			if (!empty($search)) {
				$builder->groupStart()
					->like('judul_lampiran', $search)
					->orLike('original_name', $search)
					->groupEnd();
			}

			// Get total filtered records
			$totalFiltered = $builder->countAllResults(false);

			// Apply ordering
			if ($order && isset($order['column']) && isset($order['dir'])) {
				$columns = ['judul_lampiran', 'original_name', 'created_at']; // Sesuaikan dengan kolom
				if (isset($columns[$order['column']])) {
					$builder->orderBy($columns[$order['column']], $order['dir']);
				}
			} else {
				$builder->orderBy('urutan', 'ASC')->orderBy('created_at', 'DESC');
			}

			// Apply pagination
			$builder->limit($length, $start);

			// Execute query
			$results = $builder->get()->getResultArray();

			// Filter ekstra: hanya id_peraturan yang diminta
			$filtered = [];
			foreach ($results as $row) {
				if ($row['id_peraturan'] == $id_peraturan) {
					$filtered[] = $row;
				}
			}
			log_message('debug', 'Lampiran Query - id_peraturan: ' . $id_peraturan . ', total: ' . count($filtered));

			// Format data untuk DataTable
			$data = [];
			$no = $start + 1;
			foreach ($filtered as $row) {
				// Buat HTML content dengan link download yang benar
				$file_link = '<a href="' . base_url('peraturan/download_lampiran/' . $row['id_lampiran']) . '" target="_blank" class="btn btn-sm btn-outline-primary">
					<i class="fa fa-download"></i> Download
				</a>';
				$action_form = '<form method="post" action="' . base_url('data_peraturan/delete_lampiran') . '" style="display:inline">'
					. csrf_field()
					. '<input type="hidden" name="id_lampiran" value="' . esc($row['id_lampiran']) . '">'
					. '<input type="hidden" name="id_peraturan" value="' . esc($id_peraturan) . '">'
					. '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Hapus lampiran ini?\')">Hapus</button>'
					. '</form>';

				$data[] = [
					'ignore_no_urut' => $no++,
					'judul_lampiran' => esc($row['judul_lampiran']),
					'file_lampiran' => base64_encode($file_link),
					'ignore_action' => base64_encode($action_form)
				];
			}

			// Format response untuk DataTable server-side
			$response = [
				'draw' => $draw,
				'recordsTotal' => $totalRecords,
				'recordsFiltered' => $totalFiltered,
				'data' => $data
			];

			// Set proper headers untuk JSON response
			$this->response->setHeader('Content-Type', 'application/json; charset=utf-8');
			$this->response->setHeader('Cache-Control', 'no-cache, must-revalidate');
			$this->response->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');

			return $this->response->setJSON($response);
		} catch (\Exception $e) {
			log_message('error', 'Lampiran DataTable Error: ' . $e->getMessage());

			// Set proper headers untuk JSON response
			$this->response->setHeader('Content-Type', 'application/json; charset=utf-8');

			return $this->response->setJSON([
				'draw' => $draw ?? 1,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => [],
				'error' => 'Terjadi kesalahan saat memuat data lampiran: ' . $e->getMessage()
			]);
		}
	}
}
