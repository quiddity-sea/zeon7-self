# Zeon7-Self Task Checklist

## Phase A: Restructure for Hostinger Deployment (Priority 1)
- [x] Move all contents of `public/` to repository root
- [x] Update `require_once` paths in PHP files (fix `__DIR__`)
- [x] Update `.htaccess` if needed
- [x] Delete empty `public/` folder
- [x] Create `assets/images/` folder at root
- [x] Move images (e.g., logo) to `assets/images/` and update references

## Phase B: Unify CSS/Design System (Priority 2)
### Consolidate Design System
- [x] Make `css/zeon7-theme.css` the base for BOTH public and admin
- [x] Move relevant variables from `admin/css` to root `css/`
- [x] Refactor `index.php`, `blog.php`, `post.php` to use admin theme structure
- [x] Remove duplicate CSS files
### Light Mode
- [x] Design light mode color palette
- [x] Add light mode CSS variables to `:root`
- [x] Ensure theme toggle works across all pages
- [x] Test both modes on public and admin interfaces

## Phase C: Security - API Key Encryption (Priority 3)
- [x] Create `api_keys` database table
- [x] Create encryption/decryption functions in `src/services/`
- [x] Update `ConfigService.php` to use database for keys
- [x] Update Settings UI to manage encrypted keys
- [x] Remove API keys from `.env` (keep DB credentials)

## Phase D: Code Cleanup (Priority 4)
### Delete Redundant Files
- [x] `public/admin/daschboard-index.php`
- [x] `public/admin/new-design.php`
- [x] `public/admin/lore-manager.php` (superseded by `lore.php`)
- [x] `public/design-demo.php`
- [x] `public/test.php`
- [x] `zeon7-ssl.conf` (root)
- [x] `token_counter.js`
### Move Files
- [x] Move `public/test_runner.php` to `tests/` (Not found / Deleted)
- [x] Move `setup_token_db.php` to `scripts/`
### Fix Code Issues
- [x] Fix duplicate HTML/logic in `lore-manager.php` / `lore.php`
- [x] Extract inline styles from public pages
- [x] Add theme toggle to `post.php` nav
### Complete News Desk UI
- [x] Add missing DOM elements to `news-desk.php` (`brainDropzone`, `memoryLogContainer`, `generatedContent`, etc.)

## Phase E: Testing & Polish (Priority 5)
- [ ] Test Centaur Protocol end-to-end (Scan -> Generate)
- [ ] Test public chat widget (guest vs admin)
- [ ] Verify `is_public` flags work
- [ ] Test mobile responsiveness
- [ ] Test light/dark mode
- [x] Add database indexes (`knowledge_chunk.content` FULLTEXT)
- [ ] Implement response caching
- [ ] Add HTTP caching headers

## Phase F: Deployment Preparation (Priority 6)
- [ ] Create `DEPLOYMENT.md`
- [ ] Document Unprocessed/Processed folder workflow
- [ ] Remove Windows paths from docs
- [ ] Create API documentation
- [x] Create `scripts/migrate.php` (Implemented via individual `fix_` and `create_` scripts)
- [x] Implement System Restart Protocol (`SystemResetService`)
- [x] Add "Factory Reset" to Admin Settings
- [x] Repair DB Schemas (`knowledge_doc`, `lore`, `system_instructions`, `chat_logs`)
- [ ] Create `/api/health.php`

## Summary
- [ ] Phase A: Restructure for Hostinger Setup
- [ ] Phase B: Unified Design System
- [ ] Phase C: Security (API Key Encryption)
- [ ] Phase D: Code Cleanup
- [ ] Phase E: Testing & Polish
- [ ] Phase F: Deployment Preparation
