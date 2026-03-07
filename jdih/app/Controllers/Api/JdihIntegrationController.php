<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\WebPeraturanModel;
use CodeIgniter\Helpers\URLHelper;

class JdihIntegrationController extends BaseController
{
    protected $webPeraturanModel;
    protected $rateLimitConfig = [
        'requests_per_minute' => 10,
        'requests_per_hour' => 100,
        'cache_duration' => 1800, // 30 minutes
        'throttle_delay' => 60 // 1 minute delay for excessive requests
    ];

    public function __construct()
    {
        $this->webPeraturanModel = new WebPeraturanModel();

        // Load URL helper untuk url_title function
        helper('url');

        // Set JSON response header
        header("Content-Type: application/json");
    }

    /**
     * Check rate limiting untuk mencegah request berlebihan
     */
    private function checkRateLimit($ip_address, $endpoint)
    {
        try {
            $db = \Config\Database::connect();

            // Check if rate limit table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_rate_limit'")->getResult();

            if (empty($tableExists)) {
                // Create rate limit table
                $db->query("
                    CREATE TABLE IF NOT EXISTS `api_rate_limit` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `ip_address` varchar(45) NOT NULL,
                        `endpoint` varchar(100) NOT NULL,
                        `request_count` int(11) DEFAULT 1,
                        `first_request` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `last_request` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        `is_blocked` tinyint(1) DEFAULT 0,
                        `block_until` timestamp NULL DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `idx_ip_endpoint` (`ip_address`, `endpoint`),
                        KEY `idx_last_request` (`last_request`),
                        KEY `idx_is_blocked` (`is_blocked`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            }

            $now = date('Y-m-d H:i:s');
            $oneMinuteAgo = date('Y-m-d H:i:s', strtotime('-1 minute'));
            $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

            // Check if IP is currently blocked
            $blockedCheck = $db->query("
                SELECT is_blocked, block_until 
                FROM api_rate_limit 
                WHERE ip_address = ? AND endpoint = ? AND is_blocked = 1
            ", [$ip_address, $endpoint])->getRowArray();

            if ($blockedCheck && $blockedCheck['block_until'] && $blockedCheck['block_until'] > $now) {
                $remainingTime = strtotime($blockedCheck['block_until']) - time();
                return [
                    'allowed' => false,
                    'message' => "Rate limit exceeded. Please wait {$remainingTime} seconds before making another request.",
                    'retry_after' => $remainingTime
                ];
            }

            // Get current rate limit data
            $rateData = $db->query("
                SELECT * FROM api_rate_limit 
                WHERE ip_address = ? AND endpoint = ?
            ", [$ip_address, $endpoint])->getRowArray();

            if (!$rateData) {
                // First request from this IP
                $db->query("
                    INSERT INTO api_rate_limit (ip_address, endpoint, request_count, first_request, last_request)
                    VALUES (?, ?, 1, ?, ?)
                ", [$ip_address, $endpoint, $now, $now]);

                return ['allowed' => true];
            }

            // Check if we should reset counters (more than 1 hour since first request)
            if (strtotime($rateData['first_request']) < strtotime($oneHourAgo)) {
                $db->query("
                    UPDATE api_rate_limit 
                    SET request_count = 1, first_request = ?, last_request = ?, is_blocked = 0, block_until = NULL
                    WHERE ip_address = ? AND endpoint = ?
                ", [$now, $now, $ip_address, $endpoint]);

                return ['allowed' => true];
            }

            // Check minute-based rate limit
            $minuteRequests = $db->query("
                SELECT COUNT(*) as count 
                FROM api_access_log 
                WHERE ip_address = ? AND endpoint = ? AND created_at >= ?
            ", [$ip_address, $endpoint, $oneMinuteAgo])->getRowArray();

            if ($minuteRequests['count'] >= $this->rateLimitConfig['requests_per_minute']) {
                // Block for 1 minute
                $blockUntil = date('Y-m-d H:i:s', strtotime('+1 minute'));
                $db->query("
                    UPDATE api_rate_limit 
                    SET is_blocked = 1, block_until = ?, request_count = request_count + 1
                    WHERE ip_address = ? AND endpoint = ?
                ", [$blockUntil, $ip_address, $endpoint]);

                return [
                    'allowed' => false,
                    'message' => 'Too many requests per minute. Please wait 60 seconds.',
                    'retry_after' => 60
                ];
            }

            // Check hour-based rate limit
            if ($rateData['request_count'] >= $this->rateLimitConfig['requests_per_hour']) {
                // Block for 1 hour
                $blockUntil = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $db->query("
                    UPDATE api_rate_limit 
                    SET is_blocked = 1, block_until = ?, request_count = request_count + 1
                    WHERE ip_address = ? AND endpoint = ?
                ", [$blockUntil, $ip_address, $endpoint]);

                return [
                    'allowed' => false,
                    'message' => 'Hourly rate limit exceeded. Please wait 1 hour before making another request.',
                    'retry_after' => 3600
                ];
            }

            // Update request count
            $db->query("
                UPDATE api_rate_limit 
                SET request_count = request_count + 1, last_request = ?
                WHERE ip_address = ? AND endpoint = ?
            ", [$now, $ip_address, $endpoint]);

            return ['allowed' => true];
        } catch (\Exception $e) {
            log_message('error', 'Rate limit check error: ' . $e->getMessage());
            // Allow request if rate limiting fails
            return ['allowed' => true];
        }
    }

    /**
     * Set cache headers untuk mencegah request berulang
     */
    private function setCacheHeaders($cacheDuration = 1800)
    {
        $etag = md5(date('Y-m-d-H') . 'jdih-integration'); // ETag based on hour
        $lastModified = gmdate('D, d M Y H:i:s', strtotime('-' . ($cacheDuration / 60) . ' minutes')) . ' GMT';

        // Set cache headers
        header('Cache-Control: public, max-age=' . $cacheDuration);
        header('ETag: "' . $etag . '"');
        header('Last-Modified: ' . $lastModified);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheDuration) . ' GMT');

        // Check if client has cached version
        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';

        if ($ifNoneMatch === '"' . $etag . '"' || $ifModifiedSince === $lastModified) {
            http_response_code(304);
            exit;
        }
    }

    /**
     * Set throttling headers untuk memberi tahu client tentang rate limit
     */
    private function setThrottlingHeaders($rateLimitData)
    {
        if (isset($rateLimitData['retry_after'])) {
            header('Retry-After: ' . $rateLimitData['retry_after']);
            header('X-RateLimit-RetryAfter: ' . $rateLimitData['retry_after']);
        }

        header('X-RateLimit-Limit: ' . $this->rateLimitConfig['requests_per_minute'] . ' per minute');
        header('X-RateLimit-Limit-Hourly: ' . $this->rateLimitConfig['requests_per_hour'] . ' per hour');
    }

    /**
     * Log API access untuk tracking pihak luar
     */
    private function logApiAccess($endpoint, $status = 'success', $error_message = null)
    {
        try {
            $request = \Config\Services::request();

            // Get client information
            $ip_address = $request->getIPAddress();
            $user_agent = $request->getUserAgent()->getAgentString();
            $referer = $request->getServer('HTTP_REFERER') ?? '';
            $method = $request->getMethod();
            $url = $request->getUri()->getPath();
            $timestamp = date('Y-m-d H:i:s');

            // Get memory usage information
            $memory_usage = memory_get_usage(true); // true untuk mendapatkan real memory usage
            $memory_peak = memory_get_peak_usage(true);
            $memory_limit = ini_get('memory_limit');
            $memory_percentage = ($memory_usage / $this->convertToBytes($memory_limit)) * 100;

            // Get additional headers
            $headers = [
                'X-Forwarded-For' => $request->getServer('HTTP_X_FORWARDED_FOR'),
                'X-Real-IP' => $request->getServer('HTTP_X_REAL_IP'),
                'Host' => $request->getServer('HTTP_HOST'),
                'Accept' => $request->getServer('HTTP_ACCEPT'),
                'Accept-Language' => $request->getServer('HTTP_ACCEPT_LANGUAGE'),
                'Accept-Encoding' => $request->getServer('HTTP_ACCEPT_ENCODING')
            ];

            // Log ke database jika tabel api_access_log ada
            $this->logToDatabase($ip_address, $user_agent, $referer, $method, $url, $endpoint, $status, $error_message, $headers, $memory_usage, $memory_peak, $memory_percentage);

            // Log ke file
            $this->logToFile($ip_address, $user_agent, $referer, $method, $url, $endpoint, $status, $error_message, $headers, $memory_usage, $memory_peak, $memory_percentage);
        } catch (\Exception $e) {
            // Fallback: log error ke file system
            log_message('error', 'API Access Logging Error: ' . $e->getMessage());
        }
    }

    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes($memory_limit)
    {
        $unit = strtolower(substr($memory_limit, -1));
        $value = (int) substr($memory_limit, 0, -1);

        switch ($unit) {
            case 'k':
                return $value * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'g':
                return $value * 1024 * 1024 * 1024;
            default:
                return $value;
        }
    }

    /**
     * Log ke database
     */
    private function logToDatabase($ip_address, $user_agent, $referer, $method, $url, $endpoint, $status, $error_message, $headers, $memory_usage, $memory_peak, $memory_percentage)
    {
        try {
            $db = \Config\Database::connect();

            // Check if table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_access_log'")->getResult();

            if (empty($tableExists)) {
                // Create table if not exists
                $db->query("
                    CREATE TABLE IF NOT EXISTS `api_access_log` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `ip_address` varchar(45) NOT NULL,
                        `user_agent` text,
                        `referer` text,
                        `method` varchar(10) NOT NULL,
                        `url` text NOT NULL,
                        `endpoint` varchar(100) NOT NULL,
                        `status` varchar(20) NOT NULL,
                        `error_message` text,
                        `headers` text,
                        `memory_usage` bigint(20) DEFAULT NULL,
                        `memory_peak` bigint(20) DEFAULT NULL,
                        `memory_percentage` decimal(5,2) DEFAULT NULL,
                        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`),
                        KEY `idx_ip_address` (`ip_address`),
                        KEY `idx_endpoint` (`endpoint`),
                        KEY `idx_status` (`status`),
                        KEY `idx_created_at` (`created_at`),
                        KEY `idx_memory_usage` (`memory_usage`),
                        KEY `idx_memory_percentage` (`memory_percentage`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            } else {
                // Check if memory columns exist, if not add them
                $columns = $db->query("SHOW COLUMNS FROM api_access_log")->getResult();
                $columnNames = array_column($columns, 'Field');

                if (!in_array('memory_usage', $columnNames)) {
                    $db->query("ALTER TABLE api_access_log ADD COLUMN memory_usage bigint(20) DEFAULT NULL AFTER headers");
                }
                if (!in_array('memory_peak', $columnNames)) {
                    $db->query("ALTER TABLE api_access_log ADD COLUMN memory_peak bigint(20) DEFAULT NULL AFTER memory_usage");
                }
                if (!in_array('memory_percentage', $columnNames)) {
                    $db->query("ALTER TABLE api_access_log ADD COLUMN memory_percentage decimal(5,2) DEFAULT NULL AFTER memory_peak");
                }
            }

            // Insert log
            $db->query("
                INSERT INTO api_access_log 
                (ip_address, user_agent, referer, method, url, endpoint, status, error_message, headers, memory_usage, memory_peak, memory_percentage) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $ip_address,
                $user_agent,
                $referer,
                $method,
                $url,
                $endpoint,
                $status,
                $error_message,
                json_encode($headers),
                $memory_usage,
                $memory_peak,
                $memory_percentage
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Database API Logging Error: ' . $e->getMessage());
        }
    }

    /**
     * Log ke file
     */
    private function logToFile($ip_address, $user_agent, $referer, $method, $url, $endpoint, $status, $error_message, $headers, $memory_usage, $memory_peak, $memory_percentage)
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'referer' => $referer,
            'method' => $method,
            'url' => $url,
            'endpoint' => $endpoint,
            'status' => $status,
            'error_message' => $error_message,
            'headers' => $headers,
            'memory_usage' => $this->formatBytes($memory_usage),
            'memory_peak' => $this->formatBytes($memory_peak),
            'memory_percentage' => round($memory_percentage, 2) . '%'
        ];

        $logMessage = json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n";

        // Write to custom log file
        $logFile = WRITEPATH . 'logs/api_access_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);

        // Also log to CodeIgniter log
        $logLevel = ($status === 'success') ? 'info' : 'error';
        log_message($logLevel, "API Access: {$endpoint} - {$ip_address} - {$status} - Memory: {$this->formatBytes($memory_usage)} ({$memory_percentage}%)");
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }


    /**
     * Format sumber dokumen berdasarkan jenis dan tahun
     */
    private function formatSumber($row)
    {
        // Jika ada sumber dari database, gunakan itu
        if (!empty($row['sumber'])) {
            return $row['sumber'];
        }

        $tahun = $row['tahun'] ?? date('Y');
        $jenis = strtolower($row['jenis_nama'] ?? '');

        if (strpos($jenis, 'peraturan daerah') !== false || strpos($jenis, 'perda') !== false) {
            return "Lembaran Daerah Kota Padang Tahun {$tahun}";
        } elseif (strpos($jenis, 'peraturan walikota') !== false) {
            return "Berita Daerah Kota Padang Tahun {$tahun}";
        } elseif (strpos($jenis, 'keputusan') !== false) {
            return "Keputusan Walikota Padang Tahun {$tahun}";
        }

        return "Dokumen Hukum Kota Padang Tahun {$tahun}";
    }

    /**
     * Format tanggal pengundangan
     */
    private function formatTanggalPengundangan($row)
    {
        // Prioritas: tgl_pengundangan -> tgl_penetapan -> null
        if (!empty($row['tgl_pengundangan']) && $row['tgl_pengundangan'] !== '0000-00-00') {
            return $row['tgl_pengundangan'];
        } elseif (!empty($row['tanggal_penetapan']) && $row['tanggal_penetapan'] !== '0000-00-00') {
            return $row['tanggal_penetapan'];
        }

        return null;
    }

    /**
     * Get singkatan jenis dokumen
     */
    private function getSingkatanJenis($jenisNama)
    {
        $jenis = strtolower(trim($jenisNama));

        // Mapping singkatan berdasarkan jenis dokumen
        $mapping = [
            'peraturan daerah' => 'Perda',
            'peraturan walikota' => 'Perwako',
            'keputusan walikota' => 'Kepwali',
            'instruksi walikota' => 'Inwali',
            'surat edaran walikota' => 'SE Walikota',
            'peraturan bersama' => 'Perber',
            'keputusan bersama' => 'KB',
            'instruksi' => 'Instruksi',
            'surat edaran' => 'SE',
            'staatsblad' => 'Staatsblad',
            'jenis lain' => 'Lainnya'
        ];

        // Cari mapping yang cocok
        foreach ($mapping as $keyword => $singkatan) {
            if (strpos($jenis, $keyword) !== false) {
                return $singkatan;
            }
        }

        // Default fallback
        return 'Produk Hukum - Peraturan';
    }

    /**
     * Format status dokumen - Enhanced dengan database mapping
     */
    private function formatStatus($status_nama, $status_id)
    {
        // Gunakan nama status dari database jika tersedia
        if (!empty($status_nama)) {
            switch (strtolower(trim($status_nama))) {
                case 'berlaku':
                    return 'Masih Berlaku';
                case 'tidak berlaku':
                    return 'Tidak Berlaku';
                default:
                    return $status_nama;
            }
        }

        // Fallback ke ID mapping
        switch ($status_id) {
            case 1:
                return 'Masih Berlaku';
            case 2:
                return 'Tidak Berlaku';
            default:
                return 'Masih Berlaku';
        }
    }

    /**
     * Get bidang hukum - Enhanced dengan analisis judul dan jenis
     */
    private function getBidangHukum($row)
    {
        $jenis = strtolower($row['jenis_nama'] ?? '');
        $judul = strtolower($row['judul'] ?? '');

        // Analisis berdasarkan jenis dokumen dan judul
        $keywords = [
            'Hukum Keuangan Negara' => ['pajak', 'retribusi', 'apbd', 'keuangan', 'anggaran', 'pendapatan'],
            'Hukum Kepegawaian' => ['kepegawaian', 'pegawai', 'asn', 'pns', 'honorer'],
            'Hukum Lingkungan' => ['lingkungan', 'kebersihan', 'sampah', 'hijau', 'limbah'],
            'Hukum Kesehatan' => ['kesehatan', 'rumah sakit', 'puskesmas', 'obat', 'medis'],
            'Hukum Pendidikan' => ['pendidikan', 'sekolah', 'guru', 'siswa', 'universitas'],
            'Hukum Perhubungan' => ['transportasi', 'jalan', 'lalu lintas', 'angkutan', 'terminal'],
            'Hukum Infrastruktur' => ['infrastruktur', 'jalan', 'jembatan', 'gedung', 'bangunan'],
            'Hukum Sosial' => ['sosial', 'bantuan', 'kemiskinan', 'kesejahteraan', 'rakyat'],
            'Hukum Perdagangan' => ['perdagangan', 'pasar', 'toko', 'usaha', 'ekonomi'],
            'Hukum Pariwisata' => ['pariwisata', 'wisata', 'hotel', 'restoran', 'budaya'],
            'Hukum Agraria' => ['tanah', 'lahan', 'pertanian', 'perkebunan'],
            'Hukum Tata Ruang' => ['tata ruang', 'rutrk', 'wilayah', 'zonasi']
        ];

        foreach ($keywords as $bidang => $words) {
            foreach ($words as $word) {
                if (strpos($jenis, $word) !== false || strpos($judul, $word) !== false) {
                    return $bidang;
                }
            }
        }

        // Default sesuai permintaan
        return 'Hukum Administrasi Negara';
    }

    /**
     * Get nomor panggil berdasarkan jenis dan nomor peraturan
     */
    private function getNoPanggil($row)
    {
        $jenis = strtolower($row['jenis_nama'] ?? '');
        $nomor = $row['nomor'] ?? '';
        $tahun = $row['tahun'] ?? '';

        if (empty($nomor) || empty($tahun)) {
            return '-';
        }

        // Format: [SINGKATAN] [NOMOR]/[TAHUN]
        $singkatan = $this->getSingkatanJenis($jenis);
        return "{$singkatan} {$nomor}/{$tahun}";
    }

    /**
     * Get penerbit berdasarkan instansi dan penandatangan
     */
    private function getPenerbit($row)
    {
        $nama_instansi = $row['nama_instansi'] ?? '';
        $penandatangan = $row['penandatangan'] ?? '';

        // Jika ada penandatangan, gunakan itu
        if (!empty($penandatangan)) {
            return $penandatangan;
        }

        // Jika ada nama instansi, gunakan itu
        if (!empty($nama_instansi)) {
            return $nama_instansi;
        }

        return 'Pemerintah Kota Padang';
    }

    /**
     * Get deskripsi fisik dokumen
     */
    private function getDeskripsiFisik($row)
    {
        $file_download = $row['file_download'] ?? '';
        $tempat_penetapan = $row['tempat_penetapan'] ?? '';

        $deskripsi = '';

        if (!empty($file_download)) {
            $extension = strtolower(pathinfo($file_download, PATHINFO_EXTENSION));

            switch ($extension) {
                case 'pdf':
                    $deskripsi = '1 file PDF';
                    break;
                case 'doc':
                case 'docx':
                    $deskripsi = '1 file Word';
                    break;
                case 'xls':
                case 'xlsx':
                    $deskripsi = '1 file Excel';
                    break;
                default:
                    $deskripsi = '1 file dokumen';
            }
        } else {
            $deskripsi = 'Dokumen digital';
        }

        // Tambahkan tempat penetapan jika ada
        if (!empty($tempat_penetapan)) {
            $deskripsi .= " - Ditetapkan di {$tempat_penetapan}";
        }

        return $deskripsi;
    }

    /**
     * Get nomor induk buku (untuk katalogisasi)
     */
    private function getNomorIndukBuku($row)
    {
        $id = $row['id'] ?? '';
        $tahun = $row['tahun'] ?? '';

        if (!empty($id) && !empty($tahun)) {
            return "JDIH-PDG-{$tahun}-{$id}";
        }

        return '-';
    }

    /**
     * Get URL abstrak (jika ada halaman abstrak terpisah)
     */
    private function getUrlAbstrak($row)
    {
        $id = $row['id'] ?? '';

        if (!empty($id)) {
            // Jika ada halaman abstrak terpisah
            return base_url("peraturan/abstrak/{$id}");
        }

        return '';
    }

    /**
     * Get TEU badan (instansi terkait) - Simplified mapping
     */
    private function getTeuBadan($bidangHukum)
    {
        $mapping = [
            'Keuangan Daerah' => 'bapenda',
            'Pemerintahan' => 'bagtapem',
            'Lingkungan Hidup' => 'dlh',
            'Kesehatan' => 'dinkes',
            'Pendidikan' => 'disdik',
            'Perhubungan' => 'dishub',
            'Pekerjaan Umum' => 'dispupr',
            'Sosial' => 'dinsos',
            'Perdagangan' => 'disdag',
            'Pariwisata' => 'dispar'
        ];

        return $mapping[$bidangHukum] ?? 'bagtapem';
    }

    /**
     * Get subjek dari relasi tags - Enhanced dengan database relasi
     */
    private function getSubjekFromTags($id_peraturan)
    {
        $db = \Config\Database::connect();

        // Query untuk mengambil semua tags yang terkait dengan peraturan
        $query = $db->query("
            SELECT wt.nama_tag 
            FROM web_peraturan_tag wpt
            LEFT JOIN web_tag wt ON wt.id_tag = wpt.id_tag
            WHERE wpt.id_peraturan = ?
            ORDER BY wt.nama_tag ASC
        ", [$id_peraturan]);

        $tags = $query->getResultArray();

        if (empty($tags)) {
            return ''; // Jika tidak ada tags, return empty string
        }

        // Ambil nama_tag dari hasil query
        $tagNames = array_column($tags, 'nama_tag');

        // Gabungkan dengan tanda hubung jika lebih dari 1
        return implode(' - ', $tagNames);
    }

    /**
     * Get TEU badan dari instansi - Enhanced dengan database relasi
     */
    private function getTeuBadanFromInstansi($id_instansi, $nama_instansi)
    {
        // Jika tidak ada id_instansi, gunakan mapping default berdasarkan bidang hukum
        if (empty($id_instansi) || empty($nama_instansi)) {
            return 'bagtapem'; // Default
        }

        // Konversi nama instansi ke kode TEU badan
        $nama_lower = strtolower($nama_instansi);

        // Mapping berdasarkan nama instansi yang umum
        $mapping = [
            'badan pendapatan daerah' => 'bapenda',
            'bapenda' => 'bapenda',
            'bagian tata pemerintahan' => 'bagtapem',
            'bagtapem' => 'bagtapem',
            'dinas lingkungan hidup' => 'dlh',
            'dlh' => 'dlh',
            'dinas kesehatan' => 'dinkes',
            'dinkes' => 'dinkes',
            'dinas pendidikan' => 'disdik',
            'disdik' => 'disdik',
            'dinas perhubungan' => 'dishub',
            'dishub' => 'dishub',
            'dinas pekerjaan umum' => 'dispupr',
            'dispupr' => 'dispupr',
            'dinas sosial' => 'dinsos',
            'dinsos' => 'dinsos',
            'dinas perdagangan' => 'disdag',
            'disdag' => 'disdag',
            'dinas pariwisata' => 'dispar',
            'dispar' => 'dispar',
            'dinas tenaga kerja' => 'disnaker',
            'disnaker' => 'disnaker',
            'dinas pertanian' => 'distan',
            'distan' => 'distan',
            'dinas perindustrian' => 'disperin',
            'disperin' => 'disperin'
        ];

        // Cari mapping yang cocok
        foreach ($mapping as $keyword => $code) {
            if (strpos($nama_lower, $keyword) !== false) {
                return $code;
            }
        }

        // Jika tidak ditemukan mapping, gunakan default
        return 'bagtapem';
    }

    /**
     * Get file name dari path
     */
    private function getFileName($filePath)
    {
        if (empty($filePath)) {
            return '';
        }

        return basename($filePath);
    }

    /**
     * Get full download URL - Menggunakan base_url() helper
     * File dokumen ada di: uploads/peraturan/ (ROOT)
     * URL: http://localhost/webjdih/uploads/peraturan/[filename]
     */
    private function getDownloadUrl($filePath)
    {
        if (empty($filePath)) {
            return '';
        }

        // Jika sudah full URL, return as is
        if (strpos($filePath, 'http') === 0) {
            return $filePath;
        }

        // Handle CLI environment
        if (php_sapi_name() === 'cli') {
            return 'https://jdih.padang.go.id/uploads/peraturan/' . basename($filePath);
        }

        // Gunakan base_url() helper untuk konsistensi
        // Jika file sudah ada path lengkap dengan uploads/
        if (strpos($filePath, 'uploads/') === 0) {
            return base_url($filePath);
        }

        // Jika hanya filename tanpa path, tambahkan peraturan/
        if (strpos($filePath, '/') === false) {
            return base_url('uploads/peraturan/' . $filePath);
        }

        // Jika ada path relatif tapi tidak dimulai dengan uploads/
        if (strpos($filePath, 'uploads/') !== 0) {
            return base_url('uploads/peraturan/' . basename($filePath));
        }

        // Default: return dengan base_url helper
        return base_url($filePath);
    }

    /**
     * Get detail URL - Menggunakan slug atau ID
     * URL: http://localhost/webjdih/peraturan/{slug} atau http://localhost/webjdih/peraturan/detail/{id}
     */
    private function getDetailUrl($slug)
    {
        if (empty($slug)) {
            return '';
        }

        // Handle CLI environment
        if (php_sapi_name() === 'cli') {
            return "https://jdih.padang.go.id/peraturan/{$slug}";
        }

        // Jika slug mengandung 'detail/', berarti menggunakan ID
        if (strpos($slug, 'detail/') === 0) {
            return base_url("peraturan/{$slug}");
        }

        // Jika tidak, berarti menggunakan slug
        return base_url("peraturan/{$slug}");
    }

    /**
     * Endpoint untuk testing API
     */
    public function test()
    {
        try {
            // Log API access
            $this->logApiAccess('test');

            $sampleData = [
                (object)[
                    'idData' => '1',
                    'tahun_pengundangan' => '2025',
                    'tanggal_pengundangan' => '2025-01-15',
                    'jenis' => 'Peraturan Daerah',
                    'noPeraturan' => '1',
                    'judul' => 'Peraturan Daerah Nomor 1 Tahun 2025 Tentang Test API',
                    'noPanggil' => '-',
                    'singkatanJenis' => 'PERDA',
                    'tempatTerbit' => 'Padang',
                    'penerbit' => '-',
                    'deskripsiFisik' => '-',
                    'sumber' => 'Lembaran Daerah Kota Padang Tahun 2025',
                    'subjek' => 'Test API JDIH',
                    'isbn' => null,
                    'status' => 'Masih Berlaku',
                    'bahasa' => 'Indonesia',
                    'bidangHukum' => 'Pemerintahan Daerah',
                    'teuBadan' => 'bagtapem',
                    'nomorIndukBuku' => '-',
                    'fileDownload' => 'test_api.pdf',
                    'urlDownload' => base_url('uploads/peraturan/test_api.pdf'),
                    'abstrak' => 'Peraturan untuk testing API integrasi JDIH',
                    'urlabstrak' => '',
                    'urlDetailPeraturan' => base_url('peraturan/test-api-integrasi-jdih'),
                    'operasi' => '4',
                    'display' => '1'
                ]
            ];

            return $this->response->setJSON($sampleData);
        } catch (\Exception $e) {
            // Log error
            $this->logApiAccess('test', 'error', $e->getMessage());

            $this->response->setContentType('application/json');
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Internal server error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint untuk integrasi JDIH Padang
     * URL: /jdih/integrasiJDIH/integrasipadang
     */
    public function integrasipadang()
    {
        try {
            $request = \Config\Services::request();
            $ip_address = $request->getIPAddress();
            $endpoint = 'integrasipadang';

            // Check rate limiting
            $rateLimitResult = $this->checkRateLimit($ip_address, $endpoint);
            if (!$rateLimitResult['allowed']) {
                $this->setThrottlingHeaders($rateLimitResult);
                $this->logApiAccess($endpoint, 'rate_limited', $rateLimitResult['message']);

                return $this->response
                    ->setStatusCode(429)
                    ->setJSON([
                        'error' => 'Too Many Requests',
                        'message' => $rateLimitResult['message'],
                        'retry_after' => $rateLimitResult['retry_after'] ?? 60
                    ]);
            }

            // Set cache headers untuk mencegah request berulang
            $this->setCacheHeaders($this->rateLimitConfig['cache_duration']);

            // Log API access
            $this->logApiAccess($endpoint);

            // Implementasi caching untuk performa
            $cache = \Config\Services::cache();
            
            // Get limit parameter (legacy compatibility and performance)
            $limit = $request->getGet('limit');
            $limit = (is_numeric($limit)) ? (int)$limit : null;
            
            $cacheKey = 'jdih_integration_padang_data' . ($limit ? '_limit_' . $limit : '');

            // Cek cache terlebih dahulu (cache 30 menit)
            $cachedData = $cache->get($cacheKey);
            if ($cachedData !== null) {
                // Set proper headers untuk JSON
                $this->response->setContentType('application/json');
                return $this->response->setJSON($cachedData);
            }

            $varjson = [];

            // OPTIMIZED: Get all peraturan data dengan join ke jenis dokumen, status, dan instansi
            // PLUS: Pre-fetch semua tags untuk menghindari N+1 query problem
            $db = \Config\Database::connect();

            // Build query with optional limit
            $limitClause = $limit ? "LIMIT $limit" : "";
            
            // Step 1: Get all peraturan data
            $query = $db->query("
                SELECT 
                    wp.id_peraturan as id,
                    wp.tahun,
                    wp.nomor,
                    wp.judul,
                    wp.slug,
                    wp.tgl_penetapan as tanggal_penetapan,
                    wp.tgl_pengundangan,
                    wp.abstrak_teks as abstrak,
                    wp.file_dokumen as file_download,
                    wp.id_status as status_dokumen,
                    wp.id_instansi,
                    wp.sumber,
                    wp.penandatangan,
                    wp.tempat_penetapan,
                    wp.created_at,
                    wp.updated_at,
                    sd.nama_status as status_nama,
                    wjp.nama_jenis as jenis_nama,
                    wjp.kategori_nama as singkatan,
                    inst.nama_instansi
                FROM web_peraturan wp
                LEFT JOIN web_jenis_peraturan wjp ON wjp.id_jenis_peraturan = wp.id_jenis_dokumen
                LEFT JOIN status_dokumen sd ON sd.id = wp.id_status
                LEFT JOIN instansi inst ON inst.id = wp.id_instansi
                WHERE wp.is_published = 1
                ORDER BY wp.tahun DESC, wp.nomor DESC
                $limitClause
            ");

            $peraturan = $query->getResultArray();

            // Step 2: Pre-fetch semua tags untuk semua peraturan sekaligus (OPTIMIZATION)
            $peraturanIds = array_column($peraturan, 'id');
            $tagsMap = [];

            if (!empty($peraturanIds)) {
                $placeholders = str_repeat('?,', count($peraturanIds) - 1) . '?';
                $tagsQuery = $db->query("
                    SELECT wpt.id_peraturan, wt.nama_tag
                    FROM web_peraturan_tag wpt
                    LEFT JOIN web_tag wt ON wt.id_tag = wpt.id_tag
                    WHERE wpt.id_peraturan IN ($placeholders)
                    ORDER BY wpt.id_peraturan, wt.nama_tag ASC
                ", $peraturanIds);

                $tagsResult = $tagsQuery->getResultArray();

                // Group tags by peraturan ID
                foreach ($tagsResult as $tag) {
                    $id_peraturan = $tag['id_peraturan'];
                    if (!isset($tagsMap[$id_peraturan])) {
                        $tagsMap[$id_peraturan] = [];
                    }
                    $tagsMap[$id_peraturan][] = $tag['nama_tag'];
                }
            }

            // Step 3: Process data in batches untuk memory management
            $batchSize = 100; // Process 100 records at a time
            $totalRecords = count($peraturan);
            $processedCount = 0;

            for ($i = 0; $i < $totalRecords; $i += $batchSize) {
                $batch = array_slice($peraturan, $i, $batchSize);

                foreach ($batch as $row) {
                    $row_array = (object)[];

                    // Mapping data sesuai format JDIH Pusat
                    $row_array->idData = (string)$row['id'];
                    $row_array->tahun_pengundangan = (string)$row['tahun'];
                    $row_array->tanggal_pengundangan = $this->formatTanggalPengundangan($row);
                    $row_array->jenis = $row['jenis_nama'] ?? '';
                    $row_array->noPeraturan = $row['nomor'] ?? '';
                    $row_array->judul = $row['judul'] ?? '';
                    $row_array->noPanggil = $this->getNoPanggil($row);
                    $row_array->singkatanJenis = $this->getSingkatanJenis($row['jenis_nama'] ?? '');
                    $row_array->tempatTerbit = 'Kota Padang';
                    $row_array->penerbit = $this->getPenerbit($row);
                    $row_array->deskripsiFisik = $this->getDeskripsiFisik($row);
                    $row_array->sumber = $this->formatSumber($row);

                    // OPTIMIZED: Get subjek from pre-fetched tags map instead of individual query
                    $row_array->subjek = isset($tagsMap[$row['id']]) ? implode(' - ', $tagsMap[$row['id']]) : '';

                    $row_array->isbn = null;
                    $row_array->status = $this->formatStatus($row['status_nama'] ?? '', $row['status_dokumen'] ?? '');
                    $row_array->bahasa = 'Indonesia';
                    $row_array->bidangHukum = $this->getBidangHukum($row);
                    $row_array->teuBadan = $this->getTeuBadanFromInstansi($row['id_instansi'], $row['nama_instansi']);
                    $row_array->nomorIndukBuku = $this->getNomorIndukBuku($row);
                    $row_array->fileDownload = $this->getFileName($row['file_download'] ?? '');
                    $row_array->urlDownload = $this->getDownloadUrl($row['file_download'] ?? '');
                    $row_array->abstrak = $row['abstrak'] ?? '';
                    $row_array->urlabstrak = $this->getUrlAbstrak($row);

                    // Gunakan slug dari database, jika kosong generate dari judul
                    $slug = $row['slug'] ?? '';
                    if (empty($slug) && !empty($row['judul'])) {
                        $slug = url_title(strtolower($row['judul']), '-', true);
                        // Tambahkan ID di akhir slug untuk uniqueness
                        $slug .= '-' . $row['id'];
                    }

                    // Jika masih kosong, gunakan ID sebagai fallback
                    if (empty($slug)) {
                        $slug = 'detail-' . $row['id'];
                    }

                    $row_array->urlDetailPeraturan = $this->getDetailUrl($slug);
                    $row_array->operasi = "4"; // Wajib ada
                    $row_array->display = "1"; // Wajib ada

                    // Enhanced metadata dari sistem relasi
                    $row_array->lastUpdated = $row['updated_at'] ?? $row['created_at'];

                    $varjson[] = $row_array;
                    $processedCount++;
                }

                // Memory management: Clear batch data and force garbage collection
                unset($batch);
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }

                // Log progress untuk monitoring
                if ($processedCount % 500 == 0) {
                    log_message('info', "JDIH Integration: Processed {$processedCount}/{$totalRecords} records");
                }
            }

            // Cache hasil untuk 30 menit
            $cache->save($cacheKey, $varjson, 1800);

            // Log untuk monitoring
            log_message('info', 'JDIH Integration Padang API: Returned ' . count($varjson) . ' records');

            // Set proper headers dan return JSON response
            $this->response->setContentType('application/json');
            return $this->response->setJSON($varjson);
        } catch (\Exception $e) {
            // Log error dengan detail
            $this->logApiAccess('integrasipadang', 'error', $e->getMessage());
            log_message('error', 'JDIH Integration Padang API Error: ' . $e->getMessage());

            $this->response->setContentType('application/json');
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Internal server error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Legacy endpoint for compatibility with .php extension
     * URL: /jdih/integrasiJDIH/integrasipadang.php
     */
    public function integrasipadangPhp()
    {
        return $this->integrasipadang();
    }

    /**
     * Mobile-specific optimized endpoint
     * URL: /jdih/integrasiJDIH/integrasipdg
     * URL: /jdih/integrasiJDIH/integrasipdg.php
     */
    public function integrasipdg()
    {
        // Default limit for mobile to balance performance and data availability
        $request = \Config\Services::request();
        if (!$request->getGet('limit')) {
            $request->setGlobal('get', array_merge($request->getGet(), ['limit' => 100]));
        }
        
        return $this->integrasipadang();
    }

    /**
     * Optimized endpoint dengan pagination dan filtering untuk dataset besar
     * URL: /jdih/integrasiJDIH/integrasipadang-optimized
     * Parameters: page, limit, tahun, jenis, status, search
     */
    public function integrasipadangOptimized()
    {
        try {
            $request = \Config\Services::request();
            $ip_address = $request->getIPAddress();
            $endpoint = 'integrasipadang-optimized';

            // Check rate limiting
            $rateLimitResult = $this->checkRateLimit($ip_address, $endpoint);
            if (!$rateLimitResult['allowed']) {
                $this->setThrottlingHeaders($rateLimitResult);
                $this->logApiAccess($endpoint, 'rate_limited', $rateLimitResult['message']);

                return $this->response
                    ->setStatusCode(429)
                    ->setJSON([
                        'error' => 'Too Many Requests',
                        'message' => $rateLimitResult['message'],
                        'retry_after' => $rateLimitResult['retry_after'] ?? 60
                    ]);
            }

            // Set cache headers
            $this->setCacheHeaders($this->rateLimitConfig['cache_duration']);

            // Log API access
            $this->logApiAccess($endpoint);

            // Get parameters
            $page = (int)($request->getGet('page') ?? 1);
            $limit = min((int)($request->getGet('limit') ?? 50), 200); // Max 200 per page
            $tahun = $request->getGet('tahun');
            $jenis = $request->getGet('jenis');
            $status = $request->getGet('status');
            $search = $request->getGet('search');

            // Build cache key based on parameters
            $cacheKey = 'jdih_integration_padang_optimized_' . md5(serialize([
                'page' => $page,
                'limit' => $limit,
                'tahun' => $tahun,
                'jenis' => $jenis,
                'status' => $status,
                'search' => $search
            ]));

            // Check cache
            $cache = \Config\Services::cache();
            $cachedData = $cache->get($cacheKey);
            if ($cachedData !== null) {
                $this->response->setContentType('application/json');
                return $this->response->setJSON($cachedData);
            }

            $db = \Config\Database::connect();

            // Build WHERE clause
            $whereConditions = ['wp.is_published = 1'];
            $params = [];

            if ($tahun) {
                $whereConditions[] = 'wp.tahun = ?';
                $params[] = $tahun;
            }

            if ($jenis) {
                $whereConditions[] = 'wjp.nama_jenis LIKE ?';
                $params[] = '%' . $jenis . '%';
            }

            if ($status) {
                $whereConditions[] = 'sd.nama_status LIKE ?';
                $params[] = '%' . $status . '%';
            }

            if ($search) {
                $whereConditions[] = '(wp.judul LIKE ? OR wp.abstrak_teks LIKE ? OR wp.nomor LIKE ?)';
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Get total count for pagination
            $countQuery = $db->query("
                SELECT COUNT(*) as total
                FROM web_peraturan wp
                LEFT JOIN web_jenis_peraturan wjp ON wjp.id_jenis_peraturan = wp.id_jenis_dokumen
                LEFT JOIN status_dokumen sd ON sd.id = wp.id_status
                LEFT JOIN instansi inst ON inst.id = wp.id_instansi
                WHERE $whereClause
            ", $params);

            $totalRecords = $countQuery->getRowArray()['total'];
            $totalPages = ceil($totalRecords / $limit);
            $offset = ($page - 1) * $limit;

            // Get paginated data
            $query = $db->query("
                SELECT 
                    wp.id_peraturan as id,
                    wp.tahun,
                    wp.nomor,
                    wp.judul,
                    wp.slug,
                    wp.tgl_penetapan as tanggal_penetapan,
                    wp.tgl_pengundangan,
                    wp.abstrak_teks as abstrak,
                    wp.file_dokumen as file_download,
                    wp.id_status as status_dokumen,
                    wp.id_instansi,
                    wp.sumber,
                    wp.penandatangan,
                    wp.tempat_penetapan,
                    wp.created_at,
                    wp.updated_at,
                    sd.nama_status as status_nama,
                    wjp.nama_jenis as jenis_nama,
                    wjp.kategori_nama as singkatan,
                    inst.nama_instansi
                FROM web_peraturan wp
                LEFT JOIN web_jenis_peraturan wjp ON wjp.id_jenis_peraturan = wp.id_jenis_dokumen
                LEFT JOIN status_dokumen sd ON sd.id = wp.id_status
                LEFT JOIN instansi inst ON inst.id = wp.id_instansi
                WHERE $whereClause
                ORDER BY wp.tahun DESC, wp.nomor DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [$limit, $offset]));

            $peraturan = $query->getResultArray();

            // Pre-fetch tags for current batch only
            $peraturanIds = array_column($peraturan, 'id');
            $tagsMap = [];

            if (!empty($peraturanIds)) {
                $placeholders = str_repeat('?,', count($peraturanIds) - 1) . '?';
                $tagsQuery = $db->query("
                    SELECT wpt.id_peraturan, wt.nama_tag
                    FROM web_peraturan_tag wpt
                    LEFT JOIN web_tag wt ON wt.id_tag = wpt.id_tag
                    WHERE wpt.id_peraturan IN ($placeholders)
                    ORDER BY wpt.id_peraturan, wt.nama_tag ASC
                ", $peraturanIds);

                $tagsResult = $tagsQuery->getResultArray();

                foreach ($tagsResult as $tag) {
                    $id_peraturan = $tag['id_peraturan'];
                    if (!isset($tagsMap[$id_peraturan])) {
                        $tagsMap[$id_peraturan] = [];
                    }
                    $tagsMap[$id_peraturan][] = $tag['nama_tag'];
                }
            }

            // Process data
            $varjson = [];
            foreach ($peraturan as $row) {
                $row_array = (object)[];

                // Mapping data sesuai format JDIH Pusat
                $row_array->idData = (string)$row['id'];
                $row_array->tahun_pengundangan = (string)$row['tahun'];
                $row_array->tanggal_pengundangan = $this->formatTanggalPengundangan($row);
                $row_array->jenis = $row['jenis_nama'] ?? '';
                $row_array->noPeraturan = $row['nomor'] ?? '';
                $row_array->judul = $row['judul'] ?? '';
                $row_array->noPanggil = $this->getNoPanggil($row);
                $row_array->singkatanJenis = $this->getSingkatanJenis($row['jenis_nama'] ?? '');
                $row_array->tempatTerbit = 'Kota Padang';
                $row_array->penerbit = $this->getPenerbit($row);
                $row_array->deskripsiFisik = $this->getDeskripsiFisik($row);
                $row_array->sumber = $this->formatSumber($row);
                $row_array->subjek = isset($tagsMap[$row['id']]) ? implode(' - ', $tagsMap[$row['id']]) : '';
                $row_array->isbn = null;
                $row_array->status = $this->formatStatus($row['status_nama'] ?? '', $row['status_dokumen'] ?? '');
                $row_array->bahasa = 'Indonesia';
                $row_array->bidangHukum = $this->getBidangHukum($row);
                $row_array->teuBadan = $this->getTeuBadanFromInstansi($row['id_instansi'], $row['nama_instansi']);
                $row_array->nomorIndukBuku = $this->getNomorIndukBuku($row);
                $row_array->fileDownload = $this->getFileName($row['file_download'] ?? '');
                $row_array->urlDownload = $this->getDownloadUrl($row['file_download'] ?? '');
                $row_array->abstrak = $row['abstrak'] ?? '';
                $row_array->urlabstrak = $this->getUrlAbstrak($row);

                $slug = $row['slug'] ?? '';
                if (empty($slug) && !empty($row['judul'])) {
                    $slug = url_title(strtolower($row['judul']), '-', true);
                    $slug .= '-' . $row['id'];
                }
                if (empty($slug)) {
                    $slug = 'detail-' . $row['id'];
                }

                $row_array->urlDetailPeraturan = $this->getDetailUrl($slug);
                $row_array->operasi = "4";
                $row_array->display = "1";
                $row_array->lastUpdated = $row['updated_at'] ?? $row['created_at'];

                $varjson[] = $row_array;
            }

            // Prepare response with pagination info
            $response = [
                'data' => $varjson,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_records' => $totalRecords,
                    'total_pages' => $totalPages,
                    'has_next_page' => $page < $totalPages,
                    'has_prev_page' => $page > 1
                ],
                'filters' => [
                    'tahun' => $tahun,
                    'jenis' => $jenis,
                    'status' => $status,
                    'search' => $search
                ]
            ];

            // Cache for 15 minutes (shorter than full data)
            $cache->save($cacheKey, $response, 900);

            log_message('info', "JDIH Integration Optimized: Returned {$limit} records (page {$page}/{$totalPages})");

            $this->response->setContentType('application/json');
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $this->logApiAccess('integrasipadang-optimized', 'error', $e->getMessage());
            log_message('error', 'JDIH Integration Optimized API Error: ' . $e->getMessage());

            $this->response->setContentType('application/json');
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Internal server error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get API access statistics dan log
     * URL: /jdih/integrasiJDIH/stats
     */
    public function stats()
    {
        try {
            $db = \Config\Database::connect();

            // Check if table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_access_log'")->getResult();

            if (empty($tableExists)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'API access log table not found',
                    'data' => null
                ]);
            }

            // Get statistics
            $stats = [];

            // Total access today
            $todayStats = $db->query("
                SELECT 
                    COUNT(*) as total_access,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count,
                    AVG(memory_usage) as avg_memory_usage,
                    MAX(memory_usage) as max_memory_usage,
                    AVG(memory_percentage) as avg_memory_percentage,
                    MAX(memory_percentage) as max_memory_percentage
                FROM api_access_log 
                WHERE DATE(created_at) = CURDATE()
            ")->getRowArray();

            $stats['today'] = $todayStats;

            // Total access this month
            $monthStats = $db->query("
                SELECT 
                    COUNT(*) as total_access,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count,
                    AVG(memory_usage) as avg_memory_usage,
                    MAX(memory_usage) as max_memory_usage,
                    AVG(memory_percentage) as avg_memory_percentage,
                    MAX(memory_percentage) as max_memory_percentage
                FROM api_access_log 
                WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())
            ")->getRowArray();

            $stats['this_month'] = $monthStats;

            // Top IP addresses
            $topIPs = $db->query("
                SELECT 
                    ip_address,
                    COUNT(*) as access_count,
                    MAX(created_at) as last_access,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count
                FROM api_access_log 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY ip_address 
                ORDER BY access_count DESC 
                LIMIT 10
            ")->getResultArray();

            $stats['top_ips'] = $topIPs;

            // Endpoint usage
            $endpointStats = $db->query("
                SELECT 
                    endpoint,
                    COUNT(*) as access_count,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count,
                    MAX(created_at) as last_access
                FROM api_access_log 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY endpoint 
                ORDER BY access_count DESC
            ")->getResultArray();

            $stats['endpoint_usage'] = $endpointStats;

            // Memory usage statistics
            $memoryStats = $db->query("
                SELECT 
                    AVG(memory_usage) as avg_memory_usage,
                    MAX(memory_usage) as max_memory_usage,
                    MIN(memory_usage) as min_memory_usage,
                    AVG(memory_percentage) as avg_memory_percentage,
                    MAX(memory_percentage) as max_memory_percentage,
                    COUNT(CASE WHEN memory_percentage > 80 THEN 1 END) as high_memory_count,
                    COUNT(CASE WHEN memory_percentage > 90 THEN 1 END) as critical_memory_count
                FROM api_access_log 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")->getRowArray();

            $stats['memory_usage'] = $memoryStats;

            // Recent access (last 50)
            $recentAccess = $db->query("
                SELECT 
                    ip_address,
                    user_agent,
                    endpoint,
                    status,
                    created_at,
                    referer,
                    memory_usage,
                    memory_percentage
                FROM api_access_log 
                ORDER BY created_at DESC 
                LIMIT 50
            ")->getResultArray();

            $stats['recent_access'] = $recentAccess;

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'API access statistics retrieved successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * Get detailed log by IP address
     * URL: /jdih/integrasiJDIH/logs/{ip_address}
     */
    public function logs($ip_address = null)
    {
        try {
            if (empty($ip_address)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'IP address parameter required'
                ]);
            }

            $db = \Config\Database::connect();

            // Check if table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_access_log'")->getResult();

            if (empty($tableExists)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'API access log table not found'
                ]);
            }

            // Get logs for specific IP
            $logs = $db->query("
                SELECT 
                    ip_address,
                    user_agent,
                    referer,
                    method,
                    url,
                    endpoint,
                    status,
                    error_message,
                    created_at,
                    memory_usage,
                    memory_peak,
                    memory_percentage
                FROM api_access_log 
                WHERE ip_address = ?
                ORDER BY created_at DESC 
                LIMIT 100
            ", [$ip_address])->getResultArray();

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Logs retrieved successfully',
                'ip_address' => $ip_address,
                'total_records' => count($logs),
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up old rate limit data (admin function)
     * URL: /jdih/integrasiJDIH/cleanup-rate-limit
     */
    public function cleanupRateLimit()
    {
        try {
            $db = \Config\Database::connect();

            // Check if rate limit table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_rate_limit'")->getResult();

            if (empty($tableExists)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Rate limit table not found'
                ]);
            }

            // Clean up old rate limit data (older than 7 days)
            $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

            $result = $db->query("
                DELETE FROM api_rate_limit 
                WHERE last_request < ?
            ", [$sevenDaysAgo]);

            $affectedRows = $db->affectedRows();

            // Also clean up old access logs (older than 30 days)
            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

            $logResult = $db->query("
                DELETE FROM api_access_log 
                WHERE created_at < ?
            ", [$thirtyDaysAgo]);

            $logAffectedRows = $db->affectedRows();

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Cleanup completed successfully',
                'rate_limit_cleaned' => $affectedRows,
                'access_log_cleaned' => $logAffectedRows,
                'cleanup_date' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get log files content
     * URL: /jdih/integrasiJDIH/logfiles
     */
    public function logfiles()
    {
        try {
            $logDir = WRITEPATH . 'logs/';
            $logFiles = [];

            // Get all API access log files
            $files = glob($logDir . 'api_access_*.log');

            foreach ($files as $file) {
                $filename = basename($file);
                $fileSize = filesize($file);
                $lastModified = date('Y-m-d H:i:s', filemtime($file));

                // Read last 100 lines
                $lines = [];
                if ($fileSize > 0) {
                    $fileContent = file($file);
                    $lines = array_slice($fileContent, -100); // Last 100 lines
                }

                $logFiles[] = [
                    'filename' => $filename,
                    'size' => $fileSize,
                    'last_modified' => $lastModified,
                    'line_count' => count($lines),
                    'sample_lines' => array_slice($lines, -10) // Last 10 lines as sample
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Log files retrieved successfully',
                'data' => $logFiles
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get rate limit status untuk IP tertentu
     * URL: /jdih/integrasiJDIH/rate-limit/{ip_address}
     */
    public function rateLimitStatus($ip_address = null)
    {
        try {
            if (empty($ip_address)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'IP address parameter required'
                ]);
            }

            $db = \Config\Database::connect();

            // Check if rate limit table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_rate_limit'")->getResult();

            if (empty($tableExists)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Rate limit table not found'
                ]);
            }

            // Get rate limit data for IP
            $rateData = $db->query("
                SELECT * FROM api_rate_limit 
                WHERE ip_address = ?
                ORDER BY last_request DESC
            ", [$ip_address])->getResultArray();

            // Get recent access for IP
            $recentAccess = $db->query("
                SELECT endpoint, created_at, status 
                FROM api_access_log 
                WHERE ip_address = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ", [$ip_address])->getResultArray();

            return $this->response->setJSON([
                'status' => 'success',
                'ip_address' => $ip_address,
                'rate_limit_data' => $rateData,
                'recent_access' => $recentAccess,
                'limits' => $this->rateLimitConfig
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Unblock IP address (admin function)
     * URL: /jdih/integrasiJDIH/unblock/{ip_address}
     */
    public function unblockIP($ip_address = null)
    {
        try {
            if (empty($ip_address)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'IP address parameter required'
                ]);
            }

            $db = \Config\Database::connect();

            // Check if rate limit table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_rate_limit'")->getResult();

            if (empty($tableExists)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Rate limit table not found'
                ]);
            }

            // Unblock IP
            $result = $db->query("
                UPDATE api_rate_limit 
                SET is_blocked = 0, block_until = NULL 
                WHERE ip_address = ?
            ", [$ip_address]);

            $affectedRows = $db->affectedRows();

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $affectedRows > 0 ? 'IP unblocked successfully' : 'IP was not blocked',
                'ip_address' => $ip_address,
                'affected_rows' => $affectedRows
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get all blocked IPs (admin function)
     * URL: /jdih/integrasiJDIH/blocked-ips
     */
    public function blockedIPs()
    {
        try {
            $db = \Config\Database::connect();

            // Check if rate limit table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_rate_limit'")->getResult();

            if (empty($tableExists)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Rate limit table not found'
                ]);
            }

            // Get all blocked IPs
            $blockedIPs = $db->query("
                SELECT ip_address, endpoint, request_count, first_request, last_request, block_until
                FROM api_rate_limit 
                WHERE is_blocked = 1
                ORDER BY block_until DESC
            ")->getResultArray();

            return $this->response->setJSON([
                'status' => 'success',
                'total_blocked' => count($blockedIPs),
                'blocked_ips' => $blockedIPs
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Dashboard untuk monitoring API access
     * URL: /jdih/integrasiJDIH/dashboard
     */
    public function dashboard()
    {
        try {
            $db = \Config\Database::connect();

            // Check if table exists
            $tableExists = $db->query("SHOW TABLES LIKE 'api_access_log'")->getResult();

            if (empty($tableExists)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'API access log table not found. Please access an API endpoint first to create the table.',
                    'data' => null
                ]);
            }

            // Get comprehensive statistics
            $dashboard = [];

            // Overall statistics
            $overallStats = $db->query("
                SELECT 
                    COUNT(*) as total_access,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count,
                    MIN(created_at) as first_access,
                    MAX(created_at) as last_access,
                    AVG(memory_usage) as avg_memory_usage,
                    MAX(memory_usage) as max_memory_usage,
                    AVG(memory_percentage) as avg_memory_percentage,
                    MAX(memory_percentage) as max_memory_percentage
                FROM api_access_log
            ")->getRowArray();

            $dashboard['overall'] = $overallStats;

            // Daily access for last 7 days
            $dailyStats = $db->query("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as access_count,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count
                FROM api_access_log 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ")->getResultArray();

            $dashboard['daily_stats'] = $dailyStats;

            // Top 10 IP addresses with most access
            $topIPs = $db->query("
                SELECT 
                    ip_address,
                    COUNT(*) as access_count,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count,
                    MAX(created_at) as last_access,
                    MIN(created_at) as first_access
                FROM api_access_log 
                GROUP BY ip_address 
                ORDER BY access_count DESC 
                LIMIT 10
            ")->getResultArray();

            $dashboard['top_ips'] = $topIPs;

            // Endpoint usage statistics
            $endpointStats = $db->query("
                SELECT 
                    endpoint,
                    COUNT(*) as access_count,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as success_count,
                    COUNT(CASE WHEN status = 'error' THEN 1 END) as error_count,
                    MAX(created_at) as last_access
                FROM api_access_log 
                GROUP BY endpoint 
                ORDER BY access_count DESC
            ")->getResultArray();

            $dashboard['endpoint_stats'] = $endpointStats;

            // Recent activity (last 20)
            $recentActivity = $db->query("
                SELECT 
                    ip_address,
                    user_agent,
                    endpoint,
                    status,
                    created_at,
                    referer
                FROM api_access_log 
                ORDER BY created_at DESC 
                LIMIT 20
            ")->getResultArray();

            $dashboard['recent_activity'] = $recentActivity;

            // User agent analysis
            $userAgentStats = $db->query("
                SELECT 
                    user_agent,
                    COUNT(*) as access_count,
                    COUNT(DISTINCT ip_address) as unique_ips
                FROM api_access_log 
                WHERE user_agent IS NOT NULL AND user_agent != ''
                GROUP BY user_agent 
                ORDER BY access_count DESC 
                LIMIT 10
            ")->getResultArray();

            $dashboard['user_agent_stats'] = $userAgentStats;

            // Memory usage analysis
            $memoryAnalysis = $db->query("
                SELECT 
                    AVG(memory_usage) as avg_memory_usage,
                    MAX(memory_usage) as max_memory_usage,
                    MIN(memory_usage) as min_memory_usage,
                    AVG(memory_percentage) as avg_memory_percentage,
                    MAX(memory_percentage) as max_memory_percentage,
                    COUNT(CASE WHEN memory_percentage > 80 THEN 1 END) as high_memory_count,
                    COUNT(CASE WHEN memory_percentage > 90 THEN 1 END) as critical_memory_count,
                    COUNT(CASE WHEN memory_percentage <= 50 THEN 1 END) as low_memory_count
                FROM api_access_log 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")->getRowArray();

            $dashboard['memory_analysis'] = $memoryAnalysis;

            // High memory usage incidents
            $highMemoryIncidents = $db->query("
                SELECT 
                    ip_address,
                    endpoint,
                    memory_usage,
                    memory_percentage,
                    created_at,
                    status
                FROM api_access_log 
                WHERE memory_percentage > 80
                ORDER BY memory_percentage DESC, created_at DESC
                LIMIT 10
            ")->getResultArray();

            $dashboard['high_memory_incidents'] = $highMemoryIncidents;

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Dashboard data retrieved successfully',
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ]);
        }
    }
}
