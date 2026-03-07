<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupDuplicateFinalParaf extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Query untuk mencari id_ajuan yang memiliki lebih dari satu FINAL_PARAF
        // dan mengubah tipe_dokumen yang lebih lama menjadi HISTORY
        $query = "
            UPDATE harmonisasi_dokumen d1
            JOIN (
                SELECT id_ajuan, MAX(id) as max_id
                FROM harmonisasi_dokumen
                WHERE tipe_dokumen = 'FINAL_PARAF'
                GROUP BY id_ajuan
                HAVING COUNT(*) > 1
            ) d2 ON d1.id_ajuan = d2.id_ajuan
            SET d1.tipe_dokumen = 'HISTORY'
            WHERE d1.tipe_dokumen = 'FINAL_PARAF' AND d1.id < d2.max_id
        ";
        
        $db->query($query);
        
        log_message('info', 'Migration: CleanupDuplicateFinalParaf executed. Rows affected: ' . $db->affectedRows());
    }

    public function down()
    {
        // Not reversible without losing version info
    }
}
