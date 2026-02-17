<?php
/**
 * SystemResetService.php
 * Handles "Factory Reset" by reloading initialization data from restart/ folders.
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/LoreService.php';
require_once __DIR__ . '/KnowledgeService.php';

class SystemResetService extends BaseService {
    
    private LoreService $loreService;
    private KnowledgeService $knowledgeService;
    
    public function __construct() {
        parent::__construct();
        $this->loreService = new LoreService();
        $this->knowledgeService = new KnowledgeService();
    }
    
    /**
     * Perform full system reset
     * @return array Status report
     */
    public function resetAll(): array {
        $report = [];
        
        try {
            $this->beginTransaction();
            
            // 1. Clear Instructions
            $this->clearTable('system_instructions');
            $report[] = $this->loadInstructions();
            
            // 2. Clear & Reload Lore
            $this->clearTable('lore');
            $report[] = $this->loadLore();
            
            // 3. Clear & Reload Knowledge
            $this->clearTable('knowledge_chunk');
            $this->clearTable('knowledge_doc');
            $report[] = $this->loadKnowledge();
            
            // 4. Clear Chat Logs (Optional? Usually yes for factory reset)
            $this->clearTable('chat_logs');
            $report[] = "Chat logs cleared.";
            
            $this->commit();
            return ['success' => true, 'report' => $report];
            
        } catch (Exception $e) {
            $this->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function clearTable(string $inputTable): void {
        // Whitelist allowed tables to prevent SQL injection or accidental deletion of critical sys tables
        $validTables = ['system_instructions', 'lore', 'knowledge_chunk', 'knowledge_doc', 'chat_logs'];
        
        // Simple validation
        if (!in_array($inputTable, $validTables)) {
            throw new Exception("Invalid table for reset: $inputTable");
        }
        
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->db->exec("TRUNCATE TABLE `$inputTable`");
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    private function loadInstructions(): string {
        $path = __DIR__ . '/../../instructions/restart/current-instructions.md';
        if (!file_exists($path)) return "Skipped Instructions: File not found.";
        
        $content = file_get_contents($path);
        
        // Very basic insert - assumes simple structure or just stores the whole blob
        // Checking schema of system_instructions... assuming 'content' column
        // If table doesn't exist, we might need to handle that, but assuming it does.
        
        $stmt = $this->db->prepare("INSERT INTO system_instructions (content, type, is_active) VALUES (?, 'core', 1)");
        $stmt->execute([$content]);
        
        return "Instructions loaded from current-instructions.md";
    }
    
    private function loadLore(): string {
        $dir = __DIR__ . '/../../lore/restart/';
        if (!is_dir($dir)) return "Skipped Lore: Directory not found.";
        
        $files = glob($dir . '*.md');
        $count = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);
            
            // Simple parsing logic: 
            // If Zeon7_ProfileSheet.md => Type: Identity
            // Else => Type: Backstory
            
            $type = 'general';
            if (strpos($filename, 'ProfileSheet') !== false) $type = 'identity';
            if (strpos($filename, 'Biography') !== false) $type = 'backstory';
            
            $this->loreService->create($type, $content, ['restart', 'core'], true); // Public? Maybe true for core identity
            $count++;
        }
        
        return "Lore loaded: $count files processed.";
    }
    
    private function loadKnowledge(): string {
        $dir = __DIR__ . '/../../knowledge/restart/';
        if (!is_dir($dir)) return "Skipped Knowledge: Directory not found.";
        
        $files = glob($dir . '*.md');
        $count = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);
            $size = strlen($content);
            $hash = md5($content);
            
            // 1. Create Doc
            $docId = $this->knowledgeService->uploadFile($filename, "Initial content", $hash, $size, true);
            
            // 2. Chunk (Simple split by double newline for now)
            $chunksRaw = explode("\n\n", $content);
            $chunks = [];
            foreach ($chunksRaw as $chunkText) {
                if (trim($chunkText) === '') continue;
                $chunks[] = [
                    'heading' => 'Section', // Could improve parsing later
                    'content' => substr($chunkText, 0, 2000) // Truncate to safe limit
                ];
            }
            
            $this->knowledgeService->chunkFile($docId, $chunks);
            $count++;
        }
        
        return "Knowledge loaded: $count documents processed.";
    }
}
