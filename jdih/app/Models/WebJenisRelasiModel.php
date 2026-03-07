<?php

/**
 *	App Name	: JDIH Kota Padang
 *	Author		: Agus Salim
 *	Website		: https://jdih.padang.go.id
 *	Year		: 2025
 */

namespace App\Models;

use CodeIgniter\Model;

class WebJenisRelasiModel extends Model
{
    protected $table = 'web_jenis_relasi';
    protected $primaryKey = 'id_jenis_relasi';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'nama_jenis',
        'kode_jenis',
        'deskripsi',
        'auto_update_status',
        'status_target',
        'is_active',
        'urutan'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'nama_jenis' => 'required|max_length[50]',
        'kode_jenis' => 'required|max_length[20]|is_unique[web_jenis_relasi.kode_jenis,id_jenis_relasi,{id_jenis_relasi}]',
        'auto_update_status' => 'in_list[0,1]',
        'is_active' => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'nama_jenis' => [
            'required' => 'Nama jenis relasi harus diisi',
            'max_length' => 'Nama jenis relasi maksimal 50 karakter'
        ],
        'kode_jenis' => [
            'required' => 'Kode jenis relasi harus diisi',
            'max_length' => 'Kode jenis relasi maksimal 20 karakter',
            'is_unique' => 'Kode jenis relasi sudah digunakan'
        ]
    ];

    /**
     * Mendapatkan semua jenis relasi yang aktif
     * 
     * @return array
     */
    public function getActiveJenisRelasi()
    {
        return $this->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('nama_jenis', 'ASC')
            ->findAll();
    }

    /**
     * Mendapatkan jenis relasi berdasarkan kode
     * 
     * @param string $kode
     * @return array|null
     */
    public function getJenisRelasiByKode($kode)
    {
        return $this->where('kode_jenis', $kode)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Mendapatkan jenis relasi yang auto-update status
     * 
     * @return array
     */
    public function getAutoUpdateJenisRelasi()
    {
        return $this->where('is_active', 1)
            ->where('auto_update_status', 1)
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }

    /**
     * Mendapatkan dropdown options untuk form
     * 
     * @return array
     */
    public function getDropdownOptions()
    {
        $data = $this->getActiveJenisRelasi();
        $options = [];

        foreach ($data as $item) {
            $options[$item['id_jenis_relasi']] = $item['nama_jenis'];
        }

        return $options;
    }

    /**
     * Mendapatkan jenis relasi dengan informasi lengkap untuk admin
     * 
     * @return array
     */
    public function getJenisRelasiForAdmin()
    {
        return $this->select('web_jenis_relasi.*, 
                            COUNT(web_peraturan_relasi.id) as total_usage')
            ->join(
                'web_peraturan_relasi',
                'web_peraturan_relasi.id_jenis_relasi = web_jenis_relasi.id_jenis_relasi',
                'left'
            )
            ->groupBy('web_jenis_relasi.id_jenis_relasi')
            ->orderBy('web_jenis_relasi.urutan', 'ASC')
            ->findAll();
    }

    /**
     * Validasi apakah jenis relasi memerlukan auto-update status
     * 
     * @param int $id_jenis_relasi
     * @return bool
     */
    public function isAutoUpdateStatus($id_jenis_relasi)
    {
        $jenisRelasi = $this->find($id_jenis_relasi);
        return $jenisRelasi && $jenisRelasi['auto_update_status'] == 1;
    }

    /**
     * Mendapatkan status target untuk jenis relasi
     * 
     * @param int $id_jenis_relasi
     * @return int|null
     */
    public function getStatusTarget($id_jenis_relasi)
    {
        $jenisRelasi = $this->find($id_jenis_relasi);
        return $jenisRelasi ? $jenisRelasi['status_target'] : null;
    }

    /**
     * Update urutan jenis relasi
     * 
     * @param array $urutan_data Format: [id => urutan, ...]
     * @return bool
     */
    public function updateUrutan($urutan_data)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            foreach ($urutan_data as $id => $urutan) {
                $this->update($id, ['urutan' => $urutan]);
            }

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error updating urutan jenis relasi: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle status aktif jenis relasi
     * 
     * @param int $id_jenis_relasi
     * @return bool
     */
    public function toggleActive($id_jenis_relasi)
    {
        $jenisRelasi = $this->find($id_jenis_relasi);
        if (!$jenisRelasi) {
            return false;
        }

        $newStatus = $jenisRelasi['is_active'] == 1 ? 0 : 1;
        return $this->update($id_jenis_relasi, ['is_active' => $newStatus]);
    }

    /**
     * Mendapatkan statistik penggunaan jenis relasi
     * 
     * @return array
     */
    public function getUsageStatistics()
    {
        return $this->select('web_jenis_relasi.nama_jenis,
                            web_jenis_relasi.kode_jenis,
                            web_jenis_relasi.auto_update_status,
                            COUNT(web_peraturan_relasi.id) as total_usage,
                            MAX(web_peraturan_relasi.created_at) as last_used')
            ->join(
                'web_peraturan_relasi',
                'web_peraturan_relasi.id_jenis_relasi = web_jenis_relasi.id_jenis_relasi',
                'left'
            )
            ->where('web_jenis_relasi.is_active', 1)
            ->groupBy('web_jenis_relasi.id_jenis_relasi')
            ->orderBy('total_usage', 'DESC')
            ->findAll();
    }
}
