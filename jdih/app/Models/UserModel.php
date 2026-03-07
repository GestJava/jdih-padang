<?php

namespace App\Models;

class UserModel extends BaseModel
{
    protected $table = 'user';
    protected $primaryKey = 'id_user';

    /**
     * Mengambil semua pengguna aktif yang memiliki peran tertentu.
     *
     * @param string $roleName Nama peran yang dicari (misal: 'Verifikator Harmonisasi')
     * @return array Daftar pengguna
     */
    public function getUsersByRoleName($roleName)
    {
        return $this->db->table('user u')
                        ->select('u.id_user, u.nama')
            ->join('user_role ur', 'ur.id_user = u.id_user')
            ->join('role r', 'r.id_role = ur.id_role')
            ->where('r.nama_role', $roleName)
            ->where('u.status', 'active')
                        ->orderBy('u.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil semua pengguna aktif yang memiliki peran tertentu berdasarkan ID peran.
     *
     * @param int $roleId ID peran yang dicari (misal: 7 untuk Verifikator Harmonisasi)
     * @return array Daftar pengguna
     */
    public function getUsersByRoleId($roleId)
    {
        return $this->db->table('user u')
            ->select('u.id_user as id, u.nama')
            ->join('user_role ur', 'ur.id_user = u.id_user')
            ->where('ur.id_role', $roleId)
            ->where('u.status', 'active')
            ->orderBy('u.nama', 'ASC')
            ->get()
            ->getResultArray();
    }
}
