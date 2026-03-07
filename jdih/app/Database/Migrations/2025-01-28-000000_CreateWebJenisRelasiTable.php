<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebJenisRelasiTable extends Migration
{
    public function up()
    {
        // Tabel web_jenis_relasi
        $this->forge->addField([
            'id_jenis_relasi' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_jenis' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'kode_jenis' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'auto_update_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
            'status_target' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => false,
            ],
            'urutan' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_jenis_relasi', true);
        $this->forge->addUniqueKey('kode_jenis');
        $this->forge->createTable('web_jenis_relasi');

        // Insert data default untuk jenis relasi
        $jenisRelasiData = [
            [
                'nama_jenis' => 'Mengubah',
                'kode_jenis' => 'mengubah',
                'deskripsi' => 'Peraturan ini mengubah atau menambahkan ketentuan pada peraturan lain',
                'auto_update_status' => 0,
                'status_target' => null,
                'is_active' => 1,
                'urutan' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Mengganti',
                'kode_jenis' => 'mengganti',
                'deskripsi' => 'Peraturan ini mengganti seluruh atau sebagian peraturan lain',
                'auto_update_status' => 0,
                'status_target' => null,
                'is_active' => 1,
                'urutan' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Mencabut',
                'kode_jenis' => 'mencabut',
                'deskripsi' => 'Peraturan ini mencabut peraturan lain',
                'auto_update_status' => 1,
                'status_target' => 3, // ID status "Tidak Berlaku"
                'is_active' => 1,
                'urutan' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Ditetapkan Oleh',
                'kode_jenis' => 'ditetapkan_oleh',
                'deskripsi' => 'Peraturan ini ditetapkan berdasarkan peraturan lain',
                'auto_update_status' => 0,
                'status_target' => null,
                'is_active' => 1,
                'urutan' => 4,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Melaksanakan',
                'kode_jenis' => 'melaksanakan',
                'deskripsi' => 'Peraturan ini melaksanakan peraturan lain',
                'auto_update_status' => 0,
                'status_target' => null,
                'is_active' => 1,
                'urutan' => 5,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_jenis' => 'Mengatur Lanjut',
                'kode_jenis' => 'mengatur_lanjut',
                'deskripsi' => 'Peraturan ini mengatur lebih lanjut peraturan lain',
                'auto_update_status' => 0,
                'status_target' => null,
                'is_active' => 1,
                'urutan' => 6,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ];

        $this->db->table('web_jenis_relasi')->insertBatch($jenisRelasiData);
    }

    public function down()
    {
        $this->forge->dropTable('web_jenis_relasi');
    }
}
