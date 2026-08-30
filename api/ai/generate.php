<?php
/**
 * API: News Desk Generation (Context-Aware)
 * Endpoint: POST /api/generate.php
 */

require_once __DIR__ . '/../../src/Core/BaseController.php';
require_once __DIR__ . '/../../src/Services/AiService.php';
require_once __DIR__ . '/../../src/Services/InstructionService.php';
require_once __DIR__ . '/../../src/Services/KnowledgeService.php';

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
            
            // 3. Extract Leads & Direction
            $leads = $data['leads'] ?? [];
            $direction = $data['direction'] ?? '';
            
            // 4. Construct Comprehensive Prompt
            $userPrompt = "Generate a Full Content Suite based on the following News Leads and Direction.\n\n";
            $userPrompt .= "DIRECTION: $direction\n\n";
            $userPrompt .= "LEADS:\n" . json_encode($leads) . "\n\n";
            $userPrompt .= "REQUIREMENTS:\n";
            $userPrompt .= "1. Blog Post (1500-2500 words) - Deep dive, emotional, thematic.\n";
            $userPrompt .= "2. Facebook Post (600-850 words) - Narrative summary.\n";
            $userPrompt .= "3. Instagram Post (1800-2000 chars) - Visual hook.\n";
            $userPrompt .= "4. X/Twitter Post (150-250 chars) - Punchy teaser.\n";
            $userPrompt .= "5. LinkedIn Post (350-650 words) - Professional framing.\n";
            $userPrompt .= "6. Image Prompts (2 concepts: Landscape & Portrait).\n\n";
            $userPrompt .= "OUTPUT FORMAT: Return a JSON object with a 'posts' array. Each post object must have: 'type' (string), 'title' (string), 'content' (string).";

            // 5. Construct Messages
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt . "\n\nCURRENT CONTEXT:\n" . $context],
                ['role' => 'user', 'content' => $userPrompt]
            ];
            
            // 6. Call AI
            $response = $this->aiService->chat($messages);
            
            // 7. Parse Response
            $cleanJson = preg_replace('/^```json\s*|\s*```$/', '', trim($response));
            $suite = json_decode($cleanJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Fallback if AI fails to return JSON
                $suite = ['posts' => [['type' => 'Error', 'title' => 'Generation Error', 'content' => $response]]];
            }
            
            $this->sendResponse([
                'success' => true,
                'posts' => $suite['posts'] ?? []
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
    
    private function buildContextArray(): array {
        // 1. Fetch Daily Context from DB (The "Master Schedule")
        $dayName = date('l');
        $sql = "SELECT theme, tone, tagline FROM daily_context WHERE day_name = ?";
        $daily = $this->fetchOne($sql, [$dayName]);
        
        $theme = $daily['theme'] ?? "Unknown Theme";
        $tone = $daily['tone'] ?? "Standard";
        $tagline = $daily['tagline'] ?? "";

        // 2. Fetch Global Lore (Public Memory)
        // In a full implementation, we would filter by user_id here too
        $sqlLore = "SELECT content FROM lore WHERE is_public = 1 ORDER BY created_at DESC LIMIT 5";
        $loreEntries = $this->fetchAll($sqlLore);
        $loreText = "";
        foreach ($loreEntries as $entry) {
            $loreText .= "- " . $entry['content'] . "\n";
        }
        
        return [
            'day_theme' => $theme,
            'tone' => $tone,
            'tagline' => $tagline,
            'lore' => $loreText,
            'day' => $dayName
        ];
    }

    private function buildContext(): string {
        $ctx = $this->buildContextArray();
        $text = "Date: " . date('l, F j, Y') . "\n";
        $text .= "Day Theme: {$ctx['day_theme']}\n";
        $text .= "Tagline: {$ctx['tagline']}\n";
        $text .= "Tone: {$ctx['tone']}\n\n";
        
        if (!empty($ctx['lore'])) {
            $text .= "GLOBAL MEMORY (LORE):\n{$ctx['lore']}";
        }
        
        return $text;
    }
}

$controller = new GenerateController();
$controller->handleRequest();
