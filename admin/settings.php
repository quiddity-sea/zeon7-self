<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings & Keyring — Zeon7 Admin</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=15.0">
    <style>
        .settings-container { 
            padding: 1.5rem 2rem; 
            max-width: 1600px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-group { margin-bottom: 1.5rem; }
        .helper-text {
            display: block;
            margin-top: 0.35rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }
        .hud-toggle-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: rgba(34, 211, 238, 0.05);
            border: 1px solid rgba(34, 211, 238, 0.2);
            border-radius: var(--radius-sm);
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'SYSTEM SETTINGS';
        $pageSubtitle = 'AI PROVIDER & ENCRYPTION CONFIGURATION';
        include 'components/header.php';
        ?>

        <div class="settings-container">
            <!-- Left Column: Config Form -->
            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(34, 211, 238, 0.2); margin-bottom: 1.5rem; padding-bottom: 0.5rem;">
                    <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: var(--color-cyan);">
                        AI ENGINE PARAMETERS
                    </span>
                    <div style="display: flex; gap: 0.5rem;">
                        <span id="keyStatus" class="hud-badge" style="font-size: 0.65rem;">KEY: CHECKING...</span>
                        <span id="aiStatus" class="hud-badge green" style="font-size: 0.65rem;">AI: WAITING...</span>
                    </div>
                </div>
                
                <form id="settingsForm">
                    <div class="form-group">
                        <label for="provider">AI Neural Provider</label>
                        <select id="provider" class="input-box">
                            <option value="ollama">Local Ollama AI Engine (Brain32:latest)</option>
                            <option value="gemini">Google Gemini AI</option>
                            <option value="openrouter">OpenRouter Multi-LLM</option>
                        </select>
                        <span class="helper-text">Select the backend engine for Zeon7 operations.</span>
                    </div>

                    <div class="form-group">
                        <label for="model">Model Specification</label>
                        
                        <!-- Ollama Dropdown -->
                        <select id="ollamaModel" class="input-box" style="display: none;">
                            <option value="Brain32:latest">Brain32:latest (Local Qwen3.5 9B)</option>
                            <option value="tripolskypetr/qwen3.5-uncensored-aggressive:9b">qwen3.5-uncensored-aggressive:9b</option>
                            <option value="Zeon7-Gemma:64k">Zeon7-Gemma:64k</option>
                            <option value="fredrezones55/Gemma-4-Uncensored-HauhauCS-Aggressive:e2b-SCN">Gemma-4-Uncensored</option>
                        </select>

                        <!-- Gemini Dropdown -->
                        <select id="geminiModel" class="input-box" style="display: none;">
                            <option value="gemini-2.5-flash">Gemini 2.5 Flash (Recommended)</option>
                            <option value="gemini-2.5-pro">Gemini 2.5 Pro (Deep Reasoning)</option>
                            <option value="gemini-flash-latest">Gemini Flash Latest</option>
                        </select>

                        <!-- OpenRouter Input -->
                        <input type="text" id="customModel" class="input-box" placeholder="e.g. anthropic/claude-3.5-sonnet" style="display: none;">
                        
                        <span class="helper-text" id="modelHelp">Select or specify the target LLM version.</span>
                    </div>

                    <!-- Ollama Think Toggle -->
                    <div class="form-group" id="ollamaThinkGroup" style="display: none;">
                        <label>Reasoning Scratchpad Protocol</label>
                        <div class="hud-toggle-container">
                            <input type="checkbox" id="ollamaThink" style="width: 20px; height: 20px; accent-color: var(--color-cyan); cursor: pointer;">
                            <div>
                                <span id="thinkStatusText" style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--color-cyan); display: block;">
                                    THINK: FALSE (--think=false)
                                </span>
                                <span class="helper-text" style="margin-top: 0.15rem;">
                                    When disabled (recommended), model outputs instant, direct responses without raw thought chains.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="apiKeyGroup">
                        <label for="apiKey">API Access Key (Encrypted in MariaDB)</label>
                        <div style="position: relative;">
                            <input type="password" id="apiKey" class="input-box" placeholder="••••••••••••••••" style="padding-right: 40px;">
                            <button type="button" id="toggleKeyBtn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; z-index: 10;">
                                👁
                            </button>
                        </div>
                        <span class="helper-text" id="apiKeyHelp">Enter new key to update keyring. Empty keeps current key.</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
                        <button type="button" class="btn btn-secondary" id="testAiBtn">TEST CONNECTION</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">UPDATE SYSTEM PROTOCOLS</button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Live Terminal -->
            <?php include 'components/terminal-panel.php'; ?>

            <!-- Danger Zone: Factory Reset (Full Width) -->
            <div class="hud-border" style="grid-column: 1 / -1; border-color: rgba(244, 63, 94, 0.4);" data-tilt>
                <div class="hud-corner-tr" style="background: var(--color-coral); box-shadow: 0 0 8px var(--color-coral);"></div>
                <div class="hud-corner-bl" style="background: var(--color-coral); box-shadow: 0 0 8px var(--color-coral);"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(244, 63, 94, 0.2); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                    <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: var(--color-coral);">
                        ⚠️ EMERGENCY SYSTEM RE-INITIALISATION
                    </span>
                    <span class="hud-badge orange" style="font-size: 0.65rem;">DESTRUCTIVE</span>
                </div>
                
                <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.5rem;">
                    Factory Reset will purge all Lore facts, Knowledge vectors, and System Instructions from the active MariaDB database and reload clean baseline configurations. This operation cannot be undone.
                </p>
                
                <div style="display: flex; justify-content: flex-end;">
                    <button type="button" class="btn btn-danger" id="resetSystemBtn">EXECUTE FACTORY SYSTEM RESET</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/app.js"></script>
<script src="js/settings.js?v=15.0"></script>
<script>
    if (typeof App !== 'undefined') App.requireAuth();
    document.addEventListener('DOMContentLoaded', () => {
        Settings.init();
    });
</script>
</body>
</html>