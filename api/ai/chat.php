<?php
/**
 * Zeon7 Admin Chat Controller (The Cockpit / From the Noise)
 * Handles News Desk interactions with Tone Sliders and Context Injection.
 */

require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';
AuthMiddleware::handle();
require_once __DIR__ . '/../../src/Services/ConfigService.php';
require_once __DIR__ . '/../../src/Services/InstructionService.php';
require_once __DIR__ . '/../../src/Services/AIServiceFactory.php';
require_once __DIR__ . '/../../src/Services/CouncilClient.php';
require_once __DIR__ . '/../../src/Services/AgentContextService.php';

header('Content-Type: application/json');

try {
    // 1. Get Input
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $context = $input['context'] ?? []; // { tone: { satire: 20, hope: 80 } }
    $sessionId = $input['session_id'] ?? bin2hex(random_bytes(16));

    if (empty($message)) {
        throw new Exception('Message is required');
    }

    // 2. Setup Services
    $config = new ConfigService();
    $instructionService = new InstructionService();
    $councilClient = new CouncilClient();
    $agentContext = new AgentContextService();
    
    $provider = $config->getCurrentProvider();
    $apiKey   = $config->getApiKey($provider);
    $model    = $config->getCurrentModel();
    $think    = $config->getThinkMode();

    $aiService = AIServiceFactory::create($provider, $apiKey ?? '', $model, $think);

    // 3. Build Persona & Context from Council
    $basePersona = $instructionService->getCurrentContent();
    if (empty($basePersona)) {
        $basePersona = "You are Zeon7, Curator of the ForeverBox.";
    }
    
    // News Desk Mode Injection
    $adminContext = "\n\n[SYSTEM MODE: NEWS DESK COCKPIT / FROM THE NOISE]\n";
    if (isset($context['tone'])) {
        $satire = $context['tone']['satire'] ?? 50;
        $hope = $context['tone']['hope'] ?? 50;
        $adminContext .= "[TONE SETTINGS: Satire {$satire}%, Hope {$hope}%]\n";
        $adminContext .= "[INSTRUCTION: Adjust your output to match these tone percentages. High Satire = biting wit. High Hope = optimistic outlook.]\n";
    }

    $finalSystemPrompt = $basePersona . $adminContext;

    // 4. Log User Turn to Council
    $activeAgent = $agentContext->getAgentId();
    $councilClient->withAgent($activeAgent);
    if (($_ENV['CONVERSATION_BACKEND'] ?? 'local') === 'council' && $councilClient->isAvailable()) {
        try {
            $councilClient->appendMessage(
                sessionId:       $sessionId,
                role:            'user',
                content:         $message,
                metadata:        ['model' => $model, 'provider' => $provider, 'context' => $context],
                sourceInterface: 'from_the_noise'
            );
        } catch (\Throwable $e) {}
    }

    // 5. Execute Chat
    $response = $aiService->chat($message, [], $finalSystemPrompt);

    $tokensUsed = $response['usage']['total_tokens'] ?? null;

    // 6. Log Assistant Turn to Council
    if (($_ENV['CONVERSATION_BACKEND'] ?? 'local') === 'council' && $councilClient->isAvailable()) {
        try {
            $councilClient->appendMessage(
                sessionId:       $sessionId,
                role:            'assistant',
                content:         $response['reply'],
                metadata:        ['model' => $model, 'provider' => $provider, 'tokens' => $tokensUsed],
                sourceInterface: 'from_the_noise'
            );
        } catch (\Throwable $e) {}
    }

    echo json_encode([
        'success'    => true,
        'reply'      => $response['reply'],
        'usage'      => $response['usage'] ?? [],
        'session_id' => $sessionId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

