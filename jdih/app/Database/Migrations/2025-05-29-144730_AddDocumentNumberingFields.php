<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocumentNumberingFields extends Migration
{
    protected $db;
    protected $forge;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->forge = \Config\Database::forge();
    }
    public function up()
    {
        // Buat koneksi database
        $db = \Config\Database::connect();
        
        // Buat tabel baru dengan nama sementara
        $tempTableName = 'jdih_document_numbering_new';
        
        // Buat definisi kolom
        $fields = [
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'jenis_dokumen' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'tahun' => [
                'type' => 'INT',
                'constraint' => 4,
                'null' => false
            ],
            'last_number' => [
                'type' => 'INT',
                'default' => 0,
                'null' => false
            ],
            'prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'default' => 'DOC'
            ],
            'format' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => '{PREFIX}/{NUMBER}/{YEAR}'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];
        
        // Buat tabel baru
        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['jenis_dokumen', 'tahun'], false, false, 'idx_jenis_tahun');
        $this->forge->createTable($tempTableName, true);
        
        // Cek apakah tabel lama ada
        if ($db->tableExists('jdih_document_numbering')) {
            // Ambil data dari tabel lama
            $query = $db->query('SELECT * FROM jdih_document_numbering');
            $existingData = $query->getResultArray();
            
            if (!empty($existingData)) {
                // Mapping prefix berdasarkan jenis dokumen
                $prefixMap = [
                    'Keputusan Sekre' => 'SEKRE',
                    'Keputusan Walik' => 'KEPWALI'
                ];
                
                $batchData = [];
                foreach ($existingData as $row) {
                    $prefix = 'DOC';
                    foreach ($prefixMap as $key => $value) {
                        if (strpos($row['jenis_dokumen'], $key) === 0) {
                            $prefix = $value;
                            break;
                        }
                    }
                    
                    $batchData[] = [
                        'jenis_dokumen' => $row['jenis_dokumen'],
                        'tahun' => $row['tahun'],
                        'last_number' => $row['last_number'],
                        'prefix' => $prefix,
                        'format' => '{PREFIX}/{NUMBER}/{YEAR}',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                
                // Masukkan data ke tabel baru
                if (!empty($batchData)) {
                    $db->table($tempTableName)->insertBatch($batchData);
                }
            }
            
            // Hapus tabel lama
            $this->forge->dropTable('jdih_document_numbering', true);
        }
        
        // Ubah nama tabel baru menjadi nama asli
        $this->db->query("RENAME TABLE `{$tempTableName}` TO `jdih_document_numbering`");
    }

    public function down()
    {
        // Hapus tabel jika diperlukan (hati-hati dengan data yang ada)
        // $this->forge->dropTable('jdih_document_numbering');
        
        // Atau hapus kolom yang ditambahkan
        $this->forge->dropColumn('jdih_document_numbering', 'format');
        $this->forge->dropColumn('jdih_document_numbering', 'prefix');
        $this->forge->dropColumn('jdih_document_numbering', 'created_at');
        $this->forge->dropColumn('jdih_document_numbering', 'updated_at');
    }
}
