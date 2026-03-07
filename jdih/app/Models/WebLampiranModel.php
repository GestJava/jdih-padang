<?php

namespace App\Models;

use CodeIgniter\Model;

class WebLampiranModel extends Model
{
    protected $table            = 'web_lampiran';
    protected $primaryKey       = 'id_lampiran';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id_peraturan',
        'judul_lampiran',
        'file_lampiran',
        'urutan',
        'original_name',
        'file_size',
        'mime_type',
        'download_count'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'id_peraturan' => 'required|integer',
        'judul_lampiran' => 'required|max_length[255]',
        'file_lampiran' => 'required|max_length[255]',
        'urutan' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'id_peraturan' => [
            'required' => 'ID Peraturan harus diisi',
            'integer' => 'ID Peraturan harus berupa angka'
        ],
        'judul_lampiran' => [
            'required' => 'Judul lampiran harus diisi',
            'max_length' => 'Judul lampiran maksimal 255 karakter'
        ]
    ];

    /**
     * Increment download counter
     */
    public function incrementDownload($id_lampiran)
    {
        return $this->set('download_count', 'download_count + 1', false)
            ->where('id_lampiran', $id_lampiran)
            ->update();
    }
}
