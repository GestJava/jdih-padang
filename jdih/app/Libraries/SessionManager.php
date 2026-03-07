<?php

namespace App\Libraries;

use App\Models\UserSessionModel;
use CodeIgniter\Session\Session;

class SessionManager
{
    protected $session;
    protected $userSessionModel;
    protected $config;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->userSessionModel = new UserSessionModel();
        $this->config = config('App');
    }

    /**
     * Buat session baru dengan concurrent login protection
     */
    public function createSecureSession($userId, $userData)
    {
        try {
            // Dapatkan session ID saat ini
            $sessionId = $this->session->session_id ?? session_id();

            // Dapatkan informasi browser dan IP
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;

            // Buat session di database dengan invalidasi session lama
            $result = $this->userSessionModel->createUserSession(
                $userId,
                $sessionId,
                $userAgent,
                $ipAddress
            );

            if ($result) {
                // Set session data
                $this->session->set([
                    'user' => $userData,
                    'logged_in' => true,
                    'session_secure_id' => $sessionId,
                    'login_time' => time(),
                    'device_info' => $this->userSessionModel->parseDeviceInfo($userAgent)
                ]);

                // Regenerate session ID untuk keamanan
                $this->session->regenerate(false);

                log_message('info', "Secure session created for user ID: {$userId}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error creating secure session: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validasi session setiap request
     */
    public function validateCurrentSession()
    {
        $user = $this->session->get('user');
        $sessionId = $this->session->get('session_secure_id') ?? session_id();

        if (!$user || !isset($user['id_user'])) {
            return false;
        }

        // Cek apakah session masih valid di database
        $isValid = $this->userSessionModel->validateUserSession(
            $user['id_user'],
            $sessionId
        );

        if ($isValid) {
            // Update last activity
            $this->userSessionModel->updateLastActivity($sessionId);
            return true;
        }

        // Session tidak valid, logout paksa
        $this->forceLogout('Session invalidated from another device');
        return false;
    }

    /**
     * Logout dengan menghapus session dari database
     */
    public function secureLogout($userId = null)
    {
        try {
            $user = $this->session->get('user');
            $sessionId = $this->session->get('session_secure_id') ?? session_id();

            // Jika tidak ada user ID, ambil dari session
            if (!$userId && $user) {
                $userId = $user['id_user'];
            }

            // Invalidate session di database
            if ($sessionId) {
                $this->userSessionModel->invalidateSession($sessionId);
            }

            // Clear session
            $this->session->remove(['user', 'logged_in', 'session_secure_id', 'login_time', 'device_info']);
            $this->session->destroy();

            // Clear remember cookie jika ada
            if (isset($_COOKIE['remember'])) {
                setcookie('remember', '', time() - 3600, '/');
            }

            log_message('info', "Secure logout completed for user ID: {$userId}");
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error during secure logout: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Force logout dengan pesan
     */
    public function forceLogout($reason = 'Session expired')
    {
        $this->secureLogout();

        // Set flash message
        $this->session->setFlashdata('logout_reason', $reason);

        return redirect()->to('/login');
    }

    /**
     * Invalidate semua session user (logout dari semua perangkat)
     */
    public function logoutFromAllDevices($userId)
    {
        try {
            $result = $this->userSessionModel->invalidateAllUserSessions($userId);

            if ($result) {
                log_message('info', "All sessions invalidated for user ID: {$userId}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error logging out from all devices: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan session aktif user untuk monitoring
     */
    public function getUserActiveSessions($userId)
    {
        return $this->userSessionModel->getUserActiveSessions($userId);
    }

    /**
     * Cleanup expired sessions (untuk di jalankan sebagai cronjob)
     */
    public function cleanupExpiredSessions()
    {
        return $this->userSessionModel->cleanupExpiredSessions();
    }

    /**
     * Cek apakah ada concurrent login
     */
    public function hasConcurrentLogins($userId)
    {
        $activeSessions = $this->userSessionModel->getUserActiveSessions($userId);
        return count($activeSessions) > 1;
    }

    /**
     * Dapatkan statistik session untuk admin dashboard
     */
    public function getSessionStatistics()
    {
        return $this->userSessionModel->getSessionStatistics();
    }

    /**
     * Update konfigurasi session timeout
     */
    public function updateSessionTimeout($userId, $timeout = 7200)
    {
        try {
            $newExpiresAt = date('Y-m-d H:i:s', time() + $timeout);

            $this->userSessionModel->where('id_user', $userId)
                ->where('is_active', 1)
                ->set(['expires_at' => $newExpiresAt])
                ->update();

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error updating session timeout: ' . $e->getMessage());
            return false;
        }
    }
}
