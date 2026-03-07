<?php

namespace App\Config;

/**
 * Konstanta Status untuk Workflow Harmonisasi
 * 
 * Berdasarkan tabel harmonisasi_status:
 * 1 = Draft
 * 2 = Diajukan ke Kabag 
 * 3 = Ditugaskan ke Verifikator
 * 4 = Proses Validasi
 * 5 = Revisi
 * 6 = Proses Finalisasi
 * 7 = Menunggu Paraf OPD
 * 8 = Menunggu Paraf Kabag
 * 9 = Menunggu Paraf Asisten
 * 11 = Menunggu Paraf/TTE Sekda
 * 12 = Menunggu Paraf Wawako
 * 13 = Menunggu TTE Walikota
 * 10 = Revisi ke Finalisasi
 * 14 = SELESAI
 * 15 = Ditolak
 */
class HarmonisasiStatus
{
    const DRAFT = 1;
    const DIAJUKAN = 2;
    const VERIFIKASI = 3;
    const VALIDASI = 4;
    const REVISI = 5;
    const FINALISASI = 6;
    const PARAF_OPD = 7;
    const PARAF_KABAG = 8;
    const PARAF_ASISTEN = 9;
    const REVISI_FINALISASI = 10;
    const PARAF_SEKDA = 11;
    const PARAF_WAWAKO = 12;
    const TTE_WALIKOTA = 13;
    const SELESAI = 14;
    const DITOLAK = 15;

    /**
     * Get status name by ID
     */
    public static function getStatusName($id)
    {
        $statuses = [
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Diajukan ke Kabag',
            self::VERIFIKASI => 'Ditugaskan ke Verifikator',
            self::VALIDASI => 'Proses Validasi',
            self::REVISI => 'Revisi',
            self::FINALISASI => 'Proses Finalisasi',
            self::PARAF_OPD => 'Menunggu Paraf OPD',
            self::PARAF_KABAG => 'Menunggu Paraf Kabag',
            self::PARAF_ASISTEN => 'Menunggu Paraf Asisten',
            self::PARAF_SEKDA => 'Menunggu Paraf/TTE Sekda',
            self::PARAF_WAWAKO => 'Menunggu Paraf Wawako',
            self::TTE_WALIKOTA => 'Menunggu TTE Walikota',
            self::REVISI_FINALISASI => 'Revisi ke Finalisasi',
            self::SELESAI => 'Selesai',
            self::DITOLAK => 'Ditolak'
        ];

        return $statuses[$id] ?? 'Unknown Status';
    }

    /**
     * Get workflow sequence
     */
    public static function getWorkflowSequence()
    {
        return [
            self::DRAFT,
            self::DIAJUKAN,
            self::VERIFIKASI,
            self::VALIDASI,
            self::FINALISASI,
            self::PARAF_OPD,
            self::PARAF_KABAG,
            self::REVISI_FINALISASI,
            self::PARAF_ASISTEN,
            self::PARAF_SEKDA,
            self::PARAF_WAWAKO,
            self::TTE_WALIKOTA,
            self::SELESAI
        ];
    }

    /**
     * Check if status allows editing
     */
    public static function canEdit($status_id)
    {
        return in_array($status_id, [self::DRAFT, self::REVISI]);
    }

    /**
     * Check if status is final (cannot be changed)
     */
    public static function isFinal($status_id)
    {
        return in_array($status_id, [self::SELESAI, self::DITOLAK]);
    }
}
