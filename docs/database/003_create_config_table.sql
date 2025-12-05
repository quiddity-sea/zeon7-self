-- Create config table for storing application settings
CREATE TABLE IF NOT EXISTS `config` (
    `key_name` VARCHAR(255) NOT NULL PRIMARY KEY,
    `value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default values if not exist (optional, but good for structure)
INSERT IGNORE INTO `config` (`key_name`, `value`) VALUES 
('provider', 'gemini'),
('gemini_model', 'gemini-pro'),
('openrouter_model', 'openai/gpt-4');
