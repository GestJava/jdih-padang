<?php

namespace Config;

/**
 * URL Patterns Configuration
 * Konfigurasi terpusat untuk pola URL yang konsisten
 */
class URLPatterns
{
    /**
     * Frontend URL Patterns
     */
    public const FRONTEND = [
        'home' => '',
        'about' => 'tentang',
        'contact' => 'kontak',
        'search' => 'cari',
        'statistics' => 'statistik',
        'agenda' => 'agenda',
        'agenda_detail' => 'agenda/{slug}',
        'news' => 'berita',
        'news_category' => 'berita/kategori/{slug}',
        'news_detail' => 'berita/{slug}',
        'regulations' => 'peraturan',
        'regulations_type' => 'peraturan/jenis/{slug}',
        'regulations_latest' => 'peraturan/terbaru',
        'regulations_popular' => 'peraturan/populer',
        'regulations_search' => 'peraturan/search',
        'regulations_detail' => 'peraturan/{slug}',
        'documents' => 'dokumen',
        'documents_category' => 'dokumen/kategori/{slug}',
        'documents_type' => 'dokumen/jenis/{slug}',
        'documents_tag' => 'dokumen/tag/{slug}',
        'guidance' => 'panduan',
        'privacy_policy' => 'kebijakan-privasi',
        'terms_conditions' => 'syarat-ketentuan',
        'chatbot' => 'chatbot',
        'organization_structure' => 'struktur-organisasi',
        'sop' => 'sop',
    ];

    /**
     * Admin URL Patterns
     */
    public const ADMIN = [
        'login' => 'login',
        'logout' => 'logout',
        'dashboard' => 'dashboard',
        'users' => 'admin/users',
        'user_add' => 'admin/users/add',
        'user_edit' => 'admin/users/edit/{id}',
        'user_delete' => 'admin/users/delete/{id}',
        'regulations' => 'data-peraturan',
        'regulation_add' => 'data-peraturan/add',
        'regulation_edit' => 'data-peraturan/edit',
        'regulation_delete' => 'data-peraturan/delete',
        'regulation_relations' => 'data-peraturan/relasi_peraturan',
        'regulation_attachments' => 'data-peraturan/lampiran',
        'harmonization' => 'harmonisasi',
        'harmonization_new' => 'harmonisasi/new',
        'harmonization_edit' => 'harmonisasi/edit/{id}',
        'harmonization_detail' => 'harmonisasi/detail/{id}',
        'harmonization_assign' => 'harmonisasi/tugaskan/{id}',
        'verification' => 'verifikasi',
        'verification_process' => 'verifikasi/proses/{id}',
        'validation' => 'validasi',
        'validation_process' => 'validasi/proses/{id}',
        'finalization' => 'finalisasi',
        'finalization_process' => 'finalisasi/proses/{id}',
        'signature' => 'paraf',
        'signature_process' => 'paraf/prosesParaf/{id}',
        'signature_detail' => 'paraf/detail/{id}',
        'assignment' => 'penugasan',
        'assignment_assign' => 'penugasan/tugaskan/{id}',
        'settings' => 'admin/settings',
        'maintenance' => 'maintenance-notice',
    ];

    /**
     * API URL Patterns
     */
    public const API = [
        'regulations_list' => 'api/regulations',
        'regulation_detail' => 'api/regulations/{id}',
        'search' => 'api/search',
        'statistics' => 'api/statistics',
        'visitor_stats' => 'api/visitor-stats',
        'feedback' => 'api/feedback',
    ];

    /**
     * File Operations URL Patterns
     */
    public const FILES = [
        'download_regulation' => 'peraturan/download/{id}',
        'download_attachment' => 'peraturan/download_lampiran/{id}',
        'download_harmonization' => 'harmonisasi/download/{id}',
        'download_verification' => 'verifikasi/download/{id}',
        'download_validation' => 'validasi/download/{id}',
        'download_finalization' => 'finalisasi/download/{id}',
        'download_signature' => 'paraf/download/{id}',
        'preview_regulation' => 'peraturan/preview/{id}',
        'preview_document' => 'dokumen/preview/{id}',
    ];

    /**
     * AJAX URL Patterns
     */
    public const AJAX = [
        'regulations_list' => 'data-peraturan/ajax_list',
        'get_regulation' => 'data-peraturan/ajaxGetPeraturan',
        'get_institution' => 'data-peraturan/ajaxGetInstansi',
        'get_tags' => 'data-peraturan/ajaxGetTag',
        'add_tag' => 'data-peraturan/add_tag_ajax',
        'search_regulation' => 'data-peraturan/ajaxSearchPeraturan',
        'get_relation_info' => 'data-peraturan/ajaxGetJenisRelasiInfo',
        'harmonization_submit' => 'harmonisasi/submitAksi',
        'verification_submit' => 'verifikasi/submitAksi',
        'validation_submit' => 'validasi/submitAksi',
        'finalization_submit' => 'finalisasi/submitAksi',
        'signature_submit' => 'paraf/submitParaf',
    ];

    /**
     * Get URL pattern by key and category
     */
    public static function get(string $category, string $key, array $params = []): string
    {
        $patterns = constant("self::" . strtoupper($category));

        if (!isset($patterns[$key])) {
            throw new \InvalidArgumentException("URL pattern '{$key}' not found in category '{$category}'");
        }

        $pattern = $patterns[$key];

        // Replace parameters in pattern
        foreach ($params as $param => $value) {
            $pattern = str_replace('{' . $param . '}', $value, $pattern);
        }

        return $pattern;
    }

    /**
     * Get frontend URL
     */
    public static function frontend(string $key, array $params = []): string
    {
        return self::get('frontend', $key, $params);
    }

    /**
     * Get admin URL
     */
    public static function admin(string $key, array $params = []): string
    {
        return self::get('admin', $key, $params);
    }

    /**
     * Get API URL
     */
    public static function api(string $key, array $params = []): string
    {
        return self::get('api', $key, $params);
    }

    /**
     * Get file operation URL
     */
    public static function files(string $key, array $params = []): string
    {
        return self::get('files', $key, $params);
    }

    /**
     * Get AJAX URL
     */
    public static function ajax(string $key, array $params = []): string
    {
        return self::get('ajax', $key, $params);
    }

    /**
     * Get all patterns for a category
     */
    public static function getAll(string $category): array
    {
        return constant("self::" . strtoupper($category));
    }

    /**
     * Check if pattern exists
     */
    public static function exists(string $category, string $key): bool
    {
        $patterns = constant("self::" . strtoupper($category));
        return isset($patterns[$key]);
    }
}
