<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFulltextIndexToPeraturan extends Migration
{
    public function up()
    {
        // Periksa apakah tabel ada sebelum mencoba mengubahnya
        if ($this->db->tableExists('web_peraturan')) {
            // Tambahkan FULLTEXT index pada kolom judul, abstrak_teks, dan catatan_teks
            // Ini akan memungkinkan pencarian berbasis relevansi yang jauh lebih cepat dan akurat
            $this->db->query('ALTER TABLE web_peraturan ADD FULLTEXT KEY `judul_fulltext` (`judul`)');
            $this->db->query('ALTER TABLE web_peraturan ADD FULLTEXT KEY `konten_fulltext` (`abstrak_teks`, `catatan_teks`)');
        
            log_message('info', 'Successfully added FULLTEXT indexes to web_peraturan table.');
        } else {
            log_message('error', 'Table web_peraturan does not exist. Skipping migration.');
        }
    }

    public function down()
    {
        // Hapus FULLTEXT index jika migrasi di-rollback
        if ($this->db->tableExists('web_peraturan')) {
            // Perlu memeriksa apakah indeks ada sebelum menghapusnya untuk menghindari error
            $indexes = $this->db->getIndexData('web_peraturan');
            
            if (isset($indexes['judul_fulltext'])) {
                $this->db->query('ALTER TABLE `web_peraturan` DROP INDEX `judul_fulltext`');
            }

            if (isset($indexes['konten_fulltext'])) {
                $this->db->query('ALTER TABLE `web_peraturan` DROP INDEX `konten_fulltext`');
            }

            log_message('info', 'Successfully removed FULLTEXT indexes from web_peraturan table.');
        } else {
             log_message('error', 'Table web_peraturan does not exist. Skipping migration rollback.');
        }
    }
}
