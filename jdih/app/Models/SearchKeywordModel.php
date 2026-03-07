<?php

namespace App\Models;

use CodeIgniter\Model;

class SearchKeywordModel extends Model
{
    protected $table            = 'search_keywords';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'keyword',
        'search_count',
        'last_searched',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'keyword' => 'required|min_length[2]|max_length[255]',
    ];

    protected $validationMessages = [
        'keyword' => [
            'required' => 'Keyword is required',
            'min_length' => 'Keyword must be at least 2 characters',
            'max_length' => 'Keyword cannot exceed 255 characters'
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Record atau update keyword pencarian
     * Hanya menyimpan keyword jika ada hasil pencarian (hasResults = true)
     * 
     * @param string $keyword Keyword yang dicari
     * @param bool $hasResults Apakah pencarian menghasilkan hasil (default: true untuk backward compatibility)
     * @return bool|int ID record atau false jika gagal
     */
    public function recordSearch($keyword, $hasResults = true)
    {
        try {
            // Cek apakah tabel ada
            if (!$this->db->tableExists($this->table)) {
                log_message('debug', 'Table search_keywords does not exist, cannot record search');
                return false;
            }

            if (empty($keyword) || strlen(trim($keyword)) < 2) {
                return false;
            }

            // Normalize keyword: trim, lowercase, remove extra spaces, filter non-Latin
            // Jika normalizeKeyword return false, berarti keyword tidak valid (non-Latin characters)
            $originalKeyword = $keyword;
            $keyword = $this->normalizeKeyword($keyword);
            if ($keyword === false) {
                log_message('info', 'Invalid keyword rejected (non-Latin, too short, or invalid): ' . $originalKeyword);
                return false;
            }

            // Filter kata-kata kotor
            if ($this->isProfanity($keyword)) {
                log_message('debug', 'Profanity detected, skipping keyword: ' . $keyword);
                return false;
            }

            // Hanya simpan jika ada hasil pencarian
            if (!$hasResults) {
                log_message('debug', 'No search results, skipping keyword: ' . $keyword);
                return false;
            }

            // Cari keyword yang sudah ada
            $existing = $this->where('keyword', $keyword)->first();
            
            // Debug: log untuk troubleshooting
            log_message('info', 'SearchKeywordModel::recordSearch - keyword: ' . $keyword . ', existing: ' . ($existing ? 'YES (ID: ' . $existing['id'] . ')' : 'NO'));

            if ($existing && !empty($existing['id'])) {
                // Update search count dan last_searched
                $updateData = [
                    'search_count' => (int)$existing['search_count'] + 1,
                    'last_searched' => date('Y-m-d H:i:s')
                ];
                
                $updateResult = $this->update($existing['id'], $updateData);
                
                if ($updateResult) {
                    // Invalidate cache popular keywords ketika ada update
                    $cache = \Config\Services::cache();
                    $cache->delete('popular_search_keywords');
                    $cache->delete('homepage_data'); // Juga clear homepage_data karena popular_tags ada di dalamnya
                    
                    log_message('info', 'SearchKeywordModel::recordSearch - Updated keyword: ' . $keyword . ' (ID: ' . $existing['id'] . ', new count: ' . $updateData['search_count'] . ')');
                    return $existing['id'];
                } else {
                    log_message('error', 'SearchKeywordModel::recordSearch - Failed to update keyword: ' . $keyword);
                    return false;
                }
            } else {
                // Insert keyword baru
                $insertData = [
                    'keyword' => $keyword,
                    'search_count' => 1,
                    'last_searched' => date('Y-m-d H:i:s')
                ];
                
                $insertId = $this->insert($insertData);
                
                if ($insertId) {
                    // Invalidate cache popular keywords ketika ada keyword baru
                    $cache = \Config\Services::cache();
                    $cache->delete('popular_search_keywords');
                    $cache->delete('homepage_data'); // Juga clear homepage_data karena popular_tags ada di dalamnya
                    
                    log_message('info', 'SearchKeywordModel::recordSearch - Inserted new keyword: ' . $keyword . ' (ID: ' . $insertId . ')');
                    return $insertId;
                } else {
                    $errors = $this->errors();
                    log_message('error', 'SearchKeywordModel::recordSearch - Failed to insert keyword: ' . $keyword . ', errors: ' . json_encode($errors));
                    return false;
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error recording search keyword: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek apakah keyword mengandung kata-kata kotor
     * 
     * @param string $keyword
     * @return bool True jika mengandung kata kotor
     */
    private function isProfanity($keyword)
    {
        // List kata-kata kotor yang umum (bisa ditambah sesuai kebutuhan)
        $profanityList = [
            'anjing', 'babi', 'bangsat', 'bajingan', 'kontol', 'memek', 'ngentot', 'ngewe',
            'jancok', 'jancuk', 'jancok', 'asu', 'bego', 'tolol', 'goblok', 'bodoh',
            'pantek', 'pantat', 'bokep', 'porno', 'sex', 'xxx', 'fuck', 'shit',
            'bitch', 'asshole', 'bastard', 'damn', 'hell'
        ];

        $keywordLower = strtolower($keyword);
        
        // Cek apakah keyword mengandung kata kotor
        foreach ($profanityList as $profanity) {
            if (strpos($keywordLower, $profanity) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mendapatkan popular keywords berdasarkan search count
     * 
     * @param int $limit Jumlah keyword yang dikembalikan
     * @param int $minSearchCount Minimum search count untuk ditampilkan
     * @param int $daysLimit Batasi hanya keyword yang dicari dalam X hari terakhir (0 = semua)
     * @return array Array of popular keywords
     */
    public function getPopularKeywords($limit = 5, $minSearchCount = 1, $daysLimit = 90)
    {
        try {
            // Cek apakah tabel ada
            if (!$this->db->tableExists($this->table)) {
                log_message('debug', 'Table search_keywords does not exist, returning empty array');
                return [];
            }

            $builder = $this->builder();
            
            // Filter minimum search count (ubah default ke 1 agar keyword baru langsung muncul)
            $builder->where('search_count >=', $minSearchCount);
            
            // Filter berdasarkan hari terakhir (jika daysLimit > 0)
            if ($daysLimit > 0) {
                $dateLimit = date('Y-m-d H:i:s', strtotime("-{$daysLimit} days"));
                $builder->where('last_searched >=', $dateLimit);
            }
            
            // Order by search count descending, kemudian last_searched descending
            $builder->orderBy('search_count', 'DESC');
            $builder->orderBy('last_searched', 'DESC');
            
            // Limit hasil
            $builder->limit($limit);
            
            $query = $builder->get();
            
            // Cek apakah query berhasil
            if ($query === false) {
                log_message('error', 'Failed to get popular keywords from search_keywords table - query returned false');
                return [];
            }
            
            $results = $query->getResultArray();
            
            // Debug: Log hasil query
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                log_message('debug', 'getPopularKeywords query returned ' . count($results) . ' results');
            }
            
            // Debug logging (development only)
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                log_message('debug', 'getPopularKeywords found ' . count($results) . ' keywords');
                if (!empty($results)) {
                    log_message('debug', 'Popular keywords: ' . json_encode(array_column($results, 'keyword')));
                }
            }
            
            // Format hasil untuk kompatibilitas dengan komponen hero-search
            $formatted = [];
            foreach ($results as $result) {
                $formatted[] = [
                    'slug_tag' => url_title($result['keyword'], '-', true),
                    'nama_tag' => $result['keyword'],
                    'search_count' => $result['search_count']
                ];
            }
            
            return $formatted;
        } catch (\Exception $e) {
            log_message('error', 'Error getting popular keywords: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Normalize keyword: trim, lowercase, remove extra spaces
     * Hanya terima karakter Latin (a-z, A-Z), angka, dan spasi
     * 
     * @param string $keyword
     * @return string|false False jika keyword tidak valid (mengandung karakter non-Latin)
     */
    private function normalizeKeyword($keyword)
    {
        // Trim whitespace
        $keyword = trim($keyword);
        
        // Convert to lowercase
        $keyword = strtolower($keyword);
        
        // Remove extra spaces
        $keyword = preg_replace('/\s+/', ' ', $keyword);
        
        // Hanya terima karakter Latin (a-z), angka (0-9), spasi, dan tanda hubung
        // Tolak semua karakter non-Latin (Chinese, Japanese, Arabic, dll)
        $keyword = preg_replace('/[^a-z0-9\s\-]/u', '', $keyword);
        
        // Jika setelah filtering menjadi kosong atau terlalu pendek, return false
        if (empty($keyword) || strlen(trim($keyword)) < 2) {
            return false;
        }
        
        // Safety check: Pastikan tidak ada karakter non-ASCII yang tersisa
        if (!preg_match('/^[\x00-\x7F]+$/', $keyword)) {
            return false;
        }
        
        return trim($keyword);
    }

    /**
     * Cleanup keywords yang sudah lama tidak digunakan
     * 
     * @param int $daysOld Hapus keyword yang tidak digunakan lebih dari X hari
     * @return int Jumlah keyword yang dihapus
     */
    public function cleanupOldKeywords($daysOld = 365)
    {
        $dateLimit = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        return $this->where('last_searched <', $dateLimit)
            ->where('search_count <', 3) // Hanya hapus yang search count rendah
            ->delete();
    }
}

