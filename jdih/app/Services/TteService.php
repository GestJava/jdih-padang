<?php

namespace App\Services;

use Config\Services;

class TteService
{
    protected $client;
    protected $config;
    protected $lastError;

    public function __construct()
    {
        $envConfig = new \Config\Environment();

        $this->config = (object) [
            'host' => $envConfig->tteApiUrl,
            'client_id' => $envConfig->tteClientId,
            'client_secret' => $envConfig->tteClientSecret,
            'timeout' => $envConfig->tteApiTimeout,
            'debug' => $envConfig->tteApiDebug,
            'testing_mode' => $envConfig->isTteTestingMode()
        ];

        $this->client = \Config\Services::curlrequest([
            'timeout' => $this->config->timeout,
            'connect_timeout' => 10,
            'http_errors' => false,
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'JDIH-TTE-Client/1.0',
                'Cache-Control' => 'no-cache',
                'Connection' => 'close'
            ]
        ]);
    }

    /**
     * Get the last error message
     */
    public function getLastError(): ?array
    {
        return $this->lastError;
    }

    /**
     * Check user status in TTE system
     *
     * @param string $nik
     * @return array|false
     */
    public function checkUserStatus(string $nik)
    {
        // Jika testing mode aktif, gunakan mock service
        if ($this->config->testing_mode) {
            $testingService = new \App\Services\TteTestingService();
            return $testingService->mockCheckUserStatus($nik);
        }

        try {
            $endpoint = rtrim($this->config->host, '/') . '/user/status/' . $nik;
            $response = $this->client->get($endpoint);

            if ($response->getStatusCode() !== 200) {
                $this->lastError = [
                    'code' => $response->getStatusCode(),
                    'message' => 'Gagal memeriksa status pengguna',
                    'response' => (string) $response->getBody()
                ];
                return false;
            }

            $result = json_decode((string) $response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Gagal memparsing respons dari server TTE');
            }

            return $result;
        } catch (\Exception $e) {
            $this->lastError = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
            return false;
        }
    }

    /**
     * Send request to TTE API
     */
    protected function sendRequest($method, $endpoint, $data = [], $files = [])
    {
        $url = rtrim($this->config->host, '/') . '/' . ltrim($endpoint, '/');
        $options = [
            'auth' => [$this->config->client_id, $this->config->client_secret],
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->config->client_id . ':' . $this->config->client_secret)
            ]
        ];

        if (!empty($data)) {
            if (strtoupper($method) === 'GET') {
                $url .= '?' . http_build_query($data);
            } else {
                if (empty($files)) {
                    $options['form_params'] = $data;
                    $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
                } else {
                    // Handle file uploads
                    $options['multipart'] = [];
                    foreach ($data as $key => $value) {
                        $options['multipart'][] = [
                            'name' => $key,
                            'contents' => $value
                        ];
                    }
                    foreach ($files as $key => $filePath) {
                        if (file_exists($filePath)) {
                            $options['multipart'][] = [
                                'name' => $key,
                                'contents' => fopen($filePath, 'r'),
                                'filename' => basename($filePath)
                            ];
                        }
                    }
                }
            }
        }

        try {
            $response = $this->client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();

            // Handle response body dengan benar
            $body = $response->getBody();
            $bodyContent = is_string($body) ? $body : ($body ? $body->getContents() : '');

            // Coba decode JSON, jika gagal gunakan response asli
            $result = json_decode($bodyContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If it's HTML (likely FortiGuard block), log it clearly
                if (stripos($bodyContent, '<!DOCTYPE html>') !== false || stripos($bodyContent, '<html>') !== false) {
                    log_message('error', 'TTE API returned HTML instead of JSON. Likely network block/firewall.');
                }
                $result = $bodyContent;
            }

            if ($statusCode >= 400) {
                $this->lastError = [
                    'status' => $statusCode,
                    'message' => $response->getReasonPhrase(),
                    'response' => $result
                ];
                return false;
            }

            return $result;
        } catch (\Exception $e) {
            $this->lastError = [
                'status' => 500,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            return false;
        }
    }

    /**
     * Check user status
     */
    public function checkStatus($nik)
    {
        return $this->sendRequest('GET', '/user/status/' . urlencode($nik));
    }

    /**
     * Register new user
     */
    public function registerUser($data, $signatureImage, $idCard, $recommendationLetter = null)
    {
        $files = [
            'image_ttd' => $signatureImage,
            'ktp' => $idCard
        ];

        if ($recommendationLetter) {
            $files['surat_rekomendasi'] = $recommendationLetter;
        }

        return $this->sendRequest('POST', '/user/registrasi', $data, $files);
    }

    /**
     * Sign document
     */
    public function signDocument($nik, $password, $documentPath, $x = 100, $y = 100, $width = 200, $height = 100, $isSpecimen = false)
    {
        if (!file_exists($documentPath)) {
            $this->lastError = ['message' => 'Document not found: ' . $documentPath];
            return false;
        }

        $data = [
            'nik' => $nik,
            'password' => $password,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'spesimen' => $isSpecimen ? '1' : '0'
        ];

        $files = [
            'file' => $documentPath
        ];

        return $this->sendRequest('POST', '/document/sign', $data, $files);
    }

    /**
     * Sign document dengan enhancement (nomor, tanggal, QR code) menggunakan sistem TTE yang sudah sempurna
     */
    public function signDocumentWithEnhancement($nik, $password, $documentPath, $metadata = [], $x = 100, $y = 100, $width = 200, $height = 100, $isSpecimen = false)
    {
        try {
            // Jika testing mode aktif, gunakan mock service
            if ($this->config->testing_mode) {
                $testingService = new \App\Services\TteTestingService();
                return $testingService->mockSignDocument($nik, $password, $documentPath, $metadata);
            }

            // Gunakan sistem TTE yang sudah sempurna untuk enhancement
            $pdfEnhancementService = new \App\Services\PdfEnhancementService();

            // Extract data dari metadata untuk enhancement
            $nomorPeraturan = $metadata['nomor_peraturan'] ?? '1';
            $tanggalPenetapan = $metadata['tanggal_pengesahan'] ?? date('d F Y');
            $documentUrl = $metadata['document_url'] ?? base_url();
            $qrSize = 80; // Default QR size untuk dokumen legal

            // Generate QR code untuk enhancement
            $qrCodeService = new \App\Services\SimpleQRCodeService();
            $qrCodePath = $qrCodeService->generateDownloadQRCode($documentUrl, $nomorPeraturan);

            // Enhance PDF dengan sistem yang sudah sempurna
            $enhancedPath = $pdfEnhancementService->enhancePdf(
                $documentPath,
                $nomorPeraturan,
                $tanggalPenetapan,
                $documentUrl,
                $qrSize
            );

            if (!$enhancedPath) {
                $this->lastError = ['message' => 'Failed to enhance PDF with TTE system'];
                return false;
            }

            // Kemudian sign dokumen yang sudah di-enhance
            $signResult = $this->signDocument($nik, $password, $enhancedPath, $x, $y, $width, $height, $isSpecimen);

            if ($signResult === false) {
                // Hapus file enhanced jika signing gagal
                if (file_exists($enhancedPath)) {
                    unlink($enhancedPath);
                }
                return false;
            }

            // Update result dengan path enhanced dan metadata
            if (is_array($signResult)) {
                $signResult['enhanced_document_path'] = $enhancedPath;
                $signResult['metadata'] = $metadata;
                $signResult['nomor_peraturan'] = $nomorPeraturan;
                $signResult['tanggal_pengesahan'] = $tanggalPenetapan;
                $signResult['document_url'] = $documentUrl;
                $signResult['qr_code_path'] = $qrCodePath;
            }

            return $signResult;
        } catch (\Exception $e) {
            $this->lastError = [
                'message' => 'Enhanced signing failed: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            return false;
        }
    }

    /**
     * Validate certificate for TTE
     *
     * @param string $nik
     * @param string $password
     * @return bool
     */
    public function validateCertificate(string $nik, string $password): bool
    {
        try {
            // Jika dalam mode testing, validasi dengan kredensial testing
            if ($this->config->testing_mode) {
                $testNik = env('TTE_TEST_NIK', '1234567890123456');
                $testPassword = env('TTE_TEST_PASSWORD', 'testpassword');

                return ($nik === $testNik && $password === $testPassword);
            }

            // Mode production - validasi dengan BSrE
            $response = $this->client->post($this->config->host . '/validate-certificate', [
                'json' => [
                    'nik' => $nik,
                    'password' => $password,
                    'client_id' => $this->config->client_id,
                    'client_secret' => $this->config->client_secret
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            $data = json_decode($responseBody, true);

            if ($statusCode === 200 && isset($data['valid']) && $data['valid'] === true) {
                return true;
            }

            $this->lastError = [
                'message' => 'Certificate validation failed',
                'status_code' => $statusCode,
                'response' => $data
            ];

            return false;
        } catch (\Exception $e) {
            $this->lastError = [
                'message' => 'Certificate validation error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            return false;
        }
    }
}
