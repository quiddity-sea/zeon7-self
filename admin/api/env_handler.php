<?php
/**
 * Admin API: Environment & Keys Handler
 * 
 * Endpoints:
 *   GET  /admin/api/env_handler.php -> Returns dynamic list of .env keys, values, and comments
 *   POST /admin/api/env_handler.php -> Updates and/or removes keys in .env, preserving comments
 * 
 * Security: Enforces Admin Authentication via AuthMiddleware
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';

class EnvHandlerController extends BaseController {

    private string $envPath;

    public function __construct() {
        // Resolve absolute path to .env in webroot
        $path = realpath(__DIR__ . '/../../.env');
        $this->envPath = $path ?: (__DIR__ . '/../../.env');
    }

    public function handleRequest(): void {
        // 1. Enforce Admin Authentication
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            $this->sendError('Unauthorized access', 401);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->handleGet();
        } elseif ($method === 'POST') {
            $this->handlePost();
        } else {
            $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * GET: Read .env and return structured keys, values, and contextual comments
     */
    private function handleGet(): void {
        if (!file_exists($this->envPath)) {
            $this->sendError('.env configuration file not found', 404);
            return;
        }

        $rawContent = file_get_contents($this->envPath);
        if ($rawContent === false) {
            $this->sendError('Unable to read .env file', 500);
            return;
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawContent);
        $entries = [];
        $dict = [];
        $currentComment = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $currentComment = '';
                continue;
            }

            // Capture comments immediately preceding keys
            if (str_starts_with($trimmed, '#')) {
                $cleanComment = trim(ltrim($trimmed, '# -'));
                if ($cleanComment !== '') {
                    $currentComment = $cleanComment;
                }
                continue;
            }

            // Parse valid KEY=VALUE assignments
            if (preg_match('/^([A-Za-z0-9_]+)=(.*)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = $matches[2]; // keep exact string (could have quotes or spaces)

                $entries[] = [
                    'key' => $key,
                    'value' => $value,
                    'comment' => $currentComment
                ];
                $dict[$key] = $value;
                $currentComment = '';
            }
        }

        $this->sendResponse([
            'success' => true,
            'entries' => $entries,
            'env' => $dict,
            'count' => count($entries),
            'writable' => is_writable($this->envPath)
        ]);
    }

    /**
     * POST: Update and delete keys while preserving all comments, blank lines, and structure
     */
    private function handlePost(): void {
        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        if (!$payload || !is_array($payload)) {
            $this->sendError('Invalid JSON payload', 400);
            return;
        }

        $submittedKeys = isset($payload['keys']) && is_array($payload['keys']) ? $payload['keys'] : [];
        $deletedKeys = isset($payload['deleted']) && is_array($payload['deleted']) ? $payload['deleted'] : [];

        if (!file_exists($this->envPath)) {
            $this->sendError('.env file not found', 404);
            return;
        }

        $rawContent = file_get_contents($this->envPath);
        if ($rawContent === false) {
            $this->sendError('Unable to read .env file for writing', 500);
            return;
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawContent);
        $updatedLines = [];
        $seenKeys = [];

        foreach ($lines as $line) {
            if (preg_match('/^([A-Za-z0-9_]+)=(.*)$/', $line, $matches)) {
                $k = trim($matches[1]);
                $seenKeys[$k] = true;

                // If key is marked for deletion, drop this line
                if (in_array($k, $deletedKeys, true)) {
                    continue;
                }

                // If key is present in submitted update, write new value
                if (array_key_exists($k, $submittedKeys)) {
                    $newVal = (string) $submittedKeys[$k];
                    $updatedLines[] = "{$k}={$newVal}";
                } else {
                    // Retain existing unmodified line
                    $updatedLines[] = $line;
                }
            } else {
                // Retain comments and blank lines intact
                $updatedLines[] = $line;
            }
        }

        // Check for any newly added keys not in original .env
        $newKeysToAdd = [];
        foreach ($submittedKeys as $k => $v) {
            $k = trim($k);
            if ($k !== '' && !isset($seenKeys[$k]) && !in_array($k, $deletedKeys, true)) {
                $newKeysToAdd[] = "{$k}=" . (string) $v;
            }
        }

        if (!empty($newKeysToAdd)) {
            $updatedLines[] = "";
            $updatedLines[] = "# Dynamic Keys registered via Admin UI";
            foreach ($newKeysToAdd as $newLine) {
                $updatedLines[] = $newLine;
            }
        }

        $finalContent = implode("\n", $updatedLines);
        if (!str_ends_with($finalContent, "\n")) {
            $finalContent .= "\n";
        }

        // Safety backup before overwriting
        @copy($this->envPath, $this->envPath . '.backup');

        $written = @file_put_contents($this->envPath, $finalContent, LOCK_EX);
        if ($written === false) {
            $this->sendError('Failed to write .env file. Check server write permissions.', 500);
            return;
        }

        $this->sendResponse([
            'success' => true,
            'message' => 'Environment configuration updated successfully',
            'backup_created' => file_exists($this->envPath . '.backup')
        ]);
    }
}

$controller = new EnvHandlerController();
$controller->handleRequest();
