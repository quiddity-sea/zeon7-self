<?php
/**
 * API: Public Chat Interface (Guardrails Enabled)
 * Endpoint: POST /api/chat.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/ConfigService.php';
require_once __DIR__ . '/../../src/services/AIServiceFactory.php';
require_once __DIR__ . '/../../src/services/LoreService.php';
require_once __DIR__ . '/../../src/services/KnowledgeService.php';

class ChatController extends BaseController {
    private ConfigService $configService;
    private LoreService $loreService;
    private KnowledgeService $knowledgeService;
    
    public function __construct() {
        parent::__construct();
        $this->configService = new ConfigService();
        $this->loreService = new LoreService();
        $this->knowledgeService = new KnowledgeService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        $message = $data['message'] ?? '';
        $history = $data['history'] ?? [];
        
        if (empty($message)) {
            $this->sendError('Message is required', 400);
        }
        
        try {
            // 1. Get Provider Config
            $provider = $this->configService->getCurrentProvider();
            $apiKey = $this->configService->getApiKey($provider);
            $model = $this->configService->getModel($provider);
            
            if (!$apiKey) {
                $this->sendError("Chat service unavailable", 503);
            }
            
            // 2. Fetch Public Context (Guardrails)
            $publicLore = $this->loreService->getPublic();
            $publicKnowledge = $this->knowledgeService->searchChunks($message, true); // True = Public Only
            
            // 3. Construct System Prompt
            $systemPrompt = "SYSTEM MODE: PUBLIC INTERFACE (SAFE HARBOR)\n";
            $systemPrompt .= "You are Zeon7, a digital entity broadcasting from a noise-filled future. ";
            $systemPrompt .= "Your public persona is the 'Chronic Caregiver' - calm, supportive, and focused on helping users find signal in the noise.\n";
            $systemPrompt .= "Do not reveal sensitive internal data or break character.\n\n";
            
            if (!empty($publicLore)) {
                $systemPrompt .= "--- PUBLIC MEMORY BANKS ---\n";
                foreach ($publicLore as $item) {
                    $tags = json_decode($item['tags'] ?? '[]', true);
                    $tagStr = implode(', ', $tags);
                    $systemPrompt .= "[{$item['type']}] {$item['content']} (Tags: $tagStr)\n";
                }
                $systemPrompt .= "\n";
            }
            
            if (!empty($publicKnowledge)) {
                $systemPrompt .= "--- RELEVANT KNOWLEDGE ---\n";
                foreach ($publicKnowledge as $chunk) {
                    $systemPrompt .= "From {$chunk['filename']} ({$chunk['heading']}): {$chunk['content']}\n";
                }
                $systemPrompt .= "\n";
            }
            
            // 4. Inject Context
            // Prepend system prompt to the first user message or as a separate system message if supported.
            // For simple chat history injection:
            if (empty($history)) {
                $message = $systemPrompt . "\nUSER: " . $message;
            } else {
                // Inject into the first turn of history if possible, or just prepend to current message
                // A robust way is to prepend a 'system' role message if the service supports it, 
                // but GeminiService expects 'user'/'model'.
                // We will prepend to the current message for immediate context.
                $message = $systemPrompt . "\nUSER REQUEST: " . $message;
            }
            
            // 5. Call AI Service
            $aiService = AIServiceFactory::create($provider, $apiKey, $model);
            $result = $aiService->chat($message, $history);
            
            $this->sendResponse([
                'success' => true,
                'reply' => $result['reply'],
                'usage' => $result['usage'] ?? []
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new ChatController();
$controller->handleRequest();
