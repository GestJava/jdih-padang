<?php

/**
 *	App Name	: JDIH Kota Padang	
 *	Author		: Agus Salim
 *	Website		: https://jdih.padang.go.id
 *	Year		: 2025
 */

namespace App\Models;

use App\Models\BaseModel;

class DashboardModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    // Get total documents by year
    public function getTotalDokumen($tahun = null)
    {
        $builder = $this->db->table('web_peraturan');
        if ($tahun) {
            $builder->where('YEAR(tgl_penetapan)', $tahun);
        }
        $builder->where('is_published', 1);

        $current = $builder->countAllResults();

        // Get previous year for growth calculation
        if ($tahun) {
            $builder = $this->db->table('web_peraturan');
            $builder->where('YEAR(tgl_penetapan)', $tahun - 1);
            $builder->where('is_published', 1);
            $previous = $builder->countAllResults();

            $growth = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
        } else {
            $growth = 0;
        }

        return ['jml' => $current, 'growth' => $growth];
    }

    // Get total harmonization documents
    public function getTotalHarmonisasi($year = null)
    {
        // Check if table exists
        if (!$this->db->tableExists('harmonisasi_ajuan')) {
            return ['jml' => 0, 'growth' => 0];
        }

        $builder = $this->db->table('harmonisasi_ajuan');
        if ($year) {
            $builder->where('YEAR(created_at)', $year);
        }

        $current = $builder->countAllResults();

        if ($year) {
            $builder = $this->db->table('harmonisasi_ajuan');
            $builder->where('YEAR(created_at)', $year - 1);
            $previous = $builder->countAllResults();

            $growth = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
        } else {
            $growth = 0;
        }

        return ['jml' => $current, 'growth' => $growth];
    }

    // Get total visitors using traffic_summary table (OPTIMIZED)
    public function getTotalPengunjung($tahun = null)
    {
        // OPTIMASI: Gunakan traffic_summary untuk query yang lebih cepat
        if ($this->db->tableExists('traffic_summary')) {
            $builder = $this->db->table('traffic_summary');
            if ($tahun) {
                $builder->where('YEAR(date)', $tahun);
            }
            
            $result = $builder->selectSum('total_hits')->get()->getRowArray();
            $current = $result['total_hits'] ?? 0;

            if ($tahun) {
                $builder = $this->db->table('traffic_summary');
                $builder->where('YEAR(date)', $tahun - 1);
                $result = $builder->selectSum('total_hits')->get()->getRowArray();
                $previous = $result['total_hits'] ?? 0;

                $growth = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
            } else {
                $growth = 0;
            }

            return ['jml' => $current, 'growth' => $growth];
        } else {
            // Fallback: Return 0 if summary table missing to avoid killing server with 1M+ rows scan
            return ['jml' => 0, 'growth' => 0];
        }
    }

    // Get total active users from built-in user table
    public function getTotalUser($tahun = null)
    {
        $builder = $this->db->table('user');
        $builder->where('status', 'active');
        if ($tahun) {
            $builder->where('YEAR(created)', $tahun);
        }

        $current = $builder->countAllResults();

        if ($tahun) {
            $builder = $this->db->table('user');
            $builder->where('status', 'active');
            $builder->where('YEAR(created)', $tahun - 1);
            $previous = $builder->countAllResults();

            $growth = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
        } else {
            $growth = 0;
        }

        return ['jml' => $current, 'growth' => $growth];
    }

    // Get document statistics by type
    public function getDokumenByType($tahun = null)
    {
        $builder = $this->db->table('web_peraturan wp');
        $builder->select('wjp.nama_jenis as jenis_peraturan, COUNT(*) as jumlah');
        $builder->join('web_jenis_peraturan wjp', 'wp.id_jenis_dokumen = wjp.id_jenis_peraturan', 'left');
        $builder->where('wp.is_published', 1);
        if ($tahun) {
            $builder->where('YEAR(wp.tgl_penetapan)', $tahun);
        }
        $builder->groupBy('wp.id_jenis_dokumen');
        $builder->orderBy('jumlah', 'DESC');
        $builder->limit(10);

        return $builder->get()->getResultArray();
    }

    // Get dokumen peraturan berdasarkan jenis (untuk grafik)
    public function getDokumenPeraturanByJenis($tahun = null)
    {
        $builder = $this->db->table('web_peraturan wp');
        $builder->select('wjp.nama_jenis as jenis_peraturan, COUNT(*) as jumlah');
        $builder->join('web_jenis_peraturan wjp', 'wp.id_jenis_dokumen = wjp.id_jenis_peraturan', 'left');
        $builder->where('wp.is_published', 1);
        if ($tahun) {
            $builder->where('YEAR(wp.tgl_penetapan)', $tahun);
        }
        $builder->groupBy('wp.id_jenis_dokumen, wjp.nama_jenis');
        $builder->orderBy('jumlah', 'DESC');

        return $builder->get()->getResultArray();
    }

    // Get monthly document publication
    public function getDokumenPerBulan($tahun)
    {
        $builder = $this->db->table('web_peraturan');
        $builder->select('MONTH(tgl_penetapan) as bulan, COUNT(*) as jumlah');
        $builder->where('YEAR(tgl_penetapan)', $tahun);
        $builder->where('is_published', 1);
        $builder->groupBy('MONTH(tgl_penetapan)');
        $builder->orderBy('bulan');

        $result = $builder->get()->getResultArray();

        // Ensure all months are represented
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = 0;
        }

        foreach ($result as $row) {
            $months[$row['bulan']] = $row['jumlah'];
        }

        return array_values($months);
    }

    // Get recent documents
    public function getDokumenTerbaru($limit = 10)
    {
        $builder = $this->db->table('web_peraturan wp');
        $builder->select('wp.judul, wp.nomor, wp.tgl_penetapan as tanggal, wjp.nama_jenis as jenis_peraturan');
        $builder->join('web_jenis_peraturan wjp', 'wp.id_jenis_dokumen = wjp.id_jenis_peraturan', 'left');
        $builder->where('wp.is_published', 1);
        $builder->orderBy('wp.tgl_penetapan', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    // Get documents published this month
    public function getDokumenBulanIni()
    {
        $builder = $this->db->table('web_peraturan');
        $builder->where('is_published', 1);
        $builder->where('MONTH(tgl_penetapan)', date('n'));
        $builder->where('YEAR(tgl_penetapan)', date('Y'));

        return $builder->countAllResults();
    }

    // Get available years
    public function getListTahun()
    {
        $builder = $this->db->table('web_peraturan');
        $builder->select('YEAR(tgl_penetapan) as tahun');
        $builder->where('is_published', 1);
        $builder->where('tgl_penetapan IS NOT NULL');
        $builder->groupBy('YEAR(tgl_penetapan)');
        $builder->orderBy('tahun', 'DESC');

        return $builder->get()->getResultArray();
    }

    // Get harmonization status statistics
    public function getHarmonisasiStatus($year = null)
    {
        // Check if tables exist
        if (!$this->db->tableExists('harmonisasi_ajuan') || !$this->db->tableExists('harmonisasi_status')) {
            return [];
        }

        $builder = $this->db->table('harmonisasi_ajuan ha');
        $builder->select('hs.nama_status, COUNT(*) as jumlah');
        $builder->join('harmonisasi_status hs', 'ha.id_status_ajuan = hs.id', 'left');

        if ($year) {
            $builder->where('YEAR(ha.created_at)', $year);
        }

        $builder->groupBy('ha.id_status_ajuan');

        $result = $builder->get()->getResultArray();

        return $result ?: [];
    }

    // Get visitor statistics for the year using traffic_summary table (OPTIMIZED)
    public function getVisitorStats($tahun)
    {
        if ($this->db->tableExists('traffic_summary')) {
            $builder = $this->db->table('traffic_summary');
            $builder->select('MONTH(date) as bulan, SUM(total_hits) as views, SUM(total_visitors) as visitors');
            $builder->where('YEAR(date)', $tahun);
            $builder->groupBy('MONTH(date)');
            $builder->orderBy('bulan');
        } else {
            // Fallback for huge overhead
            $builder = $this->db->table('traffic');
            $builder->select('MONTH(date) as bulan, SUM(hits) as views, COUNT(DISTINCT ip) as visitors');
            $builder->where('YEAR(date)', $tahun);
            $builder->groupBy('MONTH(date)');
            $builder->orderBy('bulan');
        }

        $result = $builder->get()->getResultArray();

        // Ensure all months are represented
        $stats = [];
        for ($i = 1; $i <= 12; $i++) {
            $stats['views'][$i] = 0;
            $stats['visitors'][$i] = 0;
        }

        foreach ($result as $row) {
            $stats['views'][$row['bulan']] = $row['views'];
            $stats['visitors'][$row['bulan']] = $row['visitors'];
        }

        return $stats;
    }

    // Statistik harmonisasi: total ajuan
    public function getTotalAjuanHarmonisasi($tahun = null, $id_instansi = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan');
        if ($tahun) {
            $builder->where('YEAR(tanggal_pengajuan)', $tahun);
        }
        if ($id_instansi) {
            $builder->where('id_instansi_pemohon', $id_instansi);
        }
        return $builder->countAllResults();
    }

    // Statistik harmonisasi: per status
    public function getHarmonisasiPerStatus($tahun = null, $id_instansi = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan ha');
        $builder->select('s.nama_status, ha.id_status_ajuan, COUNT(*) as jumlah');
        $builder->join('harmonisasi_status s', 's.id = ha.id_status_ajuan', 'left');
        if ($tahun) {
            $builder->where('YEAR(ha.tanggal_pengajuan)', $tahun);
        }
        if ($id_instansi) {
            $builder->where('ha.id_instansi_pemohon', $id_instansi);
        }
        $builder->groupBy('ha.id_status_ajuan');
        $builder->orderBy('ha.id_status_ajuan');
        return $builder->get()->getResultArray();
    }

    // Statistik harmonisasi: per bulan
    public function getHarmonisasiPerBulan($tahun = null, $id_instansi = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan');
        $builder->select('MONTH(tanggal_pengajuan) as bulan, COUNT(*) as jumlah');
        if ($tahun) {
            $builder->where('YEAR(tanggal_pengajuan)', $tahun);
        }
        if ($id_instansi) {
            $builder->where('id_instansi_pemohon', $id_instansi);
        }
        $builder->groupBy('MONTH(tanggal_pengajuan)');
        $builder->orderBy('bulan');
        $result = $builder->get()->getResultArray();
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = 0;
        }
        foreach ($result as $row) {
            $months[$row['bulan']] = $row['jumlah'];
        }
        return array_values($months);
    }

    // Statistik harmonisasi: distribusi jenis peraturan
    public function getHarmonisasiByJenis($tahun = null, $id_instansi = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan ha');
        $builder->select('j.nama_jenis, COUNT(*) as jumlah');
        $builder->join('harmonisasi_jenis_peraturan j', 'j.id = ha.id_jenis_peraturan', 'left');
        if ($tahun) {
            $builder->where('YEAR(ha.tanggal_pengajuan)', $tahun);
        }
        if ($id_instansi) {
            $builder->where('ha.id_instansi_pemohon', $id_instansi);
        }
        $builder->groupBy('ha.id_jenis_peraturan');
        $builder->orderBy('jumlah', 'DESC');
        $builder->limit(10);
        return $builder->get()->getResultArray();
    }

    // Statistik harmonisasi: top 5 instansi pengusul
    public function getTopInstansiHarmonisasi($tahun = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan ha');
        $builder->select('i.nama_instansi, COUNT(*) as jumlah');
        $builder->join('instansi i', 'i.id = ha.id_instansi_pemohon', 'left');
        if ($tahun) {
            $builder->where('YEAR(ha.tanggal_pengajuan)', $tahun);
        }
        $builder->groupBy('ha.id_instansi_pemohon');
        $builder->orderBy('jumlah', 'DESC');
        $builder->limit(5);
        return $builder->get()->getResultArray();
    }

    // Statistik harmonisasi: rata-rata lama proses (hari)
    // REVISI: Menggunakan logic yang sama dengan SLA (Active only)
    public function getAvgProsesHarmonisasi($tahun = null, $id_instansi = null)
    {
        $sql = "
            WITH history_lead AS (
                SELECT 
                    hh.id_ajuan, 
                    hh.id_status_sekarang, 
                    hh.tanggal_aksi,
                    LEAD(hh.tanggal_aksi, 1) OVER (PARTITION BY hh.id_ajuan ORDER BY hh.tanggal_aksi) as next_aksi
                FROM harmonisasi_histori hh
                JOIN harmonisasi_ajuan ha ON hh.id_ajuan = ha.id
                WHERE ha.tanggal_selesai IS NOT NULL
                    " . ($tahun ? "AND YEAR(ha.tanggal_pengajuan) = " . $this->db->escape($tahun) : "") . "
                    " . ($id_instansi ? "AND ha.id_instansi_pemohon = " . $this->db->escape($id_instansi) : "") . "
            )
            SELECT AVG(active_seconds) / 86400 as rata_rata_hari FROM (
                SELECT 
                    id_ajuan,
                    SUM(
                        CASE 
                            WHEN id_status_sekarang IN (2, 3, 4, 6, 10) THEN 
                                TIMESTAMPDIFF(SECOND, tanggal_aksi, COALESCE(next_aksi, NOW()))
                            ELSE 0 
                        END
                    ) as active_seconds
                FROM history_lead
                GROUP BY id_ajuan
            ) as duration_data
        ";

        try {
            $query = $this->db->query($sql);
            $row = $query->getRowArray();
            return round($row['rata_rata_hari'] ?? 0, 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    // Statistik harmonisasi: ajuan melewati SLA (default 14 hari)
    // REVISI: Menggunakan "Stop the Clock" mechanism.
    // Waktu dihitung hanya saat di Status Admin (2,3,4,6,8,9,10,11,12)
    // Waktu PAUSE saat di Status Revisi OPD (5) atau Menunggu Paraf OPD (7)
    public function getHarmonisasiOverSLA($tahun = null, $sla_hari = 14, $id_instansi = null)
    {
        $sql = "
            WITH history_lead AS (
                SELECT 
                    hh.id_ajuan, 
                    hh.id_status_sekarang, 
                    hh.tanggal_aksi,
                    LEAD(hh.tanggal_aksi, 1) OVER (PARTITION BY hh.id_ajuan ORDER BY hh.tanggal_aksi) as next_aksi
                FROM harmonisasi_histori hh
                JOIN harmonisasi_ajuan ha ON hh.id_ajuan = ha.id
                WHERE 1=1 
                    " . ($tahun ? "AND YEAR(ha.tanggal_pengajuan) = " . $this->db->escape($tahun) : "") . "
                    " . ($id_instansi ? "AND ha.id_instansi_pemohon = " . $this->db->escape($id_instansi) : "") . "
            )
            SELECT COUNT(*) as jumlah FROM (
                SELECT 
                    id_ajuan,
                    SUM(
                        CASE 
                            WHEN id_status_sekarang IN (2, 3, 4, 6, 10) THEN 
                                TIMESTAMPDIFF(SECOND, tanggal_aksi, COALESCE(next_aksi, NOW()))
                            ELSE 0 
                        END
                    ) as active_seconds
                FROM history_lead
                GROUP BY id_ajuan
                HAVING active_seconds > (" . (int)$sla_hari . " * 24 * 3600)
            ) as over_sla_count
        ";

        try {
            $query = $this->db->query($sql);
            if (!$query) return 0; // Prevent boolean error
            $row = $query->getRowArray();
            return $row['jumlah'] ?? 0;
        } catch (\Exception $e) {
            log_message('error', 'SLA CTE Error: ' . $e->getMessage());
            return 0;
        }
    }

    // Statistik harmonisasi: ajuan per verifikator (khusus admin)
    public function getHarmonisasiPerVerifikator($tahun = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan ha');
        $builder->select('u.nama as nama_verifikator, COUNT(*) as jumlah');
        $builder->join('user u', 'u.id_user = ha.id_petugas_verifikasi', 'left');
        if ($tahun) {
            $builder->where('YEAR(ha.tanggal_pengajuan)', $tahun);
        }
        $builder->where('ha.id_petugas_verifikasi IS NOT NULL');
        $builder->groupBy('ha.id_petugas_verifikasi');
        $builder->orderBy('jumlah', 'DESC');
        $builder->limit(5);
        return $builder->get()->getResultArray();
    }

    // =========================================================================
    // LEGALISASI STATISTICS
    // Status Legalisasi: 8, 9, 11, 12, 13
    // Selesai: 14
    // =========================================================================

    public function getTotalAjuanLegalisasi($tahun = null, $id_instansi = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan');
        // Masuk tahap legalisasi = status >= 8
        $builder->where('id_status_ajuan >=', 8);
        if ($tahun) {
            $builder->where('YEAR(tanggal_pengajuan)', $tahun);
        }
        if ($id_instansi) {
            $builder->where('id_instansi_pemohon', $id_instansi);
        }
        return $builder->countAllResults();
    }

    public function getAvgProsesLegalisasi($tahun = null, $id_instansi = null)
    {
        // Active Statuses Legalisasi: 8, 9, 11, 12, 13
        $sql = "
            WITH history_lead AS (
                SELECT 
                    hh.id_ajuan, 
                    hh.id_status_sekarang, 
                    hh.tanggal_aksi,
                    LEAD(hh.tanggal_aksi, 1) OVER (PARTITION BY hh.id_ajuan ORDER BY hh.tanggal_aksi) as next_aksi
                FROM harmonisasi_histori hh
                JOIN harmonisasi_ajuan ha ON hh.id_ajuan = ha.id
                WHERE ha.id_status_ajuan = 14 -- Hanya hitung yang sudah SELESAI total? Atau yang >= 8?
                -- User request: Rata-rata proses LEGALISASI. 
                -- Bisa yang sedang berjalan (pending) atau yang sudah selesai.
                -- Untuk keadilan, biasanya yang sudah SELESAI (status 14).
                    AND ha.id_status_ajuan = 14
                    " . ($tahun ? "AND YEAR(ha.tanggal_pengajuan) = " . $this->db->escape($tahun) : "") . "
                    " . ($id_instansi ? "AND ha.id_instansi_pemohon = " . $this->db->escape($id_instansi) : "") . "
            )
            SELECT AVG(active_seconds) / 86400 as rata_rata_hari FROM (
                SELECT 
                    id_ajuan,
                    SUM(
                        CASE 
                            WHEN id_status_sekarang IN (8, 9, 11, 12, 13) THEN 
                                TIMESTAMPDIFF(SECOND, tanggal_aksi, COALESCE(next_aksi, NOW()))
                            ELSE 0 
                        END
                    ) as active_seconds
                FROM history_lead
                GROUP BY id_ajuan
                HAVING active_seconds > 0 -- Hanya yang pernah masuk legalisasi
            ) as duration_data
        ";

        try {
            $query = $this->db->query($sql);
            $row = $query->getRowArray();
            return round($row['rata_rata_hari'] ?? 0, 1);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getLegalisasiOverSLA($tahun = null, $sla_hari = 14, $id_instansi = null)
    {
        $sql = "
            WITH history_lead AS (
                SELECT 
                    hh.id_ajuan, 
                    hh.id_status_sekarang, 
                    hh.tanggal_aksi,
                    LEAD(hh.tanggal_aksi, 1) OVER (PARTITION BY hh.id_ajuan ORDER BY hh.tanggal_aksi) as next_aksi
                FROM harmonisasi_histori hh
                JOIN harmonisasi_ajuan ha ON hh.id_ajuan = ha.id
                WHERE ha.id_status_ajuan >= 8 -- Hanya yang sudah masuk legalisasi
                    " . ($tahun ? "AND YEAR(ha.tanggal_pengajuan) = " . $this->db->escape($tahun) : "") . "
                    " . ($id_instansi ? "AND ha.id_instansi_pemohon = " . $this->db->escape($id_instansi) : "") . "
            )
            SELECT COUNT(*) as jumlah FROM (
                SELECT 
                    id_ajuan,
                    SUM(
                        CASE 
                            WHEN id_status_sekarang IN (8, 9, 11, 12, 13) THEN 
                                TIMESTAMPDIFF(SECOND, tanggal_aksi, COALESCE(next_aksi, NOW()))
                            ELSE 0 
                        END
                    ) as active_seconds
                FROM history_lead
                GROUP BY id_ajuan
                HAVING active_seconds > (" . (int)$sla_hari . " * 24 * 3600)
            ) as over_sla_count
        ";

        try {
            $query = $this->db->query($sql);
            if (!$query) return 0;
            $row = $query->getRowArray();
            return $row['jumlah'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getLegalisasiSelesai($tahun = null, $id_instansi = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan');
        $builder->where('id_status_ajuan', 14); // Selesai
        if ($tahun) {
            $builder->where('YEAR(tanggal_pengajuan)', $tahun);
        }
        if ($id_instansi) {
            $builder->where('id_instansi_pemohon', $id_instansi);
        }
        return $builder->countAllResults();
    }
    
    // Helper untuk Harmonisasi Selesai (Status >= 8 dan bukan Draft)
    public function getHarmonisasiSelesai($tahun = null, $id_instansi = null)
    {
        $builder = $this->db->table('harmonisasi_ajuan');
        $builder->where('id_status_ajuan >=', 8); // Dianggap selesai harmonisasi jika sudah masuk legalisasi
        if ($tahun) {
            $builder->where('YEAR(tanggal_pengajuan)', $tahun);
        }
        if ($id_instansi) {
            $builder->where('id_instansi_pemohon', $id_instansi);
        }
        return $builder->countAllResults();
    }
}
