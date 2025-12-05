# Visual Analysis Developer Overview

This document explains how the **visual-analysis** workspace (located at `mleo/visual-analysis/`) operates so developers can maintain, extend, or automate the pipeline that feeds ForeverCore sites (starting with mleo) with curated visual assets.

## 1. Purpose & Scope
- Provide a **staging area** for high-resolution visuals before they enter any production gallery.
- Automate repeatable steps: dedupe, naming, categorization, metadata authoring, and SQL generation.
- Maintain a clean separation between the **analysis workspace** and the **live gallery** so experiments never contaminate production.
- Give both humans and AI agents a consistent set of contracts (folders, metadata files, SQL) that the ForeverCore visuals module can consume without manual rewrites.

## 2. Directory Contracts
```
mleo/
├── visuals/                       # 🏛️ Live gallery consumed by the site (docroot-visible)
│   ├── processed/<Category>/      # Production-ready assets grouped by category
│   └── unprocessed/               # (Optional) raw drop zone for the legacy gallery
└── visual-analysis/               # 🔬 Analysis workspace (this system)
    ├── visuals/
    │   ├── unprocessed/           # Input queue for new images
    │   └── processed/<Category>/  # Workspace categories (same list as production)
    ├── dashboard/                 # PHP dashboard + AJAX helpers
    ├── database/                  # schema.sql, comprehensive_seed.sql, visuals_update.sql
    └── *.md, scripts              # Specs & guides (README, GEMINI)
```
Key rules:
- Category names are canonical (AI, Animals, Architecture, CloseUp, Documentary, Editorial, Experimental, GraphicDesign, Illustration, Landscapes, Lifestyle, Nature, Portrait, Seascapes). Additions require updating the dashboard, metadata parser, and ForeverCore category seeds in lockstep.
- The dashboard **reads/writes inside `visual-analysis/visuals/processed`** only. Live galleries remain untouched until you deliberately copy/move curated assets into `../visuals/`.

## 3. Workflow Overview
1. **Deposit assets** into `visual-analysis/visuals/unprocessed/` (or directly in the proper `processed/<Category>/` folder if already sorted).
2. **AI or human agent** runs the `process_image` workflow:
   - Detect duplicates (largest file wins).
   - Choose category, craft descriptive slug (8–45 chars, lowercase, dashes), and write a 50–250 char description.
   - Move/rename the file into `visual-analysis/visuals/processed/<Category>/`.
   - Create a sibling `<slug>.txt` metadata file using the template described below.
3. **Dashboard (`dashboard/index.php`)** visualizes counts per category, pending vs. added totals, and offers buttons for:
   - `UPDATE VISUAL DATA` → `dashboard/update_visual_data.php` (generates SQL and flips metadata flags).
   - `VIEW LIVE VISUALS` → `dashboard/view_live_visuals.php` (readonly explorer that points at `../visuals/`).
4. **SQL output** is appended to `visual-analysis/database/visuals_update.sql`. Execute those statements against the target site DB (e.g., `mleo_db`) and confirm the ForeverCore VisualRepository picks them up.
5. **Promote assets** by copying the curated files from the workspace into `mleo/visuals/processed/` (or whichever site repo consumes them).

## 4. Metadata Contract (`<slug>.txt`)
Each processed image must ship with a plain-text sidecar containing:
```
Name: <slug-without-extension>
Description: <50-250 char sentence>
Category: <CanonicalCategory>
File: <filename-with-extension>
Data Added: false|true
```
- `Data Added: false` means the SQL generator should ingest it.
- After running `update_visual_data.php`, the script rewrites the file with `Data Added: true` to prevent duplicates.
- Developers can safely edit `Description` or `Category` before running the update; the script re-reads the current contents each time.

## 5. PHP Dashboard Components
| File | Responsibility |
| --- | --- |
| `dashboard/index.php` | Loads status cards (counts per category, totals, pending items) and exposes AJAX endpoints. |
| `dashboard/script.js` | Handles UI interactions, triggers update via `fetch('update_visual_data.php')`. |
| `dashboard/update_visual_data.php` | Core worker: scans metadata, builds SQL for `visual_items` and `visual_category_assignments`, writes to `database/visuals_update.sql`, flips metadata flags. |
| `dashboard/view_live_visuals.php` | Tree explorer for `../visuals/`; good for verifying promotions. |
| `dashboard/generate_metadata.php` | Helper invoked by the update script to backfill missing `.txt` files before SQL generation. |

The dashboard is intentionally framework-free (plain PHP, vanilla JS) so it can run inside Codespaces/Dev Containers or any PHP 8+ environment. For ad-hoc testing you can run `php -S localhost:8000 -t mleo/visual-analysis/dashboard`.

## 6. Database Integration
- Tables targeted: `visual_items` (core metadata) and `visual_category_assignments` (many-to-many mapping) — their definitions live in `visual-analysis/database/schema.sql` and mirror the ForeverCore migrations.
- `visuals_update.sql` batches inserts so you can review before applying. After execution, keep the file as an audit trail or clear it once committed.
- If you modify the schema (e.g., add EXIF fields), update **both** the ForeverCore migrations and the generator logic in `update_visual_data.php`.

## 7. Automation Hooks
- **`process_image` shell function** (documented in `README.md` and `GEMINI.md`) is the supported way for agents to manipulate files. Keep it updated if directory paths change.
- Duplicate detection currently relies on the calling agent (compare histograms/hashes, drop smaller files). If you introduce a new dedupe strategy, encapsulate it inside a reusable script so both humans and agents stay consistent.
- Future ForeverCore CLI integration: planned relocation to `forever-core/tools/visual-analysis` so the same workflow can be invoked via `php core/forever-core/bin/visuals:ingest --site=<site> --source=<path>`.

## 8. Environment & Dependencies
- PHP 8.3+, PDO, standard extensions (already available in the upgraded Ubuntu 22.04 stack).
- File permissions: Apache/PHP user must have RW access to `mleo/visual-analysis/visuals` and `mleo/visuals`.
- Optional Dev Container: the repo ships a ready-made container config; when reopened it auto-serves the dashboard on port 8000.

## 9. Testing & Validation
1. Run `php -l dashboard/*.php` after edits.
2. Trigger `UPDATE VISUAL DATA` with a few fixture files and inspect `database/visuals_update.sql` for valid INSERT statements.
3. Apply the SQL to a cloned DB (never `portfolio_db`), then confirm the visuals appear on `hybrid-mleo.zeon7.com/visuals.php` with correct categories and metadata.
4. Tail `logs/error-hybrid-mleo.log` while processing; the ForeverCore layer should never throw missing-file notices after promotion.

## 10. Future Enhancements
- Move the entire workspace under `forever-core/tools/visual-analysis` with a per-site adapter so Zeon7 and Foreverbox can share it.
- Replace the plain `.txt` metadata with JSON or YAML for richer attributes (orientation, photographer credit, EXIF).
- Add queue visibility/status (e.g., WebSocket or polling) so long-running AI batches surface progress in real time.
- Integrate the bulk uploader introduced in the mleo admin (`sites/mleo/public/assets/js/admin-bulk-upload.js`) so admins can push straight into the analysis workspace without SFTP.

Keep this document alongside the other project references in `docs/` so developers can quickly understand how the visual-analysis pipeline fits into the hybrid ForeverCore ecosystem.
