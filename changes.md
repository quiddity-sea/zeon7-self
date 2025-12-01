# Changelog

## [2025-12-01] Public Chat Guardrails Implementation

### Summary
Implemented the "Safe Harbor" guardrails for the Public Chat interface, ensuring strict separation between public and private data and enforcing the "Chronic Caregiver" persona for guest users.

### Database Changes
-   **Migration Script:** `docs/database/002_add_public_flags.sql`
-   **`knowledge_doc` Table:** Added `is_public` (BOOLEAN) column.
-   **`lore` Table:** Refactored from Key-Value to Journaling schema (`type`, `content`, `tags`, `is_public`).

### Backend Services
-   **`KnowledgeService.php`:** Updated `uploadFile` to accept `is_public` flag. Updated `searchChunks` to optionally filter by `is_public`.
-   **`LoreService.php`:** Rewritten to support new schema. Added `getPublic()` method.

### API Endpoints
-   **`POST /api/knowledge/upload.php`:** Now accepts `is_public` param.
-   **`POST /api/lore/upsert.php`:** Updated for new Lore schema.
-   **`DELETE /api/lore/delete.php`:** Updated to delete by ID.
-   **`POST /api/chat.php`:** Implemented "Safe Harbor" logic:
    -   Fetches only public Knowledge and Lore.
    -   Injects "Chronic Caregiver" system prompt.
    -   Prevents access to private internal data.

### Frontend UI
-   **News Desk (`news_desk.js`):** Fixed corrupted functions. Added "Public" checkbox to Quick Ingest.
-   **Lore Manager (`lore-manager.php`, `lore.js`):** Full UI implementation for managing Lore entries with visibility controls.
