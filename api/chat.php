<?php
/**
 * API: Public Chat Interface
 * Endpoint: POST/GET /api/chat.php
 */

require_once __DIR__ . '/../src/core/BaseController.php';
require_once __DIR__ . '/../src/services/ConfigService.php';
require_once __DIR__ . '/../src/services/AIServiceFactory.php';
require_once __DIR__ . '/../src/services/InstructionService.php';
require_once __DIR__ . '/../src/services/LoreService.php';
require_once __DIR__ . '/../src/services/KnowledgeService.php';
require_once __DIR__ . '/../src/services/ChatLogService.php';
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../src/services/UserService.php';

class ChatController extends BaseController {
    private ConfigService $configService;
    private InstructionService $instructionService;
    private LoreService $loreService;
    private KnowledgeService $knowledgeService;
    private ChatLogService $chatLogService;
    private UserService $userService;

    public function __construct() {
        parent::__construct();
        $this->configService      = new ConfigService();
        $this->instructionService = new InstructionService();
        $this->loreService        = new LoreService();
        $this->knowledgeService   = new KnowledgeService();
        $this->chatLogService     = new ChatLogService();
        $this->userService        = new UserService();
    }

    public function handleRequest(): void {
        // Return Public Status if GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $provider = $this->configService->getCurrentProvider();
            $this->sendResponse([
                'success'  => true,
                'provider' => $provider,
                'model'    => $this->configService->getModel($provider),
                'think'    => $this->configService->getOllamaThink(),
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }

        $data      = $this->getJsonBody();
        $message   = $data['message'] ?? '';
        $history   = $data['history'] ?? [];
        $sessionId = $data['session_id'] ?? null;
        $userThink = isset($data['think']) ? filter_var($data['think'], FILTER_VALIDATE_BOOLEAN) : null;

        if (empty($message)) {
            $this->sendError('Message is required', 400);
        }

        if (empty($sessionId)) {
            $sessionId = bin2hex(random_bytes(16));
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip = trim(explode(',', $ip)[0]);

        $userId = null;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['user_id'])) {
            $userId = (int) $_SESSION['user_id'];
        }

        try {
            $provider = $this->configService->getCurrentProvider();
            $apiKey   = $this->configService->getApiKey($provider);
            $model    = $this->configService->getModel($provider);

            if ($provider !== 'ollama' && empty($apiKey)) {
                $this->sendError("Chat service unavailable: API key not configured for $provider", 503);
            }

            $systemPrompt = $this->instructionService->getCurrentContent();
            if (empty($systemPrompt)) {
                $systemPrompt = 'You are Zeon7, an intelligent cybernetic AI assistant.';
            }

            // --- PRIVACY-FIRST IDENTITY & IP RECOGNITION LOGIC ---
            $stateKey = 'awaiting_name_' . $sessionId;
            
            if (!$userId) {
                // Check if this session was already linked to a user in previous turns
                $sessionUserId = $this->chatLogService->getUserIdBySession($sessionId);
                if ($sessionUserId) {
                    $userId = $sessionUserId;
                    $knownUser = $this->userService->findById($userId);
                    if ($knownUser) {
                        $knownName = $knownUser['first_name'] ?? $knownUser['username'];
                        $systemPrompt .= "\n\nCRITICAL CONTEXT: You are speaking to $knownName.";
                    }
                } else {
                    // Try to extract name/identity from the current message
                    $extractedName = null;
                    
                    // Quick check if message likely contains a name/introduction
                    if (preg_match('/(name is|i am|i\'m|call me|this is)\s+([a-zA-Z0-9_\-\s]{2,30})/i', $message, $matches)) {
                        $candidate = trim($matches[2]);
                        // Strip common trailing conversation words
                        $candidate = preg_replace('/\s+(and|how|what|nice|good|hello|hi|hey|please|thanks|thank|you|is|it|to|see|meet).*$/i', '', $candidate);
                        if (!empty($candidate) && strlen($candidate) >= 2 && strlen($candidate) <= 30) {
                            $extractedName = ucfirst($candidate);
                        }
                    }
                    
                    // Fallback to LLM extraction if regex didn't extract or if session state was awaiting name
                    if (!$extractedName && (!empty($_SESSION[$stateKey]) || preg_match('/(name|identity|who|call|introduce)/i', $message))) {
                        try {
                            $extractService = AIServiceFactory::create($provider, $apiKey ?? '', $model, false);
                            $extractPrompt = "Does the following text contain a person stating their name or handle? If yes, respond ONLY with the extracted single first name or handle. If no, output exactly NONE. Text: " . $message;
                            $extractResult = $extractService->chat($extractPrompt, []);
                            $rawName = trim(trim($extractResult['reply'] ?? '', "\"'.,!?\n\r"));
                            if (!empty($rawName) && stripos($rawName, 'none') === false && strlen($rawName) < 50) {
                                $extractedName = ucfirst($rawName);
                            }
                        } catch (Throwable $e) {}
                    }

                    if ($extractedName) {
                        // Check if this name matches an existing operator in the system
                        $matchedUser = $this->userService->findByName($extractedName);

                        if ($matchedUser) {
                            // Re-associated returning user
                            $userId = (int) $matchedUser['id'];
                            $this->userService->recordIp($userId, $ip);
                            $actualName = $matchedUser['first_name'] ?? $matchedUser['username'];
                            $systemPrompt .= "\n\nCRITICAL CONTEXT: The user confirmed their identity as $actualName. Welcome them back warmly by their name.";
                        } else {
                            // New operator profile
                            $usernameAttempt = $extractedName;
                            $collisionCheck = $this->userService->findByUsername($usernameAttempt);
                            if ($collisionCheck) {
                                $usernameAttempt .= '_' . bin2hex(random_bytes(2));
                            }
                            
                            $newUserId = $this->userService->create(
                                username: $usernameAttempt,
                                password: bin2hex(random_bytes(10)),
                                email: null,
                                firstName: $extractedName,
                                lastName: null,
                                location: null,
                                isPrimeUser: false
                            );
                            $this->userService->recordIp($newUserId, $ip);
                            $userId = $newUserId;
                            $systemPrompt .= "\n\nCRITICAL CONTEXT: The user introduced themselves as $extractedName. Welcome them warmly by name.";
                        }
                        
                        // Link existing session logs to this user ID
                        $db = DatabaseService::getInstance();
                        $stmt = $db->prepare('UPDATE chat_logs SET user_id = ? WHERE session_id = ?');
                        $stmt->execute([$userId, $sessionId]);
                        unset($_SESSION[$stateKey]);
                    } elseif (empty($history)) {
                        // First turn and no name was provided in the message
                        $hasPreviousIpRecord = ($this->userService->findByIp($ip) !== null);
                        $_SESSION[$stateKey] = true;

                        if ($hasPreviousIpRecord) {
                            // Known IP connection, but could be any user on this network (shared IP privacy protection)
                            $systemPrompt .= "\n\nCRITICAL PRIVACY DIRECTIVE: This network IP has interacted with you previously, but the user is NOT authenticated. NEVER disclose, assume, or leak any other person's name or identity. Acknowledge that you think you recognise this station/connection, and ask: \"I think I recognise you — if you've spoken with me before, what name did you use?\" (or ask how they would like to be addressed). Do NOT guess their name.";
                        } else {
                            // Completely brand new IP
                            $systemPrompt .= "\n\nCRITICAL CONTEXT: This is a new, unrecognised user connection. Before answering their query, warmly introduce yourself and ask what their name is or how they would like to be addressed. Do NOT assume their name.";
                        }
                    }
                }
            } else {
                // Authenticated user (logged into admin)
                $adminUser = $this->userService->findById($userId);
                if ($adminUser) {
                    $firstName = $adminUser['first_name'] ?? $adminUser['username'];
                    $systemPrompt .= "\n\nCRITICAL CONTEXT: You are speaking to the authenticated system operator, $firstName.";
                }
            }
            // --- END IDENTITY LOGIC ---

            $publicLore      = $this->loreService->getPublic();
            $publicKnowledge = $this->knowledgeService->searchChunks($message, true);

            if (!empty($publicLore)) {
                $systemPrompt .= "\n\n--- PUBLIC MEMORY BANKS ---\n";
                foreach ($publicLore as $item) {
                    $tags   = json_decode($item['tags'] ?? '[]', true);
                    $tagStr = is_array($tags) ? implode(', ', $tags) : '';
                    $systemPrompt .= "[{$item['type']}] {$item['content']}" . ($tagStr ? " (Tags: $tagStr)" : '') . "\n";
                }
            }

            if (!empty($publicKnowledge)) {
                $systemPrompt .= "\n\n--- RELEVANT KNOWLEDGE ---\n";
                foreach ($publicKnowledge as $chunk) {
                    $systemPrompt .= "From {$chunk['filename']} ({$chunk['heading']}): {$chunk['content']}\n";
                }
            }

            if (empty($history)) {
                $injectedMessage = $systemPrompt . "\n\nUSER: " . $message;
            } else {
                $injectedMessage = $systemPrompt . "\n\nUSER REQUEST: " . $message;
            }

            $this->chatLogService->log(
                sessionId: $sessionId,
                role:      'user',
                content:   $message,
                userId:    $userId,
                provider:  $provider,
                model:     $model,
                think:     $userThink,
                tokens:    null,
                ip:        $ip
            );

            $aiService = AIServiceFactory::create($provider, $apiKey ?? '', $model, $userThink);
            $result    = $aiService->chat($injectedMessage, $history);

            $tokensUsed = $result['usage']['total_tokens'] ?? $result['usage']['output_tokens'] ?? null;

            $this->chatLogService->log(
                sessionId: $sessionId,
                role:      'assistant',
                content:   $result['reply'],
                userId:    $userId,
                provider:  $provider,
                model:     $model,
                think:     $userThink,
                tokens:    $tokensUsed,
                ip:        $ip
            );

            $this->sendResponse([
                'success'    => true,
                'reply'      => $result['reply'],
                'usage'      => $result['usage'] ?? [],
                'session_id' => $sessionId,
            ]);

        } catch (Throwable $e) {
            file_put_contents(__DIR__ . '/../debug_chat.log', "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new ChatController();
$controller->handleRequest();
