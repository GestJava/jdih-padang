<?php

namespace App\Database\Seeders;

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

        // Using Query Builder
        $this->db->table('jenis_permohonan')->insertBatch($data);
    }
}
