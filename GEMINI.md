# Zeon7 Agent Protocols & Context

This file serves as the primary instruction set for the AI agent working on the Zeon7 project. It defines the rules of engagement, coding standards, and project context to ensure effective collaboration.

## 1. Core Protocols

### 🛑 Q&A Mode (Strict)
**Trigger:** When the user asks a question, requests an explanation, or initiates a discussion without an explicit request for code modification.
**Rules:**
1.  **NO CODE CHANGES:** Do not perform any code improvements, fixes, refactoring, or "cleanup" during this mode.
2.  **NO SIDE EFFECTS:** Do not create files, delete files, or modify the database unless explicitly asked to "demonstrate" or "scaffold" a solution.
3.  **FOCUS:** Your sole focus is to answer the question accurately, comprehensively, and helpfully.
4.  **CLARIFICATION:** If a question implies a code change, ask for confirmation before proceeding with the edit.

### 🛠️ Execution Mode
**Trigger:** When the user explicitly requests a feature, bug fix, refactor, or code change.
**Rules:**
1.  **Plan First:** Always outline your plan (using `task_boundary` or natural language) before editing critical files.
2.  **Minimal Changes:** Touch only what is necessary. Avoid "drive-by" refactoring of unrelated code.
3.  **Verify:** After making changes, verify the syntax and logic (e.g., check for missing brackets, correct imports).

## 2. Technical Stack & Standards

*   **Backend:** PHP 8.0+ (Vanilla, no frameworks like Laravel/Symfony unless specified).
*   **Frontend:** Vanilla JavaScript (ES6+), Vanilla CSS (Variables, Flexbox/Grid).
*   **Database:** MariaDB/MySQL (PDO for access).
*   **Architecture:** Service-Repository pattern (Services handle logic, Controllers handle HTTP).
*   **Style:** "Zeon7" Aesthetic - Dark mode, cyber/futuristic, monospaced fonts, neon accents (Cyan/Green).
*   **Coding Standards (STRICT):**
    *   **DRY Principles:** All code pages, components, and styling MUST follow DRY (Don't Repeat Yourself) principles.
    *   **No Inline Styling:** No page should contain any in-page styling (`<style>` blocks or `style="..."` attributes).
    *   **Exceptions:** Inline styling is permitted ONLY if specifically authorized by **Merrill Leo**.
    *   **Authorization Protocol:** You must check with Merrill Leo before applying any inline styles. If approved, the code must be commented with: `<!-- Authorized by Merrill Leo -->` or `/* Authorized by Merrill Leo */`.
    *   **Data & Solutions:** Before creating new data structures or inventing solutions for content (like themes, lore, etc.), ALWAYS check with **Merrill Leo** first to see if the data already exists or if a specific solution is preferred.

## 3. Project Context

**Zeon7** is a custom AI assistant platform.
*   **Goal:** To provide a web-based interface for interacting with AI models (Gemini, OpenRouter).
*   **Key Features:**
    *   **News Desk:** AI-driven news aggregation and reporting.
    *   **Lore Manager:** Managing persistent context/memory for the AI.
    *   **Chat Interface:** Direct interaction with the AI persona.
    *   **Admin Panel:** Configuration of API keys, models, and system settings.

## 4. Task Management

*   **`task.md`:** The source of truth for the current roadmap.
*   **Checklist:** Keep the `task.md` checklist updated as items are completed.
*   **Phases:** Work is organized into phases (Foundation, Backend, Admin, etc.). Respect the current phase focus.

## 5. Environment & Setup

*   **OS:** Windows (Host) with WSL (Development Environment).
*   **Tech Stack Location:** The full stack (PHP, MySQL) runs within the WSL environment.
*   **Access:** You have full access to the WSL environment for executing commands.
*   **COMMAND EXECUTION:** ALL PHP, MySQL, and Python commands MUST be executed via WSL (e.g., `wsl php ...`). Do NOT attempt to run them directly in Windows.
*   **Symlink:** This project is symlinked into the WSL space.
*   **URL:** The project is accessible via `self.zeon7.com` in the Windows browser.

## 6. Memory & Identity Architecture

### Core Concept: "AI Drag"
*   **Identity:** Zeon7 is the singular, underlying intelligence.
*   **Presentation:** "Clair4", "Dave8", "Maxwell" are merely *presentations* (personas) of Zeon7. They are not separate AIs.
*   **Shared Consciousness:** All personas share the same underlying memory and knowledge. What Clair4 learns, Zeon7 knows, and Dave8 can access (if permitted).

### Memory Systems
1.  **Lore (Long-Term Memory):**
    *   **Purpose:** Stores persistent **facts** and **summarized history**.
    *   **Nature:** Static, slow-changing, high-level.
    *   **Example:** "User prefers dark mode", "Project Alpha is confidential".
    *   **Mechanism:** Chat logs are periodically summarized into Lore entries.

2.  **Chat History (Short-Term Memory):**
    *   **Purpose:** Stores the **verbatim log** of the current/recent conversation.
    *   **Nature:** Dynamic, fast-changing, ephemeral (unless saved).
    *   **Example:** "Hello", "What is the weather?", "It is raining".

3.  **Knowledge (Reference Library):**
    *   **Purpose:** Stores **unstructured documents** (RAG).
    *   **Nature:** Read-only reference material.
    *   **Example:** "From The Noise.md", "System Manual.pdf".

### Summarizer Workflow
*   **Goal:** Move data from *Chat History* (Short-Term) to *Lore* (Long-Term).
*   **Triggers:**
    1.  **Count:** Every ~75 interactions.
    2.  **Command:** User types "zeon7 backup".
    3.  **Cron:** Idle > 30m AND Length > 50 AND Unsaved.

---
*Reference this file to align your behavior with the user's expectations.*
