<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatbotFeedbackTable extends Migration
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
            'log_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
            'feedback_type' => [
                'type' => 'ENUM',
                'constraint' => ['rating', 'suggestion', 'complaint', 'compliment', 'bug_report'],
                'null' => false,
            ],
            'rating' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'suggestion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'query_satisfaction' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'response_accuracy' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'response_helpfulness' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'system_performance' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'improvement_areas' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'contact_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'follow_up_required' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'reviewed', 'resolved', 'closed'],
                'default' => 'pending',
            ],
            'admin_response' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'admin_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('log_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('session_id');
        $this->forge->addKey('feedback_type');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addKey('follow_up_required');
        
        // Add foreign key constraints if needed
        // $this->forge->addForeignKey('log_id', 'chatbot_logs', 'id', 'CASCADE', 'SET NULL');
        // $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        // $this->forge->addForeignKey('admin_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        
        $this->forge->createTable('chatbot_feedback');
    }

    public function down()
    {
        $this->forge->dropTable('chatbot_feedback');
    }
}