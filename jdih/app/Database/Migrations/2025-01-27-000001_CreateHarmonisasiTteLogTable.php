<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHarmonisasiTteLogTable extends Migration
{
    public function up()
    {
        // Tabel harmonisasi_tte_log untuk mencatat aktivitas TTE
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_ajuan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_dokumen' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_user_penandatangan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'jenis_aksi' => [
                'type' => 'ENUM',
                'constraint' => ['sign', 'verify', 'reject'],
                'null' => false,
            ],
            'status_tte' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'success', 'failed'],
                'null' => false,
                'default' => 'pending',
            ],
            'response_tte' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_signed_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'signature_info' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_ajuan', 'harmonisasi_ajuan', 'id_ajuan', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_dokumen', 'harmonisasi_dokumen', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_user_penandatangan', 'user', 'id_user', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('harmonisasi_tte_log');

        // Add indexes for better performance
        $this->db->query('CREATE INDEX idx_harmonisasi_tte_log_ajuan ON harmonisasi_tte_log(id_ajuan)');
        $this->db->query('CREATE INDEX idx_harmonisasi_tte_log_status ON harmonisasi_tte_log(status_tte)');
        $this->db->query('CREATE INDEX idx_harmonisasi_tte_log_created ON harmonisasi_tte_log(created_at)');
    }

    public function down()
    {
        $this->forge->dropTable('harmonisasi_tte_log', true);
    }
}