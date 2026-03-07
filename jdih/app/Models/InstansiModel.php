<?php

namespace App\Models;

use CodeIgniter\Model;

class InstansiModel extends Model
{
    protected $table            = 'instansi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['nama_instansi', 'singkatan', 'id_asisten'];

    public function searchInstansi($keyword)
    {
        $builder = $this->db->table($this->table);
        $builder->select('id, nama_instansi as text'); // Select2 expects 'id' and 'text'

        if (!empty($keyword)) {
            $builder->like('nama_instansi', $keyword);
        }

        $builder->orderBy('nama_instansi', 'ASC');
        $builder->limit(20); // Limit results for performance

        $query = $builder->get();
        return $query->getResultArray();
    }

    /**
     * Get instansi yang menjadi kewenangan asisten
     * 
     * @param int $id_user_asisten ID user asisten
     * @return array List instansi yang menjadi kewenangan asisten
     */
    public function getInstansiByAsisten($id_user_asisten)
    {
        return $this->where('id_asisten', $id_user_asisten)
            ->orderBy('nama_instansi', 'ASC')
            ->findAll();
    }

    /**
     * Get asisten yang handle instansi tertentu
     * 
     * @param int $id_instansi ID instansi
     * @return array|null Data asisten atau null jika tidak ada
     */
    public function getAsistenByInstansi($id_instansi)
    {
        $instansi = $this->find($id_instansi);
        if (!$instansi || !$instansi['id_asisten']) {
            return null;
        }

        // Get user data
        $user = $this->db->table('user')
            ->where('id_user', $instansi['id_asisten'])
            ->where('status', 'active')
            ->get()
            ->getRowArray();
        
        return $user ?: null;
    }

    /**
     * Check apakah asisten punya kewenangan untuk instansi
     * 
     * @param int $id_user_asisten ID user asisten
     * @param int $id_instansi ID instansi
     * @return bool True jika asisten punya akses
     */
    public function hasAccess($id_user_asisten, $id_instansi)
    {
        if (!$id_user_asisten || !$id_instansi) {
            return false;
        }

        $instansi = $this->find($id_instansi);
        return !empty($instansi) && (int) ($instansi['id_asisten'] ?? 0) === (int) $id_user_asisten;
    }
}
