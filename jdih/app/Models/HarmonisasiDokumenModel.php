<?php

namespace App\Models;

use CodeIgniter\Model;

class HarmonisasiDokumenModel extends Model
{
    protected $table            = 'harmonisasi_dokumen';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_ajuan',
        'id_user_uploader',
        'tipe_dokumen',
        'nama_file_original',
        'path_file_storage'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null;
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get documents by ajuan ID
     *
     * @param int $id_ajuan
     * @return array
     */
    public function getDokumenByAjuan($id_ajuan)
    {
        return $this->select('id, tipe_dokumen, nama_file_original, path_file_storage, created_at')
            ->where('id_ajuan', $id_ajuan)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
