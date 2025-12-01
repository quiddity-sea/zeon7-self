<?php
/**
 * Database Seeder
 * Imports existing markdown files into the database.
 * Usage: php scripts/seed_db.php
 */

require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/core/DatabaseService.php';
require_once __DIR__ . '/../src/services/InstructionService.php';
require_once __DIR__ . '/../src/services/KnowledgeService.php';

echo "🌱 Starting Database Seed...\n";

// Initialize Services
$db = DatabaseService::getInstance();
$instructionService = new InstructionService();
$knowledgeService = new KnowledgeService();

// 1. Seed Instructions
echo "\n--- Seeding Instructions ---\n";
$instructionFile = __DIR__ . '/../instructions/current-instructions.md';

if (file_exists($instructionFile)) {
    $content = file_get_contents($instructionFile);
    
    // Check if already exists to avoid duplicates
    $current = $instructionService->getCurrentVersion();
    if (!$current || $current['content'] !== $content) {
        $version = $instructionService->createVersion($content, 'system_seed');
        echo "✅ Imported instruction version $version\n";
    } else {
        echo "ℹ️ Instructions already up to date (Version {$current['version']})\n";
    }
} else {
    echo "⚠️ Instruction file not found: $instructionFile\n";
}

// 2. Seed Knowledge
echo "\n--- Seeding Knowledge ---\n";
$knowledgeDir = __DIR__ . '/../knowledge';
$files = glob($knowledgeDir . '/*.md');

foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    $hash = md5($content);
    $size = strlen($content);
    
    // Check if file exists
    if ($knowledgeService->fileExists($filename)) {
        echo "ℹ️ Knowledge file already exists: $filename\n";
        // Optional: Update logic could go here, but for now we skip
        continue;
    }
    
    try {
        // Upload Document
        $docId = $knowledgeService->uploadFile($filename, $content, $hash, $size);
        echo "✅ Imported knowledge doc: $filename (ID: $docId)\n";
        
        // Chunk Document (Simple chunking by headers)
        $chunks = [];
        $lines = explode("\n", $content);
        $currentChunk = ['heading' => 'Introduction', 'content' => ''];
        
        foreach ($lines as $line) {
            if (preg_match('/^#{1,3}\s+(.+)$/', $line, $matches)) {
                // Save previous chunk if not empty
                if (!empty(trim($currentChunk['content']))) {
                    $chunks[] = $currentChunk;
                }
                // Start new chunk
                $currentChunk = [
                    'heading' => trim($matches[1]),
                    'content' => $line . "\n"
                ];
            } else {
                $currentChunk['content'] .= $line . "\n";
            }
        }
        // Save last chunk
        if (!empty(trim($currentChunk['content']))) {
            $chunks[] = $currentChunk;
        }
        
        $knowledgeService->chunkFile($docId, $chunks);
        echo "   -> Created " . count($chunks) . " chunks\n";
        
    } catch (Exception $e) {
        echo "❌ Failed to import $filename: " . $e->getMessage() . "\n";
    }
}

echo "\n✨ Seeding Completed!\n";
