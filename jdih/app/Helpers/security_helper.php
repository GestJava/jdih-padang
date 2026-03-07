<?php

/**
 * Security Helper untuk JDIH
 * Berisi fungsi-fungsi keamanan yang sering digunakan
 */

if (!function_exists('set_security_headers')) {
    /**
     * Set security headers untuk response
     */
    function set_security_headers()
    {
        $response = service('response');

        // Security Headers
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy
        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://www.youtube.com https://s.ytimg.com; " .
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
            "img-src 'self' data: https: *.google.com www.gstatic.com https://www.youtube.com https://i.ytimg.com https://yt3.ggpht.com; " .
            "connect-src 'self' https://www.youtube.com https://s.ytimg.com; " .
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; " .
            "frame-ancestors 'self'; " .
            "media-src 'self' https://www.youtube.com https://s.ytimg.com;";

        $response->setHeader('Content-Security-Policy', $csp);
    }
}

if (!function_exists('sanitize_xss')) {
    /**
     * Sanitasi input untuk mencegah XSS
     */
    function sanitize_xss($input, $allowed_tags = [])
    {
        if (is_array($input)) {
            return array_map('sanitize_xss', $input);
        }

        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Allow specific tags if needed
        if (!empty($allowed_tags)) {
            $allowed_tags_str = implode('|', $allowed_tags);
            $pattern = '/&lt;(\/?)(' . $allowed_tags_str . ')([^&]*?)&gt;/i';
            $input = preg_replace($pattern, '<$1$2$3>', $input);
        }

        return $input;
    }
}

if (!function_exists('validate_file_upload')) {
    /**
     * Validasi file upload yang aman
     */
    function validate_file_upload($file, $allowed_types = [], $max_size = 26214400)
    {
        $errors = [];

        // Check if file was uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'File tidak valid';
            return $errors;
        }

        // Check file size
        if ($file['size'] > $max_size) {
            $errors[] = 'Ukuran file terlalu besar (max: ' . format_bytes($max_size) . ')';
        }

        // Check file type
        if (!empty($allowed_types)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_types)) {
                $errors[] = 'Tipe file tidak diizinkan';
            }
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dangerous_extensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'sh'];

        if (in_array($extension, $dangerous_extensions)) {
            $errors[] = 'Ekstensi file berbahaya tidak diizinkan';
        }

        return $errors;
    }
}

if (!function_exists('format_bytes')) {
    /**
     * Format bytes ke human readable
     */
    function format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename untuk keamanan
     */
    function sanitize_filename($filename)
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);

        // Remove leading/trailing dots
        $filename = trim($filename, '.');

        // Limit length
        if (strlen($filename) > 255) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 255 - strlen($extension) - 1) . '.' . $extension;
        }

        return $filename;
    }
}

if (!function_exists('generate_secure_token')) {
    /**
     * Generate secure token untuk CSRF atau API
     */
    function generate_secure_token($length = 32)
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } else {
            // Fallback (less secure)
            $token = '';
            for ($i = 0; $i < $length; $i++) {
                $token .= chr(mt_rand(0, 255));
            }
            return bin2hex($token);
        }
    }
}

if (!function_exists('log_security_event')) {
    /**
     * Log security events untuk monitoring
     */
    function log_security_event($event_type, $details, $severity = 'medium')
    {
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'event_type' => $event_type,
            'details' => $details,
            'severity' => $severity,
            'url' => current_url(),
            'user_id' => session()->get('user')['id_user'] ?? 'guest'
        ];

        $log_file = WRITEPATH . 'logs/security_' . date('Y-m-d') . '.log';
        $log_entry = json_encode($log_data) . PHP_EOL;

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // Alert untuk event kritis
        if ($severity === 'high' || $severity === 'critical') {
            // Bisa dikirim ke email admin atau sistem monitoring
            log_message('alert', 'SECURITY ALERT: ' . $event_type . ' - ' . $details);
        }
    }
}

if (!function_exists('detect_sql_injection')) {
    /**
     * Deteksi pola SQL injection
     */
    function detect_sql_injection($input)
    {
        $patterns = [
            '/\b(union|select|insert|update|delete|drop|create|alter)\b/i',
            '/[\'";]/',
            '/--/',
            '/\/\*.*\*\//',
            '/xp_cmdshell/i',
            '/exec\s*\(/i',
            '/sp_/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                log_security_event('sql_injection_attempt', $input, 'high');
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('detect_xss_attempt')) {
    /**
     * Deteksi pola XSS
     */
    function detect_xss_attempt($input)
    {
        $patterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/data:text\/html/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                log_security_event('xss_attempt', $input, 'high');
                return true;
            }
        }

        return false;
    }
}
