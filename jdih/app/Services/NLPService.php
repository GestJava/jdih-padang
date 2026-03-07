<?php

namespace App\Services;

use Config\NLPConfig;
use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\Stemmer\StemmerInterface;

class NLPService
{
    protected NLPConfig $config;
    protected StemmerInterface $stemmer;

    public function __construct()
    {
        $this->config = new NLPConfig();
        $stemmerFactory = new StemmerFactory();
        $this->stemmer = $stemmerFactory->createStemmer();
    }

    /**
     * Preprocessing query yang lebih canggih
     */
    public function preprocessQuery(string $query): string
    {
        // 1. Normalisasi case dan whitespace
        $query = trim(preg_replace('/\s+/', ' ', $query));
        
        // 2. Normalisasi singkatan dan format
        $query = $this->normalizeAbbreviations($query);
        
        // 3. Koreksi ejaan umum
        $query = $this->correctCommonMisspellings($query);
        
        // 4. Hapus karakter khusus yang tidak perlu
        $query = preg_replace('/[^\w\s\-\.]/u', ' ', $query);
        
        // 5. Normalisasi spasi
        $query = preg_replace('/\s+/', ' ', trim($query));
        
        return $query;
    }

    /**
     * Normalisasi singkatan berdasarkan pattern
     */
    private function normalizeAbbreviations(string $text): string
    {
        if (empty($this->config->normalizationPatterns)) {
            return $text;
        }
        foreach ($this->config->normalizationPatterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }
        return $text;
    }

    /**
     * Koreksi ejaan yang sering salah
     */
    public function correctCommonMisspellings(string $text): string
    {
        $corrections = [
            '/\bperda\b/i' => 'peraturan daerah',
            '/\bpemkot\b/i' => 'pemerintah kota',
            '/\bpemda\b/i' => 'pemerintah daerah',
            '/\bapbd\b/i' => 'anggaran pendapatan belanja daerah',
            '/\bopd\b/i' => 'organisasi perangkat daerah',
            '/\bsop\b/i' => 'standar operasional prosedur',
            '/\brth\b/i' => 'ruang terbuka hijau',
            '/\bpmks\b/i' => 'penyandang masalah kesejahteraan sosial',
            '/\bgepeng\b/i' => 'gelandangan pengemis',
            '/\bketentraman\b/i' => 'ketertiban umum',
            '/\bketenteraman\b/i' => 'ketertiban umum'
        ];
        
        foreach ($corrections as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }
        
        return $text;
    }

    /**
     * The main function to extract meaningful keywords for search.
     */
    public function extractKeywords(string $query): array
    {
        // 1. Preprocess the query to normalize it (e.g., 'perda' -> 'peraturan daerah').
        $processedQuery = $this->preprocessQuery($query);

        // 2. Extract structured entities like numbers and years.
        $entities = $this->extractAdvancedEntities($processedQuery);

        // 3. Get the remaining text after removing entities. This is our base keyword text.
        $keywordsText = $this->extractAdvancedKeywords($processedQuery, $entities);

        // 4. Remove common stop words to focus on the core topic.
        $finalKeywordsText = $this->removeStopWords($keywordsText);

        // 5. Return keywords as an array. If empty, return nothing.
        if (empty(trim($finalKeywordsText))) {
            return [];
        }
        return explode(' ', $finalKeywordsText);
    }

    /**
     * Ekstraksi entitas yang lebih canggih
     */
    public function extractAdvancedEntities(string $query): array
    {
        $entities = [
            'regulation_numbers' => [],
            'years' => [],
            'articles' => [],
            'paragraphs' => [],
            'letters' => [],
            'topics' => [],
            'named_entities' => [],
            'temporal_expressions' => []
        ];

        if (empty($this->config->entityPatterns)) {
            return $entities;
        }

        // Ekstrak nomor peraturan dengan berbagai format
        if(isset($this->config->entityPatterns['regulation_number'])) {
            foreach ($this->config->entityPatterns['regulation_number'] as $pattern) {
                if (preg_match_all($pattern, $query, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $entities['regulation_numbers'][] = [
                            'number' => $match[1] ?? null,
                            'year' => $match[2] ?? null,
                            'full_match' => $match[0]
                        ];
                    }
                }
            }
        }

        // Ekstrak tahun
        if(isset($this->config->entityPatterns['year'])) {
            foreach ($this->config->entityPatterns['year'] as $pattern) {
                if (preg_match_all($pattern, $query, $matches)) {
                    foreach ($matches[1] as $year) {
                        if (!in_array($year, $entities['years'])) {
                            $entities['years'][] = (int)$year;
                        }
                    }
                }
            }
        }

        // Ekstrak pasal
         if(isset($this->config->entityPatterns['article'])) {
            foreach ($this->config->entityPatterns['article'] as $pattern) {
                if (preg_match_all($pattern, $query, $matches)) {
                    foreach ($matches[1] as $article) {
                        if (!in_array($article, $entities['articles'])) {
                            $entities['articles'][] = (int)$article;
                        }
                    }
                }
            }
        }

        // Ekstrak ayat
        if(isset($this->config->entityPatterns['paragraph'])) {
            foreach ($this->config->entityPatterns['paragraph'] as $pattern) {
                if (preg_match_all($pattern, $query, $matches)) {
                    foreach ($matches[1] as $paragraph) {
                        if (!in_array($paragraph, $entities['paragraphs'])) {
                            $entities['paragraphs'][] = (int)$paragraph;
                        }
                    }
                }
            }
        }

        // Ekstrak huruf
        if(isset($this->config->entityPatterns['letter'])) {
            foreach ($this->config->entityPatterns['letter'] as $pattern) {
                if (preg_match_all($pattern, $query, $matches)) {
                    foreach ($matches[1] as $letter) {
                        if (!in_array($letter, $entities['letters'])) {
                            $entities['letters'][] = strtolower($letter);
                        }
                    }
                }
            }
        }

        // Klasifikasi topik
        $entities['topics'] = $this->classifyTopics($query);

        // Ekstrak named entities (nama tempat, organisasi, dll)
        $entities['named_entities'] = $this->extractNamedEntities($query);

        // Ekstrak ekspresi temporal
        $entities['temporal_expressions'] = $this->extractTemporalExpressions($query);

        return $entities;
    }

    /**
     * Removes entities from the query to leave only topical keywords.
     */
    private function extractAdvancedKeywords(string $query, array $entities): string
    {
        $keywordQuery = ' ' . $query . ' ';

        // Remove all full matches of regulation numbers
        if (!empty($entities['regulation_numbers'])) {
            foreach ($entities['regulation_numbers'] as $entity) {
                $keywordQuery = str_ireplace(' ' . $entity['full_match'] . ' ', ' ', $keywordQuery);
            }
        }

        return trim($keywordQuery);
    }

    /**
     * Removes common stop words from a string.
     */
    private function removeStopWords(string $text): string
    {
        $lowerText = strtolower($text);
        $stopWords = [
            'tentang', 'ringkasan', 'perda', 'peraturan', 'walikota', 'keputusan', 'mengenai',
            'dan', 'atau', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'dengan', 'adalah',
            'yaitu', 'sebagai', 'bahwa', 'saya', 'anda', 'dia', 'kami', 'kalian', 'mereka',
            'apa', 'siapa', 'kapan', 'dimana', 'bagaimana', 'mengapa', 'jelaskan', 'berikan',
            'carikan', 'cari', 'tolong', 'menurut', 'yaitu', 'yakni'
        ];

        $words = explode(' ', $lowerText);
        $filteredWords = array_filter($words, function ($word) use ($stopWords) {
            return !in_array(trim($word), $stopWords);
        });

        return implode(' ', $filteredWords);
    }

    /**
     * Placeholder for topic classification.
     */
    public function classifyTopics(string $query): array
    {
        // TODO: Implement real topic classification
        return [];
    }

    /**
     * Placeholder for named entity extraction.
     */
    public function extractNamedEntities(string $query): array
    {
        // TODO: Implement real named entity extraction
        return [];
    }

    /**
     * Placeholder for temporal expression extraction.
     */
    public function extractTemporalExpressions(string $query): array
    {
        // TODO: Implement real temporal expression extraction
        return [];
    }

    /**
     * Calculate relevance score untuk ranking hasil pencarian
     */
    public function calculateRelevanceScore(array $result, array $keywords = [], array $phrases = []): float
    {
        $score = 0.0;
        $maxScore = 100.0;
        
        // Bobot untuk setiap field
        $fieldWeights = [
            'judul' => 0.4,
            'abstrak_teks' => 0.3,
            'catatan_teks' => 0.2,
            'nama_jenis' => 0.1
        ];
        
        // Hitung skor berdasarkan keyword matches
        foreach ($keywords as $keyword) {
            $keyword = strtolower(trim($keyword));
            if (empty($keyword)) continue;
            
            foreach ($fieldWeights as $field => $weight) {
                if (isset($result[$field])) {
                    $fieldText = strtolower($result[$field]);
                    
                    // Exact match mendapat skor tertinggi
                    if (strpos($fieldText, $keyword) !== false) {
                        $score += $weight * 25;
                    }
                    
                    // Partial match dengan stemming
                    $stemmedKeyword = $this->stemmer->stem($keyword);
                    $stemmedField = $this->stemmer->stem($fieldText);
                    if (strpos($stemmedField, $stemmedKeyword) !== false) {
                        $score += $weight * 15;
                    }
                    
                    // Fuzzy match dengan similar_text
                    $similarity = 0;
                    similar_text($keyword, $fieldText, $similarity);
                    if ($similarity > 70) {
                        $score += $weight * 10 * ($similarity / 100);
                    }
                }
            }
        }
        
        // Hitung skor berdasarkan phrase matches
        foreach ($phrases as $phrase) {
            $phrase = strtolower(trim($phrase));
            if (empty($phrase)) continue;
            
            foreach ($fieldWeights as $field => $weight) {
                if (isset($result[$field])) {
                    $fieldText = strtolower($result[$field]);
                    
                    // Phrase match mendapat bonus skor
                    if (strpos($fieldText, $phrase) !== false) {
                        $score += $weight * 30;
                    }
                }
            }
        }
        
        // Bonus untuk dokumen yang lebih baru (ditingkatkan untuk prioritas lebih tinggi)
        if (isset($result['tahun'])) {
            $currentYear = date('Y');
            $docYear = intval($result['tahun']);
            $yearDiff = $currentYear - $docYear;
            
            if ($yearDiff == 0) {
                $score += 20; // Bonus besar untuk dokumen tahun ini
            } elseif ($yearDiff <= 1) {
                $score += 15; // Bonus untuk dokumen tahun lalu
            } elseif ($yearDiff <= 2) {
                $score += 12; // Bonus untuk dokumen 2 tahun terakhir
            } elseif ($yearDiff <= 3) {
                $score += 8;  // Bonus untuk dokumen 3 tahun terakhir
            } elseif ($yearDiff <= 5) {
                $score += 5;  // Bonus kecil untuk dokumen 5 tahun terakhir
            }
        }
        
        // Bonus untuk dokumen dengan abstrak yang lengkap
        if (isset($result['abstrak_teks']) && strlen($result['abstrak_teks']) > 100) {
            $score += 5;
        }
        
        // Normalisasi skor ke range 0-100
        return min($score, $maxScore);
    }
}