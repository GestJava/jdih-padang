<?php

namespace App\Models;

use CodeIgniter\Model;

class WebPeraturanTagModel extends Model
{
    protected $table      = 'web_peraturan_tag';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id_peraturan', 'id_tag'
    ];

    // Nonaktifkan timestamps
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mendapatkan semua tag yang terkait dengan peraturan tertentu
     *
     * @param int $id_peraturan
     * @return array
     */
    public function getTagByPeraturanId($id_peraturan)
    {
        $builder = $this->db->table($this->table . ' pt');
        $builder->select('t.*');
        $builder->join('web_tag t', 't.id_tag = pt.id_tag');
        $builder->where('pt.id_peraturan', $id_peraturan);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Mendapatkan semua id peraturan berdasarkan id tag
     *
     * @param int $id_tag
     * @return array
     */
    public function getPeraturanIdsByTagId($id_tag)
    {
        $builder = $this->select('id_peraturan')
                         ->where('id_tag', $id_tag);
        
        $result = $builder->findAll();
        
        // Extract just the id_peraturan values
        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row['id_peraturan'];
        }
        
        return $ids;
    }

    /**
     * Menghapus semua tag untuk peraturan tertentu
     *
     * @param int $id_peraturan
     * @return boolean
     */
    public function deleteByPeraturanId($id_peraturan)
    {
        return $this->where('id_peraturan', $id_peraturan)->delete();
    }

    /**
     * Menambahkan tag untuk peraturan tertentu
     *
     * @param int $id_peraturan
     * @param array $tag_ids
     * @return boolean
     */
    public function addTagToPeraturan($id_peraturan, $tag_ids)
    {
        // Hapus tag yang ada terlebih dahulu
        $this->deleteByPeraturanId($id_peraturan);
        
        // Tambahkan tag baru
        $data = [];
        foreach ($tag_ids as $id_tag) {
            $data[] = [
                'id_peraturan' => $id_peraturan,
                'id_tag' => $id_tag
            ];
        }
        
        if (!empty($data)) {
            return $this->insertBatch($data);
        }
        
        return true;
    }
}
