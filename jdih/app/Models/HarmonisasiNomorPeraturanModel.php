<?php

namespace App\Models;

use CodeIgniter\Model;

class HarmonisasiNomorPeraturanModel extends Model
{
    protected $table            = 'harmonisasi_nomor_peraturan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_ajuan',
        'jenis_peraturan',
        'nomor_peraturan',
        'urutan',
        'tahun',
        'tanggal_pengesahan',
        'user_role',
        'document_url',
        'tte_file_path',
        'tte_completed_at',
        'tte_user_role',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Update TTE information untuk nomor peraturan
     *
     * @param int $id_ajuan
     * @param string $jenis_peraturan
     * @param array $data Data yang akan diupdate (tte_file_path, tte_completed_at, tte_user_role, document_url)
     * @return bool
     */
    public function updateTteInfo($id_ajuan, $jenis_peraturan, $data)
    {
        $updateData = [];
        
        if (isset($data['tte_file_path'])) {
            $updateData['tte_file_path'] = $data['tte_file_path'];
        }
        
        if (isset($data['tte_completed_at'])) {
            $updateData['tte_completed_at'] = $data['tte_completed_at'];
        }
        
        if (isset($data['tte_user_role'])) {
            $updateData['tte_user_role'] = $data['tte_user_role'];
        }
        
        if (isset($data['document_url'])) {
            $updateData['document_url'] = $data['document_url'];
        }

        if (empty($updateData)) {
            return false;
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $this->where('id_ajuan', $id_ajuan)
            ->where('jenis_peraturan', $jenis_peraturan)
            ->set($updateData)
            ->update();
    }

    /**
     * Get nomor peraturan by id_ajuan and jenis_peraturan
     *
     * @param int $id_ajuan
     * @param string $jenis_peraturan
     * @return array|null
     */
    public function getByAjuanAndJenis($id_ajuan, $jenis_peraturan)
    {
        return $this->where('id_ajuan', $id_ajuan)
            ->where('jenis_peraturan', $jenis_peraturan)
            ->first();
    }
}

