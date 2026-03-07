<?php

namespace App\Models;

use CodeIgniter\Model;

class HarmonisasiHistoriModel extends Model
{
    protected $table            = 'harmonisasi_histori';
    protected $primaryKey       = 'id_histori';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_ajuan',
        'id_user_aksi',
        'id_status_sebelumnya',
        'id_status_sekarang',
        'keterangan',
        'id_dokumen_terkait',
        'tanggal_aksi'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'tanggal_aksi';
    protected $updatedField  = null; // Tidak ada updated_at untuk tabel ini

    /**
     * Mencatat histori baru secara otomatis.
     *
     * @param array $data Data histori
     * @return bool
     */
    public function logHistory(array $data)
    {
        // Pastikan tanggal_aksi diisi jika tidak ada
        if (!isset($data['tanggal_aksi'])) {
            $data['tanggal_aksi'] = date('Y-m-d H:i:s');
        }

        return $this->insert($data);
    }

    /**
     * Mengambil riwayat lengkap untuk sebuah ajuan, diurutkan dari yang terbaru.
     *
     * @param int $id_ajuan
     * @return array
     */
    public function getHistoryByAjuan($id_ajuan)
    {
        return $this->db->table($this->table . ' h')
            ->select('h.*, u.nama as nama_user, s_sebelum.nama_status as status_sebelum, s_sekarang.nama_status as status_sekarang')
            ->join('user u', 'u.id_user = h.id_user_aksi', 'left')
            ->join('harmonisasi_status s_sebelum', 's_sebelum.id = h.id_status_sebelumnya', 'left')
            ->join('harmonisasi_status s_sekarang', 's_sekarang.id = h.id_status_sekarang', 'left')
            ->where('h.id_ajuan', $id_ajuan)
            ->orderBy('h.tanggal_aksi', 'DESC')
            ->get()
            ->getResultArray();
    }
}
