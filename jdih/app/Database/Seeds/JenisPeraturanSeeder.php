<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JenisPeraturanSeeder extends Seeder
{
    public function run()
    {
        // Data untuk web_jenis_peraturan
        $jenisPeraturanData = [
            [
                'kategori_nama' => 'Peraturan Daerah',
                'kategori_slug' => 'peraturan-daerah',
                'nama_jenis' => 'Peraturan Daerah',
                'slug_jenis' => 'peraturan-daerah',
                'deskripsi' => 'Peraturan yang dibuat oleh DPRD bersama Walikota',
                'icon' => 'fas fa-gavel',
                'urutan' => 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'kategori_nama' => 'Peraturan Walikota',
                'kategori_slug' => 'peraturan-walikota',
                'nama_jenis' => 'Peraturan Walikota',
                'slug_jenis' => 'peraturan-walikota',
                'deskripsi' => 'Peraturan yang dibuat oleh Walikota',
                'icon' => 'fas fa-file-alt',
                'urutan' => 2,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'kategori_nama' => 'Keputusan Walikota',
                'kategori_slug' => 'keputusan-walikota',
                'nama_jenis' => 'Keputusan Walikota',
                'slug_jenis' => 'keputusan-walikota',
                'deskripsi' => 'Keputusan yang dibuat oleh Walikota',
                'icon' => 'fas fa-stamp',
                'urutan' => 3,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'kategori_nama' => 'Instruksi Walikota',
                'kategori_slug' => 'instruksi-walikota',
                'nama_jenis' => 'Instruksi Walikota',
                'slug_jenis' => 'instruksi-walikota',
                'deskripsi' => 'Instruksi yang dibuat oleh Walikota',
                'icon' => 'fas fa-tasks',
                'urutan' => 4,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'kategori_nama' => 'Surat Edaran',
                'kategori_slug' => 'surat-edaran',
                'nama_jenis' => 'Surat Edaran Walikota',
                'slug_jenis' => 'surat-edaran-walikota',
                'deskripsi' => 'Surat edaran yang dibuat oleh Walikota',
                'icon' => 'fas fa-envelope',
                'urutan' => 5,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert data jenis peraturan
        $this->db->table('web_jenis_peraturan')->insertBatch($jenisPeraturanData);

        // Data untuk instansi
        $instansiData = [
            [
                'nama_instansi' => 'Sekretariat Daerah',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_instansi' => 'Dinas Pendidikan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_instansi' => 'Dinas Kesehatan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_instansi' => 'Dinas Perhubungan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_instansi' => 'Dinas Lingkungan Hidup',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_instansi' => 'Badan Perencanaan Pembangunan Daerah',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert data instansi
        $this->db->table('instansi')->insertBatch($instansiData);

        // Data untuk status_dokumen
        $statusDokumenData = [
            [
                'nama_status' => 'Berlaku',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Tidak Berlaku',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Dicabut',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Diubah',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert data status dokumen
        $this->db->table('status_dokumen')->insertBatch($statusDokumenData);

        // Data untuk tags
        $tagsData = [
            [
                'nama_tag' => 'Pendidikan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_tag' => 'Kesehatan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_tag' => 'Lingkungan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_tag' => 'Transportasi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_tag' => 'Ekonomi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_tag' => 'Sosial',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert data tags
        $this->db->table('tags')->insertBatch($tagsData);

        echo "Seeder JenisPeraturanSeeder berhasil dijalankan.\n";
    }
}
