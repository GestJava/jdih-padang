<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\HarmonisasiAjuanModel;
use App\Models\HarmonisasiNomorPeraturanModel;
use App\Models\HarmonisasiTteLogModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class TTEController extends BaseController
{
    use ResponseTrait;

    protected $ajuanModel;
    protected $nomorPeraturanModel;
    protected $tteLogModel;
    protected $config;
    protected $baseURL;

    public function __construct()
    {
        $this->ajuanModel = new HarmonisasiAjuanModel();
        $this->nomorPeraturanModel = new HarmonisasiNomorPeraturanModel();
        $this->tteLogModel = new HarmonisasiTteLogModel();
        
        // Load config
        $this->config = config('App');
        $this->baseURL = $this->config->baseURL;
        
        helper(['url', 'file']);
    }

    /**
     * Cek status user di TTE system
     * 
     * URL: POST /api/tte/check-status
     * Body: {nik: "1234567890123456"}
     */
    public function cekStatusUser(): ResponseInterface
    {
        try {
            // Get JSON data first
            $data = $this->request->getJSON(true);
            
            if (!$data) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
            }

            $nik = $data['nik'] ?? null;

            // Manual validation
            if (empty($nik)) {
                return $this->respond(['status' => 'error', 'message' => 'NIK is required'], 400);
            }

            if (!is_numeric($nik) || strlen($nik) !== 16) {
                return $this->respond(['status' => 'error', 'message' => 'NIK must be 16 digits'], 400);
            }

            // Log request
            $userId = $this->getCurrentUserId();
            if ($userId) {
                // KEAMANAN: NIK di-mask untuk mencegah exposure data sensitif di log
                $maskedNik = substr($nik, 0, 4) . '********' . substr($nik, -4);
                $this->logTteActivity(null, $userId, 'TTE_REQUEST', 'PENDING', null, null, null, 'Checking user status for NIK: ' . $maskedNik);
            }

            // Prepare request
            $apiUrl = $this->getTteApiUrl() . '/user/check/status';
            $payload = ['nik' => $nik];
            
            // DEVELOPMENT MODE: Gunakan mock service jika di environment development
            if (ENVIRONMENT === 'development' || (defined('ENVIRONMENT') && ENVIRONMENT === 'development')) {
                log_message('info', 'TTE [DEVELOPMENT]: Using mock service for user status check.');
                $testingService = new \App\Services\TteTestingService();
                $response = $testingService->mockCheckUserStatus($nik);
            } else {
                // Make API request ke real server
                $response = $this->makeTteApiRequest('POST', $apiUrl, $payload);
            }

            if ($response === false) {
                $error = $this->getLastError();
                if ($userId) {
                    $this->logTteActivity(null, $userId, 'TTE_REQUEST', 'FAILED', null, null, $error['message'] ?? 'Unknown error');
                }
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Gagal terhubung ke server BSrE: ' . ($error['message'] ?? 'Network Error')
                ], $error['status'] ?? 500);
            }

            // Normalize response format sesuai requirement
            // Status code 1111 = Aktif (jika response 200)
            // Status code 2011 = Tidak Aktif / Unregistered
            $statusCode = $response['status_code'] ?? null;
            $status = $response['status'] ?? 'UNKNOWN';
            $message = $response['message'] ?? 'Status tidak diketahui';

            // Jika status_code 1111 dan response 200, berarti user aktif
            // Jika status_code 2011, berarti user tidak aktif atau unregistered
            $isActive = false;
            if ($statusCode === '1111' || $statusCode === 1111) {
                $isActive = true;
            } elseif ($statusCode === '2011' || $statusCode === 2011) {
                $isActive = false;
            } else{
                $isActive = false;
            }

            $normalizedResponse = [
                'status_code' => $statusCode,
                'message' => $message,
                'status' => $status,
                'is_active' => $isActive
            ];

            // Log success
            if ($userId) {
                $this->logTteActivity(null, $userId, 'TTE_REQUEST', 'SUCCESS', null, null, null);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $normalizedResponse
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in cekStatusUser: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verify document
     * 
     * URL: POST /api/tte/verify
     * Body: {file: "base64_encoded_pdf"}
     */
    public function verifyDocument(): ResponseInterface
    {
        try {
            // Validasi input
            $rules = [
                'file' => 'required|string'
            ];

            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                return $this->respond(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }

            $data = $this->request->getJSON(true);
            $fileBase64 = $data['file'] ?? null;

            if (!$fileBase64) {
                return $this->respond(['status' => 'error', 'message' => 'File is required'], 400);
            }

            // Log request
            $userId = $this->getCurrentUserId();
            if ($userId) {
                $this->logTteActivity(null, $userId, 'TTE_REQUEST', 'PENDING', null, null, null, 'Verifying document');
            }

            // Prepare request
            $apiUrl = $this->getTteApiUrl() . '/verify/pdf';
            $payload = ['file' => $fileBase64];
            
            // DEVELOPMENT MODE: Gunakan mock response jika di environment development
            if (ENVIRONMENT === 'development' || (defined('ENVIRONMENT') && ENVIRONMENT === 'development')) {
                log_message('info', 'TTE [DEVELOPMENT]: Using mock response for document verification.');
                $response = [
                    'status_code' => 200,
                    'message' => 'Document verified successfully (MOCK)',
                    'result' => 'VALID',
                    'details' => [
                        'signer' => 'User JDIH Mock',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'is_valid' => true
                    ]
                ];
            } else {
                // Make API request ke real server
                $response = $this->makeTteApiRequest('POST', $apiUrl, $payload);
            }

            if ($response === false) {
                $error = $this->getLastError();
                if ($userId) {
                    $this->logTteActivity(null, $userId, 'TTE_REQUEST', 'FAILED', null, null, $error['message'] ?? 'Unknown error');
                }
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Gagal verifikasi dokumen: ' . ($error['message'] ?? 'Network Error')
                ], $error['status'] ?? 500);
            }

            // Log success
            if ($userId) {
                $this->logTteActivity(null, $userId, 'TTE_REQUEST', 'SUCCESS', null, null, null);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in verifyDocument: ' . $e->getMessage());
            return $this->failServerError('Internal server error: ' . $e->getMessage());
        }
    }

    /**
     * Sign document
     * 
     * URL: POST /api/tte/sign
     * Body: {
     *   id_ajuan: 1,
     *   nik: "1234567890123456",
     *   password: "password123",
     *   id_dokumen: 123
     * }
     */
    public function signDocument(): ResponseInterface
    {
        /**
         * KEBIJAKAN KEAMANAN KREDENSIAL TTE:
         * =====================================
         * 1. NIK dan Password/Passphrase TIDAK PERNAH disimpan di database lokal
         * 2. NIK dan Password hanya digunakan untuk forward ke BSrE API
         * 3. NIK di-mask saat di-log (contoh: 1234********5678)
         * 4. Password TIDAK PERNAH ditulis ke log file
         * 5. Data kredensial hanya ada di memory PHP selama request berlangsung
         * 6. Setelah request selesai, data credential otomatis hilang dari memory
         * 7. Komunikasi dengan BSrE menggunakan HTTPS (encrypted in transit)
         */
        try {
            // Get JSON data first
            $data = $this->request->getJSON(true);
            
            if (!$data) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
            }

            $idAjuan = $data['id_ajuan'] ?? null;
            $nik = $data['nik'] ?? null;
            // KEAMANAN: Password hanya disimpan di variabel lokal, tidak ke database/log
            $password = $data['password'] ?? null;
            $idDokumen = $data['id_dokumen'] ?? null;

            // Manual validation
            if (empty($idAjuan) || !is_numeric($idAjuan)) {
                return $this->respond(['status' => 'error', 'message' => 'id_ajuan is required and must be numeric'], 400);
            }

            if (empty($nik) || !is_numeric($nik) || strlen($nik) !== 16) {
                return $this->respond(['status' => 'error', 'message' => 'NIK is required and must be 16 digits'], 400);
            }

            if (empty($password) || strlen($password) < 8) {
                return $this->respond(['status' => 'error', 'message' => 'Password is required and must be at least 8 characters'], 400);
            }

            if (empty($idDokumen) || !is_numeric($idDokumen)) {
                return $this->respond(['status' => 'error', 'message' => 'id_dokumen is required and must be numeric'], 400);
            }

            // Get dokumen dari database berdasarkan id_dokumen
            $dokumenModel = new \App\Models\HarmonisasiDokumenModel();
            $dokumen = $dokumenModel->find($idDokumen);

            log_message('debug', 'TTE SignDocument - Dokumen lookup: id_dokumen=' . $idDokumen . ', found=' . ($dokumen ? 'yes' : 'no'));

            if (!$dokumen) {
                log_message('error', 'TTE SignDocument - Dokumen not found: id_dokumen=' . $idDokumen);
                return $this->respond(['status' => 'error', 'message' => 'Dokumen not found with id_dokumen: ' . $idDokumen'], 404);
            }

            log_message('debug', 'TTE SignDocument - Dokumen data: ' . json_encode([
                'id' => $dokumen['id'] ?? null,
                'id_ajuan' => $dokumen['id_ajuan'] ?? null,
                'tipe_dokumen' => $dokumen['tipe_dokumen'] ?? null,
                'path_file_storage' => $dokumen['path_file_storage'] ?? null
            ]));

            // Validasi dokumen adalah FINAL_PARAF
            if ($dokumen['tipe_dokumen'] !== 'FINAL_PARAF') {
                log_message('error', 'TTE SignDocument - Invalid document type: ' . ($dokumen['tipe_dokumen'] ?? 'null') . ', expected: FINAL_PARAF');
                return $this->respond(['status' => 'error', 'message' => 'Dokumen harus berjenis FINAL_PARAF, found: ' . ($dokumen['tipe_dokumen'] ?? 'null')], 400);
            }

            // Validasi dokumen milik ajuan yang benar
            if ($dokumen['id_ajuan'] != $idAjuan) {
                log_message('error', 'TTE SignDocument - Document ajuan mismatch: dokumen_id_ajuan=' . ($dokumen['id_ajuan'] ?? 'null') . ', request_id_ajuan=' . $idAjuan);
                return $this->respond(['status' => 'error', 'message' => 'Dokumen tidak sesuai dengan ajuan. Dokumen id_ajuan: ' . ($dokumen['id_ajuan'] ?? 'null') . ', Request id_ajuan: ' . $idAjuan], 400);
            }

            // Get file path dan construct full path
            $filePath = $dokumen['path_file_storage'] ?? null;
            
            if (empty($filePath)) {
                log_message('error', 'TTE SignDocument - path_file_storage is empty for dokumen id: ' . $idDokumen);
                return $this->respond(['status' => 'error', 'message' => 'File path tidak ditemukan di database'], 404);
            }

            log_message('debug', 'TTE SignDocument - Original path_file_storage: ' . $filePath);

            // Normalize path - remove 'uploads/' prefix if exists
            if (strpos($filePath, 'uploads/') === 0) {
                $filePath = substr($filePath, 8);
            }
            // Remove 'writable/' prefix if exists
            if (strpos($filePath, 'jdih/writable/') === 0) {
                $filePath = substr($filePath, 10);
            }

            // Construct full path
            $documentPath = WRITEPATH . 'uploads/' . ltrim($filePath, '/');
            $documentPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $documentPath);
            $documentPath = realpath($documentPath) ?: $documentPath; // Resolve any .. or . in path

            log_message('debug', 'TTE SignDocument - Constructed document path: ' . $documentPath);
            log_message('debug', 'TTE SignDocument - File exists: ' . (file_exists($documentPath) ? 'yes' : 'no'));

            if (!file_exists($documentPath)) {
                log_message('error', 'TTE SignDocument - File not found: ' . $documentPath);
                log_message('error', 'TTE SignDocument - WRITEPATH: ' . WRITEPATH);
                log_message('error', 'TTE SignDocument - Original path_file_storage: ' . $dokumen['path_file_storage']);
                return $this->respond(['status' => 'error', 'message' => 'File dokumen tidak ditemukan di server: ' . $documentPath], 404);
            }

            // Check file size
            $fileSize = filesize($documentPath);
            log_message('debug', 'TTE SignDocument - File size: ' . $fileSize . ' bytes');

            if ($fileSize === 0) {
                log_message('error', 'TTE SignDocument - File is empty: ' . $documentPath);
                return $this->respond(['status' => 'error', 'message' => 'File dokumen kosong'], 400);
            }

            // Validate it's a PDF file
            $fileContent = file_get_contents($documentPath, false, null, 0, 4);
            if ($fileContent !== '%PDF') {
                log_message('error', 'TTE SignDocument - File is not a valid PDF. Header: ' . bin2hex($fileContent));
                return $this->respond(['status' => 'error', 'message' => 'File bukan PDF yang valid'], 400);
            }

            log_message('debug', 'TTE SignDocument - File is valid PDF');

            // Get current user
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->respond(['status' => 'error', 'message' => 'User not authenticated'], 401);
            }

            // Get ajuan data untuk mendapatkan jenis_peraturan
            $ajuan = $this->ajuanModel->getAjuanDetail($idAjuan);
            if (!$ajuan) {
                return $this->respond(['status' => 'error', 'message' => 'Ajuan not found'], 404);
            }

            // Get jenis peraturan dari ajuan
            $jenisPeraturan = $ajuan['nama_jenis'] ?? null;
            if (!$jenisPeraturan) {
                return $this->respond(['status' => 'error', 'message' => 'Jenis peraturan not found for this ajuan'], 404);
            }

            // Get atau generate nomor peraturan
            $nomorPeraturan = $this->nomorPeraturanModel->getByAjuanAndJenis($idAjuan, $jenisPeraturan);
            
            // Jika nomor peraturan belum ada, generate baru
            if (!$nomorPeraturan) {
                $pdfEnhancementService = new \App\Services\PdfEnhancementService();
                $urutan = $pdfEnhancementService->getNextNumberForJenis($jenisPeraturan);
                $nomorPeraturanText = $pdfEnhancementService->generateNomorPeraturan($jenisPeraturan, $urutan);
                $tanggalPengesahan = $pdfEnhancementService->formatTanggalIndonesia();
                
                // Generate document URL - SELALU prioritaskan URL file langsung jika file TTE sudah ada
                $documentUrl = null;
                // Cek filesystem untuk file TTE yang sudah ada
                $possibleTteFile = WRITEPATH . 'uploads/harmonisasi/tte/tte_' . $idAjuan . '_*.pdf';
                $tteFiles = glob($possibleTteFile);
                if (!empty($tteFiles)) {
                    // Gunakan file TTE terbaru (path langsung ke file)
                    $latestTteFile = $tteFiles[count($tteFiles) - 1];
                    // Pastikan path relatif mengandung 'writable/' untuk URL yang benar
                    $relativePath = str_replace(WRITEPATH, '', $latestTteFile);
                    // Jika relativePath tidak mengandung 'writable/', tambahkan
                    if (strpos($relativePath, 'writable/') === false) {
                        $relativePath = 'writable/' . ltrim($relativePath, '/');
                    }
                    $documentUrl = rtrim($this->baseURL, '/') . '/jdih/' . $relativePath;
                    log_message('info', 'TTE SignDocument - Found existing TTE file in filesystem, using direct file URL: ' . $documentUrl);
                } else {
                    // Fallback: gunakan route download (untuk file yang belum di-sign)
                    $documentUrl = rtrim($this->baseURL, '/') . '/legalisasi/download/' . $idAjuan;
                    // Fix URL jika masih mengandung /jdih/legalisasi
                    if (strpos($documentUrl, '/jdih/legalisasi') !== false) {
                        $documentUrl = str_replace('/jdih/legalisasi', '/legalisasi', $documentUrl);
                    }
                    log_message('info', 'TTE SignDocument - Using route download URL (file not signed yet): ' . $documentUrl);
                }
                
                $userRole = $this->getCurrentUserRole();

                // Simpan nomor peraturan ke database
                $nomorPeraturanData = [
                    'id_ajuan' => $idAjuan,
                    'jenis_peraturan' => $jenisPeraturan,
                    'nomor_peraturan' => $nomorPeraturanText,
                    'urutan' => $urutan,
                    'tahun' => date('Y'),
                    'tanggal_pengesahan' => date('Y-m-d'),
                    'user_role' => $userRole,
                    'document_url' => $documentUrl
                ];

                $nomorPeraturanId = $this->nomorPeraturanModel->insert($nomorPeraturanData);
                if (!$nomorPeraturanId) {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to create nomor peraturan'], 500);
                }

                // Get kembali data yang baru dibuat
                $nomorPeraturan = $this->nomorPeraturanModel->getByAjuanAndJenis($idAjuan, $jenisPeraturan);
                if (!$nomorPeraturan) {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to retrieve created nomor peraturan'], 500);
                }
            }

            $documentNumber = $nomorPeraturan['nomor_peraturan'] ?? null;

            // Enhance PDF dengan QR code, nomor, dan tanggal sebelum signing
            // REQUIRED: Enhancement harus berhasil sebelum bisa lanjut ke signing
            $enhancedPdfPath = null;
            $originalPdfPath = $documentPath; // Keep reference untuk cleanup
            $predictedFilename = null; // Filename yang diprediksi untuk konsistensi QR code
            try {
                log_message('info', 'TTE SignDocument - Starting PDF enhancement with QR code...');
                
                $pdfEnhancementService = new \App\Services\PdfEnhancementService();
                
                // Prepare metadata untuk enhancement
                $nomorPeraturanText = $nomorPeraturan['nomor_peraturan'] ?? '1';
                $tanggalPengesahan = $pdfEnhancementService->validateAndFixTanggalPengesahan(
                    $nomorPeraturan['tanggal_pengesahan'] ?? date('Y-m-d')
                );
                
                // Get document URL - SELALU prioritaskan URL file langsung jika file TTE sudah ada di filesystem
                $documentUrl = null;
                
                // PRIORITAS 1: Cek filesystem untuk file TTE yang sudah ada (paling akurat)
                $possibleTteFile = WRITEPATH . 'uploads/harmonisasi/tte/tte_' . $idAjuan . '_*.pdf';
                $tteFiles = glob($possibleTteFile);
                if (!empty($tteFiles)) {
                    // Gunakan file TTE terbaru (file terakhir biasanya terbaru)
                    $latestTteFile = $tteFiles[count($tteFiles) - 1];
                    // Pastikan path relatif mengandung 'writable/' untuk URL yang benar
                    // Format: jdih/writable/uploads/harmonisasi/tte/tte_xxx.pdf
                    $relativePath = str_replace(WRITEPATH, '', $latestTteFile);
                    // Jika relativePath tidak mengandung 'writable/', tambahkan
                    if (strpos($relativePath, 'writable/') === false) {
                        $relativePath = 'writable/' . ltrim($relativePath, '/');
                    }
                    $documentUrl = rtrim($this->baseURL, '/') . '/jdih/' . $relativePath;
                    log_message('info', 'TTE SignDocument - Found TTE file in filesystem, using direct file URL: ' . $documentUrl);
                }
                // PRIORITAS 2: Jika tidak ada di filesystem, cek tte_file_path di database
                elseif (!empty($nomorPeraturan['tte_file_path'])) {
                    // Gunakan path file TTE langsung dari database
                    $tteFilePath = $nomorPeraturan['tte_file_path'];
                    // Pastikan path sudah benar (tambah jdih/ jika belum ada)
                    if (strpos($tteFilePath, 'jdih/') === false && strpos($tteFilePath, 'writable/') === 0) {
                        $documentUrl = rtrim($this->baseURL, '/') . '/jdih/' . $tteFilePath;
                    } else {
                        $documentUrl = rtrim($this->baseURL, '/') . '/' . ltrim($tteFilePath, '/');
                    }
                    log_message('info', 'TTE SignDocument - Using TTE file path from database: ' . $documentUrl);
                }
                // PRIORITAS 3: Gunakan document_url dari database (jika bukan route download)
                elseif (!empty($nomorPeraturan['document_url'])) {
                    $documentUrl = $nomorPeraturan['document_url'];
                    // Fix URL jika masih mengandung /jdih/legalisasi (route download)
                    if (strpos($documentUrl, '/jdih/legalisasi') !== false) {
                        $documentUrl = str_replace('/jdih/legalisasi', '/legalisasi', $documentUrl);
                    }
                    // Jika masih route download, abaikan dan gunakan fallback
                    if (strpos($documentUrl, '/legalisasi/download/') !== false) {
                        log_message('warning', 'TTE SignDocument - document_url is route download, will use fallback');
                        $documentUrl = null; // Reset untuk gunakan fallback
                    } else {
                        log_message('info', 'TTE SignDocument - Using document_url from database: ' . $documentUrl);
                    }
                }
                
                // PRIORITAS 4: Buat URL file TTE yang akan dibuat (format sama seperti saveSignedDocument)
                // Ini memastikan QR code menggunakan URL file langsung, bukan route download
                // Simpan filename untuk digunakan di saveSignedDocument nanti
                $predictedFilename = null;
                if (empty($documentUrl)) {
                    // Generate filename dengan format yang sama seperti saveSignedDocument
                    // Format: tte_{idAjuan}_{timestamp}_{uniqid}.pdf
                    // Gunakan timestamp dan uniqid yang sama untuk konsistensi
                    $currentTime = time();
                    $currentUniqid = uniqid();
                    $predictedFilename = 'tte_' . $idAjuan . '_' . $currentTime . '_' . $currentUniqid . '.pdf';
                    $relativePath = 'jdih/writable/uploads/harmonisasi/tte/' . $predictedFilename;
                    $documentUrl = rtrim($this->baseURL, '/') . '/' . $relativePath;
                    log_message('info', 'TTE SignDocument - Using predicted TTE file URL for QR code: ' . $documentUrl);
                    log_message('info', 'TTE SignDocument - Predicted filename: ' . $predictedFilename);
                }
                
                log_message('debug', 'TTE SignDocument - Final document URL for QR code: ' . $documentUrl);
                
                log_message('debug', 'TTE SignDocument - Enhancement metadata:');
                log_message('debug', 'TTE SignDocument - Nomor: ' . $nomorPeraturanText);
                log_message('debug', 'TTE SignDocument - Tanggal: ' . $tanggalPengesahan);
                log_message('debug', 'TTE SignDocument - URL: ' . $documentUrl);
                
                // Enhance PDF dengan QR code - REQUIRED, tidak boleh gagal
                $enhancedPdfPath = $pdfEnhancementService->enhancePdf(
                    $documentPath,
                    $nomorPeraturanText,
                    $tanggalPengesahan,
                    $documentUrl,
                    80 // Quality
                );
                
                if (!$enhancedPdfPath || !file_exists($enhancedPdfPath)) {
                    log_message('error', 'TTE SignDocument - PDF enhancement FAILED. Enhancement is required before signing.');
                    log_message('error', 'TTE SignDocument - Please ensure Python and PyMuPDF are installed correctly.');
                    log_message('error', 'TTE SignDocument - Run: bash jdih/app/PdfTools/setup_python_dependencies.sh');
                    
                    // Error message yang lebih informatif
                    $errorMessage = 'PDF enhancement gagal. Proses TTE memerlukan QR code generation yang gagal. ';
                    $errorMessage .= 'Kemungkinan penyebab: ';
                    $errorMessage .= '1) Python tidak terinstall atau tidak ada di PATH sistem, ';
                    $errorMessage .= '2) Library QR Code (Endroid) tidak tersedia, ';
                    $errorMessage .= '3) Google Charts API tidak dapat diakses (404). ';
                    $errorMessage .= 'Silakan hubungi administrator untuk menginstall Python dan library QR code yang diperlukan, atau pastikan koneksi internet tersedia untuk Google Charts API.';
                    
                    return $this->respond([
                        'status' => 'error',
                        'message' => $errorMessage
                    ], 500);
                }
                
                // Verify enhanced PDF is valid
                $enhancedFileContent = file_get_contents($enhancedPdfPath, false, null, 0, 4);
                if ($enhancedFileContent !== '%PDF') {
                    log_message('error', 'TTE SignDocument - Enhanced PDF is not a valid PDF file');
                    if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                        @unlink($enhancedPdfPath);
                    }
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'Enhanced PDF tidak valid. Proses enhancement gagal.'
                    ], 500);
                }
                
                log_message('info', 'TTE SignDocument - PDF enhanced successfully: ' . $enhancedPdfPath);
                $documentPath = $enhancedPdfPath; // Use enhanced PDF for signing
                
            } catch (\Exception $e) {
                log_message('error', 'TTE SignDocument - PDF enhancement exception: ' . $e->getMessage());
                log_message('error', 'TTE SignDocument - Stack trace: ' . $e->getTraceAsString());
                
                // Cleanup jika ada file yang terbuat
                if (isset($enhancedPdfPath) && $enhancedPdfPath && file_exists($enhancedPdfPath)) {
                    @unlink($enhancedPdfPath);
                }
                
                // Return valid JSON error response dengan pesan yang lebih informatif
                $errorMessage = 'PDF enhancement error: ' . $e->getMessage();
                
                // Deteksi jenis error untuk memberikan solusi yang tepat
                $exceptionMessage = $e->getMessage();
                if (strpos($exceptionMessage, 'QR code generation failed') !== false || 
                    strpos($exceptionMessage, 'QR Code Generation Error') !== false ||
                    strpos($exceptionMessage, 'Semua metode QR code generation gagal') !== false) {
                    $errorMessage = 'QR code generation gagal. ';
                    if (strpos($exceptionMessage, 'Python was not found') !== false || strpos($exceptionMessage, 'Python tidak ditemukan') !== false) {
                        $errorMessage .= 'Python tidak terinstall atau tidak ada di PATH sistem. ';
                    }
                    if (strpos($exceptionMessage, 'Google Charts') !== false && strpos($exceptionMessage, '404') !== false) {
                        $errorMessage .= 'Google Charts API tidak dapat diakses (404). ';
                    }
                    if (strpos($exceptionMessage, 'Endroid') !== false && strpos($exceptionMessage, 'not available') !== false) {
                        $errorMessage .= 'Library QR Code (Endroid) tidak tersedia. ';
                    }
                    $errorMessage .= 'Silakan hubungi administrator untuk menginstall Python dan library QR code yang diperlukan, atau pastikan koneksi internet tersedia untuk Google Charts API.';
                } elseif (strpos($exceptionMessage, 'Endroid') !== false || strpos($exceptionMessage, 'QrCode') !== false) {
                    $errorMessage .= ' Library QR Code tidak tersedia. Silakan install library Endroid QR Code atau pastikan koneksi internet tersedia untuk Google Charts API.';
                } else {
                    $errorMessage .= ' Pastikan Python dan PyMuPDF sudah terinstall dengan benar. Silakan jalankan setup script: bash jdih/app/PdfTools/setup_python_dependencies.sh';
                }
                
                return $this->respond([
                    'status' => 'error',
                    'message' => $errorMessage
                ], 500);
            }

            log_message('debug', 'TTE SignDocument - Starting base64 encoding for: ' . $documentPath);

            // Encode enhanced PDF ke base64 (enhancement sudah required, jadi selalu enhanced PDF)
            $fileBase64 = $this->encodePdfToBase64($documentPath);
            
            if ($fileBase64 === false) {
                // Cleanup enhanced PDF jika ada sebelum return error
                if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                    @unlink($enhancedPdfPath);
                }
                log_message('error', 'TTE SignDocument - Failed to encode PDF to base64: ' . $documentPath);
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to encode PDF file to base64'
                ], 500);
            }

            $base64Length = strlen($fileBase64);
            log_message('debug', 'TTE SignDocument - Base64 encoded successfully. Length: ' . $base64Length . ' characters');
            
            if ($base64Length === 0) {
                // Cleanup enhanced PDF jika ada sebelum return error
                if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                    @unlink($enhancedPdfPath);
                }
                log_message('error', 'TTE SignDocument - Base64 string is empty');
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Base64 encoded string is empty'
                ], 500);
            }

            // Log request (signed_path akan diisi setelah file diupload)
            $logId = $this->logTteActivity($idAjuan, $userId, 'TTE_REQUEST', 'PENDING', $documentNumber, null, null, 'Signing document');

            // Prepare signature properties
            $signatureProperties = [
                [
                    'imageBase64' => '',
                    'tampilan' => 'INVISIBLE',
                    'page' => 1,
                    'originX' => 0.0,
                    'originY' => 0.0,
                    'width' => 100.0,
                    'height' => 75.0
                ]
            ];

            // Prepare request payload
            $apiUrl = $this->getTteApiUrl() . '/sign/pdf';
            $payload = [
                'nik' => $nik,
                'passphrase' => $password,
                'signatureProperties' => $signatureProperties,
                'file' => [$fileBase64] // Array dengan base64 string sebagai elemen pertama
            ];

            log_message('debug', 'TTE SignDocument - Payload prepared:');
            log_message('debug', 'TTE SignDocument - API URL: ' . $apiUrl);
            // KEAMANAN: NIK di-mask untuk mencegah exposure data sensitif di log
            $maskedNik = substr($nik, 0, 4) . '********' . substr($nik, -4);
            log_message('debug', 'TTE SignDocument - NIK (masked): ' . $maskedNik);
            log_message('debug', 'TTE SignDocument - File array count: ' . count($payload['file']));
            log_message('debug', 'TTE SignDocument - File base64 length in payload: ' . strlen($payload['file'][0]) . ' characters');
            // CATATAN KEAMANAN: Password/passphrase TIDAK PERNAH di-log

            // DEVELOPMENT MODE: Gunakan mock service jika di environment development
            if (ENVIRONMENT === 'development' || (defined('ENVIRONMENT') && ENVIRONMENT === 'development')) {
                log_message('info', 'TTE [DEVELOPMENT]: Using mock service for document signing.');
                $testingService = new \App\Services\TteTestingService();
                $response = $testingService->mockSignDocument($nik, $password, $documentPath);
            } else {
                // Make API request ke real server
                $response = $this->makeTteApiRequest('POST', $apiUrl, $payload);
            }

            if ($response === false) {
                // Cleanup enhanced PDF jika ada sebelum return error
                if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                    @unlink($enhancedPdfPath);
                    log_message('debug', 'TTE SignDocument - Cleaned up enhanced PDF after signing failure');
                }
                $error = $this->getLastError();
                $this->logTteActivity($idAjuan, $userId, 'TTE_FAILED', 'FAILED', $documentNumber, null, $error['message'] ?? 'Unknown error', null, $logId);
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Gagal proses TTE: ' . ($error['message'] ?? 'Failed to sign document')
                ], $error['status'] ?? 500);
            }

            // Check response status
            if (!isset($response['file'])) {
                // Cleanup enhanced PDF jika ada sebelum return error
                if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                    @unlink($enhancedPdfPath);
                    log_message('debug', 'TTE SignDocument - Cleaned up enhanced PDF after invalid response');
                }
                $errorMsg = 'Invalid response from TTE API: file not found in response';
                $this->logTteActivity($idAjuan, $userId, 'TTE_FAILED', 'FAILED', $documentNumber, null, $errorMsg, null, $logId);
                return $this->respond(['status' => 'error', 'message' => $errorMsg], 500);
            }

            // Get signed file (base64) from response - always get first element from array
            $fileData = $response['file'];
            
            if (is_array($fileData)) {
                if (empty($fileData) || !isset($fileData[0])) {
                    $errorMsg = 'Invalid response from TTE API: file array is empty';
                    $this->logTteActivity($idAjuan, $userId, 'TTE_FAILED', 'FAILED', $documentNumber, null, $errorMsg, null, $logId);
                    return $this->respond(['status' => 'error', 'message' => $errorMsg], 500);
                }
                $signedFileBase64 = $fileData[0];
            } else {
                $signedFileBase64 = $fileData;
            }

            // Validate base64 string is not empty
            if (empty($signedFileBase64) || !is_string($signedFileBase64)) {
                // Cleanup enhanced PDF jika ada sebelum return error
                if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                    @unlink($enhancedPdfPath);
                    log_message('debug', 'TTE SignDocument - Cleaned up enhanced PDF after invalid file content');
                }
                $errorMsg = 'Invalid response from TTE API: file content is empty or invalid';
                $this->logTteActivity($idAjuan, $userId, 'TTE_FAILED', 'FAILED', $documentNumber, null, $errorMsg, null, $logId);
                return $this->respond(['status' => 'error', 'message' => $errorMsg], 500);
            }

            // Decode base64 to PDF and save
            // Gunakan predicted filename jika ada (untuk konsistensi dengan QR code)
            $uploadPath = $this->saveSignedDocument($signedFileBase64, $idAjuan, $jenisPeraturan, $predictedFilename ?? null);
            
            if (!$uploadPath) {
                // Cleanup enhanced PDF jika ada sebelum return error
                if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                    @unlink($enhancedPdfPath);
                    log_message('debug', 'TTE SignDocument - Cleaned up enhanced PDF after save failure');
                }
                $errorMsg = 'Failed to save signed document';
                $this->logTteActivity($idAjuan, $userId, 'TTE_FAILED', 'FAILED', $documentNumber, null, $errorMsg, null, $logId);
                return $this->respond(['status' => 'error', 'message' => $errorMsg], 500);
            }

            // Cleanup temporary enhanced PDF setelah signing berhasil
            if ($enhancedPdfPath && file_exists($enhancedPdfPath)) {
                @unlink($enhancedPdfPath);
                log_message('debug', 'TTE SignDocument - Cleaned up temporary enhanced PDF after successful signing');
            }

            // Update log with signed_path
            $this->logTteActivity($idAjuan, $userId, 'TTE_REQUEST', 'PENDING', $documentNumber, $uploadPath, null, null, $logId);

            // Get user role
            $userRole = $this->getCurrentUserRole();

            // Prepare update data
            // Generate document_url langsung ke file PDF (bukan route download)
            // uploadPath format: jdih/writable/uploads/harmonisasi/tte/tte_4352_xxx.pdf
            $documentUrl = rtrim($this->baseURL, '/') . '/' . ltrim($uploadPath, '/');
            
            $updateData = [
                'tte_file_path' => $uploadPath, // relative path: jdih/writable/uploads/harmonisasi/tte/tte_xxx.pdf
                'tte_completed_at' => date('Y-m-d H:i:s'),
                'tte_user_role' => $userRole,
                'document_url' => $documentUrl // full URL langsung ke file PDF
            ];
            
            log_message('info', 'TTE SignDocument - Updated document_url to direct file URL: ' . $documentUrl);

            // Update nomor peraturan
            $updateResult = $this->nomorPeraturanModel->updateTteInfo($idAjuan, $jenisPeraturan, $updateData);

            if (!$updateResult) {
                // Rollback: delete uploaded file
                if (file_exists(WRITEPATH . 'uploads/harmonisasi/tte/' . basename($uploadPath))) {
                    @unlink(WRITEPATH . 'uploads/harmonisasi/tte/' . basename($uploadPath));
                }
                $errorMsg = 'Failed to update nomor peraturan';
                $this->logTteActivity($idAjuan, $userId, 'TTE_FAILED', 'FAILED', $documentNumber, $uploadPath, $errorMsg, null, $logId);
                return $this->respond(['status' => 'error', 'message' => $errorMsg], 500);
            }

            // Update harmonisasi_ajuan status = 14 (SELESAI)
            $ajuanUpdateResult = $this->ajuanModel->update($idAjuan, [
                'id_status_ajuan' => 14, // Status SELESAI
                'tanggal_selesai' => date('Y-m-d H:i:s')
            ]);

            if (!$ajuanUpdateResult) {
                log_message('warning', 'Failed to update harmonisasi_ajuan status for ajuan: ' . $idAjuan);
            }

            // Log success
            $this->logTteActivity($idAjuan, $userId, 'TTE_COMPLETE', 'SUCCESS', $documentNumber, $uploadPath, null, null, $logId);

            return $this->respond([
                'status' => 'success',
                'message' => 'Document signed successfully',
                'data' => [
                    'id_ajuan' => $idAjuan,
                    'document_number' => $documentNumber,
                    'signed_path' => $uploadPath,
                    'document_url' => $updateData['document_url'],
                    'tte_file_path' => $uploadPath,
                    'tte_completed_at' => $updateData['tte_completed_at']
                ]
            ]);

        } catch (\Exception $e) {
            // Cleanup enhanced PDF jika ada sebelum return error
            if (isset($enhancedPdfPath) && $enhancedPdfPath && file_exists($enhancedPdfPath)) {
                @unlink($enhancedPdfPath);
                log_message('debug', 'TTE SignDocument - Cleaned up enhanced PDF after exception');
            }
            log_message('error', 'Error in signDocument: ' . $e->getMessage());
            log_message('error', 'Error stack trace: ' . $e->getTraceAsString());
            return $this->failServerError('Internal server error: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Encode PDF file to base64
     *
     * @param string $filePath Path to PDF file
     * @return string|false Base64 encoded string or false on failure
     */
    private function encodePdfToBase64($filePath)
    {
        try {
            log_message('debug', 'encodePdfToBase64 - Starting: ' . $filePath);

            if (!file_exists($filePath)) {
                log_message('error', 'encodePdfToBase64 - File not found: ' . $filePath);
                return false;
            }

            if (!is_readable($filePath)) {
                log_message('error', 'encodePdfToBase64 - File is not readable: ' . $filePath);
                return false;
            }

            $fileSize = filesize($filePath);
            log_message('debug', 'encodePdfToBase64 - File size: ' . $fileSize . ' bytes');

            if ($fileSize === 0) {
                log_message('error', 'encodePdfToBase64 - File is empty: ' . $filePath);
                return false;
            }

            // Read file content
            $fileContent = file_get_contents($filePath);
            
            if ($fileContent === false) {
                log_message('error', 'encodePdfToBase64 - Failed to read file: ' . $filePath);
                return false;
            }

            $contentLength = strlen($fileContent);
            log_message('debug', 'encodePdfToBase64 - Content length: ' . $contentLength . ' bytes');

            if ($contentLength === 0) {
                log_message('error', 'encodePdfToBase64 - File content is empty after reading');
                return false;
            }

            // Validate PDF header
            if (substr($fileContent, 0, 4) !== '%PDF') {
                log_message('error', 'encodePdfToBase64 - File is not a valid PDF. Header: ' . bin2hex(substr($fileContent, 0, 10)));
                return false;
            }

            // Encode to base64
            $base64String = base64_encode($fileContent);
            
            if ($base64String === false || empty($base64String)) {
                log_message('error', 'encodePdfToBase64 - Failed to encode to base64');
                return false;
            }

            $base64Length = strlen($base64String);
            log_message('debug', 'encodePdfToBase64 - Base64 string length: ' . $base64Length . ' characters');
            log_message('debug', 'encodePdfToBase64 - Success');

            return $base64String;

        } catch (\Exception $e) {
            log_message('error', 'encodePdfToBase64 - Exception: ' . $e->getMessage());
            log_message('error', 'encodePdfToBase64 - Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Helper: Decode base64 to PDF file
     *
     * @param string $base64String Base64 encoded PDF
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    private function decodeBase64ToPdf($base64String, $outputPath)
    {
        try {
            // Remove data URI prefix if present
            $base64String = preg_replace('/^data:application\/pdf;base64,/', '', $base64String);
            
            // Trim whitespace and newlines
            $base64String = trim($base64String);
            
            // Validate base64 string
            if (empty($base64String)) {
                log_message('error', 'Base64 string is empty');
                return false;
            }
            
            $pdfContent = base64_decode($base64String, true);
            
            if ($pdfContent === false) {
                log_message('error', 'Failed to decode base64 string. String length: ' . strlen($base64String));
                return false;
            }
            
            // Validate decoded content is PDF (check PDF header)
            if (substr($pdfContent, 0, 4) !== '%PDF') {
                log_message('error', 'Decoded content is not a valid PDF file. Header: ' . substr($pdfContent, 0, 20));
                return false;
            }

            // Ensure directory exists
            $dir = dirname($outputPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    log_message('error', 'Failed to create directory: ' . $dir);
                    return false;
                }
            }

            // Write file
            $result = file_put_contents($outputPath, $pdfContent);
            
            if ($result === false) {
                log_message('error', 'Failed to write file: ' . $outputPath);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error decoding base64 to PDF: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: Get TTE API URL from environment
     *
     * @return string
     */
    private function getTteApiUrl()
    {
        // Check environment variables
        $host = $_ENV['esign.host'] ?? $_ENV['TTE_API_URL'] ?? '';
        
        if (empty($host)) {
            log_message('error', 'TTE API URL not configured');
            return '';
        }

        return rtrim($host, '/');
    }

    /**
     * Helper: Get Basic Auth credentials
     *
     * @return array [username, password]
     */
    private function getAuthCredentials()
    {
        $username = $_ENV['esign.client_id'] ?? $_ENV['TTE_CLIENT_ID'] ?? '';
        $password = $_ENV['esign.client_secret'] ?? $_ENV['TTE_CLIENT_SECRET'] ?? '';

        return [$username, $password];
    }

    /**
     * Helper: Get Authorization header
     *
     * @return string
     */
    private function getAuthHeaders()
    {
        list($username, $password) = $this->getAuthCredentials();
        return 'Basic ' . base64_encode($username . ':' . $password);
    }

    /**
     * Helper: Make TTE API request menggunakan HTTP client
     * 
     * Catatan: \Config\Services::curlrequest() adalah HTTP client CodeIgniter 4
     * yang menggunakan cURL untuk membuat HTTP request ke third party API
     *
     * @param string $method HTTP method (GET, POST, etc)
     * @param string $url Full API URL
     * @param array $data Request payload
     * @return array|false Response data or false on failure
     */
    private function makeTteApiRequest($method, $url, $data = [])
    {
        try {
            // HTTP client menggunakan cURL untuk request ke third party API
            $client = \Config\Services::curlrequest([
                'timeout' => 120,
                'connect_timeout' => 30,
                'http_errors' => false,
                'verify' => false // Disable SSL verification jika diperlukan
            ]);

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => $this->getAuthHeaders()
            ];

            $options = [
                'headers' => $headers,
                'json' => $data
            ];

            log_message('debug', 'TTE API Request - Method: ' . $method . ', URL: ' . $url);
            log_message('debug', 'TTE API Request - Payload size: ' . strlen(json_encode($data)) . ' bytes');

            // Make HTTP request ke real TTE API
            $response = $client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            $responseData = json_decode($responseBody, true);

            log_message('debug', 'TTE API Response - Status Code: ' . $statusCode);
            log_message('debug', 'TTE API Response - Body length: ' . strlen($responseBody) . ' bytes');

            if ($statusCode !== 200) {
                // Handle non-JSON error response from firewall/proxy (e.g. FortiGuard block)
                $errorMessage = (is_array($responseData) && isset($responseData['message'])) 
                    ? $responseData['message'] 
                    : $response->getReasonPhrase();
                log_message('error', 'TTE API Request Failed - Status: ' . $statusCode . ', Message: ' . $errorMessage);
                log_message('error', 'TTE API Response: ' . $responseBody);
                
                $this->lastError = [
                    'status' => $statusCode,
                    'message' => $errorMessage,
                    'response' => $responseData
                ];
                return false;
            }

            log_message('debug', 'TTE API Request - Success');
            return $responseData;

        } catch (\Exception $e) {
            log_message('error', 'TTE API Request Exception: ' . $e->getMessage());
            log_message('error', 'TTE API Request Stack Trace: ' . $e->getTraceAsString());
            
            $this->lastError = [
                'status' => 500,
                'message' => $e->getMessage()
            ];
            return false;
        }
    }


    /**
     * Helper: Save signed document
     *
     * @param string $base64Content Base64 encoded PDF
     * @param int $idAjuan
     * @param string $jenisPeraturan
     * @param string|null $predictedFilename Filename yang sudah diprediksi sebelumnya (untuk konsistensi dengan QR code)
     * @return string|false Relative path or false on failure
     */
    private function saveSignedDocument($base64Content, $idAjuan, $jenisPeraturan, $predictedFilename = null)
    {
        try {
            // Create upload directory if not exists
            $uploadDir = WRITEPATH . 'uploads/harmonisasi/tte/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    log_message('error', 'Failed to create upload directory: ' . $uploadDir);
                    return false;
                }
            }

            // Gunakan predicted filename jika ada, jika tidak generate baru
            if ($predictedFilename && !empty($predictedFilename)) {
                $filename = $predictedFilename;
                log_message('info', 'TTE SignDocument - Using predicted filename: ' . $filename);
            } else {
                // Generate unique filename
                $filename = 'tte_' . $idAjuan . '_' . time() . '_' . uniqid() . '.pdf';
                log_message('info', 'TTE SignDocument - Generated new filename: ' . $filename);
            }
            $fullPath = $uploadDir . $filename;

            // Decode and save
            if (!$this->decodeBase64ToPdf($base64Content, $fullPath)) {
                return false;
            }

            // Verify file was created
            if (!file_exists($fullPath)) {
                log_message('error', 'File was not created: ' . $fullPath);
                return false;
            }

            // Return relative path
            return 'jdih/writable/uploads/harmonisasi/tte/' . $filename;

        } catch (\Exception $e) {
            log_message('error', 'Error saving signed document: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: Get current user ID from session
     *
     * @return int|null
     */
    private function getCurrentUserId()
    {
        try {
            $session = \Config\Services::session();
            $user = $session->get('user');
            return $user['id_user'] ?? null;
        } catch (\Exception $e) {
            log_message('debug', 'Session not available in API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper: Get current user role
     *
     * @return string
     */
    private function getCurrentUserRole()
    {
        try {
            $session = \Config\Services::session();
            $user = $session->get('user');
            return $user['nama_role'] ?? 'unknown';
        } catch (\Exception $e) {
            log_message('debug', 'Session not available in API: ' . $e->getMessage());
            return 'unknown';
        }
    }

    /**
     * Helper: Log TTE activity
     *
     * @param int|null $idAjuan
     * @param int|null $userId
     * @param string $action
     * @param string $status
     * @param string|null $documentNumber
     * @param string|null $signedPath
     * @param string|null $errorMessage
     * @param string|null $metadata
     * @param int|null $logId Log ID untuk update existing log
     * @return int|null Log ID
     */
    private function logTteActivity($idAjuan, $userId, $action, $status, $documentNumber = null, $signedPath = null, $errorMessage = null, $metadata = null, $logId = null)
    {
        try {
            // Skip logging if id_ajuan is null and this is not an update operation
            // because id_ajuan is required (NOT NULL) in the database
            if ($idAjuan === null && !$logId) {
                // For operations without id_ajuan (like check-status), just log to file
                log_message('info', "TTE Activity - User: {$userId}, Action: {$action}, Status: {$status}, Message: " . ($errorMessage ?? $metadata ?? 'N/A'));
                return null;
            }

            // Cek struktur tabel yang sebenarnya dengan mencoba query sederhana
            $db = \Config\Database::connect();
            $tableExists = $db->tableExists('harmonisasi_tte_log');
            
            if (!$tableExists) {
                // Tabel tidak ada, log ke file saja
                log_message('info', "TTE Activity (table not exists) - Ajuan: {$idAjuan}, User: {$userId}, Action: {$action}, Status: {$status}");
                return null;
            }

            // Cek kolom yang ada di tabel
            $columns = $db->getFieldNames('harmonisasi_tte_log');
            $hasNewStructure = in_array('id_user_penandatangan', $columns);
            $hasOldStructure = in_array('id_user', $columns) || in_array('id_user_request', $columns);

            // Map data sesuai dengan struktur tabel yang sebenarnya
            if ($hasNewStructure) {
                // Struktur baru (sesuai migration)
                $logData = [
                    'id_user_penandatangan' => $userId,
                    'jenis_aksi' => strtolower($action) === 'tte_request' ? 'sign' : (strtolower($action) === 'tte_complete' ? 'sign' : 'sign'),
                    'status_tte' => strtolower($status) === 'pending' ? 'pending' : (strtolower($status) === 'success' ? 'success' : 'failed'),
                    'error_message' => $errorMessage,
                    'file_signed_path' => $signedPath
                ];
                
                // Tambahkan signature_info jika ada metadata atau document_number
                if ($documentNumber || $metadata) {
                    $logData['signature_info'] = json_encode([
                        'document_number' => $documentNumber,
                        'metadata' => $metadata,
                        'action' => $action,
                        'status' => $status
                    ]);
                }
            } elseif ($hasOldStructure) {
                // Struktur lama (backward compatibility)
                $logData = [
                    'id_user' => $userId,
                    'action' => $action,
                    'status' => $status,
                    'document_number' => $documentNumber,
                    'signed_path' => $signedPath,
                    'error_message' => $errorMessage
                ];
                
                if ($metadata) {
                    $logData['metadata'] = is_string($metadata) ? $metadata : json_encode($metadata);
                }
            } else {
                // Struktur tidak dikenal, log ke file saja
                log_message('warning', "TTE Activity - Unknown table structure for harmonisasi_tte_log. Ajuan: {$idAjuan}, User: {$userId}, Action: {$action}");
                log_message('info', "TTE Activity - Ajuan: {$idAjuan}, User: {$userId}, Action: {$action}, Status: {$status}, Document: {$documentNumber}");
                return null;
            }

            // Only include id_ajuan if it's not null
            if ($idAjuan !== null) {
                $logData['id_ajuan'] = $idAjuan;
            }

            if ($logId) {
                // Update existing log
                $this->tteLogModel->update($logId, $logData);
                return $logId;
            } else {
                // Create new log - only if id_ajuan is provided
                if ($idAjuan !== null) {
                    return $this->tteLogModel->insert($logData);
                } else {
                    // Log to file instead
                    log_message('info', "TTE Activity (no ajuan) - User: {$userId}, Action: {$action}, Status: {$status}");
                    return null;
                }
            }
        } catch (\Exception $e) {
            // Log error dan fallback ke file logging
            log_message('error', 'Error logging TTE activity: ' . $e->getMessage());
            log_message('error', 'TTE Activity fallback - Ajuan: ' . ($idAjuan ?? 'null') . ', User: ' . ($userId ?? 'null') . ', Action: ' . $action . ', Status: ' . $status);
            return null;
        }
    }

    /**
     * Last error storage
     */
    private $lastError = null;

    /**
     * Get last error
     *
     * @return array
     */
    private function getLastError()
    {
        return $this->lastError ?? ['status' => 500, 'message' => 'Unknown error'];
    }
}
