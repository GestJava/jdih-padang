<?php

namespace App\Services;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;
use App\Services\PdfNumberingService;

class DocumentSigningService
{
    protected $config;
    protected $numberingService;
    protected $logger;
    protected $pdfNumberingService;

    public function __construct()
    {
        $this->config = config('Document');
        $this->numberingService = new DocumentNumberingService();
        $this->logger = \Config\Services::logger();
        $this->pdfNumberingService = new PdfNumberingService();
    }

    /**
     * Proses penandatanganan dokumen
     * 
     * @param string $documentPath Path lengkap ke dokumen yang akan ditandatangani
     * @param string $documentType Jenis dokumen (contoh: 'peraturan_walikota')
     * @param string $nik NIK penandatangan
     * @param string $passphrase Passphrase sertifikat digital
     * @param array $options Opsi tambahan [id_permohonan, tahun, dll]
     * @return array Hasil proses penandatanganan
     * @throws \RuntimeException Jika terjadi kesalahan
     */
    public function signDocument(
        string $documentPath,
        string $documentType,
        string $nik,
        string $passphrase,
        array $options = []
    ): array {
        try {
            // 1. Validasi dokumen
            $this->validateDocument($documentPath);

            // 2. Dapatkan nomor dokumen jika diperlukan
            $numbering = null;
            $documentNumber = $options['document_number'] ?? null;

            if (!empty($documentNumber)) {
                // Gunakan nomor dokumen yang sudah digenerate
                $numbering = [
                    'nomor_penuh' => $documentNumber,
                    'nomor_urut' => $options['nomor_urut'] ?? null,
                    'tahun' => $options['tahun'] ?? date('Y')
                ];
                $this->logger->info('Menggunakan nomor dokumen yang sudah digenerate: ' . $documentNumber);
            } elseif (empty($options['skip_numbering'])) {
                // Generate nomor dokumen baru jika tidak disediakan
                $numbering = $this->numberingService->getNextNumber($documentType);
                $this->logger->info('Mendapatkan nomor dokumen baru: ' . json_encode($numbering));
            } else {
                $this->logger->info('Melewati proses penomoran (skip_numbering = true)');
            }

            // 3. Proses penomoran dokumen jika diperlukan
            if ($numbering === null) {
                $this->logger->info('Melewati proses penomoran (sudah dinomori)');
                $processedPath = $documentPath;
            } else {
                $this->logger->info('Memulai proses penomoran dokumen');
                $processedPath = $this->addDocumentNumber(
                    $documentPath,
                    $numbering['nomor_penuh']
                );
            }

            // 4. Proses TTE
            $this->logger->info('Memulai proses TTE', [
                'document_path' => $processedPath,
                'nik' => $nik,
                'options' => $options
            ]);

            try {
                $signedPath = $this->processTte(
                    $processedPath,  // path file
                    $nik,            // NIK
                    $passphrase,     // passphrase
                    $options         // options
                );

                $this->logger->info('Proses TTE berhasil', [
                    'signed_path' => $signedPath,
                    'file_exists' => file_exists($signedPath) ? 'Ya' : 'Tidak',
                    'file_size' => file_exists($signedPath) ? filesize($signedPath) . ' bytes' : 'File tidak ada'
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Gagal memproses TTE: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'options' => $options
                ]);
                throw $e;
            }

            // 5. Simpan metadata
            return [
                'success' => true,
                'signed_path' => $signedPath,
                'processed_path' => $processedPath,
                'document_number' => $numbering['nomor_penuh'],
                'nomor_urut' => $numbering['nomor_urut'],
                'original_path' => $documentPath,
                'tahun' => $numbering['tahun']
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error dalam proses TTE: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Validasi dokumen
     */
    protected function validateDocument(string $path): void
    {
        // Normalisasi path
        $path = str_replace(['//', '\\'], DIRECTORY_SEPARATOR, $path);
        $path = preg_replace('#/+#', '/', $path);

        $this->logger->debug('Memvalidasi dokumen: ' . $path);
        $this->logger->debug('File exists: ' . (file_exists($path) ? 'Ya' : 'Tidak'));

        if (!file_exists($path)) {
            $this->logger->error('File tidak ditemukan: ' . $path);
            $this->logger->debug('Direktori: ' . dirname($path));
            $this->logger->debug('Isi direktori: ' . print_r(@scandir(dirname($path)), true));
            throw new \RuntimeException("File tidak ditemukan: {$path}");
        }

        $file = new File($path);
        $mime = $file->getMimeType();

        $this->logger->debug("MIME type: " . $mime);

        if ($mime !== 'application/pdf') {
            throw new \RuntimeException("Format file harus PDF, ditemukan: {$mime}");
        }
    }

    /**
     * Cari path Python yang tersedia
     */
    protected function getPythonPath(): string
    {
        // Deteksi OS
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $isMac = strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN';
        
        // Daftar path Python yang mungkin ada
        $possiblePaths = [];
        
        if ($isMac) {
            // macOS paths
            $possiblePaths = [
                'python3',                                  // Python3 di PATH (preferred)
                '/usr/bin/python3',                         // Default macOS Python3
                '/usr/local/bin/python3',                   // Homebrew Python3
                '/opt/homebrew/bin/python3',                // M1/M2 Homebrew Python3
                '/Library/Frameworks/Python.framework/Versions/3.9/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.10/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.11/bin/python3',
                '/Library/Frameworks/Python.framework/Versions/3.12/bin/python3',
                'python',                                   // Generic Python
            ];
        } elseif ($isWindows) {
            // Windows paths
            $possiblePaths = [
                'python',                                   // Python di PATH
                'py',                                       // Python launcher
                'C:\\Users\\HP\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
                'C:\\Users\\hp\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
                'C:\\Python313\\python.exe',
                'C:\\Python\\python.exe',
                'C:\\Program Files\\Python313\\python.exe',
                'C:\\Program Files\\Python\\python.exe',
            ];
        } else {
            // Linux paths
            $possiblePaths = [
                'python3',
                '/usr/bin/python3',
                '/usr/local/bin/python3',
                'python',
            ];
        }

        foreach ($possiblePaths as $path) {
            if ($this->isPythonAvailable($path)) {
                $this->logger->info('Python ditemukan di: ' . $path . ' (OS: ' . PHP_OS . ')');
                return $path;
            }
        }

        throw new \RuntimeException('Python tidak ditemukan di sistem. Silakan install Python 3.x dan pastikan tersedia di PATH. OS: ' . PHP_OS);
    }

    /**
     * Cek apakah Python tersedia di path tertentu
     */
    protected function isPythonAvailable(string $path): bool
    {
        try {
            $command = sprintf('"%s" --version 2>&1', $path);
            $output = [];
            $returnVar = 0;

            $descriptors = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];

            $process = proc_open($command, $descriptors, $pipes, null, null, ['bypass_shell' => true]);

            if (is_resource($process)) {
                $output = stream_get_contents($pipes[1]);
                $errorOutput = stream_get_contents($pipes[2]);

                foreach ($pipes as $pipe) {
                    fclose($pipe);
                }

                $returnVar = proc_close($process);

                // Python tersedia jika return code 0 dan output mengandung "Python"
                return $returnVar === 0 && (strpos($output, 'Python') !== false || strpos($errorOutput, 'Python') !== false);
            }
        } catch (\Exception $e) {
            $this->logger->debug('Error checking Python path ' . $path . ': ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Tambahkan nomor ke dokumen
     */
    protected function addDocumentNumber(string $sourcePath, string $number): string
    {
        // Normalisasi path
        $sourcePath = str_replace(['//', '\\'], DIRECTORY_SEPARATOR, $sourcePath);
        $sourcePath = str_replace('public/public', 'public', $sourcePath); // Perbaiki duplikasi public
        $sourcePath = preg_replace('#/+#', '/', $sourcePath);

        $outputPath = str_replace('.pdf', '_numbered.pdf', $sourcePath);

        $this->logger->info('Menambahkan nomor ke dokumen: ' . $sourcePath);
        $this->logger->debug('Output akan disimpan ke: ' . $outputPath);

        // Gunakan path Python yang fleksibel
        $pythonPath = $this->getPythonPath();

        $this->logger->debug('Menggunakan Python path: ' . $pythonPath);

        // Pastikan direktori output ada
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true)) {
                $error = 'Gagal membuat direktori output: ' . $outputDir;
                $this->logger->error($error);
                throw new \RuntimeException($error);
            }
        }
        $scriptPath = APPPATH . 'PdfTools/insert_number_after_nomor.py';

        // Pastikan script Python ada
        if (!file_exists($scriptPath)) {
            $error = "Script Python tidak ditemukan: {$scriptPath}";
            $this->logger->error($error);
            throw new \RuntimeException($error);
        }

        // Pastikan direktori output ada
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true)) {
                $error = 'Gagal membuat direktori output: ' . $outputDir;
                $this->logger->error($error);
                throw new \RuntimeException($error);
            }
        }

        // Gunakan path lengkap untuk semua argumen
        $command = sprintf(
            '"%s" "%s" "%s" "%s" "%s" 2>&1',
            $pythonPath,
            $scriptPath,
            $sourcePath,
            $outputPath,
            $number
        );

        $this->logger->debug('Menjalankan perintah: ' . $command);

        $output = [];
        $returnVar = 0;
        $descriptors = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open($command, $descriptors, $pipes, null, null, ['bypass_shell' => true]);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);

            foreach ($pipes as $pipe) {
                fclose($pipe);
            }

            $returnVar = proc_close($process);

            if (!empty($errorOutput)) {
                $this->logger->error('Error output: ' . $errorOutput);
                $output[] = $errorOutput;
            }
        } else {
            $error = 'Gagal menjalankan proses Python';
            $this->logger->error($error);
            throw new \RuntimeException($error);
        }

        $this->logger->debug('Return code: ' . $returnVar);
        $this->logger->debug('Output: ' . print_r($output, true));

        if ($returnVar !== 0 || !file_exists($outputPath)) {
            $error = sprintf(
                'Gagal menambahkan nomor ke dokumen. Kode: %d, Pesan: %s',
                $returnVar,
                is_array($output) ? implode("\n", $output) : $output
            );
            $this->logger->error($error);

            // Coba fallback method jika Python gagal
            $this->logger->info('Mencoba fallback method untuk insert nomor...');
            try {
                return $this->addDocumentNumberFallback($sourcePath, $number);
            } catch (\Exception $fallbackError) {
                $this->logger->error('Fallback method juga gagal: ' . $fallbackError->getMessage());
                throw new \RuntimeException($error . ' | Fallback gagal: ' . $fallbackError->getMessage());
            }
        }

        $this->logger->info('Berhasil menambahkan nomor ke dokumen: ' . $outputPath);

        return $outputPath;
    }

    /**
     * Fallback method untuk insert nomor menggunakan PHP (tanpa Python)
     */
    protected function addDocumentNumberFallback(string $sourcePath, string $number): string
    {
        $this->logger->info('Menggunakan PHP fallback method untuk insert nomor ke PDF');

        try {
            // Gunakan PdfNumberingService untuk insert nomor dengan PHP
            return $this->pdfNumberingService->insertNumberToPdf($sourcePath, $number);
        } catch (\Exception $e) {
            $this->logger->error('PHP PDF numbering gagal: ' . $e->getMessage());

            // Ultimate fallback - copy file dengan metadata
            $outputPath = str_replace('.pdf', '_numbered_fallback.pdf', $sourcePath);

            if (!copy($sourcePath, $outputPath)) {
                throw new \RuntimeException('Gagal copy file untuk ultimate fallback method');
            }

            $this->logger->info('Ultimate fallback: File disalin ke ' . $outputPath . ' (nomor: ' . $number . ')');
            $this->logger->warning('PERHATIAN: Ultimate fallback hanya copy file dengan metadata. Nomor: ' . $number);

            return $outputPath;
        }
    }

    /**
     * Proses TTE
     */
    protected function processTte(
        string $documentPath,
        string $nik,
        string $passphrase,
        array $options = []
    ): string {
        // Normalisasi path
        $documentPath = str_replace(['//', '\\'], DIRECTORY_SEPARATOR, $documentPath);
        $documentPath = str_replace('public/public', 'public', $documentPath);
        $documentPath = preg_replace('#/+#', '/', $documentPath);

        $outputPath = str_replace('.pdf', '_signed.pdf', $documentPath);

        $this->logger->debug('Memproses TTE', [
            'document_path' => $documentPath,
            'output_path' => $outputPath,
            'file_exists' => file_exists($documentPath) ? 'Ya' : 'Tidak',
            'options' => $options
        ]);

        // Mode pengujian - hanya copy file asli
        if (env('CI_ENVIRONMENT') === 'development' || env('APP_DEBUG') === true) {
            $this->logger->info('Mode pengujian - Menyalin file tanpa TTE');
            if (!file_exists($documentPath)) {
                throw new \RuntimeException("File sumber tidak ditemukan: {$documentPath}");
            }
            if (!copy($documentPath, $outputPath)) {
                $error = error_get_last();
                throw new \RuntimeException('Gagal menyalin file untuk mode pengujian: ' . ($error['message'] ?? 'Tidak diketahui'));
            }
            return $outputPath;
        }

        // Validasi konfigurasi API TTE
        if (empty($this->config->tteApiUrl) || empty($this->config->tteApiKey)) {
            $this->logger->error('Konfigurasi API TTE tidak lengkap', [
                'tteApiUrl_set' => !empty($this->config->tteApiUrl) ? 'Ya' : 'Tidak',
                'tteApiKey_set' => !empty($this->config->tteApiKey) ? 'Ya' : 'Tidak',
                'tteClientId_set' => !empty($this->config->tteClientId) ? 'Ya' : 'Tidak',
                'tteClientSecret_set' => !empty($this->config->tteClientSecret) ? 'Ya' : 'Tidak'
            ]);
            throw new \RuntimeException('Konfigurasi API TTE tidak lengkap. Silakan periksa pengaturan aplikasi.');
        }

        $apiUrl = rtrim($this->config->tteApiUrl, '/');

        $this->logger->debug('Menggunakan konfigurasi API TTE', [
            'api_url' => $apiUrl,
            'client_id' => $this->config->tteClientId,
            'has_api_key' => !empty($this->config->tteApiKey) ? 'Ya' : 'Tidak'
        ]);

        $client = \Config\Services::curlrequest();

        try {
            $this->logger->info('Mengirim permintaan TTE ke server', [
                'document' => basename($documentPath),
                'nik' => $nik,
                'has_passphrase' => !empty($passphrase)
            ]);

            // Pastikan file ada dan bisa dibaca
            if (!is_readable($documentPath)) {
                throw new \RuntimeException("File tidak dapat dibaca: {$documentPath}");
            }

            // Persiapkan data untuk request
            $multipart = [
                [
                    'name' => 'nik',
                    'contents' => $nik
                ],
                [
                    'name' => 'passphrase',
                    'contents' => $passphrase
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($documentPath, 'r'),
                    'filename' => basename($documentPath)
                ]
            ];

            // Tambahkan opsi tambahan jika ada
            if (!empty($options['tampilan'])) {
                $multipart[] = [
                    'name' => 'tampilan',
                    'contents' => $options['tampilan']
                ];
            }

            if (!empty($options['imageTTD'])) {
                $multipart[] = [
                    'name' => 'imageTTD',
                    'contents' => fopen($options['imageTTD'], 'r'),
                    'filename' => basename($options['imageTTD'])
                ];
            }

            // Kirim request ke API TTE
            $response = $client->post($apiUrl . '/api/sign/pdf', [
                'auth' => [
                    $this->config->tteClientId,
                    $this->config->tteClientSecret
                ],
                'headers' => [
                    'X-API-Key' => $this->config->tteApiKey,
                    'Accept' => 'application/json'
                ],
                'multipart' => $multipart,
                'connect_timeout' => 30,
                'timeout' => 120,
                'http_errors' => false,
                'verify' => false // Nonaktifkan verifikasi SSL untuk development
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = (string) $response->getBody();
            $result = json_decode($responseBody, true);

            $this->logger->debug('Response dari API TTE', [
                'status_code' => $statusCode,
                'response' => $result ?: 'Tidak ada response atau bukan JSON'
            ]);

            if ($statusCode !== 200) {
                $errorMsg = $result['message'] ?? 'Status code tidak valid: ' . $statusCode;
                $this->logger->error('Gagal memproses TTE: ' . $errorMsg, [
                    'status_code' => $statusCode,
                    'response' => $result,
                    'request' => [
                        'url' => $apiUrl . '/api/sign/pdf',
                        'method' => 'POST',
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode($this->config->tteClientId . ':' . $this->config->tteClientSecret),
                            'X-API-Key' => '***' . substr($this->config->tteApiKey, -4)
                        ]
                    ]
                ]);
                throw new \RuntimeException('Gagal memproses TTE: ' . $errorMsg);
            }

            if (!isset($result['signed_file'])) {
                $this->logger->error('Format response TTE tidak valid', [
                    'response' => $result,
                    'status_code' => $statusCode
                ]);
                throw new \RuntimeException('Format response dari server TTE tidak valid');
            }

            // Simpan file hasil TTE
            $signedContent = base64_decode($result['signed_file']);
            if ($signedContent === false) {
                $this->logger->error('Gagal mendekode konten hasil TTE', [
                    'response' => $result
                ]);
                throw new \RuntimeException('Gagal mendekode konten hasil TTE');
            }

            // Pastikan direktori tujuan ada
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                if (!mkdir($outputDir, 0777, true)) {
                    throw new \RuntimeException("Gagal membuat direktori: {$outputDir}");
                }
            }

            $bytesWritten = file_put_contents($outputPath, $signedContent);
            if ($bytesWritten === false) {
                throw new \RuntimeException('Gagal menyimpan file hasil TTE ke: ' . $outputPath);
            }

            $this->logger->info('File TTE berhasil disimpan', [
                'path' => $outputPath,
                'size' => $bytesWritten . ' bytes',
                'file_exists' => file_exists($outputPath) ? 'Ya' : 'Tidak'
            ]);

            return $outputPath;
        } catch (\Exception $e) {
            $this->logger->error('Error saat memproses TTE: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $documentPath,
                'nik' => $nik,
                'api_url' => $apiUrl . '/api/sign/pdf'
            ]);
            throw new \RuntimeException('Gagal memproses TTE: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
