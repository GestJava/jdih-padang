<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi Posisi TTE untuk PDF Enhancement
 * 
 * File ini berisi pengaturan posisi untuk:
 * - Nomor peraturan
 * - Tanggal penetapan  
 * - QR code
 * - Font size dan styling
 */
class TTEPositionConfig extends BaseConfig
{
    /**
     * ========================================
     * PENGATURAN POSISI NOMOR PERATURAN
     * ========================================
     */

    /**
     * Posisi fallback untuk nomor peraturan (jika tidak ditemukan kata kunci)
     * Format: [x, y] dalam points
     */
    public $nomorPeraturanFallback = [50, 30];

    /**
     * Font size untuk nomor peraturan - DIPERBESAR untuk alignment
     */
    public $nomorPeraturanFontSize = 12;

    /**
     * Font family untuk nomor peraturan (kompatibel dengan PyMuPDF)
     * Menggunakan Bookman Old Style dari file TTF
     */
    public $nomorPeraturanFontFamily = 'Bookman Old Style'; // Bookman Old Style font

    /**
     * Warna untuk nomor peraturan (RGB)
     */
    public $nomorPeraturanColor = [0, 0, 0]; // Hitam

    /**
     * Offset Y untuk nomor peraturan (jarak dari posisi yang ditemukan)
     * DISESUAIKAN agar sama dengan tanggal yang sudah pas
     */
    public $nomorPeraturanOffsetY = 10;

    /**
     * Kata kunci untuk mencari posisi nomor peraturan (STRICT MODE)
     * Hanya menggunakan placeholder yang tepat
     */
    public $nomorPeraturanKeywords = [
        '${NO}'            // Placeholder utama untuk nomor peraturan (STRICT)
    ];

    /**
     * ========================================
     * PENGATURAN POSISI TANGGAL PENETAPAN
     * ========================================
     */

    /**
     * Posisi fallback untuk tanggal penetapan (jika tidak ditemukan kata kunci)
     * Format: [x, y] dalam points
     */
    public $tanggalPenetapanFallback = [50, 50];

    /**
     * Font size untuk tanggal penetapan - DISESUAIKAN untuk konsistensi
     */
    public $tanggalPenetapanFontSize = 12;

    /**
     * Font family untuk tanggal penetapan (kompatibel dengan PyMuPDF)
     * Menggunakan Bookman Old Style dari file TTF
     */
    public $tanggalPenetapanFontFamily = 'Bookman Old Style'; // Bookman Old Style font

    /**
     * Warna untuk tanggal penetapan (RGB)
     */
    public $tanggalPenetapanColor = [0, 0, 0]; // Hitam

    /**
     * Offset Y untuk tanggal penetapan (jarak dari posisi yang ditemukan)
     * DISESUAIKAN untuk konsistensi dengan nomor peraturan
     */
    public $tanggalPenetapanOffsetY = 10;

    /**
     * Kata kunci untuk mencari posisi tanggal penetapan (STRICT MODE)
     * Hanya menggunakan placeholder yang tepat
     */
    public $tanggalPenetapanKeywords = [
        '${TGL}'            // Placeholder utama untuk tanggal penetapan (STRICT)
    ];

    /**
     * Prefix text untuk tanggal penetapan
     */
    public $tanggalPenetapanPrefix = '';

    /**
     * ========================================
     * PENGATURAN POSISI QR CODE
     * ========================================
     */

    /**
     * Posisi QR code (kanan atas)
     * Format: [x1, y1, x2, y2] dalam points
     * x1,y1 = kiri atas, x2,y2 = kanan bawah
     */
    public $qrCodePosition = [450, 50, 600, 200];

    /**
     * Ukuran QR code (width x height dalam points) - DIPERKECIL agar tidak menutupi nama
     */
    public $qrCodeSize = [60, 60];

    /**
     * Posisi alternatif QR code (jika posisi utama tidak cocok)
     */
    public $qrCodePositionAlternative = [400, 100, 550, 250];

    /**
     * Kata kunci untuk mencari posisi QR code (STRICT MODE)
     * Hanya menggunakan placeholder yang tepat
     */
    public $qrCodeKeywords = [
        '${QR}'             // Placeholder utama untuk QR code (STRICT)
    ];

    /**
     * ========================================
     * PENGATURAN POSISI TCPDF FALLBACK
     * ========================================
     */

    /**
     * Posisi nomor peraturan di TCPDF fallback
     */
    public $tcpdfNomorPeraturanPosition = [0, 10]; // Cell position

    /**
     * Posisi tanggal penetapan di TCPDF fallback
     */
    public $tcpdfTanggalPenetapanPosition = [0, 20]; // Cell position

    /**
     * Posisi QR code di TCPDF fallback
     */
    public $tcpdfQrCodePosition = [150, 50, 50, 50]; // [x, y, width, height]

    /**
     * ========================================
     * PENGATURAN FONT DAN STYLING
     * ========================================
     */


    /**
     * Font style untuk nomor peraturan (B = Bold, I = Italic, BI = Bold Italic)
     */
    public $nomorPeraturanFontStyle = 'B';


    /**
     * Font style untuk tanggal penetapan
     */
    public $tanggalPenetapanFontStyle = '';

    /**
     * ========================================
     * PENGATURAN LAYOUT DAN SPACING
     * ========================================
     */

    /**
     * Margin kiri untuk semua elemen
     */
    public $leftMargin = 50;

    /**
     * Margin atas untuk semua elemen
     */
    public $topMargin = 30;

    /**
     * Spacing antara elemen (dalam points)
     */
    public $elementSpacing = 20;

    /**
     * ========================================
     * PENGATURAN PENCARIAN POSISI
     * ========================================
     */

    /**
     * Apakah menggunakan smart positioning (cari kata kunci)
     */
    public $useSmartPositioning = true;

    /**
     * Apakah menggunakan fallback positioning jika smart positioning gagal
     */
    public $useFallbackPositioning = true;

    /**
     * Case sensitive untuk pencarian kata kunci
     */
    public $caseSensitiveSearch = false;

    /**
     * Prioritas keyword (keyword dengan prioritas lebih tinggi akan dicari terlebih dahulu)
     */
    public $keywordPriority = [
        'tanggal' => [
            '${tgl}' => 1,
            '${TGL}' => 2,
            '$tgl' => 3,
            '$TGL' => 4
        ],
        'nomor' => [
            '${no}' => 1,
            '${NO}' => 2,
            '$no' => 3,
            '$NO' => 4
        ],
        'qr' => [
            '${qr}' => 1,
            '${QR}' => 2,
            '$qr' => 3,
            '$QR' => 4
        ]
    ];

    /**
     * ========================================
     * PENGATURAN DEBUGGING
     * ========================================
     */

    /**
     * Apakah menampilkan debug info
     */
    public $debugMode = false;

    /**
     * Log level untuk positioning
     */
    public $logLevel = 'info'; // debug, info, warning, error

    /**
     * ========================================
     * PENGATURAN FOOTER TTE
     * ========================================
     */

    /**
     * Apakah menambahkan footer TTE
     */
    public $enableTteFooter = true;

    /**
     * Teks footer TTE (2 baris)
     */
    public $tteFooterLine1 = "Dokumen ini telah ditandatangani secara elektronik menggunakan sertifikat elektronik";
    public $tteFooterLine2 = "yang diterbitkan oleh Balai Besar Sertifikasi Elektronik (BSrE), Badan Siber dan Sandi Negara.";

    /**
     * Font size untuk footer TTE
     */
    public $tteFooterFontSize = 10;

    /**
     * Warna footer TTE (RGB)
     */
    public $tteFooterColor = [0, 0, 0]; // Hitam

    /**
     * Posisi footer TTE dari bawah halaman (dalam points)
     */
    public $tteFooterMarginBottom = 35; // Baris 1: 35 point dari bawah
    public $tteFooterMarginBottomLine2 = 20; // Baris 2: 20 point dari bawah

    /**
     * Margin kiri untuk center alignment
     */
    public $tteFooterMarginLeft = 20;

    /**
     * ========================================
     * HELPER METHODS
     * ========================================
     */

    /**
     * Get posisi nomor peraturan dengan fallback
     */
    public function getNomorPeraturanPosition($foundPosition = null): array
    {
        if ($foundPosition && $this->useSmartPositioning) {
            return [
                $foundPosition[0],
                $foundPosition[1] + $this->nomorPeraturanOffsetY
            ];
        }

        return $this->nomorPeraturanFallback;
    }

    /**
     * Get posisi tanggal penetapan dengan fallback
     */
    public function getTanggalPenetapanPosition($foundPosition = null): array
    {
        if ($foundPosition && $this->useSmartPositioning) {
            return [
                $foundPosition[0],
                $foundPosition[1] + $this->tanggalPenetapanOffsetY
            ];
        }

        return $this->tanggalPenetapanFallback;
    }

    /**
     * Get posisi QR code
     */
    public function getQrCodePosition($useAlternative = false): array
    {
        return $useAlternative ? $this->qrCodePositionAlternative : $this->qrCodePosition;
    }

    /**
     * Get font size untuk nomor peraturan
     */
    public function getNomorPeraturanFontSize(): int
    {
        return $this->nomorPeraturanFontSize;
    }

    /**
     * Get font size untuk tanggal penetapan
     */
    public function getTanggalPenetapanFontSize(): int
    {
        return $this->tanggalPenetapanFontSize;
    }

    /**
     * Get warna untuk nomor peraturan
     */
    public function getNomorPeraturanColor(): array
    {
        return $this->nomorPeraturanColor;
    }

    /**
     * Get warna untuk tanggal penetapan
     */
    public function getTanggalPenetapanColor(): array
    {
        return $this->tanggalPenetapanColor;
    }
}
