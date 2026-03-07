<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatTabelKonfigurasi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_konfigurasi' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'deskripsi' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
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
        $this->forge->addKey('id_konfigurasi', true);
        $this->forge->createTable('web_konfigurasi');
    }

    public function down()
    {
        $this->forge->dropTable('web_konfigurasi');
    }
}

