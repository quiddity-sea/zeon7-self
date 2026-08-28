# VPS Migration Inventory & Deployment Map

**Generated:** 2026-08-28  
**Architecture Baseline:** `docs/hermes-integrate-v2.md`  
**Execution Plan:** `docs/hermes-integrate-v2-implementation-plan.md` (Phase 0)  
**Primary Repository:** `quiddity-sea/zeon7-self`  

---

## 1. Executive Migration Summary

This document establishes the precise inventory of all runtime, database, filesystem, and model components migrating from the local development environment (Main PC / WSL) to the primary VPS node (`vigorous-panini`), formalizing the **VPS-First Deployment Topology**.

```text
                        TAILSCALE NETWORK
                                |
              +-----------------+-----------------+
              |                                   |
              v                                   v
             VPS                               MAIN PC
      (100.126.174.30)                     (100.106.5.121)
    +-------------------+             +----------------------+
    | Council API :8080 |             | Local Models         |
    | Hermes PRIMARY    |             | RTX / GPU Compute    |
    | MariaDB PRIMARY   |             | Local Dev Tooling    |
    | ForeverBox Data   |             | Optional Local DB    |
    | Quiddity Lore Sea |             | Secondary Hermes CLI |
    +---------+---------+             +----------+-----------+
              |                                  |
              +----------------------------------+
```

---

## 2. Component Migration Matrix

| Component | Current Location (Main PC) | Current Authority | VPS Destination (`vigorous-panini`) | Migration Method | Verification Method |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Council REST API** | `/foreverbox_data/council-library/php-api` | Local PHP/Apache (:8080) | `/var/www/council-library/` or `/foreverbox_data/council-library/` (:8080) | Git clone / pull | `curl http://127.0.0.1:8080/v1/healthz` + `test_api_contracts.php` (9/9 pass) |
| **Council MariaDB** | Local MariaDB 11.8 (`localhost:3306`) | Local Primary | VPS MariaDB 11.8 (`localhost:3306`) | `mariadb-dump` -> restore via SSH/Tailscale | Cross-table row count and checksum verification |
| **Hermes Runtime** | Main PC (`/foreverbox_data/bin/fbox-launch`) | Local Execution | VPS Hermes (Primary Daemon / CLI) | Clean Hermes install via pip / systemd daemon | `fbox-launch zeon7` runs headless on VPS without PC |
| **ForeverBox Data** | `/foreverbox_data/` (Profiles, configs, bin) | Local / Git | `/foreverbox_data/` (VPS Primary) | Git clone + `rsync` over Tailscale | SHA-256 hash comparison across profile directories |
| **Quiddity Lore Sea** | `/foreverbox_data/Quiddity_Lore_Sea/` (8 Domains) | Local Filesystem | `/foreverbox_data/Quiddity_Lore_Sea/` | `rsync -avz` over Tailscale | File count & directory structure audit |
| **Embedding Daemon** | `localhost:8900` (`all-MiniLM-L6-v2`) | Local Python | `localhost:8900` (VPS systemd service) | Systemd unit + Python venv | `curl http://127.0.0.1:8900/health` (384 dimensions) |
| **Cognitive Router** | `/foreverbox_data/council-library/router/` | Local Python Hook | VPS Council Router | Git deployment + YAML config | Router budget & tier selection unit test |
| **Self Web App** | `/var/www/self/` | Local Apache / Dev DB | `/var/www/self/` (Public Server) | Git pull + deployment pipeline | Self Admin login + chat end-to-end test |

---

## 3. Database Inventory & Table Metrics (Pre-Migration Baseline)

Snapshot taken from local MariaDB on 2026-08-28:

### A. Control Plane & Commons
| Database | Table | Rows | Purpose |
| :--- | :--- | :---: | :--- |
| **`agent_registry`** | `agents` | 0 | Canonical agent roster (seeded during deployment) |
| **`agent_registry`** | `api_keys` | 1 | SHA-256 hashed API access tokens |
| **`agent_registry`** | `privileged_action_log` | 4 | Audit trail of elevated DDL / system commands |
| **`agent_registry`** | `soul_components` | 21 | Modular SOUL persona blocks & head definitions |
| **`agent_registry`** | `specialist_workers` | 0 | Worker daemon status register |
| **`agent_registry`** | `task_queue` | 1 | Inter-agent / Wolf async task queue |
| **`agent_registry`** | `token_budget_ledger` | 4 | Daily token consumption quotas |
| **`agent_registry`** | `user_agent_assignments`| 4 | Operator/user to agent mapping & permissions |
| **`quiddity_commons`**| `quiddity_files` | 19 | Indexed Markdown documents in Lore Sea |
| **`quiddity_commons`**| `quiddity_vector_references` | 971 | 384-dim embedding vectors for Lore Sea chunks |
| **`quiddity_commons`**| `quiddity_folder_centroids` | 6 | 384-dim domain classification centroids |
| **`quiddity_commons`**| `conversation_vectors` | 0 | Embedded cross-session conversation memories |
| **`quiddity_commons`**| `connected_sites` | 1 | Connected site network directory |
| **`quiddity_commons`**| `ingestion_dead_letter`| 167 | Document processing failure logs |

### B. Agent Sanctums (Cognitive Private State)
| Agent / Database | `memory_lore` | `conversation_history` | `soul` | `user_context` | `wolf_working_memory` |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Zeon7** (`agent_curator`) | 11 | 2 | 0 | 0 | 0 |
| **Leon** (`agent_producer`) | 2 | 0 | 0 | 0 | 0 |
| **Gemma** (`agent_coach`) | 0 | 0 | 0 | 0 | 0 |
| **Otec** (`agent_director`) | 2 | 0 | 0 | 0 | 0 |
| **Wolf** (`agent_wolf`) | 4 | 0 | 0 | 0 | 0 |

---

## 4. Network & Tailscale Configuration

```text
SELF_BASE_URL=https://self.foreverbox.local / public web domain
COUNCIL_BASE_URL=http://127.0.0.1:8080 (on VPS) / http://100.126.174.30:8080 (over Tailscale)
COUNCIL_PRIVATE_HOST=100.126.174.30
COUNCIL_PORT=8080
EMBEDDING_SERVICE_URL=http://127.0.0.1:8900
LOCAL_MODEL_ENDPOINT=http://100.106.5.121:11434
```

---

## 5. Migration Execution Sequence

```text
Step 1: VPS Prerequisites (Python 3.12, MariaDB 11.8, Apache/PHP 8.3, Tailscale)
   ↓
Step 2: Schema Migration & Seed Data (01_commons.sql through 07_soul_components.sql)
   ↓
Step 3: Database Data Dump & Restore (agent_registry, quiddity_commons, agent_*)
   ↓
Step 4: File Sync (foreverbox-data, Quiddity Lore Sea via rsync)
   ↓
Step 5: Council API & Embedding Daemon Services on VPS
   ↓
Step 6: Primary Hermes CLI Installation & Verification on VPS
   ↓
Step 7: Connect Main PC Local RTX Model Endpoint via Tailscale
   ↓
Step 8: Independent VPS Acceptance Test (VPS executes cloud/local without Main PC dependency)
```

