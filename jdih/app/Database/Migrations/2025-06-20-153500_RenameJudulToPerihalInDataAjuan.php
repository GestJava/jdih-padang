<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameJudulToPerihalInDataAjuan extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('judul', 'data_ajuan')) {
            $fields = [
                'judul' => [
                    'name' => 'perihal',
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                ],
            ];
            $this->forge->modifyColumn('data_ajuan', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('perihal', 'data_ajuan')) {
            $fields = [
                'perihal' => [
                    'name' => 'judul',
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                ],
            ];
            $this->forge->modifyColumn('data_ajuan', $fields);
        }
    }
}
