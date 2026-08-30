<?php
/**
 * API: Get All Users
 * Endpoint: GET /api/users/all.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/UserService.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';

class UserListController extends BaseController {
    private UserService $userService;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::handle();
        $this->userService = new UserService();
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }

        try {
            $users = $this->userService->getAll();

            // Decode last_10_ips for convenient frontend consumption
            foreach ($users as &$u) {
                $ips = is_array($u['last_10_ips'] ?? null) ? $u['last_10_ips'] : (json_decode($u['last_10_ips'] ?? '[]', true) ?: []);
                $u['ip_history'] = is_array($ips) ? $ips : [];
            }
            unset($u);

            $this->sendResponse([
                'success' => true,
                'count'   => count($users),
                'users'   => $users,
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new UserListController();
$controller->handleRequest();
