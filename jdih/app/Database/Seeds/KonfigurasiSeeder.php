<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KonfigurasiSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'key' => 'nama_website',
                'value' => 'JDIH Kota Padang',
                'deskripsi' => 'Nama lengkap website, ditampilkan di judul halaman.',
            ],
            [
                'key' => 'alamat',
                'value' => 'Jl. Bagindo Aziz Chan No. 1, Aie Pacah, Kota Padang, Sumatera Barat',
                'deskripsi' => 'Alamat kantor atau lokasi fisik.',
            ],
            [
                'key' => 'email',
                'value' => 'bagianhukum@padang.go.id',
                'deskripsi' => 'Alamat email resmi untuk kontak.',
            ],
            [
                'key' => 'telepon',
                'value' => '081169112112',
                'deskripsi' => 'Nomor telepon resmi untuk kontak.',
            ],
            [
                'key' => 'maps',
                'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.333622839234!2d100.45025237496523!3d-0.892943899092438!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2fd4b8ab45bee081%3A0x154f3583515712c!2sKantor%20Wali%20Kota%20Padang!5e0!3m2!1sid!2sid!4v1695715583620!5m2!1sid!2sid',
                'deskripsi' => 'URL embed Google Maps untuk ditampilkan di halaman kontak.',
            ],
        ];

        // Using Query Builder
        $this->db->table('web_konfigurasi')->insertBatch($data);
    }
}
