<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotFeedbackModel extends Model
{
    protected $table = 'chatbot_feedback';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'log_id',
        'user_id',
        'session_id',
        'feedback_type',
        'rating',
        'comment',
        'suggestion',
        'query_satisfaction',
        'response_accuracy',
        'response_helpfulness',
        'system_performance',
        'improvement_areas',
        'contact_email',
        'follow_up_required',
        'status',
        'admin_response',
        'admin_user_id',
        'resolved_at',
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
        'feedback_type' => 'required|in_list[rating,suggestion,complaint,compliment,bug_report]',
        'rating' => 'permit_empty|integer|greater_than[0]|less_than[6]',
        'comment' => 'permit_empty|max_length[1000]',
        'suggestion' => 'permit_empty|max_length[1000]',
        'query_satisfaction' => 'permit_empty|integer|in_list[1,2,3,4,5]',
        'response_accuracy' => 'permit_empty|integer|in_list[1,2,3,4,5]',
        'response_helpfulness' => 'permit_empty|integer|in_list[1,2,3,4,5]',
        'system_performance' => 'permit_empty|integer|in_list[1,2,3,4,5]',
        'contact_email' => 'permit_empty|valid_email',
        'status' => 'permit_empty|in_list[pending,reviewed,resolved,closed]'
    ];

    protected $validationMessages = [
        'feedback_type' => [
            'required' => 'Feedback type is required',
            'in_list' => 'Invalid feedback type'
        ],
        'rating' => [
            'integer' => 'Rating must be a number',
            'greater_than' => 'Rating must be between 1 and 5',
            'less_than' => 'Rating must be between 1 and 5'
        ],
        'comment' => [
            'max_length' => 'Comment cannot exceed 1000 characters'
        ],
        'suggestion' => [
            'max_length' => 'Suggestion cannot exceed 1000 characters'
        ],
        'contact_email' => [
            'valid_email' => 'Please provide a valid email address'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setDefaults'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Set default values before insert
     */
    protected function setDefaults(array $data)
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'pending';
        }
        
        if (!isset($data['data']['ip_address'])) {
            $data['data']['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        
        if (!isset($data['data']['user_agent'])) {
            $data['data']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }
        
        return $data;
    }

    /**
     * Submit feedback
     */
    public function submitFeedback($feedbackData)
    {
        // Ensure required session data
        $data = [
            'log_id' => $feedbackData['log_id'] ?? null,
            'user_id' => $feedbackData['user_id'] ?? null,
            'session_id' => $feedbackData['session_id'] ?? session_id(),
            'feedback_type' => $feedbackData['feedback_type'],
            'rating' => $feedbackData['rating'] ?? null,
            'comment' => $feedbackData['comment'] ?? null,
            'suggestion' => $feedbackData['suggestion'] ?? null,
            'query_satisfaction' => $feedbackData['query_satisfaction'] ?? null,
            'response_accuracy' => $feedbackData['response_accuracy'] ?? null,
            'response_helpfulness' => $feedbackData['response_helpfulness'] ?? null,
            'system_performance' => $feedbackData['system_performance'] ?? null,
            'improvement_areas' => is_array($feedbackData['improvement_areas'] ?? null) 
                ? json_encode($feedbackData['improvement_areas']) 
                : $feedbackData['improvement_areas'],
            'contact_email' => $feedbackData['contact_email'] ?? null,
            'follow_up_required' => $feedbackData['follow_up_required'] ?? false
        ];

        return $this->insert($data);
    }

    /**
     * Get feedback statistics
     */
    public function getFeedbackStats($startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        $stats = [
            'total_feedback' => $builder->countAllResults(false),
            'avg_rating' => $builder->selectAvg('rating')->get()->getRow()->rating ?? 0,
            'feedback_by_type' => $this->getFeedbackByType($startDate, $endDate),
            'satisfaction_scores' => $this->getSatisfactionScores($startDate, $endDate),
            'pending_feedback' => $this->where('status', 'pending')->countAllResults()
        ];

        return $stats;
    }

    /**
     * Get feedback grouped by type
     */
    public function getFeedbackByType($startDate = null, $endDate = null)
    {
        $builder = $this->builder()
            ->select('feedback_type, COUNT(*) as count')
            ->groupBy('feedback_type');
            
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get satisfaction scores
     */
    public function getSatisfactionScores($startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        return [
            'query_satisfaction' => $builder->selectAvg('query_satisfaction')->get()->getRow()->query_satisfaction ?? 0,
            'response_accuracy' => $builder->selectAvg('response_accuracy')->get()->getRow()->response_accuracy ?? 0,
            'response_helpfulness' => $builder->selectAvg('response_helpfulness')->get()->getRow()->response_helpfulness ?? 0,
            'system_performance' => $builder->selectAvg('system_performance')->get()->getRow()->system_performance ?? 0
        ];
    }

    /**
     * Get pending feedback for admin review
     */
    public function getPendingFeedback($limit = 50)
    {
        return $this->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get feedback requiring follow-up
     */
    public function getFollowUpRequired($limit = 20)
    {
        return $this->where('follow_up_required', true)
            ->where('status !=', 'resolved')
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Update feedback status
     */
    public function updateStatus($feedbackId, $status, $adminUserId = null, $adminResponse = null)
    {
        $updateData = [
            'status' => $status,
            'admin_user_id' => $adminUserId,
            'admin_response' => $adminResponse
        ];

        if ($status === 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($feedbackId, $updateData);
    }

    /**
     * Get recent feedback
     */
    public function getRecentFeedback($limit = 20)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get feedback by rating range
     */
    public function getFeedbackByRating($minRating = 1, $maxRating = 5, $limit = 100)
    {
        return $this->where('rating >=', $minRating)
            ->where('rating <=', $maxRating)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get improvement suggestions
     */
    public function getImprovementSuggestions($limit = 50)
    {
        return $this->where('feedback_type', 'suggestion')
            ->where('suggestion IS NOT NULL')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Search feedback by keyword
     */
    public function searchFeedback($keyword, $limit = 50)
    {
        return $this->groupStart()
                ->like('comment', $keyword)
                ->orLike('suggestion', $keyword)
                ->orLike('admin_response', $keyword)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}