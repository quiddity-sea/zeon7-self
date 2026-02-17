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
    echo "Fixing Knowledge tables...\n";

    // Disable foreign keys to allow dropping parents
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // DROP tables to ensure clean slate with correct schema
    $db->exec("DROP TABLE IF EXISTS knowledge_chunk");
    $db->exec("DROP TABLE IF EXISTS knowledge_doc");

    echo "Dropped existing tables.\n";

    // Re-enable FKs (though we are creating fresh)
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 1. knowledge_doc (Added is_public, description)
    $sqlDoc = "CREATE TABLE knowledge_doc (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        description TEXT,
        file_hash VARCHAR(64) NOT NULL,
        file_size INT DEFAULT 0,
        is_public TINYINT(1) DEFAULT 0,
        processed TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sqlDoc);
    echo "Table 'knowledge_doc' reformatted.\n";

    // 2. knowledge_chunk (Using chunk_index)
    $sqlChunk = "CREATE TABLE knowledge_chunk (
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
    echo "Table 'knowledge_chunk' reformatted.\n";

    // Add FULLTEXT
    try {
        $db->exec("ALTER TABLE knowledge_chunk ADD FULLTEXT INDEX idx_content_fulltext (content)");
        echo "Added FULLTEXT index.\n";
    } catch (Exception $e) { /* Ignore if somehow exists */ }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
