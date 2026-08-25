# Hermes Agent Control & Unified Architecture

## Problem Statement

The ForeverBox ecosystem features a dynamic SOUL assembly system and a rich CLI environment (Hermes) with its own memory states, databases, and file storage. 

Historically, the Self web interface (`i-am-self`) used its own isolated MariaDB database (`zeon7_self`) for chat logs, system instructions, and agent configurations. This created a split brain: if an agent learned something in the CLI, the web interface didn't know about it.

The new goal is absolute unification. The `zeon7_self` database will be stripped down to handle only web-specific concerns (UI layouts, web user accounts). Everything relating to the agents—personalities, memory, vector storage, and Quiddity Sea files—must point directly to the exact same backend data sources that the Hermes CLI uses.

## Proposed Architecture: The Unified Data Layer

We will implement this in four phases, starting with unifying the data layer.

### Phase 0: Unified Data Layer (The Foundation)
We will deprecate the isolated agent tables in `zeon7_self` and route the web application directly to the Hermes ecosystem backends:

- **Identity & SOULs**: The web app will read/write directly to the `agent_registry` MariaDB database (specifically the `soul_components` table) for all persona generation.
- **Memory & Vectors**: The web chat endpoints will connect directly to the agent's Hermes SQLite databases (e.g., `/foreverbox_data/profiles/zeon7/state.db`) or vector stores. If Zeon7 learns a fact in the CLI, the public web chat will instantly have access to that same memory.
- **Shared Knowledge**: The web app will read directly from the Quiddity Sea (`/foreverbox_data/`) just as the CLI agents do.
- **Web DB Scope**: The `zeon7_self` database will be reduced to handling web authentication, UI preferences, and web-specific layout configs.

### Phase 1: Public Agent Router & Model Assignment
We need a dedicated admin page (`/admin/hermes-router.php`) to control who answers the public chat and how they operate, using the unified data layer.

- **Public Agent**: Dropdown (Zeon7, Leon, Gemma, Otec)
- **Active Head**: Dropdown (e.g., `default`, `coder`, `fiction`) pulling from `agent_registry`.
- **Active Model**: Dropdown mapping to local Ollama models or cloud providers.

### Phase 2: Dynamic SOUL / Head Editor
We will build a visual CRUD (Create, Read, Update, Delete) interface in the admin panel to manage the `agent_registry.soul_components` table directly.

- **View**: A dashboard showing all components grouped by Agent -> Head.
- **Edit/Create**: A markdown editor to change or create new heads (e.g., adding a `designer` variant).
- **API**: Secure endpoints (`api/soul/update.php`) to save changes straight to the `agent_registry` DB.

### Phase 3: Web Chat Dynamic Assembly
Currently, the web chat uses `InstructionService.php` to fetch static prompts. We will wire it up to the dynamic SOUL system.

- **Dynamic Assembly**: When a web chat request comes in, the PHP backend will execute `python3 /foreverbox_data/bin/assemble_soul.py {agent_slug} {head}` to generate the exact prompt for that specific head.
- **Model Routing**: The chat will be routed to the specific model chosen in Phase 1.
- **Context Injection**: The chat handler will pull relevant context from the agent's Hermes CLI memory states (implemented in Phase 0) before sending the prompt to the LLM.

---

## Architecture Flow (After Implementation)

1. You log into Self Admin.
2. You go to **SOUL Editor**, create a new head for Gemma called `fiction_writer`, and write her directives. (Saves to `agent_registry`).
3. You go to **Hermes Router**, set the Public Agent to `Gemma`, Head to `fiction_writer`, Model to `openrouter:anthropic/claude-3.5-sonnet`.
4. A public user asks Gemma to write a story about a specific event she learned about yesterday in the CLI.
5. `api/chat.php` dynamically runs `assemble_soul.py`, connects to Gemma's `/foreverbox_data/profiles/gemma/state.db` to pull the memory of that event, and sends the enriched prompt to OpenRouter.

## Next Steps

> [!IMPORTANT]
> **Database Permissions**: We need to ensure the PHP PDO connection (likely `Database.php`) has credentials or permissions to read/write to `agent_registry` and access the SQLite files in `/foreverbox_data/profiles/`.
