<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class NLPConfig extends BaseConfig
{
    /**
     * Advanced stopwords untuk preprocessing yang lebih baik
     */
    public array $advancedStopwords = [
        // Stopwords dasar
        'yang', 'dan', 'di', 'ke', 'dari', 'pada', 'untuk', 'dengan', 'oleh', 'dalam', 'adalah', 'ada', 'akan', 'atau', 'juga', 'ini', 'itu', 'tersebut', 'dapat', 'bisa', 'sudah', 'telah', 'masih', 'belum', 'tidak', 'jangan', 'harus', 'wajib', 'boleh', 'mungkin', 'barangkali', 'kiranya', 'agaknya',
        
        // Kata tanya yang tidak informatif untuk pencarian
        'apa', 'apakah', 'bagaimana', 'mengapa', 'kenapa', 'dimana', 'kapan', 'siapa', 'berapa',
        
        // Kata penghubung
        'tetapi', 'namun', 'akan tetapi', 'sedangkan', 'sementara', 'karena', 'sebab', 'akibat', 'sehingga', 'supaya', 'agar', 'jika', 'kalau', 'bila', 'seandainya', 'andaikan', 'sekiranya',
        
        // Kata keterangan waktu umum
        'sekarang', 'kini', 'saat ini', 'hari ini', 'kemarin', 'besok', 'nanti', 'dulu', 'dahulu', 'sebelumnya', 'sesudahnya',
        
        // Kata sapaan dan kesopanan
        'tolong', 'mohon', 'silakan', 'terima kasih', 'maaf', 'permisi', 'selamat', 'halo', 'hai', 'assalamualaikum',

        // Kata-kata percakapan umum yang sering muncul
        'saya', 'aku', 'anda', 'kamu', 'dia', 'ia', 'kami', 'kita', 'beliau',
        'mau', 'ingin', 'hendak', 'minta', 'coba',
        'tanya', 'bertanya', 'menanyakan', 'carikan', 'berikan', 'jelaskan', 'tunjukkan',
        'dong', 'sih', 'deh', 'kok', 'ya', 'kak', 'min', 'admin', 'pak', 'bu',
        'tentang', 'mengenai', 'perihal', 'berkaitan', 'soal',
        'peraturan', 'dokumen', 'hukum', 'undang',
        'banget', 'sekali', 'sangat'
    ];

    /**
     * Kata kunci penting yang tidak boleh dihapus meski mirip stopword
     */
    public array $protectedKeywords = [
        'pajak', 'retribusi', 'perizinan', 'lingkungan', 'kesehatan', 'pendidikan', 'transportasi', 'perdagangan', 'industri', 'pariwisata', 'keamanan', 'ketertiban', 'ketentraman', 'sosial', 'budaya', 'olahraga',
        'perda', 'perwali', 'kepwal', 'pergub', 'kepgub', 'pp', 'perpres', 'permen', 'kepmen', 'inpres',
        'nomor', 'tahun', 'pasal', 'ayat', 'huruf', 'angka'
    ];

    /**
     * Pattern untuk normalisasi teks
     */
    public array $normalizationPatterns = [
        // Normalisasi singkatan peraturan
        '/\bperda\b/i' => 'peraturan daerah',
        '/\bperwali\b/i' => 'peraturan walikota',
        '/\bkepwal\b/i' => 'keputusan walikota',
        '/\bpergub\b/i' => 'peraturan gubernur',
        '/\bkepgub\b/i' => 'keputusan gubernur',
        '/\bpp\b/i' => 'peraturan pemerintah',
        '/\bperpres\b/i' => 'peraturan presiden',
        '/\bpermen\b/i' => 'peraturan menteri',
        '/\bkepmen\b/i' => 'keputusan menteri',
        '/\binpres\b/i' => 'instruksi presiden',
        '/\bse\b/i' => 'surat edaran',
        
        // Normalisasi format nomor dan tahun
        '/\bno\.?\s*(\d+)/i' => 'nomor $1',
        '/\bthn\.?\s*(\d{4})/i' => 'tahun $1',
        '/\btahun\s*(\d{4})/i' => 'tahun $1',
        
        // Normalisasi kata umum
        '/\btentang\b/i' => 'mengenai',
        '/\bterkait\b/i' => 'berkaitan',
        '/\bhal\b/i' => 'perihal',
        
        // Normalisasi ejaan yang sering salah
        '/\bpemkot\b/i' => 'pemerintah kota',
        '/\bpemda\b/i' => 'pemerintah daerah',
        '/\bapbd\b/i' => 'anggaran pendapatan belanja daerah',
        '/\bopd\b/i' => 'organisasi perangkat daerah'
    ];

    /**
     * Pattern untuk ekstraksi entitas yang lebih canggih
     */
    public array $entityPatterns = [
        'regulation_number' => [
            '/(?:nomor|no\.?)\s*(\d+)(?:\s*tahun\s*(\d{4}))?/i',
            '/(?:no\.?)\s*(\d+)\/(\d{4})/i',
            '/(\d+)\s*tahun\s*(\d{4})/i'
        ],
        'year' => [
            '/\b(20\d{2})\b/',
            '/tahun\s*(20\d{2})/i',
            '/\b(19\d{2})\b/' // untuk peraturan lama
        ],
        'article' => [
            '/pasal\s*(\d+)/i',
            '/ps\.?\s*(\d+)/i'
        ],
        'paragraph' => [
            '/ayat\s*\((\d+)\)/i',
            '/ayat\s*(\d+)/i'
        ],
        'letter' => [
            '/huruf\s*([a-z])/i',
            '/poin\s*([a-z])/i'
        ]
    ];

    /**
     * Kategori topik untuk klasifikasi query
     */
    public array $topicCategories = [
        'taxation' => [
            'keywords' => ['pajak', 'pbb', 'bphtb', 'restoran', 'hotel', 'reklame', 'parkir', 'penerangan jalan'],
            'weight' => 1.0
        ],
        'retribution' => [
            'keywords' => ['retribusi', 'pelayanan', 'perizinan', 'pasar', 'terminal', 'sampah', 'kebersihan'],
            'weight' => 1.0
        ],
        'licensing' => [
            'keywords' => ['izin', 'perizinan', 'siup', 'situ', 'imb', 'mendirikan bangunan', 'usaha', 'perdagangan'],
            'weight' => 0.9
        ],
        'environment' => [
            'keywords' => ['lingkungan', 'limbah', 'pencemaran', 'hutan', 'taman', 'ruang terbuka hijau', 'rth'],
            'weight' => 0.8
        ],
        'social' => [
            'keywords' => ['sosial', 'anak jalanan', 'gepeng', 'gelandangan', 'pengemis', 'pmks', 'kesejahteraan'],
            'weight' => 0.8
        ],
        'security' => [
            'keywords' => ['keamanan', 'ketertiban', 'ketentraman', 'ketenteraman', 'satpol pp', 'ronda', 'kamtibmas'],
            'weight' => 0.9
        ],
        'health' => [
            'keywords' => ['kesehatan', 'rumah sakit', 'puskesmas', 'obat', 'makanan', 'minuman', 'sanitasi'],
            'weight' => 0.7
        ],
        'education' => [
            'keywords' => ['pendidikan', 'sekolah', 'guru', 'siswa', 'beasiswa', 'perpustakaan'],
            'weight' => 0.7
        ],
        'transportation' => [
            'keywords' => ['transportasi', 'angkutan', 'jalan', 'lalu lintas', 'parkir', 'terminal'],
            'weight' => 0.6
        ]
    ];

    /**
     * Konfigurasi untuk fuzzy matching
     */
    public array $fuzzyConfig = [
        'similarity_threshold' => 0.7, // Minimum similarity untuk dianggap match
        'max_distance' => 2, // Maximum Levenshtein distance
        'use_soundex' => true, // Gunakan soundex untuk matching fonetik
        'use_metaphone' => true // Gunakan metaphone untuk matching fonetik
    ];

    /**
     * Konfigurasi untuk ranking dan scoring
     */
    public array $scoringConfig = [
        'title_weight' => 3.0,
        'abstract_weight' => 2.0,
        'notes_weight' => 1.5,
        'regulation_type_weight' => 1.2,
        'year_weight' => 0.8,
        'exact_match_bonus' => 2.0,
        'phrase_match_bonus' => 1.5,
        'recent_regulation_bonus' => 0.3 // Bonus untuk peraturan yang lebih baru
    ];

    /**
     * Konfigurasi untuk context window dalam prompt AI
     */
    public array $contextConfig = [
        'max_context_items' => 5,
        'max_full_abstract_items' => 3,
        'abstract_max_length' => 500,
        'title_max_length' => 200,
        'notes_max_length' => 300
    ];

    /**
     * Template untuk berbagai jenis respons AI
     */
    public array $responseTemplates = [
        'no_results' => [
            'greeting' => 'Maaf, saya tidak menemukan peraturan yang spesifik sesuai dengan pertanyaan Anda.',
            'suggestion' => 'Mungkin Anda bisa mencoba kata kunci seperti: {suggestions}',
            'alternative' => 'Sebagai alternatif, berikut adalah beberapa peraturan terbaru yang mungkin relevan:'
        ],
        'partial_results' => [
            'greeting' => 'Saya menemukan beberapa peraturan yang mungkin relevan dengan pertanyaan Anda.',
            'explanation' => 'Berdasarkan pencarian, berikut adalah informasi yang dapat saya berikan:'
        ],
        'full_results' => [
            'greeting' => 'Saya menemukan peraturan yang sesuai dengan pertanyaan Anda.',
            'explanation' => 'Berdasarkan peraturan yang berlaku di Kota Padang:'
        ]
    ];

    /**
     * Search configuration for result limits
     */
    public array $searchConfig = [
        'base_limit' => 5,
        'complex_limit' => 10,
        'simple_limit' => 3
    ];
}