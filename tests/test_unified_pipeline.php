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
    // 3. Verify indexing status and chunks in DB
    $chunksRes = $council->getFileChunks($fileId);
    $chunkCount = count($chunksRes['chunks'] ?? []);
    assertTest("Vector Chunks Generated & Saved", $chunkCount > 0, "Chunk count: $chunkCount");

    // 4. Search for unique keyword via CouncilClient
    sleep(1);
    $searchRes = $council->searchCommons("unique test content entity", 5);
    $found = false;
    foreach ($searchRes['results'] ?? [] as $r) {
        if (str_contains($r['chunk_text'] ?? '', 'unique test content entity')) {
            $found = true;
            break;
        }
    }
    assertTest("Hybrid Vector Search Retrieval (Cosine Sim + Dense Vectors)", $found, "Search results did not contain uploaded text");

    // 5. Test KnowledgeService getAllFiles() includes new file
    $allFiles = $knowledge->getAllFiles();
    $inList = false;
    foreach ($allFiles as $f) {
        if ($f['id'] === $fileId || str_contains($f['sea_path'] ?? '', $testDocName)) {
            $inList = true;
            break;
        }
    }
    assertTest("KnowledgeService::getAllFiles contains file", $inList, "File not listed in getAllFiles");

    // 6. Test Re-ingest
    $reingestRes = $council->reingestFiles([$fileId]);
    assertTest("Council Reingest Endpoint", !empty($reingestRes['success']), json_encode($reingestRes));

    // 7. Test Delete
    $deleteRes = $council->deleteCommonsFile($fileId);
    assertTest("Council Delete Endpoint (DB + Disk Cascade)", !empty($deleteRes['success']), json_encode($deleteRes));

    // 8. Verify chunks deleted from database
    $cleanedUp = false;
    try {
        $chunksAfter = $council->getFileChunks($fileId);
        $cleanedUp = empty($chunksAfter['success']) || count($chunksAfter['chunks'] ?? []) === 0;
    } catch (\Throwable $e) {
        // HTTP 404 File not found is expected and verifies cleanup
        $cleanedUp = str_contains($e->getMessage(), 'File not found') || str_contains($e->getMessage(), '404');
    }
    assertTest("Chunks Cleaned Up (404 Confirmed)", $cleanedUp);

    // 9. Verify search no longer returns deleted file
    $searchAfter = $council->searchCommons("unique test content entity", 5);
    $stillFound = false;
    foreach ($searchAfter['results'] ?? [] as $r) {
        if (str_contains($r['chunk_text'] ?? '', 'unique test content entity')) {
            $stillFound = true;
            break;
        }
    }
    assertTest("Search Cleared (Deleted Doc Excluded)", !$stillFound);
}

echo "\n----------------------------------------------------------------\n";
echo "RESULT: $passed / $total tests passed (" . round(($passed/$total)*100) . "%)\n";
echo "================================================================\n";
