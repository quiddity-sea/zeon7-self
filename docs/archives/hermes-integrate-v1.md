# Hermes Agent Control & Unified Architecture: Detailed Implementation Guide

## 1. Executive Summary & Problem Statement

The ForeverBox ecosystem currently operates with a "split brain" architecture:
1. **The Hermes CLI Environment**: Uses the `agent_registry` MariaDB database to dynamically assemble personas ("SOULs") and "heads" (variants like `coder` or `fiction`). It stores long-term memory, vectors, and lore in agent-specific MariaDB databases (e.g., `agent_curator`, `agent_producer`) via the Council Library REST API.
2. **The Self Web Interface (`i-am-self`)**: Currently relies on a separate, isolated MariaDB database (`zeon7_self`) for its system instructions and chat logs. It has no awareness of dynamic heads, cannot route to specific models per agent, and cannot access the memories formed by agents in the CLI.

**The Goal:** To achieve absolute architectural unification. The web interface must become the central "Control Center" for the Hermes ecosystem. The `zeon7_self` database will be restricted to web-only concerns (UI layouts, user accounts). All agent data (memory, knowledge, personality) in the web app must read from and write to the exact same MariaDB databases used by the Hermes CLI. 

This will allow an administrator to define a public-facing agent, attach a specific "head", route it to a specific LLM model, and have that web agent instantly share the memory and knowledge of its CLI counterpart.

---

## 2. Phase 0: The Unified Data Layer (Eradicating the Split Brain)

Before building new UI, the web application's backend must be rewired to point to the correct data sources.

### 2.1 Database Unification
- **`zeon7_self`**: Will continue to store `users`, UI `config`, and web-specific preferences. The isolated `system_instructions` and isolated `chat_logs` tables will be deprecated for agent use.
- **`agent_registry` (MariaDB)**: The web app will connect here to manage the `soul_components` table (which defines the base personalities and "heads").
- **`agent_curator`, `agent_producer`, etc. (MariaDB)**: The web app will connect to these (either directly via PDO or via the `localhost:8080/v1` Council Library REST API) to read and write memories, lore, and context.

### 2.2 Shared File Storage (Quiddity Sea)
- The web app will read directly from the `/foreverbox_data/` directory for lore files and system states, exactly as the CLI agents do.

---

## 3. Phase 1: The Hermes Public Router

The admin dashboard needs a control panel to dictate exactly *who* the public interacts with and *how* that agent operates.

### 3.1 Admin Interface (`/admin/hermes-router.php`)
A new settings page that allows the admin to define the **Active Public Profile**:
1. **Target Agent**: Dropdown selecting the core agent (e.g., Zeon7, Leon, Gemma, Otec).
2. **Target Head**: Dropdown selecting the active variant/head (e.g., `default`, `coder`, `fiction_writer`). This populates dynamically by scanning `provider_filter` values in the `agent_registry.soul_components` table.
3. **Target Model / Provider**: Dropdown to select the exact LLM driving this instance (e.g., `Ollama: Zeon7-Gemma:64k` or `OpenRouter: anthropic/claude-3.5-sonnet`).

### 3.2 Configuration Storage
These selections will be saved in the `zeon7_self.config` table under specific keys:
- `public_agent_id`
- `public_agent_head`
- `public_agent_model`
- `public_agent_provider`

---

## 4. Phase 2: Dynamic SOUL & Head Editor (Persona Management)

Currently, altering an agent's base personality or creating a new head requires writing manual SQL queries against the `agent_registry` database. We will build a visual editor in the Self web admin.

### 4.1 Admin Interface (`/admin/soul-editor.php`)
A full CRUD (Create, Read, Update, Delete) dashboard connected directly to `agent_registry.soul_components`.

- **Matrix View**: A grid showing all agents and their available components, filtered by "Head" (the `provider_filter` column).
- **Edit Base Personality**: Edit rows where `provider_filter` is `NULL`. This changes the core identity of the agent (e.g., Zeon7's "First Truth").
- **Edit / Create Heads**: Edit rows where `provider_filter` equals a head name (e.g., `coder`). The admin can create a new head (e.g., `designer`) by adding a new row with `provider_filter = 'designer'` for a specific `component_key`.

### 4.2 Backend API (`/api/soul/`)
Secure PHP endpoints that execute the `INSERT`/`UPDATE` queries against the `agent_registry` MariaDB database.

---

## 5. Phase 3: Web Chat Dynamic Assembly & Routing

The public chat endpoint (`api/chat.php`) currently uses static instructions. It must be upgraded to dynamically assemble the prompt and route the request based on the router settings and unified memory.

### 5.1 Dynamic Prompt Assembly
When a public user sends a message:
1. `chat.php` reads the active public agent and head from the Config service.
2. It executes the dynamic assembly logic (either by calling `python3 /foreverbox_data/bin/assemble_soul.py {agent} {head}` or by re-implementing that exact SQL `COALESCE`/override logic natively in PHP).
3. The resulting assembled Markdown is used as the System Prompt.

### 5.2 Context & Memory Injection
Before sending the prompt to the LLM:
1. The backend connects to the agent's specific memory database (e.g., `agent_coach` for Gemma) or calls the Council Library API (`/v1/sanctum/memory/search`).
2. It retrieves vector-matched memories, lore, and past conversation history relevant to the user's message.
3. This context is injected into the prompt, ensuring the web agent "remembers" things it learned in the CLI.

### 5.3 Model Routing
The `AIServiceFactory` will route the API request to the specific provider and model defined in the Hermes Router (e.g., bypassing a global Ollama setting to use OpenRouter specifically for this chat session).

---

## 6. Implementation Checklist & Prerequisites

### Prerequisites
- [ ] **Database Credentials**: The PHP application (`src/Database.php`) must be granted privileges to read/write to `agent_registry` and the various `agent_*` databases.
- [ ] **API Access**: If accessing memory via the Council Library REST API instead of direct DB queries, the PHP app must have the correct `Authorization: Bearer` token configured in its `.env`.

### Step-by-Step Execution Plan
1. **Verify DB Connections**: Write a test script in PHP to ensure `i-am-self` can successfully query `agent_registry.soul_components`.
2. **Build Phase 1 (Router)**: Create `hermes-router.php`, update `ConfigService.php` to save/load public routing keys.
3. **Build Phase 2 (SOUL Editor)**: Create the UI and API endpoints to manage `soul_components`.
4. **Refactor Phase 3 (Chat Pipeline)**: Modify `api/chat.php` and `InstructionService.php` to dynamically assemble the prompt and fetch memory context before routing to the LLM.
5. **Testing**:
    - Edit a head in the Self UI.
    - Verify the CLI `fbox-launch` reflects the change.
    - Set the web public router to use that head.
    - Verify web chat uses the new personality and can access a memory created via the CLI.
