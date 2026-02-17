<?php
/**
 * Zeon7 Admin Chat Controller (The Cockpit)
 * Handles News Desk interactions with Tone Sliders and Context Injection.
 */

require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';
AuthMiddleware::handle();
require_once __DIR__ . '/../../src/Services/ConfigService.php';
require_once __DIR__ . '/../../src/Services/InstructionService.php';
require_once __DIR__ . '/../../src/Services/GeminiService.php';
require_once __DIR__ . '/../../src/Services/OpenRouterService.php';

header('Content-Type: application/json');

try {
    // 1. Get Input
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $context = $input['context'] ?? []; // { tone: { satire: 20, hope: 80 } }

    if (empty($message)) {
        throw new Exception('Message is required');
    }

    // 2. Setup Services
    $config = new ConfigService();
    $instructionService = new InstructionService();
    
    $provider = $config->get('ai_provider', 'gemini');
    $apiKey = $config->get('ai_api_key');
    $model = $config->get('ai_model');

    // Factory Logic
    if ($provider === 'openrouter') {
        $aiService = new OpenRouterService($apiKey, $model ?: 'openai/gpt-4');
    } else {
        $aiService = new GeminiService($apiKey, $model ?: 'gemini-1.5-pro');
    }

    // 3. Build Persona & Context
    $basePersona = $instructionService->getCurrentInstruction();
    
    // Admin Mode Injection
    $adminContext = "\n\n[SYSTEM MODE: NEWS DESK COCKPIT]\n";
    $adminContext .= "[CURRENT DAY THEME: Survival Monday]\n"; // TODO: Fetch real theme from DB
    
    if (isset($context['tone'])) {
        $satire = $context['tone']['satire'] ?? 50;
        $hope = $context['tone']['hope'] ?? 50;
        $adminContext .= "[TONE SETTINGS: Satire {$satire}%, Hope {$hope}%]\n";
        $adminContext .= "[INSTRUCTION: Adjust your output to match these tone percentages. High Satire = biting wit. High Hope = optimistic outlook.]\n";
    }

    $finalSystemPrompt = $basePersona . $adminContext;

    // 4. Execute Chat
    // Note: We are passing the system prompt as the first history item for now
    // A more robust history management would go here
    $history = [
        ['role' => 'user', 'content' => $finalSystemPrompt . "\n\nUser Input: " . $message]
    ];

    $response = $aiService->chat($message, $history);

    echo json_encode([
        'success' => true,
        'reply' => $response['reply'],
        'usage' => $response['usage']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
