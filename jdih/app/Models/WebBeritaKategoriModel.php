<?php

namespace App\Models;

use CodeIgniter\Model;

class WebBeritaKategoriModel extends Model
{
    protected $table      = 'web_berita_kategori';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['nama_kategori', 'slug_kategori', 'deskripsi', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mendapatkan semua kategori berita
     *
     * @return array
     */
    public function getAllKategori()
    {
        return $this->orderBy('nama_kategori', 'ASC')->findAll();
    }
}
