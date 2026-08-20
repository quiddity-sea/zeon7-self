<?php
/**
 * API: Upsert User (Create or Update)
 * Endpoint: POST /api/users/upsert.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/UserService.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class UserUpsertController extends BaseController {
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

        $data = $this->getJsonBody();

        $id          = !empty($data['id']) ? (int) $data['id'] : null;
        $username    = trim($data['username'] ?? '');
        $password    = $data['password'] ?? '';
        $email       = trim($data['email'] ?? '');
        $firstName   = trim($data['first_name'] ?? '');
        $lastName    = trim($data['last_name'] ?? '');
        $location    = trim($data['location'] ?? '');
        $isPrimeUser = !empty($data['is_prime_user']);

        if (empty($username)) {
            $this->sendError('Username is required', 400);
        }

        // Validate username formatting
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $username)) {
            $this->sendError('Username may only contain letters, numbers, hyphens, dots, and underscores', 400);
        }

        try {
            if ($id) {
                // Update existing user
                $existing = $this->userService->findById($id);
                if (!$existing) {
                    $this->sendError('User not found', 404);
                }

                // Check username uniqueness if changed
                if ($username !== $existing['username']) {
                    $byName = $this->userService->findByUsername($username);
                    if ($byName && (int)$byName['id'] !== $id) {
                        $this->sendError('Username already taken by another operator', 400);
                    }
                }

                // Check email uniqueness if set
                if (!empty($email) && $email !== ($existing['email'] ?? '')) {
                    $byEmail = $this->userService->findByEmail($email);
                    if ($byEmail && (int)$byEmail['id'] !== $id) {
                        $this->sendError('Email address already associated with another operator', 400);
                    }
                }

                $updateData = [
                    'username'      => $username,
                    'email'         => !empty($email) ? $email : null,
                    'first_name'    => !empty($firstName) ? $firstName : null,
                    'last_name'     => !empty($lastName) ? $lastName : null,
                    'location'      => !empty($location) ? $location : null,
                    'is_prime_user' => $isPrimeUser,
                ];

                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $this->sendError('Password must be at least 6 characters long', 400);
                    }
                    $updateData['password'] = $password;
                }

                $this->userService->updateUser($id, $updateData);

                $this->sendResponse([
                    'success' => true,
                    'id'      => $id,
                    'message' => 'Operator profile updated successfully',
                ]);
            } else {
                // Create new user
                if (empty($password)) {
                    $this->sendError('Password is required when creating a new operator', 400);
                }
                if (strlen($password) < 6) {
                    $this->sendError('Password must be at least 6 characters long', 400);
                }

                $byName = $this->userService->findByUsername($username);
                if ($byName) {
                    $this->sendError('Username already taken', 400);
                }

                if (!empty($email)) {
                    $byEmail = $this->userService->findByEmail($email);
                    if ($byEmail) {
                        $this->sendError('Email address already registered', 400);
                    }
                }

                $newId = $this->userService->create(
                    $username,
                    $password,
                    !empty($email) ? $email : null,
                    !empty($firstName) ? $firstName : null,
                    !empty($lastName) ? $lastName : null,
                    !empty($location) ? $location : null,
                    $isPrimeUser
                );

                $this->sendResponse([
                    'success' => true,
                    'id'      => $newId,
                    'message' => 'New operator registered successfully',
                ], 201);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new UserUpsertController();
$controller->handleRequest();
