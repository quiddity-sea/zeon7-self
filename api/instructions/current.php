<?php
/**
 * API: Get Current Instruction Version
 */
require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';
AuthMiddleware::handle();

require_once __DIR__ . '/../../src/Services/InstructionService.php';

header('Content-Type: application/json');

try {
    $service = new InstructionService();
    $current = $service->getCurrentVersion();
    
    echo json_encode([
        'success' => true,
        'version' => $current
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
