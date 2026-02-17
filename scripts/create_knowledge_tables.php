<?php
// Manually load .env since we are in CLI
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(sprintf('%s=%s', trim($parts[0]), trim($parts[1])));
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}

require_once __DIR__ . '/../src/core/DatabaseService.php';

try {
    $db = DatabaseService::getInstance();
    echo "Checking Knowledge tables...\n";

    // 1. knowledge_doc
    $sqlDoc = "CREATE TABLE IF NOT EXISTS knowledge_doc (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        description TEXT,
        file_hash VARCHAR(64) NOT NULL,
        file_size INT DEFAULT 0,
        is_public TINYINT(1) DEFAULT 0,
        processed TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sqlDoc);
    echo "Table 'knowledge_doc' checked.\n";

    // 2. knowledge_chunk
    $sqlChunk = "CREATE TABLE IF NOT EXISTS knowledge_chunk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doc_id INT NOT NULL,
        chunk_index INT NOT NULL,
        heading VARCHAR(255),
        content TEXT NOT NULL,
        embedding BLOB,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (doc_id) REFERENCES knowledge_doc(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sqlChunk);
    echo "Table 'knowledge_chunk' checked.\n";

    // Add FULLTEXT if missing (might have failed in previous script if table didn't exist)
    try {
        $stmt = $db->query("SHOW INDEX FROM knowledge_chunk WHERE Key_name = 'idx_content_fulltext'");
        if (!$stmt->fetch()) {
            $db->exec("ALTER TABLE knowledge_chunk ADD FULLTEXT INDEX idx_content_fulltext (content)");
            echo "Added FULLTEXT index to knowledge_chunk.\n";
        }
    } catch (Exception $e) { /* Ignore index errors */ }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
