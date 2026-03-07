<?php

namespace App\Services;

use App\Models\DocumentNumberingModel;

class DocumentNumberingService
{
    protected $numberingModel;
    
    public function __construct()
    {
        $this->numberingModel = new DocumentNumberingModel();
    }
    
    /**
     * Dapatkan nomor dokumen berikutnya
     * 
     * @param string $documentType Jenis dokumen (contoh: peraturan_walikota, perda, dll)
     * @param int|null $tahun Tahun dokumen (opsional, default tahun berjalan)
     * @return array [
     *   'nomor_urut' => int,
     *   'nomor_penuh' => string,
     *   'prefix' => string,
     *   'tahun' => int,
     *   'format' => string
     * ]
     * @throws \RuntimeException Jika gagal mendapatkan nomor dokumen
     */
    public function getNextNumber(string $documentType, ?int $tahun = null): array
    {
        try {
            return $this->numberingModel->getNextNumber($documentType, $tahun);
        } catch (\Exception $e) {
            log_message('error', 'Gagal mendapatkan nomor dokumen: ' . $e->getMessage());
            throw new \RuntimeException('Gagal menghasilkan nomor dokumen. Silakan coba lagi.');
        }
    }
    
    /**
     * Format nomor dokumen secara manual
     * 
     * @param string $format Format nomor (contoh: '{PREFIX}/{NUMBER}/{YEAR}')
     * @param string $prefix Awalan nomor (contoh: 'PERWALI')
     * @param int $number Nomor urut
     * @param int $tahun Tahun dokumen
     * @return string Nomor yang sudah diformat
     */
    public function formatNumber(
        string $format, 
        string $prefix, 
        int $number, 
        int $tahun
    ): string {
        return $this->numberingModel->formatNumber($format, $prefix, $number, $tahun);
    }
    
    /**
     * Dapatkan daftar format penomoran yang tersedia
     * 
     * @return array Daftar format penomoran
     */
    public function getAvailableFormats(): array
    {
        return [
            '{PREFIX}/{NUMBER}/{YEAR}' => 'Format Standar (contoh: PERWALI/001/2023)',
            '{PREFIX}-{NUMBER}-{YEAR}' => 'Dengan tanda hubung (contoh: PERWALI-001-2023)',
            '{NUMBER}/JD/{YEAR}' => 'Dengan kode JD (contoh: 001/JD/2023)',
            '{PREFIX} {NUMBER} TAHUN {YEAR}' => 'Format panjang (contoh: PERWALI 001 TAHUN 2023)'
        ];
    }
    
    /**
     * Validasi format nomor
     * 
     * @param string $format Format yang akan divalidasi
     * @return bool True jika valid, false jika tidak
     */
    public function validateFormat(string $format): bool
    {
        // Pastikan format mengandung minimal satu placeholder
        $requiredPlaceholders = ['{PREFIX}', '{NUMBER}', '{YEAR}'];
        
        foreach ($requiredPlaceholders as $placeholder) {
            if (strpos($format, $placeholder) === false) {
                return false;
            }
        }
        
        return true;
    }
}
