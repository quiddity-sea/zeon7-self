<?php
/**
 * InstructionService - Manage versioned instruction sets (AI prompts)
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class InstructionService extends BaseService {
    
    /**
     * Get the latest instruction version
     */
    public function getCurrentVersion(): ?array {
        $sql = "SELECT * FROM instruction_set 
                ORDER BY version DESC 
                LIMIT 1";
        return $this->fetchOne($sql);
    }
    
    /**
     * Get specific instruction version by version number
     */
    public function getVersionById(int $version): ?array {
        $sql = "SELECT * FROM instruction_set WHERE version = ?";
        return $this->fetchOne($sql, [$version]);
    }
    
    /**
     * Get all instruction versions
     */
    public function getAllVersions(): array {
        $sql = "SELECT version, content, created_at, created_by 
                FROM instruction_set 
                ORDER BY version DESC";
        return $this->fetchAll($sql);
    }
    
    /**
     * Create new instruction version
     */
    public function createVersion(string $content, string $createdBy = 'system'): int {
        $nextVersion = $this->getNextVersionNumber();
        
        $sql = "INSERT INTO instruction_set (version, content, created_by) 
                VALUES (?, ?, ?)";
        
        $this->executeQuery($sql, [$nextVersion, $content, $createdBy]);
        return $nextVersion;
    }
    
    /**
     * Calculate next version number
     */
    public function getNextVersionNumber(): int {
        $sql = "SELECT COALESCE(MAX(version), 0) + 1 as next_version 
                FROM instruction_set";
        $result = $this->fetchOne($sql);
        return (int) $result['next_version'];
    }
    
    /**
     * Get content of latest version (convenience method)
     */
    public function getCurrentContent(): ?string {
        $current = $this->getCurrentVersion();
        return $current['content'] ?? null;
    }
    /**
     * Ingest instructions from file system
     * Scans the instructions/ directory for the most recent .md file
     */
    public function ingestFromFolder(string $directoryPath): array {
        if (!is_dir($directoryPath)) {
            return ['success' => false, 'message' => "Directory not found: $directoryPath"];
        }

        // 1. Find most recent .md file
        $files = glob($directoryPath . '/*.md');
        if (empty($files)) {
            return ['success' => false, 'message' => "No .md files found in instructions/"];
        }

        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latestFile = $files[0];
        $content = file_get_contents($latestFile);
        $filename = basename($latestFile);

        if ($content === false) {
            return ['success' => false, 'message' => "Failed to read file: $filename"];
        }

        // 2. Compare with current DB version
        $current = $this->getCurrentVersion();
        $currentContent = $current['content'] ?? '';

        // Normalize line endings for comparison
        $content = str_replace("\r\n", "\n", $content);
        $currentContent = str_replace("\r\n", "\n", $currentContent);

        if (trim($content) !== trim($currentContent)) {
            // 3. Create new version
            $newVersion = $this->createVersion($content, "ingest:$filename");
            return [
                'success' => true, 
                'ingested' => true, 
                'version' => $newVersion, 
                'file' => $filename,
                'message' => "Ingested new version from $filename"
            ];
        }

        return ['success' => true, 'ingested' => false, 'message' => "Database is up to date with $filename"];
    }
}
