<?php

namespace Config;

/**
 * Environment Configuration
 * Konfigurasi untuk berbagai environment deployment
 */
class EnvironmentConfig
{
    /**
     * Environment configurations
     */
    private static $environments = [
        'development' => [
            'base_url' => 'http://localhost/webjdih/',
            'database' => [
                'hostname' => 'localhost',
                'username' => 'root',
                'password' => '',
                'database' => 'jdih',
                'port' => 3306,
            ],
            'debug' => true,
            'log_threshold' => 4,
            'show_errors' => true,
            'force_https' => false,
        ],
        'staging' => [
            'base_url' => 'https://staging.jdih.padang.go.id/',
            'database' => [
                'hostname' => 'localhost',
                'username' => 'staging_user',
                'password' => 'staging_password',
                'database' => 'staging_jdih',
                'port' => 3306,
            ],
            'debug' => false,
            'log_threshold' => 2,
            'show_errors' => false,
            'force_https' => true,
        ],
        'production' => [
            'base_url' => 'https://jdih.padang.go.id/',
            'database' => [
                'hostname' => 'localhost',
                'username' => 'jdih_user',
                'password' => 'production_password',
                'database' => 'jdih_production',
                'port' => 3306,
            ],
            'debug' => false,
            'log_threshold' => 1,
            'show_errors' => false,
            'force_https' => true,
        ],
    ];

    /**
     * Get current environment
     */
    public static function getCurrentEnvironment(): string
    {
        return $_ENV['CI_ENVIRONMENT'] ?? 'development';
    }

    /**
     * Get configuration for current environment
     */
    public static function getConfig(): array
    {
        $env = self::getCurrentEnvironment();
        return self::$environments[$env] ?? self::$environments['development'];
    }

    /**
     * Get specific config value
     */
    public static function get(string $key, $default = null)
    {
        $config = self::getConfig();
        return $config[$key] ?? $default;
    }

    /**
     * Get database configuration
     */
    public static function getDatabaseConfig(): array
    {
        return self::get('database', []);
    }

    /**
     * Get base URL
     */
    public static function getBaseUrl(): string
    {
        return self::get('base_url', 'http://localhost/webjdih/');
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebugMode(): bool
    {
        return self::get('debug', false);
    }

    /**
     * Check if HTTPS is forced
     */
    public static function isHttpsForced(): bool
    {
        return self::get('force_https', false);
    }

    /**
     * Get log threshold
     */
    public static function getLogThreshold(): int
    {
        return self::get('log_threshold', 4);
    }

    /**
     * Check if errors should be shown
     */
    public static function shouldShowErrors(): bool
    {
        return self::get('show_errors', true);
    }

    /**
     * Auto-detect environment based on server characteristics
     */
    public static function autoDetectEnvironment(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // Production detection
        if (strpos($host, 'jdih.padang.go.id') !== false && strpos($host, 'staging') === false) {
            return 'production';
        }

        // Staging detection
        if (strpos($host, 'staging') !== false || strpos($host, 'dev') !== false) {
            return 'staging';
        }

        // Development detection (localhost, IP addresses, etc.)
        if (
            strpos($host, 'localhost') !== false ||
            strpos($host, '127.0.0.1') !== false ||
            strpos($host, '192.168.') !== false ||
            strpos($host, '10.0.') !== false ||
            preg_match('/^\d+\.\d+\.\d+\.\d+/', $host)
        ) {
            return 'development';
        }

        // Default to development
        return 'development';
    }

    /**
     * Get auto-detected base URL
     */
    public static function autoDetectBaseUrl(): string
    {
        $protocol = 'http';
        if (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        ) {
            $protocol = 'https';
        }

        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // Auto-detect path
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $pathInfo = dirname($scriptName);

        // Clean up path
        $path = '';
        if ($pathInfo !== '/' && $pathInfo !== '\\') {
            $path = rtrim($pathInfo, '/\\') . '/';
        }

        return $protocol . '://' . $host . $path;
    }

    /**
     * Set environment configuration
     */
    public static function setEnvironmentConfig(string $environment, array $config): void
    {
        self::$environments[$environment] = array_merge(
            self::$environments[$environment] ?? [],
            $config
        );
    }

    /**
     * Get all environments
     */
    public static function getAllEnvironments(): array
    {
        return array_keys(self::$environments);
    }

    /**
     * Check if environment exists
     */
    public static function environmentExists(string $environment): bool
    {
        return isset(self::$environments[$environment]);
    }

    /**
     * Get environment-specific file path
     */
    public static function getEnvironmentFilePath(string $filename): string
    {
        $env = self::getCurrentEnvironment();
        $envFile = pathinfo($filename, PATHINFO_DIRNAME) . '/' .
            pathinfo($filename, PATHINFO_FILENAME) . '.' . $env . '.' .
            pathinfo($filename, PATHINFO_EXTENSION);

        return file_exists($envFile) ? $envFile : $filename;
    }
}
