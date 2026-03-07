<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHistoryToDokumenEnum extends Migration
{
    public function up()
    {
        // 1. Ubah ke VARCHAR dulu agar aman
        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);

        // 2. Ubah kembali ke ENUM dengan tambahan 'HISTORY'
        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'DRAFT_AWAL',
                    'HASIL_VERIFIKASI',
                    'HASIL_VALIDASI',
                    'REVISI_OPD',
                    'REVISI_FINALISASI',
                    'FINAL_PARAF',
                    'FINAL_TTE',
                    'LAMPIRAN',
                    'HISTORY' // Tambahan baru
                ],
                'default'    => 'DRAFT_AWAL',
            ],
        ]);
    }

    public function down()
    {
        // Kembalikan ke ENUM sebelumnya (tanpa HISTORY)
        // PERINGATAN: Dokumen dengan status HISTORY akan error jika dikembalikan tanpa mapping
        $db = \Config\Database::connect();
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'LAMPIRAN' WHERE tipe_dokumen = 'HISTORY'");

        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'DRAFT_AWAL',
                    'HASIL_VERIFIKASI',
                    'HASIL_VALIDASI',
                    'REVISI_OPD',
                    'REVISI_FINALISASI',
                    'FINAL_PARAF',
                    'FINAL_TTE',
                    'LAMPIRAN'
                ],
                'default'    => 'DRAFT_AWAL',
            ],
        ]);
    }
}
