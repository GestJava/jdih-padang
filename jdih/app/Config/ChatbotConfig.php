<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ChatbotConfig extends BaseConfig
{
    /**
     * Your Google Gemini API Key.
     * IMPORTANT: Replace 'YOUR_API_KEY' with your actual API key.
     *
     * @var string
     */
    public string $geminiApiKey;

    /**
     * The Gemini API endpoint URL.
     * Format: https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
     * 
     * Model yang tersedia (dari ListModels API):
     * - gemini-2.5-flash (Recommended - latest and fast)
     * - gemini-2.5-pro (More powerful)
     * - gemini-flash-latest (Latest flash model)
     * - gemini-pro-latest (Latest pro model)
     * - gemini-2.0-flash
     *
     * @var string
     */
    public string $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    
    /**
     * Base URL untuk Gemini API
     * 
     * @var string
     */
    public string $geminiApiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    /**
     * Fallback models jika model utama gagal (dalam urutan prioritas)
     * Model yang benar-benar tersedia berdasarkan ListModels API
     * 
     * @var array
     */
    public array $fallbackModels = [
        'gemini-flash-latest',
        'gemini-pro-latest',
        'gemini-2.5-pro',
        'gemini-2.0-flash',
        'gemini-2.0-flash-001',
        'gemini-2.5-flash-lite'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->geminiApiKey = getenv('GEMINI_API_KEY') ?: '';
    }
}
