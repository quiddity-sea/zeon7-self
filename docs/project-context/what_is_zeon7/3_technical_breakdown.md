# What is Zeon7? Part 3: Technical Breakdown

## 1. System Architecture Overview
The Zeon7 platform is a **Monolithic Web Application** engineered for longevity, stability, and ease of deployment. It rejects the complexity of modern frontend frameworks (React/Next.js) in favor of a **"Vanilla" LAMP Stack** approach. This ensures the site remains maintainable for decades, aligning with the "Archivist" philosophy of the persona.

### The Stack
*   **OS**: Windows/Linux (Cross-platform compatible).
*   **Web Server**: Apache 2.4 (with `mod_rewrite` for clean URL routing).
*   **Language**: PHP 8.3 (Strict typing, modern features).
*   **Database**: MariaDB 10+ (Relational data storage).
*   **Frontend**: HTML5, CSS3 (Custom Properties), ES6+ JavaScript (No build tools).
*   **AI Integration**: Google Gemini / OpenRouter APIs via `AiService`.

---

## 2. Directory Structure & Code Organization
The codebase is organized to separate concerns (MVC pattern) while keeping the structure flat and readable.

```text
e:/Dev/Projects/zeon7/self/
├── public/                 # Web Root (Apache DocumentRoot)
│   ├── admin/              # Protected Admin Interface (HTML/JS)
│   ├── api/                # REST API Endpoints (PHP)
│   ├── css/                # Global Styles (variables.css, components.css)
│   ├── js/                 # Shared Frontend Logic
│   └── index.php           # Public Router
├── src/                    # Core Application Logic (Not accessible via web)
│   ├── config/             # Database & API Config
│   ├── core/               # Base Classes (Database, Router, Auth)
│   ├── services/           # Business Logic (The "Brain")
│   └── utils/              # Helper Functions
├── docs/                   # Documentation (You are here)
└── instructions/           # Raw Markdown Source for Zeon7 Persona
```

---
    *   `.card`: Standard container with shadow and rounded corners.
    *   `.btn-primary`: The "Signal Red" action button.
*   **`style.css`**: Admin-specific layout overrides.

### JavaScript Architecture
*   **ES6 Modules**: We use native browser modules (`<script type="module">`).
*   **Classes**: Logic is encapsulated in classes like `NewsDesk`, `PostEditor`, `KnowledgeManager`.
*   **State Management**: Simple reactive state (e.g., `this.state = { loading: false }`) triggers UI updates via direct DOM manipulation.

---

## 7. Security & Performance
*   **Authentication**: Session-based auth (`AuthService`). Admin routes check `$_SESSION['user_id']` before rendering.
*   **CSRF Protection**: All POST requests require a `csrf_token` header, validated by `CsrfMiddleware`.
*   **Rate Limiting**: `RateLimitMiddleware` prevents API abuse (e.g., max 60 requests/minute).
*   **Performance**: Since there is no heavy framework bundle to download, the site loads in milliseconds. Database queries are optimized with indexes on `slug`, `status`, and `created_at`.
