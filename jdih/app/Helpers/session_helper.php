<?php

/**
 * Session Helper Functions
 * 
 * Helper functions untuk mengakses session dengan aman di view
 * 
 * @author Agus Salim
 * @year 2025
 */

if (!function_exists('get_session_data')) {
    /**
     * Get session data safely
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    function get_session_data($key, $default = null)
    {
        try {
            $session = service('session');
            return $session->get($key, $default);
        } catch (Exception $e) {
            log_message('error', 'Session helper error: ' . $e->getMessage());
            return $default;
        }
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    function is_logged_in()
    {
        return get_session_data('logged_in', false);
    }
}

if (!function_exists('get_current_user')) {
    /**
     * Get current user data
     * 
     * @return array|null
     */
    function get_current_user()
    {
        return get_session_data('user', null);
    }
}

if (!function_exists('get_user_id')) {
    /**
     * Get current user ID
     * 
     * @return int|null
     */
    function get_user_id()
    {
        $user = get_current_user();
        return $user['id_user'] ?? null;
    }
}

if (!function_exists('get_user_name')) {
    /**
     * Get current user name
     * 
     * @return string|null
     */
    function get_user_name()
    {
        $user = get_current_user();
        return $user['nama'] ?? null;
    }
}

if (!function_exists('get_user_email')) {
    /**
     * Get current user email
     * 
     * @return string|null
     */
    function get_user_email()
    {
        $user = get_current_user();
        return $user['email'] ?? null;
    }
}

if (!function_exists('get_user_instansi')) {
    /**
     * Get current user instansi
     * 
     * @return string|null
     */
    function get_user_instansi()
    {
        $user = get_current_user();
        return $user['nama_instansi'] ?? null;
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if user has specific permission
     * 
     * @param string $permission Permission name
     * @return bool
     */
    function has_permission($permission)
    {
        $user = get_current_user();
        if (!$user) return false;

        // Check user permissions (implement based on your permission system)
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }
}

if (!function_exists('get_flash_message')) {
    /**
     * Get flash message safely
     * 
     * @param string $key Flash message key
     * @return mixed
     */
    function get_flash_message($key = 'message')
    {
        try {
            $session = service('session');
            return $session->getFlashdata($key);
        } catch (Exception $e) {
            log_message('error', 'Flash message helper error: ' . $e->getMessage());
            return null;
        }
    }
}
