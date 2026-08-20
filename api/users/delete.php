<?php
/**
 * API: Delete User
 * Endpoint: DELETE /api/users/delete.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/UserService.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class UserDeleteController extends BaseController {
    private UserService $userService;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::handle();
        CsrfMiddleware::handle();
        $this->userService = new UserService();
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendError('Method not allowed', 405);
        }

        $id = !empty($_GET['id']) ? (int) $_GET['id'] : null;

        if (!$id) {
            $this->sendError('User ID is required', 400);
        }

        // Prevent admin from deleting their own currently logged-in account
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        if ($currentUserId === $id) {
            $this->sendError('Cannot delete your own active operator account', 403);
        }

        try {
            $user = $this->userService->findById($id);
            if (!$user) {
                $this->sendError('Operator not found', 404);
            }

            $success = $this->userService->deleteUser($id);

            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Operator profile deleted successfully',
                ]);
            } else {
                $this->sendError('Failed to delete operator', 500);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new UserDeleteController();
$controller->handleRequest();
