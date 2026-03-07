<?php

namespace App\Models;

use CodeIgniter\Model;

class WebJenisPeraturanModel extends Model
{
    protected $table            = 'web_jenis_peraturan';
    protected $primaryKey       = 'id_jenis_peraturan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kategori_nama',
        'kategori_slug',
        'nama_jenis',
        'slug_jenis',
        'deskripsi',
        'icon',
        'urutan',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil semua jenis peraturan yang aktif berdasarkan slug kategori induknya.
     *
     * @param string $kategoriSlug Slug dari tabel web_kategori.
     * @return array
     */
    public function getJenisByKategoriSlug($kategoriSlug)
    {
        return $this->where('kategori_slug', $kategoriSlug)
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }
}
