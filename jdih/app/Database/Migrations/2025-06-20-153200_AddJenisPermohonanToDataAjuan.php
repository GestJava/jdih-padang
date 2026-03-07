<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenisPermohonanToDataAjuan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('data_ajuan', [
            'id_jenis_permohonan' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => false,

            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('data_ajuan', 'id_jenis_permohonan');
    }
}
