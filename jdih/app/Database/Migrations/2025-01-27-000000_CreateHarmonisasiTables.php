<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHarmonisasiTables extends Migration
{
    public function up()
    {
        // Tabel harmonisasi_jenis_peraturan
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_jenis' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
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
        $this->forge->createTable('harmonisasi_jenis_peraturan');

        // Tabel harmonisasi_status
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nama_status' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'deskripsi' => [
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
        $this->forge->createTable('harmonisasi_status');

        // Tabel harmonisasi_ajuan
        $this->forge->addField([
            'id_ajuan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'judul_peraturan' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => false,
            ],
            'id_jenis_peraturan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_instansi_pemohon' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_user_pemohon' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_status_ajuan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
                'default' => 1,
            ],
            'id_petugas_verifikasi' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tanggal_pengajuan' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'tanggal_selesai' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('id_ajuan', true);
        $this->forge->addForeignKey('id_jenis_peraturan', 'harmonisasi_jenis_peraturan', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_instansi_pemohon', 'instansi', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_user_pemohon', 'user', 'id_user', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_status_ajuan', 'harmonisasi_status', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_petugas_verifikasi', 'user', 'id_user', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('harmonisasi_ajuan');

        // Tabel harmonisasi_dokumen
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_ajuan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_user_uploader' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'tipe_dokumen' => [
                'type' => 'ENUM',
                'constraint' => ['draft_pemohon', 'hasil_verifikasi', 'hasil_validasi', 'dokumen_final', 'lampiran'],
                'null' => false,
                'default' => 'draft_pemohon',
            ],
            'nama_file_original' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'path_file_storage' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_ajuan', 'harmonisasi_ajuan', 'id_ajuan', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_user_uploader', 'user', 'id_user', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('harmonisasi_dokumen');

        // Tabel harmonisasi_histori
        $this->forge->addField([
            'id_histori' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_ajuan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_user_aksi' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'id_status_sebelumnya' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_status_sekarang' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'id_dokumen_terkait' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tanggal_aksi' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);
        $this->forge->addKey('id_histori', true);
        $this->forge->addForeignKey('id_ajuan', 'harmonisasi_ajuan', 'id_ajuan', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_user_aksi', 'user', 'id_user', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_status_sebelumnya', 'harmonisasi_status', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('id_status_sekarang', 'harmonisasi_status', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_dokumen_terkait', 'harmonisasi_dokumen', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('harmonisasi_histori');

        // Insert data default untuk harmonisasi_status
        $statusData = [
            ['nama_status' => 'Diajukan', 'deskripsi' => 'Pengajuan baru yang belum diverifikasi'],
            ['nama_status' => 'Dalam Verifikasi', 'deskripsi' => 'Sedang dalam proses verifikasi'],
            ['nama_status' => 'Perlu Revisi', 'deskripsi' => 'Memerlukan perbaikan dari pemohon'],
            ['nama_status' => 'Disetujui', 'deskripsi' => 'Pengajuan telah disetujui'],
            ['nama_status' => 'Ditolak', 'deskripsi' => 'Pengajuan ditolak'],
            ['nama_status' => 'Selesai', 'deskripsi' => 'Proses harmonisasi telah selesai'],
        ];
        $this->db->table('harmonisasi_status')->insertBatch($statusData);

        // Insert data default untuk harmonisasi_jenis_peraturan
        $jenisData = [
            ['nama_jenis' => 'Peraturan Daerah'],
            ['nama_jenis' => 'Peraturan Walikota'],
            ['nama_jenis' => 'Peraturan DPRD'],
            ['nama_jenis' => 'Keputusan Walikota'],
            ['nama_jenis' => 'Instruksi Walikota'],
        ];
        $this->db->table('harmonisasi_jenis_peraturan')->insertBatch($jenisData);
    }

    public function down()
    {
        // Drop tables in reverse order due to foreign key constraints
        $this->forge->dropTable('harmonisasi_histori', true);
        $this->forge->dropTable('harmonisasi_dokumen', true);
        $this->forge->dropTable('harmonisasi_ajuan', true);
        $this->forge->dropTable('harmonisasi_status', true);
        $this->forge->dropTable('harmonisasi_jenis_peraturan', true);
    }
}
