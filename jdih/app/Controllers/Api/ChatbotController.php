<?php

namespace App\Controllers\Api;

use CodeIgniter\I18n\Time;

use App\Controllers\BaseController;
use App\Services\CodeRefactoringService;
use App\Services\NLPService;
use App\Services\EntityExtractionService;
use App\Services\EnhancedFallbackService;
use App\Services\AIPromptService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ChatbotController extends BaseController
{
    use ResponseTrait;

    // Services and Models are initialized in the constructor for efficiency.
    protected NLPService $nlpService;
    protected AIPromptService $aiPromptService;
    protected \App\Models\ChatbotPeraturanModel $chatbotModel;

    public function __construct()
    {
        // Initialize services and models here to avoid re-creation on every request
        $this->nlpService = new \App\Services\NLPService();
        $this->aiPromptService = new \App\Services\AIPromptService();
        $this->chatbotModel = new \App\Models\ChatbotPeraturanModel();
    }

    public function ask()
    {
        $json = $this->request->getJSON();
        $query = $json->query ?? '';
        $interactionId = $json->interaction_id ?? uniqid('chat_', true);

        if (empty(trim($query))) {
            return $this->fail('Pertanyaan tidak boleh kosong.', 400);
        }

        try {
            // 1. Extract keywords
            $keywords = $this->nlpService->extractKeywords($query);
            $results = [];

            if (!empty($keywords)) {
                // 2. Search Database
                $keywordString = implode(' ', $keywords);
                $results = $this->chatbotModel->searchPeraturanForChatbot($keywordString, 3);
            }
            
            // 3. Generate AI Prompt
            $prompt = $this->aiPromptService->buildSimplifiedPrompt($query, $results);

            // 4. Get AI Response from Gemini
            $aiResponse = $this->aiPromptService->getGeminiResponse($prompt);

            // 5. Log and Return Response
            $this->logChatbotInteraction($interactionId, $query, $aiResponse, ['keywords' => $keywords], $results);

            $finalResponsePayload = ['response' => $aiResponse, 'interaction_id' => $interactionId, 'csrf_token' => csrf_hash()];

            return $this->respond($finalResponsePayload);

        } catch (\Throwable $e) {
            log_message('error', '[CHATBOT_ERROR] ' . $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->failServerError('Maaf, terjadi masalah pada sistem internal kami. Silakan coba lagi nanti.');
        }
    }

    private function logChatbotInteraction(string $interactionId, string $query, string $response, array $entities, array $results = []): void
    {
        $db = \Config\Database::connect();
        $builder = $db->table('chatbot_interactions');

        $logData = [
            'interaction_id' => $interactionId,
            'user_query' => $query,
            'ai_response' => $response,
            'entities' => json_encode($entities),
            'search_results' => json_encode($results),
            'search_params' => json_encode(['limit' => 3]), // Using the hardcoded limit from the search call
            'created_at' => new Time('now', 'Asia/Jakarta')
        ];

        try {
            $builder->insert($logData);
        } catch (\Exception $e) {
            log_message('error', '[CHATBOT_LOG_ERROR] Failed to log interaction: ' . $e->getMessage());
        }
    }
}
