<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class GeminiService
{
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent';
    private CURLRequest $client;

    public function __construct()
    {
        $this->apiKey = getenv('GEMINI_API_KEY');
        if (empty($this->apiKey)) {
            throw new \Exception('GEMINI_API_KEY tidak ditemukan di file .env');
        }
        
        $this->client = Services::curlrequest();
    }

    /**
     * Generate content using Gemini API
     */
    public function generateContent(string $prompt): string
    {
        try {
            $url = $this->baseUrl . '?key=' . $this->apiKey;
            
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048
                ]
            ];

            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $data,
                'timeout' => 30
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Gemini API error: ' . $response->getStatusCode());
            }

            $responseData = json_decode($response->getBody(), true);
            
            if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                log_message('error', 'Gemini API response format unexpected: ' . $response->getBody());
                throw new \Exception('Format respons Gemini API tidak sesuai');
            }

            return $responseData['candidates'][0]['content']['parts'][0]['text'];
            
        } catch (\Exception $e) {
            log_message('error', 'Gemini API error: ' . $e->getMessage());
            
            // Fallback response
            return 'Maaf, saat ini sistem sedang mengalami gangguan. Silakan coba lagi nanti atau hubungi administrator untuk bantuan lebih lanjut.';
        }
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get API status
     */
    public function getStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'api_key_length' => $this->isConfigured() ? strlen($this->apiKey) : 0,
            'base_url' => $this->baseUrl
        ];
    }
}