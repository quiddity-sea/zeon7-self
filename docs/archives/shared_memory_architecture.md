# Zeon7 Shared Memory System Architecture

## Overview
The Zeon7 Shared Memory System is a unified architecture designed to provide persistent identity, context, and knowledge across multiple user interactions and "AI Drag" personas (Zeon7, Clair4, Dave8, etc.). It bridges the gap between ephemeral chat sessions and long-term knowledge retention.

## Core Components

### 1. The "AI Drag" Identity Layer
*   **Concept:** Zeon7 is the singular underlying intelligence. "Clair4" or "Dave8" are merely presentation layers (personas).
*   **Shared Access:** All personas access the same central database of Lore, Knowledge, and History.
*   **Differentiation:** Personas are distinguished by their specific **System Instruction** set, which dictates *how* they present the shared information (tone, style, vocabulary).

### 2. Data Ingestion & Processing

#### A. Knowledge Files (`knowledge/` folder)
*   **Source:** Markdown (`.md`) or Text (`.txt`) files uploaded via the Admin Panel or placed in the directory.
*   **Example:** `from_the_noise_daily_content_framework_zeon_7_merrill_leo.md`
*   **Processing Pipeline:**
    1.  **Ingestion:** System detects new file.
    2.  **Chunking:** File is split into logical segments (e.g., by headers `##`).
    3.  **Embedding (Future):** Chunks are converted to vector embeddings for semantic search.
    4.  **Storage:**
        *   `knowledge_doc` table: Stores file metadata AND **Full Content** (`LONGTEXT`). This allows the AI to read the entire document if the chunks are insufficient.
        *   `knowledge_chunk` table: Stores the searchable text segments (Vectors/Embeddings) AND **Location Metadata** (`start_offset`, `length`) to quickly locate this chunk within the main document for context expansion.
*   **Retrieval:** When a user asks a question, the system searches `knowledge_chunk` for relevant segments (RAG) and injects them into the prompt.

#### B. Instruction Files (`instructions/` folder)
*   **Source:** `current-instructions.md` (or similar).
*   **Processing:**
    1.  **Versioning:** New instructions are saved as a new version in the `instruction_set` table.
    2.  **Activation:** The "Active" version is loaded into the System Prompt for every chat session.

### 3. The Database Layer (Memory Bank)

| Table | Purpose | Content Type |
| :--- | :--- | :--- |
| `lore` | **Long-Term Facts** | Key-Value pairs. **Scoped by `user_id`** (Private) or marked Global (Public). |
| `chat_history` | **Short-Term Context** | Raw log of recent messages (`role`, `content`, `timestamp`). |
| `knowledge_chunk` | **Reference Library** | Searchable text segments from uploaded documents. |
| `gemini_log` | **Audit Trail** | Token usage, costs, and error logs (not used for context). |
| `daily_context` | **Dynamic Context** | Daily themes (e.g., "Survival Monday") derived from the Framework file. |

### 4. Prompt Construction (The "Context Window")

When a user sends a message, the system constructs a **Composite Prompt** dynamically:

```text
[SYSTEM INSTRUCTION]
"You are Zeon7... [Content from active instruction_set]..."

[DYNAMIC CONTEXT]
"Today is Monday. Theme: The Signal Still Comes Through."
"Tone: Sincere, Resilient."
(Fetched from `daily_context` table)

[LORE INJECTION]
"Recall: User prefers dark mode. Project Alpha is active."
(Fetched from `lore` table based on keywords AND `user_id` scope. *Private lore is never shared between users.*)

[KNOWLEDGE RAG]
"Reference: According to the Daily Content Framework..."
(Fetched from `knowledge_chunk`. *System can also read full `knowledge_doc` content if deep context is required.*)

[CHAT HISTORY]
User: "Hello"
AI: "Signal received."
(Last 10-20 messages from `chat_history`)

[CURRENT INPUT]
User: "What should I post today?"
```

### 5. The Summarizer Workflow (Memory Consolidation)
To prevent the Chat History from growing indefinitely, a **Summarizer** process runs periodically:
*   **Triggers:**
    *   Every 75 interactions.
    *   User command: "zeon7 backup".
    *   Cron Job: Idle > 30m.
*   **Action:**
    1.  Reads the recent `chat_history`.
    2.  Asks the AI: "Summarize the key facts and user preferences from this conversation."
    3.  Saves the summary as new entries in the `lore` table.
    4.  (Optional) Archives/Clears the raw `chat_history` to free up context space.

## Specific Reference: `from_the_noise_daily_content_framework...`
This specific file acts as the **Master Schedule**.

## 6. Implementation Guide for `instructions.php`
For the AI agent working on the Instructions Editor:

### Goal
The `instructions.php` page is the **Identity Editor**. It allows the user to modify the core persona (Zeon7) that permeates the entire system.

### Database Integration
*   **Table:** `instruction_set`
*   **Columns:**
    *   `id` (PK)
    *   `version` (INT, Auto-increment logic required)
    *   `content` (MEDIUMTEXT, The actual prompt)
    *   `created_at` (DATETIME)
    *   `created_by` (VARCHAR, e.g., 'admin')

### Workflow Requirements
1.  **File Ingestion:** If a new file is detected in `instructions/` (e.g., `new_persona.md`), the system should automatically parse it and propose it as a new Version.
2.  **Fetch Current:** On load, fetch the row with the highest `version` number. Display this in the editor.
2.  **Save New:** When "Save" is clicked:
    *   Do NOT update the existing row.
    *   INSERT a NEW row with `version = current_version + 1`.
    *   This preserves history (Version Control).
3.  **History List:** Fetch all rows ordered by `version DESC` to show the "Version History" sidebar.
4.  **Impact:** Remind the user that saving a new version **immediately** changes the persona for *all* new chats and generated content.

## 7. Module Integration Protocols (The "Contract")
To ensure system integrity, all AI agents working on specific modules **MUST** adhere to these protocols. Do not create isolated data silos.

### A. Knowledge Page AI
*   **Role:** Optimize file ingestion and RAG retrieval.
*   **Constraint:** You must NOT alter the `knowledge_chunk` table schema without a migration plan that preserves existing embeddings.
*   **Critical Dependency:** You must ensure the `from_the_noise...` file is always parsed correctly. If you optimize the chunking algorithm, verify that the "Daily Framework" parser still works.

### B. Vision Page AI
*   **Role:** Analyze images and generate captions/slugs.
*   **Integration:**
    *   Do NOT just save images to disk.
    *   You **MUST** store the generated "Caption" and "Keywords" into the `knowledge_chunk` (or a specialized visual table linked to it) so that the text-based AI can "remember" seeing the image later.
    *   *Example:* User asks "Show me that photo of the neon rain," the system searches the *text captions* to find the image.

### C. News Desk AI
*   **Role:** Generate daily content suites.
*   **Constraint:**
    *   **Read-Only:** You consume `daily_context` and `lore`.
    *   **Write-Back:** If the user edits a generated post and says "Save this style," you trigger the **Summarizer** to write a new `lore` entry ("User prefers punchy headlines").
    *   Do NOT create a separate "News Context" table. Use the shared `lore`.

### D. Front-End Chat AI
*   **Role:** The public face (Visitor interaction).
*   **Integration:**
    *   **Identity:** You must load the *Active* `instruction_set` (Zeon7 Persona).
    *   **Memory:** You read `lore` (Public entries only).
    *   **History:** You write to `chat_history` with a unique `session_id` for each visitor.
    *   **Safety:** You must respect the `is_public` flag on all Knowledge/Lore.
