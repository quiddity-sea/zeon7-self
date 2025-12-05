<?php
/**
 * API: Trigger Instruction Ingestion
 */
require_once __DIR__ . '/../../../src/Middleware/AuthMiddleware.php';
AuthMiddleware::handle();

require_once __DIR__ . '/../../../src/Services/InstructionService.php';

header('Content-Type: application/json');

try {
    $service = new InstructionService();
    $instructionsPath = __DIR__ . '/../../../instructions';
    
    $result = $service->ingestFromFolder($instructionsPath);
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
