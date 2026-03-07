<?php

/**
 *	App Name	: Admin Template Codeigniter 4	
 *	Author		: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2020-2023
 */

namespace App\Controllers\Builtin;

use App\Models\Builtin\SettingAppModel;

class Setting_app extends \App\Controllers\BaseController
{
	public function __construct()
	{

		parent::__construct();
		// $this->mustLoggedIn();

		$this->model = new SettingAppModel;
		$this->data['site_title'] = 'Halaman Setting Web';

		$this->addJs(base_url('vendors/spectrum/spectrum.min.js?r=') . time());
		$this->addJs(base_url('themes/modern/js/setting-logo.js?r=') . time());
		$this->addJs(base_url('themes/modern/js/image-upload.js?r=') . time());
		$this->addStyle(base_url('vendors/spectrum/spectrum.css'));
		$this->addStyle(base_url('themes/modern/builtin/css/setting-app.css'));
		// $this->addStyle ( base_url('themes/modern/builtin/css/login-header.css'));

		helper(['cookie', 'form']);
	}

	public function index()
	{
		$data = $this->data;
		if ($this->request->getMethod() === 'post') {
			$form_errors = $this->validateForm();

			if ($form_errors) {
				$data['message'] = ['status' => 'error', 'message' => $form_errors];
			} else {


				if (!$this->hasPermission('update_all')) {
					$data['message'] = ['status' => 'error', 'message' => 'Role anda tidak diperbolehkan melakukan perubahan'];
				} else {
					$files = $this->request->getFiles();
					$result = $this->model->saveData($files);
					$data['message'] = ['status' => $result['status'], 'message' => $result['message']];
				}
			}
		}

		$query = $this->model->getSettingAplikasi();
		foreach ($query as $val) {
			$data[$val['param']] = $val['value'];
		}

		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];

		$this->view('builtin/setting-app-form.php', $data);
	}

	private function validateForm()
	{
		$validation =  \Config\Services::validation();
		$validation->setRule('footer_app', 'Footer Aplikasi', 'trim|required');
		$validation->setRule('background_logo', 'Background Logo', 'trim|required');
		$validation->setRule('judul_web', 'Judul Website', 'trim|required');
		$validation->setRule('deskripsi_web', 'Deskripsi Web', 'trim|required');

		// Aturan validasi untuk file upload
		$validation->setRules([
			'logo_login' => [
				'label' => 'Logo Login',
				'rules' => 'if_exist|uploaded[logo_login]|max_size[logo_login,300]|is_image[logo_login]|mime_in[logo_login,image/png,image/jpeg,image/jpg]',
				'errors' => [
					'max_size' => '{field} terlalu besar. Ukuran maksimal 300KB.',
					'is_image' => '{field} harus berupa gambar.',
					'mime_in' => '{field} harus berformat PNG, JPG, atau JPEG.'
				]
			],
			'logo_app' => [
				'label' => 'Logo Aplikasi',
				'rules' => 'if_exist|uploaded[logo_app]|max_size[logo_app,300]|is_image[logo_app]|mime_in[logo_app,image/png,image/jpeg,image/jpg]',
				'errors' => [
					'max_size' => '{field} terlalu besar. Ukuran maksimal 300KB.',
					'is_image' => '{field} harus berupa gambar.',
					'mime_in' => '{field} harus berformat PNG, JPG, atau JPEG.'
				]
			],
			'favicon' => [
				'label' => 'Favicon',
				'rules' => 'if_exist|uploaded[favicon]|max_size[favicon,300]|is_image[favicon]|mime_in[favicon,image/png]',
				'errors' => [
					'max_size' => '{field} terlalu besar. Ukuran maksimal 300KB.',
					'is_image' => '{field} harus berupa gambar.',
					'mime_in' => '{field} harus berformat PNG.'
				]
			]
		]);

		if (!$validation->withRequest($this->request)->run()) {
			return $validation->getErrors();
		}

		return false; // No errors
	}
}
