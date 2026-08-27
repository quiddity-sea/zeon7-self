# Hermes / Council / Self V2 — Detailed Implementation and Rebuild Plan

**Target builder:** DeepSeek 4 Fast  
**Alternative builder:** Gemini 3.7 Fast  
**Status:** Ready for implementation  
**Architecture baseline:** `docs/hermes-integrate-v2.md`  
**Historical baseline:** `docs/hermes-integrate-v1.md` must remain unchanged  
**Primary repository:** `quiddity-sea/zeon7-self`  
**Related repositories:** `quiddity-sea/council-library`, `quiddity-sea/foreverbox-data`

---

# 0. Builder Instructions

This document is an execution plan, not a suggestion list. Follow it in order.

The objective is to implement the architecture described by `hermes-integrate-v2.md` while progressively removing Self's duplicate agent state and making the VPS-hosted Council/Hermes environment the operational centre of ForeverBox.

### Non-negotiable rules

1. **Do not modify or delete `docs/hermes-integrate-v1.md`.** It is the historical architecture record.
2. **Do not replace `docs/hermes-integrate-v2.md`.** It is the architectural baseline.
3. **Do not introduce LoRA, user-specific fine-tuning, cognitive profiling, adaptive training or Master's research features.** Those are future work.
4. **Do not rewrite the existing Self UI from scratch.** The existing component, template, design, admin and public systems are the foundation for everything that follows.
5. **Do not create a second agent database in Self.** Self's database is for web/UI/site state only.
6. **Do not make Self depend on Council's internal database schema as the final architecture.** Use Council API contracts.
7. **Do not invent a second model registry, head registry, agent registry, memory store, conversation store or vector store.** Extend and consume the existing Council facilities.
8. **Do not assume an API exists because V2 names it conceptually.** Inspect current Council routes/controllers and extend them using existing conventions.
9. **Do not remove existing functionality simply because it is being migrated.** Redirect, verify, then remove the duplicate.
10. **Every migration phase must end with an acceptance test across at least two interfaces.**
11. **Do not introduce Cloudflare or any alternative tunnel/proxy architecture.** The deployment topology is Tailscale-based unless the user explicitly changes it later.
12. **Do not treat the local PC as the primary server.** The VPS is the primary Council + Hermes runtime and primary canonical database host.
13. **Do not make local and VPS MariaDB co-equal writable databases.** The intended model is VPS primary, with any local database being development/replica only.
14. **Do not require the local PC to be online for the canonical agent system to exist.** Local models are compute resources, not the location of agent identity/state.
15. **Before application integration is declared complete, the VPS must be able to operate as a self-contained primary ForeverBox runtime without depending on the main PC's filesystem, local MariaDB, local Hermes process or local model service.**

The final system must satisfy this invariant:

> **One canonical agent state, many interfaces, one primary Council/Hermes environment.**

Self Public, Self Admin, From the Noise and Hermes must all resolve the same authorised agent state through Council.

---

# 1. Current System and Deployment Direction

## 1.1 Self

`zeon7-self` is a PHP/MariaDB web application containing:

- public interface
- admin cockpit
- chat endpoint
- operator/user management
- system instruction management
- lore management
- knowledge/RAG management
- From the Noise / News Desk
- posts
- settings
- multimodal vision interface
- AI provider abstraction
- reusable UI components and templates
- authentication, CSRF and rate limiting

The repository still contains local agent-related state and AI provider logic that must be progressively replaced by Council-backed operations. fileciteturn49file0L2-L2

## 1.2 Council

Council is the canonical cognitive/agent-state service.

Existing capabilities include:

- Commons
- Sanctums
- Registry
- vector/search services
- ingestion
- conversation management
- Soul handling
- assignments
- Cognitive Router
- AgentContext
- authentication/authorization middleware
- privileged actions
- Wolves

The current Council middleware includes `AgentContext`, `Auth` and `PrivilegedActionGate`. fileciteturn41file0L2-L10

## 1.3 ForeverBox Data

`foreverbox-data` contains the persistent file-based ecosystem resources including agent profiles, Soul/configuration, Shared Skills, Quiddity Lore Sea, wrappers and integration resources.

## 1.4 Primary deployment topology

The operational architecture is VPS-first:

```text
                        TAILSCALE NETWORK
                               |
             +-----------------+-----------------
             |                                   |
             v                                   v
           VPS                               MAIN PC
   +-------------------+             +----------------------+
   | Council           |             | Local models        |
   | Hermes PRIMARY    |             | GPU / RTX compute   |
   | MariaDB PRIMARY   |             | Local dev tooling   |
   | ForeverBox Data   |             | Optional local DB  |
   | Quiddity Lore Sea |             | Optional CLI       |
   +---------+---------+             +----------+-----------+
             |                                  |
             +----------------------------------+

      Self / remote clients reach Council over Tailscale
```

The VPS is the primary operational location for:

- Council
- primary Hermes instance
- primary MariaDB
- operational `foreverbox-data`
- operational Quiddity Lore Sea

The main PC is primarily:

- local model compute
- development workstation
- optional secondary/local Hermes access
- optional local database replica/development database

### Network rule

Tailscale is the private connectivity layer between these services where remote access is required.

Do not introduce Cloudflare Tunnel, Cloudflare Access, or another public reverse-proxy dependency as part of this migration.

---

# 2. Canonical Data and Runtime Architecture

```text
                         GITHUB
                 source-controlled code
             and controlled data definitions
                           |
                    deployment/update
                           |
                           v
                         VPS
        +------------------------------------------+
        |                                          |
        | foreverbox-data                           |
        | Quiddity Lore Sea                        |
        | Council                                  |
        | Hermes PRIMARY                            |
        | MariaDB PRIMARY                           |
        |                                          |
        +------------------+-----------------------+
                           |
                        Tailscale
                           |
              +------------+-------------+
              |                          |
              v                          v
         Main PC                     Self server
       Local models                 Public/Admin UI
       GPU compute                       |
              |                          |
              +------- Tailscale --------+
                           |
                         Council
```

There is an important distinction between **source control** and **runtime authority**:

- GitHub is the source-controlled repository for code and controlled file definitions.
- VPS is the primary runtime copy.
- Council/MariaDB on the VPS is the canonical operational state for agent cognition and state.
- Runtime-derived indexes, vectors and conversation state belong to the Council environment.
- The local PC must not become a second competing source of truth.

---

# 3. Primary Database Decision

## 3.1 VPS MariaDB is PRIMARY

The Council VPS MariaDB becomes the authoritative database for the production agent system.

It should hold the canonical state for:

- agent registry data
- heads/Soul components where applicable
- agent assignments
- memory
- conversations
- vector/reference metadata
- model/routing configuration
- other Council-owned agent state

## 3.2 Local MariaDB is not co-equal

Do not implement bidirectional active-active replication.

Avoid:

```text
Local MariaDB <----> VPS MariaDB
 both accepting writes
```

Target:

```text
              VPS MariaDB
                PRIMARY
                   |
             one-way replica
                   |
                   v
              Local MariaDB
          optional/development
```

The local database can be retained as:

- development DB
- read replica
- migration/testing target
- emergency local copy

but its role must be explicit.

## 3.3 Backups are separate

A local replica is not a backup.

The implementation plan must preserve/introduce:

- VPS database backup
- point-in-time recovery where practical
- separate/off-VPS backup
- restoration testing

Do not treat Tailscale replication as disaster recovery.

---

# 4. ForeverBox Data and Quiddity Lore Sea Placement

## 4.1 Operational copy moves to VPS

The VPS primary runtime must have access to the operational copy of:

```text
/foreverbox_data/
/foreverbox_data/Quiddity_Lore_Sea/
```

and the relevant agent resources/configuration from `foreverbox-data`.

Primary Hermes must not depend on the main PC filesystem for fundamental agent operation.

## 4.2 GitHub remains source-controlled

The repositories remain the source-controlled development records.

Preferred direction:

```text
GitHub
   |
   v
VPS deployment
   |
   v
optional local development copy
```

Do not make ad-hoc edits on the VPS the normal source-control workflow.

## 4.3 Quiddity Lore Sea remains file-backed

Do not automatically move Lore Sea documents into MariaDB merely because the runtime moved to the VPS.

Preferred model:

```text
VPS Quiddity Lore Sea files
          |
          v
Council ingestion/indexing
          |
          v
Commons metadata/chunks/vectors
```

The file corpus remains recoverable and can be re-indexed if required.

## 4.4 Local copy

Retain a local development copy where useful, but do not treat changes on the local copy as automatically authoritative.

The V2 migration is not a bidirectional filesystem synchronisation project.

---

# 5. Tailscale Network and Service Boundary

## Objective

Make the actual private network topology explicit so implementation does not accidentally expose Council publicly.

### Required service relationships

```text
Self server
    |
    | Tailscale/private route
    v
Council API on VPS
    |
    +--> VPS MariaDB
    +--> VPS ForeverBox Data
    +--> VPS Quiddity Lore Sea
    +--> VPS Hermes
```

And:

```text
Local model service on Main PC
    |
    | Tailscale/private route
    v
VPS Council / Hermes
```

### Rules

- Prefer Tailscale DNS/name resolution where already available.
- Prefer private Tailscale addresses over public database/service ports.
- Do not expose MariaDB directly to the public internet.
- Do not expose internal Council services merely to allow browser JavaScript access.
- Browser requests should go to Self; Self's server-side PHP should call Council.
- Keep Council authentication independent of browser cookies where appropriate.
- Use TLS as appropriate to the actual Tailscale/Council deployment, without inventing a new proxy layer.
- Do not hard-code Tailscale IPs in application code. Use deployment configuration/environment settings.

### First implementation task

Before coding the client, inspect the actual VPS/Tailscale topology and record:

```text
SELF_BASE_URL
COUNCIL_BASE_URL
COUNCIL_PRIVATE_HOST
COUNCIL_PORT
HERMES_ENDPOINT if separately addressable
LOCAL_MODEL_ENDPOINT(s)
```

Use the real environment values found in the existing deployment rather than inventing them.

---

# 6. Phase -1 — Critical Preconditions

## Objective

Make the Council/ForeverBox core **internally coherent, secure, and reproducibly deployable** before any remote exposure, VPS migration, or Self integration work begins.

These six blockers were identified by a cross-repository code audit. Each must be resolved and verified before Phase 0.

> **Hard gate: Do not proceed to VPS production migration or expose Council remotely until all six preconditions are resolved and verified.**

---

## 6.1 Precondition A — Real Council Authentication

**Problem:** `Auth.php` accepts any non-empty Bearer token. The database lookup against `agent_registry.api_keys` is commented out.

**Required resolution:**

1. Implement real token validation in `Auth.php` — SHA-256 hash lookup against `agent_registry.api_keys`.
2. Fix the undefined `$pdo` variable in `AgentContext.php` line 36.
3. Verify that `PrivilegedActionGate` still works correctly after auth changes.
4. Add route-level tests confirming that unauthenticated requests are rejected.

**Verification:** An HTTP request with an invalid or missing Bearer token must receive `401 Unauthorized` on every protected route.

---

## 6.2 Precondition B — Single Canonical SOUL Authority

**Problem:** There are two competing SOUL systems:

| System | Location | Mechanism |
|---|---|---|
| `assemble_soul.py` | `/foreverbox_data/bin/` | Queries `agent_registry.soul_components`, writes `SOUL.md` to disk |
| `SoulController.php` | Council REST API | Reads/writes `soul` table (YAML blobs) in Sanctum DB |

These two systems do not share data. A head created via `soul_components` is invisible to `SoulController`, and vice versa.

**Required architecture:**

```text
                 CANONICAL SOUL / HEAD STATE
                           │
                  ┌────────┴────────┐
                  │                 │
                  ▼                 ▼
            Hermes runtime      Council API
                  │                 │
                  └────────┬────────┘
                           ▼
                      Self Admin
```

**Required resolution:**

1. Determine the canonical data model. The intended architecture is that **Council owns the canonical representation**.
2. Establish a single assembler/resolver that produces effective agent context from that canonical representation.
3. Make Hermes consume that resolver.
4. Make the REST API expose the same state.
5. Make Self edit the same state through the REST/API boundary.

Whether the assembler implementation ultimately lives in Python, PHP, or a shared service is secondary. **The representation and resolution semantics must be singular.**

Do not choose between the two systems based on which is easier to keep. Choose based on which correctly implements the canonical architecture.

**Verification:** A head created through any interface must be immediately visible and usable through every other interface without a synchronisation step.

---

## 6.3 Precondition C — Canonical Schema in Council's Deployment Path

**Problem:** The `soul_components` table definition exists only in:

```
profiles/zeon7/skills/software-development/dynamic-config-assembly/references/soul-components-schema.sql
```

It is not in `council-library/schema/`. A fresh Council deployment from the schema directory will not create this table.

**Required resolution:**

1. Move or duplicate the `soul_components` schema into `council-library/schema/` (e.g., `07_soul_components.sql`).
2. Include seed data if required for a minimal working deployment.
3. The skill reference directory may continue to reference the schema, but it must not be the only place it exists.

**Required architectural rule:**

> **Every Council-owned database structure must have a canonical migration/schema definition in Council's deployment system.**

**Verification:** A fresh deployment must be capable of cloning, running migrations, and resolving an agent without hidden schemas.

---

## 6.4 Precondition D — Secrets Remediation

**Problem:** Multiple `.env` files, config files, and scripts contain inline credentials committed to Git. The committed history must be treated as potentially compromised.

**Required resolution:**

```text
identify committed secrets
        ↓
inventory where each secret is used
        ↓
rotate/revoke each live credential
        ↓
remove secrets from active tracked files
        ↓
introduce runtime secret configuration (env vars, secret store)
        ↓
verify no active credential remains exposed in tracked files
```

Because credentials have been committed to Git history, merely deleting the current files is insufficient. The relevant history should be treated as compromised and live credentials must be rotated.

**Important:** Do not put literal credentials into architecture or implementation documents. The coding model needs instructions for secret handling, not the secrets themselves.

**Verification:** No live credential is present in any tracked file. All production secrets are supplied at runtime via environment configuration.

---

## 6.5 Precondition E — Embedding Contract and Vector Dimension Alignment

**Problem:** Council's SQL schemas define `VECTOR(1024)` columns, but the actual embedding service (`embedding_service.py`) uses `all-MiniLM-L6-v2` which produces 384-dimensional vectors. Additionally, `conversation_embedder.py` targets columns that do not match the actual table schema.

This is not merely "change 1024 to 384." It is a data migration.

**Required resolution:**

Establish and document the embedding contract:

```text
embedding model identifier
        ↓
actual vector dimension
        ↓
normalisation method
        ↓
distance/similarity method
        ↓
Council schema alignment
        ↓
existing stored vectors: compatible or require reindex?
        ↓
migration/reindex procedure if required
```

Then align all `VECTOR()` column definitions, fix `conversation_embedder.py` column references, and reindex if necessary.

**Verification:** Vector search returns correct similarity results. No dimension mismatch errors occur.

---

## 6.6 Precondition F — API Route Contract Repair and Testing

**Problem:** Five Council API routes silently fail due to mismatched parameter names between `public/index.php` route declarations and controller `$args` lookups.

**Required resolution:**

1. Reconcile every router parameter name with its corresponding controller method signature.
2. Audit **all** routes for the same class of mismatch.
3. Add a contract test for every Council route that Self will consume:

```text
route
  ↓
router parameter names
  ↓
controller method signature
  ↓
service call
  ↓
response schema
```

**Verification:** Every Council route returns the expected response shape with correct data. No undefined-index PHP errors occur.

---

## 6.7 Phase -1 Exit Condition

> **Do not proceed to Phase 0 until all of the following are true:**

- [ ] Council authentication rejects invalid tokens on all protected routes
- [ ] A single canonical SOUL/head representation exists and is consumed by both Hermes and the REST API
- [ ] All Council-owned schemas exist in Council's deployment/schema directory
- [ ] All live credentials have been rotated and removed from tracked files
- [ ] Runtime secret configuration is in place
- [ ] The embedding contract is documented and schema dimensions are aligned
- [ ] All Council API routes pass contract tests with correct parameter handling

---

# 7. Phase 0 — Initial Local-to-VPS Migration

## Objective

Before changing Self's application architecture, establish the VPS as a working, self-contained ForeverBox core and move the currently local operational components onto it.

This is a **deployment/data migration phase**, not an application rewrite.

The sequence must be completed and verified before dependent application integration work begins.

## 6.1 Inventory the current local runtime

On the main PC, identify the actual current locations and runtime mechanisms for:

- Council
- Council MariaDB
- Hermes
- `foreverbox-data`
- Quiddity Lore Sea
- Council configuration files
- Hermes configuration
- local model serving endpoints
- any agent registry/state files
- any local vector/index databases
- local backups relevant to migration

Do not assume paths. Inspect the real environment.

Produce:

`docs/vps-migration-inventory.md`

with a table:

| Component | Current location | Current owner | Destination | Migration method | Verification |
|---|---|---|---|---|---|
| Council | local PC | Council | VPS | deployment/install | API health |
| MariaDB | local PC | Council | VPS | dump/restore | row/checksum tests |
| Hermes | local PC | Hermes | VPS | install/configure | agent execution |
| foreverbox-data | local PC/Git | Data | VPS | Git/deployment copy | file/hash verification |
| Lore Sea | local PC | Data | VPS | file copy/Git/deployment | file/hash/count |
| vectors/indexes | local | Council | VPS | rebuild or controlled migration | retrieval tests |
| config | local | relevant service | VPS | secure configuration | startup/health |

## 6.2 Prepare the VPS

Install/configure the required runtime components on the VPS without disturbing the working local environment:

```text
OS/runtime prerequisites
MariaDB
Council
Hermes
foreverbox-data
Quiddity Lore Sea
Tailscale
required service accounts
required file permissions
backup tooling
```

Use existing project requirements and deployment documentation where available.

Do not invent a new software stack simply because it seems fashionable.

## 6.3 Establish Tailscale connectivity first

Before moving application traffic:

```text
Main PC <---- Tailscale ----> VPS
```

Verify from both directions as required:

- name resolution
- TCP connectivity
- Council API reachability
- model endpoint reachability
- SSH/admin access as applicable

Do not expose MariaDB or internal Council services publicly merely to simplify migration.

## 6.4 Migrate MariaDB

Create a verified backup/dump of the current local Council database.

Restore into VPS MariaDB.

Verify:

- schema
- table count
- row counts
- critical IDs
- foreign keys
- indexes
- vector/reference fields
- conversation history
- agent registry
- head/Soul state
- assignments
- model/routing configuration

Do not switch writes until the restored database passes reconciliation.

Record migration timestamp and source/destination identifiers.

## 6.5 Migrate ForeverBox Data

Deploy the operational `foreverbox-data` copy to the VPS.

Verify at minimum:

- agent directories
- agent profiles
- SOUL files
- `config.yaml`
- Shared Skills
- agent-linked skills
- wrappers
- required Council resources

Use Git history/checksums where appropriate to verify that the VPS copy corresponds to the intended source-controlled revision.

## 6.6 Migrate Quiddity Lore Sea

Copy the operational Lore Sea to the VPS.

Verify:

- directory structure
- file count
- expected file types
- file hashes for controlled/critical content where practical
- permissions
- readability by Council/Hermes service account

Do not delete the local copy at this stage.

## 6.7 Install/configure VPS Hermes as PRIMARY

Install the primary Hermes instance on the VPS.

Configure it to use:

- VPS Council
- VPS ForeverBox Data
- VPS Lore Sea
- VPS canonical MariaDB through Council where appropriate
- configured model endpoints

The VPS Hermes instance must be capable of running a canonical agent without requiring the local PC Hermes process.

## 6.8 Configure local model access

Expose the main-PC local model service through Tailscale only as required.

The intended relationship is:

```text
VPS Hermes/Council
        |
     Tailscale
        |
Main PC model server
        |
      RTX GPU
```

Confirm that the primary VPS runtime can use the local model when it is available.

Then stop/disable the local model service temporarily and confirm that Council/Hermes can use a configured fallback model.

The fallback test must prove that **loss of local inference does not imply loss of agent state**.

## 6.9 Switch primary operation to VPS

Only after verification:

```text
VPS Council = primary
VPS Hermes = primary
VPS MariaDB = primary
VPS foreverbox-data = operational primary copy
VPS Lore Sea = operational primary copy
```

The local PC retains its copies for development/replication until the migration is formally complete.

## 6.10 Configure local Hermes as secondary/remote

The local Hermes instance should now be treated as a secondary client/runtime.

Where it needs canonical agent state, it should resolve against the VPS Council/Hermes architecture rather than becoming another independently authoritative environment.

The exact local Hermes arrangement should follow what Hermes supports natively. Do not fabricate a new synchronization protocol just to make two installations look symmetrical.

## 6.11 Define the local database role

If local MariaDB is retained:

- document whether it is development, replica or test
- do not accept production writes as a competing authority
- do not let Self accidentally point to it in production

If one-way MariaDB replication is later introduced, configure it deliberately after the VPS-primary cutover and verify failure behaviour.

It is not required to complete the core Self/Council integration.

## 6.12 Back up before decommissioning local authority

Before the local environment is downgraded from primary:

- take final local DB dump
- archive configuration
- preserve local data copy
- preserve migration logs
- verify VPS backup
- test VPS restore enough to establish recovery confidence

## 6.13 Initial migration acceptance test

The local PC must be completely unnecessary for the following:

```text
VPS boots
  ↓
Council starts
  ↓
MariaDB available
  ↓
ForeverBox Data available
  ↓
Lore Sea available
  ↓
Hermes starts
  ↓
agent loads
  ↓
agent memory/context loads
  ↓
cloud/VPS model executes
```

Then independently test:

```text
VPS Council/Hermes
       ↓ Tailscale
Main-PC local model
       ↓
response
```

The two paths must be independently demonstrable.

## Exit condition

Do not proceed to the Self application migration until:

- [ ] VPS Council is operational
- [ ] VPS MariaDB is the verified primary
- [ ] VPS Hermes is the primary Hermes instance
- [ ] VPS has operational `foreverbox-data`
- [ ] VPS has operational Quiddity Lore Sea
- [ ] Tailscale connectivity is verified
- [ ] VPS can run an agent without the PC
- [ ] VPS can optionally use the PC's local model via Tailscale
- [ ] cloud/fallback model path is verified
- [ ] local copies are preserved
- [ ] backups exist
- [ ] migration inventory is documented

---

# 7. Phase 1 — Repository and Self Database Inventory

## Objective

Establish an exact map of the current Self state before migrating application ownership.

Create:

`docs/hermes-v2-self-data-inventory.md`

Classify every Self table and major data structure:

```text
KEEP
MOVE
REPLACE
DELETE
ARCHIVE
```

At minimum inspect:

- `system_instructions`
- `chat_logs`
- `lore`
- `knowledge_doc`
- `knowledge_chunk`
- provider/model tables
- agent/persona tables
- routing tables
- user/assignment tables
- site configuration
- posts/content
- UI/template configuration

Trace all application dependencies using repository-wide search.

Also inspect Council:

- routes
- controllers
- middleware
- Registry
- Soul
- Assignment
- Memory
- Conversation
- Commons
- VectorSearch
- Cognitive Router
- database switching
- ingestion workers

Do not create an endpoint merely because V2 mentions the concept.

## Additional inventory: deployment

Record the final post-migration topology:

```text
VPS:
  Council
  Hermes PRIMARY
  MariaDB PRIMARY
  foreverbox-data
  Quiddity Lore Sea

Main PC:
  local models
  development
  optional secondary Hermes
  optional local DB replica/development DB

Tailscale:
  private connectivity
```

## Exit condition

A migration matrix exists for both application data and deployment topology.

---

# 8. Phase 2 — Establish Canonical Council APIs

## Objective

Create the stable API contract Self will consume.

### Required API capability groups

```text
Agent catalogue
Agent detail
Head catalogue
Head detail
Head create/update/delete
SOUL/identity management as supported
Model catalogue
Routing configuration
Agent-specific model override
Agent context
Assignments
Memory/search
Knowledge/Commons search
Conversation read/write
```

### Important

Reuse existing Council APIs where possible.

The current Council architecture already exposes assignment, Soul, conversation and Commons functionality. Extend existing controllers before creating parallel controllers.

### Agent management API

Self Admin must eventually be able to:

- create an agent if Council supports agent creation
- edit agent metadata
- create/edit/delete heads
- edit supported Soul components
- inspect model choices
- change supported model assignments
- inspect capabilities
- inspect routing

### Model API

The model API should expose **available configuration**, not leak provider secrets.

The response may contain:

```text
provider
model identifier
tier/layer
local/cloud
enabled
capabilities
availability/health
```

but never raw API credentials.

### Exit condition

The canonical operations required by Self exist in Council or have a documented implementation gap assigned to Council.

---

# 9. Phase 3 — Build Self's Council Client

Create a single reusable integration layer in Self.

Suggested shape:

```text
src/services/
    CouncilApiClient.php
    CouncilAgentService.php
    CouncilAssignmentService.php
    CouncilConversationService.php
    CouncilMemoryService.php
    CouncilKnowledgeService.php
    CouncilModelService.php
```

Follow current project naming conventions.

The low-level client handles:

- base URL
- Tailscale/private hostname from configuration
- service authentication
- HTTP methods
- JSON
- timeouts
- retries only where safe
- status/error handling
- request IDs
- logging

Higher-level services translate Council API responses into Self application concepts.

### Important network behaviour

Self should make Council calls **server-side**.

Do not make browser JavaScript responsible for reaching the private Council API.

```text
Browser
   ↓
Self PHP
   ↓ Tailscale
Council
```

### Failure behaviour

When Council is unavailable:

- fail clearly
- do not use stale local agent state
- do not silently fall back to Self's old model/memory implementation
- preserve non-agent UI where possible
- log the failure without logging secrets

---

# 10. Phase 4 — Self Admin Becomes the Council Management Front End

The current Self Admin remains the UI foundation.

Build management screens using the existing component/template architecture.

## Required management areas

### Agents

- list agents
- search/filter
- view agent
- status
- available heads
- model/routing summary

### Heads

- list heads
- inspect head
- create head
- edit head
- clone head where appropriate
- archive/delete where Council permits

A newly created head is canonical immediately.

### Model management

- list available models
- view provider/model
- view layer/tier
- view local/cloud status
- configure supported assignments
- view agent overrides

### Agent preview

Show at minimum:

```text
Agent
Head
Resolved model
Resolved routing layer
Memory sources
Knowledge sources
Response
```

This is a diagnostic interface as well as a useful admin feature.

## Exit condition

An authorised administrator can change canonical agent configuration from Self and the change is immediately visible through Council/Hermes.

---

# 11. Phase 5 — Replace Self's Agent Instruction Authority

Trace `InstructionService`, `instructions.php`, and associated APIs.

Classify each instruction source:

```text
canonical agent identity
canonical head
runtime protocol
workflow-specific instruction
site/UI instruction
```

Only the first three should be considered for Council/Hermes authority.

From the Noise may retain workflow-specific production instructions if they belong to the workflow rather than the agent identity.

Do not simply move every `system_instructions` row into a Council field.

### Exit condition

Changing a canonical head/Soul in Council changes effective agent behaviour consistently across Self and Hermes without Self maintaining a competing instruction copy.

---

# 12. Phase 6 — Replace Self's Agent Memory and Knowledge Authority

Audit:

- `LoreService`
- `KnowledgeService`
- `admin/lore.php`
- `admin/knowledge.php`
- `/api/lore/*`
- `/api/knowledge/*`
- local search/chunk code

### Lore

Classify each record as:

```text
agent memory -> Council
shared knowledge -> Council Commons / ForeverBox Data
site editorial content -> Self
```

### Knowledge

Where the material is already part of Quiddity Lore Sea/Commons, use the Council pipeline.

Council's current `QuiddityController` exposes Commons file listing, sync and search and delegates semantic search to `VectorSearch`. fileciteturn42file0L2-L2

### Important technical distinction

Council currently contains both memory searching and Commons vector searching. Do not treat them as identical without verification.

The existing `MemoryController::search()` and Commons `VectorSearch` paths should be tested separately before Self's old search implementation is retired.

### Exit condition

Self knowledge/memory screens operate as UI over canonical Council facilities.

---

# 13. Phase 7 — Configure Primary VPS Hermes and Local Model Compute

This phase formalises the runtime already established in Phase 0.

## VPS Hermes

Primary Hermes runs on the VPS and is the default Hermes runtime for the ForeverBox ecosystem.

## Main PC

The main PC provides local inference when available.

It is not the canonical location of:

- agent identity
- agent memory
- agent conversations
- heads
- canonical model configuration
- canonical knowledge

## Routing

```text
Council Router
      |
      +--> local PC model via Tailscale
      |
      +--> VPS model if configured
      |
      +--> cloud model if required
```

The router must own the decision.

## Exit condition

Primary VPS Hermes can execute a canonical agent using either a local-PC model or an available fallback without changing canonical agent state.

---

# 14. Phase 8 — Rebuild Self Public Chat Around Council/Hermes

The current `/api/chat.php` must become an adapter to the canonical runtime.

Target flow:

```text
Browser
   |
   v
Self Public Chat
   |
   | authenticate/identify user
   v
Council API over Tailscale
   |
   +--> assigned agent
   +--> allowed head
   +--> canonical context
   +--> memory
   +--> knowledge
   +--> model/routing
   |
   v
Primary VPS Hermes
   |
   v
resolved model endpoint
   |
   v
response
   |
   +--> canonical conversation
   +--> durable memory where appropriate
```

## Preserve Self-specific security

Keep:

- authentication/session logic
- CSRF as appropriate
- rate limiting
- privacy recognition flow
- public web handling

But do not let Self security tables become the source of agent cognitive state.

## Provider migration

`AIServiceFactory` may remain during a transitional stage.

Sequence:

```text
old Self provider path
        |
        v
Council resolution in parallel
        |
        v
compare/verify
        |
        v
Council/Hermes execution
        |
        v
remove obsolete direct-provider authority
```

Do not delete old providers until the new path has passed acceptance tests.

---

# 15. Phase 9 — Canonical Conversation and Semantic Reference System

## Objective

All interfaces must write/read the same canonical conversation system.

Current Council contains `ConversationController` and `conversation_history`. fileciteturn29file0L2-L10

### Required metadata

Where not already present, preserve:

```text
conversation/session ID
agent ID
user/operator ID
source interface
timestamp
role
model used
head used
request/correlation ID where practical
tool/action metadata where appropriate
```

Source interface values should distinguish:

```text
self_public
self_admin
from_the_noise
hermes_cli
other
```

This is metadata, not separate storage silos.

### Vector principle

```text
Canonical chat log
      |
      v
embedding/index
      |
      v
vector reference -> original log/message
```

The vector must not become the authoritative transcript.

### Sequence safety

The current conversation append implementation uses `MAX(message_seq)+1`. Harden this for concurrent writes before declaring the shared runtime complete.

Possible approaches:

- transaction with row locking
- safe sequence table
- database-generated sequence strategy

Use the least invasive pattern consistent with existing Council design.

### Exit condition

A message written by Self is retrievable by Hermes and vice versa, with no duplicate conversation authority.

---

# 16. Phase 10 — From the Noise Reconciliation

From the Noise already lives in Self and should remain a Self workflow/presentation concern.

Audit every AI operation in its production pipeline.

For each operation record:

```text
agent
head
model
routing
knowledge
memory
conversation/context
```

All agent-function values must resolve from Council.

Keep in Self only:

- post records
- publication state
- slugs
- web presentation
- editorial workflow state
- content-production UI state

unless an item is demonstrably canonical agent state.

### Required test

Generate a controlled test item from From the Noise, then verify its agent/head/model/context against Self Admin and Hermes.

---

# 17. Phase 11 — Self Database Reduction

After all migration tests pass, shrink Self to web/application concerns.

### KEEP

```text
users
sessions
authentication
UI preferences
themes
components
templates
layouts
routes/navigation
site structure
web workflow state
publication/content state
web-only audit/telemetry
```

### MOVE / REPLACE

```text
agent identity
heads
agent Soul state
agent memory
agent knowledge
agent vectors
agent conversations
agent model registry
agent routing authority
agent capability state
```

### ARCHIVE

Historical Self chat/memory/content that cannot safely be merged should be exported before removal.

### Destructive migration rule

Never combine:

```text
new canonical path
+
DROP old tables
```

in one unverified deployment.

Use:

```text
snapshot
 -> migrate
 -> verify
 -> switch authority
 -> verify cross-interface
 -> archive
 -> later remove
```

---

# 18. Phase 12 — Self Component and Template System as the Permanent UI Foundation

The existing component/template system must remain the foundation.

Do not build separate applications such as:

```text
Zeon7 UI
Leon UI
Gemma UI
Otec UI
Wolf UI
```

Build:

```text
I-Am-Self
  |
  +-- shared components
  +-- shared design system
  +-- public
  +-- admin
  +-- site structure
  +-- agent templates
        +-- zeon7
        +-- leon
        +-- gemma
        +-- otec
        +-- wolf
        +-- future agents
```

A template controls presentation/composition.

It does not own the agent's identity, memory, model, head or knowledge.

### Example

```text
Council resolves:
  agent = zeon7
  head = writer
  model = X
  capabilities = [...]

Self resolves:
  template = zeon7-cockpit

Template renders the canonical agent.
```

Changing templates must not change the underlying agent state.

---

# 19. Phase 13 — User / Agent / Template Assignment

The user access relationship is:

```text
User
  |
  +--> authorised agent
         |
         +--> permissions
         +--> memory scope
         +--> allowed capabilities
         +--> Self presentation template
```

Council enforces actual authority.

Self chooses/render the presentation.

A hidden UI element is never a security boundary.

The current Council AssignmentController already provides the basis for this relationship. fileciteturn27file0L2-L10

---

# 20. Phase 14 — Security Hardening for the Distributed Runtime

Verify:

- Self -> Council authentication
- Council -> internal services
- Tailscale-only private services where intended
- no public MariaDB exposure
- no Council secrets in browser JavaScript
- no secrets in logs
- CSRF on Self state-changing requests
- Council authorization on canonical agent mutations
- privileged operations through Council's gate

Council already has `Auth`, `AgentContext` and `PrivilegedActionGate` middleware. fileciteturn41file0L2-L10

Test unauthorized access both from Self and direct API requests.

---

# 21. Phase 15 — VPS Data Deployment and Source-Control Rules

## Goal

Keep the VPS operationally self-contained without turning it into an uncontrolled editing environment.

### Controlled source flow

```text
GitHub controlled files
        |
        v
VPS deployment
        |
        +--> foreverbox-data
        +--> Quiddity Lore Sea
        +--> Council configuration
```

### Runtime state flow

```text
Self / From Noise / Hermes
            |
            v
         Council
            |
            v
       VPS MariaDB
```

### Local model flow

```text
Council/Hermes VPS
        |
     Tailscale
        |
        v
Main PC local model
```

Do not make local and VPS filesystem edits automatically authoritative in both directions.

---

# 22. Phase 16 — Migration Tooling and Rollback

Create/revise scripts for:

- Self data export
- Council migration checks
- record counts
- hashes where practical
- archive creation
- table rename/archive
- restore

For data copied from Self into Council, record:

```text
source table
source row ID
canonical destination
migration timestamp
migration status
```

Maintain a rollback checkpoint before each destructive step.

---

# 23. Phase 17 — Automated Cross-Interface Test Suite

The most important tests are architectural, not just page-level.

## Agent/head

1. Create head in Self.
2. Read through Council.
3. Read through Hermes.
4. Verify exact canonical definition.

## Model

1. Change supported model assignment in Self.
2. Resolve through Council.
3. Confirm Hermes sees same assignment.

## Memory

1. Write test memory through Hermes.
2. Retrieve via Council.
3. Retrieve via Self.

Then reverse direction.

## Conversation

1. Start conversation in Self.
2. Continue/retrieve in Hermes.
3. Verify same conversation identity.

## From the Noise

1. Trigger controlled generation.
2. Verify canonical agent/head/model.
3. Verify intended memory/conversation writes.

## Local model

1. Configure local model endpoint on main PC.
2. Verify Council/Hermes can use it over Tailscale.
3. Remove/unavailable the endpoint temporarily.
4. Verify configured fallback routing selects another model.
5. Confirm canonical agent state remains unchanged.

## VPS independence

1. Shut down or disconnect main-PC model service.
2. Run a cloud/VPS-backed agent interaction through primary Hermes.
3. Verify agent identity/memory remain available.

This explicitly tests the distinction between **compute availability** and **agent-state availability**.

## Authorization

- unauthorized user cannot access agent
- unauthorized user cannot mutate head/model
- direct API calls are denied even if UI hides controls

---

# 24. Phase 18 — Manual End-to-End Acceptance Scenarios

### Scenario A — Build a new head from Self

```text
Self Admin
 -> Zeon7
 -> New Head
 -> designer
 -> edit
 -> save
```

Immediately test:

```text
Hermes CLI
 -> Zeon7
 -> designer
```

The same definition must be used.

### Scenario B — Change model from Self

Modify an allowed agent model configuration.

Then inspect Hermes resolution.

No synchronization step should be necessary.

### Scenario C — Learn through Hermes

Write a controlled test fact.

Retrieve it in Self through semantic/contextual search.

### Scenario D — Learn through Self

Write a durable test memory.

Retrieve it through Hermes.

### Scenario E — From the Noise

Run a controlled generation.

Verify the canonical state used to generate it.

### Scenario F — Local model

Use the main-PC local model from the VPS runtime through Tailscale.

Then disable the local endpoint and verify cloud fallback.

### Scenario G — VPS primary

Run Hermes and Council entirely on VPS without the PC's local model service.

The canonical agent must continue to operate.

### Scenario H — UI template separation

Render the same agent through two different Self templates.

Verify the underlying agent state is identical.

---

# 25. Phase 19 — Documentation and Status Tracking

Update documentation only after verified implementation.

## Self

- README
- deployment documentation
- Council integration documentation
- Self DB ownership documentation
- component/template documentation
- migration status

## Council

- API documentation
- agent management API
- conversation/vector API
- deployment/runtime notes
- Tailscale service notes

## ForeverBox Data

- VPS deployment notes
- source-controlled versus runtime data
- Lore Sea deployment/indexing

## Architecture trail

Preserve:

```text
hermes-integrate-v1.md
       ↓
hermes-integrate-v2.md
       ↓
hermes-integrate-v2-implementation-plan.md
       ↓
actual implementation commits
```

Do not rewrite historical reasoning out of the repository.

---

# 26. Recommended Commit Sequence

Keep commits small and phase-aligned.

```text
1. docs: inventory current local deployment and identify secrets
2. security: rotate live credentials and remove secrets from git
3. council: implement active API token authentication
4. council: repair routing parameter mismatches and add tests
5. council: establish canonical SOUL/head resolution path
6. council: consolidate all schemas into deployment path
7. council: align vector dimensions to embedding contract
8. docs: record VPS/Tailscale topology
9. infra: prepare VPS runtime
10. infra: migrate/verify Council MariaDB to VPS
11. infra: deploy foreverbox-data and Lore Sea to VPS
12. infra: install/configure primary VPS Hermes
13. infra: connect local model endpoint over Tailscale
14. infra: make VPS primary / local Hermes secondary
15. docs: record migration acceptance
16. council: expose agent catalogue
17. council: expose/extend head management
18. council: expose model/routing management
19. self: add Council API client
20. self: migrate admin agent reads
21. self: migrate head management
22. self: migrate model management
23. self: migrate instruction authority
24. self: migrate lore/memory
25. self: migrate knowledge/search
26. self: migrate public chat execution
27. council: harden canonical conversation sequencing
28. self: migrate conversation writes
29. self: reconcile From the Noise
30. self: archive/remove duplicate agent tables
31. self: generalise component/template integration
32. tests: add cross-interface suite
33. docs: record final architecture and deviations
```

Do not create one enormous "V2 complete" commit.

---

# 27. Known Risks to Verify During Implementation

## Risk 1 — File versus DB authority

Some agent identity/Soul information may currently exist in both files and database representations. Determine the actual runtime authority before moving anything.

## Risk 2 — Memory versus Commons vector search

Do not assume Council's memory FULLTEXT search and Commons semantic/vector search provide identical semantics. Test both.

## Risk 3 — Conversation concurrency

`MAX(message_seq)+1` must not remain an unsafe concurrent writer pattern if Self and Hermes can write the same session.

## Risk 4 — Local model routing

The local model is a provider/compute endpoint. It must not become the owner of agent identity or memory.

## Risk 5 — VPS filesystem

The VPS must contain the operational `foreverbox-data` and Quiddity Lore Sea needed by primary Hermes. Do not leave primary runtime dependent upon a Windows/WSL path on the main PC.

## Risk 6 — Configuration drift

Avoid hard-coded local/VPS/Tailscale addresses. Use deployment configuration.

## Risk 7 — Active-active databases

Do not introduce bidirectional MariaDB writes unless the architecture is explicitly redesigned later. This V2 plan assumes a single primary.

## Risk 8 — Hidden old fallback

After switching to Council, search for dormant paths that still read old Self agent tables. A fallback that nobody remembers is still a second source of truth.

---

# 28. Explicitly Out of Scope

This implementation does **not** include:

- LoRA
- personal fine-tuning
- adaptive model training
- user cognitive profiling
- neurodivergence experiments
- autonomous self-training
- consciousness research
- photonic architecture
- new research features unrelated to the unification

Those may come later.

This build creates the clean technical foundation on which those future systems can be researched.

---

# 29. Final Definition of Done

The migration is complete only when:

## Canonical state

- [ ] Council + ForeverBox Data are authoritative for agent function/state.
- [ ] VPS MariaDB is the canonical production database.
- [ ] Self contains no competing agent database.
- [ ] Heads are canonical and editable through Self.
- [ ] Models/routing are canonical and editable through Self.
- [ ] Agent identity is canonical.
- [ ] Memory is canonical.
- [ ] Knowledge is canonical.
- [ ] Conversation logs are canonical.

## Runtime

- [ ] VPS Hermes is the primary Hermes runtime.
- [ ] Main-PC Hermes is secondary/remote where used.
- [ ] Local models are compute endpoints reachable over Tailscale.
- [ ] Cloud fallback works when a local model endpoint is unavailable.
- [ ] The local PC is not required for canonical agent identity/state.
- [ ] VPS can run the canonical agent without the main PC.

## Networking

- [ ] Self -> Council uses the intended Tailscale/private path.
- [ ] Council/internal database services are not publicly exposed unnecessarily.
- [ ] No Cloudflare tunnel/proxy dependency has been introduced.
- [ ] Endpoint configuration is deployment-controlled.

## Data

- [ ] Operational `foreverbox-data` is available on VPS.
- [ ] Operational Quiddity Lore Sea is available on VPS.
- [ ] GitHub remains source-controlled.
- [ ] Backup/recovery is separate from replication.
- [ ] Local data copies are not competing authorities.

## Interfaces

- [ ] Self Public uses canonical Council/Hermes execution.
- [ ] Self Admin manages canonical agents.
- [ ] From the Noise uses canonical agent state.
- [ ] Hermes CLI uses the same canonical state.

## UI

- [ ] Existing component system remains intact.
- [ ] Existing template architecture remains foundational.
- [ ] Different agents can use different UI compositions.
- [ ] A new agent does not require cloning the application.

## Verification

- [ ] Cross-interface tests pass.
- [ ] Memory continuity passes.
- [ ] Conversation continuity passes.
- [ ] Model/head continuity passes.
- [ ] From the Noise continuity passes.
- [ ] VPS-primary independence test passes.
- [ ] Local-model/fallback test passes.
- [ ] Security tests pass.
- [ ] Restore/recovery validation passes.

---

# 30. Builder's Final Instruction

Do not reinterpret the architecture while implementing it.

The architecture has already been decided.

The implementation goal is to turn the existing three repositories into one coherent operational ForeverBox system:

```text
                           FOREVERBOX
                               |
             +-----------------+------------------+
             |                 |                  |
             v                 v                  v
          DATA              COUNCIL             SELF
             |                 |                  |
       persistent files   canonical agent     UI/auth/site
       and resources       state/runtime      templates
             |                 |                  |
             +-----------------+------------------+
                               |
                      VPS PRIMARY CORE
                               |
                  +------------+------------+
                  |            |            |
                  v            v            v
               Self       From Noise     Hermes
               Public         Prod          CLI
                  \             |             /
                   +------------+------------+
                               |
                        SAME AGENT STATE
                               |
                   +-----------+-----------+
                   |                       |
               VPS models             Local models
                                        via Tailscale
```

The final test is simple:

> **If an authorised change to an agent is made through Self, Hermes must immediately see the same agent. If the agent learns something through Hermes, Self must be able to retrieve it. If From the Noise uses the agent, it must use the same canonical state. No synchronisation job should be required to make the three interfaces agree.**

Build that first. Everything more ambitious comes later.
