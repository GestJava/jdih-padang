<?php

/**
 *	App Name	: Admin Template Codeigniter 4
 *	Author		: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2020-2023
 */

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Auth;
use Config\App;
use App\Models\BaseModel;
use CodeIgniter\HTTP\Response;

class BaseController extends Controller
{
	protected $data;
	protected $config;
	protected $session;
	protected $router;

	/**
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;
	protected $isLoggedIn;
	protected $auth;
	protected $user;
	protected $model;
	protected $themeMode;

	public $currentModule;
	private $controllerName;
	private $methodName;
	// protected $actionUser;
	protected $moduleURL;
	// protected $moduleRole;
	protected $modulePermission;
	protected $userPermission;

	/*
	Alur:
	Sistem akan menegcek router.
	Default router adalah login (app\Config\Routes.php) $routes->get('/', 'Login::index');
	Sistem akan mengecek app\Filters\Bootstrap
	Selanjutnya kaan dieksekusi fungsi $this->loginRestricted();
	Kemudian sistem akan mengakses controller login
	Pada controller login dieksekusi fungsi $this->mustNotLoggedIn(); 
	-- Jika user telah login maka method $this->mustNotLoggedIn(); akan mengarahkan user ke halaman default yang di assign pada user tersebut
	
	// Menghindari looping redirect gunakan
	if ($this->isLoggedIn && $this->currentModule['nama_module'] != 'login') {
	
	}
	*/
	public function __construct()
	{
		date_default_timezone_set('Asia/Jakarta');

		$this->config = new App;
		$this->auth = new Auth;
		$this->model = new BaseModel;

		// Autoload util helpers (app\Helpers\Autoload.php)
		if ($this->config->csrf['enable']) {
			helper('csrf');
			csrf_settoken();
		}

		$this->session = \Config\Services::session();
		$this->request = \Config\Services::request();

		helper(['util', 'notification']);
		$web = $this->session->get('web');

		// Fix: Check if session web exists and has nama_module
		if (!$web || !isset($web['nama_module'])) {
			// Default to controller name if session not set
			$controllerName = strtolower(class_basename(get_class($this)));
			$methodName = 'index'; // Default method

			// Try to get current method name from router
			try {
				$router = service('router');
				$methodName = $router->methodName() ?: 'index';
			} catch (\Exception $e) {
				$methodName = 'index';
			}

			$web = [
				'nama_module' => $controllerName,
				'module_url' => base_url($controllerName),
				'method_name' => $methodName
			];
			$this->session->set('web', $web);
		}

		// Ensure method_name exists in session web
		if (!isset($web['method_name'])) {
			try {
				$router = service('router');
				$web['method_name'] = $router->methodName() ?: 'index';
				$this->session->set('web', $web);
			} catch (\Exception $e) {
				$web['method_name'] = 'index';
				$this->session->set('web', $web);
			}
		}

		$nama_module = $web['nama_module'];
		$module = $this->model->getModule($nama_module);

		if (!$module) {
			$this->data['content'] = 'Module ' . $nama_module . ' tidak ditemukan di database';
			$this->exitError($this->data);
		}

		$this->isLoggedIn = $this->session->get('logged_in');
		$this->currentModule = $module;
		$this->moduleURL = $web['module_url'];
		$this->user = $this->session->get('user');
		$this->model->checkRememberme();

		$this->data['current_module'] = $this->currentModule;
		$this->data['config'] = $this->config;
		$this->data['request'] = $this->request;
		$this->data['isloggedin'] = $this->isLoggedIn;
		$this->data['session'] = $this->session;
		$this->data['site_title'] = 'JDIH Kota Padang';
		$this->data['site_desc'] = 'Jaringan Dokumentasi dan Informasi Hukum Kota Padang';
		$this->data['settingAplikasi'] = $this->model->getSettingAplikasi();
		$this->data['user'] = [];
		$this->data['auth'] = $this->auth;
		$this->data['scripts'] = [];
		$this->data['styles'] = [];
		$this->data['module_url'] = $this->moduleURL;

		if (!empty($_COOKIE['jwd_adm_theme'])) {
			$this->themeMode = $this->data['theme_mode'] = $_COOKIE['jwd_adm_theme'];
		} else {
			$this->themeMode = $this->data['theme_mode'] = 'light';
		}

		if ($this->isLoggedIn) {
			$user_setting = $this->model->getUserSetting();

			if ($user_setting) {
				$this->data['app_layout'] = json_decode($user_setting->param, true);
			}
		} else {
			$query = $this->model->getAppLayoutSetting();
			foreach ($query as $val) {
				$app_layout[$val['param']] = $val['value'];
			}
			$this->data['app_layout'] = $app_layout;
		}

		// Login? Yes, No, Restrict
		if ($this->currentModule['login'] == 'Y' && $nama_module != 'login') {
			$this->loginRequired();
		} else if ($this->currentModule['login'] == 'R') {
			$this->loginRestricted();
		}

		// KEAMANAN: Set no-cache headers untuk semua halaman admin/login
		// Mencegah browser/CDN cache halaman yang mengandung session
		// Ini penting untuk mencegah user tidak berhak mengakses halaman admin tanpa login
		if ($nama_module == 'login' || $this->isLoggedIn) {
			$response = service('response');
			$response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
			$response->setHeader('Pragma', 'no-cache');
			$response->setHeader('Expires', '0');
			$response->setHeader('X-Cache-Status', 'DISABLED');
			$response->setHeader('X-Accel-Expires', '0'); // Nginx specific
			$response->setHeader('X-Content-Type-Options', 'nosniff');
		}

		if ($this->isLoggedIn) {
			// KEAMANAN: Pastikan user data selalu fresh dari session
			// Jangan gunakan $this->user yang mungkin ter-cache, ambil langsung dari session
			$freshUser = $this->session->get('user');
			if ($freshUser && is_array($freshUser) && !empty($freshUser['id_user'])) {
				$this->user = $freshUser; // Update property juga
				$this->data['user'] = $freshUser;
			} else {
				// Jika session user tidak valid, reset isLoggedIn
				$this->isLoggedIn = false;
				$this->data['isloggedin'] = false;
				$this->data['user'] = [];
			}

			// List action assigned to role
			$this->data['action_user'] = $this->userPermission;
			$this->data['menu'] = $this->model->getMenu($this->currentModule['nama_module']);

			$this->data['breadcrumb'] = ['Home' => $this->config->baseURL, $this->currentModule['judul_module'] => $this->moduleURL];
			$this->data['module_role'] = $this->model->getDefaultUserModule();

			$this->getModulePermission();
			$this->getListPermission();

			$result = $this->model->getAllModulePermission($_SESSION['user']['id_user']);
			$all_module_permission = [];
			if ($result) {
				foreach ($result as $val) {
					$all_module_permission[$val['id_module']][$val['nama_permission']] = $val;
				}
			}
			$_SESSION['user']['all_permission'] = $all_module_permission;

			// Get Notifications
			$this->data['notifications'] = get_user_notifications($this->user);

			// Check Global Role Action
			$this->checkRoleAction();
			if ($nama_module == 'login') {
				$this->redirectOnLoggedIn();
			}
		}

		if ($module['id_module_status'] != 1) {
			$this->printError('Module ' . $module['judul_module'] . ' sedang ' . strtolower($module['nama_status']));
			exit();
		}
	}

	private function getModulePermission()
	{
		$query = $this->model->getModulePermission($this->currentModule['id_module']);

		$this->modulePermission = [];
		foreach ($query as $val) {
			$nama_permission = $val['nama_permission'] ?: 'null';
			$this->modulePermission[$val['id_role']][$nama_permission] = $nama_permission;
		}
	}

	private function getListPermission()
	{
		$user_role = $this->session->get('user')['role'];

		if ($this->isLoggedIn && $this->currentModule['nama_module'] != 'login') {
			$current_user = $this->model->getUserById($this->user['id_user']);
			if ($current_user['status'] != 'active') {
				$this->data['content'] = 'Status akun Anda ' . ucfirst($current_user['status']);
				$this->exitError($this->data);
			}
			if (!$user_role) {
				$this->printError('User belum memiliki role');
				exit;
			}

			// Dashboard harus bisa diakses semua role karena sangat informatif
			// Dashboard menampilkan data global untuk semua role
			if ($this->currentModule['nama_module'] == 'dashboard') {
				$this->userPermission = ['read'];
				$session_user = $this->session->get('user');
				$session_user['permission'] = $this->userPermission;
				$this->session->set('user', $session_user);
				return;
			}

			if ($this->modulePermission) {
				$error = false;
				if ($this->currentModule['nama_module'] != 'login') {

					$role_exists = false;
					foreach ($user_role as $id_role => $val) {
						if (key_exists($id_role, $this->modulePermission)) {
							$this->userPermission = $this->modulePermission[$id_role];
							unset($this->userPermission['null']);
							$role_exists = true;
							break;
						}
					}

					if ($this->userPermission) {
						$session_user = $this->session->get('user');
						$session_user['permission'] = $this->userPermission;
						$this->session->set('user', $session_user);
					}

					if ($role_exists) {
						if (!$this->userPermission) {
							$error = 'Role Anda tidak memiliki permission pada module ' . $this->currentModule['judul_module'];
						}
					} else {
						$error = 'Anda tidak berhak mengakses halaman ini';
					}

					if ($error) {
						$this->printError($error);
						exit();
					}
				}
			} else {
				$this->printError('Role untuk module ini belum diatur');
				exit();
			}
		}
	}

	private function setCurrentModule($module)
	{
		$this->currentModule['nama_module'] = $module;
	}

	protected function getControllerName()
	{
		return $this->controllerName;
	}

	protected function getMethodName()
	{
		return $this->methodName;
	}

	protected function addStyle($file)
	{
		$this->data['styles'][] = $file;
	}

	protected function addJs($file, $print = false)
	{
		if ($print) {
			$this->data['scripts'][] = ['print' => true, 'script' => $file];
		} else {
			$this->data['scripts'][] = $file;
		}
	}

	protected function exitError($data)
	{
		echo view('app_error.php', $data);
		exit;
	}

	protected function view($file, $data = false, $file_only = false)
	{
		// Add cache control headers to prevent caching issues
		// CRITICAL: Prevent nginx/proxy cache untuk semua halaman admin
		$response = service('response');
		$response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
		$response->setHeader('Pragma', 'no-cache');
		$response->setHeader('Expires', '0');
		$response->setHeader('X-Cache-Status', 'DISABLED');

		// Validate session integrity with retry mechanism
		if ($this->isLoggedIn) {
			// Retry mechanism untuk session validation
			$retryCount = 0;
			$maxRetries = 3;

			while ($retryCount < $maxRetries) {
				// Refresh session data
				$this->session->regenerate();

				// Check if user data exists
				$user = $this->session->get('user');
				if ($user && isset($user['id_user'])) {
					$this->user = $user;
					break;
				}

				$retryCount++;
				if ($retryCount < $maxRetries) {
					// Wait a bit before retry
					usleep(100000); // 0.1 second
				}
			}

			// If still no valid user after retries, destroy session
			if (!$this->user || !isset($this->user['id_user'])) {
				log_message('error', 'Session validation failed after ' . $maxRetries . ' retries');
				$this->session->destroy();
				header('Location: ' . $this->config->baseURL . 'login');
				exit();
			}
		}

		// Merge base data with passed data
		$viewData = is_array($data) ? array_merge($this->data, $data) : $this->data;

		if (is_array($file)) {
			foreach ($file as $file_item) {
				echo view($file_item, $viewData);
			}
		} else {
			echo view('themes/modern/header.php', $viewData);
			echo view('themes/modern/' . $file, $viewData);
			echo view('themes/modern/footer.php', $viewData);
		}
	}

	protected function loginRequired()
	{
		if (!$this->isLoggedIn) {
			header('Location: ' . $this->config->baseURL . 'login');
			// redirect()->to($this->config->baseURL . 'login');
			exit();
		}
	}

	protected function loginRestricted()
	{
		if ($this->isLoggedIn) {
			if ($this->methodName !== 'logout') {
				header('Location: ' . $this->config->baseURL);
			}
		}
	}

	protected function redirectOnLoggedIn()
	{
		if ($this->isLoggedIn) {

			header('Location: ' . $this->config->baseURL . $this->user['default_module']['nama_module']);
			// header('Location: ' . $this->config->baseURL . $this->user['default_module']['nama_module']);
			// redirect($this->router->default_controller);
		}
	}

	/* Redirect User setelah login */
	protected function mustNotLoggedIn()
	{
		if ($this->isLoggedIn) {
			if ($this->currentModule['nama_module'] == 'login') {

				$redirect_url = '';
				if ($this->user['default_page_type'] == 'url') {
					$redirect_url = str_replace('{{BASE_URL}}', $this->config->baseURL, $this->user['default_page_url']);
				} else if ($this->user['default_page_type'] == 'id_module') {
					$redirect_url = $this->config->baseURL . $this->user['default_module']['nama_module'];
				} else {
					$redirect_url = $this->config->baseURL . $this->user['role'][$this->user['default_page_id_role']]['nama_module'];
				}
				// header('Location: ' . $this->config->baseURL . $this->data['module_role']->nama_module);
				header('Location: ' . $redirect_url);
				exit();
			}
		}
	}

	protected function mustLoggedIn()
	{
		if (!$this->isLoggedIn) {
			header('Location: ' . $this->config->baseURL . 'login');
			exit();
		}
	}

	private function checkRoleAction()
	{

		if ($this->config->checkRoleAction['enable_global']) {
			$method = $this->session->get('web')['method_name'];
			$list_action = ['add' => 'create', 'edit' => 'update'];
			$list_error = ['add' => 'menambah', 'edit' => 'mengubah'];

			$error = false;
			if ($method == 'add' || $method == 'edit') {
				if (key_exists($method, $list_action)) {

					foreach ($this->userPermission as $val) {
						$exp = explode('_', $val);
						$exists = false;

						if ($list_action[$method] == trim($exp[0])) {;
							$exists = true;
							break;
						}
					}
					if (!$exists) {
						$error = 'Role Anda tidak memiliki permission untuk ' . $list_error[$method] . ' data module ' . $this->currentModule['judul_module'];
					}
				}
			} else if (!empty($_POST['delete'])) {
				foreach ($this->userPermission as $val) {
					$exp = explode('_', $val);
					$exists = false;
					if (trim($exp[0]) == 'delete') {
						$exists = true;
						break;
					}
				}

				if (!$exists) {
					$error = 'Role Anda tidak diperkenankan untuk menghapus data';
				}
			}

			if ($error) {
				$this->data['msg'] = ['status' => 'error', 'message' => $error];
				$this->view('error.php', $this->data);
				exit;
			}
		}
	}

	protected function userCan($action)
	{
		if (!$this->userPermission) {
			return '';
		}

		foreach ($this->userPermission as $val) {

			$exp = explode('_', $val);
			if (count($exp) == 1) {
				if (trim($exp[0]) == trim($action)) {
					return true;
				}
			} else {

				if ($exp[0] == $action) {
					if ($exp[1] == 'all') {
						return 'all';
					} else if ($exp[1] == 'own') {
						return 'own';
					}
				}
			}
		}
		return '';
	}

	protected function mustHavePermission($permission)
	{

		if (!in_array($permission, $this->userPermission)) {
			$response = service('response');
			$response->setStatusCode(Response::HTTP_UNAUTHORIZED);
			$response->setJSON(['status' => 'error', 'message' => 'Akses ditolak: Anda tidak memiliki permission ' . $permission]);
			$response->setHeader('Content-type', 'application/json');
			$response->noCache();
			$response->send();
			exit;
		}
	}

	protected function hasPermission($action, $exit = false)
	{
		if (!in_array($action, $this->userPermission)) {
			if ($exit) {
				$this->data['msg'] = ['status' => 'error', 'message' => 'Anda tidak memiliki permission ' . $action];
				$this->view('error.php', $this->data);
				exit;
			}
		}
		return in_array($action, $this->userPermission);
	}

	protected function hasPermissionPrefix($action, $return = false)
	{

		$has_permission = false;

		foreach ($this->userPermission as $val) {
			$exp = explode('_', $val);
			$user_action = trim($exp[0]);
			if ($user_action == $action || $user_action == $action . '_all') {
				$has_permission = true;
				break;
			}
		}

		if (!$has_permission && $return = false) {

			$action_title = ['read' => 'melihat data', 'create' => 'menambah data', 'update' => 'mengubah data', 'delete' => 'menghapus data'];
			$this->currentModule['nama_module'] = 'error';
			$this->data['msg'] = ['status' => 'error', 'message' => 'Role Anda tidak diperkenankan untuk pada ' . $action_title[$action]];
			$this->view('error.php', $this->data);
			exit;
		}

		return $has_permission;
	}

	public function whereOwn($column = null)
	{
		/* if (!$column)
			$column = $this->config->checkRoleAction['field'];
			
		if ($this->actionUser['read_data'] == 'own') {
			return ' WHERE ' . $column . ' = ' . $_SESSION['user']['id_user'];
		}
		
		return ' WHERE 1 = 1 '; */

		if (!$column)
			$column = $this->config->checkRoleAction['field'];

		if (key_exists('read_own', $this->userPermission) && !key_exists('read_all', $this->userPermission)) {
			return ' WHERE ' . $column . ' = ' . $_SESSION['user']['id_user'];
		}

		return ' WHERE 1 = 1 ';
	}

	protected function printError($message)
	{
		$this->data['title'] = 'Error...';
		if (is_string($message)) {
			$message = ['status' => 'error', 'message' => $message];
		}
		$this->data['msg'] = $message;
		$this->view('error.php', $this->data);
	}

	/* Used for modules when edited data not found */
	protected function errorDataNotFound($addData = null)
	{
		$data = $this->data;
		$data['title'] = 'Error';
		$data['msg']['status'] = 'error';
		$data['msg']['content'] = 'Data tidak ditemukan';

		if ($addData) {
			$data = array_merge($data, $addData);
		}
		$this->view('error-data-notfound.php', $data);
	}

	protected function validateAjaxSession()
	{
		// Check if user is logged in
		if (!$this->isLoggedIn) {
			log_message('error', 'AJAX Session validation failed: User not logged in');
			return false;
		}

		// Check if user data exists and is valid
		$user = $this->session->get('user');
		if (!$user || !isset($user['id_user'])) {
			log_message('error', 'AJAX Session validation failed: Invalid user data');
			return false;
		}

		// Check if permissions are loaded
		if (!$this->userPermission || empty($this->userPermission)) {
			log_message('error', 'AJAX Session validation failed: No permissions loaded');
			return false;
		}

		return true;
	}

	protected function ajaxResponse($data, $status = 200)
	{
		$response = service('response');
		$response->setStatusCode($status);
		$response->setJSON($data);
		$response->setHeader('Content-type', 'application/json');
		$response->noCache();
		return $response;
	}
}
