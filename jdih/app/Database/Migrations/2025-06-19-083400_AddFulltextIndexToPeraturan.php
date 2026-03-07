<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFulltextIndexToPeraturan extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE web_peraturan ADD FULLTEXT INDEX peraturan_fulltext_search (judul, abstrak_teks, catatan_teks)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE web_peraturan DROP INDEX peraturan_fulltext_search');
    }
}
