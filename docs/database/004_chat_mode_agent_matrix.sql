-- Migration 004: Chat Mode & Per-Agent Engine Matrix
USE zeon7_self_dev;

-- Default agent assignments for the two chat modes
INSERT INTO config (key_name, value) VALUES 
  ('public_chat_agent', 'zeon7'),
  ('authenticated_default_agent', 'zeon7')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Per-Agent Provider & Model Specifications (Decoupled Defaults)
INSERT INTO config (key_name, value) VALUES 
  ('agent_zeon7_provider', 'gemini'),
  ('agent_zeon7_model', 'gemini-2.5-flash'),
  ('agent_zeon7_think', '0'),

  ('agent_leon_provider', 'openrouter'),
  ('agent_leon_model', 'openai/gpt-4'),
  ('agent_leon_think', '0'),

  ('agent_gemma_provider', 'gemini'),
  ('agent_gemma_model', 'gemini-2.5-flash'),
  ('agent_gemma_think', '0'),

  ('agent_otec_provider', 'openrouter'),
  ('agent_otec_model', 'deepseek/deepseek-chat'),
  ('agent_otec_think', '0'),

  ('agent_wolf_provider', 'ollama'),
  ('agent_wolf_model', 'Brain32:latest'),
  ('agent_wolf_think', '0')
ON DUPLICATE KEY UPDATE value = VALUES(value);
