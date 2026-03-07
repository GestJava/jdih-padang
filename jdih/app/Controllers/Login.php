<?php

/**
 *	App Name	: Admin Template Codeigniter 4	
 *	Author		: Agus Prawoto Hadi
 *	Website		: https://jagowebdev.com
 *	Year		: 2020-2023
 */

namespace App\Controllers;

use App\Models\Builtin\LoginModel;
use \Config\App;
use App\Libraries\Auth;

class Login extends \App\Controllers\BaseController
{
	protected $loginModel;

	public function __construct()
	{
		parent::__construct();
		$this->loginModel = new LoginModel;
		$this->data['site_title'] = 'Login ke akun Anda';

		helper(['cookie', 'form', 'csrf']);
	}

	public function index()
	{
		// KEAMANAN: Set no-cache headers untuk mencegah browser/CDN cache halaman login
		// Ini penting untuk memastikan session tidak ter-cache dan user selalu melihat halaman login terbaru
		$response = service('response');
		$response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
		$response->setHeader('Pragma', 'no-cache');
		$response->setHeader('Expires', '0');
		$response->setHeader('X-Cache-Status', 'DISABLED');
		$response->setHeader('X-Accel-Expires', '0'); // Nginx specific
		$response->setHeader('X-Content-Type-Options', 'nosniff');
		
		$this->mustNotLoggedIn();
		$this->data['status'] = '';
		
		// Cek flashdata untuk pesan logout sukses atau pesan lainnya
		$flashMessage = $this->session->getFlashdata('message');
		if ($flashMessage) {
			$this->data['status'] = $flashMessage['status'] ?? '';
			$this->data['message'] = $flashMessage['message'] ?? '';
		}

		if ($this->request->getPost('password')) {
			if ($this->processLogin() === true) {
				return $this->redirectAfterLogin();
			}
		}

		$this->loadRegistrationSettings();
		csrf_settoken();
		
		// Generate Math CAPTCHA challenge untuk MFA
		$this->generateMathChallenge();
		
		$this->data['style'] = ' style="max-width:375px"';
		return view('themes/modern/builtin/login', $this->data);
	}

	private function processLogin(): bool
	{
		try {
			if (!$this->validateLoginInput()) {
				return false;
			}

			$username = $this->request->getPost('username');
			$password = $this->request->getPost('password');

			log_message('debug', 'Login attempt for user: ' . $username);

			if (!$this->authenticateUser($username, $password)) {
				return false;
			}

			$this->setUserSession($username);
			$this->handleRememberMe();

			log_message('info', 'User logged in successfully: ' . $username);
			return true;
		} catch (\Exception $e) {
			log_message('error', 'Login error: ' . $e->getMessage());
			$this->data['status'] = 'error';
			$this->data['message'] = 'Terjadi kesalahan sistem saat proses login. Silakan coba lagi nanti.';
			return false;
		}
	}

	private function validateLoginInput(): bool
	{
		$validation_message = csrf_validation();
		if ($validation_message) {
			$this->data['status'] = 'error';
			$this->data['message'] = $validation_message['message'];
			log_message('error', 'CSRF validation failed: ' . $validation_message['message']);
			return false;
		}
		
		// Validasi Math CAPTCHA MFA
		if (!$this->validateMathChallenge()) {
			$this->data['status'] = 'error';
			$this->data['message'] = 'Verifikasi keamanan gagal. Silakan jawab pertanyaan matematika dengan benar.';
			log_message('warning', 'Math CAPTCHA verification failed from IP: ' . $this->request->getIPAddress());
			// Regenerate challenge untuk percobaan berikutnya
			$this->generateMathChallenge();
			return false;
		}
		
		return true;
	}

	/**
	 * Check rate limiting untuk mencegah brute force attack
	 * @param string $username
	 * @return bool
	 */
	private function checkRateLimit(string $username): bool
	{
		// Feature flag untuk disable rate limiting jika diperlukan
		$rateLimitConfig = config('App')->rateLimit;
		if (!$rateLimitConfig['enable']) {
			return true;
		}

		$attempts = $this->session->get('login_attempts_' . $username) ?? 0;
		$lastAttempt = $this->session->get('last_attempt_' . $username) ?? 0;
		$lockoutTime = $rateLimitConfig['lockout_time'];
		$maxAttempts = $rateLimitConfig['max_attempts'];

		// Reset attempts jika sudah melewati lockout time
		if (time() - $lastAttempt > $lockoutTime) {
			$this->session->remove('login_attempts_' . $username);
			$this->session->remove('last_attempt_' . $username);
			$attempts = 0;
		}

		// Cek apakah sudah melebihi batas percobaan
		if ($attempts >= $maxAttempts) {
			$remainingTime = $lockoutTime - (time() - $lastAttempt);
			$remainingMinutes = ceil($remainingTime / 60);

			$this->data['status'] = 'error';
			$this->data['message'] = 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $remainingMinutes . ' menit.';

			log_message('warning', 'Rate limit exceeded for user: ' . $username . ' from IP: ' . $this->request->getIPAddress());
			return false;
		}

		return true;
	}

	/**
	 * Log failed login attempt dan update rate limiting counter
	 * @param string $username
	 */
	private function logFailedAttempt(string $username): void
	{
		$attempts = $this->session->get('login_attempts_' . $username) ?? 0;
		$attempts++;

		$this->session->set('login_attempts_' . $username, $attempts);
		$this->session->set('last_attempt_' . $username, time());

		// Log ke file dengan informasi detail
		log_message('warning', sprintf(
			'Failed login attempt %d for user: %s from IP: %s, User-Agent: %s',
			$attempts,
			$username,
			$this->request->getIPAddress(),
			$this->request->getUserAgent()->getAgentString()
		));
	}

	/**
	 * Reset rate limiting counter setelah login berhasil
	 * @param string $username
	 */
	private function resetRateLimit(string $username): void
	{
		$this->session->remove('login_attempts_' . $username);
		$this->session->remove('last_attempt_' . $username);

		log_message('info', 'Rate limit reset for successful login: ' . $username);
	}

	private function authenticateUser(string $username, string $password): bool
	{
		// TAMBAHAN: Rate limiting check sebelum authentication
		if (!$this->checkRateLimit($username)) {
			return false;
		}

		$user = $this->loginModel->checkUser($username);

		if (!$user || !password_verify($password, $user['password'])) {
			// TAMBAHAN: Log failed attempt dan update rate limiting
			$this->logFailedAttempt($username);

			log_message('warning', 'Invalid credentials for user: ' . $username);
			$this->data['status'] = 'error';
			$this->data['message'] = 'Username dan/atau Password tidak cocok';
			return false;
		}

		if ($user['verified'] == 0) {
			// TAMBAHAN: Log failed attempt untuk unverified user
			$this->logFailedAttempt($username);

			log_message('warning', 'User not verified: ' . $username);
			$this->data['status'] = 'error';
			$this->data['message'] = 'Akun Anda belum diverifikasi. Silakan cek email Anda.';
			return false;
		}

		if ($user['status'] != 'active') {
			// TAMBAHAN: Log failed attempt untuk inactive user
			$this->logFailedAttempt($username);

			log_message('warning', 'User not active: ' . $username . ' - Status: ' . $user['status']);
			$this->data['status'] = 'error';
			$this->data['message'] = 'Status akun Anda ' . ucfirst($user['status']) . '. Silakan hubungi administrator.';
			return false;
		}

		// TAMBAHAN: Reset rate limiting setelah login berhasil
		$this->resetRateLimit($username);

		$this->data['user'] = $user;
		return true;
	}

	private function setUserSession(string $username): void
	{
		// CRITICAL: Regenerate session ID setelah login untuk mencegah session fixation
		$this->session->regenerate(true); // true = destroy old session
		
		$this->session->set('user', $this->data['user']);
		$this->session->set('logged_in', true);
		$this->loginModel->recordLogin();
	}

	private function handleRememberMe(): void
	{
		if ($this->request->getPost('remember')) {
			$this->loginModel->setUserToken($this->data['user']);
		}
	}

	private function redirectAfterLogin()
	{
		$user = $this->session->get('user');
		$redirect_url = 'harmonisasi'; // Default redirect

		if (
			!empty($user['default_page_type']) &&
			$user['default_page_type'] == 'id_module' &&
			!empty($user['default_module']['nama_module'])
		) {
			$redirect_url = $user['default_module']['nama_module'];
		}

		return redirect()->to($redirect_url);
	}

	private function loadRegistrationSettings(): void
	{
		$query = $this->loginModel->getSettingRegistrasi();
		foreach ($query as $val) {
			$this->data['setting_registrasi'][$val['param']] = $val['value'];
		}
	}

	/**
	 * Generate Math CAPTCHA challenge untuk MFA
	 * Menyimpan soal dan jawaban di session
	 */
	private function generateMathChallenge(): void
	{
		// Generate random math question (penjumlahan atau pengurangan)
		$operation = rand(0, 1); // 0 = addition, 1 = subtraction
		
		if ($operation === 0) {
			// Penjumlahan: angka antara 1-20
			$num1 = rand(1, 20);
			$num2 = rand(1, 20);
			$answer = $num1 + $num2;
			$question = "$num1 + $num2";
		} else {
			// Pengurangan: pastikan hasil positif
			$num1 = rand(10, 30);
			$num2 = rand(1, min(10, $num1 - 1));
			$answer = $num1 - $num2;
			$question = "$num1 - $num2";
		}
		
		// Generate unique challenge token
		$challengeToken = bin2hex(random_bytes(16));
		
		// Simpan di session dengan expiry 5 menit
		$this->session->set('math_captcha', [
			'token' => $challengeToken,
			'question' => $question,
			'answer' => $answer,
			'created_at' => time(),
			'expires_at' => time() + 300 // 5 menit
		]);
		
		// Pass challenge data ke view
		$this->data['math_captcha'] = [
			'token' => $challengeToken,
			'question' => $question
		];
	}

	/**
	 * Validasi Math CAPTCHA challenge dari user
	 * @return bool
	 */
	private function validateMathChallenge(): bool
	{
		$challengeData = $this->session->get('math_captcha');
		
		// Cek apakah challenge ada dan belum expired
		if (!$challengeData || time() > $challengeData['expires_at']) {
			log_message('warning', 'Math CAPTCHA challenge expired or not found');
			return false;
		}
		
		// Cek token challenge
		$submittedToken = $this->request->getPost('math_captcha_token');
		if ($submittedToken !== $challengeData['token']) {
			log_message('warning', 'Math CAPTCHA token mismatch');
			return false;
		}
		
		// Cek jawaban
		$submittedAnswer = (int) $this->request->getPost('math_captcha_answer');
		$correctAnswer = (int) $challengeData['answer'];
		
		if ($submittedAnswer !== $correctAnswer) {
			log_message('debug', sprintf(
				'Math CAPTCHA answer mismatch: submitted=%d, correct=%d, question=%s',
				$submittedAnswer,
				$correctAnswer,
				$challengeData['question']
			));
			return false;
		}
		
		// Hapus challenge setelah validasi berhasil
		$this->session->remove('math_captcha');
		
		return true;
	}

	public function refreshLoginData()
	{
		$user_session = $this->session->get('user');
		if (!$user_session || empty($user_session['email'])) {
			log_message('error', 'Attempted to refresh login data without a valid session.');
			return;
		}

		$result = $this->loginModel->checkUser($user_session['email']);
		if ($result) {
			$this->session->set('user', $result);
		}
	}

	public function logout()
	{
		// KEAMANAN: Set no-cache headers untuk mencegah browser/CDN cache response logout
		$this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate, private');
		$this->response->setHeader('Pragma', 'no-cache');
		$this->response->setHeader('Expires', '0');
		
		$user = $this->session->get('user');
		$sessionConfig = config('Session');
		$appConfig = config('App');
		
		// Fallback ke App config jika Session config tidak ada
		$cookieName = $sessionConfig->cookieName ?? $appConfig->sessionCookieName ?? 'ci_session';
		
		// Ambil session ID dari cookie sebelum destroy
		$sessionId = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] : session_id();
		
		if ($user && !empty($user['id_user'])) {
			$this->loginModel->deleteAuthCookie($user['id_user']);
		}
		
		// Log logout untuk audit (SEBELUM menghapus data)
		if ($user && !empty($user['id_user'])) {
			log_message('info', 'User logged out: ' . ($user['email'] ?? $user['username'] ?? 'unknown') . ' (ID: ' . $user['id_user'] . ')');
		}
		
		// Hapus semua data session user
		$this->session->remove(['user', 'logged_in', 'login_time', 'login_ip', 'math_captcha']);
		
		// CRITICAL: Set flashdata SEBELUM regenerate/destroy
		// Flashdata akan tetap tersimpan setelah regenerate
		$flashMessage = ['status' => 'success', 'message' => 'Anda telah berhasil logout. Terima kasih!'];
		$this->session->setFlashdata('message', $flashMessage);
		
		// Regenerate session ID dengan true untuk membuat session baru yang bersih
		// Ini akan membuat session ID baru tapi tetap mempertahankan flashdata
		// Regenerate HARUS dilakukan SEBELUM destroy, karena setelah destroy session tidak aktif
		$this->session->regenerate(true);
		
		// Setelah regenerate, pastikan data user benar-benar dihapus dari session baru
		$this->session->remove(['user', 'logged_in', 'login_time', 'login_ip', 'math_captcha']);
		
		// Hapus cookie session lama di browser
		$cookiePath = '/';
		$cookieDomain = '';
		$cookieSecure = $sessionConfig->secure ?? $appConfig->cookieSecure ?? false;
		$cookieHttpOnly = $sessionConfig->httpOnly ?? $appConfig->cookieHTTPOnly ?? false;
		
		// Hapus semua cookie session yang mungkin tersisa
		$cookiesToDelete = [$cookieName, 'remember', 'ci_session'];
		foreach ($cookiesToDelete as $cookie) {
			if (isset($_COOKIE[$cookie])) {
				setcookie($cookie, '', time() - 3600, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly);
				unset($_COOKIE[$cookie]);
			}
		}
		
		// Hapus file session lama secara eksplisit (jika menggunakan file handler)
		// Perbaikan: Tambahkan null check untuk $sessionConfig
		$sessionDriver = $sessionConfig->driver ?? $appConfig->sessionDriver ?? \CodeIgniter\Session\Handlers\FileHandler::class;
		if ($sessionDriver === \CodeIgniter\Session\Handlers\FileHandler::class && $sessionId) {
			$sessionPath = $sessionConfig->savePath ?? $appConfig->sessionSavePath ?? WRITEPATH . 'session';
			$sessionFile = $sessionPath . '/ci_session' . $sessionId;
			if (file_exists($sessionFile)) {
				@unlink($sessionFile);
			}
		}
		
		// Redirect ke login tanpa query string
		// Flashdata akan tersedia di request berikutnya (login page)
		return redirect()->to('login');
	}
}
