<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatbotLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'query' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'response' => [
                'type' => 'LONGTEXT',
                'null' => false,
            ],
            'search_results_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'processing_time' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'null' => true,
            ],
            'ai_response_time' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'null' => true,
            ],
            'search_strategy' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'standard',
            ],
            'entities_extracted' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'confidence_score' => [
                'type' => 'DECIMAL',
                'constraint' => '5,4',
                'null' => true,
            ],
            'feedback_rating' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'feedback_comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('session_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('feedback_rating');
        
        $this->forge->createTable('chatbot_logs');
    }

    public function down()
    {
        $this->forge->dropTable('chatbot_logs');
    }
}
