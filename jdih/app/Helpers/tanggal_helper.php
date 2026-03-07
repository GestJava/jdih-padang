<?php

if (!function_exists('format_tanggal_indo')) {
    /**
     * Mengubah format tanggal menjadi format Indonesia (contoh: 20 Juni 2025).
     *
     * @param string|null $date Tanggal dalam format Y-m-d atau format lain yang dikenali strtotime().
     * @return string Tanggal yang sudah diformat atau '-' jika tanggal tidak valid.
     */
    function format_tanggal_indo($date)
    {
        if (empty($date) || $date === '0000-00-00') {
            return '-';
        }
        
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '-'; // Return gracefully if date is invalid
        }

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return date('d', $timestamp) . ' ' . $bulan[date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}
