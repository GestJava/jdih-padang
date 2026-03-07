<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixHarmonisasiDokumenEnum extends Migration
{
    public function up()
    {
        // STEP 1: Ubah kolom tipe_dokumen menjadi VARCHAR sementara untuk fleksibilitas manipulasi data
        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        // STEP 2: Update data lama agar sesuai dengan format ENUM baru (UPPERCASE)
        // Gunakan raw SQL query untuk update massal
        $db = \Config\Database::connect();
        
        // Mapping: draft_pemohon -> DRAFT_AWAL
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'DRAFT_AWAL' WHERE tipe_dokumen = 'draft_pemohon'");
        
        // Mapping: hasil_verifikasi -> HASIL_VERIFIKASI
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'HASIL_VERIFIKASI' WHERE tipe_dokumen = 'hasil_verifikasi'");
        
        // Mapping: hasil_validasi -> HASIL_VALIDASI
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'HASIL_VALIDASI' WHERE tipe_dokumen = 'hasil_validasi'");
        
        // Mapping: dokumen_final -> FINAL_TTE (Asumsi dokumen_final lama adalah hasil akhir)
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'FINAL_TTE' WHERE tipe_dokumen = 'dokumen_final'");
        
        // Mapping: lampiran -> LAMPIRAN
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'LAMPIRAN' WHERE tipe_dokumen = 'lampiran'");
        
        // Mapping: jika ada data kosong tapi nama file mengandung 'draft', set DRAFT_AWAL
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'DRAFT_AWAL' WHERE (tipe_dokumen IS NULL OR tipe_dokumen = '') AND (nama_file_original LIKE '%draft%' OR nama_file_original LIKE '%konsep%')");

        // STEP 3: Ubah kembali kolom menjadi ENUM dengan definisi baru yang lengkap
        // ENUM baru mencakup semua konstanta yang dipakai di aplikasi Controller
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

    public function down()
    {
        // Revert changes if needed (not recommended to revert as it might lose new data)
        // But for completeness, we revert to old lowercase ENUM
        
        // 1. VARCHAR
        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);
        
        // 2. Map back to lowercase (Best effort)
        $db = \Config\Database::connect();
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'draft_pemohon' WHERE tipe_dokumen = 'DRAFT_AWAL'");
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'hasil_verifikasi' WHERE tipe_dokumen = 'HASIL_VERIFIKASI'");
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'hasil_validasi' WHERE tipe_dokumen = 'HASIL_VALIDASI'");
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'dokumen_final' WHERE tipe_dokumen = 'FINAL_TTE'");
        $db->query("UPDATE harmonisasi_dokumen SET tipe_dokumen = 'lampiran' WHERE tipe_dokumen = 'LAMPIRAN'");

        // 3. ENUM Old
        $this->forge->modifyColumn('harmonisasi_dokumen', [
            'tipe_dokumen' => [
                'type'       => 'ENUM',
                'constraint' => ['draft_pemohon','hasil_verifikasi','hasil_validasi','dokumen_final','lampiran'],
                'default'    => 'draft_pemohon',
            ],
        ]);
    }
}
