-- Zeon7 Database Migration - Enhanced Schema
-- Database: zeon7_self_dev
-- Run: mysql -u zeon7sql -p zeon7_self_dev < migration.sql

-- Knowledge file metadata (files stay on disk)
CREATE TABLE IF NOT EXISTS knowledge_doc (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL UNIQUE,
  file_hash VARCHAR(64) NOT NULL COMMENT 'SHA-256 hash for change detection',
  file_size INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_filename (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Knowledge chunks for selective retrieval
CREATE TABLE IF NOT EXISTS knowledge_chunk (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  doc_id BIGINT NOT NULL,
  heading VARCHAR(255),
  content MEDIUMTEXT NOT NULL,
  chunk_order INT NOT NULL DEFAULT 0,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (doc_id) REFERENCES knowledge_doc(id) ON DELETE CASCADE,
  INDEX idx_doc_order (doc_id, chunk_order),
  FULLTEXT(content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Versioned instruction set
CREATE TABLE IF NOT EXISTS instruction_set (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  version INT NOT NULL,
  content MEDIUMTEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_by VARCHAR(100),
  UNIQUE KEY uq_version (version),
  INDEX idx_created_at (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mutable memory (lore)
CREATE TABLE IF NOT EXISTS lore (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(255) NOT NULL UNIQUE,
  `value` MEDIUMTEXT NOT NULL,
  version INT NOT NULL DEFAULT 1,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_updated (updated_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Generated posts (full blog content)
CREATE TABLE IF NOT EXISTS posts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  content MEDIUMTEXT NOT NULL,
  source_url TEXT,
  status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published_at DATETIME,
  INDEX idx_status_published (status, published_at DESC),
  INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Image prompts extracted from a post
CREATE TABLE IF NOT EXISTS image_prompt (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT NOT NULL,
  prompt TEXT NOT NULL,
  prompt_order INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  INDEX idx_post_order (post_id, prompt_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API usage tracking for rate limiting
CREATE TABLE IF NOT EXISTS api_usage (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  endpoint VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  user_agent TEXT,
  request_count INT NOT NULL DEFAULT 1,
  window_start DATETIME NOT NULL,
  INDEX idx_endpoint_ip_window (endpoint, ip_address, window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gemini API call logging
CREATE TABLE IF NOT EXISTS gemini_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  endpoint VARCHAR(100) NOT NULL,
  prompt_tokens INT NOT NULL,
  response_tokens INT NOT NULL,
  total_tokens INT NOT NULL,
  model VARCHAR(50) NOT NULL,
  status ENUM('success', 'error') NOT NULL,
  error_message TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_created_at (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- End of migration
