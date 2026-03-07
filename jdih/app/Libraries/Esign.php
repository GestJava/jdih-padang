<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Response;
use Config\Services;

class Esign
{
    protected $clientId;
    protected $clientSecret;
    protected $host;
    protected $error;
    protected $timeout = 30; // detik

    public function __construct()
    {
        $this->clientId = getenv('esign.client_id') ?: 'diskominfo';
        $this->clientSecret = getenv('esign.client_secret') ?: '';
        $this->host = rtrim(getenv('esign.host') ?: 'http://103.141.74.94', '/');
        $this->error = null;
    }

    /**
     * Sign PDF secara tidak terlihat (invisible signature)
     */
    public function signInvisible(string $nik, string $passphrase, string $pdfPath): ResponseInterface
    {
        try {
            if (!is_file($pdfPath)) {
                throw new \RuntimeException("File PDF tidak ditemukan: $pdfPath");
            }

            $client = $this->getHttpClient();
            
            $options = [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($pdfPath, 'r'),
                        'filename' => basename($pdfPath),
                    ],
                    [
                        'name'     => 'nik',
                        'contents' => $nik,
                    ],
                    [
                        'name'     => 'passphrase',
                        'contents' => $passphrase,
                    ],
                    [
                        'name'     => 'tampilan',
                        'contents' => 'invisible',
                    ]
                ]
            ];

            $url = $this->host . '/api/sign/pdf';
            $response = $client->post($url, $options);
            
            if ($response->getStatusCode() !== 200) {
                $error = json_decode($response->getBody(), true);
                $message = $error['message'] ?? 'Gagal melakukan tanda tangan';
                throw new \RuntimeException($message);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            log_message('error', 'TTE Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cek status sertifikat TTE
     */
    public function checkCertificateStatus(string $nik): array
    {
        try {
            $client = $this->getHttpClient();
            $url = $this->host . "/api/user/status/" . urlencode($nik);
            
            $response = $client->get($url, [
                'headers' => ['Accept' => 'application/json']
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            if ($statusCode === 200) {
                return [
                    'status' => $body['status'] ?? 'UNKNOWN',
                    'expired_date' => $body['expired_date'] ?? null,
                    'error_code' => null,
                    'message' => $body['message'] ?? 'Success',
                    'http_status' => $statusCode
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'error_code' => $body['error_code'] ?? 'UNKNOWN_ERROR',
                    'message' => $body['message'] ?? 'Gagal memeriksa status sertifikat',
                    'http_status' => $statusCode
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking certificate status: ' . $e->getMessage());
            
            return [
                'status' => 'ERROR',
                'error_code' => 'CONNECTION_ERROR',
                'message' => 'Gagal terhubung ke server TTE: ' . $e->getMessage(),
                'http_status' => 0
            ];
        }
    }

    /**
     * Get HTTP client with common configuration
     */
    protected function getHttpClient()
    {
        return Services::curlrequest([
            'timeout' => $this->timeout,
            'http_errors' => false,
            'verify' => false,
            'auth' => [$this->clientId, $this->clientSecret]
        ]);
    }

    /**
     * Get last error message
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Check if last operation was successful
     */
    public function isSuccess(): bool
    {
        return $this->error === null;
    }
}
