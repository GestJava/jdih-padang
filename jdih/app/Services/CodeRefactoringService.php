<?php

namespace App\Services;

use App\Models\ChatbotPeraturanModel;
use App\Models\ChatbotLogModel;
use App\Models\ChatbotFeedbackModel;
use App\Services\NLPService;
use App\Services\EntityExtractionService;
use App\Services\EnhancedFallbackService;
use App\Services\AIPromptService;
use Config\NLPConfig;

/**
 * Service untuk refactoring dan simplification ChatbotController
 * Memisahkan logic menjadi komponen-komponen yang lebih kecil dan terorganisir
 */
class CodeRefactoringService
{
    protected ChatbotPeraturanModel $peraturanModel;
    protected ChatbotLogModel $logModel;
    protected ChatbotFeedbackModel $feedbackModel;
    protected NLPService $nlpService;
    protected EntityExtractionService $entityService;
    protected EnhancedFallbackService $fallbackService;
    protected AIPromptService $promptService;
    protected NLPConfig $nlpConfig;
    
    public function __construct()
    {
        $this->peraturanModel = new ChatbotPeraturanModel();
        $this->logModel = new ChatbotLogModel();
        $this->feedbackModel = new ChatbotFeedbackModel();
        $this->nlpService = new NLPService();
        $this->entityService = new EntityExtractionService();
        $this->fallbackService = new EnhancedFallbackService();
        $this->promptService = new AIPromptService();
        $this->nlpConfig = new NLPConfig();
    }

    /**
     * Main method untuk memproses query chatbot
     * Simplified version dengan hybrid approach: simple search + AI enhancement
     */
    public function processQuery(string $userQuery, array $requestData = []): array
    {
        try {
            // 1. Preprocessing query pengguna untuk mendapatkan kata kunci yang lebih baik
            $keywords = $this->nlpService->extractKeywords($userQuery);
            $searchQuery = implode(' ', $keywords);

        // Log untuk debugging
        log_message('debug', 'Chatbot Keywords Extracted: ' . $searchQuery);

            // 2. Lakukan pencarian cerdas dengan skor relevansi
            // Jika tidak ada kata kunci, gunakan query asli
            $finalResults = $this->peraturanModel->searchPeraturanForChatbot($searchQuery ?: $userQuery, 10);
            $searchMethod = 'relevance_search';

            // 3. Bangun prompt yang dioptimalkan untuk AI
            $prompt = $this->buildSimplifiedPrompt($userQuery, $finalResults);

            // 4. Panggil layanan AI untuk mendapatkan respons
            $aiResponse = $this->callAIService($prompt);

            // 5. Catat interaksi untuk analisis
            $this->logSimplifiedInteraction($userQuery, $finalResults, $aiResponse, $searchMethod, $requestData);

            return [
                'success'       => true,
                'response'      => $aiResponse,
                'results'       => $finalResults,
                'search_method' => $searchMethod,
                'result_count'  => count($finalResults)
            ];

        } catch (\Exception $e) {
            // Tangani error dengan logging dan respons yang ramah
            log_message('error', 'Chatbot processing error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

            return [
                'success'       => false,
                'error'         => 'Terjadi kesalahan dalam memproses pertanyaan Anda.',
                'response'      => 'Maaf, saya mengalami kendala teknis. Silakan coba lagi dalam beberapa saat.',
                'results'       => [],
                'search_method' => 'error'
            ];
        }
    }
    
    /**
     * Build simplified AI prompt
     */
    private function buildSimplifiedPrompt(string $query, array $results): string
    {
        return $this->promptService->buildSimplifiedPrompt($query, $results);
    }
    
    /**
     * Log simplified interaction
     */
    private function logSimplifiedInteraction(string $query, array $results, string $response, string $method, array $requestData): void
    {
        $this->logModel->insert([
            'user_query' => $query,
            'ai_response' => $response,
            'search_method' => $method,
            'result_count' => count($results),
            'ip_address' => $requestData['ip_address'] ?? null,
            'user_agent' => $requestData['user_agent'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }




    /**
     * Call AI service (Gemini API)
     */
    private function callAIService(string $prompt): string
    {
        $geminiService = \Config\Services::gemini();
        return $geminiService->generateContent($prompt);
    }

    /**
     * Legacy method - use simplified logging
     */
    private function logInteraction(string $query, array $entities, array $searchParams, string $response, array $results, array $requestData): void
    {
        $this->logSimplifiedInteraction($query, $results, $response, 'legacy', $requestData);
    }
    
    // Removed duplicate callAIService method and legacy complexity calculation methods
    // Using simplified approach with fixed limits

    /**
     * Process feedback dari user - simplified
     */
    public function processFeedback(array $feedbackData): array
    {
        try {
            $data = [
                'feedback_type' => $feedbackData['type'] ?? 'general',
                'rating' => $feedbackData['rating'] ?? null,
                'comment' => $feedbackData['comment'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            $this->feedbackModel->insert($data);
            
            return [
                'success' => true,
                'message' => 'Terima kasih atas feedback Anda!'
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Feedback processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal menyimpan feedback.'
            ];
        }
    }

    /**
     * Get simplified analytics data
     */
    public function getAnalytics(array $filters = []): array
    {
        try {
            $builder = $this->logModel->select([
                'DATE(created_at) as date',
                'COUNT(*) as total_queries',
                'AVG(result_count) as avg_results',
                'search_method',
                'COUNT(CASE WHEN result_count = 0 THEN 1 END) as empty_results_count'
            ]);
            
            if (!empty($filters['start_date'])) {
                $builder->where('DATE(created_at) >=', $filters['start_date']);
            }
            
            if (!empty($filters['end_date'])) {
                $builder->where('DATE(created_at) <=', $filters['end_date']);
            }
            
            $dailyStats = $builder->groupBy('DATE(created_at)')
                                 ->orderBy('date', 'DESC')
                                 ->limit(30)
                                 ->get()
                                 ->getResultArray();
            
            // Get popular queries
            $popularQueries = $this->logModel->select('user_query as query, COUNT(*) as frequency')
                                             ->where('result_count >', 0)
                                             ->groupBy('user_query')
                                             ->orderBy('frequency', 'DESC')
                                             ->limit(10)
                                             ->get()
                                             ->getResultArray();
            
            // Get failed queries (empty results)
            $failedQueries = $this->logModel->select('user_query as query, COUNT(*) as frequency')
                                            ->where('result_count', 0)
                                            ->groupBy('user_query')
                                            ->orderBy('frequency', 'DESC')
                                            ->limit(10)
                                            ->get()
                                            ->getResultArray();
            
            return [
                'daily_stats' => $dailyStats,
                'popular_queries' => $popularQueries,
                'failed_queries' => $failedQueries,
                'summary' => [
                    'total_queries' => array_sum(array_column($dailyStats, 'total_queries')),
                    'avg_success_rate' => $this->calculateSuccessRate($dailyStats)
                ]
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Analytics error: ' . $e->getMessage());
            return [
                'daily_stats' => [],
                'popular_queries' => [],
                'failed_queries' => [],
                'summary' => [
                    'total_queries' => 0,
                    'avg_success_rate' => 0
                ]
            ];
        }
    }

    /**
     * Calculate success rate dari daily stats
     */
    private function calculateSuccessRate(array $dailyStats): float
    {
        if (empty($dailyStats)) {
            return 0.0;
        }
        
        $totalQueries = array_sum(array_column($dailyStats, 'total_queries'));
        $totalEmptyResults = array_sum(array_column($dailyStats, 'empty_results_count'));
        
        if ($totalQueries === 0) {
            return 0.0;
        }
        
        return (($totalQueries - $totalEmptyResults) / $totalQueries) * 100;
    }
}