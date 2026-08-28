# Hermes V2 — Self Data Ownership & Migration Inventory

**Generated:** 2026-08-28  
**Architecture Baseline:** `docs/hermes-integrate-v2.md`  
**Execution Plan:** `docs/hermes-integrate-v2-implementation-plan.md` (Phase 1)  
**Primary Repository:** `quiddity-sea/zeon7-self`  

---

## 1. Executive Summary

This inventory audits every table in `zeon7_self_dev`, every API endpoint in `/var/www/self/api/`, and every Admin management cockpit in `/var/www/self/admin/`. It establishes strict data ownership boundaries so that all agent cognitive state is unified under the Council Library while Self is preserved as the presentation, auth, and template layer.

---

## 2. Self Database (`zeon7_self_dev`) Table Classification

| Table Name | Current Columns / Purpose | Rows | Classification | Target Location / Canonical Replacement |
| :--- | :--- | :---: | :--- | :--- |
| **`users`** | `id`, `username`, `password_hash`, `email`, `is_prime_user`, `google_id`, `last_10_ips` | 3 | **KEEP (Web/UI)** | Remains in Self MariaDB. Manages web logins, session credentials, and operator security. |
| **`daily_context`**| `id`, `day_name`, `theme`, `tone`, `tagline` | 7 | **KEEP (Web/UI)** | Remains in Self MariaDB. Controls daily broadcast themes for the News Desk UI. |
| **`posts`** | `id`, `title`, `slug`, `content`, `status`, `published_at` | 0 | **KEEP (Web/UI)** | Remains in Self MariaDB. Public blog articles and dispatch archive. |
| **`image_prompt`** | `id`, `post_id`, `prompt`, `prompt_order` | 0 | **KEEP (Web/UI)** | Remains in Self MariaDB. UI asset generation prompts attached to posts. |
| **`api_usage`** | `id`, `endpoint`, `ip_address`, `request_count`, `window_start` | 7 | **KEEP (Web/UI)** | Remains in Self MariaDB. Rate limiting and web firewall telemetry. |
| **`system_instructions`** | `id`, `agent_id`, `component`, `type`, `content`, `is_active` | 6 | **MOVE & REPLACE** | Replaced by **Council Sanctum SOUL** (`/v1/sanctum/soul` & `soul_components`). |
| **`lore`** | `id`, `type`, `content`, `tags`, `is_public` | 0 | **MOVE & REPLACE** | Replaced by **Council Sanctum Memory** (`/v1/sanctum/memory/*`). |
| **`knowledge_doc`** | `id`, `filename`, `file_hash`, `file_size`, `is_public` | 0 | **MOVE & REPLACE** | Replaced by **Council Commons** (`/v1/commons/files`). |
| **`knowledge_chunk`**| `id`, `doc_id`, `heading`, `content`, `embedding` | 0 | **MOVE & REPLACE** | Replaced by **Council Commons Search** (`/v1/commons/search`). |
| **`chat_logs`** | `id`, `session_id`, `user_id`, `role`, `content`, `model` | 25 | **REPLACE** | Replaced by **Council Sanctum Conversations** (`/v1/sanctum/conversations`). |
| **`config`** | `key_name`, `value` (AI provider, model overrides) | 6 | **REPLACE** | AI routing moves to Council; pure UI theme settings remain in Self. |
| **`token_usage`** | `id`, `provider`, `model`, `prompt_tokens`, `total_tokens` | 67 | **REPLACE** | Replaced by Council token budget ledger (`/v1/registry/budget`). |
| **`instruction_set`**| Legacy v1 single-agent prompt table | 0 | **DELETE** | Legacy table to be dropped. |
| **`gemini_log`** | Legacy Gemini raw call logger | 76 | **DELETE** | Legacy table to be dropped. |
| **`api_keys`** | Local AES-256 encrypted provider keys | 1 | **DELETE** | Cloud API keys are managed in Council server runtime environment. |

---

## 3. Self Services Audit & Council Replacement

```text
src/services/
├── CouncilClient.php          -> [ACTIVE / PRIMARY] HTTP Client for Council REST API (:8080)
├── InstructionService.php     -> [REPLACE] Redirects reads/writes to Council SOUL API
├── LoreService.php            -> [REPLACE] Redirects to Council Sanctum Memory API
├── KnowledgeService.php       -> [REPLACE] Redirects to Council Commons Search API
├── AIServiceFactory.php       -> [REPLACE] Redirects chat/inference to Council execution pipeline
├── AgentContextService.php    -> [EVOLVE] Reads active agent metadata via Council assignments
└── AuthService.php            -> [KEEP] Manages bcrypt passwords, sessions, Google OAuth
```

---

## 4. API Endpoints Migration Trace (40 Endpoints)

### A. AI & Generation (Moves to Council Execution)
* `api/chat.php`: Core public chat endpoint. Delegates prompt assembly, memory retrieval, model routing, and turn logging to Council REST API (`/v1/sanctum/*`).
* `api/ai/generate.php`: News Desk article suite generator. Resolves active agent/head/model via Council.
* `api/ai/scan.php` & `api/ai/scan-vision.php`: Grounding & vision scanners.

### B. Admin Persona & Memory Management (Replaced by Council API UI)
* `api/instruction/*` (`create.php`, `current.php`, `versions.php`): Becomes UI client for Council Dynamic SOUL components.
* `api/lore/*` (`all.php`, `upsert.php`, `delete.php`): Becomes UI client for `/v1/sanctum/memory/*`.
* `api/knowledge/*` (`list.php`, `upload.php`, `delete.php`): Becomes UI client for `/v1/commons/*`.
* `admin/api/chat_logs.php`: Reads transcript from `/v1/sanctum/conversations/{sid}`.

### C. Web / Auth / Editorial Endpoints (Retained in Self)
* `api/auth/*` (`login.php`, `logout.php`, `check.php`, `google_redirect.php`, `google_callback.php`): Preserved.
* `api/posts/*` (`create.php`, `list.php`, `get.php`, `update.php`, `publish.php`, `delete.php`): Preserved.
* `api/users/*` (`all.php`, `upsert.php`, `delete.php`, `remove_ip.php`): Preserved.

---

## 5. Feature Flags Transition State (`.env`)

```ini
# Council Integration Active Configuration
COUNCIL_API_URL=http://127.0.0.1:8080
COUNCIL_AGENT_ID=zeon7
COUNCIL_API_KEY=dev-key-change-in-production
COUNCIL_TIMEOUT=10
FOREVERBOX_DATA_PATH=/foreverbox_data

# Feature Flags (Progressively switched from 'local' to 'council')
MEMORY_BACKEND=council
KNOWLEDGE_BACKEND=council
CONVERSATION_BACKEND=council
SOUL_BACKEND=council
```

---

## 6. Phase 1 Exit Criteria Checklist

- [x] All 15 database tables classified (KEEP: 5, MOVE/REPLACE: 7, DELETE: 3)
- [x] Service replacements mapped to Council REST endpoints
- [x] All 40 API endpoints traced and classified
- [x] Diagnostic leaks (`api/env_test.php`) deleted
- [x] Data ownership rules established (Self = UI/Presentation, Council = Cognition/State)

