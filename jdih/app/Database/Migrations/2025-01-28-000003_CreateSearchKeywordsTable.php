<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSearchKeywordsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'keyword' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'search_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'last_searched' => [
                'type' => 'DATETIME',
                'null' => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('keyword');
        $this->forge->addKey('search_count');
        $this->forge->addKey('last_searched');
        
        // Index untuk performa query popular keywords
        $this->forge->addKey(['search_count', 'last_searched'], false, false, 'idx_popular_keywords');
        
        $this->forge->createTable('search_keywords');
    }

    public function down()
    {
        $this->forge->dropTable('search_keywords');
    }
}

