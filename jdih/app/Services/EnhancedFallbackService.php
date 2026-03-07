<?php

namespace App\Services;

use App\Models\WebPeraturanModel;
use App\Services\NLPService;
use Config\NLPConfig;
use CodeIgniter\I18n\Time;

class EnhancedFallbackService
{
    protected WebPeraturanModel $peraturanModel;
    protected NLPService $nlpService;
    protected NLPConfig $nlpConfig;
    
    public function __construct()
    {
        $this->peraturanModel = new WebPeraturanModel();
        $this->nlpService = new NLPService();
        $this->nlpConfig = new NLPConfig();
    }

    /**
     * Enhanced fallback mechanism dengan 10 strategi
     */
    public function executeEnhancedFallback(string $query, array $entities, array $params): array
    {
        $strategies = [
            'individual_keywords' => [$this, 'strategyIndividualKeywords'],
            'fuzzy_expanded' => [$this, 'strategyFuzzyExpanded'],
            'fuzzy_original' => [$this, 'strategyFuzzyOriginal'],
            'semantic_search' => [$this, 'strategySemanticSearch'],
            'category_based' => [$this, 'strategyCategoryBased'],
            'temporal_based' => [$this, 'strategyTemporalBased'],
            'topic_classification' => [$this, 'strategyTopicClassification'],
            'partial_match' => [$this, 'strategyPartialMatch'],
            'latest_regulations' => [$this, 'strategyLatestRegulations'],
            'popular_regulations' => [$this, 'strategyPopularRegulations']
        ];

        $this->logFallbackAttempt($query, $entities);

        foreach ($strategies as $strategyName => $strategyMethod) {
            try {
                $results = call_user_func($strategyMethod, $query, $entities, $params);
                
                if (!empty($results)) {
                    $this->logSuccessfulStrategy($strategyName, $query, count($results));
                    
                    // Tambahkan metadata tentang strategi yang digunakan
                    foreach ($results as &$result) {
                        $result['fallback_strategy'] = $strategyName;
                        $result['fallback_confidence'] = $this->calculateFallbackConfidence($strategyName);
                    }
                    
                    return $results;
                }
            } catch (\Exception $e) {
                log_message('error', "Fallback strategy {$strategyName} failed: " . $e->getMessage());
                continue;
            }
        }

        $this->logAllStrategiesFailed($query, $entities);
        return [];
    }

    /**
     * Handle empty search results with fallback strategies
     * This method is called from CodeRefactoringService when main search returns empty results
     */
    public function handleEmptyResults(array $searchParams, array $entities): array
    {
        // Extract query from search params or entities
        $query = $searchParams['keyword'] ?? $searchParams['phrase'] ?? '';
        
        if (empty($query) && !empty($entities['keywords'])) {
            $query = implode(' ', $entities['keywords']);
        }
        
        // Use the enhanced fallback mechanism
        return $this->executeEnhancedFallback($query, $entities, $searchParams);
    }

    /**
     * Strategi 1: Pencarian dengan kata kunci individual
     */
    private function strategyIndividualKeywords(string $query, array $entities, array $params): array
    {
        if (empty($entities['keywords']) || count($entities['keywords']) <= 1) {
            return [];
        }

        // Urutkan keywords berdasarkan panjang (yang lebih panjang biasanya lebih spesifik)
        $keywords = $entities['keywords'];
        usort($keywords, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($keywords as $keyword) {
            $fallbackParams = ['keyword' => $keyword];
            $results = $this->peraturanModel->searchPeraturanForChatbot($fallbackParams, 5);
            
            if (!empty($results)) {
                return $this->rankAndLimitResults($results, [$keyword], 5);
            }
        }

        return [];
    }

    /**
     * Strategi 2: Fuzzy search dengan expanded keywords
     */
    private function strategyFuzzyExpanded(string $query, array $entities, array $params): array
    {
        if (empty($entities['expanded_keywords'])) {
            return [];
        }

        $fuzzyParams = ['keyword' => implode(' ', $entities['expanded_keywords'])];
        $results = $this->peraturanModel->searchPeraturanFuzzy($fuzzyParams, 10);
        
        if (!empty($results)) {
            return $this->rankAndLimitResults($results, $entities['expanded_keywords'], 8);
        }

        return [];
    }

    /**
     * Strategi 3: Fuzzy search dengan original keywords
     */
    private function strategyFuzzyOriginal(string $query, array $entities, array $params): array
    {
        if (empty($entities['keywords'])) {
            return [];
        }

        $fuzzyParams = ['keyword' => implode(' ', $entities['keywords'])];
        $results = $this->peraturanModel->searchPeraturanFuzzy($fuzzyParams, 8);
        
        if (!empty($results)) {
            return $this->rankAndLimitResults($results, $entities['keywords'], 6);
        }

        return [];
    }

    /**
     * Strategi 4: Semantic search berdasarkan topik
     */
    private function strategySemanticSearch(string $query, array $entities, array $params): array
    {
        if (empty($entities['topic_classification'])) {
            return [];
        }

        $topTopics = array_slice($entities['topic_classification'], 0, 2);
        $semanticKeywords = [];
        
        foreach ($topTopics as $topic) {
            $semanticKeywords = array_merge($semanticKeywords, $topic['matched_keywords']);
        }

        if (!empty($semanticKeywords)) {
            $semanticParams = ['keyword' => implode(' ', array_unique($semanticKeywords))];
            $results = $this->peraturanModel->searchPeraturanForChatbot($semanticParams, 8);
            
            if (!empty($results)) {
                return $this->rankAndLimitResults($results, $semanticKeywords, 6);
            }
        }

        return [];
    }

    /**
     * Strategi 5: Pencarian berdasarkan kategori peraturan
     */
    private function strategyCategoryBased(string $query, array $entities, array $params): array
    {
        $results = [];

        // Cari berdasarkan jenis peraturan
        if (!empty($entities['regulation_info']['jenis_peraturan'])) {
            $typeParams = ['jenis' => $entities['regulation_info']['jenis_peraturan']];
            $results = $this->peraturanModel->searchPeraturanForChatbot($typeParams, 6);
        }

        // Jika tidak ada hasil, coba dengan jenis peraturan yang mirip
        if (empty($results) && !empty($entities['keywords'])) {
            $regulationTypes = array_keys($this->getRegulationTypeMapping());
            
            foreach ($entities['keywords'] as $keyword) {
                $matchedType = $this->nlpService->fuzzyMatch($keyword, $regulationTypes, 0.7);
                if ($matchedType) {
                    $typeMapping = $this->getRegulationTypeMapping();
                    $typeParams = ['jenis' => $typeMapping[$matchedType]];
                    $results = $this->peraturanModel->searchPeraturanForChatbot($typeParams, 6);
                    
                    if (!empty($results)) {
                        break;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Strategi 6: Pencarian berdasarkan informasi temporal
     */
    private function strategyTemporalBased(string $query, array $entities, array $params): array
    {
        $results = [];

        // Cari berdasarkan tahun spesifik
        if (!empty($entities['regulation_info']['tahun'])) {
            $yearParams = ['tahun' => $entities['regulation_info']['tahun']];
            $results = $this->peraturanModel->searchPeraturanForChatbot($yearParams, 6);
        }

        // Cari berdasarkan rentang tahun jika ada referensi temporal
        if (empty($results) && !empty($entities['temporal_info']['time_reference'])) {
            $currentYear = (int)date('Y');
            
            switch ($entities['temporal_info']['time_reference']) {
                case 'recent':
                    $yearRange = range($currentYear - 2, $currentYear);
                    break;
                case 'present':
                    $yearRange = [$currentYear, $currentYear - 1];
                    break;
                case 'past':
                    $yearRange = range($currentYear - 10, $currentYear - 3);
                    break;
                default:
                    $yearRange = [];
            }

            foreach ($yearRange as $year) {
                $yearParams = ['tahun' => $year];
                $yearResults = $this->peraturanModel->searchPeraturanForChatbot($yearParams, 3);
                $results = array_merge($results, $yearResults);
                
                if (count($results) >= 6) {
                    break;
                }
            }
        }

        return array_slice($results, 0, 6);
    }

    /**
     * Strategi 7: Pencarian berdasarkan klasifikasi topik
     */
    private function strategyTopicClassification(string $query, array $entities, array $params): array
    {
        if (empty($entities['topic_classification'])) {
            return [];
        }

        $topicKeywords = [];
        foreach ($entities['topic_classification'] as $topic) {
            if ($topic['confidence'] > 0.5) {
                $topicKeywords = array_merge($topicKeywords, $topic['matched_keywords']);
            }
        }

        if (!empty($topicKeywords)) {
            $topicParams = ['keyword' => implode(' ', array_unique($topicKeywords))];
            $results = $this->peraturanModel->searchPeraturanForChatbot($topicParams, 8);
            
            return $this->rankAndLimitResults($results, $topicKeywords, 6);
        }

        return [];
    }

    /**
     * Strategi 8: Partial matching dengan substring
     */
    private function strategyPartialMatch(string $query, array $entities, array $params): array
    {
        if (empty($entities['keywords'])) {
            return [];
        }

        $results = [];
        
        foreach ($entities['keywords'] as $keyword) {
            if (strlen($keyword) >= 4) { // Hanya untuk keyword yang cukup panjang
                // Cari dengan substring
                $partialParams = ['keyword' => substr($keyword, 0, -1)];
                $partialResults = $this->peraturanModel->searchPeraturanFuzzy($partialParams, 5);
                $results = array_merge($results, $partialResults);
            }
        }

        // Hapus duplikat dan batasi hasil
        $uniqueResults = $this->removeDuplicateResults($results);
        return array_slice($uniqueResults, 0, 6);
    }

    /**
     * Strategi 9: Peraturan terbaru sebagai saran
     */
    private function strategyLatestRegulations(string $query, array $entities, array $params): array
    {
        $latestParams = [];
        $results = $this->peraturanModel->searchPeraturanForChatbot($latestParams, 5);
        
        // Tambahkan metadata bahwa ini adalah saran peraturan terbaru
        foreach ($results as &$result) {
            $result['is_latest_suggestion'] = true;
        }
        
        return $results;
    }

    /**
     * Strategi 10: Peraturan populer sebagai alternatif
     */
    private function strategyPopularRegulations(string $query, array $entities, array $params): array
    {
        $results = $this->peraturanModel->getPopularRegulations(5);
        
        // Tambahkan metadata bahwa ini adalah saran peraturan populer
        foreach ($results as &$result) {
            $result['is_popular_suggestion'] = true;
        }
        
        return $results;
    }

    /**
     * Ranking dan limit hasil berdasarkan relevansi
     */
    private function rankAndLimitResults(array $results, array $keywords, int $limit): array
    {
        // Gunakan advanced ranking dari NLPService
        $rankedResults = $this->nlpService->rankByAdvancedRelevance($results, [], $keywords);
        
        return array_slice($rankedResults, 0, $limit);
    }

    /**
     * Hapus hasil duplikat berdasarkan ID
     */
    private function removeDuplicateResults(array $results): array
    {
        $seen = [];
        $unique = [];
        
        foreach ($results as $result) {
            $id = $result['id_peraturan'] ?? $result['id'] ?? null;
            if ($id && !in_array($id, $seen)) {
                $seen[] = $id;
                $unique[] = $result;
            }
        }
        
        return $unique;
    }

    /**
     * Hitung confidence score untuk strategi fallback
     */
    private function calculateFallbackConfidence(string $strategyName): float
    {
        $confidenceMap = [
            'individual_keywords' => 0.8,
            'fuzzy_expanded' => 0.7,
            'fuzzy_original' => 0.6,
            'semantic_search' => 0.75,
            'category_based' => 0.65,
            'temporal_based' => 0.6,
            'topic_classification' => 0.7,
            'partial_match' => 0.5,
            'latest_regulations' => 0.3,
            'popular_regulations' => 0.2
        ];
        
        return $confidenceMap[$strategyName] ?? 0.5;
    }

    /**
     * Mapping jenis peraturan untuk fuzzy matching
     */
    private function getRegulationTypeMapping(): array
    {
        return [
            'peraturan daerah' => 'perda',
            'peraturan walikota' => 'perwali',
            'keputusan walikota' => 'kepwal',
            'peraturan gubernur' => 'pergub',
            'keputusan gubernur' => 'kepgub',
            'peraturan pemerintah' => 'pp',
            'peraturan presiden' => 'perpres',
            'peraturan menteri' => 'permen',
            'keputusan menteri' => 'kepmen',
            'instruksi presiden' => 'inpres',
            'surat edaran' => 'se'
        ];
    }

    /**
     * Log percobaan fallback
     */
    private function logFallbackAttempt(string $query, array $entities): void
    {
        $time = new Time('now');
        $date = $time->format('Y-m-d');
        $timestamp = $time->format('Y-m-d H:i:s');
        
        $logFile = WRITEPATH . "logs/chatbot/fallback-{$date}.log";
        
        $logData = [
            'timestamp' => $timestamp,
            'query' => $query,
            'entities' => $entities,
            'event' => 'fallback_initiated'
        ];
        
        $logJson = json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $logEntry = "[FALLBACK ATTEMPT] {$logJson}\n\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Log strategi yang berhasil
     */
    private function logSuccessfulStrategy(string $strategy, string $query, int $resultCount): void
    {
        $time = new Time('now');
        $date = $time->format('Y-m-d');
        $timestamp = $time->format('Y-m-d H:i:s');
        
        $logFile = WRITEPATH . "logs/chatbot/fallback-{$date}.log";
        
        $logData = [
            'timestamp' => $timestamp,
            'query' => $query,
            'successful_strategy' => $strategy,
            'result_count' => $resultCount,
            'event' => 'fallback_success'
        ];
        
        $logJson = json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $logEntry = "[FALLBACK SUCCESS] {$logJson}\n\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        log_message('info', "Fallback strategy '{$strategy}' successful for query: {$query} ({$resultCount} results)");
    }

    /**
     * Log ketika semua strategi gagal
     */
    private function logAllStrategiesFailed(string $query, array $entities): void
    {
        $time = new Time('now');
        $date = $time->format('Y-m-d');
        $timestamp = $time->format('Y-m-d H:i:s');
        
        $logFile = WRITEPATH . "logs/chatbot/fallback-{$date}.log";
        
        $logData = [
            'timestamp' => $timestamp,
            'query' => $query,
            'entities' => $entities,
            'event' => 'all_fallback_failed'
        ];
        
        $logJson = json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $logEntry = "[ALL FALLBACK FAILED] {$logJson}\n\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        log_message('warning', "All fallback strategies failed for query: {$query}");
    }

    /**
     * Generate keyword suggestions berdasarkan failed queries
     */
    public function generateSmartSuggestions(string $query, array $entities): array
    {
        $suggestions = [];
        
        // Saran berdasarkan topik yang terdeteksi
        if (!empty($entities['topic_classification'])) {
            foreach ($entities['topic_classification'] as $topic) {
                $suggestions = array_merge($suggestions, $topic['matched_keywords']);
            }
        }
        
        // Saran berdasarkan kategori umum
        $generalSuggestions = [
            'pajak', 'retribusi', 'perizinan', 'lingkungan', 'kesehatan',
            'pendidikan', 'transportasi', 'perdagangan', 'industri', 'pariwisata',
            'keamanan', 'ketertiban', 'sosial', 'budaya', 'olahraga'
        ];
        
        // Fuzzy match dengan general suggestions
        if (!empty($entities['keywords'])) {
            foreach ($entities['keywords'] as $keyword) {
                $match = $this->nlpService->fuzzyMatch($keyword, $generalSuggestions, 0.6);
                if ($match && !in_array($match, $suggestions)) {
                    $suggestions[] = $match;
                }
            }
        }
        
        // Jika tidak ada saran spesifik, berikan saran umum
        if (empty($suggestions)) {
            $suggestions = array_slice($generalSuggestions, 0, 5);
        }
        
        return array_unique(array_slice($suggestions, 0, 5));
    }
}