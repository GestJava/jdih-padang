<?php

namespace Config;

/**
 * Production Configuration Override
 * 
 * File ini berisi konfigurasi khusus untuk environment production
 * Akan di-load otomatis jika environment = production
 */

class Production extends App
{
    /**
     * Production-specific configurations
     */
    public function __construct()
    {
        parent::__construct();

        // Force HTTPS in production
        $this->forceGlobalSecureRequests = true;
    }

    /**
     * Override base URL detection for production
     */
    protected function detectBaseURL()
    {
        // Always use HTTPS in production
        $this->baseURL = 'https://jdih.padang.go.id/';
        $this->imagesURL = $this->baseURL . 'images/';
    }
}
