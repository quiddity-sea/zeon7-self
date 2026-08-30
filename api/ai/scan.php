<?php
/**
 * Zeon7 News Scanner Controller
 * Uses Gemini Grounding to fetch real-time news.
 */

require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';
AuthMiddleware::handle();
require_once __DIR__ . '/../../src/Services/ConfigService.php';
require_once __DIR__ . '/../../src/Services/GeminiService.php';

header('Content-Type: application/json');

try {
    // 1. Setup
    $config = new ConfigService();
    $apiKey = $config->get('ai_api_key');
    $model = $config->get('ai_model') ?: 'gemini-1.5-pro';
    
    // Force Gemini for Grounding (OpenRouter doesn't support Google Grounding natively in this way usually)
    $gemini = new GeminiService($apiKey, $model);

    // 2. Parameters (Hardcoded for now based on "Survival Monday" theme, or dynamic later)
    $topic = "Global Resilience and Technology";
    $angle = "Survival, Adaptation, and Future Tech";

    // 3. Execute Scan
    $rawResult = $gemini->scanNews($topic, $angle);

    // Clean Markdown if present (```json ... ```)
    $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($rawResult));
    $leads = json_decode($cleanJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Fallback: Return raw text if JSON parsing fails
        $leads = [['title' => 'Error Parsing Leads', 'summary' => $rawResult, 'angles' => [], 'sources' => []]];
    }

    echo json_encode([
        'success' => true,
        'leads' => $leads
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
