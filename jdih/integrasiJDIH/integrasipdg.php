<?php

/**
 * API Integrasi JDIH Kota Padang ke JDIH Pusat
 * Compatible dengan format standar JDIH Nasional
 * Updated: 2025 - Enhanced with Relasi System
 * 
 * URL: https://jdih.padang.go.id/integrasiJDIH/integrasipdg.php
 */

// Increase memory and execution time limits for large datasets
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300); // 5 minutes

// Only set headers if not in CLI mode
if (php_sapi_name() !== 'cli') {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type");
}

// Database configuration
$servername = "localhost";
$username = "jdih_user";
$password = "passwordku123!";
$dbname = "jdih_db";

// Cache configuration
$cacheFile = __DIR__ . '/../writable/cache/integrasipdg_cache.json';
$cacheTime = 1800; // 30 minutes

// Default limit for mobile optimization (100 latest regulations)
$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 100;

// Check cache
if (isset($_GET['limit']) === false && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

$varjson = array();

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $limitClause = $limit ? "LIMIT $limit" : "";

    // Step 1: Fetch Peraturan
    $sql = "SELECT 
                wp.id_peraturan as id,
                wp.tahun,
                wp.nomor,
                wp.judul,
                wp.tgl_penetapan as tanggal_penetapan,
                wp.tgl_pengundangan,
                wp.abstrak_teks as abstrak,
                wp.file_dokumen as file_download,
                wp.id_status as status_dokumen,
                wp.id_instansi,
                sd.nama_status as status_nama,
                wjp.nama_jenis as jenis_nama,
                wjp.kategori_nama as singkatan,
                inst.nama_instansi,
                wp.sumber,
                wp.penandatangan,
                wp.tempat_penetapan,
                wp.created_at,
                wp.updated_at
            FROM web_peraturan wp
            LEFT JOIN web_jenis_peraturan wjp ON wjp.id_jenis_peraturan = wp.id_jenis_dokumen
            LEFT JOIN status_dokumen sd ON sd.id = wp.id_status
            LEFT JOIN instansi inst ON inst.id = wp.id_instansi
            WHERE wp.is_published = 1
            ORDER BY wp.tahun DESC, wp.nomor DESC
            $limitClause";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $peraturanList = [];
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $peraturanList[] = $row;
            $ids[] = $row['id'];
        }

        // Step 2: Batch fetch tags (Fix N+1 query problem)
        $idList = implode(',', array_map('intval', $ids));
        $tagsMap = [];
        if (!empty($idList)) {
            $tagSql = "SELECT wpt.id_peraturan, wt.nama_tag 
                       FROM web_peraturan_tag wpt
                       LEFT JOIN web_tag wt ON wt.id_tag = wpt.id_tag
                       WHERE wpt.id_peraturan IN ($idList)
                       ORDER BY wt.nama_tag ASC";
            $tagResult = $conn->query($tagSql);
            while ($tagRow = $tagResult->fetch_assoc()) {
                $tagsMap[$tagRow['id_peraturan']][] = $tagRow['nama_tag'];
            }
        }

        // Step 3: Format response
        foreach ($peraturanList as $row) {
            $row_array = new stdClass();

            // Mapping data sesuai format JDIH Pusat
            $row_array->idData = (string)$row['id'];
            $row_array->tahun_pengundangan = (string)$row['tahun'];
            $row_array->tanggal_pengundangan = formatTanggalPengundangan($row);
            $row_array->jenis = $row['jenis_nama'] ?? '';
            $row_array->noPeraturan = $row['nomor'] ?? '';
            $row_array->judul = $row['judul'] ?? '';
            $row_array->noPanggil = '-';
            $row_array->singkatanJenis = getSingkatanJenis($row['jenis_nama'] ?? '');
            $row_array->tempatTerbit = 'Kota Padang';
            $row_array->penerbit = '-';
            $row_array->deskripsiFisik = '-'; 
            $row_array->sumber = $row['sumber'] ?? formatSumber($row);
            
            // Subjek from pre-fetched tags
            $rowTags = isset($tagsMap[$row['id']]) ? implode(' - ', $tagsMap[$row['id']]) : '';
            $row_array->subjek = $rowTags;
            
            $row_array->isbn = null;
            $row_array->status = formatStatus($row['status_nama'] ?? '', $row['status_dokumen'] ?? '');
            $row_array->bahasa = 'Indonesia';
            $row_array->bidangHukum = getBidangHukum($row);
            $row_array->teuBadan = getTeuBadanFromInstansi($row['id_instansi'] ?? null, $row['nama_instansi'] ?? null);
            $row_array->nomorIndukBuku = '-';
            $row_array->fileDownload = getFileName($row['file_download'] ?? '');
            $row_array->urlDownload = getDownloadUrl($row['file_download'] ?? '');
            $row_array->abstrak = $row['abstrak'] ?? '';
            $row_array->urlabstrak = '';
            $row_array->urlDetailPeraturan = getDetailUrl($row['id']);
            $row_array->operasi = "4"; 
            $row_array->display = "1"; 
            $row_array->lastUpdated = $row['updated_at'] ?? $row['created_at'];

            array_push($varjson, $row_array);
        }

        $jsonOutput = json_encode($varjson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Save to cache if using default mobile limit (no explicit limit requested)
        if (isset($_GET['limit']) === false) {
            @file_put_contents($cacheFile, $jsonOutput);
        }
        
        echo $jsonOutput;
    } else {
        echo json_encode([]);
    }

    $conn->close();
} catch (Exception $e) {
    error_log("JDIH Integration API Error: " . $e->getMessage());
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Helper functions
 */
function formatSumber($row)
{
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

function formatTanggalPengundangan($row)
{
    // Prioritas: tgl_pengundangan -> tgl_penetapan -> null
    if (!empty($row['tgl_pengundangan']) && $row['tgl_pengundangan'] !== '0000-00-00') {
        return $row['tgl_pengundangan'];
    } elseif (!empty($row['tanggal_penetapan']) && $row['tanggal_penetapan'] !== '0000-00-00') {
        return $row['tanggal_penetapan'];
    }

    return null;
}

function getSingkatanJenis($jenisNama)
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

function formatStatus($status_nama, $status_id)
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

    // Fallback ke ID mapping jika nama tidak tersedia
    switch ($status_id) {
        case '1':
        case 1:
            return 'Masih Berlaku';
        case '2':
        case 2:
            return 'Tidak Berlaku';
        default:
            return 'Masih Berlaku';
    }
}

function getBidangHukum($row)
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

function getTeuBadan($bidangHukum)
{
    $mapping = [
        'Keuangan Daerah' => 'bapenda',
        'Pemerintahan' => 'bagtapem',
        'Lingkungan Hidup' => 'dlh',
        'Kesehatan' => 'dinkes',
        'Pendidikan' => 'disdik',
        'Agama' => 'kemenag',
        'Pemerintahan Daerah' => 'bagtapem'
    ];

    return $mapping[$bidangHukum] ?? 'bagtapem';
}

function getFileName($filePath)
{
    if (empty($filePath)) {
        return '';
    }

    return basename($filePath);
}

function getDownloadUrl($filePath)
{
    if (empty($filePath)) {
        return '';
    }

    // Handle CLI environment
    $baseUrl = (php_sapi_name() === 'cli') ? 'https://jdih.padang.go.id' : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'jdih.padang.go.id'));

    // Jika sudah full URL, return as is
    if (strpos($filePath, 'http') === 0) {
        return $filePath;
    }

    // Jika file ada di uploads/peraturan
    if (strpos($filePath, 'uploads/') === 0) {
        return $baseUrl . '/' . $filePath;
    }

    // Default path untuk peraturan
    return $baseUrl . '/uploads/peraturan/' . basename($filePath);
}

function getDetailUrl($id)
{
    // Handle CLI environment
    $baseUrl = (php_sapi_name() === 'cli') ? 'https://jdih.padang.go.id' : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'jdih.padang.go.id'));

    return $baseUrl . "/peraturan/detail/{$id}";
}

function getTeuBadanFromInstansi($id_instansi, $nama_instansi)
{
    // Jika tidak ada id_instansi, gunakan mapping default
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
