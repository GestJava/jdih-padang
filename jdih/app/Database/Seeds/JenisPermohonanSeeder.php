<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JenisPermohonanSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'nama_jenis' => 'Keputusan Walikota',
            ],
            [
                'id' => 2,
                'nama_jenis' => 'Keputusan Sekda',
            ],
        ];

        // Hanya seed jika tabel kosong untuk menghindari error foreign key
        if ($this->db->table('jenis_permohonan')->countAll() == 0) {
            $this->db->table('jenis_permohonan')->insertBatch($data);
        }
    }
}
