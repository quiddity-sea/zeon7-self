<?php
/**
 * API: Admin Chat Logs Fetcher
 * Endpoint: GET /admin/api/chat_logs.php?session_id=...
 */

require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/ChatLogService.php';

// Auth checks
AuthMiddleware::enforcePageAuth();

class AdminChatLogsController extends BaseController {
    private ChatLogService $chatLogService;

    public function __construct() {
        parent::__construct();
        $this->chatLogService = new ChatLogService();
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }

        $sessionId = $_GET['session_id'] ?? '';
        if (empty($sessionId)) {
            $this->sendError('Session ID is required', 400);
        }

        $logs = $this->chatLogService->getSession($sessionId);
        
        $this->sendResponse([
            'success' => true,
            'session_id' => $sessionId,
            'logs' => $logs
        ]);
    }
}

$controller = new AdminChatLogsController();
$controller->handleRequest();
