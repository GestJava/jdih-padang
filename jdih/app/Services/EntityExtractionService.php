<?php

namespace App\Services;

use Config\Chatbot as ChatbotConfig;
use Config\Synonyms;
use Config\IntentPatterns;
use Config\NLPConfig;
use App\Services\NLPService;
use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\Stemmer\StemmerInterface;

class EntityExtractionService
{
    protected ChatbotConfig $chatbotConfig;
    protected Synonyms $synonymsConfig;
    protected IntentPatterns $intentPatternsConfig;
    protected NLPConfig $nlpConfig;
    protected NLPService $nlpService;
    protected StemmerInterface $stemmer;

    public function __construct()
    {
        $this->chatbotConfig = new ChatbotConfig();
        $this->synonymsConfig = new Synonyms();
        $this->intentPatternsConfig = new IntentPatterns();
        $this->nlpConfig = new NLPConfig();
        $this->nlpService = new NLPService();
        
        $stemmerFactory = new StemmerFactory();
        $this->stemmer = $stemmerFactory->createStemmer();
    }

    /**
     * Ekstraksi entitas lengkap dari query
     */
    public function extractAllEntities(string $query): array
    {
        // Preprocessing query
        $processedQuery = $this->nlpService->preprocessQuery($query);
        
        $entities = [
            'original_query' => $query,
            'processed_query' => $processedQuery,
            'intent' => $this->extractIntent($processedQuery),
            'regulation_info' => $this->extractRegulationInfo($processedQuery),
            'temporal_info' => $this->extractTemporalInfo($processedQuery),
            'location_info' => $this->extractLocationInfo($processedQuery),
            'topic_classification' => $this->classifyTopics($processedQuery),
            'keywords' => $this->extractKeywords($processedQuery),
            'phrases' => $this->extractImportantPhrases($processedQuery),
            'named_entities' => $this->extractNamedEntities($processedQuery),
            'query_complexity' => $this->analyzeQueryComplexity($processedQuery),
            'search_strategy' => $this->determineSearchStrategy($processedQuery)
        ];

        // Expand keywords dengan sinonim
        $entities['expanded_keywords'] = $this->expandWithSynonyms($entities['keywords']);
        
        // Backward compatibility dengan format lama
        $entities = $this->addBackwardCompatibility($entities);
        
        return $entities;
    }

    /**
     * Ekstraksi intent dengan analisis yang lebih mendalam
     */
    public function extractIntent(string $query): array
    {
        $intent = [
            'type' => 'search',
            'confidence' => 0.5,
            'complexity' => 'simple',
            'context' => [],
            'sub_intents' => []
        ];

        $lowerQuery = strtolower($query);
        $queryWordCount = str_word_count($query);

        // Deteksi greeting
        $greetings = ['halo', 'hai', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'assalamualaikum'];
        foreach ($greetings as $greeting) {
            if (strpos($lowerQuery, $greeting) !== false) {
                $intent['type'] = 'greeting';
                $intent['confidence'] = 0.9;
                
                // Cek apakah hanya greeting atau ada pertanyaan lanjutan
                if ($queryWordCount <= 4) {
                    $intent['complexity'] = 'greeting_only';
                    return $intent;
                }
                break;
            }
        }

        // Deteksi kompleksitas query
        $complexityIndicators = $this->intentPatternsConfig->complexityIndicators;
        $isComplex = (
            preg_match_all($complexityIndicators['multiple_questions_regex'], $query) > 1 ||
            preg_match($complexityIndicators['conjunctions_regex'], $query) ||
            str_word_count($query) > $complexityIndicators['length_threshold'] ||
            preg_match($complexityIndicators['detailed_inquiry_regex'], $query)
        );

        if ($isComplex) {
            $intent['complexity'] = 'complex';
            $intent['context']['needs_comprehensive_answer'] = true;
        }

        // Deteksi intent utama
        $intentRegexes = $this->intentPatternsConfig->intentRegexes;
        
        $intentChecks = [
            'explanation' => [
                'pattern' => $intentRegexes['explanation'],
                'confidence' => 0.8,
                'sub_patterns' => [
                    'policy_inquiry' => $intentRegexes['explanation_policy_inquiry']
                ]
            ],
            'comparison' => [
                'pattern' => $intentRegexes['comparison'],
                'confidence' => 0.9
            ],
            'specific' => [
                'pattern' => $intentRegexes['specific'],
                'confidence' => 0.7
            ],
            'list' => [
                'pattern' => $intentRegexes['list'],
                'confidence' => 0.8
            ],
            'status' => [
                'pattern' => $intentRegexes['status'],
                'confidence' => 0.9
            ],
            'policy' => [
                'pattern' => $intentRegexes['policy'],
                'confidence' => 0.8
            ]
        ];

        foreach ($intentChecks as $intentType => $config) {
            if (preg_match($config['pattern'], $query)) {
                $intent['type'] = $intentType;
                $intent['confidence'] = $config['confidence'];
                
                // Cek sub-patterns jika ada
                if (isset($config['sub_patterns'])) {
                    foreach ($config['sub_patterns'] as $subType => $subPattern) {
                        if (preg_match($subPattern, $query)) {
                            $intent['context']['sub_type'] = $subType;
                            $intent['confidence'] = min($intent['confidence'] + 0.1, 1.0);
                        }
                    }
                }
                break;
            }
        }

        // Deteksi multiple intents
        $detectedIntents = [];
        foreach ($intentChecks as $intentType => $config) {
            if (preg_match($config['pattern'], $query)) {
                $detectedIntents[] = [
                    'type' => $intentType,
                    'confidence' => $config['confidence']
                ];
            }
        }
        
        if (count($detectedIntents) > 1) {
            $intent['sub_intents'] = $detectedIntents;
            $intent['context']['multiple_intents'] = true;
        }

        return $intent;
    }

    /**
     * Ekstraksi informasi peraturan (jenis, nomor, tahun)
     */
    public function extractRegulationInfo(string $query): array
    {
        $info = [
            'jenis_peraturan' => null,
            'nomor' => null,
            'tahun' => null,
            'pasal' => null,
            'ayat' => null,
            'huruf' => null,
            'confidence' => []
        ];

        $lowerQuery = strtolower($query);

        // Ekstrak jenis peraturan
        foreach ($this->chatbotConfig->jenisPeraturanKeywords as $keyword => $slug) {
            if (strpos($lowerQuery, $keyword) !== false) {
                $info['jenis_peraturan'] = $slug;
                $info['confidence']['jenis_peraturan'] = 0.9;
                break;
            }
        }

        // Fuzzy matching untuk jenis peraturan jika tidak ditemukan exact match
        if (!$info['jenis_peraturan']) {
            $words = explode(' ', $lowerQuery);
            foreach ($words as $word) {
                $word = trim($word);
                if (strlen($word) > 2) {
                    $match = $this->nlpService->fuzzyMatch($word, array_keys($this->chatbotConfig->jenisPeraturanKeywords), 0.8);
                    if ($match) {
                        $info['jenis_peraturan'] = $this->chatbotConfig->jenisPeraturanKeywords[$match];
                        $info['confidence']['jenis_peraturan'] = 0.7;
                        break;
                    }
                }
            }
        }

        // Ekstrak nomor peraturan dengan berbagai format
        $numberPatterns = [
            '/(?:nomor|no\.?)\s*(\d+)(?:\s*tahun\s*(\d{4}))?/i',
            '/(?:no\.?)\s*(\d+)\/(\d{4})/i',
            '/(\d+)\s*tahun\s*(\d{4})/i',
            '/(?:nomor|no\.?)\s*(\d+)/i'
        ];

        foreach ($numberPatterns as $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                $info['nomor'] = $matches[1];
                $info['confidence']['nomor'] = 0.9;
                
                if (isset($matches[2]) && strlen($matches[2]) === 4) {
                    $info['tahun'] = (int)$matches[2];
                    $info['confidence']['tahun'] = 0.9;
                }
                break;
            }
        }

        // Ekstrak tahun jika belum ditemukan
        if (!$info['tahun']) {
            $yearPatterns = [
                '/\b(20\d{2})\b/',
                '/tahun\s*(20\d{2})/i',
                '/\b(19\d{2})\b/' // untuk peraturan lama
            ];
            
            foreach ($yearPatterns as $pattern) {
                if (preg_match($pattern, $query, $matches)) {
                    $year = (int)$matches[1];
                    if ($year >= 1945 && $year <= (int)date('Y') + 1) { // Validasi tahun
                        $info['tahun'] = $year;
                        $info['confidence']['tahun'] = 0.8;
                        break;
                    }
                }
            }
        }

        // Ekstrak pasal
        if (preg_match('/pasal\s*(\d+)/i', $query, $matches)) {
            $info['pasal'] = (int)$matches[1];
            $info['confidence']['pasal'] = 0.9;
        }

        // Ekstrak ayat
        if (preg_match('/ayat\s*\((\d+)\)/i', $query, $matches)) {
            $info['ayat'] = (int)$matches[1];
            $info['confidence']['ayat'] = 0.9;
        } elseif (preg_match('/ayat\s*(\d+)/i', $query, $matches)) {
            $info['ayat'] = (int)$matches[1];
            $info['confidence']['ayat'] = 0.8;
        }

        // Ekstrak huruf
        if (preg_match('/huruf\s*([a-z])/i', $query, $matches)) {
            $info['huruf'] = strtolower($matches[1]);
            $info['confidence']['huruf'] = 0.9;
        }

        return $info;
    }

    /**
     * Ekstraksi informasi temporal
     */
    public function extractTemporalInfo(string $query): array
    {
        $temporal = [
            'time_reference' => null,
            'specific_period' => null,
            'relative_time' => null,
            'urgency' => 'normal'
        ];

        $lowerQuery = strtolower($query);

        // Deteksi referensi waktu
        $timePatterns = [
            'present' => ['sekarang', 'saat ini', 'hari ini', 'sedang berlaku', 'yang berlaku'],
            'recent' => ['terbaru', 'terkini', 'terakhir', 'baru-baru ini', 'belakangan'],
            'past' => ['lama', 'dulu', 'dahulu', 'sebelumnya', 'masa lalu'],
            'future' => ['akan datang', 'mendatang', 'masa depan', 'rencana']
        ];

        foreach ($timePatterns as $timeType => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($lowerQuery, $pattern) !== false) {
                    $temporal['time_reference'] = $timeType;
                    break 2;
                }
            }
        }

        // Deteksi periode spesifik
        $periodPatterns = [
            '/\b(semester\s+\d)/i' => 'semester',
            '/\b(triwulan\s+\d)/i' => 'quarter',
            '/\b(bulan\s+\w+)/i' => 'month',
            '/\b(minggu\s+\w+)/i' => 'week'
        ];

        foreach ($periodPatterns as $pattern => $type) {
            if (preg_match($pattern, $query, $matches)) {
                $temporal['specific_period'] = [
                    'type' => $type,
                    'value' => trim($matches[1])
                ];
                break;
            }
        }

        // Deteksi urgency
        $urgencyPatterns = [
            'high' => ['segera', 'urgent', 'mendesak', 'penting', 'darurat'],
            'low' => ['nanti', 'suatu saat', 'kapan-kapan', 'tidak terburu']
        ];

        foreach ($urgencyPatterns as $urgencyLevel => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($lowerQuery, $pattern) !== false) {
                    $temporal['urgency'] = $urgencyLevel;
                    break 2;
                }
            }
        }

        return $temporal;
    }

    /**
     * Ekstraksi informasi lokasi
     */
    public function extractLocationInfo(string $query): array
    {
        $location = [
            'city' => null,
            'district' => null,
            'subdistrict' => null,
            'specific_place' => null,
            'scope' => 'city' // default scope
        ];

        $lowerQuery = strtolower($query);

        // Deteksi kota (default Padang)
        if (strpos($lowerQuery, 'padang') !== false || strpos($lowerQuery, 'kota padang') !== false) {
            $location['city'] = 'padang';
        }

        // Deteksi kecamatan
        if (preg_match('/kecamatan\s+(\w+)/i', $query, $matches)) {
            $location['district'] = strtolower($matches[1]);
            $location['scope'] = 'district';
        }

        // Deteksi kelurahan
        if (preg_match('/kelurahan\s+(\w+)/i', $query, $matches)) {
            $location['subdistrict'] = strtolower($matches[1]);
            $location['scope'] = 'subdistrict';
        }

        // Deteksi tempat spesifik
        $placePatterns = [
            '/\b(pasar\s+\w+)/i',
            '/\b(terminal\s+\w+)/i',
            '/\b(jalan\s+[\w\s]+)/i',
            '/\b(taman\s+\w+)/i',
            '/\b(gedung\s+\w+)/i'
        ];

        foreach ($placePatterns as $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                $location['specific_place'] = trim($matches[1]);
                $location['scope'] = 'specific';
                break;
            }
        }

        return $location;
    }

    /**
     * Klasifikasi topik dengan scoring yang lebih detail
     */
    public function classifyTopics(string $query): array
    {
        return $this->nlpService->classifyTopics($query);
    }

    /**
     * Ekstraksi keywords yang lebih canggih
     */
    public function extractKeywords(string $query): array
    {
        // Gunakan advanced keyword extraction dari NLPService
        $advancedEntities = $this->nlpService->extractAdvancedEntities($query);
        $keywords = $this->nlpService->extractAdvancedKeywords($query, $advancedEntities);
        
        return $keywords;
    }

    /**
     * Ekstraksi frasa penting
     */
    public function extractImportantPhrases(string $query): array
    {
        $phrases = [];
        $lowerQuery = strtolower($query);

        // Frasa peraturan
        $regulationPhrases = [
            'peraturan daerah', 'peraturan walikota', 'keputusan walikota',
            'tata ruang', 'pajak daerah', 'retribusi daerah',
            'anggaran pendapatan', 'apbd', 'organisasi perangkat daerah'
        ];

        // Frasa sosial
        $socialPhrases = [
            'anak jalanan', 'anak terlantar', 'gelandangan pengemis',
            'penyandang masalah kesejahteraan sosial', 'pembinaan sosial',
            'rehabilitasi sosial', 'bantuan sosial', 'program pemberdayaan'
        ];

        // Frasa kebijakan
        $policyPhrases = [
            'pemerintah kota', 'dinas sosial', 'satpol pp',
            'program khusus', 'mekanisme penanganan', 'prosedur penanganan',
            'standar operasional', 'pedoman pelaksanaan'
        ];

        $allPhrases = array_merge($regulationPhrases, $socialPhrases, $policyPhrases);
        
        foreach ($allPhrases as $phrase) {
            if (strpos($lowerQuery, $phrase) !== false) {
                $phrases[] = $phrase;
            }
        }

        // Ekstraksi frasa dinamis
        $dynamicPatterns = [
            '/\b(program\s+\w+(?:\s+\w+)?)/i',
            '/\b(penanganan\s+\w+(?:\s+\w+)?)/i',
            '/\b(pembinaan\s+\w+(?:\s+\w+)?)/i',
            '/\b(layanan\s+\w+(?:\s+\w+)?)/i',
            '/\b(bantuan\s+\w+(?:\s+\w+)?)/i'
        ];

        foreach ($dynamicPatterns as $pattern) {
            if (preg_match_all($pattern, $lowerQuery, $matches)) {
                foreach ($matches[1] as $match) {
                    $phrases[] = trim($match);
                }
            }
        }

        return array_unique($phrases);
    }

    /**
     * Ekstraksi named entities
     */
    public function extractNamedEntities(string $query): array
    {
        return $this->nlpService->extractNamedEntities($query);
    }

    /**
     * Analisis kompleksitas query
     */
    public function analyzeQueryComplexity(string $query): array
    {
        $complexity = [
            'level' => 'simple',
            'score' => 0,
            'factors' => [],
            'processing_strategy' => 'standard'
        ];

        $wordCount = str_word_count($query);
        $questionMarks = substr_count($query, '?');
        $conjunctions = preg_match_all('/\b(dan|atau|serta|juga|selain itu|bagaimana.*dan|apa.*dan)\b/i', $query);
        
        // Hitung score kompleksitas
        if ($wordCount > 20) {
            $complexity['score'] += 2;
            $complexity['factors'][] = 'long_query';
        } elseif ($wordCount > 10) {
            $complexity['score'] += 1;
            $complexity['factors'][] = 'medium_query';
        }

        if ($questionMarks > 1) {
            $complexity['score'] += 2;
            $complexity['factors'][] = 'multiple_questions';
        }

        if ($conjunctions > 0) {
            $complexity['score'] += 1;
            $complexity['factors'][] = 'conjunctions';
        }

        if (preg_match('/\b(saya ingin tahu|tolong jelaskan|mohon informasi|bagaimana.*menanggapi)\b/i', $query)) {
            $complexity['score'] += 1;
            $complexity['factors'][] = 'detailed_inquiry';
        }

        // Tentukan level kompleksitas
        if ($complexity['score'] >= 3) {
            $complexity['level'] = 'complex';
            $complexity['processing_strategy'] = 'comprehensive';
        } elseif ($complexity['score'] >= 2) {
            $complexity['level'] = 'medium';
            $complexity['processing_strategy'] = 'enhanced';
        }

        return $complexity;
    }

    /**
     * Tentukan strategi pencarian
     */
    public function determineSearchStrategy(string $query): array
    {
        $strategy = [
            'primary' => 'keyword',
            'fallback' => ['fuzzy', 'category', 'latest'],
            'ranking_method' => 'relevance',
            'result_limit' => 5
        ];

        $lowerQuery = strtolower($query);

        // Jika ada nomor/tahun spesifik, gunakan exact search
        if (preg_match('/\b(nomor|no\.?|tahun|\d{4})\b/i', $query)) {
            $strategy['primary'] = 'exact';
            $strategy['ranking_method'] = 'exact_match';
        }

        // Jika query kompleks, tingkatkan limit hasil
        if (str_word_count($query) > 15) {
            $strategy['result_limit'] = 8;
            $strategy['ranking_method'] = 'comprehensive';
        }

        // Jika mencari daftar, gunakan category search
        if (preg_match('/\b(daftar|list|semua|seluruh)\b/i', $query)) {
            $strategy['primary'] = 'category';
            $strategy['result_limit'] = 10;
        }

        return $strategy;
    }

    /**
     * Expand keywords dengan sinonim
     */
    private function expandWithSynonyms(array $keywords): array
    {
        $expanded = $keywords;
        
        foreach ($keywords as $keyword) {
            if (isset($this->synonymsConfig->synonyms[$keyword])) {
                $synonyms = $this->synonymsConfig->synonyms[$keyword];
                $expanded = array_merge($expanded, $synonyms);
            }
        }
        
        return array_unique($expanded);
    }

    /**
     * Tambahkan backward compatibility dengan format lama
     */
    private function addBackwardCompatibility(array $entities): array
    {
        // Format lama untuk compatibility
        $entities['jenis_peraturan'] = $entities['regulation_info']['jenis_peraturan'];
        $entities['nomor'] = $entities['regulation_info']['nomor'];
        $entities['tahun'] = $entities['regulation_info']['tahun'];
        
        return $entities;
    }
}