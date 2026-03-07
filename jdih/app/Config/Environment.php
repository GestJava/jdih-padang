<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Environment Configuration for TTE System
 */
class Environment extends BaseConfig
{
    /**
     * TTE Testing Mode
     * Set to true for development/testing
     */
    public bool $tteTestingMode = true;

    /**
     * TTE API Configuration
     * Read from .env file, fallback to defaults
     */
    public string $tteApiUrl;
    public string $tteClientId;
    public string $tteClientSecret;
    public int $tteApiTimeout;
    public bool $tteApiDebug;

    public function __construct()
    {
        parent::__construct();
        
        // Read from .env file
        $this->tteApiUrl = $_ENV['esign.host'] ?? $_ENV['TTE_API_URL'] ?? 'http://103.141.74.94/api/v2';
        $this->tteClientId = $_ENV['esign.client_id'] ?? $_ENV['TTE_CLIENT_ID'] ?? 'jdih';
        $this->tteClientSecret = $_ENV['esign.client_secret'] ?? $_ENV['TTE_CLIENT_SECRET'] ?? 'password1234';
        $this->tteApiTimeout = (int)($_ENV['TTE_API_TIMEOUT'] ?? 30);
        $this->tteApiDebug = filter_var($_ENV['TTE_API_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Test Credentials for TTE Testing Mode
     */
    public string $testNik = '1234567890123456';
    public string $testPassword = 'test_password_123';
    public string $testCertificateStatus = 'ACTIVE';

    /**
     * Get TTE Testing Mode Status
     */
    public function isTteTestingMode(): bool
    {
        return $this->tteTestingMode || getenv('CI_ENVIRONMENT') === 'development';
    }

    /**
     * Get Test Credentials
     */
    public function getTestCredentials(): array
    {
        return [
            'test_nik' => $this->testNik,
            'test_password' => $this->testPassword,
            'test_certificate_status' => $this->testCertificateStatus
        ];
    }
}
