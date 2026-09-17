<?php
/**
 * Agent command endpoint for The Eye.
 * Receives commands from the agent bar, routes to Otec via the existing chat system,
 * returns reply + optional eye_command JSON for globe control.
 *
 * Auth-gated: returns 401 for unauthenticated users.
 */

$selfRoot = file_exists(__DIR__ . '/../../../src/config/env.php')
    ? dirname(__DIR__, 3)
    : '/var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk';

require_once $selfRoot . '/src/config/env.php';
require_once $selfRoot . '/src/services/AuthService.php';
require_once __DIR__ . '/../../src/services/EyeService.php';

header('Content-Type: application/json');

// Auth check
$auth = new AuthService();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required for agent commands']);
    exit;
}

$currentUser = $auth->getCurrentUser();
$userId = (int) $currentUser['user_id'];

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing message']);
    exit;
}

$message = trim($input['message']);
$sessionToken = $input['session_token'] ?? '';
$cameraState = $input['camera_state'] ?? null;
$activeLayers = $input['active_layers'] ?? [];

// Build context for Otec
$eyeContext = "The user is viewing The Eye geospatial intelligence globe.\n";
if ($cameraState) {
    $eyeContext .= "Camera position: LAT {$cameraState['lat']}, LON {$cameraState['lon']}, ALT {$cameraState['alt']}m\n";
}
if (!empty($activeLayers)) {
    $eyeContext .= "Active layers: " . implode(', ', $activeLayers) . "\n";
}
$eyeContext .= "You can control the globe by including an eye_command JSON object in your response.\n";
$eyeContext .= "Available actions: fly_to, track, toggle_layer, set_style, annotate, save_view, reset, query\n";

// Route to the existing chat system as Otec
// We'll call the chat API internally with Otec as the agent
$chatPayload = [
    'message' => $message,
    'agent' => 'otec',
    'context_extra' => $eyeContext,
    'is_eye_command' => true
];

// Internal HTTP call to the main chat endpoint
$chatUrl = 'https://self.foreverbox.co.uk/api/chat.php';
$ch = curl_init($chatUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($chatPayload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Cookie: ' . ($_SERVER['HTTP_COOKIE'] ?? '')  // Forward session cookie
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 5,
]);

$chatResponse = curl_exec($ch);
$chatCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$reply = 'Command received.';
$eyeCommand = null;

if ($chatResponse && $chatCode === 200) {
    $chatData = json_decode($chatResponse, true);
    $reply = $chatData['reply'] ?? $chatData['message'] ?? 'Acknowledged.';

    // Parse eye_command from the reply text (agent should output JSON block)
    if (preg_match('/\{[^{}]*"action"\s*:\s*"[^"]+"/s', $reply, $matches)) {
        $cmdJson = json_decode($matches[0], true);
        if ($cmdJson && isset($cmdJson['action'])) {
            $eyeCommand = $cmdJson;
            // Remove the JSON from the display reply
            $reply = trim(str_replace($matches[0], '', $reply));
        }
    }

    // Also check if chat returned a structured eye_command field
    if (!$eyeCommand && isset($chatData['eye_command'])) {
        $eyeCommand = $chatData['eye_command'];
    }
}

// Log the query
$eyeService = new EyeService();
$eyeService->logAgentQuery($userId, 'otec', $sessionToken, $message, $reply, $eyeCommand, $eyeCommand !== null);

echo json_encode([
    'reply' => $reply,
    'eye_command' => $eyeCommand
]);
