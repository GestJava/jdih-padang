<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToUserToken extends Migration
{
    public function up()
    {
        // Add indexes to user_token table for better performance during logout
        try {
            // Check if table exists by trying to describe it
            $this->db->query('DESCRIBE user_token');

            // Add composite index for action and id_user (most used combination)
            $this->db->query('CREATE INDEX IF NOT EXISTS idx_user_token_action_user ON user_token(action, id_user)');

            // Add index for id_user only
            $this->db->query('CREATE INDEX IF NOT EXISTS idx_user_token_user ON user_token(id_user)');

            // Add index for action only
            $this->db->query('CREATE INDEX IF NOT EXISTS idx_user_token_action ON user_token(action)');

            // Add index for expires to help with cleanup
            $this->db->query('CREATE INDEX IF NOT EXISTS idx_user_token_expires ON user_token(expires)');

            log_message('info', 'Successfully added indexes to user_token table');
        } catch (\Exception $e) {
            log_message('warning', 'user_token table may not exist or indexes already exist: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Remove indexes from user_token table
        try {
            $this->db->query('DROP INDEX IF EXISTS idx_user_token_action_user');
            $this->db->query('DROP INDEX IF EXISTS idx_user_token_user');
            $this->db->query('DROP INDEX IF EXISTS idx_user_token_action');
            $this->db->query('DROP INDEX IF EXISTS idx_user_token_expires');

            log_message('info', 'Successfully removed indexes from user_token table');
        } catch (\Exception $e) {
            log_message('error', 'Failed to remove indexes from user_token table: ' . $e->getMessage());
        }
    }
}
