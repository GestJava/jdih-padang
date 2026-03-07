<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJenisPermohonanTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('jenis_permohonan')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 5,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama_jenis' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '100',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('jenis_permohonan');
        }
    }

    public function down()
    {
        $this->forge->dropTable('jenis_permohonan');
    }
}
