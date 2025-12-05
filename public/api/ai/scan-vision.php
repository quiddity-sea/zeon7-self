<?php
/**
 * Zeon7 Vision Studio Controller
 * Handles Image Upload -> Analysis -> DB Storage
 */

require_once __DIR__ . '/../../../src/Middleware/AuthMiddleware.php';
AuthMiddleware::handle();
require_once __DIR__ . '/../../../src/Services/ConfigService.php';
require_once __DIR__ . '/../../../src/Services/GeminiService.php';
require_once __DIR__ . '/../../../src/Services/InstructionService.php';
require_once __DIR__ . '/../../../src/Services/KnowledgeService.php'; // Reuse for DB saving

header('Content-Type: application/json');

try {
    if (!isset($_FILES['image'])) {
        throw new Exception('No image uploaded');
    }

    $file = $_FILES['image'];
    $imageData = file_get_contents($file['tmp_name']);
    $mimeType = $file['type'];

    // 1. Setup Services
    $config = new ConfigService();
    $instructionService = new InstructionService();
    $knowledgeService = new KnowledgeService(); // We'll use this to save the file/metadata
    
    $apiKey = $config->get('ai_api_key');
    $model = $config->get('ai_model') ?: 'gemini-1.5-pro';
    $gemini = new GeminiService($apiKey, $model);

    // 2. Build Prompt
    $persona = $instructionService->getCurrentInstruction();
    $visionPrompt = "Analyze this image. Assign it to one of these categories: Architecture, Portrait, Nature, Experimental, Documentary, Seascapes. \n";
    $visionPrompt .= "Generate a unique 'slug' (8-45 chars, kebab-case). \n";
    $visionPrompt .= "Generate a descriptive caption (50-250 chars) written in the Zeon7 persona (cryptic, observant, noir). \n";
    $visionPrompt .= "Return ONLY a JSON object: { \"category\": \"...\", \"slug\": \"...\", \"caption\": \"...\" }";

    // 3. Execute Vision Analysis
    $analysisJson = $gemini->scanVision($imageData, $mimeType, $visionPrompt);
    
    // Clean markdown code blocks if present
    $analysisJson = str_replace(['```json', '```'], '', $analysisJson);
    $analysis = json_decode($analysisJson, true);

    if (!$analysis) {
        throw new Exception('Failed to parse AI analysis');
    }

    // 4. Save to DB (Simulated via KnowledgeService or Custom Logic)
    // For now, we'll just return the analysis to the frontend to display in the "Processed" gallery
    // In a full implementation, we'd move the file to /uploads and insert into a 'visuals' table.
    
    echo json_encode([
        'success' => true,
        'analysis' => $analysis,
        'preview' => 'data:' . $mimeType . ';base64,' . base64_encode($imageData)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
