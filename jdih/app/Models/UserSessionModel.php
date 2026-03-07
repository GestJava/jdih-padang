<?php

namespace App\Models;

use CodeIgniter\Model;

class UserSessionModel extends Model
{
    protected $table = 'user_session';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_user',
        'session_id',
        'user_agent',
        'ip_address',
        'device_info',
        'is_active',
        'created_at',
        'last_activity',
        'expires_at'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    /**
     * Membuat session baru dan menginvalidasi session lama
     */
    public function createUserSession($userId, $sessionId, $userAgent = null, $ipAddress = null)
    {
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours')); // Sesuai dengan session expiration

        // Dapatkan device info dari user agent
        $deviceInfo = $this->parseDeviceInfo($userAgent);

        // Mulai transaction
        $this->db->transStart();

        try {
            // 1. Non-aktifkan semua session lama user ini
            $this->where('id_user', $userId)
                ->where('is_active', 1)
                ->set(['is_active' => 0])
                ->update();

            // 2. Hapus session yang sudah expired
            $this->where('expires_at <', $now)
                ->delete();

            // 3. Buat session baru
            $sessionData = [
                'id_user' => $userId,
                'session_id' => $sessionId,
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'device_info' => $deviceInfo,
                'is_active' => 1,
                'created_at' => $now,
                'last_activity' => $now,
                'expires_at' => $expiresAt
            ];

            $this->insert($sessionData);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Failed to create user session for user ID: ' . $userId);
                return false;
            }

            log_message('info', "New session created for user ID {$userId}, old sessions invalidated");
            return true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error creating user session: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update aktivitas session terakhir
     */
    public function updateLastActivity($sessionId)
    {
        $now = date('Y-m-d H:i:s');

        try {
            $this->where('session_id', $sessionId)
                ->where('is_active', 1)
                ->set(['last_activity' => $now])
                ->update();

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error updating session activity: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validasi apakah session masih valid
     */
    public function validateUserSession($userId, $sessionId)
    {
        $session = $this->where('id_user', $userId)
            ->where('session_id', $sessionId)
            ->where('is_active', 1)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        return $session !== null;
    }

    /**
     * Invalidate session tertentu
     */
    public function invalidateSession($sessionId)
    {
        try {
            $this->where('session_id', $sessionId)
                ->set(['is_active' => 0])
                ->update();

            log_message('info', 'Session invalidated: ' . $sessionId);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error invalidating session: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate semua session user
     */
    public function invalidateAllUserSessions($userId)
    {
        try {
            $this->where('id_user', $userId)
                ->set(['is_active' => 0])
                ->update();

            log_message('info', 'All sessions invalidated for user ID: ' . $userId);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error invalidating all user sessions: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cleanup expired sessions
     */
    public function cleanupExpiredSessions()
    {
        try {
            $deletedCount = $this->where('expires_at <', date('Y-m-d H:i:s'))
                ->delete();

            if ($deletedCount > 0) {
                log_message('info', "Cleaned up {$deletedCount} expired sessions");
            }

            return $deletedCount;
        } catch (\Exception $e) {
            log_message('error', 'Error cleaning up expired sessions: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Dapatkan semua session aktif user
     */
    public function getUserActiveSessions($userId)
    {
        return $this->where('id_user', $userId)
            ->where('is_active', 1)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('last_activity', 'DESC')
            ->findAll();
    }

    /**
     * Parse device info dari user agent
     */
    public function parseDeviceInfo($userAgent)
    {
        if (!$userAgent) return 'Unknown Device';

        $device = 'Desktop';
        $browser = 'Unknown Browser';
        $os = 'Unknown OS';

        // Detect mobile devices
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            $device = 'Mobile';
        }

        // Detect browser
        if (preg_match('/Chrome/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            $browser = 'Edge';
        }

        // Detect OS
        if (preg_match('/Windows/', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iOS/', $userAgent)) {
            $os = 'iOS';
        }

        return "{$device} - {$browser} on {$os}";
    }

    /**
     * Get session statistics untuk dashboard admin
     */
    public function getSessionStatistics()
    {
        $now = date('Y-m-d H:i:s');

        $stats = [
            'total_active_sessions' => $this->where('is_active', 1)
                ->where('expires_at >', $now)
                ->countAllResults(),
            'total_users_online' => $this->select('DISTINCT id_user')
                ->where('is_active', 1)
                ->where('expires_at >', $now)
                ->countAllResults(),
            'sessions_today' => $this->where('created_at >=', date('Y-m-d 00:00:00'))
                ->countAllResults(),
            'expired_sessions' => $this->where('expires_at <', $now)
                ->countAllResults()
        ];

        return $stats;
    }
}
