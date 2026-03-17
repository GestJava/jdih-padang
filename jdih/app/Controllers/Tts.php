<?php

namespace App\Controllers;

use Config\Services;
use Config\TtsConfig;

class Tts extends BaseController
{
    protected TtsConfig $config;

    public function __construct()
    {
        $this->config = new TtsConfig();
    }

    /**
     * Synthesize text to speech
     * GET or POST parameters:
     * - text: The text to convert
     * - voice: (optional) A, B, C, or D (Wavenet voices)
     * - rate: (optional) Speaking rate (0.25 - 4.0)
     */
    public function synthesize()
    {
        $text = $this->request->getVar('text');
        $voiceType = $this->request->getVar('voice') ?: 'A';
        $rate = $this->request->getVar('rate') ?: 1.0;

        if (empty($text)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Text is required']);
        }

        // Limit text length per request to prevent abuse/timeout
        $text = mb_substr($text, 0, 5000); 

        // Generate cache filename
        $cacheKey = md5($text . $voiceType . $rate);
        $cacheDir = $this->config->cachePath;
        $cacheFile = $cacheDir . $cacheKey . '.mp3';

        // Check cache
        if (file_exists($cacheFile)) {
            return $this->response
                ->setHeader('Content-Type', 'audio/mpeg')
                ->setHeader('Content-Disposition', 'inline; filename="speech.mp3"')
                ->setBody(file_get_contents($cacheFile));
        }

        // Check API Key
        if (empty($this->config->apiKey)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Google TTS API Key is not configured']);
        }

        // Prepare Google Cloud TTS Request
        $voiceName = "id-ID-Wavenet-" . strtoupper($voiceType);
        $gender = (in_array(strtoupper($voiceType), ['A', 'D'])) ? 'FEMALE' : 'MALE';

        $payload = [
            'input' => ['text' => $text],
            'voice' => [
                'languageCode' => 'id-ID',
                'name' => $voiceName,
                'ssmlGender' => $gender
            ],
            'audioConfig' => [
                'audioEncoding' => $this->config->audioEncoding,
                'speakingRate' => (float)$rate
            ]
        ];

        try {
            $apiUrl = "https://texttospeech.googleapis.com/v1/text:synthesize?key=" . $this->config->apiKey;
            
            $client = Services::curlrequest([
                'timeout' => 30,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $response = $client->post($apiUrl, [
                'json' => $payload,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody(), true);
                if (isset($result['audioContent'])) {
                    $audioData = base64_decode($result['audioContent']);
                    
                    // Ensure cache directory exists
                    if (!is_dir($cacheDir)) {
                        mkdir($cacheDir, 0777, true);
                    }
                    
                    // Save to cache
                    file_put_contents($cacheFile, $audioData);

                    return $this->response
                        ->setHeader('Content-Type', 'audio/mpeg')
                        ->setHeader('Content-Disposition', 'inline; filename="speech.mp3"')
                        ->setBody($audioData);
                }
            }

            log_message('error', 'Google Cloud TTS API Error: ' . $response->getStatusCode() . ' - ' . $response->getBody());
            return $this->response->setStatusCode($response->getStatusCode() ?: 500)
                ->setJSON(['error' => 'Gagal memproses suara premium. Sistem dialihkan ke suara standar.', 'details' => $response->getBody()]);

        } catch (\Exception $e) {
            log_message('error', 'TTS Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
}
