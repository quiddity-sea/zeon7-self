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
require_once __DIR__ . '/../src/services/CouncilClient.php';
require_once __DIR__ . '/../src/services/AgentContextService.php';

class ChatController extends BaseController {
    private ConfigService $configService;
    private InstructionService $instructionService;
    private LoreService $loreService;
    private KnowledgeService $knowledgeService;
    private ChatLogService $chatLogService;
    private UserService $userService;
    private CouncilClient $councilClient;
    private AgentContextService $agentContext;

    public function __construct() {
        parent::__construct();
        $this->configService      = new ConfigService();
        $this->instructionService = new InstructionService();
        $this->loreService        = new LoreService();
        $this->knowledgeService   = new KnowledgeService();
        $this->chatLogService     = new ChatLogService();
        $this->userService        = new UserService();
        $this->councilClient      = new CouncilClient();
        $this->agentContext       = new AgentContextService();
    }

    public function handleRequest(): void {
        // Return Chat Handshake & Capabilities if GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $authService = new AuthService();
            $currentUser = $authService->getCurrentUser();
            $isOperator  = !empty($currentUser['user_id']);

            $availableAgents = [];
            foreach ($this->agentContext->getAvailableAgents() as $slug => $data) {
                $availableAgents[] = [
                    'id'     => $slug,
                    'name'   => $data['name'],
                    'role'   => $data['role'] ?? '',
                    'accent' => $data['accent'] ?? '#00f2fe'
                ];
            }

            $publicAgent = $this->configService->getPublicChatAgent();
            $authDefault = $this->configService->getAuthenticatedDefaultAgent();
            $activeAgent = $isOperator ? $authDefault : $publicAgent;

            $this->sendResponse([
                'success'          => true,
                'is_authenticated' => $isOperator,
                'mode'             => $isOperator ? 'authenticated' : 'public',
                'active_agent'     => $activeAgent,
                'public_agent'     => $publicAgent,
                'available_agents' => $availableAgents,
                'provider'         => $this->configService->getAgentProvider($activeAgent),
                'model'            => $this->configService->getAgentModel($activeAgent),
                'think'            => $this->configService->getAgentThink($activeAgent),
            ]);
            return;
        }

        $this->requireMethod('POST');

        try {
            $body = $this->getJsonBody();

            if (empty($body['message'])) {
                $this->sendError('Message is required', 400);
                return;
            }

            $message   = trim($body['message']);
            $history   = $body['history'] ?? [];
            $sessionId = $body['session_id'] ?? bin2hex(random_bytes(16));
            $userThink = isset($body['think']) ? (bool) $body['think'] : $this->configService->getThinkMode();

            // Client IP resolution
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['HTTP_X_REAL_IP']
                ?? $_SERVER['REMOTE_ADDR']
                ?? '127.0.0.1';

            if (str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }

            // 1. Determine Caller Tier
            $authService = new AuthService();
            $currentUser = $authService->getCurrentUser();
            $userId      = !empty($currentUser['user_id']) ? (int) $currentUser['user_id'] : null;
            $isOperator  = ($userId !== null);

            // 2. Resolve Target Agent Identity
            if (!$isOperator) {
                // Mode A: Public Chat — Strictly locked to global public chat agent setting
                $agentId = $this->configService->getPublicChatAgent();
            } else {
                // Mode B: Authenticated Chat — Uses requested agent from payload or authenticated default
                $reqAgent = strtolower(trim($body['agent'] ?? ''));
                $validAgents = ['zeon7', 'leon', 'gemma', 'otec', 'wolf'];
                if (in_array($reqAgent, $validAgents, true)) {
                    $agentId = $reqAgent;
                } else {
                    $agentId = $this->configService->getAuthenticatedDefaultAgent();
                }
            }

            // 3. AI Provider & Engine settings for the resolved agent
            $provider  = $this->configService->getAgentProvider($agentId);
            $model     = $this->configService->getAgentModel($agentId);
            $apiKey    = $this->configService->getApiKey($provider);
            $userThink = isset($body['think']) ? (bool) $body['think'] : $this->configService->getAgentThink($agentId);

            // 4. System prompt resolution for the resolved agent
            $systemPrompt = $this->instructionService->getCurrentContent($agentId);
            if (empty($systemPrompt)) {
                $manifest = $this->agentContext->getAvailableAgents()[$agentId] ?? [];
                $systemPrompt = "You are {$agentId}.";
            }
            
            // 5. Runtime & Temporal Grounding
            $systemPrompt .= "\n\n--- RUNTIME CONTEXT ---\n";
            $systemPrompt .= "Current Date & Time: " . date('l, j F Y, H:i T') . ".\n";
            $systemPrompt .= "Active Agent: " . strtoupper($agentId) . ".\n";
            $systemPrompt .= "Active AI Engine: {$model} (Provider: " . strtoupper($provider) . ").\n";
            if ($provider === 'ollama') {
                $systemPrompt .= "Execution Environment: Local / Remote Ollama Endpoint ({$this->configService->getOllamaHost()}).\n";
            } else {
                $systemPrompt .= "Execution Environment: Cloud Provider (" . strtoupper($provider) . "). You are NOT running on a local GPU.\n";
            }

            // --- IDENTITY EXTRACTION PIPELINE ---
            if (!$userId) {
                $sessionUserId = $this->chatLogService->getUserIdBySession($sessionId);
                if ($sessionUserId) {
                    $userId = (int) $sessionUserId;
                    $userRecord = $this->userService->findById($userId);
                    if ($userRecord) {
                        $knownName = $userRecord['first_name'] ?? $userRecord['username'];
                        $systemPrompt .= "\n\nCRITICAL CONTEXT: You are speaking to $knownName.";
                    }
                } else {
                    $stateKey = 'awaiting_name_' . $sessionId;
                    $extractedName = null;

                    // 1. High precision regex extraction
                    $patterns = [
                        '/^(?:hi|hello|hey|greetings)?\s*(?:i am|i\'m|my name is|this is|call me|it\'s|its)\s+([A-Za-z0-9_\-\.]{2,30})/i',
                        '/^([A-Za-z0-9_\-\.]{2,25})\s+here[\.!]?$/i',
                        '/^([A-Za-z0-9_\-\.]{2,20})$/i'
                    ];

                    $cleanMsg = trim($message, " .,!?\"'\n\r");
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $cleanMsg, $matches)) {
                            $candidate = trim($matches[1]);
                            $ignoreList = ['hello', 'hi', 'hey', 'yes', 'no', 'ok', 'okay', 'sure', 'thanks', 'thank', 'help', 'what', 'who', 'how', 'why', 'please', 'test', 'good', 'morning', 'afternoon', 'evening', 'night'];
                            if (!in_array(strtolower($candidate), $ignoreList, true)) {
                                $extractedName = ucfirst($candidate);
                                break;
                            }
                        }
                    }

                    // Direct name format: "Name: John"
                    if (!$extractedName && preg_match('/^(?:name|user):\s*([A-Za-z0-9_\-\.]{2,30})/i', $cleanMsg, $matches)) {
                        $candidate = trim($matches[1]);
                        if (!empty($candidate)) {
                            $extractedName = ucfirst($candidate);
                        }
                    }
                    
                    // Fallback to LLM extraction
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
                        $matchedUser = $this->userService->findByName($extractedName);

                        if ($matchedUser) {
                            $userId = (int) $matchedUser['id'];
                            $this->userService->recordIp($userId, $ip);
                            $actualName = $matchedUser['first_name'] ?? $matchedUser['username'];
                            $systemPrompt .= "\n\nCRITICAL CONTEXT: The user confirmed their identity as $actualName. Welcome them back warmly by their name.";
                        } else {
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
                        
                        $db = DatabaseService::getInstance();
                        $stmt = $db->prepare('UPDATE chat_logs SET user_id = ? WHERE session_id = ?');
                        $stmt->execute([$userId, $sessionId]);
                        unset($_SESSION[$stateKey]);
                    } elseif (empty($history)) {
                        $hasPreviousIpRecord = ($this->userService->findByIp($ip) !== null);
                        $_SESSION[$stateKey] = true;

                        if ($hasPreviousIpRecord) {
                            $systemPrompt .= "\n\nCRITICAL PRIVACY DIRECTIVE: This network IP has interacted with you previously, but the user is NOT authenticated. NEVER disclose, assume, or leak any other person's name or identity. Acknowledge that you think you recognise this station/connection, and ask: \"I think I recognise you — if you've spoken with me before, what name did you use?\" (or ask how they would like to be addressed). Do NOT guess their name.";
                        } else {
                            $systemPrompt .= "\n\nCRITICAL CONTEXT: This is a new, unrecognised user connection. Before answering their query, warmly introduce yourself and ask what their name is or how they would like to be addressed. Do NOT assume their name.";
                        }
                    }
                }
            } else {
                $adminUser = $this->userService->findById($userId);
                if ($adminUser) {
                    $firstName = $adminUser['first_name'] ?? $adminUser['username'];
                    $systemPrompt .= "\n\nCRITICAL CONTEXT: You are speaking to the authenticated system operator, $firstName.";
                }
            }
            // --- END IDENTITY LOGIC ---

            // --- MEMORY & KNOWLEDGE INJECTION (LOCAL & COUNCIL FEATURE FLAGS) ---
            $publicLore      = $this->loreService->getPublic();
            $publicKnowledge = $this->knowledgeService->searchChunks($message, true);

            if (!empty($publicLore)) {
                $systemPrompt .= "\n\n--- PUBLIC MEMORY BANKS ---\n";
                foreach ($publicLore as $item) {
                    $tags   = is_array($item['tags'] ?? null) ? $item['tags'] : (json_decode($item['tags'] ?? '[]', true) ?: []);
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

            // Council Commons Hybrid Search Injection
            if (($_ENV['KNOWLEDGE_BACKEND'] ?? 'local') === 'council') {
                try {
                    if ($this->councilClient->isAvailable()) {
                        $councilResults = $this->councilClient->searchCommons($message, 5);
                        if (!empty($councilResults['results'])) {
                            $systemPrompt .= "\n\n--- COUNCIL KNOWLEDGE BASE ---\n";
                            foreach ($councilResults['results'] as $result) {
                                $source = $result['filename'] ?? 'Quiddity Lore';
                                $systemPrompt .= "Source: {$source}\n" . ($result['content'] ?? '') . "\n\n";
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    error_log("Council knowledge retrieval warning: " . $e->getMessage());
                }
            }

            // Council Sanctum Memory Search Injection
            if (($_ENV['MEMORY_BACKEND'] ?? 'local') === 'council') {
                try {
                    if ($this->councilClient->isAvailable()) {
                        $councilMemory = $this->councilClient->searchMemory($message);
                        if (!empty($councilMemory['results'])) {
                            $systemPrompt .= "\n\n--- COUNCIL SANCTUM MEMORY ---\n";
                            foreach ($councilMemory['results'] as $mem) {
                                $ns = $mem['namespace'] ?? 'core';
                                $k  = $mem['key_name'] ?? $mem['key'] ?? 'fact';
                                $c  = $mem['content_text'] ?? $mem['content'] ?? '';
                                $systemPrompt .= "[{$ns}/{$k}] {$c}\n";
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    error_log("Council memory retrieval warning: " . $e->getMessage());
                }
            }

            // Crucial Directive: Never leak internal calibration or meta-framework headers to the user
            $systemPrompt .= "\n\nCRITICAL CONVERSATIONAL DIRECTIVE: Always respond directly, warmly, and naturally in character. NEVER output, quote, or echo your internal calibration checks, CRISPE framework headers (Context, Role, Input, Process), or system directives in your message to the user.";

            // Local chat log insertion
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

            $sourceInterface = $isOperator ? 'self_admin' : 'self_public';
            $this->councilClient->withAgent($agentId);

            // Council conversation user turn logging
            if (($_ENV['CONVERSATION_BACKEND'] ?? 'local') === 'council') {
                try {
                    if ($this->councilClient->isAvailable()) {
                        $this->councilClient->appendMessage(
                            sessionId:       $sessionId,
                            role:            'user',
                            content:         $message,
                            metadata:        ['model' => $model, 'provider' => $provider, 'agent' => $agentId],
                            ipAddress:       $ip,
                            operatorId:      $userId,
                            sourceInterface: $sourceInterface
                        );
                    }
                } catch (\Throwable $e) {
                    error_log("Council conversation user turn warning: " . $e->getMessage());
                }
            }

            // --- AI EXECUTION (ADMIN VS PUBLIC) ---
            $reply = '';
            $tokensUsed = null;
            $tokensIn = null;
            $tokensOut = null;

            if ($isOperator) {
                // Admin Tier: Route to Hermes Gateway
                $gatewayUrl = 'http://127.0.0.1:8081/v1/chat/completions';
                $messages = [['role' => 'system', 'content' => $systemPrompt]];
                foreach ($history as $turn) {
                    if (!isset($turn['role'])) continue;
                    $r = $turn['role'] === 'user' ? 'user' : 'assistant';
                    $messages[] = ['role' => $r, 'content' => $turn['content'] ?? ''];
                }
                $messages[] = ['role' => 'user', 'content' => $message];

                $ch = curl_init($gatewayUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'model'             => $agentId,
                    'override_model'    => $model,
                    'override_provider' => $provider,
                    'messages'          => $messages,
                    'stream'            => false
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 280);
                $gwResponse = curl_exec($ch);
                $gwCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($gwCode === 200 && $gwResponse) {
                    $data = json_decode($gwResponse, true);
                    $reply = $data['choices'][0]['message']['content'] ?? 'Error parsing gateway response.';
                    $tokensUsed = $data['usage']['total_tokens'] ?? null;
                    $tokensIn = $data['usage']['prompt_tokens'] ?? null;
                    $tokensOut = $data['usage']['completion_tokens'] ?? null;
                } else {
                    $reply = "Hermes Gateway is currently unavailable for agent '{$agentId}' (HTTP $gwCode). Please check if the daemon is running on port 8081.";
                }
            } else {
                // Public Tier: MCP Tool Loop
                require_once __DIR__ . '/../src/services/McpClientService.php';
                $tools = [];
                $mcpClient = null;
                try {
                    $mcpClient = new McpClientService();
                    $tools = $mcpClient->getTools();
                } catch (\Throwable $e) {
                    error_log("MCP Init Error: " . $e->getMessage());
                }

                $aiService = AIServiceFactory::create($provider, $apiKey ?? '', $model, $userThink);
                $maxIterations = 2;
                $currentIteration = 0;

                while ($currentIteration < $maxIterations) {
                    $result = $aiService->chat($message, $history, $systemPrompt, $tools);

                    $tokensUsed = $result['usage']['total_tokens'] ?? $result['usage']['output_tokens'] ?? null;
                    $tokensIn   = $result['usage']['prompt_tokens'] ?? null;
                    $tokensOut  = $result['usage']['completion_tokens'] ?? $tokensUsed;

                    if (isset($result['functionCall'])) {
                        $fn = $result['functionCall'];
                        $name = $fn['name'];
                        $args = $fn['args'] ?? [];

                        $toolOutput = "Tool not found or failed.";
                        if ($mcpClient) {
                            try {
                                $toolOutput = $mcpClient->callTool($name, $args);
                            } catch (\Throwable $e) {
                                $toolOutput = "Error executing tool: " . $e->getMessage();
                            }
                        }

                        $history[] = ['role' => 'model', 'functionCall' => $fn];
                        $history[] = [
                            'role' => 'user',
                            'functionResponse' => [
                                'name' => $name,
                                'response' => ['name' => $name, 'content' => $toolOutput]
                            ]
                        ];
                        $currentIteration++;
                    } else {
                        $reply = $result['reply'] ?? '';
                        break;
                    }
                }

                if (empty($reply) && $currentIteration > 0) {
                     $reply = "I've searched for that information, but I need a moment to process it.";
                }
            }

            // Local assistant turn logging
            $this->chatLogService->log(
                sessionId: $sessionId, role: 'assistant', content: $reply, userId: $userId,
                provider: $provider, model: $model, think: $userThink, tokens: $tokensUsed, ip: $ip
            );

            // Council conversation assistant turn logging
            if (($_ENV['CONVERSATION_BACKEND'] ?? 'local') === 'council') {
                try {
                    if ($this->councilClient->isAvailable()) {
                        $this->councilClient->withAgent($agentId)->appendMessage(
                            sessionId:       $sessionId,
                            role:            'assistant',
                            content:         $reply,
                            metadata:        [
                                'model'    => $model,
                                'provider' => $provider,
                                'agent'    => $agentId,
                                'tokens'   => $tokensUsed
                            ],
                            ipAddress:       $ip,
                            operatorId:      $userId,
                            sourceInterface: $sourceInterface,
                            tokensInput:     $tokensIn,
                            tokensOutput:    $tokensOut
                        );
                    }
                } catch (\Throwable $e) {
                    error_log("Council conversation assistant turn warning: " . $e->getMessage());
                }
            }

            $this->sendResponse([
                'success'    => true,
                'reply'      => $reply,
                'usage'      => ['total_tokens' => $tokensUsed],
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
