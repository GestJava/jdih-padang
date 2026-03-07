<?php

namespace App\Models;

use CodeIgniter\Model;

class WebPeraturanKategoriModel extends Model
{
    protected $table      = 'web_peraturan_kategori';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id_peraturan', 'id_kategori'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mendapatkan semua kategori yang terkait dengan peraturan tertentu
     *
     * @param int $id_peraturan
     * @return array
     */
    public function getKategoriByPeraturanId($id_peraturan)
    {
        // Karena tabel web_kategori sudah dihapus, kita hanya mengembalikan array kosong
        // untuk mencegah error
        return [];
        
        // Kode asli yang menggunakan tabel web_kategori yang sudah dihapus:
        // $builder = $this->db->table($this->table . ' pk');
        // $builder->select('k.*');
        // $builder->join('web_kategori k', 'k.id_kategori = pk.id_kategori');
        // $builder->where('pk.id_peraturan', $id_peraturan);
        // 
        // return $builder->get()->getResultArray();
    }

    /**
     * Mendapatkan semua id peraturan berdasarkan id kategori
     *
     * @param int $id_kategori
     * @return array
     */
    public function getPeraturanIdsByKategoriId($id_kategori)
    {
        $builder = $this->select('id_peraturan')
                         ->where('id_kategori', $id_kategori);
        
        return $builder->findAll();
    }

    /**
     * Menyimpan kategori untuk peraturan
     *
     * @param int $id_peraturan
     * @param array $kategori_ids
     * @return bool
     */
    public function saveKategori($id_peraturan, $kategori_ids = [])
    {
        // Hapus kategori yang ada
        $this->where('id_peraturan', $id_peraturan)->delete();
        
        // Jika tidak ada kategori baru, selesai
        if (empty($kategori_ids)) {
            return true;
        }
        
        // Simpan kategori baru
        $data = [];
        foreach ($kategori_ids as $id_kategori) {
            $data[] = [
                'id_peraturan' => $id_peraturan,
                'id_kategori' => $id_kategori,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->insertBatch($data);
    }
}
