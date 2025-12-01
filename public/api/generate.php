<?php
/**
 * API: News Desk Generation (Context-Aware)
 * Endpoint: POST /api/generate.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/AiService.php';
require_once __DIR__ . '/../../src/services/InstructionService.php';
require_once __DIR__ . '/../../src/services/KnowledgeService.php';

class GenerateController extends BaseController {
    private AiService $aiService;
    private InstructionService $instructionService;
    private KnowledgeService $knowledgeService;
    
    public function __construct() {
        parent::__construct();
        $this->aiService = new AiService();
        $this->instructionService = new InstructionService();
        $this->knowledgeService = new KnowledgeService();
    }
    
    public function handleRequest(): void {
        // Handle Context Fetching (GET)
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'context') {
            $this->handleContextRequest();
            return;
        }

        // Handle Chat Generation (POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        $userMessage = $data['message'] ?? '';
        
        if (empty($userMessage)) {
            $this->sendError('Message is required', 400);
        }
        
        try {
            // 1. Build System Prompt from Database
            $systemPrompt = $this->buildSystemPrompt();
            
            // 2. Build Context (Day Theme, etc.)
            $context = $this->buildContext();
            
            // 3. Construct Messages
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt . "\n\nCURRENT CONTEXT:\n" . $context],
                ['role' => 'user', 'content' => $userMessage]
            ];
            
            // 4. Call AI
            $response = $this->aiService->chat($messages);
            
            // 5. Check for Generated Content (Heuristic: Look for "Headline:" or long text)
            $generatedContent = null;
            if (strlen($response) > 500 || str_contains($response, 'Headline:')) {
                $generatedContent = $response;
            }
            
            $this->sendResponse([
                'success' => true,
                'reply' => $response,
                'generated_content' => $generatedContent
            ]);
            
        } catch (Exception $e) {
            error_log("Generate API Error: " . $e->getMessage());
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function handleContextRequest(): void {
        try {
            $context = $this->buildContextArray();
            $this->sendResponse([
                'success' => true,
                'context' => $context
            ]);
        } catch (Exception $e) {
            error_log("Context API Error: " . $e->getMessage());
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    private function buildSystemPrompt(): string {
        $current = $this->instructionService->getCurrentVersion();
        if ($current && !empty($current['content'])) {
            return $current['content'];
        }
        return "You are Zeon7. (Fallback: System instructions not found in DB).";
    }
    
    private function buildContext(): string {
        $ctx = $this->buildContextArray();
        return "Day Theme: {$ctx['day_theme']}\nTone: {$ctx['tone']}\nDate: " . date('l, F j, Y');
    }

    private function buildContextArray(): array {
        // Determine Day of Week
        $dayOfWeek = date('l'); // e.g., "Friday"
        
        // Search Knowledge for Day Theme
        // We look for "From The Noise" doc and try to parse the day
        $chunks = $this->knowledgeService->searchChunks($dayOfWeek);
        
        $theme = "Unknown";
        $tone = "Standard";
        
        foreach ($chunks as $chunk) {
            if (stripos($chunk['filename'], 'From The Noise') !== false) {
                // Simple parsing logic (can be improved)
                if (preg_match('/Tagline:\s*\*(.*)\*/i', $chunk['content'], $matches)) {
                    $theme = trim($matches[1]);
                }
                if (preg_match('/Tone:\s*(.*)/i', $chunk['content'], $matches)) {
                    $tone = trim($matches[1]);
                }
                break; 
            }
        }
        
        return [
            'day_theme' => $theme,
            'tone' => $tone,
            'day' => $dayOfWeek
        ];
    }
}

$controller = new GenerateController();
$controller->handleRequest();
