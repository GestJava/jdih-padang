<?php

namespace App\Models;

use CodeIgniter\Model;

class HarmonisasiTteLogModel extends Model
{
    protected $table            = 'harmonisasi_tte_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_ajuan',
        'id_dokumen',
        'id_user_penandatangan',
        'id_user',
        'id_user_request',
        'jenis_aksi',
        'action',
        'status_tte',
        'status',
        'response_tte',
        'response_payload',
        'request_payload',
        'error_message',
        'file_signed_path',
        'signed_path',
        'signature_info',
        'metadata',
        'document_number'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mencatat log permintaan TTE baru.
     *
     * @param array $data
     * @return int|string (ID log yang baru dibuat)
     */
    public function logRequest(array $data)
    {
        return $this->insert($data, true);
    }

    /**
     * Memperbarui log TTE dengan respons dari server.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateResponse($id, array $data)
    {
        return $this->update($id, $data);
    }
}
