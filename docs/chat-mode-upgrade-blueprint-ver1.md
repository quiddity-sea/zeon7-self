# Chat Mode & Neural Link Matrix — Detailed Implementation Blueprint (V1)

**Target builder:** DeepSeek 4 Fast  
**Alternative builder:** Gemini 3.8 / 3.7 Fast  
**Status:** Ready for Implementation  
**Architecture baseline:** `docs/hermes-integrate-v2.md`  
**Related blueprints:** `docs/vps-migration-inventory.md`, `docs/hermes-v2-self-data-inventory.md`  
**Primary repository:** `quiddity-sea/zeon7-self`  
**Related repositories:** `quiddity-sea/foreverbox-data`, `quiddity-sea/council-library`

---

# 0. Builder Instructions

This document is a concrete, step-by-step engineering implementation plan, not a high-level suggestion list. Follow every phase in strict sequential order.

The objective is to upgrade the Foreverbox Self Neural Link Chat architecture to support **two distinct operational modes (Public Chat vs Authenticated Chat)** within a single unified floating/fullscreen interface, while **strictly decoupling agent identities from fixed models** and **preserving the FBOX: SELF Landing Page as an immutable, unified host portal**.

### Non-negotiable rules

1. **The FBOX: SELF Landing Page (`index.php`) must remain structurally and visually stable.** It is the canonical entry point for the platform. Do not morph the landing page layout or re-render different HTML shells when an agent or model is switched. Persona and model switching is localized exclusively to the **Neural Link Chat Widget**.
2. **Agents do NOT have fixed models.** Never hardcode `Brain32:latest`, `gemini-2.5-flash`, or `openai/gpt-4` to any specific agent (`zeon7`, `leon`, `gemma`, `otec`, `wolf`). Models and providers must be dynamically resolved from Admin Settings or from the agent's Hermes profile on the command line.
3. **Preserve strict vanilla PHP architecture (Zero Composer).** Do not introduce Composer dependencies, vendor autoloaders, or third-party PHP packages. All services must continue to use native PHP 8.1+ MVC patterns.
4. **Never expose the Authenticated Agent Switcher to public visitors.** Anonymous visitors must always be locked to the globally configured Public Chat Agent. The in-widget agent switcher dropdown is rendered **only** when an operator session is verified.
5. **Preserve dual-tier execution integrity:**
   - Public chat interactions must execute through the native PHP guarded MCP loop (Tavily search, privacy checks, temporal & runtime grounding).
   - Authenticated operator interactions must execute through the sovereign **Hermes Gateway Daemon** (`http://127.0.0.1:8081`), passing the targeted agent profile (`--profile <agent_slug>`).
6. **Preserve Council Sanctum memory isolation.** When an operator chats with Leon, the conversation turn must append to `sanctum_leon`. When chatting with Otec, it must append to `sanctum_otec`. Never cross-contaminate agent Sanctums.
7. **Maintain cross-environment parity.** Every change made to local WSL must be replicated to the production VPS at `/var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk/` and `/foreverbox_data/`.
8. **Every phase must end with an explicit exit verification test.** Do not proceed to subsequent phases until verification commands succeed.

---

# 1. System Analysis & Architectural State

## 1.1 Current Limitations

1. **Hardcoded Widget Branding**: `js/chat-widget.js` (line 50) hardcodes `<span class="title">ZEON7 NEURAL LINK</span>`. The widget has no dynamic awareness of whether Zeon7, Leon, Gemma, or Otec is active.
2. **Hardcoded Gateway Proxy Profile**: `hermes_openai_proxy.py` and `api/chat.php` hardcode `subprocess.run(["hermes", "--profile", "zeon7", ...])`. An operator selecting Otec in Admin is still executed under Zeon7's Hermes profile.
3. **Session vs Public Conflation**: Switching agents in `admin/components/header.php` updates `$_SESSION['active_agent']`. If the operator tests the front page in the same browser, their admin session silently hijacks the chat pipeline, confusing public tests with operator Hermes execution.
4. **Flat Model Configuration**: `zeon7_self_dev.config` stores single global keys (`provider`, `gemini_model`, `ollama_model`). It cannot specify that Zeon7 runs on Ollama `Brain32:latest` while Leon runs on OpenRouter `deepseek-v4-flash`.

## 1.2 Target Architecture

```
===================================================================================
                               FBOX: SELF PLATFORM
===================================================================================
                                       |
                   +-------------------+-------------------+
                   |                                       |
                   v                                       v
         FBOX: SELF LANDING PAGE                  ADMIN COCKPIT
             (`index.php`)                       (`/admin/`)
                   |                                   |
         +---------+---------+               +---------+---------+
         |                   |               |                   |
         v                   v               v                   v
   [MODE A: PUBLIC]   [MODE B: AUTH]   [CHAT SETTINGS]    [AGENT ENGINES]
   - Anonymous User   - Prime Operator - Set Public Agent - Set Model/Provider
   - Locked Agent     - In-Widget Drop - Set Auth Agent     per Agent
   - Guarded MCP Loop - Hermes Gateway - Sync to DB       - Sync to Hermes
   - Cloud / Assigned - Local / Cloud                     - Test Connection
===================================================================================
```

---

# 2. Phase 1 — Database Schema & Configuration Engine Upgrades

## Objective
Expand `zeon7_self_dev.config` and `src/services/ConfigService.php` to manage per-agent model/provider assignments and default chat mode agents.

## 2.1 Database Schema Migration
File: `docs/database/004_chat_mode_agent_matrix.sql`

```sql
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
```

## 2.2 Update `ConfigService.php`
File: `src/services/ConfigService.php`

Extend the `ConfigService` class with dedicated helpers for the two chat modes and per-agent resolution:

```php
// Add to src/services/ConfigService.php

public function getPublicChatAgent(): string {
    return $this->get('public_chat_agent') ?: 'zeon7';
}

public function setPublicChatAgent(string $agentSlug): void {
    $this->set('public_chat_agent', strtolower(trim($agentSlug)));
}

public function getAuthenticatedDefaultAgent(): string {
    return $this->get('authenticated_default_agent') ?: 'zeon7';
}

public function setAuthenticatedDefaultAgent(string $agentSlug): void {
    $this->set('authenticated_default_agent', strtolower(trim($agentSlug)));
}

public function getAgentProvider(string $agentSlug): string {
    $val = $this->get("agent_{$agentSlug}_provider");
    return !empty($val) ? $val : $this->getCurrentProvider();
}

public function getAgentModel(string $agentSlug): string {
    $val = $this->get("agent_{$agentSlug}_model");
    if (!empty($val)) return $val;
    $provider = $this->getAgentProvider($agentSlug);
    return $this->getModelForProvider($provider);
}

public function getAgentThink(string $agentSlug): bool {
    $val = $this->get("agent_{$agentSlug}_think");
    return ($val !== null) ? (bool)$val : $this->getOllamaThink();
}

public function setAgentEngine(string $agentSlug, string $provider, string $model, bool $think = false): void {
    $slug = strtolower(trim($agentSlug));
    $this->set("agent_{$slug}_provider", $provider);
    $this->set("agent_{$slug}_model", $model);
    $this->set("agent_{$slug}_think", $think ? '1' : '0');
}
```

## 2.3 Phase 1 Exit Verification
Run command:
```bash
wsl -d Ubuntu-24.04 -e mysql zeon7_self_dev -e "SELECT * FROM config WHERE key_name LIKE 'public_chat%' OR key_name LIKE 'agent_%';"
```
**Exit Condition:**
- [ ] `public_chat_agent` and `authenticated_default_agent` exist with default `'zeon7'`.
- [ ] All 5 agents (`zeon7`, `leon`, `gemma`, `otec`, `wolf`) have per-agent provider and model entries.
- [ ] `ConfigService` methods return non-empty strings for all 5 agents.

---

# 3. Phase 2 — Hermes Gateway Dynamic Profile & Model Dispatch

## Objective
Upgrade the FastAPI OpenAI-compatible proxy daemon (`/foreverbox_data/bin/hermes_openai_proxy.py`) to accept dynamic agent profile targets and runtime model overrides.

## 3.1 Modify `hermes_openai_proxy.py`
File: `/foreverbox_data/bin/hermes_openai_proxy.py`

Replace the hardcoded `--profile zeon7` with dynamic extraction:

```python
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import subprocess
import json
import uuid
import os

app = FastAPI(title="ForeverBox Hermes OpenAI Gateway", version="2.0")

VALID_PROFILES = {"zeon7", "leon", "gemma", "otec", "wolf"}

@app.post("/v1/chat/completions")
async def chat_completions(request: Request):
    data = await request.json()
    messages = data.get("messages", [])
    if not messages:
        return JSONResponse({"error": "No messages supplied"}, status_code=400)
    
    last_message = messages[-1]["content"] if messages[-1]["role"] == "user" else "Hello"
    
    # 1. Resolve Target Agent Profile
    # The 'model' parameter passes the agent slug (e.g. 'leon', 'zeon7', 'otec')
    raw_model = data.get("model", "zeon7").lower().strip()
    profile = raw_model if raw_model in VALID_PROFILES else "zeon7"
    
    # 2. Check for optional Model & Provider runtime overrides
    override_model = data.get("override_model")
    override_provider = data.get("override_provider")
    
    cmd = [
        "/foreverbox_data/venv/bin/hermes",
        "--profile", profile,
        "chat",
        "-Q", "--yolo", "--accept-hooks",
        "--query", last_message
    ]
    
    if override_model:
        cmd.extend(["-m", override_model])
    if override_provider:
        cmd.extend(["--provider", override_provider])
        
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            check=True,
            timeout=280
        )
        reply = result.stdout.strip()
    except subprocess.TimeoutExpired:
        reply = f"Error: Hermes execution for agent '{profile}' exceeded 280-second timeout."
    except subprocess.CalledProcessError as e:
        reply = f"Error from Hermes ({profile}): {e.stderr.strip() or e.stdout.strip()}"
    except Exception as e:
        reply = f"System Gateway Error: {str(e)}"
    
    return {
        "id": f"chatcmpl-{uuid.uuid4()}",
        "object": "chat.completion",
        "created": 1234567890,
        "model": profile,
        "choices": [
            {
                "index": 0,
                "message": {
                    "role": "assistant",
                    "content": reply
                },
                "finish_reason": "stop"
            }
        ],
        "usage": {
            "prompt_tokens": 0,
            "completion_tokens": 0,
            "total_tokens": 0
        }
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8081)
```

## 3.2 Daemon Restart & Hot Reload
```bash
# On VPS and WSL
killall -9 python 2>/dev/null || true
source /foreverbox_data/venv/bin/activate
nohup python /foreverbox_data/bin/hermes_openai_proxy.py > /foreverbox_data/hermes_gateway.log 2>&1 &
```

## 3.3 Phase 2 Exit Verification
Test running Leon profile via proxy:
```bash
curl -s -X POST http://127.0.0.1:8081/v1/chat/completions \
  -H "Content-Type: application/json" \
  -d '{"model": "leon", "messages": [{"role": "user", "content": "Identify yourself in one sentence."}]}'
```
**Exit Condition:**
- [ ] Proxy returns HTTP 200 with response acknowledging Leon persona (`"model": "leon"`).
- [ ] Proxy accepts Zeon7, Gemma, Otec, and Wolf dynamically without crashing.

---

# 4. Phase 3 — Backend Dual-Mode Chat Controller (`api/chat.php`)

## Objective
Refactor `api/chat.php` to handle Mode A (Public) vs Mode B (Authenticated) cleanly without session leakage, respecting per-agent model configurations and logging turns to the correct Council Sanctum.

## 4.1 Update `GET /api/chat.php` Status Handshake
Allow the frontend widget to inspect current session authentication, available agents, and mode on load:

```php
// In api/chat.php handleGetStatus()
$authService = new AuthService();
$currentUser = $authService->getCurrentUser();
$isOperator  = !empty($currentUser['user_id']);

$agentCtx = new AgentContextService();
$availableAgents = [];
foreach ($agentCtx->getAvailableAgents() as $slug => $data) {
    $availableAgents[] = [
        'id'     => $slug,
        'name'   => $data['name'],
        'role'   => $data['role'],
        'accent' => $data['accent']
    ];
}

$publicAgent = $this->configService->getPublicChatAgent();
$authDefault = $this->configService->getAuthenticatedDefaultAgent();

echo json_encode([
    'success'          => true,
    'is_authenticated' => $isOperator,
    'mode'             => $isOperator ? 'authenticated' : 'public',
    'active_agent'     => $isOperator ? $authDefault : $publicAgent,
    'public_agent'     => $publicAgent,
    'available_agents' => $availableAgents,
    'think'            => $this->configService->getAgentThink($isOperator ? $authDefault : $publicAgent)
]);
exit;
```

## 4.2 Update `POST /api/chat.php` Dispatch Flow

```php
// In api/chat.php handleRequest()

// 1. Determine Caller Tier
$authService = new AuthService();
$currentUser = $authService->getCurrentUser();
$userId      = !empty($currentUser['user_id']) ? (int)$currentUser['user_id'] : null;
$isOperator  = ($userId !== null);

// 2. Resolve Target Agent Identity
if (!$isOperator) {
    // Mode A: Public Chat — Strictly locked to global public chat agent setting
    $agentId = $this->configService->getPublicChatAgent();
} else {
    // Mode B: Authenticated Chat — Uses selected agent from payload, or default
    $requestedAgent = strtolower(trim($input['agent'] ?? ''));
    $validAgents = ['zeon7', 'leon', 'gemma', 'otec', 'wolf'];
    if (in_array($requestedAgent, $validAgents, true)) {
        $agentId = $requestedAgent;
    } else {
        $agentId = $this->configService->getAuthenticatedDefaultAgent();
    }
}

// 3. Resolve Engine Settings for Resolved Agent
$provider = $this->configService->getAgentProvider($agentId);
$model    = $this->configService->getAgentModel($agentId);
$apiKey   = $this->configService->getApiKey($provider);
$userThink = isset($input['think']) ? (bool)$input['think'] : $this->configService->getAgentThink($agentId);

// 4. Resolve Dynamic SOUL & System Prompt for Target Agent
$systemPrompt = $this->instructionService->getCurrentContent($agentId);
if (empty($systemPrompt)) {
    $manifest = $this->agentContext->getManifestForAgent($agentId);
    $systemPrompt = $manifest['persona']['fallback_greeting'] ?? "You are {$agentId}.";
}

// 5. Runtime Context Grounding
$systemPrompt .= "\n\n--- RUNTIME CONTEXT ---\n";
$systemPrompt .= "Current Date & Time: " . date('l, j F Y, H:i T') . ".\n";
$systemPrompt .= "Active Agent: " . strtoupper($agentId) . ".\n";
$systemPrompt .= "Active AI Engine: {$model} (Provider: " . strtoupper($provider) . ").\n";
if ($provider === 'ollama') {
    $systemPrompt .= "Execution Environment: Local / Remote Ollama Endpoint ({$this->configService->getOllamaHost()}).\n";
} else {
    $systemPrompt .= "Execution Environment: Cloud Provider (" . strtoupper($provider) . "). You are NOT running on a local GPU.\n";
}

// 6. Execution Branch
if ($isOperator) {
    // MODE B: Authenticated Hermes Gateway Execution
    $gatewayUrl = 'http://127.0.0.1:8081/v1/chat/completions';
    $messages = [['role' => 'system', 'content' => $systemPrompt]];
    foreach ($history as $turn) {
        $messages[] = ['role' => ($turn['role'] === 'user' ? 'user' : 'assistant'), 'content' => $turn['content'] ?? ''];
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $ch = curl_init($gatewayUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model'             => $agentId,
        'override_model'    => $model,
        'override_provider' => $provider,
        'messages'          => $messages,
        'stream'            => false
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 290);
    $gwResponse = curl_exec($ch);
    $gwCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($gwCode === 200 && $gwResponse) {
        $data = json_decode($gwResponse, true);
        $reply = $data['choices'][0]['message']['content'] ?? 'Error parsing gateway response.';
    } else {
        $reply = "Hermes Gateway unavailable for agent '{$agentId}' (HTTP {$gwCode}).";
    }
} else {
    // MODE A: Public Guarded MCP Tool Loop
    require_once __DIR__ . '/../src/services/McpClientService.php';
    $tools = [];
    try {
        $mcpClient = new McpClientService();
        $tools = $mcpClient->getTools();
    } catch (\Throwable $e) {}

    $aiService = AIServiceFactory::create($provider, $apiKey ?? '', $model, $userThink);
    $maxIterations = 2;
    $currentIteration = 0;

    while ($currentIteration < $maxIterations) {
        $result = $aiService->chat($message, $history, $systemPrompt, $tools);
        if (!empty($result['functionCall'])) {
            $fn = $result['functionCall'];
            $toolOutput = $mcpClient ? $mcpClient->callTool($fn['name'], $fn['args'] ?? []) : 'Tool failed';
            $history[] = ['role' => 'model', 'functionCall' => $fn];
            $history[] = ['role' => 'user', 'functionResponse' => ['name' => $fn['name'], 'response' => ['content' => $toolOutput]]];
            $currentIteration++;
        } else {
            $reply = $result['reply'] ?? '';
            break;
        }
    }
}

// 7. Append Conversation Turn to Targeted Agent Sanctum
try {
    if ($this->councilClient->isAvailable()) {
        $this->councilClient->withAgent($agentId)->appendMessage(
            sessionId: $sessionId,
            role: 'assistant',
            content: $reply,
            metadata: ['model' => $model, 'provider' => $provider, 'agent' => $agentId],
            ipAddress: $ip,
            operatorId: $userId
        );
    }
} catch (\Throwable $e) {}
```

## 4.3 Phase 3 Exit Verification
Run curl test as public visitor:
```bash
curl -s -X POST https://self.foreverbox.co.uk/api/chat.php \
  -H "Content-Type: application/json" \
  -d '{"message": "who are you?"}'
```
**Exit Condition:**
- [ ] Responds with the configured Public Chat Agent identity (e.g. Zeon7).
- [ ] Ignores any client-injected `"agent": "otec"` when unauthenticated.
- [ ] Authenticated cookie properly activates Mode B and routes to Hermes.

---

# 5. Phase 4 — Frontend Neural Link Widget Upgrade (`js/chat-widget.js`)

## Objective
Refactor `js/chat-widget.js` to render the dynamic title, conditional in-widget agent switcher dropdown for operators, and preserve the FBOX: SELF Landing Page structure.

## 5.1 DOM Template Restructure
File: `/var/www/self/js/chat-widget.js`

Replace lines 46-52 in `render()`:

```javascript
<!-- Header -->
<div class="chat-header">
    <div class="header-info">
        <span class="status-dot" id="chat-status-dot"></span>
        <span class="title" id="chat-header-title">NEURAL LINK</span>
    </div>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <!-- In-Widget Authenticated Agent Switcher (Hidden for Public) -->
        <div id="chat-agent-switcher-container" style="display: none;">
            <select id="chat-agent-select" class="chat-agent-dropdown" title="Switch Council Agent Frequency">
            </select>
        </div>
        <!-- Think Toggle Badge -->
        <button type="button" id="chat-think-toggle" class="think-badge think-off" title="Toggle Reasoning Mode">
            [ THINKING OFF ]
        </button>
        <button id="chat-close-btn" class="chat-close">&times;</button>
    </div>
</div>
```

## 5.2 Header & Mode State Handler
Add methods to `ChatWidget` class:

```javascript
// State properties
isAuthenticated: false,
activeAgent: 'zeon7',
activeAgentName: 'Zeon7',
availableAgents: [],

async fetchInitialStatus() {
    try {
        const res = await fetch('/api/chat.php');
        const data = await res.json();
        if (data.success) {
            this.isAuthenticated = Boolean(data.is_authenticated);
            this.activeAgent = data.active_agent || 'zeon7';
            this.availableAgents = data.available_agents || [];
            this.isThinking = Boolean(data.think);

            this.updateHeaderDisplay();
            this.setupAgentSwitcher();
            this.updateThinkBadge();
        }
    } catch (e) {
        console.warn('Could not fetch initial chat status', e);
        this.updateHeaderDisplay();
    }
},

updateHeaderDisplay() {
    const titleEl = document.getElementById('chat-header-title');
    if (!titleEl) return;

    const agentObj = this.availableAgents.find(a => a.id === this.activeAgent);
    const displayName = agentObj ? agentObj.name : this.activeAgent.toUpperCase();
    const modeLabel = this.isAuthenticated ? 'AUTHENTICATED CHAT' : 'PUBLIC CHAT';

    titleEl.textContent = `${displayName.toUpperCase()} NEURAL LINK — ${modeLabel}`;
    
    // Update status dot glow
    const dot = document.getElementById('chat-status-dot');
    if (dot && agentObj && agentObj.accent) {
        dot.style.backgroundColor = agentObj.accent;
        dot.style.boxShadow = `0 0 10px ${agentObj.accent}`;
    }
},

setupAgentSwitcher() {
    const container = document.getElementById('chat-agent-switcher-container');
    const select = document.getElementById('chat-agent-select');
    if (!container || !select) return;

    // Rule: Hide switcher completely for public visitors
    if (!this.isAuthenticated) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';
    select.innerHTML = '';

    this.availableAgents.forEach(agent => {
        const opt = document.createElement('option');
        opt.value = agent.id;
        opt.textContent = `${agent.name} (${agent.role || 'Agent'})`;
        if (agent.id === this.activeAgent) opt.selected = true;
        select.appendChild(opt);
    });

    select.onchange = (e) => {
        const newAgent = e.target.value;
        if (newAgent === this.activeAgent) return;
        
        this.activeAgent = newAgent;
        const agentObj = this.availableAgents.find(a => a.id === newAgent);
        this.activeAgentName = agentObj ? agentObj.name : newAgent;
        
        this.updateHeaderDisplay();
        this.appendMessage('assistant', `[ Switched frequency to ${this.activeAgentName.toUpperCase()} // Sanctum Active ]`);
    };
}
```

## 5.3 Include `agent` in POST Payload
In `handleSubmit()`:
```javascript
const payload = {
    message: message,
    session_id: this.sessionId,
    history: this.history,
    think: this.isThinking,
    agent: this.activeAgent // Dynamically passes selected agent
};
```

## 5.4 Widget CSS Enhancements
File: `css/components.css` / `css/zeon7-theme.css`
```css
.chat-agent-dropdown {
    background: rgba(10, 14, 23, 0.95);
    border: 1px solid var(--color-cyan, #00f2fe);
    color: var(--color-cyan, #00f2fe);
    font-family: var(--font-mono, monospace);
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 3px;
    cursor: pointer;
    outline: none;
}
.chat-agent-dropdown option {
    background: #0a0e17;
    color: #fff;
}
```

## 5.5 Phase 4 Exit Verification
- Open in Incognito window (unauthenticated):
  - Check header: says `[AGENT] NEURAL LINK — PUBLIC CHAT`.
  - Check dropdown: `#chat-agent-switcher-container` is hidden (`display: none`).
- Log into Admin in regular window:
  - Check header: says `[AGENT] NEURAL LINK — AUTHENTICATED CHAT`.
  - Check dropdown: visible, lists `Zeon7`, `Leon`, `Gemma`, `Otec`, `Wolf`.
  - Select Leon -> Title updates to `LEON NEURAL LINK — AUTHENTICATED CHAT`.

---

# 6. Phase 5 — Admin Settings Interface Matrix

## Objective
Upgrade `admin/settings.php` and `admin/js/settings.js` to configure the default Public Chat Agent, default Authenticated Chat Agent, and per-agent model/provider assignments.

## 6.1 UI Controls in `admin/settings.php`
Add dedicated configuration groups:

```html
<!-- CHAT MODES ASSIGNMENT -->
<div class="hud-card" style="margin-bottom: 1.5rem;">
    <h3 style="color: var(--color-cyan); margin-bottom: 1rem;">// NEURAL LINK CHAT CONFIGURATION</h3>
    
    <div class="form-group">
        <label for="publicChatAgent">Public Chat Default Agent</label>
        <select id="publicChatAgent" class="input-box">
            <option value="zeon7">Zeon7 (The Curator)</option>
            <option value="leon">Leon (The Producer)</option>
            <option value="gemma">Gemma (The Coach)</option>
            <option value="otec">Otec (The Director)</option>
            <option value="wolf">Wolf (Research Swarm)</option>
        </select>
        <span class="helper-text">Agent that greets anonymous visitors on the FBOX: SELF Landing Page.</span>
    </div>

    <div class="form-group">
        <label for="authChatAgent">Authenticated Chat Default Agent</label>
        <select id="authChatAgent" class="input-box">
            <option value="zeon7">Zeon7 (The Curator)</option>
            <option value="leon">Leon (The Producer)</option>
            <option value="gemma">Gemma (The Coach)</option>
            <option value="otec">Otec (The Director)</option>
            <option value="wolf">Wolf (Research Swarm)</option>
        </select>
        <span class="helper-text">Initial agent selected when an authenticated operator opens the chat.</span>
    </div>
</div>

<!-- PER-AGENT ENGINE MATRIX -->
<div class="hud-card" style="margin-bottom: 1.5rem;">
    <h3 style="color: var(--color-gold); margin-bottom: 1rem;">// PER-AGENT ENGINE ASSIGNMENT</h3>
    <div class="form-group">
        <label for="configAgentSelect">Select Agent to Configure</label>
        <select id="configAgentSelect" class="input-box">
            <option value="zeon7">⚡ Zeon7</option>
            <option value="leon">🛠️ Leon</option>
            <option value="gemma">🧭 Gemma</option>
            <option value="otec">👁️ Otec</option>
            <option value="wolf">🐺 Wolf</option>
        </select>
    </div>

    <!-- Active Agent Engine Settings Card -->
    <div id="agentEngineCard" style="border: 1px dashed rgba(0, 242, 254, 0.3); padding: 1rem; border-radius: 4px;">
        <div class="form-group">
            <label for="agentProvider">AI Neural Provider</label>
            <select id="agentProvider" class="input-box">
                <option value="gemini">Google Gemini AI</option>
                <option value="ollama">Local / Remote Ollama (Tailscale)</option>
                <option value="openrouter">OpenRouter Multi-LLM</option>
            </select>
        </div>
        <div class="form-group">
            <label for="agentModel">Model Specification</label>
            <input type="text" id="agentModel" class="input-box" placeholder="e.g. Brain32:latest or gemini-2.5-flash">
        </div>
    </div>
</div>
```

## 6.2 Settings Controller Endpoints
Update `api/config/get.php` and `api/config/update.php`:
- `get.php`: Returns `public_chat_agent`, `authenticated_default_agent`, and all `agent_{slug}_*` records.
- `update.php`: Accepts and saves updated agent mappings with CSRF verification.

## 6.3 Phase 5 Exit Verification
- Save Public Chat Agent as `leon`.
- Visit `index.php` in an Incognito window:
  - Header displays: `LEON NEURAL LINK — PUBLIC CHAT`.
  - Asking `"who are you?"` yields Leon's response.
- Change Public Chat Agent back to `zeon7`.
- Visit `index.php` in Incognito:
  - Header displays: `ZEON7 NEURAL LINK — PUBLIC CHAT`.

---

# 7. Cross-Interface Acceptance Tests

Before declaring implementation complete, run this 4-step acceptance test:

```text
TEST 1: Public Isolation
Action: Open Incognito window -> navigate to https://self.foreverbox.co.uk/
Expectation:
- Landing page renders the unified FBOX: SELF Landing Page (not morphed).
- Chat header reads "ZEON7 NEURAL LINK — PUBLIC CHAT".
- Agent switcher dropdown is NOT present in the DOM.
- Chatting queries Gemini (cloud) without spinning up local GPU fans.

TEST 2: Authenticated Operator In-Widget Switching
Action: Log in to /admin -> navigate to https://self.foreverbox.co.uk/
Expectation:
- Landing page renders identically to Test 1.
- Chat header reads "ZEON7 NEURAL LINK — AUTHENTICATED CHAT".
- Agent dropdown is present. Select "Leon".
- Header title updates to "LEON NEURAL LINK — AUTHENTICATED CHAT".
- Chatting queries Hermes Gateway with --profile leon.
- Response reflects Leon's persona.

TEST 3: Sanctum Continuity
Action: Chat with Leon, then switch dropdown to Otec and ask "what did I just ask you?"
Expectation:
- Otec responds from Otec's private Sanctum and does not leak Leon's private thoughts.

TEST 4: CLI Parity
Action: In VPS terminal, run: hermes --profile leon chat -Q "Identify yourself"
Expectation:
- Response tone, model, and identity match the web chat responses.
```

---

# 8. Definition of Done

The Chat Mode & Neural Link Matrix is complete when:
1. `index.php` is explicitly confirmed as the immutable **FBOX: SELF Landing Page**.
2. Unauthenticated visitors are locked to the configured Public Chat Agent with `[AGENT] NEURAL LINK — PUBLIC CHAT`.
3. Authenticated operators receive `[AGENT] NEURAL LINK — AUTHENTICATED CHAT` and a live in-widget agent switcher.
4. No agent has a fixed model; models resolve dynamically from Admin Settings or Hermes CLI.
5. Operator chats route to Hermes Gateway with `--profile <selected_agent>`.
6. Public chats route through the vanilla PHP guarded MCP loop.
7. Zero Composer dependencies are introduced.
8. Local WSL and production VPS are 100% synchronized.
