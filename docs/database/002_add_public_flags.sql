-- Migration: Add Public Flags & Refactor Lore
-- Description: Adds is_public to knowledge_doc and refactors lore table for Journaling.

-- 1. Add is_public to knowledge_doc
ALTER TABLE knowledge_doc 
ADD COLUMN is_public BOOLEAN NOT NULL DEFAULT FALSE AFTER file_size;

-- 2. Refactor lore table
-- We are moving from a Key-Value store to a Time-Series Journal.
-- Existing data in `value` will be preserved in `content`. `key` will be moved to `tags` as a JSON string.

-- Step A: Add new columns
ALTER TABLE lore
ADD COLUMN type ENUM('memory', 'journal', 'admin_note') NOT NULL DEFAULT 'memory' AFTER id,
ADD COLUMN content MEDIUMTEXT NOT NULL AFTER type,
ADD COLUMN tags JSON AFTER content,
ADD COLUMN is_public BOOLEAN NOT NULL DEFAULT FALSE AFTER tags,
ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER is_public;

-- Step B: Migrate existing data (if any)
-- Copy `value` to `content`
UPDATE lore SET content = `value`;
-- Convert `key` to a tag: ["legacy_key:KEY_NAME"]
UPDATE lore SET tags = JSON_ARRAY(CONCAT('legacy_key:', `key`));

-- Step C: Drop old columns
ALTER TABLE lore
DROP COLUMN `key`,
DROP COLUMN `value`,
DROP COLUMN version;

-- Step D: Add new indexes
CREATE INDEX idx_lore_type ON lore(type);
CREATE INDEX idx_lore_public ON lore(is_public);
CREATE INDEX idx_lore_created ON lore(created_at DESC);

-- 3. Add is_public to knowledge_chunk (Optional, but good for granular control later)
-- For now, we control at the Document level (knowledge_doc).
