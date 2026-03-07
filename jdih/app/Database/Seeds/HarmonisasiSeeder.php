<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HarmonisasiSeeder extends Seeder
{
    public function run()
    {
        // Data untuk harmonisasi_status
        $statusData = [
            [
                'nama_status' => 'Diajukan',
                'deskripsi' => 'Pengajuan baru yang belum diverifikasi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Dalam Verifikasi',
                'deskripsi' => 'Sedang dalam proses verifikasi oleh petugas',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Perlu Revisi',
                'deskripsi' => 'Memerlukan perbaikan dari pemohon',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Dalam Review',
                'deskripsi' => 'Sedang dalam tahap review lanjutan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Menunggu Persetujuan',
                'deskripsi' => 'Menunggu persetujuan dari pejabat berwenang',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Disetujui',
                'deskripsi' => 'Pengajuan telah disetujui',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Ditolak',
                'deskripsi' => 'Pengajuan ditolak dengan alasan tertentu',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_status' => 'Selesai',
                'deskripsi' => 'Proses harmonisasi telah selesai',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        // Insert data status
        $this->db->table('harmonisasi_status')->insertBatch($statusData);

        // Data untuk harmonisasi_jenis_peraturan
        $jenisData = [
            [
                'nama_jenis' => 'Peraturan Daerah',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Peraturan Walikota',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Peraturan DPRD',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Keputusan Walikota',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Instruksi Walikota',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Surat Edaran Walikota',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Peraturan Kepala Daerah',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        // Insert data jenis peraturan
        $this->db->table('harmonisasi_jenis_peraturan')->insertBatch($jenisData);

        echo "Seeder HarmonisasiSeeder berhasil dijalankan.\n";
    }
}