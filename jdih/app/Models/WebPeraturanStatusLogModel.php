<?php

/**
 *	App Name	: JDIH Kota Padang
 *	Author		: Agus Salim
 *	Website		: https://jdih.padang.go.id
 *	Year		: 2025
 */

namespace App\Models;

use CodeIgniter\Model;

class WebPeraturanStatusLogModel extends Model
{
    protected $table = 'web_peraturan_status_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'id_peraturan',
        'id_status_lama',
        'id_status_baru',
        'trigger_type',
        'trigger_id',
        'keterangan',
        'user_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false; // Tidak ada updated_at karena log tidak boleh diubah

    // Validation
    protected $validationRules = [
        'id_peraturan' => 'required|is_natural_no_zero',
        'trigger_type' => 'required|in_list[manual,relasi_add,relasi_delete,system]'
    ];

    protected $validationMessages = [
        'id_peraturan' => [
            'required' => 'ID Peraturan harus diisi',
            'is_natural_no_zero' => 'ID Peraturan harus berupa angka positif'
        ],
        'trigger_type' => [
            'required' => 'Tipe trigger harus diisi',
            'in_list' => 'Tipe trigger tidak valid'
        ]
    ];

    /**
     * Mendapatkan history status peraturan
     * 
     * @param int $id_peraturan
     * @return array
     */
    public function getStatusHistory($id_peraturan)
    {
        return $this->select('web_peraturan_status_log.*, 
                            s1.nama_status as status_lama_nama,
                            s2.nama_status as status_baru_nama,
                            u.nama_lengkap as user_nama')
            ->join('status_dokumen s1', 's1.id = web_peraturan_status_log.id_status_lama', 'left')
            ->join('status_dokumen s2', 's2.id = web_peraturan_status_log.id_status_baru', 'left')
            ->join('user u', 'u.id_user = web_peraturan_status_log.user_id', 'left')
            ->where('id_peraturan', $id_peraturan)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Log perubahan status peraturan
     * 
     * @param int $id_peraturan
     * @param int|null $status_lama
     * @param int|null $status_baru
     * @param string $trigger_type
     * @param int|null $trigger_id
     * @param string|null $keterangan
     * @param int|null $user_id
     * @return bool
     */
    public function logStatusChange($id_peraturan, $status_lama, $status_baru, $trigger_type = 'manual', $trigger_id = null, $keterangan = null, $user_id = null)
    {
        // Jangan log jika status tidak berubah
        if ($status_lama == $status_baru) {
            return true;
        }

        $data = [
            'id_peraturan' => $id_peraturan,
            'id_status_lama' => $status_lama,
            'id_status_baru' => $status_baru,
            'trigger_type' => $trigger_type,
            'trigger_id' => $trigger_id,
            'keterangan' => $keterangan,
            'user_id' => $user_id ?: session('user_id')
        ];

        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Error logging status change: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mendapatkan log berdasarkan trigger relasi
     * 
     * @param int $trigger_id
     * @param string $trigger_type
     * @return array
     */
    public function getLogByTrigger($trigger_id, $trigger_type = 'relasi_add')
    {
        return $this->where('trigger_id', $trigger_id)
            ->where('trigger_type', $trigger_type)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Mendapatkan statistik perubahan status
     * 
     * @param string $periode Format: 'daily', 'weekly', 'monthly', 'yearly'
     * @param int $limit
     * @return array
     */
    public function getStatusChangeStatistics($periode = 'monthly', $limit = 12)
    {
        $dateFormat = match ($periode) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m'
        };

        return $this->select("DATE_FORMAT(created_at, '$dateFormat') as periode,
                            trigger_type,
                            COUNT(*) as total_changes")
            ->groupBy(['periode', 'trigger_type'])
            ->orderBy('periode', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mendapatkan peraturan yang sering berubah status
     * 
     * @param int $limit
     * @return array
     */
    public function getFrequentlyChangedPeraturan($limit = 10)
    {
        return $this->select('web_peraturan_status_log.id_peraturan,
                            COUNT(*) as total_changes,
                            p.nomor, p.tahun, p.judul,
                            jjd.nama_jenis,
                            MAX(web_peraturan_status_log.created_at) as last_change')
            ->join('web_peraturan p', 'p.id_peraturan = web_peraturan_status_log.id_peraturan')
            ->join('web_jenis_peraturan jjd', 'jjd.id_jenis_peraturan = p.id_jenis_dokumen', 'left')
            ->groupBy('web_peraturan_status_log.id_peraturan')
            ->having('total_changes >', 1)
            ->orderBy('total_changes', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mendapatkan aktivitas user dalam perubahan status
     * 
     * @param int|null $user_id
     * @param int $limit
     * @return array
     */
    public function getUserActivity($user_id = null, $limit = 50)
    {
        $builder = $this->select('web_peraturan_status_log.*,
                                p.nomor, p.tahun, p.judul,
                                s1.nama_status as status_lama_nama,
                                s2.nama_status as status_baru_nama,
                                u.nama_lengkap as user_nama')
            ->join('web_peraturan p', 'p.id_peraturan = web_peraturan_status_log.id_peraturan')
            ->join('status_dokumen s1', 's1.id = web_peraturan_status_log.id_status_lama', 'left')
            ->join('status_dokumen s2', 's2.id = web_peraturan_status_log.id_status_baru', 'left')
            ->join('user u', 'u.id_user = web_peraturan_status_log.user_id', 'left');

        if ($user_id) {
            $builder->where('web_peraturan_status_log.user_id', $user_id);
        }

        return $builder->orderBy('web_peraturan_status_log.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Cek apakah ada log rollback untuk relasi tertentu
     * 
     * @param int $trigger_id
     * @return array|null
     */
    public function getRollbackLog($trigger_id)
    {
        return $this->where('trigger_id', $trigger_id)
            ->where('trigger_type', 'relasi_delete')
            ->first();
    }

    /**
     * Mendapatkan summary perubahan status harian
     * 
     * @param string $date Format: Y-m-d
     * @return array
     */
    public function getDailySummary($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        return $this->select('trigger_type, COUNT(*) as total')
            ->where('DATE(created_at)', $date)
            ->groupBy('trigger_type')
            ->findAll();
    }

    /**
     * Cleanup log lama (opsional untuk maintenance)
     * 
     * @param int $days Hapus log lebih dari X hari
     * @return bool
     */
    public function cleanupOldLogs($days = 365)
    {
        try {
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
            return $this->where('created_at <', $cutoffDate)
                ->where('trigger_type', 'system') // Hanya hapus log system, bukan manual
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'Error cleaning up old logs: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Export log ke CSV untuk audit
     * 
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function exportForAudit($start_date, $end_date)
    {
        return $this->select('web_peraturan_status_log.*,
                            p.nomor, p.tahun, p.judul,
                            s1.nama_status as status_lama_nama,
                            s2.nama_status as status_baru_nama,
                            u.nama_lengkap as user_nama')
            ->join('web_peraturan p', 'p.id_peraturan = web_peraturan_status_log.id_peraturan')
            ->join('status_dokumen s1', 's1.id = web_peraturan_status_log.id_status_lama', 'left')
            ->join('status_dokumen s2', 's2.id = web_peraturan_status_log.id_status_baru', 'left')
            ->join('user u', 'u.id_user = web_peraturan_status_log.user_id', 'left')
            ->where('DATE(web_peraturan_status_log.created_at) >=', $start_date)
            ->where('DATE(web_peraturan_status_log.created_at) <=', $end_date)
            ->orderBy('web_peraturan_status_log.created_at', 'ASC')
            ->findAll();
    }
}
