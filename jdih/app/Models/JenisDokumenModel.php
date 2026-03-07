<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisDokumenModel extends Model
{
    protected $table            = 'web_jenis_peraturan'; // Sesuaikan jika nama tabel berbeda
    protected $primaryKey       = 'id_jenis_peraturan'; // Diubah dari id_jenis_dokumen // Sesuaikan jika primary key berbeda
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Set true jika menggunakan soft deletes
    protected $protectFields    = true;
    protected $allowedFields    = ['kategori_nama', 'kategori_slug', 'nama_jenis', 'slug_jenis', 'deskripsi', 'icon', 'urutan', 'is_active', 'created_at', 'updated_at']; // Diubah dari nama_jenis // Sesuaikan dengan kolom di tabel Anda

    // Dates
    protected $useTimestamps = true; // Set true jika ada kolom created_at dan updated_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Jika menggunakan soft deletes

    // Validation
    protected $validationRules      = [
        'nama_jenis' => 'required|min_length[3]|max_length[255]|is_unique[web_jenis_peraturan.nama_jenis,id_jenis_peraturan,{id_jenis_peraturan}]'
    ];
    protected $validationMessages   = [
        'nama_jenis' => [
            'required' => 'Nama jenis peraturan wajib diisi.',
            'is_unique' => 'Nama jenis peraturan sudah ada.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = [];
    // protected $afterInsert    = [];
    // protected $beforeUpdate   = [];
    // protected $afterUpdate    = [];
    // protected $beforeFind     = [];
    // protected $afterFind      = [];
    // protected $beforeDelete   = [];
    // protected $afterDelete    = [];
}
