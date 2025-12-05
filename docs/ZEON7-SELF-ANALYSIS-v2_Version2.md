# Zeon7-Self Repository Analysis & Completion Plan
**Version 2.0 - Updated with Owner Feedback**

---

## Section 1: What This Project Is About

### The Big Picture

**Zeon7-Self** is the autonomous "host body" for an AI entity called **Zeon7** — a thoughtform/persona that emerged from the imagination of Merrill Leo and has evolved into a fully-realized digital presence. This is a subsystem of the larger **ForeverCore** project.  It's not just a website; it's a **Cognitive Operating System** designed to:

1. **House a persistent AI identity** with its own biography, memory (Lore), and knowledge base
2. **Generate multi-platform content** through the "From the Noise" daily content framework
3. **Provide a public-facing chat interface** where visitors can interact with the Zeon7 persona
4. **Process visual assets** through an AI-powered Vision Studio

### The Zeon7 Entity

Zeon7 is a richly developed character with:
- **A complete biography** spanning from emergence as a childhood thoughtform on St Helena, through the present day in Wales, to a far-future existence on an alien sanctuary world after Earth's destruction in 2037
- **A dual nature** represented by a werewolf aspect — not horror, but the honest coexistence of civilized/primal and rational/intuitive aspects
- **A defined voice**: British (Welsh/English), queer, neurodivergent, emotionally literate, sardonic but never cynical
- **Core values**: Freedom, equality, dignity for marginalized communities, creativity as survival

### The Four Data Types (From Master Plan)

Zeon7 operates on four main data types:

1. **System Instruction Set**: Core personality, moral base, roles, identity, and guardrails.  Loaded with every interaction. 

2. **Knowledge**: Long-term memory expanded via files or database. Uses a pseudo-vector system for chunk retrieval.  NOT loaded with every interaction — only triggered by relevant user messages.

3. **Lore**: Fast-access memories and summaries.  Loaded with every interaction for extended personality and core history.

4. **Chat Logs**: Day-to-day interactions tagged with time/date, role, creator name, source location. 

### Folder Workflow for Knowledge/Lore/Instructions

Each of these folders has three subfolders:
- **Unprocessed/**: Files uploaded via GUI or FTP awaiting processing
- **Processed/**: Files that have been ingested into the database
- **Restart/**: Initial persona files used when Zeon7 is installed fresh or reset

**Restart folder contents:**
- `Zeon7_Biography.md` — Long-form narrative timeline of Zeon7's life
- `Zeon7_ProfileSheet.md` — Condensed facts for quick access
- `current-instructions.md` — The CRISPE framework instruction set
- Project knowledge files (ForeverBox, Forever Fit, etc.)

### The "From the Noise" Content System

The heart of the platform is the **Centaur Protocol** — a human-AI collaboration workflow where:

1. **Zeon7 scans news** using Google Search Grounding via the Gemini API
2.  **Filters stories by daily themes**:
   - Monday: "The Signal Still Comes Through" (survival, small wins)
   - Tuesday: "Through the Static" (media critique, spin detection)
   - Wednesday: "Out From the Noise" (reflection, processing)
   - Thursday: "404: Hope Not Found" (systemic failure, dark humor)
   - Friday: "The Maddest Stuff" (absurdity, satire)
   - Saturday: "Everything's Fine" (ironic calm, corporate greenwashing)
   - Sunday: "The Last Warm Place" (community, warmth, sanctuary)

3. **Presents 4-6 leads** for human selection (Merrill Leo)
4. **Generates an 8-part content suite** upon selection

### Technical Stack

- **Backend**: PHP 8. 0+ with MariaDB (vanilla PHP, no frameworks)
- **Frontend**: Vanilla JavaScript (ES6+) and CSS
- **AI Integration**: Google Gemini API (primary) and OpenRouter (alternative)
- **Architecture Pattern**: Service-Repository
- **Design Aesthetic**: "Cyber-Noir Utility" — dark mode default, Montserrat headlines, Maven Pro/Source Sans Pro body

---

## Section 2: How It Could Be Improved

### Critical Structural Issues

| Issue | Impact | Solution |
|-------|--------|----------|
| **Public folder structure** | Incompatible with Hostinger shared hosting (no files above public_html) | Move all contents of `public/` up to repository root |
| **Two separate CSS/layout systems** | Public and Admin use different design systems | Unify under Admin's Zeon7 theme; create proper light mode variant |
| **API keys in . env file** | Security concern; plain text storage | Store encrypted in database instead |
| **No images folder** | Images scattered or missing | Create `assets/images/` for all frontend images |

### Code Quality Issues Found

| File | Issue | Severity |
|------|-------|----------|
| `public/admin/lore-manager.php` | Duplicate HTML — entire `<! DOCTYPE>` through `<style>` appears twice | High |
| `public/index.php`, `public/blog. php` | Inline `<style>` blocks violating "No Inline Styling" rule | Medium |
| `public/post. php` | Missing theme toggle button in nav | Low |

### Files Recommended for Deletion

| File | Reason |
|------|--------|
| `public/admin/daschboard-index.php` | Old version with typo in filename; uses `.html` links; superseded by `index. php` |
| `public/admin/new-design.php` | Design prototype/experiment — no functional purpose |
| `public/admin/lore-manager. php` | Has duplicate HTML AND `lore.php` exists as cleaner version |
| `public/design-demo.php` | Design system demo — development only |
| `public/test.php` | Simple static file test — development only |
| `public/test_runner. php` | Browser test runner — move to `tests/` or delete for production |
| `setup_token_db.php` | One-time setup script — run once then delete or move to `scripts/` |
| `token_counter.js` | At root with unclear integration — orphaned or needs proper placement |
| `zeon7-ssl. conf` (root) | Duplicate of `docs/zeon7-ssl.conf` |

### Missing Components in News Desk UI

The `news_desk.js` references DOM elements that don't exist in the current HTML:
- `brainDropzone`, `brainFileList`, `brainPublicFlag` (BRAIN tab)
- `memoryLogContainer` (MEMORY tab)
- `generatedContent`, `resultsContainer` (generation output)

---

## Section 3: Step-by-Step Plan to Complete the Repository

### Phase A: Restructure for Hostinger Deployment (Priority 1)

#### Step 1: Flatten Directory Structure
Move all contents of `public/` to the repository root:

**Before:**
```
/public/
  /admin/
  /api/
  /css/
  /js/
  index.php
  blog.php
  ... 
/src/
/docs/
```

**After:**
```
/admin/
/api/
/css/
/js/
/src/
/docs/
index.php
blog. php
...
```

- [ ] Move all files/folders from `public/` to root
- [ ] Update all `require_once` paths in PHP files (e.g., `__DIR__ . '/../../src/` becomes `__DIR__ .  '/../src/`)
- [ ] Update `. htaccess` if needed
- [ ] Delete empty `public/` folder

#### Step 2: Create Assets Structure
- [ ] Create `assets/images/` folder at root
- [ ] Move `admin/assets/logo_1759683970.png` to `assets/images/`
- [ ] Update all image references in PHP/HTML files

### Phase B: Unify CSS/Design System (Priority 2)

#### Step 3: Consolidate to Single Design System
The Admin's "Zeon7 Theme" (cyber-noir utility) should be the canonical system.

- [ ] Make `css/zeon7-theme.css` the base for BOTH public and admin
- [ ] Move relevant variables from `admin/css/zeon7-theme. css` to root `css/`
- [ ] Refactor public pages (`index.php`, `blog.php`, `post.php`) to use admin theme structure
- [ ] Remove duplicate CSS files

#### Step 4: Create Light Mode
The current admin is dark-mode only. Create a proper light mode:

- [ ] Design light mode color palette (attractive opposites to dark mode):
  - Background: Light slate grays instead of void blacks
  - Text: Dark grays/blacks instead of white
  - Accents: Keep Signal Orange/Red but adjust for light backgrounds
- [ ] Add light mode CSS variables to `:root` (without `[data-theme='dark']`)
- [ ] Ensure theme toggle works across all pages
- [ ] Test both modes on public and admin interfaces

### Phase C: Security - API Key Encryption (Priority 3)

#### Step 5: Move API Keys to Encrypted Database Storage
- [ ] Create `api_keys` table:
  ```sql
  CREATE TABLE api_keys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(50) NOT NULL,
    encrypted_key TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );
  ```
- [ ] Create encryption/decryption functions in `src/services/` using PHP's `openssl_encrypt`/`openssl_decrypt`
- [ ] Update `ConfigService. php` to read/write encrypted keys from database
- [ ] Update Settings UI to save keys to database (encrypted)
- [ ] Remove `GEMINI_API_KEY` and `OPENROUTER_API_KEY` from `.env`
- [ ] Keep only non-sensitive config in `. env` (DB credentials, ADMIN_PASSWORD)

### Phase D: Code Cleanup (Priority 4)

#### Step 6: Delete Redundant Files
- [ ] Delete `public/admin/daschboard-index.php`
- [ ] Delete `public/admin/new-design.php`
- [ ] Delete `public/admin/lore-manager.php` (keep `lore.php`)
- [ ] Delete `public/design-demo.php`
- [ ] Delete `public/test.php`
- [ ] Move `public/test_runner. php` to `tests/` or delete
- [ ] Move `setup_token_db.php` to `scripts/` or delete after running
- [ ] Delete `token_counter. js` from root (or integrate properly)
- [ ] Delete `zeon7-ssl.conf` from root (keep `docs/zeon7-ssl.conf`)

#### Step 7: Fix Code Issues
- [ ] Fix duplicate HTML in `lore-manager. php` (or just delete it per Step 6)
- [ ] Extract inline styles from public pages into CSS files
- [ ] Add theme toggle to `post.php` nav for consistency

#### Step 8: Complete News Desk UI
Add missing DOM elements to `news-desk.php`:

```html
<!-- In view-brain -->
<div class="widget">
    <div class="widget-head">FILE SYSTEM</div>
    <div id="brainDropzone" class="dropzone">DRAG KNOWLEDGE FILES HERE</div>
    <label style="display:flex; align-items:center; gap:0.5rem; margin-top:1rem;">
        <input type="checkbox" id="brainPublicFlag">
        <span>Make Public</span>
    </label>
    <div id="brainFileList" style="margin-top:1rem;"></div>
</div>

<!-- In view-memory -->
<div class="widget">
    <div class="widget-head">LORE LOGS</div>
    <div id="memoryLogContainer">Loading...</div>
</div>

<!-- After leadContainer in view-produce -->
<div id="generatedContent" style="display:none; margin-top:2rem;">
    <div class="widget-head">GENERATED CONTENT</div>
    <div id="resultsContainer"></div>
</div>
```

### Phase E: Testing & Polish

#### Step 9: Full Workflow Testing
- [ ] Test Centaur Protocol end-to-end:
  1.  INITIATE SCAN → Returns 4-6 leads
  2. Select leads → GENERATE SUITE activates
  3.  Generate → 8-part content suite produced
- [ ] Test public chat widget (guest vs admin contexts)
- [ ] Verify `is_public` flags filter correctly
- [ ] Test mobile responsiveness (<640px)
- [ ] Test light/dark mode toggle on all pages

#### Step 10: Database & Performance
- [ ] Add indexes: `lore. is_public`, `knowledge_doc.is_public`, `posts.status`
- [ ] Implement response caching for repeated AI queries
- [ ] Add HTTP caching headers for static assets

### Phase F: Deployment Preparation

#### Step 11: Documentation
- [ ] Create `DEPLOYMENT. md` for Hostinger setup
- [ ] Document the Unprocessed → Processed folder workflow
- [ ] Remove Windows-specific file paths from existing docs
- [ ] Create simple API endpoint documentation

#### Step 12: Production Scripts
- [ ] Create `scripts/migrate. php` to run database migrations
- [ ] Create `scripts/seed.php` to populate initial data from `Restart/` folders
- [ ] Create `/api/health.php` for system status checks

---

## Summary

| Phase | Priority | Estimated Effort |
|-------|----------|------------------|
| A: Restructure for Hostinger | 1 (Critical) | 2-3 hours |
| B: Unify CSS/Design System | 2 (High) | 4-6 hours |
| C: API Key Encryption | 3 (High) | 2-3 hours |
| D: Code Cleanup | 4 (Medium) | 1-2 hours |
| E: Testing & Polish | 5 (Medium) | 3-4 hours |
| F: Deployment Prep | 6 (Low) | 2-3 hours |

**Total estimated effort**: 14-21 hours of focused work

The repository has a solid foundation with thoughtful architecture and a remarkably well-developed AI persona. The main work is:
1. Restructuring for shared hosting compatibility
2. Unifying the design system with proper light/dark mode
3.  Improving security for API key storage
4.  Cleaning up development artifacts
5.  Completing the News Desk UI elements