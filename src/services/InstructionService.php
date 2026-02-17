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
    /**
     * Get the latest instruction version
     */
    public function getCurrentVersion(): ?array {
        $sql = "SELECT * FROM system_instructions 
                WHERE is_active = 1
                ORDER BY created_at DESC 
                LIMIT 1";
        $row = $this->fetchOne($sql);
        
        if ($row) {
            // Map to expected format
            return [
                'version' => $row['id'], // Use ID as version for now
                'content' => $row['content'],
                'created_at' => $row['created_at'],
                'created_by' => 'system' // Default since not in table
            ];
        }
        return null;
    }
    
    /**
     * Get specific instruction version by version number
     */
    public function getVersionById(int $version): ?array {
        $sql = "SELECT * FROM system_instructions WHERE id = ?";
        return $this->fetchOne($sql, [$version]);
    }
    
    /**
     * Get all instruction versions
     */
    public function getAllVersions(): array {
        $sql = "SELECT id as version, content, created_at, 'system' as created_by 
                FROM system_instructions 
                ORDER BY created_at DESC";
        return $this->fetchAll($sql);
    }
    
    /**
     * Create new instruction version
     */
    public function createVersion(string $content, string $createdBy = 'system'): int {
        // Deactivate old active ones if we want single active? 
        // For now just insert. `getCurrentVersion` filters by is_active=1 and order by desc.
        
        // Optional: Set others to inactive ??
        // $this->executeQuery("UPDATE system_instructions SET is_active = 0");

        $sql = "INSERT INTO system_instructions (content, type, is_active) 
                VALUES (?, 'core', 1)";
        
        $this->executeQuery($sql, [$content]);
        return (int) $this->lastInsertId();
    }
    
    /**
     * Calculate next version number
     */
    public function getNextVersionNumber(): int {
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 as next_version 
                FROM system_instructions";
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
