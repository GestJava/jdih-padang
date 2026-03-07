<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class IntentPatterns extends BaseConfig
{
    public array $greetings = [
        'halo', 'hai', 'hi', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam'
    ];

    public array $complexityIndicators = [
        'multiple_questions_regex' => '/\?/', // Pattern to count question marks
        'conjunctions_regex' => '/\b(dan|atau|serta|juga|selain itu|bagaimana.*dan|apa.*dan)\b/i',
        'length_threshold' => 15, // Word count
        'detailed_inquiry_regex' => '/\b(saya ingin tahu|tolong jelaskan|mohon informasi|bagaimana.*menanggapi|ada.*program)\b/i'
    ];

    public array $intentRegexes = [
        'explanation' => '/\b(apa|apakah|bagaimana|mengapa|kenapa|jelaskan|penjelasan|saya ingin tahu|tolong.*jelaskan|mohon.*informasi)\b/i',
        'explanation_policy_inquiry' => '/\b(apakah.*punya|ada.*aturan|bagaimana.*menanggapi|program.*pembinaan)\b/i',
        'comparison' => '/\b(bandingkan|beda|perbedaan|versus|vs|dibanding)\b/i',
        'specific' => '/\b(nomor|no\.?|tahun|\d{4})\b/i',
        'list' => '/\b(daftar|list|semua|seluruh|ada berapa)\b/i',
        'status' => '/\b(status|berlaku|masih berlaku|dicabut|dibatalkan)\b/i',
        'policy' => '/\b(program|kebijakan|pembinaan|penanganan|upaya|langkah|tindakan)\b/i',
    ];

    // public array $greetingNameExtractionRegex = '/(?:%s)\s+(?:saya\s+)?([a-zA-Z]+)/i'; // %s will be replaced by a greeting - Incorrect type
    public string $greetingNameExtractionRegex = '/(?:%s)\s+(?:saya\s+)?([a-zA-Z]+)/i'; // %s will be replaced by a greeting - Corrected type
}