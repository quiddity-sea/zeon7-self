<?php
/**
 * Admin API: System Reset
 * Endpoint: POST /admin/api/system-reset.php
 * Requires: Admin Session
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/Services/SystemResetService.php';

class SystemResetController extends BaseController {
    
    public function handleRequest(): void {
        // 1. Auth Check (Critical)
        session_start();
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            $this->sendError('Unauthorized', 401);
        }
        
        // 2. Method Check
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        try {
            $service = new SystemResetService();
            $result = $service->resetAll();
            
            if ($result['success']) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'System reset successfully.',
                    'report' => $result['report']
                ]);
            } else {
                $this->sendError($result['error']);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new SystemResetController();
$controller->handleRequest();
