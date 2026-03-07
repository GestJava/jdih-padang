<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatbotInteractionsTable extends Migration
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
            'interaction_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'unique'     => true,
            ],
            'user_query' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ai_response' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'entities' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'search_results' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'search_params' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('chatbot_interactions');
    }

    public function down()
    {
        $this->forge->dropTable('chatbot_interactions');
    }
}
