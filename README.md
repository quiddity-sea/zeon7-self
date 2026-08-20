# ZEON7 — Cybernetic AI Persona & Neural Link Platform

[![System Status](https://img.shields.io/badge/System-ONLINE-00f2fe?style=for-the-badge&logo=cpu)](https://self.invigor.com/admin)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.6+-003545?style=for-the-badge&logo=mariadb&logoColor=white)](https://mariadb.org)
[![GSAP Animations](https://img.shields.io/badge/GSAP-3.12-88CE02?style=for-the-badge&logo=greensock&logoColor=white)](https://gsap.com)
[![Google Gemini](https://img.shields.io/badge/Google_Gemini-2.5_Flash-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://ai.google.dev)

**ZEON7** is an advanced, high-performance cybernetic AI persona platform, neural link interface, and intelligent assistant matrix built for **Merrill Leo** and **The Foreverbox Initiative**. 

The system combines a privacy-focused public AI chat widget with a comprehensive **Cybernetic Admin Cockpit**, multi-provider LLM orchestration (Google Gemini & local Ollama), RAG knowledge retrieval, persistent memory lore anchors, and operator management.

---

## ⚡ Key Capabilities & Features

### 1. Multi-Provider Neural Engine (`AIServiceFactory`)
- **Google Gemini Integration**: Native support for `gemini-2.5-flash` and `gemini-pro`, leveraging Google Search Grounding for real-time news and web intelligence.
- **Local Ollama Cluster**: Support for local LLM models (e.g. `Brain32:latest`, `llama3`) with configurable thinking/reasoning modes.
- **Dynamic Provider Switching**: Seamless runtime switching between cloud and local AI models without service interruption.

---

### 2. Admin Cockpit & Command Center (`/admin`)

- **Mission Control Dashboard (`index.php`)**: Live token consumption telemetry, API request metrics, daily persona themes, and a real-time system terminal.
- **Operator Matrix / User Management (`users.php`)**:
  - Full CRUD administrative user interface with role-based access tiers (*Prime Operator* vs *Standard*).
  - BCrypt password hashing (`cost: 12`) and session controls.
  - **IP Telemetry Auditing**: Tracks user login sessions with a rolling 10-IP history (`users.last_10_ips`).
  - **IP Removal & Purge Controls**: Allows admins to selectively remove individual IP records or purge IP history for any operator.
- **Chat Telemetry & Audit Suite (`chat_logs.php` & `chat_logs_view.php`)**:
  - Real-time and historical chat session auditing.
  - Granular message turn inspection, token usage breakdowns, model identification, and IP tracking.
- **Memory Bank / Lore Manager (`lore.php`)**:
  - Immutable lore registry for persistent persona anchors, biography rules, and worldview constants injected directly into system prompt cycles.
- **RAG Knowledge Base (`knowledge.php`)**:
  - Document ingestion, automatic text chunking, and full-text vector-like search for grounding AI responses in custom documents.
- **System Instruction Manager (`instructions.php`)**:
  - Live system prompt editor with full version history tracking, rollback support, and instant deployment.
- **AI News Desk & Content Engine (`news-desk.php` & `posts.php`)**:
  - Grounded web search news scanning, multi-angle story curation, and automated blog post generation and publishing.
- **Vision Intelligence (`vision.php`)**:
  - Multimodal image scanning and analysis interface powered by Gemini Vision models.

---

### 3. Security, Privacy & Authentication Architecture

- **Privacy-First Identity Verification (`api/chat.php`)**:
  - Prevents privacy leaks on shared network connections (e.g. public WiFi, coffee shops, shared offices).
  - When an unauthenticated visitor connects from a previously logged IP, the AI prompts with a neutral recognition check (*"I think I recognise you — what name did you use?"*) **without exposing or guessing prior operator names**.
  - Automatically links returning operators upon name confirmation and registers distinct profiles for new visitors.
- **Dual-Layer Authentication (`AuthService.php`)**:
  - DB-backed user session management with fallback to environment admin credentials.
- **Google OAuth 2.0 Single Sign-On (`google_redirect.php` & `google_callback.php`)**:
  - Seamless single sign-on with automatic account linking by verified email address.
- **CSRF & Rate Limiting Guardrails (`CsrfMiddleware.php` & `RateLimitMiddleware.php`)**:
  - Cryptographic token validation on state-changing API endpoints and sliding-window IP rate limiting (5 attempts/60s).

---

### 4. Cybernetic UI & Kinetic Animation Engine

- **Cybernetic Design System (`zeon7-theme.css`)**: High-contrast dark mode aesthetic with HUD scanline overlays, neon cyan/coral/gold accents, and clean typography.
- **GSAP Kinetic Engine (`animations.js`)**: 3D card unfolding, elastic 4-corner HUD crosshairs (`hud-corner-tr`, `hud-corner-bl`), rolling numerical counters, and pop-in modal tweens.
- **Responsive Kinetic Sidebar**: Collapsible navigation bar (75px collapsed to 220px expanded on hover) with active page indicator dots.

---

## 📁 Repository Directory Structure

```
/
├── admin/                         # Admin Cockpit Interface
│   ├── components/                # Reusable HUD Components (Header, Sidebar, Token Counter)
│   ├── css/                       # Admin-Specific Layout Stylesheets
│   ├── js/                        # Admin Async Managers (Users, Lore, Knowledge, News, Settings)
│   ├── chat_logs.php              # Session Telemetry Overview
│   ├── chat_logs_view.php         # Transcript Bubble Viewer
│   ├── index.php                  # Mission Control Dashboard
│   ├── instructions.php           # System Prompt Version Control
│   ├── knowledge.php              # RAG Knowledge Document Management
│   ├── login.php                  # Operator Login Screen
│   ├── lore.php                   # Memory Bank & Factual Anchors
│   ├── news-desk.php              # AI Grounded News Curation
│   ├── posts.php                  # Article & Post Manager
│   ├── settings.php               # System & API Key Configuration
│   ├── users.php                  # User Management & IP Telemetry Matrix
│   └── vision.php                 # Multimodal Image Analysis Desk
├── api/                           # Public & Admin REST Endpoints
│   ├── ai/                        # Generative AI, Scan & Chat Endpoints
│   ├── auth/                      # Authentication (Login, Logout, Check, Google OAuth)
│   ├── config/                    # System & API Key Configuration Endpoints
│   ├── instruction/               # Prompt Versioning & History APIs
│   ├── knowledge/                 # Knowledge Base Ingestion & Search APIs
│   ├── lore/                      # Memory Bank CRUD APIs
│   ├── posts/                     # Blog Publishing & Post Management APIs
│   ├── users/                     # Operator CRUD & IP Telemetry APIs (all, upsert, delete, remove_ip)
│   └── chat.php                   # Public AI Chat Endpoint with Privacy Flow
├── assets/                        # Static Branding Assets (Logos, Icons, Diagrams)
├── css/                           # Core Theme Stylesheets (zeon7-theme.css)
├── docs/                          # Architecture Master Plans, System Analysis & Tasks
├── js/                            # Public Client Scripts (Chat Widget, Theme Switcher, Public UI)
├── scripts/                       # Database Migration, Seeding & Indexing Scripts
├── src/                           # Backend MVC Core Framework
│   ├── config/                    # Database Connections & Environment Loader
│   ├── core/                      # Base Controllers, Services & Exception Classes
│   ├── middleware/                # AuthGuard, CsrfMiddleware & RateLimitMiddleware
│   └── services/                  # Business Logic Services (AIServiceFactory, UserService, etc.)
├── blog.php                       # Public Blog Directory
├── index.php                      # Public Landing Page & Cybernetic Persona Gateway
└── post.php                       # Public Article Viewer
```

---

## 🛠️ Installation & Setup Guide

### Prerequisites
- **Web Server**: Apache 2.4+ (with `mod_rewrite` enabled) or Nginx
- **PHP**: PHP 8.1 or higher (extensions required: `pdo_mysql`, `curl`, `json`, `mbstring`)
- **Database**: MariaDB 10.6+ or MySQL 8.0+

---

### Step 1: Clone Repository & Directory Permissions
```bash
cd /var/www
git clone https://github.com/quiddity-sea/zeon7-self.git self
cd self
sudo chown -R www-data:www-data /var/www/self
```

---

### Step 2: Configure Environment Variables
Create a `.env` file in the root directory:

```env
# Application Settings
APP_ENV=production
APP_KEY=your_generated_32_byte_hex_key
APP_URL=https://self.invigor.com

# Database Connection
DB_HOST=localhost
DB_NAME=zeon7_self_dev
DB_USER=zeon7
DB_PASS=your_secure_db_password

# Default Admin Credentials
ADMIN_USER=Mez
ADMIN_PASSWORD=your_emergency_admin_password

# AI Provider API Keys
GEMINI_API_KEY=your_google_gemini_api_key
OPENROUTER_API_KEY=your_openrouter_api_key

# Google OAuth Credentials (Optional)
GOOGLE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_google_client_secret
```

---

### Step 3: Database Initialization
Run the database migration and seeding scripts:

```bash
php scripts/create_chat_logs_table.php
php scripts/create_knowledge_tables.php
php scripts/create_system_instructions_table.php
php scripts/seed_db.php
php scripts/add_fulltext_index.php
```

---

### Step 4: Apache Virtual Host Configuration
Configure your Apache virtual host (`/etc/apache2/sites-available/zeon7.conf`):

```apache
<VirtualHost *:443>
    ServerName self.invigor.com
    DocumentRoot /var/www/self

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/zeon7.crt
    SSLCertificateKeyFile /etc/ssl/private/zeon7.key

    <Directory /var/www/self>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/zeon7_error.log
    CustomLog ${APACHE_LOG_DIR}/zeon7_access.log combined
</VirtualHost>
```

Reload Apache:
```bash
sudo a2enmod rewrite ssl
sudo systemctl restart apache2
```

---

## 📡 REST API Overview

| Method | Endpoint | Description | Auth Guard |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/chat.php` | Public AI chat interface with privacy flow | Public |
| `POST` | `/api/auth/login.php` | Operator login with rate limiting | Public |
| `POST` | `/api/auth/logout.php` | Session termination | Authenticated |
| `GET` | `/api/auth/check.php` | Check session authentication & CSRF token | Public |
| `GET` | `/api/users/all.php` | Get all registered operators & IP history | Admin Auth |
| `POST` | `/api/users/upsert.php` | Create or update operator profile | Admin Auth + CSRF |
| `DELETE`| `/api/users/delete.php` | Delete operator account | Admin Auth + CSRF |
| `POST` | `/api/users/remove_ip.php` | Remove specific IP or purge IP telemetry | Admin Auth + CSRF |
| `GET` | `/api/lore/all.php` | Fetch memory bank lore entries | Public / Admin |
| `POST` | `/api/lore/upsert.php` | Create/update lore fact | Admin Auth + CSRF |
| `DELETE`| `/api/lore/delete.php` | Delete lore entry | Admin Auth + CSRF |
| `POST` | `/api/knowledge/upload.php` | Ingest knowledge base document | Admin Auth + CSRF |

---

## 📄 License & Credits

- **Platform & Persona**: Developed for **Merrill Leo** & **The Foreverbox Initiative**.
- **Design Framework**: Zeon7 Cybernetic HUD Matrix.
- **Copyright**: © 2026 The Foreverbox Initiative. All rights reserved.
