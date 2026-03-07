<?php

namespace App\Models;

use CodeIgniter\Model;

class WebTagModel extends Model
{
    protected $table      = 'web_tag';
    protected $primaryKey = 'id_tag';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'nama_tag', 'slug_tag'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mendapatkan semua tag
     * 
     * @return array
     */
    public function getAllTags()
    {
        return $this->orderBy('nama_tag', 'ASC')->findAll();
    }

    /**
     * Mendapatkan tag berdasarkan id
     * 
     * @param int $id
     * @return array
     */
    public function getTagById($id)
    {
        return $this->find($id);
    }

    /**
     * Mendapatkan tag berdasarkan peraturan
     * 
     * @param int $id_peraturan
     * @return array
     */
    public function getTagByPeraturan($id_peraturan)
    {
        $db = db_connect();
        $result = $db->table('web_tag t')
                ->select('t.*')
                ->join('web_peraturan_tag pt', 'pt.id_tag = t.id_tag')
                ->where('pt.id_peraturan', $id_peraturan)
                ->get()->getResultArray();
        
        // Tambahkan slug_tag jika tidak ada
        foreach ($result as &$item) {
            if (!isset($item['slug_tag']) || empty($item['slug_tag'])) {
                // Jika slug_tag tidak ada, buat dari nama tag
                $item['slug_tag'] = url_title($item['nama_tag'], '-', true);
            }
            // Untuk kompatibilitas dengan kode yang menggunakan slug
            $item['slug'] = $item['slug_tag'];
        }
        
        return $result;
    }
    
    /**
     * Mendapatkan tag berdasarkan slug
     * 
     * @param string $slug
     * @return array
     */
    public function getTagBySlug($slug)
    {
        return $this->where('slug_tag', $slug)->first();
    }

    /**
     * Membuat slug dari nama tag
     * 
     * @param string $nama_tag
     * @param int|null $id_tag
     * @return string
     */
    public function createSlug($nama_tag, $id_tag = null)
    {
        $slug = strtolower(url_title($nama_tag));
        $count = 0;
        
        // Cek apakah slug_tag sudah ada
        while(true) {
            $slugCount = $this->where('slug_tag', $slug)
                              ->where('id_tag !=', $id_tag)
                              ->countAllResults();
            if ($slugCount == 0) {
                break;
            }
            $count++;
            $slug = strtolower(url_title($nama_tag)) . '-' . $count;
        }
        
        return $slug;
    }

    /**
     * Mendapatkan tag berdasarkan keyword pencarian
     *
     * @param string|null $keyword Kata kunci pencarian
     * @param int $limit Batas jumlah data
     * @param int $offset Offset data
     * @return array
     */
    public function getTagsByKeyword($keyword = null, $limit = 10, $offset = 0)
    {
        $builder = $this->builder();
        $builder->select('id_tag, nama_tag');
        
        if (!empty($keyword)) {
            $builder->like('nama_tag', $keyword);
        }
        
        $builder->orderBy('nama_tag', 'ASC');
        $builder->limit($limit, $offset);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Menghitung jumlah tag berdasarkan keyword pencarian
     *
     * @param string|null $keyword Kata kunci pencarian
     * @return int
     */
    public function countTagsByKeyword($keyword = null)
    {
        $builder = $this->builder();
        
        if (!empty($keyword)) {
            $builder->like('nama_tag', $keyword);
        }
        
        return $builder->countAllResults();
    }
    
    /**
     * Mendapatkan tag populer berdasarkan jumlah peraturan yang terkait
     *
     * @param int $limit Batas jumlah tag yang dikembalikan
     * @return array
     */
    public function getPopularTags($limit = 5)
    {
        $db = db_connect();
        
        // Query untuk menghitung jumlah peraturan untuk setiap tag
        $db = db_connect();
        
        // Query untuk menghitung jumlah peraturan untuk setiap tag
        $db = db_connect();
        
        // Query untuk menghitung jumlah peraturan untuk setiap tag
        $result = $db->table('web_tag t')
        ->select('t.id_tag, t.nama_tag, t.slug_tag, COUNT(pt.id_peraturan) as total_peraturan')
        ->join('web_peraturan_tag pt', 'pt.id_tag = t.id_tag', 'left')
        ->join('web_peraturan p', 'p.id_peraturan = pt.id_peraturan', 'left')
        ->where('p.is_published', 1)
        ->groupBy('t.id_tag, t.nama_tag, t.slug_tag')
        ->orderBy('total_peraturan', 'DESC')
        ->limit($limit)
        ->get()
        ->getResultArray();
        
        return $result;
    }
}
