<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKeteranganToHarmonisasiAjuan extends Migration
{
    public function up()
    {
        $fields = [
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'tanggal_selesai',
            ],
        ];
        $this->forge->addColumn('harmonisasi_ajuan', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('harmonisasi_ajuan', 'keterangan');
    }
}
