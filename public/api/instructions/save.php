<?php
/**
 * API: Save New Instruction Version
 */
require_once __DIR__ . '/../../../src/Middleware/AuthMiddleware.php';
AuthMiddleware::handle();

require_once __DIR__ . '/../../../src/Services/InstructionService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $content = $data['content'] ?? '';
    
    if (empty($content)) {
        throw new Exception("Content cannot be empty");
    }
    
    $service = new InstructionService();
    $newVersion = $service->createVersion($content, 'admin'); // TODO: Get real user from session
    
    echo json_encode([
        'success' => true,
        'version' => $newVersion,
        'message' => 'New version created successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
