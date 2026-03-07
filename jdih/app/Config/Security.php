<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    /**
     * CSRF Protection Configuration
     */
    public $csrfProtection = [
        'enabled' => true,
        'tokenName' => 'csrf_token',
        'headerName' => 'X-CSRF-TOKEN',
        'expire' => 7200, // 2 hours
        'regenerate' => true,
        'redirect' => false,
        'samesite' => 'Lax',
    ];

    /**
     * Rate Limiting Configuration
     */
    public $rateLimiting = [
        'enabled' => true,
        'maxRequests' => 100, // Max requests per minute
        'window' => 60, // Time window in seconds
        'storage' => 'cache', // Use cache for rate limiting
    ];

    /**
     * Input Validation Rules
     */
    public $inputValidation = [
        'maxLength' => [
            'keyword' => 100,
            'filename' => 255,
            'url' => 2048,
        ],
        'allowedExtensions' => [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'jpg',
            'jpeg',
            'png',
            'gif'
        ],
        'blockedPatterns' => [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
        ]
    ];

    /**
     * File Upload Security
     */
    public $fileUpload = [
        'maxSize' => 10485760, // 10MB
        'allowedTypes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'scanVirus' => false, // Enable if antivirus available
        'validateContent' => true,
    ];

    /**
     * SQL Injection Protection
     */
    public $sqlInjection = [
        'enabled' => true,
        'blockedKeywords' => [
            'UNION',
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'DROP',
            'CREATE',
            'ALTER',
            'EXEC',
            'EXECUTE',
            'SCRIPT',
            '--',
            '/*',
            '*/',
            'xp_'
        ],
        'maxQueryLength' => 1000,
    ];

    /**
     * XSS Protection
     */
    public $xssProtection = [
        'enabled' => true,
        'mode' => 'block', // block, sanitize
        'reportUri' => null,
    ];

    /**
     * Directory Traversal Protection
     */
    public $directoryTraversal = [
        'enabled' => true,
        'blockedPatterns' => [
            '..',
            '\\',
            '//',
            '~',
            '..\\',
            '../',
            '..//'
        ],
        'allowedPaths' => [
            'uploads/peraturan',
            'uploads/lampiran',
            'uploads/berita',
            'uploads/agenda'
        ]
    ];

    /**
     * IP Blocking Configuration
     */
    public $ipBlocking = [
        'enabled' => true,
        'blockedIPs' => [],
        'whitelistIPs' => [],
        'maxFailedAttempts' => 5,
        'blockDuration' => 3600, // 1 hour
    ];

    /**
     * Session Security
     */
    public $sessionSecurity = [
        'regenerateId' => true,
        'regenerateInterval' => 300, // 5 minutes
        'secure' => true,
        'httpOnly' => true,
        'sameSite' => 'Lax',
    ];

    /**
     * Error Reporting Security
     */
    public $errorReporting = [
        'displayErrors' => false,
        'logErrors' => true,
        'hidePaths' => true,
        'sanitizeMessages' => true,
    ];
}
