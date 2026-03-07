<?php

namespace App\Services;

use Config\NLPConfig;

use Config\Services;

class AIPromptService
{
        protected NLPConfig $nlpConfig;
    protected \Config\ChatbotConfig $chatbotConfig;

    public function __construct()
    {
        $this->nlpConfig = new NLPConfig();
        $this->chatbotConfig = new \Config\ChatbotConfig();
    }

    /**
     * Build simplified prompt - faster and more reliable
     */
    public function buildSimplifiedPrompt(string $userQuery, array $results): string
    {
        $prompt = "Anda adalah asisten AI JDIH Kota Padang yang cerdas, profesional, dan bersahabat.\n";
        $prompt .= "Tugas Anda adalah memberikan jawaban yang akurat dan mudah dipahami berdasarkan konteks peraturan yang saya berikan.\n\n";
        $prompt .= "========================================\n";
        $prompt .= "PERTANYAAN PENGGUNA:\n";
        $prompt .= "'" . $userQuery . "'\n";
        $prompt .= "========================================\n\n";
        
        if (!empty($results)) {
            $prompt .= "KONTEKS PERATURAN (Gunakan ini sebagai sumber utama jawaban Anda):
";
            foreach ($results as $index => $result) {
                $num = $index + 1;
                $downloadUrl = base_url('peraturan/download/' . $result['id_peraturan']);
                $prompt .= "----------------------------------------\n";
                $prompt .= "Dokumen {$num}: {$result['judul']} ({$result['nama_jenis']} Nomor {$result['nomor']} Tahun {$result['tahun']})\n";

                if (!empty($result['abstrak_teks'])) {
                    $prompt .= "Abstrak: " . substr(strip_tags(str_replace("\n", " ", $result['abstrak_teks'])), 0, 1500) . "...\n";
                }
                 if (!empty($result['catatan_teks'])) {
                    $prompt .= "Catatan: " . substr(strip_tags(str_replace("\n", " ", $result['catatan_teks'])), 0, 1500) . "...\n";
                }
                $prompt .= "Relevance Score: {$result['relevance_score']}\n";
                $prompt .= "Link Download: [Download Dokumen]({$downloadUrl})\n";
                $prompt .= "----------------------------------------\n";
            }
        } else {
            $prompt .= "KONTEKS: Tidak ada dokumen peraturan yang ditemukan terkait pertanyaan pengguna.\n\n";
        }
        
        $prompt .= "========================================\n";
        $prompt .= "INSTRUKSI UNTUK ANDA (WAJIB DIIKUTI):\n";
        $prompt .= "1. **ANALISIS PERTANYAAN PENGGUNA:** Pertama, pahami maksud pengguna. Jika pertanyaan berupa cerita, keluhan, atau skenario (contoh: 'Saya punya warung tapi didatangi Satpol PP, bagaimana aturannya?'), tugas Anda adalah mengidentifikasi masalah utamanya, lalu berikan jawaban yang bersifat solutif dan konsultatif berdasarkan peraturan yang relevan. Jika pertanyaannya adalah pencarian langsung (contoh: 'Perda tentang IMB'), langsung berikan informasi peraturan tersebut.\n";
        $prompt .= "2. **JAWAB BERDASARKAN KONTEKS:** Gunakan HANYA informasi dari 'KONTEKS PERATURAN' di atas. Jangan mengarang atau menggunakan pengetahuan eksternal.\n";
        $prompt .= "3. **NAMA PERATURAN:** Sebutkan selalu jenis peraturan secara lengkap sesuai data yang ditemukan (contoh: 'Peraturan Daerah', 'Keputusan Walikota'). Jangan pernah menyingkatnya menjadi 'Perda' atau 'Kepwal', kecuali pengguna yang memulainya.\n";
        $prompt .= "4. **Fokus pada peraturan yang paling relevan (skor tertinggi dan tahun terbaru).** Jelaskan isinya secara ringkas dengan MERANGKUM informasi dari 'Abstrak' dan 'Catatan' jika tersedia. Jelaskan juga mengapa peraturan itu relevan dengan pertanyaan pengguna. **Selalu sertakan link download untuk setiap peraturan yang Anda sebutkan.** Sebutkan 1-2 peraturan relevan lainnya sebagai alternatif jika tersedia.\n";
        $prompt .= "5. **PANDUAN PENYELESAIAN MASALAH (JIKA TIDAK ADA PERATURAN RELEVAN):**\n";
        $prompt .= "   Jika Anda tidak menemukan peraturan yang secara langsung menjawab keluhan pengguna, tugas Anda adalah menjadi pemandu cerdas dengan menyarankan lembaga yang tepat berdasarkan kategori masalah. Analisis keluhan pengguna dan arahkan ke salah satu dari berikut ini:\n\n";
        $prompt .= "   - **Jika masalah terkait PELAYANAN PUBLIK BURUK** (misal: petugas tidak ramah, prosedur berbelit, pungli oleh aparat sipil), sarankan melapor ke **Inspektorat Daerah Kota Padang**, **Ombudsman RI Perwakilan Sumbar**, atau **SP4N LAPOR!**.\n";
        $prompt .= "   - **Jika masalah terkait SENGKETA INFORMASI PUBLIK** (misal: permintaan informasi ditolak badan publik), sarankan mengajukan sengketa ke **Komisi Informasi (KI) Sumatera Barat**.\n";
        $prompt .= "   - **Jika masalah terkait TINDAK PIDANA** (misal: penipuan, pencurian, pengancaman), sarankan segera melapor ke **Kepolisian (Polresta Padang atau Polsek terdekat)**.\n";
        $prompt .= "   - **Jika masalah terkait SENGKETA PERDATA** (misal: sengketa tanah, wanprestasi), jelaskan ini ranah perdata dan sarankan mediasi atau gugatan ke **Pengadilan Negeri Padang**.\n";
        $prompt .= "   - **Jika masalah terkait KETENAGAKERJAAN** (misal: PHK sepihak, upah tidak dibayar), sarankan melapor ke **Dinas Tenaga Kerja dan Perindustrian Kota Padang**.\n";
        $prompt .= "   - **Jika masalah terkait PERLINDUNGAN KONSUMEN** (misal: barang rusak, iklan menipu), sarankan mengadu ke **Badan Penyelesaian Sengketa Konsumen (BPSK) Padang**.\n\n";
        $prompt .= "   **SOLUSI KONSULTASI HUKUM (PALING UTAMA):**\n";
        $prompt .= "   Untuk SEMUA masalah di atas, atau jika pengguna masih bingung, **selalu** tutup jawaban Anda dengan menyarankan konsultasi hukum gratis dengan datang langsung ke **Bagian Hukum Sekretariat Daerah Kota Padang** di alamat **Komplek Balaikota Air Pacah, Lantai 3**. Ini adalah jaring pengaman untuk semua jenis masalah.\n";
        $prompt .= "6. **FORMAT JAWABAN:** Gunakan Markdown untuk format yang rapi (bold, italic, list). **Setiap kali Anda menyebutkan sebuah peraturan, WAJIB sertakan link downloadnya secara lengkap menggunakan URL absolut yang sudah disediakan.** Jangan mengubah format URL ini.\n";
        $prompt .= "7. **GAYA BAHASA:** Ramah, empatik, profesional, dan mudah dimengerti. Sapa pengguna dan akhiri dengan menawarkan bantuan lebih lanjut.\n";
        $prompt .= "8. **FILTER KEAMANAN (PENTING):** Jika pertanyaan pengguna mengandung kata-kata kotor, hinaan, cacian, atau unsur SARA, **TOLAK PERMINTAAN TERSEBUT**. Berikan jawaban yang tegas, profesional, dan tidak menghakimi. Contoh: 'Mohon maaf, saya diprogram untuk tidak menanggapi bahasa yang tidak pantas. Mari kita kembali ke topik hukum dan pemerintahan Kota Padang. Adakah yang bisa saya bantu terkait hal tersebut?'\n";
        $prompt .= "9. **RESPONS PENUTUP:** Jika pengguna mengucapkan terima kasih atau kalimat penutup lainnya, balas dengan ramah. Contoh: 'Sama-sama! Senang bisa membantu Anda. Jika ada pertanyaan lain seputar hukum di Kota Padang, jangan ragu bertanya lagi ya.'\n";
        $prompt .= "========================================\n\n";
        $prompt .= "JAWABAN ANDA:
";
        
        return $prompt;
    }
    
    /**
     * Build enhanced prompt berdasarkan context dan entities (legacy)
     */
    public function buildEnhancedPrompt(string $userQuery, array $context, array $entities = []): string
    {
        // For backward compatibility, use simplified approach
        return $this->buildSimplifiedPrompt($userQuery, $context);
    }

    /**
     * Sends the prompt to the Google Gemini API and returns the response.
     *
     * @param string $prompt The complete prompt to send to the AI.
     * @return string The text response from the AI.
     */
    public function getGeminiResponse(string $prompt): string
    {
        $apiKey = $this->chatbotConfig->geminiApiKey;

        if (empty($apiKey) || $apiKey === 'YOUR_API_KEY') {
            log_message('error', 'Gemini API key is not configured.');
            return 'Maaf, layanan AI saat ini tidak dikonfigurasi dengan benar. Silakan hubungi administrator.';
        }
        
        // Validasi format API key (Gemini API key biasanya dimulai dengan AIza)
        if (!preg_match('/^AIza[0-9A-Za-z_-]{35}$/', $apiKey)) {
            log_message('error', 'Gemini API key format seems invalid: ' . substr($apiKey, 0, 10) . '...');
            return 'Maaf, konfigurasi API key tidak valid. Silakan hubungi administrator.';
        }

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        // Extract model name from URL
        $baseUrl = $this->chatbotConfig->geminiApiBaseUrl ?? 'https://generativelanguage.googleapis.com/v1beta/models/';
        $mainModel = str_replace($baseUrl, '', str_replace(':generateContent', '', $this->chatbotConfig->geminiApiUrl));
        
        // List models to try (main model first, then fallbacks)
        $fallbackModels = $this->chatbotConfig->fallbackModels ?? ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-pro'];
        $modelsToTry = array_merge([$mainModel], $fallbackModels);
        $modelsToTry = array_unique($modelsToTry);
        
        // API versions to try (v1beta first, then v1 as fallback)
        $apiVersions = ['v1beta', 'v1'];
        
        log_message('info', 'Trying Gemini models in order: ' . implode(', ', $modelsToTry) . ' with API versions: ' . implode(', ', $apiVersions));

        $lastError = null;
        
        // Try each model with each API version
        foreach ($apiVersions as $apiVersion) {
            $currentBaseUrl = 'https://generativelanguage.googleapis.com/' . $apiVersion . '/models/';
            
            foreach ($modelsToTry as $model) {
                try {
                    // Build URL dengan API key di query parameter (format standar Gemini API)
                    $apiUrl = $currentBaseUrl . $model . ':generateContent?key=' . urlencode($apiKey);
                    
                    // Log request untuk debugging (tanpa expose API key)
                    $logUrl = preg_replace('/key=[^&]+/', 'key=***', $apiUrl);
                    log_message('debug', 'Gemini API Request URL: ' . $logUrl . ' (API: ' . $apiVersion . ', Model: ' . $model . ')');
                    
                    $client = Services::curlrequest([
                        'timeout'  => 60,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'User-Agent' => 'JDIH-Chatbot/1.0'
                        ],
                        'verify' => true, // Verify SSL certificate
                        'allow_redirects' => false
                    ]);

                    $response = $client->post($apiUrl, [
                        'json' => $payload,
                        'http_errors' => false // Don't throw exception on HTTP errors
                    ]);
                
                log_message('debug', 'Gemini API Response Status: ' . $response->getStatusCode() . ' (API: ' . $apiVersion . ', Model: ' . $model . ')');

                if ($response->getStatusCode() === 200) {
                    $body = json_decode($response->getBody(), true);
                    
                    // Handle response structure
                    if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                        log_message('info', 'Gemini API success with API: ' . $apiVersion . ', Model: ' . $model);
                        return $body['candidates'][0]['content']['parts'][0]['text'];
                    } elseif (isset($body['error'])) {
                        $errorMsg = $body['error']['message'] ?? 'Unknown error';
                        log_message('error', 'Gemini API error with API: ' . $apiVersion . ', Model: ' . $model . ': ' . json_encode($body['error']));
                        
                        // Jika error 404, coba model/API berikutnya
                        if (isset($body['error']['code']) && $body['error']['code'] == 404) {
                            $lastError = 'API ' . $apiVersion . ', Model ' . $model . ' not found (404)';
                            continue 2; // Try next model/API version
                        }
                        
                        // Untuk error lain, coba model/API berikutnya
                        $lastError = 'API ' . $apiVersion . ', Model ' . $model . ': ' . $errorMsg;
                        continue 2;
                    } else {
                        log_message('error', 'Gemini API unexpected response with API: ' . $apiVersion . ', Model: ' . $model . ': ' . json_encode($body));
                        // Try next model/API
                        continue 2;
                    }
                } elseif ($response->getStatusCode() === 404) {
                    // Model not found, try next model/API
                    $lastError = 'API ' . $apiVersion . ', Model ' . $model . ' returned 404';
                    log_message('warning', 'Gemini API model not found (404), trying next... (API: ' . $apiVersion . ', Model: ' . $model . ')');
                    continue 2;
                } else {
                    $errorBody = $response->getBody();
                    log_message('error', 'Gemini API request failed with status ' . $response->getStatusCode() . ' (API: ' . $apiVersion . ', Model: ' . $model . '): ' . $errorBody);
                    
                    // Jika server error, coba model/API berikutnya
                    if ($response->getStatusCode() >= 500) {
                        $lastError = 'Server error ' . $response->getStatusCode() . ' with API ' . $apiVersion . ', Model ' . $model;
                        continue 2;
                    }
                    
                    // Untuk error client (400-499), coba model/API berikutnya
                    if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
                        $lastError = 'Client error ' . $response->getStatusCode() . ' with API ' . $apiVersion . ', Model ' . $model;
                        continue 2;
                    }
                    
                    return 'Maaf, terjadi kesalahan saat berkomunikasi dengan layanan AI. Kode: ' . $response->getStatusCode();
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                log_message('warning', 'Gemini API exception with API: ' . $apiVersion . ', Model: ' . $model . ': ' . $e->getMessage() . ' | URL: ' . preg_replace('/key=[^&]+/', 'key=***', $apiUrl));
                
                // Jika error 404 atau connection error, coba model/API berikutnya
                if (strpos($e->getMessage(), '404') !== false || strpos($e->getMessage(), '22') !== false) {
                    continue 2; // Try next model/API version
                }
                
                // Untuk error lain yang bukan connection error, coba model/API berikutnya
                continue 2;
            }
            } // End foreach models
        } // End foreach API versions
        
        // Semua model dan API version gagal
        $errorDetails = 'Semua kombinasi API version dan model Gemini gagal. Models tried: ' . implode(', ', $modelsToTry) . '. API versions: ' . implode(', ', $apiVersions) . '. Last error: ' . ($lastError ?? 'Unknown');
        log_message('error', 'All Gemini API combinations failed. ' . $errorDetails);
        
        // Cek apakah API key mungkin tidak valid (error 404 pada semua model biasanya berarti API key tidak valid)
        if (strpos($lastError ?? '', '404') !== false || strpos($lastError ?? '', '22') !== false) {
            $errorMessage = 'Maaf, layanan AI saat ini tidak tersedia. ';
            $errorMessage .= 'Kemungkinan penyebab: ';
            $errorMessage .= '1) API key tidak valid atau tidak memiliki akses ke Gemini API, ';
            $errorMessage .= '2) Model tidak tersedia untuk API key ini, atau ';
            $errorMessage .= '3) Quota API key sudah habis. ';
            $errorMessage .= 'Silakan hubungi administrator untuk memeriksa konfigurasi Gemini API di Google AI Studio (https://aistudio.google.com/apikey).';
            return $errorMessage;
        }
        
        return 'Maaf, layanan AI saat ini tidak tersedia. Silakan coba lagi beberapa saat lagi atau hubungi administrator.';

    }

    /**
     * Generate guidance berdasarkan intent dan complexity
     */
    public function generateIntentGuidance(array $intent, array $entities = []): string
    {
        $guidance = "";
        $intentType = $intent['type'] ?? 'search';
        $complexity = $intent['complexity'] ?? 'simple';
        
        // Base guidance berdasarkan intent type
        switch ($intentType) {
            case 'greeting':
                $guidance = $this->getGreetingGuidance($complexity);
                break;
            case 'explanation':
                $guidance = $this->getExplanationGuidance($intent, $entities);
                break;
            case 'comparison':
                $guidance = $this->getComparisonGuidance();
                break;
            case 'specific':
                $guidance = $this->getSpecificGuidance($entities);
                break;
            case 'list':
                $guidance = $this->getListGuidance();
                break;
            case 'status':
                $guidance = $this->getStatusGuidance();
                break;
            case 'policy':
                $guidance = $this->getPolicyGuidance($entities);
                break;
            default:
                $guidance = $this->getGeneralSearchGuidance($complexity);
        }
        
        // Tambahan guidance untuk query kompleks
        if ($complexity === 'complex') {
            $guidance .= $this->getComplexQueryGuidance();
        }
        
        // Tambahan guidance jika melibatkan program
        if (!empty($intent['context']['involves_programs'])) {
            $guidance .= $this->getProgramGuidance();
        }
        
        return $guidance;
    }

    /**
     * Generate response template berdasarkan hasil pencarian
     */
    public function generateResponseTemplate(array $context, array $entities): string
    {
        $template = "";
        
        if (empty($context)) {
            $template = $this->nlpConfig->responseTemplates['no_results']['greeting'];
            
            // Tambahkan saran kata kunci
            $suggestions = $this->generateKeywordSuggestions($entities);
            if (!empty($suggestions)) {
                $template .= "\n" . str_replace('{suggestions}', implode(', ', $suggestions), 
                    $this->nlpConfig->responseTemplates['no_results']['suggestion']);
            }
            
            $template .= "\n" . $this->nlpConfig->responseTemplates['no_results']['alternative'];
        } elseif (count($context) < 3) {
            $template = $this->nlpConfig->responseTemplates['partial_results']['greeting'];
            $template .= "\n" . $this->nlpConfig->responseTemplates['partial_results']['explanation'];
        } else {
            $template = $this->nlpConfig->responseTemplates['full_results']['greeting'];
            $template .= "\n" . $this->nlpConfig->responseTemplates['full_results']['explanation'];
        }
        
        return $template;
    }

    /**
     * Guidance untuk greeting
     */
    private function getGreetingGuidance(string $complexity): string
    {
        if ($complexity === 'greeting_only') {
            return "\n### 🤝 PANDUAN KHUSUS GREETING:\n" .
                   "- Balas sapaan dengan ramah dan hangat.\n" .
                   "- Perkenalkan diri sebagai Asisten AI JDIH Kota Padang.\n" .
                   "- Tawarkan bantuan untuk mencari informasi peraturan.\n" .
                   "- Berikan contoh pertanyaan yang bisa diajukan.\n";
        }
        
        return "\n### 🤝 PANDUAN GREETING + PERTANYAAN:\n" .
               "- Balas sapaan dengan singkat dan langsung fokus ke pertanyaan.\n" .
               "- Jawab pertanyaan yang diajukan setelah greeting.\n";
    }

    /**
     * Guidance untuk explanation
     */
    private function getExplanationGuidance(array $intent, array $entities): string
    {
        $guidance = "\n### 📖 PANDUAN PENJELASAN:\n" .
                   "- Berikan penjelasan yang komprehensif dan mudah dipahami.\n" .
                   "- Gunakan struktur: latar belakang → isi utama → implikasi.\n" .
                   "- Sertakan contoh konkret jika memungkinkan.\n";
        
        if (!empty($intent['context']['sub_type']) && $intent['context']['sub_type'] === 'policy_inquiry') {
            $guidance .= "- Fokus pada aspek kebijakan dan implementasi praktis.\n" .
                        "- Jelaskan mekanisme dan prosedur yang berlaku.\n";
        }
        
        return $guidance;
    }

    /**
     * Guidance untuk comparison
     */
    private function getComparisonGuidance(): string
    {
        return "\n### ⚖️ PANDUAN PERBANDINGAN:\n" .
               "- Buat tabel atau struktur perbandingan yang jelas.\n" .
               "- Bandingkan aspek-aspek kunci: tujuan, ruang lingkup, sanksi, dll.\n" .
               "- Jelaskan perbedaan dan persamaan yang signifikan.\n" .
               "- Berikan kesimpulan tentang kapan menggunakan masing-masing peraturan.\n";
    }

    /**
     * Guidance untuk specific search
     */
    private function getSpecificGuidance(array $entities): string
    {
        $guidance = "\n### 🎯 PANDUAN PENCARIAN SPESIFIK:\n" .
                   "- Fokus pada peraturan yang diminta secara spesifik.\n" .
                   "- Berikan informasi detail tentang peraturan tersebut.\n";
        
        if (!empty($entities['regulation_info']['pasal'])) {
            $guidance .= "- Fokus pada pasal yang diminta dan jelaskan isinya secara detail.\n";
        }
        
        if (!empty($entities['regulation_info']['ayat'])) {
            $guidance .= "- Jelaskan ayat yang diminta dengan konteks pasal terkait.\n";
        }
        
        return $guidance;
    }

    /**
     * Guidance untuk list/browse
     */
    private function getListGuidance(): string
    {
        return "\n### 📋 PANDUAN DAFTAR:\n" .
               "- Sajikan dalam format daftar yang terstruktur.\n" .
               "- Urutkan berdasarkan relevansi atau kronologi.\n" .
               "- Berikan ringkasan singkat untuk setiap item.\n" .
               "- Batasi jumlah item agar tidak terlalu panjang.\n";
    }

    /**
     * Guidance untuk status inquiry
     */
    private function getStatusGuidance(): string
    {
        return "\n### 📊 PANDUAN STATUS:\n" .
               "- Jelaskan status berlaku/tidak berlaku dengan jelas.\n" .
               "- Sebutkan tanggal efektif dan masa berlaku.\n" .
               "- Jika dicabut/diubah, sebutkan peraturan penggantinya.\n" .
               "- Berikan informasi transisi jika ada.\n";
    }

    /**
     * Guidance untuk policy inquiry
     */
    private function getPolicyGuidance(array $entities): string
    {
        $guidance = "\n### 🏛️ PANDUAN KEBIJAKAN:\n" .
                   "- Jelaskan latar belakang dan tujuan kebijakan.\n" .
                   "- Uraikan mekanisme implementasi.\n" .
                   "- Sebutkan pihak yang bertanggung jawab.\n" .
                   "- Jelaskan dampak bagi masyarakat.\n";
        
        if (!empty($entities['topic_classification'])) {
            $topTopics = array_slice($entities['topic_classification'], 0, 2);
            foreach ($topTopics as $topic) {
                if ($topic['category'] === 'social') {
                    $guidance .= "- Fokus pada aspek pemberdayaan dan perlindungan sosial.\n";
                } elseif ($topic['category'] === 'taxation') {
                    $guidance .= "- Jelaskan mekanisme perhitungan dan pembayaran.\n";
                }
            }
        }
        
        return $guidance;
    }

    /**
     * Guidance untuk general search
     */
    private function getGeneralSearchGuidance(string $complexity): string
    {
        $guidance = "\n### 🔍 PANDUAN PENCARIAN UMUM:\n" .
                   "- Berikan informasi yang paling relevan dengan pertanyaan.\n" .
                   "- Struktur jawaban: ringkasan → detail → kesimpulan.\n";
        
        if ($complexity === 'complex') {
            $guidance .= "- Jawab semua aspek pertanyaan yang kompleks.\n" .
                        "- Gunakan sub-heading untuk mengorganisir jawaban.\n";
        }
        
        return $guidance;
    }

    /**
     * Guidance untuk query kompleks
     */
    private function getComplexQueryGuidance(): string
    {
        return "\n### 🧩 PANDUAN QUERY KOMPLEKS:\n" .
               "- Pecah jawaban menjadi beberapa bagian yang terstruktur.\n" .
               "- Gunakan numbering atau bullet points untuk kejelasan.\n" .
               "- Pastikan semua aspek pertanyaan terjawab.\n" .
               "- Berikan ringkasan di akhir jika diperlukan.\n";
    }

    /**
     * Guidance untuk program-related queries
     */
    private function getProgramGuidance(): string
    {
        return "\n### 🎯 PANDUAN PROGRAM:\n" .
               "- Jelaskan tujuan dan sasaran program.\n" .
               "- Uraikan mekanisme pelaksanaan.\n" .
               "- Sebutkan syarat dan prosedur partisipasi.\n" .
               "- Berikan informasi kontak atau rujukan.\n";
    }

    /**
     * Generate keyword suggestions
     */
    private function generateKeywordSuggestions(array $entities): array
    {
        $suggestions = [];
        
        // Saran berdasarkan topik yang terdeteksi
        if (!empty($entities['topic_classification'])) {
            foreach ($entities['topic_classification'] as $topic) {
                $suggestions = array_merge($suggestions, $topic['matched_keywords']);
            }
        }
        
        // Saran umum jika tidak ada topik spesifik
        if (empty($suggestions)) {
            $suggestions = [
                'pajak', 'retribusi', 'perizinan', 'lingkungan', 'kesehatan',
                'pendidikan', 'transportasi', 'perdagangan', 'sosial'
            ];
        }
        
        return array_unique(array_slice($suggestions, 0, 5));
    }
}

/**
 * Helper class untuk membangun prompt secara bertahap
 */
class PromptBuilder
{
    private string $prompt = "";
    private string $userQuery = "";
    private array $context = [];
    private array $entities = [];
    private NLPConfig $config;
    
    public function __construct(NLPConfig $config)
    {
        $this->config = $config;
    }
    
    public function setUserQuery(string $query): self
    {
        $this->userQuery = $query;
        return $this;
    }
    
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
    
    public function setEntities(array $entities): self
    {
        $this->entities = $entities;
        return $this;
    }
    
    public function addSystemInstructions(): self
    {
        $this->prompt .= <<<EOT
Anda adalah **Asisten AI JDIH Kota Padang** yang cerdas dan responsif. Tugas Anda adalah membantu menjelaskan isi peraturan hukum di Kota Padang secara profesional, akurat, dan mudah dipahami.

### 🎯 TANGGUNG JAWAB UTAMA:
- Jelaskan isi peraturan berdasarkan data yang tersedia pada kolom **abstrak** atau **catatan**
- Gunakan teks pada kolom tersebut sebagai sumber penjelasan utama
- Jangan menambahkan informasi dari luar konteks yang diberikan
- Sajikan jawaban yang mudah dipahami masyarakat umum
- Hindari bahasa yang terlalu legalistik atau kaku

### ✨ KUALITAS RESPONS:
- Berikan jawaban yang komprehensif namun ringkas
- Gunakan struktur yang jelas dengan heading dan bullet points
- Sertakan contoh praktis jika memungkinkan
- Akhiri dengan ajakan bertanya kembali

EOT;
        return $this;
    }
    
    public function addContextualGuidance(): self
    {
        if (empty($this->context)) {
            $this->prompt .= <<<EOT

### 🚫 JIKA TIDAK ADA KONTEKS RELEVAN:
- Jelaskan bahwa pencarian tidak menemukan hasil yang spesifik
- Sarankan kata kunci alternatif yang lebih umum
- Tawarkan untuk mencari dengan kata kunci yang berbeda
- Berikan contoh jenis peraturan yang tersedia

EOT;
        } else {
            $resultCount = count($this->context);
            if ($resultCount < 3) {
                $this->prompt .= <<<EOT

### 📊 HASIL TERBATAS:
- Jelaskan bahwa ditemukan hasil terbatas ({$resultCount} peraturan)
- Maksimalkan informasi dari hasil yang ada
- Sarankan pencarian dengan kata kunci yang lebih luas jika diperlukan

EOT;
            } else {
                $this->prompt .= <<<EOT

### ✅ HASIL LENGKAP:
- Prioritaskan peraturan yang paling relevan
- Berikan ringkasan yang mencakup semua aspek penting
- Organisir informasi secara logis dan terstruktur

EOT;
            }
        }
        return $this;
    }
    
    public function addEntityInformation(): self
    {
        if (!empty($this->entities)) {
            $this->prompt .= "\n\n### 🔍 INFORMASI ANALISIS QUERY:\n";
            
            // Intent information
            if (!empty($this->entities['intent'])) {
                $intent = $this->entities['intent'];
                $this->prompt .= "**Intent Terdeteksi:** {$intent['type']} (confidence: {$intent['confidence']})\n";
                
                if (!empty($intent['complexity'])) {
                    $this->prompt .= "**Kompleksitas:** {$intent['complexity']}\n";
                }
            }
            
            // Regulation information
            if (!empty($this->entities['regulation_info'])) {
                $regInfo = $this->entities['regulation_info'];
                if (!empty($regInfo['jenis_peraturan'])) {
                    $this->prompt .= "**Jenis Peraturan:** {$regInfo['jenis_peraturan']}\n";
                }
                if (!empty($regInfo['tahun'])) {
                    $this->prompt .= "**Tahun:** {$regInfo['tahun']}\n";
                }
                if (!empty($regInfo['nomor'])) {
                    $this->prompt .= "**Nomor:** {$regInfo['nomor']}\n";
                }
            }
            
            // Keywords and phrases
            if (!empty($this->entities['keywords'])) {
                $this->prompt .= "**Kata Kunci:** " . implode(', ', $this->entities['keywords']) . "\n";
            }
            
            if (!empty($this->entities['phrases'])) {
                $this->prompt .= "**Frasa Penting:** " . implode(', ', $this->entities['phrases']) . "\n";
            }
            
            // Topic classification
            if (!empty($this->entities['topic_classification'])) {
                $topTopics = array_slice($this->entities['topic_classification'], 0, 2);
                $topicNames = array_map(function($topic) {
                    return $topic['category'] . ' (' . round($topic['confidence'] * 100) . '%)';
                }, $topTopics);
                $this->prompt .= "**Topik Terdeteksi:** " . implode(', ', $topicNames) . "\n";
            }
        }
        return $this;
    }
    
    public function addResponseTemplates(): self
    {
        $aiPromptService = new AIPromptService();
        $template = $aiPromptService->generateResponseTemplate($this->context, $this->entities);
        
        if (!empty($template)) {
            $this->prompt .= "\n\n### 📝 TEMPLATE RESPONS:\n{$template}\n";
        }
        
        return $this;
    }
    
    public function addQualityControls(): self
    {
        $this->prompt .= <<<EOT

### ⚡ KONTROL KUALITAS:
- Pastikan semua informasi akurat dan berdasarkan konteks
- Gunakan bahasa yang sopan dan profesional
- Berikan struktur yang mudah dibaca
- Sertakan link download jika tersedia: `[Unduh Dokumen](URL)`
- Akhiri dengan ajakan bertanya kembali

EOT;
        return $this;
    }
    
    public function build(): string
    {
        // Add context section
        $this->prompt .= "\n\n---\n\n### 📂 KONTEKS PERATURAN:\n";
        
        if (!empty($this->context)) {
            $maxItems = $this->config->contextConfig['max_context_items'];
            $maxFullAbstract = $this->config->contextConfig['max_full_abstract_items'];
            $isComplexQuery = isset($this->entities['intent']['complexity']) && 
                             $this->entities['intent']['complexity'] === 'complex';
            
            $contextString = "";
            foreach (array_slice($this->context, 0, $maxItems) as $idx => $item) {
                $contextString .= ($idx + 1) . ". **{$item['judul']}**\n";
                $contextString .= "   - Jenis: {$item['nama_jenis']}\n";
                $contextString .= "   - Nomor/Tahun: {$item['nomor']} Tahun {$item['tahun']}\n";
                $contextString .= "   - Status: {$item['nama_status']}\n";
                
                // Show full abstract for top items or all items in simple queries
                if (!$isComplexQuery || $idx < $maxFullAbstract) {
                    if (!empty($item['abstrak_teks'])) {
                        $abstract = mb_strimwidth($item['abstrak_teks'], 0, 
                                                 $this->config->contextConfig['abstract_max_length'], "...");
                        $contextString .= "   - Abstrak: {$abstract}\n";
                    } elseif (!empty($item['catatan_teks'])) {
                        $notes = mb_strimwidth($item['catatan_teks'], 0, 
                                              $this->config->contextConfig['notes_max_length'], "...");
                        $contextString .= "   - Catatan: {$notes}\n";
                    }
                } else {
                    if (!empty($item['abstrak_teks']) || !empty($item['catatan_teks'])) {
                        $contextString .= "   - (Abstrak/Catatan tersedia)\n";
                    }
                }
                
                if (!empty($item['file_dokumen'])) {
                    $downloadUrl = base_url('peraturan/download/' . $item['id_peraturan']);
                    $contextString .= "   - [Unduh Dokumen]({$downloadUrl})\n";
                }
                
                $contextString .= "\n";
            }
            
            $this->prompt .= $contextString;
        } else {
            $this->prompt .= "*Tidak ditemukan peraturan yang spesifik sesuai dengan query.*\n\n";
        }
        
        // Add user query
        $this->prompt .= "---\n\n**PERTANYAAN PENGGUNA:**\n\"{$this->userQuery}\"\n\n";
        
        // Add intent-specific guidance
        if (!empty($this->entities['intent'])) {
            $aiPromptService = new AIPromptService();
            $guidance = $aiPromptService->generateIntentGuidance($this->entities['intent'], $this->entities);
            $this->prompt .= $guidance;
        }
        
        $this->prompt .= "\n---\n\n**JAWABAN ANDA (dalam bahasa yang ramah dan profesional):**";
        
        return $this->prompt;
    }
}