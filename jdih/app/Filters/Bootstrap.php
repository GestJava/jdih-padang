<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Bootstrap implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		$config = config('App');

		helper('csrf');

		// Custom CSRF with comprehensive AJAX bypass
		if ($config->csrf['enable']) {
			if ($config->csrf['auto_check']) {
				// Get current path for route-based exclusion
				$currentPath = $request->getUri()->getPath();

				// Define AJAX endpoints that should skip CSRF validation
				$ajaxEndpoints = [
					'ajax_list',
					'ajaxGet',
					'ajaxSearch',
					'ajax_',
					'_ajax'
				];

				$skipCSRF = false;

				// Check if current path contains any AJAX endpoint pattern
				foreach ($ajaxEndpoints as $endpoint) {
					if (strpos($currentPath, $endpoint) !== false) {
						$skipCSRF = true;
						break;
					}
				}

				// Check for AJAX headers as additional detection
				$ajaxHeaders = [
					$request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest',
					strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false,
					$request->getMethod() === 'POST' && (
						strpos($currentPath, 'data_peraturan') !== false ||
						strpos($currentPath, 'ajax') !== false
					)
				];

				if (!$skipCSRF) {
					$skipCSRF = in_array(true, $ajaxHeaders);
				}

				// Only run CSRF validation if not an AJAX request
				if (!$skipCSRF) {
					$message = csrf_validation();
					if ($message) {
						echo view('app_error.php', ['content' => $message['message']]);
						exit;
					}
				}
			}

			if ($config->csrf['auto_settoken']) {
				csrf_settoken();
			}
		}

		$router = service('router');
		$controller  = $router->controllerName();

		$exp  = explode('\\', $controller);

		$nama_module =  'welcome';
		foreach ($exp as $key => $val) {
			if (!$val || strtolower($val) == 'app' || strtolower($val) == 'controllers')
				unset($exp[$key]);
		}

		// Dash tidak valid untuk nama class, sehingga jika ada dash di url maka otomatis akan diubah menjadi underscore, hal tersebut berpengaruh ke nama controller
		$nama_module = str_replace('_', '-', strtolower(join('/', $exp)));
		$module_url = $config->baseURL . $nama_module;

		session()->set('web', ['module_url' => $module_url, 'nama_module' => $nama_module, 'method_name' => $router->methodName()]);
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// CRITICAL: Set no-cache headers untuk semua halaman admin
		// Mencegah browser/CDN/nginx cache halaman yang mengandung session
		$uri = $request->getUri();
		$path = $uri->getPath();
		
		// Daftar path admin yang TIDAK BOLEH di-cache
		$adminPaths = ['/dashboard', '/login', '/logout', '/harmonisasi', '/legalisasi', 
			'/data_peraturan', '/data-peraturan', '/verifikasi', '/validasi', '/finalisasi',
			'/penugasan', '/hasil', '/filepicker', '/builtin'];
		
		$isAdminPath = false;
		foreach ($adminPaths as $adminPath) {
			if (strpos($path, $adminPath) === 0) {
				$isAdminPath = true;
				break;
			}
		}
		
		// Jika ini halaman admin atau ada session cookie, set no-cache headers
		if ($isAdminPath || ($request->getHeaderLine('Cookie') && strpos($request->getHeaderLine('Cookie'), 'ci_session') !== false)) {
			$response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
			$response->setHeader('Pragma', 'no-cache');
			$response->setHeader('Expires', '0');
			$response->setHeader('X-Accel-Expires', '0'); // Nginx specific
			$response->setHeader('X-Cache-Status', 'DISABLED');
			$response->setHeader('Vary', 'Cookie, Authorization'); // Vary header untuk cache
		}
		
		return $response;
	}
}
