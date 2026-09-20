# Implementation Plan: Dynamic APIs & Keys Admin UI

## Goal Description
Add a new administrative tab called "APIs & Keys" to the `settings.php` page in the Self web app. This tab will allow the user to easily manage (edit, change, delete) API keys directly from their mobile phone. It will dynamically parse the `.env` file, self-updating the UI to expose any current or future keys without requiring frontend code changes.

## User Review Required
- **Security Implications:** Exposing the `.env` file in the web UI means all secrets (including `DB_PASS`) will be accessible to any user who passes `AuthMiddleware::enforcePageAuth()`. Ensure only authorized administrators have access to this page.
- **Save Strategy (Regex):** To preserve comments, structure, and spacing in the `.env` file, we will use regex replacement to update values inline, rather than `parse_ini_file()` and overwriting everything.
- **Delete Behavior:** Deleting a key in the UI will physically remove the corresponding `KEY=VALUE` line from the `.env` file.

## Proposed Changes

### UI Components (`admin/settings.php`)
- **[MODIFY]** `admin/settings.php`
  - Add a 4th tab button in `.settings-tabs`: `<button type="button" class="settings-tab-btn" data-tab="tab-api-keys" id="tabBtnApiKeys">APIs &amp; Keys</button>`
  - Add the corresponding tab pane: `<div class="settings-tab-pane" id="tab-api-keys" style="display: none;">`
  - Inside the pane, create a responsive container to hold the dynamically generated form fields (e.g., `<div id="envFieldsContainer" class="settings-container"></div>`).
  - Add Save and Reset buttons at the bottom of the pane.

### JavaScript Logic
- **[MODIFY]** `admin/settings.php` (Script block)
  - Tab Switching logic: Ensure the new tab is bound to the existing tab-switching JS.
  - **[NEW]** `fetchEnvConfig()`: An AJAX `fetch` call to retrieve the current `.env` keys/values on page load or tab click.
  - **[NEW]** `renderEnvFields(data)`: Dynamically generates `<div class="form-group">` with labels, input fields, and a small "Delete" button (X or Trash icon) for each key.
  - **[NEW]** `saveEnvConfig()`: Collects all keys/values from the generated inputs, packages them as a JSON object, and sends a POST request to the backend save endpoint.

### Backend API Endpoints
- **[NEW]** `admin/api/env_handler.php` (or similar endpoint)
  - **GET Action:**
    - Validates `AuthMiddleware::enforceApiAuth()`.
    - Reads the `.env` file line by line.
    - Uses regex `^([A-Za-z0-9_]+)=(.*)$` to extract valid keys and values.
    - Skips comments (`#`) and empty lines.
    - Returns a JSON dictionary of `{ "KEY": "VALUE" }`.
  - **POST Action:**
    - Validates `AuthMiddleware::enforceApiAuth()`.
    - Receives a JSON payload of all keys and their new values.
    - Reads the entire `.env` file into a string.
    - Iterates over the original file lines.
    - If a line matches an existing key, updates the line with the new value.
    - If a key was submitted in the payload but doesn't exist in the file, appends it to the end.
    - If a key existed in the file but is missing from the payload (marked for deletion), removes the line.
    - Writes the modified string back to the `.env` file, preserving all original comments and empty lines.

## Verification Plan

### Manual Verification
1. Open `settings.php` on a mobile device or desktop browser.
2. Click the "APIs & Keys" tab.
3. Verify that all existing `.env` keys (e.g., `DB_HOST`, `DB_NAME`, `APP_ENV`) populate in the form dynamically.
4. Modify a key (e.g., add `CARTO_API_KEY=test1234`) and click Save.
5. Check the raw `.env` file on the server to ensure the change was written and comments were perfectly preserved.
6. Delete a key using the UI and confirm the line is entirely removed from `.env`.
