<?php
// Konfigurasi Groq API
define('GROQ_API_KEY', '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'); // Ganti dengan API key Groq Anda
define('GROQ_MODEL', 'llama-3.3-70b-versatile'); // Model yang akan digunakan
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

// Fungsi untuk validasi API key
function validateGroqConfig()
{
    return !empty(GROQ_API_KEY) && str_starts_with(GROQ_API_KEY, 'gsk_');
}
