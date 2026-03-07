<?php

/**
 * URL Helper untuk konsistensi penulisan URL di seluruh aplikasi
 * Memudahkan maintenance dan perubahan struktur URL
 */

if (!function_exists('app_url')) {
    /**
     * Generate application URL dengan konsistensi
     * 
     * @param string $path Path relatif dari base URL
     * @param array $params Query parameters (optional)
     * @return string Complete URL
     */
    function app_url(string $path = '', array $params = []): string
    {
        $url = rtrim(base_url(), '/') . '/' . ltrim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('admin_url')) {
    /**
     * Generate URL untuk admin area
     * 
     * @param string $path Path relatif dari admin area
     * @param array $params Query parameters (optional)
     * @return string Admin URL
     */
    function admin_url(string $path = '', array $params = []): string
    {
        return app_url('admin/' . ltrim($path, '/'), $params);
    }
}

if (!function_exists('api_url')) {
    /**
     * Generate URL untuk API endpoints
     * 
     * @param string $path Path relatif dari API area
     * @param array $params Query parameters (optional)
     * @return string API URL
     */
    function api_url(string $path = '', array $params = []): string
    {
        return app_url('api/' . ltrim($path, '/'), $params);
    }
}

if (!function_exists('asset_url')) {
    /**
     * Generate URL untuk static assets
     * 
     * @param string $path Path relatif dari assets folder
     * @param bool $versioning Add version parameter for cache busting
     * @return string Asset URL
     */
    function asset_url(string $path, bool $versioning = false): string
    {
        $url = app_url('assets/' . ltrim($path, '/'));

        if ($versioning) {
            $fullPath = FCPATH . 'assets/' . ltrim($path, '/');
            $url .= '?v=' . (is_file($fullPath) ? filemtime($fullPath) : time());
        }

        return $url;
    }
}

if (!function_exists('vendor_url')) {
    /**
     * Generate URL untuk vendor assets (CSS/JS libraries)
     * 
     * @param string $path Path relatif dari vendors folder
     * @param bool $versioning Add version parameter for cache busting
     * @return string Vendor URL
     */
    function vendor_url(string $path, bool $versioning = false): string
    {
        $url = app_url('vendors/' . ltrim($path, '/'));

        if ($versioning) {
            $url .= '?v=' . time();
        }

        return $url;
    }
}

if (!function_exists('theme_asset_url')) {
    /**
     * Generate URL untuk theme assets (alternative to existing theme_url)
     * 
     * @param string $theme Theme name
     * @param string $path Path relatif dari theme folder
     * @param bool $versioning Add version parameter for cache busting
     * @return string Theme URL
     */
    function theme_asset_url(string $theme, string $path = '', bool $versioning = false): string
    {
        $url = app_url('themes/' . $theme . '/' . ltrim($path, '/'));

        if ($versioning) {
            $url .= '?v=' . time();
        }

        return $url;
    }
}

if (!function_exists('upload_url')) {
    /**
     * Generate URL untuk uploaded files
     * Auto-detect struktur folder (root atau subfolder jdih)
     * 
     * @param string $path Path relatif dari uploads folder
     * @return string Upload URL
     */
    function upload_url(string $path): string
    {
        $fileName = basename($path);
        $subPath = dirname($path);
        $subPath = ($subPath === '.' || $subPath === '') ? '' : $subPath . '/';
        
        // Cek beberapa kemungkinan path untuk menentukan URL yang benar
        // Prioritas: ROOTPATH (subfolder jdih) > FCPATH (root)
        $filePath2 = ROOTPATH . 'uploads/' . $subPath . $fileName;
        $filePath1 = FCPATH . 'uploads/' . $subPath . $fileName;
        
        // Jika file ada di subfolder jdih, gunakan URL dengan jdih/
        if (is_file($filePath2)) {
            return app_url('jdih/uploads/' . $subPath . $fileName);
        } elseif (is_file($filePath1)) {
            // File di root
            return app_url('uploads/' . $subPath . $fileName);
        } else {
            // Default: coba dengan jdih/ dulu (karena struktur umum)
            return app_url('jdih/uploads/' . ltrim($path, '/'));
        }
    }
}

if (!function_exists('image_url')) {
    /**
     * Generate URL untuk images
     * 
     * @param string $path Path relatif dari images folder
     * @return string Image URL
     */
    function image_url(string $path): string
    {
        return app_url('images/' . ltrim($path, '/'));
    }
}

if (!function_exists('file_url')) {
    /**
     * Generate URL untuk files
     * 
     * @param string $path Path relatif dari files folder
     * @return string File URL
     */
    function file_url(string $path): string
    {
        return app_url('files/' . ltrim($path, '/'));
    }
}

if (!function_exists('route_url')) {
    /**
     * Generate URL berdasarkan route name (jika menggunakan named routes)
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Route URL
     */
    function route_url(string $name, array $params = []): string
    {
        return route_to($name, ...$params);
    }
}

if (!function_exists('current_url_with_params')) {
    /**
     * Get current URL dengan tambahan/modifikasi parameters
     * 
     * @param array $params Parameters to add/modify
     * @param array $remove Parameters to remove
     * @return string Current URL with modified parameters
     */
    function current_url_with_params(array $params = [], array $remove = []): string
    {
        $currentParams = $_GET;

        // Remove specified parameters
        foreach ($remove as $key) {
            unset($currentParams[$key]);
        }

        // Add/modify parameters
        $currentParams = array_merge($currentParams, $params);

        $baseUrl = strtok(current_url(), '?');

        if (!empty($currentParams)) {
            $baseUrl .= '?' . http_build_query($currentParams);
        }

        return $baseUrl;
    }
}

if (!function_exists('pagination_url')) {
    /**
     * Generate URL untuk pagination
     * 
     * @param int $page Page number
     * @return string Pagination URL
     */
    function pagination_url(int $page): string
    {
        return current_url_with_params(['page' => $page]);
    }
}

if (!function_exists('search_url')) {
    /**
     * Generate URL untuk search dengan query
     * 
     * @param string $query Search query
     * @param array $filters Additional filters
     * @return string Search URL
     */
    function search_url(string $query, array $filters = []): string
    {
        $params = array_merge(['q' => $query], $filters);
        return app_url('search', $params);
    }
}

if (!function_exists('download_url')) {
    /**
     * Generate URL untuk download files
     * 
     * @param string $type File type (peraturan, dokumen, etc)
     * @param int $id File ID
     * @return string Download URL
     */
    function download_url(string $type, int $id): string
    {
        return app_url("download/{$type}/{$id}");
    }
}

if (!function_exists('preview_url')) {
    /**
     * Generate URL untuk preview files
     * 
     * @param string $type File type (peraturan, dokumen, etc)
     * @param int $id File ID
     * @return string Preview URL
     */
    function preview_url(string $type, int $id): string
    {
        return app_url("preview/{$type}/{$id}");
    }
}

if (!function_exists('redirect_back_url')) {
    /**
     * Generate URL untuk redirect back dengan fallback
     * 
     * @param string $fallback Fallback URL if no referrer
     * @return string Redirect URL
     */
    function redirect_back_url(string $fallback = ''): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (empty($referer) || !str_contains($referer, base_url())) {
            return $fallback ?: app_url();
        }

        return $referer;
    }
}

if (!function_exists('pattern_url')) {
    /**
     * Generate URL menggunakan pattern dari URLPatterns config
     * 
     * @param string $category Category pattern (frontend, admin, api, files, ajax)
     * @param string $key Pattern key
     * @param array $params Parameters untuk replace dalam pattern
     * @return string Generated URL
     */
    function pattern_url(string $category, string $key, array $params = []): string
    {
        $pattern = \Config\URLPatterns::get($category, $key, $params);
        return app_url($pattern);
    }
}

if (!function_exists('frontend_url')) {
    /**
     * Generate frontend URL menggunakan pattern
     * 
     * @param string $key Pattern key
     * @param array $params Parameters untuk replace dalam pattern
     * @return string Frontend URL
     */
    function frontend_url(string $key, array $params = []): string
    {
        return pattern_url('frontend', $key, $params);
    }
}

if (!function_exists('admin_pattern_url')) {
    /**
     * Generate admin URL menggunakan pattern
     * 
     * @param string $key Pattern key
     * @param array $params Parameters untuk replace dalam pattern
     * @return string Admin URL
     */
    function admin_pattern_url(string $key, array $params = []): string
    {
        return pattern_url('admin', $key, $params);
    }
}

if (!function_exists('api_pattern_url')) {
    /**
     * Generate API URL menggunakan pattern
     * 
     * @param string $key Pattern key
     * @param array $params Parameters untuk replace dalam pattern
     * @return string API URL
     */
    function api_pattern_url(string $key, array $params = []): string
    {
        return pattern_url('api', $key, $params);
    }
}

if (!function_exists('files_pattern_url')) {
    /**
     * Generate file operation URL menggunakan pattern
     * 
     * @param string $key Pattern key
     * @param array $params Parameters untuk replace dalam pattern
     * @return string File URL
     */
    function files_pattern_url(string $key, array $params = []): string
    {
        return pattern_url('files', $key, $params);
    }
}

if (!function_exists('ajax_pattern_url')) {
    /**
     * Generate AJAX URL menggunakan pattern
     * 
     * @param string $key Pattern key
     * @param array $params Parameters untuk replace dalam pattern
     * @return string AJAX URL
     */
    function ajax_pattern_url(string $key, array $params = []): string
    {
        return pattern_url('ajax', $key, $params);
    }
}

if (!function_exists('menu_url')) {
    /**
     * Generate URL untuk menu navigation
     * 
     * @param string $type Menu type (frontend/admin)
     * @param string $key Menu key
     * @param array $params Parameters
     * @return string Menu URL
     */
    function menu_url(string $type, string $key, array $params = []): string
    {
        return pattern_url($type, $key, $params);
    }
}

if (!function_exists('breadcrumb_url')) {
    /**
     * Generate URL untuk breadcrumb
     * 
     * @param string $type URL type
     * @param string $key URL key
     * @param array $params Parameters
     * @return string Breadcrumb URL
     */
    function breadcrumb_url(string $type, string $key, array $params = []): string
    {
        return pattern_url($type, $key, $params);
    }
}

if (!function_exists('form_action_url')) {
    /**
     * Generate URL untuk form action
     * 
     * @param string $action Action type (add, edit, delete, etc)
     * @param string $module Module name
     * @param array $params Parameters
     * @return string Form action URL
     */
    function form_action_url(string $action, string $module, array $params = []): string
    {
        $key = $module . '_' . $action;
        return pattern_url('admin', $key, $params);
    }
}

if (!function_exists('crud_url')) {
    /**
     * Generate CRUD operation URLs
     * 
     * @param string $module Module name
     * @param string $action CRUD action (list, add, edit, delete, view)
     * @param array $params Parameters
     * @return string CRUD URL
     */
    function crud_url(string $module, string $action = 'list', array $params = []): string
    {
        if ($action === 'list') {
            $key = $module;
        } else {
            $key = $module . '_' . $action;
        }

        return pattern_url('admin', $key, $params);
    }
}

if (!function_exists('nav_active_class')) {
    /**
     * Generate active class untuk navigation
     * 
     * @param string $currentUrl Current URL
     * @param string $menuUrl Menu URL
     * @param string $activeClass Active class name
     * @return string Active class or empty string
     */
    function nav_active_class(string $currentUrl, string $menuUrl, string $activeClass = 'active'): string
    {
        return (strpos($currentUrl, $menuUrl) !== false) ? $activeClass : '';
    }
}

if (!function_exists('is_current_url')) {
    /**
     * Check if current URL matches given pattern
     * 
     * @param string $pattern URL pattern to check
     * @return bool True if matches
     */
    function is_current_url(string $pattern): bool
    {
        $currentUrl = current_url();
        return strpos($currentUrl, $pattern) !== false;
    }
}

if (!function_exists('generate_slug_url')) {
    /**
     * Generate URL with slug
     * 
     * @param string $title Title to convert to slug
     * @param string $pattern URL pattern
     * @param array $params Additional parameters
     * @return string URL with slug
     */
    function generate_slug_url(string $title, string $pattern, array $params = []): string
    {
        $slug = url_title($title, '-', true);
        $params['slug'] = $slug;
        return app_url(str_replace('{slug}', $slug, $pattern));
    }
}

if (!function_exists('canonical_url')) {
    /**
     * Generate canonical URL untuk SEO
     * 
     * @param string $path Path relatif
     * @return string Canonical URL
     */
    function canonical_url(string $path = ''): string
    {
        return rtrim(base_url(), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('social_share_url')) {
    /**
     * Generate URL untuk social media sharing
     * 
     * @param string $platform Platform (facebook, twitter, linkedin, whatsapp)
     * @param string $url URL to share
     * @param string $title Title to share
     * @param string $description Description to share
     * @return string Social share URL
     */
    function social_share_url(string $platform, string $url, string $title = '', string $description = ''): string
    {
        $encodedUrl = urlencode($url);
        $encodedTitle = urlencode($title);
        $encodedDescription = urlencode($description);

        switch ($platform) {
            case 'facebook':
                return "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}";
            case 'twitter':
                return "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}";
            case 'linkedin':
                return "https://www.linkedin.com/sharing/share-offsite/?url={$encodedUrl}";
            case 'whatsapp':
                return "https://wa.me/?text={$encodedTitle}%20{$encodedUrl}";
            default:
                return $url;
        }
    }
}
