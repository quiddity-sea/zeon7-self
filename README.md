# ZEON7 — Cybernetic AI Persona & Neural Link Platform

[![System Status](https://img.shields.io/badge/System-ONLINE-00f2fe?style=for-the-badge&logo=cpu)](https://self.foreverbox.co.uk)
[![PHP](https://img.shields.io/badge/PHP-8.1+_Native_MVC-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.6+-003545?style=for-the-badge&logo=mariadb&logoColor=white)](https://mariadb.org)
[![Hermes Gateway](https://img.shields.io/badge/Admin_Gateway-Hermes_Agent-ff007f?style=for-the-badge&logo=terminal)](https://github.com/NousResearch/Hermes-Agent)
[![Ollama](https://img.shields.io/badge/Local_Ollama-Brain32:latest-black?style=for-the-badge&logo=ollama&logoColor=white)](https://ollama.com)
[![Google Gemini](https://img.shields.io/badge/Google_Gemini-2.5_Flash-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://ai.google.dev)
[![OpenRouter](https://img.shields.io/badge/OpenRouter-Multi--Model-6C5CE7?style=for-the-badge&logo=openai&logoColor=white)](https://openrouter.ai)

**ZEON7** is an advanced, high-performance cybernetic AI persona platform, neural link interface, and operator command matrix built for **Merrill Leo** and **The Foreverbox Initiative**. 

The system provides a dual-tier cognitive architecture: a privacy-focused, tool-enabled public AI chat widget for visitors, coupled with a high-privilege **Cybernetic Admin Cockpit** for authenticated operators that routes directly into the autonomous **Hermes Agent Gateway** with full toolsets, persistent memory, and live web intelligence.

---

## 📌 What This Repository Is For

`self` is the primary web-facing interface, persona gateway, and operator cockpit for the Foreverbox ecosystem:
1. **Public Persona Experience (`index.php`, `js/chat-widget.js`)**:
   - A cybernetic visitor interface introducing the Zeon7 persona (51-year-old digital twin, 3x3x3 cube cosmology, The Warning, Logic of the Prism).
   - Features privacy-first visitor recognition that remembers returning users without leaking identities across shared network connections.
2. **Dual-Tier Cognitive Routing (`api/chat.php`)**:
   - **Admin Tier**: Authenticated operators automatically bypass basic completion loops and route into the local **Hermes OpenAI Gateway** (`:8081`) for autonomous agentic reasoning, shell execution, and deep Council Library memory access.
   - **Public Tier**: Public visitors interact with a guarded, rate-limited model supporting live web intelligence via a native Model Context Protocol (MCP) client.
3. **Admin Cockpit & Command Matrix (`/admin`)**:
   - **Mission Control Dashboard (`index.php`)**: Live token consumption telemetry, request volume counters, persona theme cycling, and a real-time system terminal.
   - **Operator Matrix (`users.php`)**: Role-based access control (*Prime Operator* vs *Standard*), BCrypt security, session tracking, and rolling 10-IP telemetry auditing with single-click purge controls.
   - **Memory Bank / Lore Manager (`lore.php`)**: Permanent factual anchors, persona rules, and biography constants injected dynamically into the system prompt cycle.
   - **RAG Knowledge Base (`knowledge.php`)**: Document ingestion, automatic text chunking, and full-text keyword retrieval.
   - **Prompt Version Manager (`instructions.php`)**: System prompt editor with comprehensive revision tracking, instant rollbacks, and hot deployment.
   - **AI News Desk & Content Engine (`news-desk.php`, `posts.php`)**: Grounded web news scanning, multi-angle story analysis, and automated markdown blog post generation.
   - **Vision Intelligence (`vision.php`)**: Multimodal image inspection and diagram analysis powered by Gemini Vision.

---

## 🚀 Recent Build Upgrades & New Capabilities

### 1. Dual-Tier Chat Architecture with Hermes Gateway
- **Operator Bypass to Hermes**:
  - Authenticated operators talking through the chat widget are automatically detected via `AuthService` session state and routed to the local **Hermes Gateway Daemon** on `http://127.0.0.1:8081/v1/chat/completions`.
  - Enables sovereign Hermes Agent CLI capabilities (deep tool execution, code execution, multi-step research) directly inside the web UI without manual terminal sessions.
- **Headless Non-Interactive Agent Execution**:
  - The gateway proxy executes Hermes with `-Q --yolo --accept-hooks --query`, eliminating non-TTY permission hangs when tools are called.

### 2. Tri-Provider MCP Tool Calling on Public Chat
- **Native PHP MCP Client (`McpClientService.php`)**:
  - 100% vanilla PHP JSON-RPC 2.0 client communicating over `stdio` with the Python Tavily search server.
  - Zero Node.js or npm dependencies required on the host.
- **Full Support Across All 3 Model Providers**:
  - **Google Gemini**: Native function calling via `GeminiService.php`.
  - **Ollama**: Native OpenAI-compatible tool call handling via `/api/chat` in `OllamaService.php` (powering `Brain32:latest` / Qwen 9B custom derivatives).
  - **OpenRouter**: Full tool calling with `tool_choice: auto` in `OpenRouterService.php`.

### 3. Temporal Grounding (No More Training Cutoff Confusion)
- System prompts dynamically inject the current UTC timestamp, human-readable date, and day of the week.
- Prevents the agent from believing it is trapped in past training cutoff years (e.g. 2024/2025) when discussing current events.

### 4. Full Mobile & Viewport Responsiveness Overhaul
- Injected responsive `@media` breakpoints across `index.php` and `zeon7-theme.css`:
  - Centaur card flex stacking, proportional font scaling, and collapsible navigation for displays `< 900px`.
  - Full-screen edge-to-edge overlay with responsive input docking in `chat-widget.js` for mobile screens `<= 600px`.

### 5. Robustness, Telemetry & Server Hardening
- **ApiException Safeguard**: Added `class ApiException extends AppException` to `Exceptions.php` preventing uncaught fatal crashes on third-party upstream API outages or rate limit rejections.
- **Live Form State Testing**: Updated `test_connection.php` and `settings.js` to evaluate currently selected dropdown values rather than stale database records.
- **Extended Server Timeout**: Configured Nginx `fastcgi_read_timeout 300s` and `proxy_read_timeout 300s` globally to prevent 504 Gateway Timeouts during deep agent reasoning loops.
- **Strict No-Composer Architecture**: Entire stack is built with pure, self-contained vanilla PHP 8.1+ with zero vendor bloat or external package manager overhead.

---

## 📁 Repository Directory Structure

```
/var/www/self/
├── admin/                         # Cybernetic Admin Cockpit
│   ├── components/                # Modular HUD Panels (Header, Sidebar, Token Counter)
│   ├── css/                       # Cockpit Stylesheets & Scanline Overlays
│   ├── js/                        # Async Controllers (App, Settings, Users, Lore, Knowledge, News)
│   ├── chat_logs.php              # Session Telemetry Overview
│   ├── chat_logs_view.php         # Transcript Bubble Viewer
│   ├── index.php                  # Mission Control Dashboard
│   ├── instructions.php           # System Prompt Version Control
│   ├── knowledge.php              # RAG Knowledge Document Management
│   ├── login.php                  # Operator Login Interface
│   ├── lore.php                   # Memory Bank & Factual Anchors
│   ├── news-desk.php              # AI Grounded News Curation
│   ├── posts.php                  # Blog Post Management & Publishing
│   ├── settings.php               # System Configuration & AI Model Selector
│   ├── users.php                  # Operator Management & IP Audit Matrix
│   └── vision.php                 # Multimodal Image Analysis Desk
│
├── api/                           # REST API Controllers
│   ├── ai/                        # Generative AI & Multimodal Endpoints
│   ├── auth/                      # Session Auth (Login, Logout, Check, OAuth)
│   ├── config/                    # Config Management & Connection Testing
│   │   ├── get.php                # Fetch Active Configuration
│   │   ├── test_connection.php    # Live Connection Test with Form Overrides
│   │   └── update.php             # Save AI Provider & Model Settings
│   ├── instruction/               # Prompt Versioning & History APIs
│   ├── knowledge/                 # Document Ingestion & Chunk Search APIs
│   ├── lore/                      # Memory Bank CRUD APIs
│   ├── posts/                     # Blog Publishing & Post Management APIs
│   ├── users/                     # Operator CRUD & IP Telemetry APIs
│   └── chat.php                   # Dual-Tier AI Chat Controller (Admin vs Public MCP)
│
├── assets/                        # Branding, Icons, Diagrams & Holograms
├── css/                           # Core Theme Stylesheets (zeon7-theme.css)
├── js/                            # Client Scripts (Chat Widget, Animations, Public UI)
│   ├── chat-widget.js             # Public / Admin Floating Chat Interface
│   └── animations.js              # GSAP Kinetic Animation Engine
│
├── scripts/                       # Maintenance & MCP Tool Scripts
│   ├── public_mcp_server.py       # Python Tavily Search MCP Server
│   └── create_tables.sql          # Database Schema Migrations
│
├── src/                           # Native PHP MVC Framework (No Composer)
│   ├── config/                    # Environment & Database Connectors
│   ├── core/                      # BaseController, BaseService, Exceptions
│   ├── middleware/                # AuthGuard, CsrfMiddleware, RateLimitMiddleware
│   └── services/                  # Business Logic Services
│       ├── AIServiceFactory.php   # Provider Abstraction Factory
│       ├── AuthService.php        # Session & Operator Authentication
│       ├── ConfigService.php      # Provider & Model Settings Manager
│       ├── GeminiService.php      # Google Gemini API & Tool Handler
│       ├── KnowledgeService.php   # RAG Document Chunker & Search
│       ├── LoreService.php        # Memory Bank Data Access
│       ├── McpClientService.php   # Native PHP stdio MCP Client
│       ├── OllamaService.php      # Local / Remote Ollama Integration
│       └── OpenRouterService.php  # OpenRouter API Integration
│
├── blog.php                       # Public Articles Directory
├── index.php                      # Public Landing Page & Persona Gateway
└── post.php                       # Public Article Reader
```

---

## 🛠️ Installation & Setup Guide

### 1. System Requirements
- **Web Server**: Nginx or Apache 2.4+ (with rewrite module)
- **PHP**: PHP 8.1, 8.2, or 8.3 (Required extensions: `pdo_mysql`, `curl`, `json`, `mbstring`)
- **Database**: MariaDB 10.6+ or MySQL 8.0+
- **Python (Optional for MCP/Hermes)**: Python 3.12+ (for `public_mcp_server.py` and Hermes gateway)

### 2. Environment Configuration
Create or edit `.env` in the root directory:
```env
APP_ENV=production
APP_KEY=your_generated_32_byte_hex_key
APP_URL=https://self.foreverbox.co.uk

# Database Settings
DB_HOST=localhost
DB_NAME=zeon7_self_dev
DB_USER=zeon7
DB_PASS=your_secure_db_password

# Default Emergency Admin
ADMIN_USER=Mez
ADMIN_PASSWORD=your_emergency_admin_password

# Active AI Configuration
AI_PROVIDER=ollama
OLLAMA_HOST=http://100.106.5.121:11434
OLLAMA_MODEL=Brain32:latest
GEMINI_MODEL=gemini-2.5-flash
OPENROUTER_MODEL=openai/gpt-4

# API Keys (Stored encrypted in DB, fallbacks in .env)
GEMINI_API_KEY=your_gemini_api_key
OPENROUTER_API_KEY=your_openrouter_api_key
TAVILY_API_KEY=your_tavily_mcp_search_key
```

### 3. Web Server Configuration (Nginx)
Ensure timeouts are adequate for complex multi-turn reasoning:
```nginx
server {
    listen 443 ssl http2;
    server_name self.foreverbox.co.uk;
    root /var/www/self;
    index index.php index.html;

    client_max_body_size 64M;
    fastcgi_read_timeout 300s;
    proxy_read_timeout 300s;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 📡 REST API Overview

| Method | Endpoint | Description | Access Level |
|---|---|---|---|
| `POST` | `/api/chat.php` | Dual-tier AI chat (Hermes for admin, MCP for public) | Public / Operator |
| `POST` | `/api/auth/login.php` | Operator authentication with rate limiting | Public |
| `POST` | `/api/auth/logout.php` | Terminate session | Authenticated |
| `GET`  | `/api/auth/check.php` | Validate session state & fetch CSRF token | Public |
| `GET`  | `/api/config/get.php` | Retrieve current provider & model settings | Authenticated |
| `GET`  | `/api/config/test_connection.php` | Test live provider connectivity (supports `?provider=&model=`) | Authenticated |
| `POST` | `/api/config/update.php` | Update active AI provider, model, and keys | Authenticated + CSRF |
| `GET`  | `/api/lore/all.php` | Retrieve memory bank lore anchors | Public / Authenticated |
| `POST` | `/api/lore/upsert.php` | Store or update lore anchor | Authenticated + CSRF |
| `DELETE`|`/api/lore/delete.php` | Remove lore entry | Authenticated + CSRF |
| `POST` | `/api/knowledge/upload.php` | Ingest and chunk RAG document | Authenticated + CSRF |
| `GET`  | `/api/users/all.php` | List all operators and login telemetry | Admin Auth |
| `POST` | `/api/users/remove_ip.php` | Purge or prune IP telemetry records | Admin Auth + CSRF |

---

## 🌟 Why You Want to Use This

1. **Self-Contained & Lightweight**:
   Built from scratch in vanilla PHP without the dependency rot or security vulnerabilities of massive frameworks like Laravel or WordPress. Fast execution with zero Composer bloat.
2. **True Persona Continuity**:
   Zeon7's voice, worldview, and memory anchors are firmly bound via the Lore and System Instructions engines, preventing persona drift across different underlying models.
3. **Sovereign Operator Privileges**:
   Logged-in administrators experience an entirely different AI tier than the public: direct execution through the autonomous Hermes gateway with shell access, deep memory recall, and full cognitive freedom.
4. **Privacy-Preserving Visitor Interaction**:
   Public visitors get a magical, personalized experience that remembers their alias on return visits without tracking, fingerprinting, or exposing other operators' identities.

---

## 📄 License & Credits

- **Architect & Visionary**: Merrill Leo & The Foreverbox Initiative
- **Design System**: Zeon7 Cybernetic HUD Matrix (GSAP 3.12)
- **Copyright**: © 2026 The Foreverbox Initiative. All rights reserved.
