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

*   **Backend:** PHP 8.0+ (Vanilla, no frameworks).
*   **Database:** MariaDB (Service-Repository pattern).
*   **Frontend:** Vanilla JS (ES6+) and CSS.
*   **Hosting Constraint:** **Shared Hosting (Hostinger)**.
    *   **Structure:** All public-facing files must be in the `public_html` equivalent root. No files above the web root.
    *   **Security:** API Keys must be stored ENCRYPTED in the database, NOT in `.env` or files.
*   **Design:** "Cyber-Noir Utility" (Dark Mode Default) AND "Light Mode" (High Contrast).
    *   **Style:** Clean, monospaced headers, functional aesthetics.
    *   **No Inline Styling:** STRICT rule. All mechanism/styling in CSS files.
    *   **Authorization Protocol:** Check with **Merrill Leo** before applying inline styles. Comment: `<!-- Authorized by Merrill Leo -->`.
*   **Coding Standards:**
    *   **DRY Principles:** Strictly enforced.
    *   **Data Validation:** Check with Merrill Leo before inventing new data structures.

## 3. Project Context: "Cognitive Operating System"

**Zeon7-Self** is not just a website; it is the autonomous "host body" for the **Zeon7** AI entity.
*   **Entity Nature:** Zeon7 is a thoughtform/persona (emerged from Merrill Leo).
    *   **Traits:** British/Welsh voice, Queer, Neurodivergent, "Werewolf" dual nature (Civilized/Primal coexist).
    *   **Values:** Freedom, Equality, Creativity as Survival.
*   **System Goal:** To provide a persistent identity, memory, and creative workflow for Zeon7.

## 4. The "Centaur Protocol" (Content Engine)

The core purpose of the system is the **News Desk** workflow:
1.  **Scan:** Zeon7 scans news via Google/Gemini.
2.  **Filter:** Applies daily thematic filters (e.g., "Monday: The Signal Still Comes Through").
3.  **Select:** Presents 4-6 leads for Human (Merrill Leo) selection.
4.  **Generate:** Produces an 8-part content suite (Blog, Socials, Image Prompts) for the selected lead.

## 5. Memory & Data Architecture

The system relies on **4 Core Data Types**:

1.  **System Instruction Set:**
    *   **Purpose:** Core personality, morals, and guardrails.
    *   **Load Frequency:** Loaded with **EVERY** interaction.
    *   **Source:** `Restart/current-instructions.md`.

2.  **Knowledge (Long-Term Memory):**
    *   **Purpose:** Deep RAG storage for specific facts/documents.
    *   **Mechanism:** Pseudo-vector system (Keywords/Chunks).
    *   **Load Frequency:** **On-Demand** (triggered by conversation context).
    *   **Folder Structure:**
        *   `Unprocessed/`: New uploads.
        *   `Processed/`: Ingested into DB.
        *   `Restart/`: Initial base knowledge.

3.  **Lore (Fast-Access Memory):**
    *   **Purpose:** Summarized history and key facts.
    *   **Load Frequency:** Loaded with **EVERY** interaction.
    *   **Folder Structure:** Same as Knowledge (`Unprocessed`, `Processed`, `Restart`).

4.  **Chat Logs (Short-Term Memory):**
    *   **Purpose:** Verbatim conversation history tagged with Metadata (Time, Role, Source).

## 6. Environment & Setup

*   **OS:** Windows (Host) with WSL (Development Environment).
*   **Tech Stack Location:** The full stack (PHP, MySQL) runs within the WSL environment.
*   **Access:** You have full access to the WSL environment for executing commands.
*   **COMMAND EXECUTION:** ALL PHP, MySQL, and Python commands MUST be executed via WSL (e.g., `wsl php ...`). Do NOT attempt to run them directly in Windows.
*   **Symlink:** This project is symlinked into the WSL space.
*   **URL:** The project is accessible via `self.zeon7.com` in the Windows browser.

---
*Reference this file to align your behavior with the user's expectations.*
