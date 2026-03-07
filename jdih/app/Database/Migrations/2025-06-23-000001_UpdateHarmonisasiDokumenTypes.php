<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateHarmonisasiDokumenTypes extends Migration
{
    public function up()
    {
        // Backup existing data
        $this->db->query('CREATE TABLE harmonisasi_dokumen_backup AS SELECT * FROM harmonisasi_dokumen');

        // Modify the column type
        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type' => 'ENUM',
                'constraint' => ['draft_pemohon', 'hasil_verifikasi', 'hasil_validasi', 'dokumen_final', 'lampiran'],
                'default' => 'draft_pemohon'
            ]
        ]);

        // Update existing data
        $this->db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = CASE 
            WHEN tipe_dokumen = 'draft' THEN 'draft_pemohon'
            WHEN tipe_dokumen = 'revisi' AND EXISTS (
                SELECT 1 FROM harmonisasi_histori h 
                WHERE h.id_dokumen_terkait = harmonisasi_dokumen.id 
                AND h.id_status_sekarang = 3
            ) THEN 'hasil_verifikasi'
            WHEN tipe_dokumen = 'revisi' AND EXISTS (
                SELECT 1 FROM harmonisasi_histori h 
                WHERE h.id_dokumen_terkait = harmonisasi_dokumen.id 
                AND h.id_status_sekarang = 4
            ) THEN 'hasil_validasi'
            WHEN tipe_dokumen = 'final' THEN 'dokumen_final'
            ELSE tipe_dokumen 
        END");
    }

    public function down()
    {
        // Restore original column type
        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'lampiran', 'revisi', 'final'],
                'default' => 'draft'
            ]
        ]);

        // Check if backup table exists using SHOW TABLES
        $query = $this->db->query("SHOW TABLES LIKE 'harmonisasi_dokumen_backup'");
        if ($query->getNumRows() > 0) {
            $this->db->query('TRUNCATE harmonisasi_dokumen');
            $this->db->query('INSERT INTO harmonisasi_dokumen SELECT * FROM harmonisasi_dokumen_backup');
            $this->db->query('DROP TABLE harmonisasi_dokumen_backup');
        }
    }
}
