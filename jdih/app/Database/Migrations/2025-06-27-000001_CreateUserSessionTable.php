<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSessionTable extends Migration
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
            'id_user' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'device_info' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'last_activity' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_user');
        $this->forge->addKey('session_id');
        $this->forge->addKey(['id_user', 'is_active']);
        $this->forge->addKey('last_activity');
        $this->forge->addKey('expires_at');

        $this->forge->addForeignKey('id_user', 'user', 'id_user', 'CASCADE', 'CASCADE');

        $this->forge->createTable('user_session');
    }

    public function down()
    {
        $this->forge->dropTable('user_session');
    }
}
