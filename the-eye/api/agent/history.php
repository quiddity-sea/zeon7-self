<?php
/**
 * Agent query history and annotation retrieval for authenticated users.
 */

require_once __DIR__ . '/../../../src/config/env.php';
require_once __DIR__ . '/../../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/services/EyeService.php';

header('Content-Type: application/json');

$auth = new AuthService();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$currentUser = $auth->getCurrentUser();
$userId = (int) $currentUser['user_id'];
$eyeService = new EyeService();

$type = $_GET['type'] ?? 'queries';

if ($type === 'annotations') {
    $annotations = $eyeService->getAnnotations($userId);
    echo json_encode(['annotations' => $annotations]);
} elseif ($type === 'views') {
    $views = $eyeService->getSavedViews($userId);
    echo json_encode(['views' => $views]);
} else {
    $limit = min((int) ($_GET['limit'] ?? 50), 200);
    $history = $eyeService->getAgentHistory($userId, $limit);
    echo json_encode(['history' => $history]);
}
