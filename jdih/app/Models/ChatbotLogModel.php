<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotLogModel extends Model
{
    protected $table = 'chatbot_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'session_id',
        'query',
        'response',
        'search_results_count',
        'processing_time',
        'ai_response_time',
        'search_strategy',
        'entities_extracted',
        'confidence_score',
        'feedback_rating',
        'feedback_comment',
        'ip_address',
        'user_agent',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'query' => 'required|min_length[1]|max_length[1000]',
        'response' => 'required',
        'search_results_count' => 'permit_empty|integer',
        'processing_time' => 'permit_empty|decimal',
        'ai_response_time' => 'permit_empty|decimal',
        'confidence_score' => 'permit_empty|decimal',
        'feedback_rating' => 'permit_empty|integer|in_list[1,2,3,4,5]'
    ];

    protected $validationMessages = [
        'query' => [
            'required' => 'Query is required',
            'min_length' => 'Query must be at least 1 character',
            'max_length' => 'Query cannot exceed 1000 characters'
        ],
        'response' => [
            'required' => 'Response is required'
        ],
        'feedback_rating' => [
            'in_list' => 'Feedback rating must be between 1 and 5'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Log a chatbot interaction
     */
    public function logInteraction($data)
    {
        // Ensure required fields are present
        $logData = [
            'user_id' => $data['user_id'] ?? null,
            'session_id' => $data['session_id'] ?? session_id(),
            'query' => $data['query'],
            'response' => $data['response'],
            'search_results_count' => $data['search_results_count'] ?? 0,
            'processing_time' => $data['processing_time'] ?? null,
            'ai_response_time' => $data['ai_response_time'] ?? null,
            'search_strategy' => $data['search_strategy'] ?? 'standard',
            'entities_extracted' => is_array($data['entities_extracted'] ?? null) 
                ? json_encode($data['entities_extracted']) 
                : $data['entities_extracted'],
            'confidence_score' => $data['confidence_score'] ?? null,
            'ip_address' => $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        return $this->insert($logData);
    }

    /**
     * Update feedback for a log entry
     */
    public function updateFeedback($logId, $rating, $comment = null)
    {
        return $this->update($logId, [
            'feedback_rating' => $rating,
            'feedback_comment' => $comment
        ]);
    }

    /**
     * Get analytics data
     */
    public function getAnalytics($startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        return [
            'total_queries' => $builder->countAllResults(false),
            'avg_processing_time' => $builder->selectAvg('processing_time')->get()->getRow()->processing_time,
            'avg_confidence' => $builder->selectAvg('confidence_score')->get()->getRow()->confidence_score,
            'feedback_stats' => $this->getFeedbackStats($startDate, $endDate)
        ];
    }

    /**
     * Get feedback statistics
     */
    public function getFeedbackStats($startDate = null, $endDate = null)
    {
        $builder = $this->builder()
            ->select('feedback_rating, COUNT(*) as count')
            ->where('feedback_rating IS NOT NULL')
            ->groupBy('feedback_rating');
            
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get popular queries
     */
    public function getPopularQueries($limit = 10, $days = 30)
    {
        return $this->builder()
            ->select('query, COUNT(*) as count')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->groupBy('query')
            ->orderBy('count', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs($limit = 50)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}