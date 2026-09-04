# Implementation Plan - Zeon7 Self V2

## Goal
Execute the comprehensive completion plan for the Zeon7-Self repository as outlined in `ZEON7-SELF-ANALYSIS-v2_Version2.md`. This involves restructuring the project for shared hosting compatibility, unifying the design system (including light mode), securing API keys, cleaning up the codebase, completing the News Desk UI, and preparing for deployment.

## User Review Required
> [!IMPORTANT]
> **Directory Restructuring**: Phase A involves moving all contents of the `public/` directory to the repository root. This is a significant structural change to support Hostinger shared hosting.
> **Database Changes**: Phase C involves creating a new `api_keys` table and storing keys in the database instead of the `.env` file.

## Proposed Changes

### Phase A: Restructure for Hostinger Deployment (Priority 1)
#### [MODIFY] [Directory Structure]
- Move all files/folders from `public/` to the repository root.
- Delete the empty `public/` folder.
- Update `require_once` paths in PHP files (e.g., change `__DIR__ . '/../../src/` to `__DIR__ . '/../src/`).
- Update `.htaccess` if needed.

#### [NEW] [assets/images/]
- Create `assets/images/` folder at the root.
- Move `admin/assets/logo_1759683970.png` to `assets/images/`.
- Update all image references in PHP/HTML files.

### Phase B: Unify CSS/Design System (Priority 2)
#### [MODIFY] [css/zeon7-theme.css]
- Make `css/zeon7-theme.css` the base for BOTH public and admin.
- Move relevant variables from `admin/css/zeon7-theme.css` to root `css/`.
- Refactor public pages (`index.php`, `blog.php`, `post.php`) to use the admin theme structure.
- Remove duplicate CSS files.

#### [NEW] [Light Mode Support]
- Design light mode color palette (Light slate grays, Dark grays/blacks text).
- Add light mode CSS variables to `:root`.
- Ensure theme toggle works across all pages.

### Phase C: Security - API Key Encryption (Priority 3)
#### [NEW] [Database Table: api_keys]
- Create `api_keys` table with columns: `id`, `provider`, `encrypted_key`, `created_at`, `updated_at`.

#### [NEW] [src/services/EncryptionService.php]
- Create functions for encryption/decryption using `openssl_encrypt`/`openssl_decrypt`.

#### [MODIFY] [src/services/ConfigService.php]
- Update to read/write encrypted keys from the database.

#### [MODIFY] [admin/settings.php]
- Update Settings UI to save keys to the database (encrypted).

#### [MODIFY] [.env]
- Remove `GEMINI_API_KEY` and `OPENROUTER_API_KEY`.

### Phase D: Code Cleanup (Priority 4)
#### [DELETE] [Redundant Files]
- `public/admin/daschboard-index.php`
- `public/admin/new-design.php`
- `public/admin/lore-manager.php` (keep `lore.php`)
- `public/design-demo.php`
- `public/test.php`
- `zeon7-ssl.conf` (keep `docs/zeon7-ssl.conf`)
- `token_counter.js`

#### [MOVE]
- `public/test_runner.php` -> `tests/`
- `setup_token_db.php` -> `scripts/`

#### [NEW] [System Restart Functionality]
Create `src/Services/SystemResetService.php` to:
- Clear `lore`, `knowledge_doc`, `knowledge_chunk`, `system_instructions` tables.
- Read files from `instructions/restart/`, `lore/restart/`, `knowledge/restart/`.
- Parse and seed data into respective tables.

Create `admin/api/system-reset.php` endpoint.
Update `admin/settings.php` with a "Factory Reset" button.

#### [MODIFY] [Code Issues]
- Provide clean implementation for `lore.php` to replace `lore-manager.php`.
- Extract inline styles from public pages into CSS files.
- Add theme toggle to `post.php` nav.

#### [MODIFY] [news-desk.php]
- Add missing DOM elements (`brainDropzone`, `brainFileList`, `brainPublicFlag`, `memoryLogContainer`, `generatedContent`, `resultsContainer`).

### Phase E: Testing & Polish (Priority 5)
#### [VERIFY] [Workflows]
- Test Centaur Protocol end-to-end (Scan -> Leads -> Generate).
- Test public chat widget.
- Verify `is_public` flags.
- Test mobile responsiveness and light/dark mode.

#### [MODIFY] [Database & Performance]
- Add indexes: `lore.is_public`, `knowledge_doc.is_public`, `posts.status`.
- Implement response caching for repeated AI queries.
- Add HTTP caching headers.

### Phase F: Deployment Preparation (Priority 6)
#### [NEW] [Documentation]
- `DEPLOYMENT.md` for Hostinger setup.
- Document Unprocessed → Processed folder workflow.
- Create API endpoint documentation.
- Create `scripts/migrate.php` and `scripts/seed.php`.
- Create `/api/health.php`.

## Verification Plan

### Automated Tests
- Verification of successful build/lint (if applicable).
- Testing of critical paths (Login, News Desk flow) via Browser tool.

### Manual Verification
- Deploy to local test environment (WSL).
- Verify directory structure matches the new "Hostinger-ready" layout.
- Verify Admin Dashboard loads correctly with the unified theme.
- Toggle Light/Dark mode and check for visual consistency.
- Perform a News Desk scan and generation cycle.
- Verify API keys are stored encrypted in the DB.
