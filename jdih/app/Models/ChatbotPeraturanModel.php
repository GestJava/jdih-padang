<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotPeraturanModel extends Model
{
    protected $table            = 'web_peraturan';
    protected $primaryKey       = 'id_peraturan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = []; // Not used for this custom query model

    /**
     * Mencari peraturan yang relevan untuk chatbot menggunakan FULLTEXT search.
     *
     * @param string $keywords Kata kunci dari pengguna.
     * @param int    $limit    Jumlah hasil yang diinginkan.
     * @return array           Hasil pencarian.
     */
    public function searchPeraturanForChatbot($keywords, $limit = 3)
    {
        $original_keywords = trim($keywords);
        if (empty($original_keywords)) {
            return [];
        }

        $clean_keywords = ' ' . strtolower($original_keywords) . ' ';

        $year = null;
        if (preg_match('/[\s\W]tahun\s+(\d{4})[\s\W]/', $clean_keywords, $matches)) {
            $year = $matches[1];
            $clean_keywords = str_replace($matches[0], ' ', $clean_keywords);
        } elseif (preg_match('/[\s\W]((?:19|20)\d{2})[\s\W]/', $clean_keywords, $matches)) {
            $year = $matches[1];
            $clean_keywords = str_replace($matches[0], ' ', $clean_keywords);
        }

        $number = null;
        if (preg_match('/[\s\W]nomor\s+(\d+)[\s\W]/', $clean_keywords, $matches)) {
            $number = $matches[1];
            $clean_keywords = str_replace($matches[0], ' ', $clean_keywords);
        }

        $final_keywords = trim($clean_keywords);

        if ($year === null && $number === null) {
            $final_keywords = $original_keywords;
        }

        if (empty($final_keywords) && $year === null && $number === null) {
            return [];
        }

        // Start building the query.
        // FIX: Define the alias 'j' for the joined table.
        $builder = $this->from('web_peraturan', true) // Using from() to get a fresh builder instance
                        ->join('web_jenis_peraturan j', 'j.id_jenis_peraturan = web_peraturan.id_jenis_dokumen', 'left');

        $select_columns = "web_peraturan.id_peraturan, web_peraturan.judul, web_peraturan.nomor, web_peraturan.tahun, web_peraturan.abstrak_teks, web_peraturan.catatan_teks, web_peraturan.file_dokumen, web_peraturan.slug, j.nama_jenis";

        if (!empty($final_keywords)) {
            $escaped_keywords = $this->db->escape($final_keywords);
            $relevance_clause = "MATCH(web_peraturan.judul, web_peraturan.nomor, web_peraturan.abstrak_teks, web_peraturan.catatan_teks) AGAINST({$escaped_keywords} IN NATURAL LANGUAGE MODE)";
            $builder->select("{$select_columns}, {$relevance_clause} AS relevance_score");
            $builder->orderBy('relevance_score', 'DESC');

            if ($year === null && $number === null) {
                $builder->groupStart();
                $builder->where($relevance_clause);
                // FIX: Now this will work because 'j' is a defined alias.
                $builder->orLike('j.nama_jenis', $final_keywords);
                $builder->groupEnd();
            }
        } else {
            $builder->select("{$select_columns}, 10 AS relevance_score");
        }

        if ($number !== null) {
            $builder->where('web_peraturan.nomor', $number);
        }
        if ($year !== null) {
            $builder->where('web_peraturan.tahun', $year);
        }

        return $builder->where('web_peraturan.is_published', 1)
            ->orderBy('web_peraturan.tahun', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
