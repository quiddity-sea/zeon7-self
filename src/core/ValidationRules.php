<?php
/**
 * ValidationRules - Reusable validation rule sets
 */
class ValidationRules {
    /**
     * Post creation/update validation rules
     */
    public static function postRules(): array {
        return [
            'title' => ['required' => true, 'max_length' => 255, 'min_length' => 3],
            'slug' => [
                'required' => true,
                'max_length' => 255,
                'pattern' => '/^[a-z0-9-]+$/' // URL-safe slug
            ],
            'content' => ['required' => true, 'min_length' => 10],
            'source_url' => ['required' => false, 'type' => 'url']
        ];
    }
    
    /**
     * Knowledge upload validation rules
     */
    public static function knowledgeUploadRules(): array {
        return [
            'file' => [
                'required' => true,
                'mime_types' => ['text/markdown', 'text/plain'],
                'max_size' => 10485760, // 10MB in bytes
                'extensions' => ['md', 'txt']
            ]
        ];
    }
    
    /**
     * Lore entry validation rules
     */
    public static function loreRules(): array {
        return [
            'key' => ['required' => true, 'max_length' => 255, 'pattern' => '/^[a-z0-9_-]+$/i'],
            'value' => ['required' => true]
        ];
    }
    
    /**
     * Instruction set validation rules
     */
    public static function instructionRules(): array {
        return [
            'content' => ['required' => true, 'min_length' => 100]
        ];
    }
    
    /**
     * Chat message validation rules
     */
    public static function chatRules(): array {
        return [
            'message' => ['required' => true, 'max_length' => 2000, 'min_length' => 1]
        ];
    }
}
