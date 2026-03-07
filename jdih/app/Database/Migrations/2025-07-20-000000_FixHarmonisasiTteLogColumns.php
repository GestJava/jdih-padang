<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixHarmonisasiTteLogColumns extends Migration
{
    public function up()
    {
        // Menambahkan kolom yang hilang di tabel harmonisasi_tte_log agar sesuai dengan Model
        $fields = [
            'id_user' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'id_user_penandatangan'
            ],
            'id_user_request' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'id_user'
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'jenis_aksi'
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'status_tte'
            ],
            'request_payload' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'status'
            ],
            'response_payload' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'request_payload'
            ],
            'signed_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'file_signed_path'
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'signature_info'
            ],
            'document_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'metadata'
            ],
        ];

        $this->forge->addColumn('harmonisasi_tte_log', $fields);
    }

    public function down()
    {
        $columns = [
            'id_user', 'id_user_request', 'action', 'status', 
            'request_payload', 'response_payload', 'signed_path', 
            'metadata', 'document_number'
        ];
        
        foreach ($columns as $column) {
            $this->forge->dropColumn('harmonisasi_tte_log', $column);
        }
    }
}
