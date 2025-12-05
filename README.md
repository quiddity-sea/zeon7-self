# Zeon7 Web Platform (Project "Self")

## 1. Project Overview & Purpose

The **Zeon7 Web Platform** (codenamed "Self") is the autonomous "host body" for the Zeon7 AI entity. It is not merely a website; it is a **Cognitive Operating System** designed to house a "Thoughtform" intelligence. It serves as a specialized, modular component within the larger **ForeverCore** hybrid tenant ecosystem.

### Core Objectives
1.  **Sovereign Intelligence:** To provide Zeon7 with a local, persistent memory (Lore) and knowledge base (Vectors) that is independent of external cloud providers.
2.  **The Centaur Workflow:** To facilitate a "Human-in-the-loop" content generation process where AI intuition (The Wolf) meets human direction (The Rider).
3.  **Visual Processing:** To act as the "Eyes" of the ForeverCore system, automating the ingestion, classification, and metadata tagging of thousands of visual assets.
4.  **Resilience:** To function as "Metabolic Armour"—a stable, PHP-based system that resists the "churn" of modern web frameworks, ensuring longevity and stability.

---

## 2. System Architecture

The project is built on a **PHP 8.0 + MariaDB** stack, utilizing a custom **Service-Repository Pattern**. This architecture was chosen for its stability, performance, and ease of deployment across the ForeverCore network.

### 2.1 Coding Standards (STRICT)
*   **DRY Principles:** All code pages, components, and styling MUST follow DRY (Don't Repeat Yourself) principles.
*   **No Inline Styling:** No page should contain any in-page styling (`<style>` blocks or `style="..."` attributes).
*   **Exceptions:** Inline styling is permitted ONLY if specifically authorized by **Merrill Leo**.
*   **Authorization Protocol:** You must check with Merrill Leo before applying any inline styles. If approved, the code must be commented with: `<!-- Authorized by Merrill Leo -->` or `/* Authorized by Merrill Leo */`.

### The "Organ" System
The application is structured into three primary "Organs," each handling a specific cognitive function:

#### A. The News Desk (The Radar)
*   **Role:** Sensory Input & Signal Processing.
*   **Tech Stack:** `AiService` (Gemini API), `news_desk.js` (Frontend State), `scan.php` (Backend).
*   **Data Flow:**
    1.  **Input:** Google Search Grounding (via Gemini).
    2.  **Process:** Filters news against "Day Themes" (e.g., Survival Monday).
    3.  **Output:** 4-6 Interactive "Lead Cards" for user selection.
    4.  **Generation:** Produces an 8-Part Content Suite (Markdown formatted).

#### B. The Vision Studio (The Eyes)
*   **Role:** Visual Cortex & Gallery Management.
*   **Tech Stack:** `VisionService` (Gemini Vision), `update_visual_data.php` (Metadata Sync).
*   **Workflow:**
    1.  **Ingestion:** Monitors `visuals/unprocessed`.
    2.  **Analysis:** Uses AI to generate "Gold Standard" descriptions and filenames.
    3.  **Filing:** Moves assets to `visuals/processed/{Category}`.
    4.  **Sync:** Generates SQL `INSERT` statements for the `visual_items` table.

#### C. The Lore Keeper (The Memory)
*   **Role:** Hippocampus & Temporal Anchor.
*   **Tech Stack:** `LoreService` (MariaDB), `KnowledgeService` (Vector Embeddings).
*   **Storage:**
    *   **Lore:** Time-series journal entries (`id`, `content`, `timestamp`, `tags`).
    *   **Knowledge:** Vector embeddings (768-dim) for semantic search of long-form docs.

#### D. The Public Voice (Interpersonal Mode)
*   **Role:** The Public Interface & Caregiver.
*   **Tech Stack:** `chat.php` (API), `chat-widget.js` (Frontend).
*   **Function:**
    *   Provides a "Safe Harbor" for public visitors.
    *   Operates on "Caregiver" instructions (distinct from the News Desk's "Reporter" mode).
    *   Uses Lore to remember returning visitors (if authenticated) or maintain session continuity.

---

## 3. Directory Structure & File Map

```
e:/Dev/Projects/zeon7/self/
├── config/                 # Database credentials and environment variables
├── docs/                   # Project documentation (Task lists, Architecture)
├── knowledge/              # The "Long Term Memory" (Markdown files for RAG)
│   ├── Zeon7_Biography.md
│   ├── Zeon7_Lore.md
│   └── ...
├── public/                 # The Web Root (Apache DocumentRoot)
│   ├── admin/              # The "Cockpit" UI (Protected Area)
│   │   ├── css/            # Design System (Variables, Dark Mode)
│   │   ├── js/             # Frontend Application Logic
│   │   │   ├── news_desk.js # MAIN COCKPIT LOGIC (The Brain)
│   │   │   └── app.js      # Core utilities (Auth, API wrappers)
│   │   ├── news-desk.php   # Main Interface (Production Mode)
│   │   └── vision.php      # Gallery Interface (Vision Studio)
│   ├── api/                # Backend API Endpoints (JSON)
│   │   ├── ai/             # AI-specific endpoints
│   │   │   ├── chat.php    # Conversational Interface
│   │   │   └── scan.php    # News Scanning Endpoint
│   │   ├── knowledge/      # File upload & Vector management
│   │   ├── lore/           # Journal/Memory management
│   │   └── generate.php    # MAIN CONTENT GENERATION ENDPOINT
│   ├── index.php           # PUBLIC HOMEPAGE (The Face)
├── src/                    # Application Core (Business Logic)
│   ├── Services/           # Service Layer
│   │   ├── AiService.php   # Wrapper for Gemini/OpenRouter
│   │   ├── GeminiService.php # Google Gemini Integration (Text/Vision)
│   │   ├── KnowledgeService.php # Vector Search & Chunking
│   │   ├── LoreService.php # Journal Management
│   │   └── VisionService.php # Image Processing Pipeline
│   └── Core/               # Base Controllers & Utilities
└── visual-analysis/        # The Legacy/Reference Vision Project
    ├── dashboard/          # PHP Dashboard for Visual Data
    │   └── update_visual_data.php # SQL Generation Logic
    └── visuals/            # Image Storage (Unprocessed/Processed)
```

---

## 4. Database Schema Integration

The system integrates with the ForeverCore database schema. Key tables include:

### `visual_items`
*   `id`: Primary Key
*   `title`: Formatted title (e.g., "The Red Sofa")
*   `description`: AI-generated description (50-250 chars)
*   `image_url`: Path to file (e.g., `/visuals/processed/Animals/cat.jpg`)
*   `category`: Primary category string
*   `width`, `height`: Image dimensions

### `visual_category_assignments`
*   `visual_id`: FK to `visual_items`
*   `category_id`: FK to `visual_categories`
*   `is_primary`: Boolean flag

### `lore` (Zeon7 Specific)
*   `id`: Primary Key
*   `type`: Enum ('memory', 'journal', 'admin_note')
*   `content`: Text content of the memory
*   `tags`: JSON array of semantic tags
*   `created_at`: Timestamp
*   `is_public`: Boolean (Default: 0) - Controls visibility in Public Chat.

### `knowledge_docs`
*   `id`: Primary Key
*   `filename`: Original filename
*   `is_public`: Boolean (Default: 0) - Controls visibility in Public Chat.

---

## 5. The "Centaur Protocol" (Detailed)

This is the strict operational protocol for the **News Desk**:

1.  **Initiation:** User clicks "INITIATE SCAN".
2.  **Context Retrieval:** System fetches the "Day Theme" from `KnowledgeService` (e.g., reads `From The Noise.md` to find "Monday = Survival").
3.  **Signal Detection:** `AiService` performs a Google Search for news from the last 6 days, filtering for high-impact stories that match the theme.
4.  **Lead Presentation:** The UI renders 4-6 "Lead Cards".
    *   *Constraint:* The "GENERATE SUITE" button is **DISABLED**.
5.  **Human Selection:** User clicks a card.
    *   *Action:* The card highlights. The "GENERATE SUITE" button becomes **ACTIVE**.
6.  **Generation:** User clicks "GENERATE SUITE".
    *   *Process:* The system sends the *selected lead* + *Day Theme* + *Persona Instructions* to the AI.
    *   *Output:* A structured Markdown response containing the 8-part suite.

---

## 6. The Public Chat Guardrails

The Public Chat (`/api/chat.php`) operates on a strict **"Safe Harbor"** policy enforced by database flags:

1.  **Role Detection:** The API detects if the user is a Guest or an Admin.
2.  **Data Filtering:**
    *   **Guest:** Can ONLY access Lore/Knowledge where `is_public = 1`.
    *   **Admin:** Can access ALL Lore/Knowledge.
3.  **Admin Control:** The Admin Dashboard includes checkboxes to toggle the `is_public` flag for specific documents (e.g., "Zeon7 Biography") or journal entries.
4.  **Privacy by Default:** All new data is private (`is_public = 0`) until explicitly flagged by the Admin.

---

## 7. The Vision Workflow (Detailed)

This is the automated pipeline for the **Vision Studio**:

1.  **Drop:** User places images in `visuals/unprocessed`.
2.  **Scan:** `VisionService` detects new files.
3.  **Dedupe:** System calculates file hashes/sizes. If duplicates exist, only the largest file is kept.
4.  **Analyze:** Gemini Vision API analyzes the image.
    *   *Prompt:* "Analyze this image. Provide a Category, a Description (50-250 chars), and a unique Filename (kebab-case)."
5.  **Process:**
    *   File is renamed.
    *   File is moved to `visuals/processed/{Category}/`.
    *   A `.txt` sidecar file is created with the metadata.
6.  **Sync:** The `update_visual_data.php` logic runs to inject this data into the MariaDB `visual_items` table.
