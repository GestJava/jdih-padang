<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Chatbot extends BaseConfig
{
    /**
     * Daftar stopwords bahasa Indonesia untuk diabaikan dalam query chatbot.
     * @var array<int, string>
     */
    public array $stopwords = [
        'yang', 'dan', 'di', 'ke', 'dari', 'pada', 'untuk', 'adalah', 'dengan', 'ini', 'itu',
        'atau', 'juga', 'serta', 'dalam', 'akan', 'oleh', 'ada', 'tidak', 'bisa', 'dapat',
        'tentang', 'bagaimana', 'apa', 'siapa', 'kapan', 'dimana', 'mengapa', 'kenapa',
        'tolong', 'mohon', 'bantu', 'cari', 'carikan', 'mencari', 'ingin', 'mau', 'butuh',
        'peraturan', 'dokumen', 'undang', 'undang-undang', 'terkait', 'mengenai', 'berkaitan',
        'berhubungan', 'saya', 'kamu', 'anda', 'kita', 'kami', 'mereka', 'dia', 'beliau',
        // Added stopwords for better long query handling
        'karena', 'sedang', 'menulis', 'makalah', 'rentan', 'tersebut', 'yaitu', 'merupakan',
        'bagi', 'demi', 'guna', 'supaya', 'agar', 'namun', 'tetapi', 'yakni', 'misalnya',
        'contohnya', 'seperti', 'sebagai', 'terhadap'
    ];

    /**
     * Pemetaan kata kunci ke slug jenis peraturan.
     * @var array<string, string>
     */
    public array $jenisPeraturanKeywords = [
        'perda'                 => 'peraturan-daerah',
        'peraturan daerah'      => 'peraturan-daerah',
        'perwako'               => 'peraturan-walikota',
        'peraturan walikota'    => 'peraturan-walikota',
        'peraturan wali kota'   => 'peraturan-walikota',
        'kepwako'               => 'keputusan-walikota',
        'keputusan walikota'    => 'keputusan-walikota',
        'keputusan wali kota'   => 'keputusan-walikota',
        'instruksi walikota'    => 'instruksi-walikota',
        'instruksi wali kota'   => 'instruksi-walikota',
        'surat edaran'          => 'surat-edaran',
        'mou'                   => 'mou-nota-kesepahaman',
        'nota kesepahaman'      => 'mou-nota-kesepahaman'
    ];
}
