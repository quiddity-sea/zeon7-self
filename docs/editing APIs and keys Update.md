# Implementation Plan: Dynamic APIs & Keys Admin UI

## Goal Description
Add a new administrative tab called "APIs & Keys" to the `settings.php` page in the Self web app. This tab allows the operator to easily manage (edit, change, delete) API keys directly from a mobile phone or desktop browser. It dynamically parses the `.env` file, self-updating the UI to expose any current or future keys without requiring frontend code changes.

---

## Phase 1: Core Dynamic `.env` Engine & Tab (Completed)

### 1. Backend API (`admin/api/env_handler.php`)
- **GET Action**:
  - Validates admin session via `AuthMiddleware`.
  - Reads `.env` line by line.
  - Extracts keys, values, and contextual comments immediately preceding each key.
  - Returns `{ success: true, entries: [...], env: {...}, writable: bool }`.
- **POST Action**:
  - Validates admin session via `AuthMiddleware`.
  - Receives JSON payload with updated `keys` and `deleted` key arrays.
  - Iterates through original `.env` preserving comments, section headers, and blank lines.
  - Updates keys in-place, drops deleted lines, and appends newly registered keys.
  - Creates `.env.backup` prior to atomic write (`LOCK_EX`).

### 2. Frontend Management UI (`admin/settings.php` & `admin/js/settings.js`)
- Added 4th navigation tab button `🔑 APIS & KEYS` with flex-wrap styling for mobile devices.
- Dynamic key card rendering with:
  - Masking toggle (`👁️` / `🔒`) for secrets.
  - One-tap clipboard copy (`📋`).
  - Delete / restore toggle (`🗑️` / `↩️`).
  - Add New Key modal card (`➕ ADD KEY`).
  - Instant text filter (`#envSearchInput`).
  - Independent `UPDATE .ENV KEYRING` commit action.

---

## Phase 2: Domain-Specific Sub-Tab Filtering (The Eye, Chat/Council, Auth, System)

### Goal
Below the main settings navigation tabs, provide a secondary set of sub-tabs (category pills) grouping `.env` variables by application component so mobile operators can immediately jump to the relevant configuration without scrolling through 38+ keys.

### 1. Sub-Tab Categories & Classification Matrix
Every key in `.env` is categorized dynamically based on its key prefix and domain association:

| Category Pill | Associated `.env` Keys | Description |
| :--- | :--- | :--- |
| **🌐 ALL** | All keys | Comprehensive view with total key counter. |
| **👁️ THE EYE** | `CESIUM_*`, `CARTO_*`, `GOOGLE_MAPS_*`, `OPENSKY_*`, `ADSB_*`, `NASA_*`, `ROCKET_*`, `AISSTREAM_*`, `BARENTSWATCH_*`, `TOMTOM_*`, `EYE_*` | Geospatial intelligence layers, tile providers, tracking feeds, and rate limits. |
| **🔐 AUTH & SECURITY** | `ADMIN_PASSWORD`, `APP_KEY`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `SESSION_TIMEOUT`, `RATE_LIMIT_*` | Passwords, session expiration, encryption keys, rate limits, and OAuth secrets. |
| **🧠 COUNCIL & CHAT** | `COUNCIL_*`, `MEMORY_*`, `KNOWLEDGE_*`, `SOUL_*`, `CONVERSATION_*`, `FOREVERBOX_DATA_PATH` | Hermes / Council gateway endpoints, agent memory backends, and storage paths. |
| **⚙️ SYSTEM & DB** | `DB_*`, `APP_ENV`, `APP_DEBUG`, `UPLOAD_*`, and dynamic custom keys | MariaDB credentials, application environment, upload caps, and unclassified keys. |

### 2. UI Components (`admin/settings.php`)
- **Sub-Tab Navigation Bar**:
  Directly above the search filter inside `#tab-api-keys`, add a cybernetic category pill bar:
  ```html
  <div class="env-category-tabs" id="envCategoryTabs">
      <button type="button" class="env-cat-pill active" data-category="all">
          <span>🌐</span> ALL <span class="cat-count" id="count-all">38</span>
      </button>
      <button type="button" class="env-cat-pill" data-category="eye">
          <span>👁️</span> THE EYE <span class="cat-count" id="count-eye">14</span>
      </button>
      <button type="button" class="env-cat-pill" data-category="auth">
          <span>🔐</span> AUTH &amp; SECURITY <span class="cat-count" id="count-auth">7</span>
      </button>
      <button type="button" class="env-cat-pill" data-category="council">
          <span>🧠</span> COUNCIL &amp; CHAT <span class="cat-count" id="count-council">7</span>
      </button>
      <button type="button" class="env-cat-pill" data-category="system">
          <span>⚙️</span> SYSTEM &amp; DB <span class="cat-count" id="count-system">10</span>
      </button>
  </div>
  ```
- **CSS Styling (`admin/settings.php`)**:
  - Horizontal scrolling / flex-wrap support for mobile touch screens (`overflow-x: auto`, `white-space: nowrap`, `-webkit-overflow-scrolling: touch`).
  - Sleek cybernetic pills matching the Zeon7 HUD theme (neon border, active glow, small badge counters).

### 3. JavaScript Logic (`admin/js/settings.js`)
- **`categorizeEnvKey(key)`**: Classification helper mapping a key string to one of `eye`, `auth`, `council`, or `system`.
- **Dynamic Counters**: When `.env` is loaded, calculate and display counts on each category pill (`#count-eye`, `#count-auth`, etc.).
- **Combined Filtering**: Update `renderEnvFields()` to evaluate both:
  1. Active category pill (e.g. `currentCategory === 'eye'`).
  2. Active search input query (`#envSearchInput`).
- **Pill Click Handler**: Switching sub-tabs updates the active pill style and immediately filters the card list without reloading the page.

---

## Verification Plan

### Automated Verification
1. Run Playwright script with mobile viewport (390×844) to verify:
   - Category pills render with accurate key count badges.
   - Clicking `👁️ THE EYE` displays only geospatial/satellite/maritime keys.
   - Clicking `🔐 AUTH & SECURITY` displays only authentication and rate-limit keys.
   - Clicking `🧠 COUNCIL & CHAT` displays Council gateway keys.
   - Searching within a sub-tab works combined with the category filter.
2. Verify adding a new key through `➕ ADD KEY` automatically updates the category count and assigns the key to the proper pill.
