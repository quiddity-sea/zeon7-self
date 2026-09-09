# Unified Knowledge Ingestion Pipeline

**Document:** `docs/unified-knowledge-pipeline-v1-implementation-plan.md`  
**Architecture baseline:** `docs/hermes-integrate-v2-implementation-plan.md`  
**Primary repository:** `quiddity-sea/zeon7-self`  
**Related repositories:** `quiddity-sea/foreverbox-data`

---

# 0. Builder Instructions

This document is an execution plan, not a suggestion list. Follow it in order.

The objective is to unify the two separate knowledge ingestion pipelines (Web UI upload and Hermes/Council scanner) into a **single canonical pipeline** where the Quiddity Lore Sea filesystem is the source of truth and the Council ingestion worker is the sole processor.

### Non-negotiable rules

1. **One pipeline, one truth.** Every document ??? whether uploaded via the Web UI or discovered via Hermes/Council scanner ??? must flow through the same ingestion path: **File ??? Sea ??? Sync ??? Chunk ??? Embed ??? Vector Store.**
2. **The Quiddity Lore Sea filesystem is the canonical document store.** No document should exist only in a database. The filesystem at `/foreverbox_data/Quiddity_Lore_Sea/` is the authoritative source.
3. **The Council ingestion worker is the sole chunking/embedding processor.** The Self app must never chunk or embed documents itself. It writes files to the Sea and tells Council to process them.
4. **Do not break existing Council scanner functionality.** The existing `POST /v1/commons/files/sync` endpoint and `ingestion_worker.php` CLI script must continue to work exactly as they do now.
5. **Do not remove the local DB fallback tables yet.** Keep `knowledge_doc` and `knowledge_chunk` in `zeon7_self_dev` as dead tables for now. They will be archived in a future cleanup phase. Just stop writing to them.
6. **Do not invent a second vector store or embedding service.** Consume the existing `127.0.0.1:8900` embedding microservice via the existing `ingestion_worker.php`.
7. **PDF support is a bonus ??? implement `.md` and `.txt` first.** The worker already handles PDF; this plan focuses on the upload path.

### The final system must satisfy this invariant:

> **A file uploaded through the Self admin Knowledge page must appear in the Quiddity Lore Sea, be indexed by the Council scanner, be chunked and embedded by the ingestion worker, and be retrievable by vector search ??? using exactly the same pipeline as a file placed on disk and scanned by the Hermes cron job. No separate codepath. No separate database. One truth.**

---

# 1. Current State (The Problem)

## 1.1 Two Disconnected Pipelines

```text
                    CURRENT STATE (BROKEN)
                    
    ?????????????????????????????????????????????????????????????????????????????????     ????????????????????????????????????????????????????????????????????????????????????????????????
    ???   PATH 1: Web Upload    ???     ???  PATH 2: Council Scanner     ???
    ???   (Self Admin UI)       ???     ???  (Hermes/Cron)               ???
    ?????????????????????????????????????????????????????????????????????????????????     ????????????????????????????????????????????????????????????????????????????????????????????????
    ??? 1. User uploads .md     ???     ??? 1. File exists on disk in    ???
    ???    via admin/knowledge   ???     ???    /Quiddity_Lore_Sea/       ???
    ???                         ???     ???                              ???
    ??? 2. PHP reads into       ???     ??? 2. POST /v1/commons/files/   ???
    ???    memory               ???     ???    sync registers file       ???
    ???                         ???     ???    in quiddity_files          ???
    ??? 3. Stores metadata in   ???     ???                              ???
    ???    knowledge_doc (local  ???     ??? 3. ingestion_worker.php      ???
    ???    zeon7_self_dev DB)    ???     ???    chunks text (1000-char    ???
    ???                         ???     ???    paragraph boundaries)     ???
    ??? 4. Chunks by markdown   ???     ???                              ???
    ???    headers into          ???     ??? 4. Embeds via 384-dim        ???
    ???    knowledge_chunk       ???     ???    all-MiniLM-L6-v2          ???
    ???                         ???     ???    at 127.0.0.1:8900         ???
    ??? 5. NO embeddings        ???     ???                              ???
    ??? 6. NO file on disk      ???     ??? 5. Stores vectors in         ???
    ??? 7. File is DISCARDED    ???     ???    quiddity_vector_references ???
    ???                         ???     ???                              ???
    ??? RESULT: Document goes   ???     ??? 6. Classifies & moves file   ???
    ??? into a DB that nothing  ???     ???    into appropriate subfolder???
    ??? reads (when backend =   ???     ???                              ???
    ??? 'council')              ???     ??? RESULT: Full vector search   ???
    ?????????????????????????????????????????????????????????????????????????????????     ??? available to all interfaces  ???
                                    ????????????????????????????????????????????????????????????????????????????????????????????????
```

## 1.2 The disconnect

- `KNOWLEDGE_BACKEND=council` in Self's `.env` means `getAllFiles()` and `searchChunks()` already query **Council Commons** (`quiddity_commons`).
- But `uploadFile()` writes to **`zeon7_self_dev.knowledge_doc`** ??? a completely different database that is never read.
- Uploaded files vanish from the UI on page refresh because the list comes from Council, but the upload went to the local DB.
- No embeddings are ever created for web-uploaded files.
- The uploaded file content is never saved to disk ??? the PHP temp file is deleted after the request.

---

# 2. Target Architecture

```text
                    UNIFIED PIPELINE
                    
    ???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
    ???                   ENTRY POINTS                            ???
    ???                                                           ???
    ???   Web Admin UI          Hermes/Cron Scanner               ???
    ???   (upload .md/.txt)     (discovers files on disk)         ???
    ???        ???                       ???                          ???
    ???        ???                       ???                          ???
    ???   ????????????????????????????????????????????????????????????         ???                          ???
    ???   ??? Write file to    ???         ???                          ???
    ???   ??? Quiddity Lore    ???         ???                          ???
    ???   ??? Sea filesystem   ???         ???                          ???
    ???   ????????????????????????????????????????????????????????????         ???                          ???
    ???            ???                   ???                          ???
    ???            ???                   ???                          ???
    ???   ????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????                ???
    ???   ???  Council API: POST /v1/commons/      ???                ???
    ???   ???  files/sync  (with explicit paths)   ???                ???
    ???   ???  ??? Registers in quiddity_files       ???                ???
    ???   ???  ??? Sets indexing_status = 'pending'  ???                ???
    ???   ????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????                ???
    ???                      ???                                    ???
    ???                      ???                                    ???
    ???   ????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????                ???
    ???   ???  Council: IngestionService           ???                ???
    ???   ???  (ALWAYS runs inline during upload)  ???                ???
    ???   ???                                      ???                ???
    ???   ???  Step 0: ensureEmbeddingService()    ???                ???
    ???   ???    ??? Health-check 127.0.0.1:8900     ???                ???
    ???   ???    ??? If down: systemctl start        ???                ???
    ???   ???      council-embedding.service       ???                ???
    ???   ???    ??? Wait for ready (up to 20s)      ???                ???
    ???   ???                                      ???                ???
    ???   ???  Step 1: Paragraph chunking (1000c)  ???                ???
    ???   ???  Step 2: 384-dim embeddings          ???                ???
    ???   ???          (MiniLM-L6-v2)              ???                ???
    ???   ???  Step 3: Store in                    ???                ???
    ???   ???          quiddity_vector_references  ???                ???
    ???   ???  Step 4: FolderRouter classification ???                ???
    ???   ???  Step 5: Dead-letter queue on fail   ???                ???
    ???   ????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????                ???
    ???                      ???                                    ???
    ???                      ???                                    ???
    ???   ????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????                ???
    ???   ???  SINGLE VECTOR STORE                 ???                ???
    ???   ???  quiddity_commons.quiddity_vector_   ???                ???
    ???   ???  references                          ???                ???
    ???   ???  ??? Hybrid search (cosine + FULLTEXT) ???                ???
    ???   ???  ??? Used by ALL interfaces            ???                ???
    ???   ????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????                ???
    ???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
```

---

# 3. Implementation Phases

## Phase 1: Add Upload Endpoint to Council Library API

> [!IMPORTANT]
> This is the foundation. Council needs an endpoint that accepts a file upload, saves it to the Lore Sea, runs sync, and optionally triggers inline ingestion.

### What to build

A new route `POST /v1/commons/files/upload` in the Council Library API that:

1. Accepts a multipart file upload (`Content-Type: multipart/form-data`) with fields:
   - `file` ??? the uploaded file (required)
   - `subfolder` ??? optional target subfolder within the Lore Sea (e.g. `06_QuiddityLtd_Dev_Specs`)
2. Validates:
   - File extension is `.md`, `.txt`, or `.pdf`
   - File size is under 10MB
   - Filename does not contain path traversal characters (`..`, `/`, `\`)
3. Saves the file to `/foreverbox_data/Quiddity_Lore_Sea/{subfolder}/{filename}` (or root if no subfolder)
4. Calls `QuiddityController::sync` logic internally with the explicit path of the new file
5. **ALWAYS runs inline ingestion** via `IngestionService::ingestFile()`:
   - Calls `ensureEmbeddingService()` first ??? health-checks `127.0.0.1:8900` and if the embedding microservice is down, starts it via `systemctl start council-embedding.service` and waits up to 20 seconds for it to become ready
   - Chunks the file content (paragraph boundaries, 1000-char ceiling)
   - Embeds all chunks via the embedding microservice (384-dim `all-MiniLM-L6-v2`)
   - Stores vectors in `quiddity_vector_references`
   - Classifies via `FolderRouter` and optionally moves the file into a subfolder
   - On failure, writes chunks to `ingestion_dead_letter` for retry
6. Returns JSON with `file_id`, `indexing_status` (should be `'indexed'`), `chunk_count`, and `relative_path`
7. Sets `set_time_limit(300)` at the start ??? embedding + chunking can take time for large files

> [!IMPORTANT]
> **Vector mode is not optional.** Every upload must produce embeddings. If the embedding service is not running, the upload endpoint wakes it up. If it still can't start after 20 seconds, the upload returns `indexing_status: 'failed'` with an error message ??? the file is still saved to the Sea so the cron worker can retry later, but the user is told it failed.

### Files to create/modify

#### [NEW] `council-library/php-api/src/Service/IngestionService.php`

Extract the core ingestion logic from `scripts/ingestion_worker.php` into a reusable service class:

```php
<?php
namespace CouncilLibrary\Service;

class IngestionService
{
    private \PDO $pdo;
    private string $embeddingUrl;
    private FolderRouter $router;

    public function __construct(\PDO $pdo, ?string $embeddingUrl = null)
    {
        $this->pdo = $pdo;
        $this->embeddingUrl = $embeddingUrl ?: 'http://127.0.0.1:8900';
        $this->router = new FolderRouter(null, null, $pdo);
    }

    /**
     * Ensure the embedding microservice is running.
     * Health-checks 127.0.0.1:8900/health ??? if down, starts it via systemctl
     * and polls for readiness up to 20 seconds.
     *
     * @throws \RuntimeException if the service cannot be started
     */
    private function ensureEmbeddingService(): void
    {
        // Quick health check (3s timeout)
        if ($this->isEmbeddingHealthy()) return;

        // Not running ??? wake it up
        exec('systemctl start council-embedding.service 2>&1', $out, $ret);

        // Poll for readiness: the sentence-transformers model takes
        // a few seconds to load into memory on first start
        for ($attempt = 0; $attempt < 10; $attempt++) {
            sleep(2);
            if ($this->isEmbeddingHealthy()) return;
        }

        throw new \RuntimeException(
            'Embedding service at ' . $this->embeddingUrl .
            ' failed to start after 20 seconds. Check council-embedding.service logs.'
        );
    }

    private function isEmbeddingHealthy(): bool
    {
        $ch = curl_init($this->embeddingUrl . '/health');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp && str_contains($resp, '"ok"');
    }

    /**
     * Ingest a single file by its quiddity_files ID.
     *
     * 1. Calls ensureEmbeddingService() ??? wakes up the microservice if needed
     * 2. Reads file from disk via its relative_path in quiddity_files
     * 3. Chunks by paragraph boundaries (1000-char ceiling)
     * 4. Embeds all chunks via the embedding microservice
     * 5. Classifies via FolderRouter, optionally moves file on disk
     * 6. Stores vectors in quiddity_vector_references
     * 7. On failure: writes to ingestion_dead_letter for retry
     *
     * @return array{status: string, chunks: int, error: ?string}
     */
    public function ingestFile(int $fileId): array { ... }

    /**
     * Ingest all pending files (indexing_status IN ('pending','processing')).
     * Calls ensureEmbeddingService() once at the start, then processes each file.
     *
     * @return array{processed: int, indexed: int, failed: int}
     */
    public function ingestPending(): array { ... }

    /**
     * Reset a file's indexing_status to 'pending' for re-ingestion.
     */
    public function resetFile(int $fileId): void { ... }

    /**
     * Fetch embeddings from the microservice. Extracted from ingestion_worker.php.
     * Sends all chunk texts in a single batch POST to /embed.
     *
     * @return string[] Array of hex-encoded float32 embedding vectors
     */
    private function getEmbeddings(array $texts): array { ... }
}
```

The logic inside `ingestFile()` is a direct extraction from `ingestion_worker.php` lines 63???198, refactored into a method. The existing `ingestion_worker.php` script must then be updated to instantiate `IngestionService` and call `ingestPending()`, keeping backwards compatibility.

The `getEmbeddings()` helper function (lines 204???219 of the worker) should become a private method of the service.

#### [MODIFY] `council-library/php-api/src/Controller/IngestionController.php`

Replace the stub `batch()` method with a working implementation that uses `IngestionService`. Add a new `upload()` method:

```php
public function upload(Request $request, Response $response): Response
{
    set_time_limit(300); // embedding can take time for large files
    $this->ensureCommons();
    
    // 1. Extract uploaded file from $_FILES['file']
    //    Council's zero-dependency router passes $_FILES through natively
    // 2. Validate extension (.md, .txt, .pdf), size (< 10MB), filename safety
    // 3. Read optional 'subfolder' from POST body / $_POST
    // 4. Build target path: /foreverbox_data/Quiddity_Lore_Sea/{subfolder?}/{filename}
    // 5. file_put_contents($targetPath, file_get_contents($tmpFile))
    // 6. Register in quiddity_files:
    //    INSERT INTO quiddity_files (relative_path, content_hash, mime_type,
    //    file_size_bytes, last_modified, indexing_status)
    //    VALUES (?, ?, 'text/markdown', ?, NOW(), 'pending')
    //    ON DUPLICATE KEY UPDATE content_hash=?, indexing_status='pending', ...
    // 7. ALWAYS ingest inline:
    //    $service = new IngestionService($this->pdo);
    //    $result = $service->ingestFile($fileId);
    //    ??? ensureEmbeddingService() is called internally
    //    ??? chunks, embeds, stores vectors, classifies
    // 8. Return JSON: { success, file_id, relative_path, indexing_status, chunk_count }
}

public function batch(Request $request, Response $response): Response
{
    // Replace stub: accept file_ids array, call IngestionService::ingestFile for each
    // Also always calls ensureEmbeddingService() via the service
}
```

#### [MODIFY] `council-library/php-api/public/index.php`

Add within the `/v1/commons` group:

```php
$c->post('/files/upload', c(IngestionController::class, 'upload'));
$c->delete('/files/{id}', c(QuiddityController::class, 'deleteFile'));
```

#### [MODIFY] `council-library/scripts/ingestion_worker.php`

Refactor to use the new `IngestionService` class:

```php
require_once __DIR__ . '/../php-api/vendor/autoload.php';

$pdo = new PDO(/* same connection as current */);

$service = new \CouncilLibrary\Service\IngestionService($pdo);

if (in_array('--reclassify', $argv)) {
    echo "Reclassify: resetting all files to pending...\n";
    $pdo->exec("UPDATE quiddity_files SET indexing_status = 'pending'");
}

$result = $service->ingestPending();
echo "Done. Processed: {$result['processed']}, Indexed: {$result['indexed']}, Failed: {$result['failed']}\n";
```

### Phase 1 Verification

```bash
# 0. Test embedding service wake-up (stop it first, upload should restart it)
sudo systemctl stop council-embedding.service
curl -s -X POST http://127.0.0.1:8900/health  # should fail

# 1. Upload a test file ??? this should wake up the embedding service automatically
curl -X POST -F "file=@test.md" \
  -H "Authorization: Bearer <key>" -H "X-Agent-ID: zeon7" \
  http://100.126.174.30:8080/v1/commons/files/upload
# Expected: { success: true, indexing_status: "indexed", chunk_count: N }

# 2. Verify embedding service is now running (woken up by upload)
curl -s -X POST http://127.0.0.1:8900/health  # should return {"status":"ok"}

# 3. Verify: file on disk
ls -la /foreverbox_data/Quiddity_Lore_Sea/test.md

# 4. Verify: DB record
sudo mysql quiddity_commons -e \
  "SELECT id, relative_path, indexing_status FROM quiddity_files WHERE relative_path LIKE '%test%'"

# 5. Verify: chunks with embeddings (ALL chunks must have non-null embeddings)
sudo mysql quiddity_commons -e \
  "SELECT COUNT(*) as chunks, SUM(embedding IS NOT NULL) as with_vectors \
   FROM quiddity_vector_references WHERE file_id = <id>"

# 6. Verify: search returns content
curl "http://100.126.174.30:8080/v1/commons/search?q=<term>" \
  -H "Authorization: Bearer <key>" -H "X-Agent-ID: zeon7"
```

---

## Phase 2: Rewire Self Upload to Use Council

> [!IMPORTANT]
> Self's upload endpoint stops writing to its local DB and instead sends the file to Council's new upload endpoint.

### Files to modify

#### [MODIFY] `/var/www/self/api/knowledge/upload.php`

Complete rewrite of `UploadController::handleRequest()`:

```php
public function handleRequest(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->sendError('Method not allowed', 405);
    }
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $this->sendError('No file uploaded or upload error', 400);
    }
    
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['md', 'txt'])) {
        $this->sendError('Only .md and .txt files are allowed', 400);
    }
    
    // Forward to Council API ??? the single source of truth
    // Council will:
    //   1. Save file to Quiddity Lore Sea filesystem
    //   2. Register in quiddity_files
    //   3. Wake up the embedding microservice if it's not running
    //   4. Chunk + embed inline (paragraph-based, 384-dim vectors)
    //   5. Store vectors in quiddity_vector_references
    //   6. Return the result with chunk count and indexed status
    $councilClient = new CouncilClient();
    $result = $councilClient->uploadToCommons(
        $file['tmp_name'],
        $file['name'],
        $_POST['subfolder'] ?? null
    );
    
    $this->sendResponse([
        'success'      => true,
        'id'           => $result['file_id'],
        'filename'     => $result['relative_path'],
        'chunks_count' => $result['chunk_count'] ?? 0,
        'status'       => $result['indexing_status'] ?? 'pending',
        'message'      => 'File ingested into Quiddity Lore Sea'
    ]);
}
```

**Remove entirely:** The `parseChunks()` method. Remove calls to `$this->knowledgeService->uploadFile()` and `$this->knowledgeService->chunkFile()`. The `KnowledgeService` dependency is no longer needed in the upload path.

#### [MODIFY] `/var/www/self/src/services/CouncilClient.php`

Add a new method for multipart file upload to Council:

```php
/**
 * Upload a file to Council Commons (Quiddity Lore Sea).
 * Uses multipart/form-data instead of JSON.
 * Council ALWAYS ingests inline: writes file to Sea, chunks, embeds,
 * and wakes up the embedding microservice if needed.
 *
 * @param string      $tmpFilePath  PHP temp file path
 * @param string      $filename     Original filename
 * @param string|null $subfolder    Target subfolder in Lore Sea
 * @return array  { file_id, relative_path, indexing_status, chunk_count }
 */
public function uploadToCommons(
    string $tmpFilePath,
    string $filename,
    ?string $subfolder = null
): array {
    $url = $this->baseUrl . '/v1/commons/files/upload';
    
    $postFields = [
        'file' => new \CURLFile($tmpFilePath, 'text/plain', $filename),
    ];
    if ($subfolder) {
        $postFields['subfolder'] = $subfolder;
    }
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'X-Agent-ID: ' . $this->agentId,
            'Authorization: Bearer ' . $this->apiKey,
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new \RuntimeException("Council upload error: {$error}");
    }
    
    $decoded = json_decode($response, true);
    if ($httpCode >= 400) {
        throw new \RuntimeException(
            'Council upload failed: ' . ($decoded['error'] ?? "HTTP {$httpCode}")
        );
    }
    
    return $decoded;
}

/**
 * Delete a file from Council Commons (removes from DB + Lore Sea filesystem).
 */
public function deleteCommonsFile(int $fileId): array
{
    return $this->delete("/v1/commons/files/{$fileId}");
}
```

### Phase 2 Verification

1. Upload a `.md` file via `https://self.foreverbox.co.uk/admin/knowledge.php`
2. File appears in Council Commons file list on the same page
3. File physically exists in `/foreverbox_data/Quiddity_Lore_Sea/`
4. Chat search returns content from the uploaded file

---

## Phase 3: Upgrade Knowledge Admin UI

> [!NOTE]
> The current UI only shows filename, size, date, and a delete button. Upgrade it to show the Council pipeline state.

### Files to modify

#### [MODIFY] `/var/www/self/admin/knowledge.php`

Add new table columns and UI elements:

1. **STATUS column**: Show `indexing_status` as a coloured HUD badge:
   - `indexed` ??? green badge `INDEXED`
   - `pending` ??? amber badge `PENDING`
   - `processing` ??? blue badge `PROCESSING`
   - `failed` ??? red badge `FAILED`
2. **SEA PATH column**: Show `relative_path` so the user can see where the file lives
3. **SUBFOLDER selector**: Add a `<select>` dropdown in the upload zone populated from known Lore Sea subfolders:
   - `(Root ??? auto-classify)`
   - `01_TheForeverbox_Mythos`
   - `02_ReInvigor_Texts`
   - `03_TheInitiative_Audio`
   - `04_FromTheNoise_Archives`
   - `05_Agent_Profiles`
   - `06_QuiddityLtd_Dev_Specs`
   - `07_MerrillLeo_CreativeWorks`
   - `08_VisualMedia`
4. **RE-INGEST button**: Per-file action to reset status to `pending` and re-trigger ingestion

#### [MODIFY] `/var/www/self/admin/js/knowledge.js`

Update `loadFiles()` to render the new columns from Council data:

```javascript
tbody.innerHTML = data.files.map(file => `
    <tr>
        <td>${this.escapeHtml(file.filename)}</td>
        <td>${this.escapeHtml(file.sea_path || file.filename)}</td>
        <td>${this.formatSize(file.file_size || file.size)}</td>
        <td>${this.statusBadge(file.status)}</td>
        <td>${new Date(file.created_at).toLocaleDateString()}</td>
        <td style="text-align: right;">
            <button onclick="Knowledge.reingestFile(${file.id})">Re-ingest</button>
            <button onclick="Knowledge.deleteFile(${file.id})">Delete</button>
        </td>
    </tr>
`).join('');
```

Add subfolder dropdown in upload zone. Add `reingestFile(id)` method.

#### [NEW] `/var/www/self/api/knowledge/reingest.php`

Simple proxy:
1. Accept `POST` with `{ file_id: N }`
2. Call Council API to reset the file's `indexing_status` to `pending`
3. Trigger inline ingestion via `POST /v1/commons/ingest/batch` with `{ files: [N] }`
4. Return the result

### Phase 3 Verification

- Knowledge page shows Council files with correct status badges
- Upload with subfolder selection places file in correct Lore Sea directory
- Re-ingest button re-processes a file and status changes from `pending` ??? `indexed`

---

## Phase 4: Fix Duplicate Search in Chat Pipeline

> [!NOTE]
> `api/chat.php` currently makes redundant duplicate calls to Council Commons search. Clean this up.

### Files to modify

#### [MODIFY] `/var/www/self/api/chat.php`

Locate and remove the redundant second `searchCommons()` call. There should be exactly **one** search call via `$this->knowledgeService->searchChunks($message, true)` which already delegates to Council when `KNOWLEDGE_BACKEND=council`. Remove any inline `$this->councilClient->searchCommons()` calls in the chat handler.

### Phase 4 Verification

- Chat returns relevant knowledge (single search call)
- No duplicate chunks appear in the system prompt
- Verify via Council API logs that only one `/v1/commons/search` request is made per chat message

---

## Phase 5: Update Delete to Remove from Sea Filesystem

### Files to modify

#### [MODIFY] `/var/www/self/api/knowledge/delete.php`

Rewrite to use Council API:

```php
$councilClient = new CouncilClient();
$result = $councilClient->deleteCommonsFile($fileId);
// Returns success ??? file deleted from DB + disk
```

#### [MODIFY] `council-library/php-api/src/Controller/QuiddityController.php`

Add `deleteFile()` method:

```php
public function deleteFile(Request $request, Response $response, array $args): Response
{
    $this->ensureCommons();
    $fileId = (int) ($args['id'] ?? 0);
    
    // 1. Look up file
    $stmt = $this->pdo->prepare('SELECT id, relative_path FROM quiddity_files WHERE id = :id');
    $stmt->execute(['id' => $fileId]);
    $file = $stmt->fetch();
    if (!$file) {
        return $this->json($response, ['success' => false, 'error' => 'File not found'], 404);
    }
    
    // 2. Delete physical file
    $fullPath = '/foreverbox_data/Quiddity_Lore_Sea/' . $file['relative_path'];
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
    
    // 3. Delete DB record (CASCADE deletes quiddity_vector_references)
    $this->pdo->prepare('DELETE FROM quiddity_files WHERE id = :id')->execute(['id' => $fileId]);
    
    return $this->json($response, ['success' => true, 'deleted' => $file['relative_path']]);
}
```

### Phase 5 Verification

- Delete a file from Knowledge admin page
- File is removed from Quiddity Lore Sea filesystem
- File is removed from `quiddity_files` and `quiddity_vector_references`
- Search no longer returns content from that file

---

# 4. File Change Summary

## Council Library (`foreverbox_data/council-library/`)

| Action | File | Description |
|--------|------|-------------|
| **[NEW]** | [`IngestionService.php`](file:///foreverbox_data/council-library/php-api/src/Service/IngestionService.php) | Reusable ingestion service extracted from worker |
| **[MODIFY]** | [`IngestionController.php`](file:///foreverbox_data/council-library/php-api/src/Controller/IngestionController.php) | Replace stub with working `upload()` + `batch()` |
| **[MODIFY]** | [`QuiddityController.php`](file:///foreverbox_data/council-library/php-api/src/Controller/QuiddityController.php) | Add `deleteFile()` method |
| **[MODIFY]** | [`index.php`](file:///foreverbox_data/council-library/php-api/public/index.php) | Add `POST /files/upload` and `DELETE /files/{id}` routes |
| **[MODIFY]** | [`ingestion_worker.php`](file:///foreverbox_data/council-library/scripts/ingestion_worker.php) | Refactor to use `IngestionService` |

## Self App (`/var/www/self/`)

| Action | File | Description |
|--------|------|-------------|
| **[MODIFY]** | `api/knowledge/upload.php` | Rewrite to forward file to Council API |
| **[MODIFY]** | `api/knowledge/delete.php` | Rewrite to delete via Council API + filesystem |
| **[NEW]** | `api/knowledge/reingest.php` | Trigger re-ingestion for a specific file |
| **[MODIFY]** | `src/services/CouncilClient.php` | Add `uploadToCommons()` and `deleteCommonsFile()` |
| **[MODIFY]** | `src/services/KnowledgeService.php` | Remove local DB upload/chunk methods (keep search) |
| **[MODIFY]** | `admin/knowledge.php` | Add status badges, chunk counts, subfolder picker |
| **[MODIFY]** | `admin/js/knowledge.js` | Updated rendering + subfolder + reingest UI |
| **[MODIFY]** | `api/chat.php` | Remove duplicate Council search call |

## Documentation

| Action | File | Description |
|--------|------|-------------|
| **[NEW]** | `docs/unified-knowledge-pipeline-v1-implementation-plan.md` | This plan (committed to self repo) |

---

# 5. Verification Plan

### Automated Tests

```bash
# 1. Council upload endpoint
curl -X POST -F "file=@test_doc.md" \
  -H "Authorization: Bearer <key>" \
  -H "X-Agent-ID: zeon7" \
  http://100.126.174.30:8080/v1/commons/files/upload

# 2. Verify file exists on disk
ls -la /foreverbox_data/Quiddity_Lore_Sea/test_doc.md

# 3. Verify indexed in DB
sudo mysql quiddity_commons -e \
  "SELECT id, relative_path, indexing_status FROM quiddity_files WHERE relative_path LIKE '%test_doc%'"

# 4. Verify chunks + embeddings exist
sudo mysql quiddity_commons -e \
  "SELECT COUNT(*) as chunks, SUM(embedding IS NOT NULL) as with_vectors \
   FROM quiddity_vector_references WHERE file_id = <id>"

# 5. Verify search returns content
curl "http://100.126.174.30:8080/v1/commons/search?q=test+content" \
  -H "Authorization: Bearer <key>" -H "X-Agent-ID: zeon7"

# 6. Delete via Council
curl -X DELETE "http://100.126.174.30:8080/v1/commons/files/<id>" \
  -H "Authorization: Bearer <key>" -H "X-Agent-ID: zeon7"

# 7. Verify file removed from disk
ls -la /foreverbox_data/Quiddity_Lore_Sea/test_doc.md  # should not exist
```

### Manual Verification

1. Upload a `.md` file via `https://self.foreverbox.co.uk/admin/knowledge.php`
2. Verify file appears in the files list with `indexed` status and chunk count > 0
3. Open the chat widget, ask a question related to the uploaded file content
4. Verify the agent's response includes knowledge from the uploaded file
5. Delete the file from the Knowledge page
6. Verify it's gone from the list AND from `/foreverbox_data/Quiddity_Lore_Sea/`
7. Search again ??? verify the content is no longer returned

### Cross-Interface Test

1. Upload via Web UI ??? verify Hermes agent can find the knowledge via Council search
2. Place a file manually in `/foreverbox_data/Quiddity_Lore_Sea/` ??? run sync + ingestion worker ??? verify it appears in the Web UI knowledge list
3. Both paths produce identical results in `quiddity_vector_references`

---

# 6. Dependencies and Prerequisites

| Dependency | Status | Notes |
|---|---|---|
| Embedding microservice (`127.0.0.1:8900`) | ??? Running | `council-embedding.service` active on VPS |
| Council API (`100.126.174.30:8080`) | ??? Running | Verified via healthz |
| `quiddity_commons` database | ??? Exists | Tables `quiddity_files`, `quiddity_vector_references` exist |
| `ingestion_worker.php` | ??? Working | Runs via cron, processes pending files |
| `KNOWLEDGE_BACKEND=council` in Self `.env` | ??? Set | Self already reads from Council |
| PHP `CURLFile` support | ??? Available | Standard PHP 8.x |

---

# 7. Risk Assessment

> [!WARNING]
> **Inline ingestion timeout for very large files**: Embedding + chunking runs inline within the upload request. `set_time_limit(300)` is called in the Council endpoint and the CouncilClient uses a 120s cURL timeout. For files that exceed this (e.g. a 500-page PDF), the ingestion will fail ??? but the file is still saved to the Sea with `indexing_status: 'failed'`, so the cron worker can retry later.

> [!IMPORTANT]
> **Embedding service auto-start**: `IngestionService::ensureEmbeddingService()` calls `systemctl start council-embedding.service` if the health check fails. This requires the PHP process user to have systemctl permissions for that service unit. On the VPS, the Council API runs under `quiddity` ??? ensure a sudoers entry or polkit rule allows `quiddity` to start `council-embedding.service` without a password. Alternatively, the systemd unit can be configured with `Restart=always` so it auto-restarts on crash, making the `systemctl start` call a safety net rather than the primary mechanism.

> [!NOTE]
> **File permissions**: Files written to the Lore Sea by the Council API must be readable by the ingestion worker. On the VPS, web files are owned by `quiddity:psacln`. Ensure `ingestion_worker.php` runs with compatible permissions or that the upload sets ownership appropriately.

> [!NOTE]
> **Duplicate filename handling**: If a file with the same name already exists in the Sea, the upload endpoint should reject with 409 Conflict, consistent with the existing Self upload behaviour.

---

# 8. Future Work (Out of Scope)

These are explicitly NOT part of this plan:

- Archive/drop `knowledge_doc` and `knowledge_chunk` tables from `zeon7_self_dev`
- Bulk upload (multiple files at once)
- Web-based file editor for Lore Sea documents
- Drag-and-drop reordering of Lore Sea folders
- PDF upload via the web UI (worker supports it; UI doesn't expose it yet)
- Real-time ingestion progress (WebSocket/SSE streaming)

