<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveJenisPermohonanFromAjuan extends Migration
{
    public function up()
    {
        // Hanya jalankan jika bukan SQLite dan tabelnya ada
        if ($this->db->DBDriver !== 'SQLite3' && $this->db->tableExists('data_ajuan')) {
            try {
                // Periksa apakah foreign key ada sebelum mencoba menghapusnya
                $foreignKeys = $this->db->getForeignKeyData('data_ajuan');
                $fkName = 'data_ajuan_id_jenis_permohonan_foreign';
                $fkExists = false;
                foreach ($foreignKeys as $fk) {
                    if ($fk->constraint_name === $fkName) {
                        $fkExists = true;
                        break;
                    }
                }
                if ($fkExists) {
                    $this->forge->dropForeignKey('data_ajuan', $fkName);
                }
            } catch (\Throwable $th) {
                // Abaikan jika terjadi error, mungkin karena constraint tidak ada
            }
        }

        // Hapus kolomnya jika ada
        if ($this->db->fieldExists('id_jenis_permohonan', 'data_ajuan')) {
            $this->forge->dropColumn('data_ajuan', 'id_jenis_permohonan');
        }
    }

    public function down()
    {
        // Tambahkan kembali kolom jika belum ada
        if (!$this->db->fieldExists('id_jenis_permohonan', 'data_ajuan')) {
            $this->forge->addColumn('data_ajuan', [
                'id_jenis_permohonan' => [
                    'type'       => 'INT',
                    'constraint' => 5,
                    'unsigned'   => true,
                    'null'       => false,
                    'after'      => 'perihal',
                ],
            ]);
        }

        // Tambahkan kembali foreign key jika belum ada
        if ($this->db->DBDriver !== 'SQLite3') {
            // Beri nama foreign key secara eksplisit untuk menghindari ambiguitas
            $this->forge->addForeignKey('id_jenis_permohonan', 'jenis_permohonan', 'id', 'CASCADE', 'CASCADE', 'data_ajuan_id_jenis_permohonan_foreign');
        }
    }
}