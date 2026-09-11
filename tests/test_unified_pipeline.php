<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/services/CouncilClient.php';
require_once __DIR__ . '/../src/services/KnowledgeService.php';

echo "================================================================\n";
echo "       UNIFIED KNOWLEDGE PIPELINE VERIFICATION SUITE           \n";
echo "================================================================\n\n";

$council = new CouncilClient();
$knowledge = new KnowledgeService();

$passed = 0;
$total = 0;

function assertTest(string $name, bool $condition, string $details = '') {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] $name\n";
    } else {
        echo " [FAIL] $name" . ($details ? " -> $details" : "") . "\n";
    }
}

// 1. Health Check
$healthy = $council->isAvailable();
assertTest("Council API Availability", $healthy, "Council endpoint not reachable");

// 2. Upload test document to Commons
$testDocName = 'test_unified_pipeline_' . time() . '.md';
$testDocContent = "# Test Document For Unified Ingestion Pipeline\n\nThis is a unique test content entity generated at " . date('Y-m-d H:i:s') . ".\nThe quick brown fox jumps over the lazy dog in the Quiddity Lore Sea.\n\n## Section Two\n\nForeverBox cognitive architecture guarantees single truth for knowledge ingestion.";

$tmpFile = tempnam(sys_get_temp_dir(), 'test_kb_');
file_put_contents($tmpFile, $testDocContent);

$uploadRes = [];
try {
    $uploadRes = $council->uploadToCommons($tmpFile, $testDocName, '06_QuiddityLtd_Dev_Specs');
    assertTest("Council Upload to Commons", !empty($uploadRes['success']) && !empty($uploadRes['file_id']), json_encode($uploadRes));
} catch (\Throwable $e) {
    assertTest("Council Upload to Commons", false, $e->getMessage());
}
unlink($tmpFile);

$fileId = (int)($uploadRes['file_id'] ?? 0);
$relPath = $uploadRes['relative_path'] ?? '';

if ($fileId > 0) {
    // 3. Verify physical file exists on disk
    $expectedDiskPath = '/foreverbox_data/Quiddity_Lore_Sea/' . ltrim($relPath, '/');
    assertTest("Physical File on Disk", file_exists($expectedDiskPath), "Missing at $expectedDiskPath");

    // 4. Verify indexing status and chunks in DB
    $chunksRes = $council->getFileChunks($fileId);
    $chunkCount = count($chunksRes['chunks'] ?? []);
    assertTest("Vector Chunks Generated", $chunkCount > 0, "Chunk count: $chunkCount");

    // 5. Search for unique keyword via CouncilClient
    sleep(1);
    $searchRes = $council->searchCommons("unique test content entity", 5);
    $found = false;
    foreach ($searchRes['results'] ?? [] as $r) {
        if (str_contains($r['chunk_text'] ?? '', 'unique test content entity')) {
            $found = true;
            break;
        }
    }
    assertTest("Hybrid Vector Search Retrieval", $found, "Search results did not contain uploaded text");

    // 6. Test KnowledgeService getAllFiles() includes new file
    $allFiles = $knowledge->getAllFiles();
    $inList = false;
    foreach ($allFiles as $f) {
        if ($f['id'] === $fileId || str_contains($f['sea_path'] ?? '', $testDocName)) {
            $inList = true;
            break;
        }
    }
    assertTest("KnowledgeService::getAllFiles contains file", $inList, "File not listed in getAllFiles");

    // 7. Test Re-ingest
    $reingestRes = $council->reingestFiles([$fileId]);
    assertTest("Council Reingest Endpoint", !empty($reingestRes['success']), json_encode($reingestRes));

    // 8. Test Delete
    $deleteRes = $council->deleteCommonsFile($fileId);
    assertTest("Council Delete Endpoint", !empty($deleteRes['success']), json_encode($deleteRes));

    // 9. Verify physical file deleted from disk
    assertTest("Physical File Removed from Disk", !file_exists($expectedDiskPath), "File still exists at $expectedDiskPath");

    // 10. Verify chunks deleted from database
    $chunksAfter = $council->getFileChunks($fileId);
    assertTest("Chunks Cleaned Up (404/Empty)", empty($chunksAfter['success']) || count($chunksAfter['chunks'] ?? []) === 0);
}

echo "\n----------------------------------------------------------------\n";
echo "RESULT: $passed / $total tests passed (" . round(($passed/$total)*100) . "%)\n";
echo "================================================================\n";
