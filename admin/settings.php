<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Mission Control — System Settings</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .settings-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.8rem;
            font-family: var(--font-mono);
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .helper-text {
            font-size: 0.725rem;
            color: var(--text-muted);
            margin-top: 0.35rem;
            display: block;
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
        .settings-tabs {
            display: flex;
            gap: 0.4rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid rgba(34, 211, 238, 0.2);
            padding-bottom: 0.75rem;
            flex-wrap: wrap;
        }
        .env-key-card {
            background: rgba(10, 14, 23, 0.6);
            border: 1px solid rgba(34, 211, 238, 0.18);
            border-radius: var(--radius-sm);
            padding: 0.75rem 0.85rem;
            transition: all 0.2s ease;
        }
        .env-key-card:hover {
            border-color: rgba(34, 211, 238, 0.4);
            background: rgba(10, 14, 23, 0.8);
        }
        .env-key-card.deleted {
            opacity: 0.4;
            border-color: rgba(244, 63, 94, 0.4);
            background: rgba(244, 63, 94, 0.05);
            text-decoration: line-through;
        }
        .env-action-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.15s;
        }
        .env-action-btn:hover {
            background: rgba(34, 211, 238, 0.15);
            border-color: var(--color-cyan);
            color: var(--color-cyan);
        }
        .env-action-btn.delete:hover {
            background: rgba(244, 63, 94, 0.2);
            border-color: var(--color-coral);
            color: var(--color-coral);
        }
        .env-category-tabs {
            display: flex;
            gap: 0.35rem;
            margin-bottom: 0.85rem;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 0.4rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(34, 211, 238, 0.3) transparent;
        }
        .env-category-tabs::-webkit-scrollbar {
            height: 4px;
        }
        .env-category-tabs::-webkit-scrollbar-thumb {
            background: rgba(34, 211, 238, 0.3);
            border-radius: 2px;
        }
        .env-cat-pill {
            background: rgba(10, 14, 23, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .env-cat-pill:hover {
            border-color: rgba(34, 211, 238, 0.4);
            color: #ffffff;
        }
        .env-cat-pill.active {
            background: rgba(34, 211, 238, 0.15);
            border-color: var(--color-cyan);
            color: var(--color-cyan);
            box-shadow: 0 0 8px rgba(34, 211, 238, 0.25);
        }
        .env-cat-pill .cat-count {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-secondary);
            font-size: 0.6rem;
            padding: 1px 5px;
            border-radius: 10px;
            margin-left: 0.15rem;
        }
        .env-cat-pill.active .cat-count {
            background: rgba(34, 211, 238, 0.25);
            color: var(--color-cyan);
            font-weight: 700;
        }
        .settings-tab-btn {
            background: rgba(10, 14, 23, 0.6);
            border: 1px solid rgba(34, 211, 238, 0.2);
            color: var(--text-muted);
            padding: 0.45rem 0.85rem;
            border-radius: var(--radius-sm);
            font-family: var(--font-mono);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s ease;
        }
        .settings-tab-btn:hover {
            border-color: var(--color-cyan);
            color: #ffffff;
        }
        .settings-tab-btn.active {
            background: rgba(34, 211, 238, 0.12);
            border-color: var(--color-cyan);
            color: var(--color-cyan);
            box-shadow: 0 0 10px rgba(34, 211, 238, 0.25);
        }
        .settings-tab-pane {
            animation: fadeInTab 0.15s ease-out;
        }
        @keyframes fadeInTab {
            from { opacity: 0; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
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
                    <!-- 4-TAB NAVIGATION -->
                    <div class="settings-tabs">
                        <button type="button" class="settings-tab-btn active" data-tab="tab-defaults" id="tabBtnDefaults">
                            <span>⚙️</span> DEFAULTS
                        </button>
                        <button type="button" class="settings-tab-btn" data-tab="tab-public" id="tabBtnPublic">
                            <span>🌐</span> PUBLIC
                        </button>
                        <button type="button" class="settings-tab-btn" data-tab="tab-auth" id="tabBtnAuth">
                            <span>🔐</span> AUTHENTICATED
                        </button>
                        <button type="button" class="settings-tab-btn" data-tab="tab-api-keys" id="tabBtnApiKeys">
                            <span>🔑</span> APIS &amp; KEYS
                        </button>
                    </div>

                    <!-- TAB 1: DEFAULTS -->
                    <div class="settings-tab-pane" id="tab-defaults">
                        <div style="margin-bottom: 1rem;">
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-cyan); display: block;">
                                // GLOBAL AI ENGINE & KEYRING VAULT
                            </span>
                            <span class="helper-text">Global API keys and fallback provider for general system tasks and background processes.</span>
                        </div>

                        <div class="form-group">
                            <label for="provider">Global AI Neural Provider</label>
                            <select id="provider" class="input-box">
                                <option value="ollama">Local Ollama AI Engine (Brain32:latest)</option>
                                <option value="gemini">Google Gemini AI</option>
                                <option value="openrouter">OpenRouter Multi-LLM</option>
                            </select>
                            <span class="helper-text">Select fallback provider when an agent has no specific override.</span>
                        </div>

                        <div class="form-group">
                            <label for="model">Global Model Specification</label>
                            
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

                        <!-- Ollama Host / Tailscale Tunnel Endpoint -->
                        <div class="form-group" id="ollamaHostGroup" style="display: none;">
                            <label for="ollamaHost">Ollama Host / Tailscale Tunnel Endpoint</label>
                            <input type="text" id="ollamaHost" class="input-box" placeholder="http://127.0.0.1:11434">
                            <span class="helper-text">Use <code>http://127.0.0.1:11434</code> for local machine or <code>http://100.x.y.z:11434</code> for Tailscale VPN offloading.</span>
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
                    </div>

                    <!-- TAB 2: PUBLIC -->
                    <div class="settings-tab-pane" id="tab-public" style="display: none;">
                        <div style="margin-bottom: 1rem;">
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-cyan); display: block;">
                                // PUBLIC VISITOR CHAT MODE (MODE A)
                            </span>
                            <span class="helper-text">Configures the Neural Link for anonymous public visitors on the FBOX: SELF Landing Page.</span>
                        </div>

                        <div class="form-group">
                            <label for="publicChatAgent">Public Greeting Agent</label>
                            <select id="publicChatAgent" class="input-box">
                                <option value="zeon7">Zeon7 (The Curator)</option>
                                <option value="leon">Leon (The Producer)</option>
                                <option value="gemma">Gemma (The Coach)</option>
                                <option value="otec">Otec (The Director)</option>
                                <option value="wolf">Wolf (Research Swarm)</option>
                            </select>
                            <span class="helper-text">The default persona greeting visitors on the landing page. Visitors are locked to this persona and the switcher dropdown is hidden.</span>
                        </div>

                        <div style="background: rgba(34, 211, 238, 0.04); border: 1px dashed rgba(34, 211, 238, 0.3); border-radius: 4px; padding: 1rem; margin-top: 1.25rem;">
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-cyan); display: block; margin-bottom: 0.5rem;">
                                🛡️ PUBLIC ISOLATION & TOOL GUARDS
                            </span>
                            <p style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5; margin: 0;">
                                • Mode A executes through the guarded MCP loop with cloud execution (Gemini), ensuring zero local GPU load.<br>
                                • Public visitors cannot switch agents or access private operator tools.<br>
                                • Header clearly states <code>[AGENT] NEURAL LINK — PUBLIC CHAT</code>.
                            </p>
                        </div>
                    </div>

                    <!-- TAB 3: AUTHENTICATED -->
                    <div class="settings-tab-pane" id="tab-auth" style="display: none;">
                        <div style="margin-bottom: 1rem;">
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-gold); display: block;">
                                // AUTHENTICATED OPERATOR CHAT MODE (MODE B)
                            </span>
                            <span class="helper-text">Configures operator defaults and the per-agent dynamic engine matrix.</span>
                        </div>

                        <div class="form-group">
                            <label for="authChatAgent">Initial Default Operator Agent</label>
                            <select id="authChatAgent" class="input-box">
                                <option value="zeon7">Zeon7 (The Curator)</option>
                                <option value="leon">Leon (The Producer)</option>
                                <option value="gemma">Gemma (The Coach)</option>
                                <option value="otec">Otec (The Director)</option>
                                <option value="wolf">Wolf (Research Swarm)</option>
                            </select>
                            <span class="helper-text">Initial agent active when you first open the chat after logging into Admin.</span>
                        </div>

                        <div style="border-top: 1px solid rgba(245, 158, 11, 0.2); margin-top: 1.25rem; padding-top: 1rem;">
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-gold); display: block; margin-bottom: 0.5rem;">
                                // PER-AGENT ENGINE MATRIX (HERMES PROFILES)
                            </span>
                            <span class="helper-text" style="margin-bottom: 0.75rem;">Configure the underlying AI provider and model for each Council agent:</span>

                            <div class="form-group">
                                <label for="configAgentSelect">Select Agent to Configure</label>
                                <select id="configAgentSelect" class="input-box">
                                    <option value="zeon7">⚡ Zeon7 (Curator)</option>
                                    <option value="leon">🛠️ Leon (Producer)</option>
                                    <option value="gemma">🧭 Gemma (Coach)</option>
                                    <option value="otec">👁️ Otec (Director)</option>
                                    <option value="wolf">🐺 Wolf (Research Swarm)</option>
                                </select>
                            </div>

                            <!-- Active Agent Engine Settings Card -->
                            <div id="agentEngineCard" style="border: 1px dashed rgba(245, 158, 11, 0.35); padding: 1rem; border-radius: 4px; background: rgba(245, 158, 11, 0.03); margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label for="agentProvider">AI Neural Provider</label>
                                    <select id="agentProvider" class="input-box">
                                        <option value="gemini">Google Gemini AI</option>
                                        <option value="ollama">Local / Remote Ollama (Tailscale)</option>
                                        <option value="openrouter">OpenRouter Multi-LLM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="agentProvider">Model Specification</label>
                                    <!-- Agent Ollama Dropdown -->
                                    <select id="agentOllamaModel" class="input-box" style="display: none;">
                                        <option value="Brain32:latest">Brain32:latest (Local Qwen3.5 9B)</option>
                                        <option value="tripolskypetr/qwen3.5-uncensored-aggressive:9b">qwen3.5-uncensored-aggressive:9b</option>
                                        <option value="Zeon7-Gemma:64k">Zeon7-Gemma:64k</option>
                                        <option value="fredrezones55/Gemma-4-Uncensored-HauhauCS-Aggressive:e2b-SCN">Gemma-4-Uncensored</option>
                                    </select>

                                    <!-- Agent Gemini Dropdown -->
                                    <select id="agentGeminiModel" class="input-box" style="display: none;">
                                        <option value="gemini-2.5-flash">Gemini 2.5 Flash (Recommended)</option>
                                        <option value="gemini-2.5-pro">Gemini 2.5 Pro (Deep Reasoning)</option>
                                        <option value="gemini-flash-latest">Gemini Flash Latest</option>
                                    </select>

                                    <!-- Agent OpenRouter / Custom Input -->
                                    <input type="text" id="agentCustomModel" class="input-box" placeholder="e.g. anthropic/claude-3.5-sonnet" style="display: none;">
                                    <span class="helper-text" id="agentModelHelp">Select target model for this agent frequency.</span>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div class="hud-toggle-container" style="margin-top: 0.25rem;">
                                        <input type="checkbox" id="agentThink" style="width: 18px; height: 18px; accent-color: var(--color-gold); cursor: pointer;">
                                        <span style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-secondary); margin-left: 0.5rem;">Enable Reasoning Mode (--think=true)</span>
                                    </div>
                                </div>
                            </div>

                            <div style="background: rgba(0,0,0,0.25); border-radius: 4px; padding: 0.75rem; border: 1px solid rgba(255,255,255,0.06);">
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">
                                    💡 In-Widget Switching: When logged in, you can switch between these agents dynamically via the dropdown in the live chat header.
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: APIS & KEYS (.ENV) -->
                    <div class="settings-tab-pane" id="tab-api-keys" style="display: none;">
                        <div style="margin-bottom: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.35rem;">
                                <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-cyan); display: block;">
                                    // ENVIRONMENT KEYRING &amp; INTEGRATION VAULT (.ENV)
                                </span>
                                <div style="display: flex; gap: 0.4rem;">
                                    <button type="button" class="btn btn-secondary" id="reloadEnvBtn" style="padding: 0.3rem 0.6rem; font-size: 0.7rem;">
                                        🔄 RELOAD
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="addEnvKeyBtn" style="padding: 0.3rem 0.6rem; font-size: 0.7rem; border-color: var(--color-cyan); color: var(--color-cyan);">
                                        ➕ ADD KEY
                                    </button>
                                </div>
                            </div>
                            <span class="helper-text">
                                Dynamically synced with <code>.env</code>. Edit, add, or delete keys on mobile or desktop. Changes update the environment configuration directly while preserving formatting and comments.
                            </span>
                        </div>

                        <!-- Domain Sub-Tabs / Category Filter Pills -->
                        <div class="env-category-tabs" id="envCategoryTabs">
                            <button type="button" class="env-cat-pill active" data-category="all" id="pill-all">
                                <span>🌐</span> ALL <span class="cat-count" id="count-all">0</span>
                            </button>
                            <button type="button" class="env-cat-pill" data-category="eye" id="pill-eye">
                                <span>👁️</span> THE EYE <span class="cat-count" id="count-eye">0</span>
                            </button>
                            <button type="button" class="env-cat-pill" data-category="auth" id="pill-auth">
                                <span>🔐</span> AUTH &amp; SECURITY <span class="cat-count" id="count-auth">0</span>
                            </button>
                            <button type="button" class="env-cat-pill" data-category="council" id="pill-council">
                                <span>🧠</span> COUNCIL &amp; CHAT <span class="cat-count" id="count-council">0</span>
                            </button>
                            <button type="button" class="env-cat-pill" data-category="system" id="pill-system">
                                <span>⚙️</span> SYSTEM &amp; DB <span class="cat-count" id="count-system">0</span>
                            </button>
                        </div>

                        <!-- Filter / Search Bar -->
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <input type="text" id="envSearchInput" class="input-box" placeholder="🔍 Filter keys (e.g. CARTO, CESIUM, TOMTOM, DB)..." style="font-size: 0.8rem; padding: 0.5rem 0.75rem;">
                        </div>

                        <!-- Add Key Inline Card (Hidden by default) -->
                        <div id="newKeyCard" style="display: none; border: 1px dashed var(--color-cyan); padding: 1rem; border-radius: 4px; background: rgba(34, 211, 238, 0.04); margin-bottom: 1.25rem;">
                            <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-cyan); display: block; margin-bottom: 0.75rem;">
                                ➕ REGISTER NEW CONFIGURATION KEY
                            </span>
                            <div class="form-group" style="margin-bottom: 0.75rem;">
                                <label for="newKeyName">Key Identifier (Monospace Uppercase)</label>
                                <input type="text" id="newKeyName" class="input-box" placeholder="e.g. NEW_SERVICE_API_KEY" style="font-family: var(--font-mono); text-transform: uppercase;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0.75rem;">
                                <label for="newKeyValue">Key Value</label>
                                <input type="text" id="newKeyValue" class="input-box" placeholder="e.g. secret_token_value">
                            </div>
                            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                                <button type="button" class="btn btn-secondary" id="cancelNewKeyBtn" style="padding: 0.35rem 0.75rem; font-size: 0.72rem;">CANCEL</button>
                                <button type="button" class="btn btn-primary" id="confirmNewKeyBtn" style="padding: 0.35rem 0.75rem; font-size: 0.72rem;">INSERT KEY</button>
                            </div>
                        </div>

                        <!-- Dynamic Keys Container -->
                        <div id="envLoadingIndicator" style="text-align: center; padding: 2rem; color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem;">
                            SYNCING KEYRING MATRIX...
                        </div>
                        <div id="envKeysContainer" style="display: flex; flex-direction: column; gap: 0.85rem;"></div>

                        <!-- Save Actions within Tab -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                            <span id="envStatusBadge" class="hud-badge" style="font-size: 0.65rem;">ENV: READY</span>
                            <button type="button" class="btn btn-primary" id="saveEnvBtn" style="min-width: 170px;">
                                UPDATE .ENV KEYRING
                            </button>
                        </div>
                    </div>

                    <!-- FOOTER ACTIONS (COMMON TO ALL TABS) -->
                    <div id="defaultFooterActions" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.08);">
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
<script src="js/settings.js?v=2.6"></script>
<script>
    if (typeof App !== 'undefined') App.requireAuth();
    document.addEventListener('DOMContentLoaded', () => {
        Settings.init();
    });
</script>
</body>
</html>
