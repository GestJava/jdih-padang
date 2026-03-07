<?php

/**
 * Rate Limiting Helper
 * 
 * Helper untuk mengelola rate limiting di aplikasi JDIH
 * Developed by: Agus Salim
 * Website: https://jdih.padang.go.id
 * Year: 2025
 */

/**
 * Check rate limit untuk user tertentu
 * 
 * @param string $username Username yang akan dicek
 * @param string $action Jenis aksi (default: 'login')
 * @return bool True jika masih dalam batas, False jika sudah melebihi limit
 */
function check_rate_limit(string $username, string $action = 'login'): bool
{
    $session = \Config\Services::session();
    $rateLimitConfig = config('App')->rateLimit;

    if (!$rateLimitConfig['enable']) {
        return true;
    }

    $attempts = $session->get($action . '_attempts_' . $username) ?? 0;
    $lastAttempt = $session->get($action . '_last_attempt_' . $username) ?? 0;
    $lockoutTime = $rateLimitConfig['lockout_time'];
    $maxAttempts = $rateLimitConfig['max_attempts'];

    // Reset attempts jika sudah melewati lockout time
    if (time() - $lastAttempt > $lockoutTime) {
        $session->remove($action . '_attempts_' . $username);
        $session->remove($action . '_last_attempt_' . $username);
        $attempts = 0;
    }

    return $attempts < $maxAttempts;
}

/**
 * Increment rate limit counter untuk user tertentu
 * 
 * @param string $username Username yang akan di-increment
 * @param string $action Jenis aksi (default: 'login')
 */
function increment_rate_limit(string $username, string $action = 'login'): void
{
    $session = \Config\Services::session();
    $rateLimitConfig = config('App')->rateLimit;

    if (!$rateLimitConfig['enable']) {
        return;
    }

    $attempts = $session->get($action . '_attempts_' . $username) ?? 0;
    $attempts++;

    $session->set($action . '_attempts_' . $username, $attempts);
    $session->set($action . '_last_attempt_' . $username, time());

    // Log jika diaktifkan
    if ($rateLimitConfig['log_attempts']) {
        $request = \Config\Services::request();
        log_message('warning', sprintf(
            'Rate limit increment for %s: user=%s, attempts=%d, IP=%s',
            $action,
            $username,
            $attempts,
            $request->getIPAddress()
        ));
    }
}

/**
 * Reset rate limit counter untuk user tertentu
 * 
 * @param string $username Username yang akan di-reset
 * @param string $action Jenis aksi (default: 'login')
 */
function reset_rate_limit(string $username, string $action = 'login'): void
{
    $session = \Config\Services::session();
    $rateLimitConfig = config('App')->rateLimit;

    if (!$rateLimitConfig['enable']) {
        return;
    }

    $session->remove($action . '_attempts_' . $username);
    $session->remove($action . '_last_attempt_' . $username);

    // Log jika diaktifkan
    if ($rateLimitConfig['log_attempts']) {
        log_message('info', sprintf(
            'Rate limit reset for %s: user=%s',
            $action,
            $username
        ));
    }
}

/**
 * Get remaining attempts untuk user tertentu
 * 
 * @param string $username Username yang akan dicek
 * @param string $action Jenis aksi (default: 'login')
 * @return int Sisa percobaan yang tersedia
 */
function get_remaining_attempts(string $username, string $action = 'login'): int
{
    $session = \Config\Services::session();
    $rateLimitConfig = config('App')->rateLimit;

    if (!$rateLimitConfig['enable']) {
        return $rateLimitConfig['max_attempts'];
    }

    $attempts = $session->get($action . '_attempts_' . $username) ?? 0;
    $lastAttempt = $session->get($action . '_last_attempt_' . $username) ?? 0;
    $lockoutTime = $rateLimitConfig['lockout_time'];
    $maxAttempts = $rateLimitConfig['max_attempts'];

    // Reset attempts jika sudah melewati lockout time
    if (time() - $lastAttempt > $lockoutTime) {
        return $maxAttempts;
    }

    return max(0, $maxAttempts - $attempts);
}

/**
 * Get lockout time remaining untuk user tertentu
 * 
 * @param string $username Username yang akan dicek
 * @param string $action Jenis aksi (default: 'login')
 * @return int Sisa waktu lockout dalam detik
 */
function get_lockout_time_remaining(string $username, string $action = 'login'): int
{
    $session = \Config\Services::session();
    $rateLimitConfig = config('App')->rateLimit;

    if (!$rateLimitConfig['enable']) {
        return 0;
    }

    $lastAttempt = $session->get($action . '_last_attempt_' . $username) ?? 0;
    $lockoutTime = $rateLimitConfig['lockout_time'];

    $remaining = $lockoutTime - (time() - $lastAttempt);
    return max(0, $remaining);
}
