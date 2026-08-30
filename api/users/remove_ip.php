<?php
/**
 * API: Remove IP from User Telemetry History
 * Endpoint: POST /api/users/remove_ip.php
 * Body: { user_id: int, ip: string } OR { user_id: int, action: 'clear_all' }
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/UserService.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class UserRemoveIpController extends BaseController {
    private UserService $userService;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::handle();
        CsrfMiddleware::handle();
        $this->userService = new UserService();
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }

        $data   = $this->getJsonBody();
        $userId = !empty($data['user_id']) ? (int) $data['user_id'] : null;
        $ip     = trim($data['ip'] ?? '');
        $action = trim($data['action'] ?? '');

        if (!$userId) {
            $this->sendError('User ID is required', 400);
        }

        try {
            $user = $this->userService->findById($userId);
            if (!$user) {
                $this->sendError('Operator not found', 404);
            }

            if ($action === 'clear_all') {
                $this->userService->clearAllIps($userId);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'All recorded IPs purged for operator',
                ]);
            } else {
                if (empty($ip)) {
                    $this->sendError('IP address to remove is required', 400);
                }

                $this->userService->removeIp($userId, $ip);

                // Fetch updated IP history
                $updatedUser = $this->userService->findById($userId);
                $ips = is_array($updatedUser['last_10_ips'] ?? null) ? $updatedUser['last_10_ips'] : (json_decode($updatedUser['last_10_ips'] ?? '[]', true) ?: []);

                $this->sendResponse([
                    'success'    => true,
                    'message'    => "IP {$ip} removed from telemetry records",
                    'ip_history' => is_array($ips) ? $ips : [],
                ]);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new UserRemoveIpController();
$controller->handleRequest();
