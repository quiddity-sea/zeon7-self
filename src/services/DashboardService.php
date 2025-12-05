<?php
/**
 * DashboardService - Aggregates data for the admin dashboard
 */

require_once __DIR__ . '/../core/BaseService.php';

class DashboardService extends BaseService {
    
    /**
     * Get total API requests from gemini_log
     */
    public function getApiRequestCount(): int {
        // Check if table exists first to avoid errors during setup
        try {
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM gemini_log");
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total knowledge documents
     */
    public function getKnowledgeCount(): int {
        try {
            $result = $this->fetchOne("SELECT COUNT(*) as count FROM knowledge_doc");
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get preview of active instruction set (first 75 words)
     */
    public function getActiveInstructionPreview(): string {
        try {
            $result = $this->fetchOne("SELECT content FROM instruction_set ORDER BY version DESC LIMIT 1");
            if (!$result) return "No instructions found.";

            $content = $result['content'];
            $words = explode(' ', $content);
            if (count($words) > 25) {
                return implode(' ', array_slice($words, 0, 25)) . '...';
            }
            return $content;
        } catch (Exception $e) {
            return "Error loading instructions.";
        }
    }

    /**
     * Get daily theme (Mock for now)
     */
    /**
     * Get daily theme from database
     */
    /**
     * Get daily theme from database
     */
    public function getDailyTheme(): array {
        $day = date('l');
        $sql = "SELECT theme, tone, tagline FROM daily_context WHERE day_name = ?";
        
        try {
            $result = $this->fetchOne($sql, [$day]);
            if ($result) {
                return $result;
            }
        } catch (Exception $e) {
            // Fallback if DB fails
        }

        return [
            'theme' => 'General News',
            'tone' => 'Neutral',
            'tagline' => 'System Default'
        ];
    }

    /**
     * Get scanned leads (Mock for now)
     */
    public function getScannedLeads(): array {
        // TODO: Implement real lead tracking
        // Return empty array to simulate "No scans"
        return []; 
    }
}
