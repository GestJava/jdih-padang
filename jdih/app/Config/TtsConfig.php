<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class TtsConfig extends BaseConfig
{
    /**
     * Google Cloud API Key for Text-to-Speech
     * If empty, will try to use GEMINI_API_KEY from .env
     */
    public string $apiKey = '';

    /**
     * Default voice configuration for Indonesian
     */
    public array $defaultVoice = [
        'languageCode' => 'id-ID',
        'name'         => 'id-ID-Wavenet-A', // Options: A (Female), B (Male), C (Male), D (Female)
        'ssmlGender'   => 'FEMALE'
    ];

    /**
     * Audio encoding format
     */
    public string $audioEncoding = 'MP3';

    /**
     * Cache directory for generated audio files
     */
    public string $cachePath = WRITEPATH . 'cache/tts/';

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = getenv('GOOGLE_TTS_API_KEY') ?: getenv('GEMINI_API_KEY') ?: '';
    }
}
