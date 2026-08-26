# Hermes / Council / Self V2 — Detailed Implementation and Rebuild Plan

**Target builder:** DeepSeek 4 Fast  
**Alternative builder:** Gemini 3.7 Fast  
**Status:** Ready for implementation  
**Architecture baseline:** `docs/hermes-integrate-v2.md`  
**Historical baseline:** `docs/hermes-integrate-v1.md` must remain unchanged  
**Primary repository:** `quiddity-sea/zeon7-self`  
**Related repositories:** `quiddity-sea/council-library`, `quiddity-sea/foreverbox-data`

---

## 0. Builder Instructions

This document is an execution plan, not a suggestion list. Follow it in order.

The objective is to implement the architecture described by `hermes-integrate-v2.md` without turning `zeon7-self` into a second Council implementation.

### Non-negotiable rules

1. **Do not modify or delete `docs/hermes-integrate-v1.md`.** It is the historical architecture record.
2. **Do not replace `docs/hermes-integrate-v2.md`.** This implementation plan implements it.
3. **Do not introduce LoRA, user-specific fine-tuning, cognitive profiling, adaptive training or Master's research features.** Those are future work.
4. **Do not rewrite the existing Self UI from scratch.** Preserve the current component, template, design, admin and public systems and rewire their backend dependencies.
5. **Do not create a second agent database in Self.** Self's database is for web/UI/site state only.
6. **Do not make Self directly depend on Council's internal database schema as the final architecture.** Use Council API contracts. Temporary direct DB access is allowed only as a migration bridge when a required API does not yet exist.
7. **Do not invent a second model registry, head registry, agent registry, memory store or conversation store.** Extend the existing Council facilities.
8. **Do not assume an API exists just because V2 names a conceptual endpoint.** Inspect the current Council controllers/routes first and extend them using existing conventions.
9. **Do not remove existing functionality simply because it is being migrated.** First redirect it to the canonical backend, verify it, then remove the duplicate.
10. **Every migration stage must finish with a cross-interface acceptance test.**

The final system must satisfy this invariant:

> One canonical agent state, many interfaces.

Self Public, Self Admin, From the Noise and Hermes must all use the same canonical agent identity, heads, models, routing, knowledge, memory and conversations for the same authorised agent.

---

# 1. Current System You Are Starting With

## 1.1 Self

The current Self repository is a PHP/MariaDB web application with:

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
- existing reusable UI components and design system
- authentication and CSRF/rate limiting

The repository currently contains service, middleware and UI layers that assume Self owns some agent state. The migration must progressively remove that assumption.

The existing README confirms that the current application has its own `AIServiceFactory`, RAG/knowledge, lore, instruction manager, chat, admin and public systems. Preserve these interfaces while replacing their agent backend dependencies. fileciteturn49file0L2-L2

## 1.2 Council

Council already provides most of the backend capabilities required by V2:

- Commons shared knowledge
- Sanctum memory
- Registry/control plane
- vector search
- ingestion
- conversation controller
- Soul controller
- Assignment controller
- Cognitive Router
- AgentContext middleware
- authentication middleware
- privileged-action gate
- Wolf infrastructure

The repository describes this as a sovereign memory architecture with MariaDB vector/FULLTEXT storage, REST APIs and three-tier cognitive routing. fileciteturn13file0L2-L2

The current controller layer includes `AssignmentController`, `ConversationController`, `MemoryController`, `SoulController`, `QuiddityController` and others. fileciteturn24file0L2-L2

## 1.3 ForeverBox Data

`foreverbox-data` provides:

- agent profiles
- `SOUL.md`
- `config.yaml`
- skills
- Shared_Skills
- Quiddity Lore Sea
- shell wrappers
- sync resources
- Council integration

This remains the persistent ecosystem/data layer. fileciteturn14file0L2-L2

---

# 2. Target Architecture

```text
                         FOREVERBOX
                             |
          +------------------+------------------+
          |                  |                  |
          v                  v                  v
     DATA LAYER         COUNCIL LAYER       SELF LAYER
          |                  |                  |
          |                  |             users/auth
          |                  |             UI/templates
          |                  |             public/admin
          |                  |             site structure
          |                  |                  |
          |            canonical agent state    |
          |            memory / vectors         |
          |            heads / models           |
          |            routing / context        |
          |                  |                  |
          +------------------+------------------+
                             |
                 +-----------+-----------+
                 |           |           |
                 v           v           v
              Self       From Noise    Hermes
              Public        Prod         CLI
```

The architecture is intentionally asymmetric:

- **Self owns presentation and web-specific state.**
- **Council owns canonical agent cognition/state.**
- **ForeverBox Data owns persistent ecosystem files/resources and agent definitions.**
- **Hermes executes agents and consumes the same canonical system.**

---

# 3. Phase 0 — Freeze the Existing System and Build an Inventory

## Objective

Understand exactly what Self currently stores and calls before changing it.

Do not begin by deleting tables or replacing `api/chat.php`.

## Tasks

### 3.1 Create a migration inventory

Add a temporary engineering document, for example:

`docs/hermes-v2-self-data-inventory.md`

Record every Self database table and classify it:

| Classification | Meaning |
|---|---|
| KEEP | Genuine Self/UI/site state |
| MOVE | Canonical agent state belongs in Council/ForeverBox |
| REPLACE | Existing local implementation must become a Council API call |
| DELETE | Obsolete duplicate after migration |
| ARCHIVE | Historical data retained for migration/reference |

Inspect at minimum:

- `system_instructions`
- `chat_logs`
- `lore`
- `knowledge_doc`
- `knowledge_chunk`
- model/provider configuration
- agent/persona configuration
- routing configuration
- user/assignment tables
- site/content tables
- posts/content tables

### 3.2 Trace code dependencies

Search the entire Self repository for:

```text
system_instructions
chat_logs
lore
knowledge_doc
knowledge_chunk
AIServiceFactory
InstructionService
LoreService
KnowledgeService
ConfigService
chat.php
news-desk.php
posts.php
agent
head
model
provider
```

Do not rely only on filenames. Trace actual call paths.

For each result record:

```text
file
function/class
reads/writes
purpose
current source
future source
```

### 3.3 Inventory Council before adding endpoints

Inspect current Council:

- controller classes
- routes
- middleware
- database switching behaviour
- current API authentication
- existing AgentContext handling
- SoulController
- AssignmentController
- ConversationController
- MemoryController
- QuiddityController
- Router configuration

Do not invent an endpoint that duplicates an existing Council operation.

For example, Council already has an Assignment controller for user -> agent assignments, with `agent_id`, `template_id`, `permissions`, `memory_scope` and `status`. fileciteturn27file0L2-L10

Likewise, SoulController already provides Soul and user-context operations. fileciteturn26file0L2-L2

## Exit condition

An inventory exists and every major Self agent-related table/function has an identified future owner.

**Do not proceed until ownership is unambiguous.**

---

# 4. Phase 1 — Establish the Council API Boundary

## Objective

Give Self a stable backend contract for canonical agent management.

The Council API should encapsulate the database layout so Self does not have to know whether a particular value lives in `agent_registry`, an agent Sanctum or a file.

## 4.1 Agent catalogue

Provide or reuse a Council API that can return:

- agent ID/slug
- display name
- enabled status
- available heads
- available model/routing configuration
- supported capabilities
- relevant permissions metadata

Use existing Council naming and route conventions.

Do not create a second agent table in Self.

## 4.2 Head management

Provide Council operations for:

- list heads for agent
- get head
- create head
- update head
- delete/archive head
- list head components
- edit component

The current Council Soul controller already works on the Sanctum `soul` data structure. fileciteturn26file0L2-L2

Before adding a separate head table, inspect the current `soul_components` / head representation used by the existing Hermes assembly system and preserve it.

### Required test

Creating:

```text
zeon7 / designer
```

through Self must result in the same head being visible to Hermes without an additional synchronisation operation.

## 4.3 Model and routing management

Council is authoritative for models and routing.

The existing Cognitive Router already defines model profiles and per-agent overrides. fileciteturn18file0L2-L2

Expose enough of that configuration for Self Admin to:

- display model choices
- display provider
- display layer assignment
- display agent-specific overrides
- change supported assignments

Do not make Self maintain `public_agent_model` as the authoritative model registry.

A Self-side preference may identify which agent a page/interface wants to use, but it must resolve the actual model configuration through Council.

## 4.4 User-to-agent assignment

Council already has `AssignmentController` and a registry table for `user_agent_assignments`. fileciteturn27file0L2-L10

Reuse this instead of inventing another agent assignment table in Self.

Self may retain the UI/template assignment information if the architecture decides that presentation choice is genuinely Self-owned, but the canonical agent permissions and memory scope remain Council-authoritative.

## Exit condition

A minimal authenticated API contract exists for:

```text
agents
heads
head components
models/routing
user-agent assignments
agent context
```

and Self can consume it without querying Council tables directly.

---

# 5. Phase 2 — Build the Self Council Client Layer

## Objective

Create one reusable client/service layer inside Self so individual PHP pages do not each invent their own Council HTTP calls.

## Recommended structure

Use the current Self service architecture and add a dedicated Council client, for example:

```text
src/services/
    CouncilApiClient.php
    CouncilAgentService.php
    CouncilMemoryService.php
    CouncilConversationService.php
    CouncilKnowledgeService.php
    CouncilAssignmentService.php
```

Exact names may follow the repository's existing conventions.

## CouncilApiClient responsibilities

- base URL configuration
- authentication token
- GET/POST/PUT/DELETE
- JSON encoding/decoding
- timeout handling
- error translation
- structured logging
- correlation/request ID
- no business logic

## Higher-level services

The higher-level service classes should expose application concepts, for example:

```php
getAgents()
getAgent($slug)
getHeads($agent)
getHead($agent, $head)
createHead(...)
updateHead(...)
getModelCatalogue(...)
getAssignments($userId)
searchMemory(...)
getConversation(...)
appendConversation(...)
searchCommons(...)
```

## Important rule

No page such as `admin/soul-editor.php` or `api/chat.php` should contain direct SQL against Council databases once the migration is complete.

## Failure handling

If Council is unavailable:

- do not silently fall back to stale agent state
- return a clear service-unavailable response
- log the failure
- preserve the user's existing conversation/UI state where practical

The system should fail closed on canonical agent state rather than accidentally using obsolete local state.

---

# 6. Phase 3 — Rebuild the Self Admin Agent Control Surface

## Objective

Turn the existing Self Admin into the front end for managing the canonical agent system.

This phase is where the planned functionality in V1 becomes real, but through Council rather than local duplication.

## 6.1 Agent selector

Create an agent management view using the existing Self component system.

Display:

- agent name
- status
- available heads
- current active/default configuration
- available models/routing
- capabilities

Do not hard-code `Zeon7`, `Leon`, `Gemma`, `Otec` in the UI.

The UI should be capable of rendering future agents returned by Council.

## 6.2 Head editor

Create a visual head editor.

Required actions:

- select agent
- list heads
- select head
- inspect component matrix
- create new head
- edit head component
- save changes
- version/updated metadata if Council provides it
- delete/archive head where permitted

The editor must use shared Self components.

### Example flow

```text
Admin
 -> Agent: Zeon7
 -> New Head
 -> Name: designer
 -> Edit components
 -> Save
 -> Council API
 -> canonical agent state updated
 -> Hermes sees designer
```

## 6.3 Base personality editor

Expose base identity/SOUL editing only where Council's current data model permits it.

Do not create a second `system_instructions` replacement in Self.

Where the existing Self instruction editor currently edits agent instructions, redirect the operation to the canonical Council/SOUL source after verifying exactly which parts map to:

- SOUL
- head
- protocol
- runtime instruction
- site-only content

Do not blindly move all old instruction records into one field.

## 6.4 Model control UI

Build the UI to display and change canonical model/routing assignments.

The view should distinguish:

```text
Provider
Model
Routing layer
Agent override
Default/fallback
Local/cloud
```

The UI should not duplicate the Router's decision logic. Council decides what model is ultimately used.

## 6.5 Agent preview/test panel

Add an admin test interaction surface that explicitly displays:

- agent
- selected head
- model selected/resolved by Council
- retrieved memory count
- knowledge sources used
- final routing layer
- response

This becomes extremely useful during migration and later debugging.

## Exit condition

An administrator can create a head in Self and use the preview interface to test it through the canonical Council/Hermes path.

---

# 7. Phase 4 — Replace Self's Independent Agent Instruction Path

## Objective

Remove `InstructionService` as an independent authority for agent identity/instructions.

The current repository has `src/services/InstructionService.php` and a local instructions management system. The old integration plan explicitly identifies this as a component that must be replaced by dynamic SOUL/Head assembly. fileciteturn10file0L2-L2

## Tasks

1. Trace every call to `InstructionService`.
2. Classify each use as:
   - agent identity
   - head/personality
   - prompt/runtime instruction
   - content-generation instruction
   - site/UI instruction
3. Move agent-function responsibilities to Council.
4. Keep genuinely site/workflow-specific instruction only where it is not part of agent identity.
5. Replace Self reads with Council service calls.
6. Add integration tests.

## Important distinction

From the Noise may have workflow-specific production instructions. Those are not necessarily the same thing as the agent's canonical Soul/head.

The correct architecture is:

```text
Canonical Agent Identity
        +
Canonical Head
        +
Workflow-specific production context
        |
        v
Council/Hermes execution
```

Do not flatten the entire From the Noise workflow into the SOUL.

## Exit condition

Changing a head in Council changes the agent consistently across Self and Hermes, without Self having a competing prompt definition.

---

# 8. Phase 5 — Replace Self's Local Memory/Lore/RAG Authority

## Objective

Move agent memory and agent knowledge operations onto Council.

Council already has memory and Commons search infrastructure. Its `MemoryController` manages Sanctum memory, while `QuiddityController` exposes shared Commons file/search operations. fileciteturn30file0L2-L2 fileciteturn42file0L2-L2

## 8.1 Lore

Audit `admin/lore.php`, `/api/lore/*` and `LoreService`.

Split current lore into:

- canonical agent memory -> Council
- shared knowledge -> Council Commons / ForeverBox Data
- site editorial content -> Self

The existing Self lore screen should remain as a UI, but its agent-memory operations should call Council.

## 8.2 Knowledge

Audit `admin/knowledge.php`, `/api/knowledge/*`, `KnowledgeService`, chunking and any local search SQL.

Replace local agent knowledge search with Council Commons search where appropriate.

Council already exposes a shared Commons search endpoint through `QuiddityController`, which delegates to `VectorSearch`. fileciteturn42file0L2-L2

## 8.3 Important current-state warning

The current Council `MemoryController::search()` shown in the repository is a FULLTEXT memory search implementation. It is not the same thing as the Commons vector search service. Do not claim a Council endpoint is semantic/vector retrieval until the actual endpoint is verified.

Where a required semantic memory endpoint is absent, add it to Council rather than recreating semantic search inside Self.

## Exit condition

The Self knowledge and memory UI can search and manipulate canonical Council data and does not require the old Self RAG tables to answer agent questions.

---

# 9. Phase 6 — Rebuild Self Public Chat Around the Canonical Runtime

## Objective

Turn `api/chat.php` into an adapter into Council/Hermes rather than a separate LLM application.

## Required request pipeline

```text
HTTP request
   |
   v
Self authentication / public identity
   |
   v
Resolve authorised agent
   |
   v
Resolve template/UI assignment
   |
   v
Council Agent Context
   |
   +-- agent identity
   +-- head
   +-- permissions
   +-- memory scope
   +-- model/routing
   +-- available capabilities
   |
   v
Council memory / knowledge retrieval
   |
   v
Canonical Hermes/agent execution
   |
   v
Response
   |
   +-- canonical conversation append
   +-- optional durable memory operation
```

## 9.1 Do not duplicate routing

The old Self `AIServiceFactory` can remain initially as a compatibility layer, but it must no longer decide independently whether the agent uses Gemini, Ollama or OpenRouter if Council already owns that decision.

The migration sequence should be:

1. keep old provider code available
2. add Council resolution
3. compare resolved model/provider with old path
4. switch execution to Council/Hermes
5. verify
6. remove obsolete direct-provider routing only when no longer used

## 9.2 Preserve public privacy behaviour

The current Self chat has privacy/user-recognition protections. Preserve the web-facing security flow, but separate:

```text
Who is the visitor?
```

from:

```text
What is the agent's canonical state?
```

Self identifies/authenticates the user. Council decides what agent data the user may access.

## Exit condition

A conversation made through public Self is genuinely the same conversation/agent context that Hermes can retrieve through Council.

---

# 10. Phase 7 — Canonical Conversation Storage and Vector References

## Objective

Remove Self's independent chat log as an agent-memory authority while preserving historical data and web audit requirements.

Council already has `ConversationController` and `conversation_history`. fileciteturn29file0L2-L10

## 10.1 Canonical log fields

Ensure canonical conversation records retain enough information to distinguish:

- agent
- user/operator
- session
- interface/source
- timestamp
- role
- model used
- relevant tool/action metadata

Do not discard useful audit metadata from Self merely because the canonical log moves to Council.

## 10.2 Source interface

Where Council's conversation schema does not yet include an interface/source field, add one or an equivalent metadata field so a record can indicate:

```text
self_public
self_admin
from_the_noise
hermes_cli
other_future_interface
```

This is diagnostic metadata, not a second conversation namespace.

## 10.3 Vector reference model

Vectors should be generated from canonical conversation logs.

Vector records should reference:

```text
conversation/session ID
message ID or message range
agent
operator/user
embedding
created_at
```

The vector is an index, not the authoritative transcript.

## 10.4 Concurrency

Audit `ConversationController::append()` carefully. The current implementation calculates the next sequence as `MAX(message_seq) + 1`. This may be vulnerable under concurrent writers.

Because Self and Hermes will eventually write to the same canonical conversation store, make message sequencing safe before declaring multi-interface conversation storage complete.

Use an existing safe pattern if the Council codebase already has one. Do not invent unnecessary distributed locking.

## Exit condition

Self, From the Noise and Hermes can append/read the same canonical conversation without sequence collisions, and vector references resolve to the original records.

---

# 11. Phase 8 — Reconcile From the Noise

## Objective

Verify that From the Noise is a workflow client rather than a separate agent backend.

The Self repository already contains From the Noise content and public/post interfaces. fileciteturn20file0L1-L5 fileciteturn20file2L12-L15

## Tasks

Trace the full production flow:

```text
News Desk
 -> scan
 -> leads
 -> research/context
 -> agent generation
 -> content suite
 -> image prompts
 -> post storage
 -> publication
```

For each AI call identify:

- agent
- head
- model
- knowledge source
- memory source
- conversation state
- provider selection

Replace any local agent state access with Council calls.

## Do not move editorial website content unnecessarily

Posts, publication state, slugs, site display data and presentation metadata can remain in Self if they are website/content concerns rather than agent cognition.

The distinction is:

```text
The article exists on the website -> Self

What Zeon7 knows about the subject -> Council/ForeverBox

How Zeon7 behaves while generating it -> Council/Hermes
```

## Exit condition

From the Noise is demonstrably using the same canonical agent/head/model/memory system as chat and Hermes.

---

# 12. Phase 9 — Reduce the Self Database

## Objective

After successful migration, remove the duplicate agent-function state from `zeon7_self`.

Do not drop anything until migration and acceptance tests have passed.

## Suggested treatment

### KEEP

- users
- sessions
- authentication
- UI configuration
- page/site configuration
- template configuration
- theme configuration
- navigation
- publication/content state
- web-only telemetry if it is genuinely web-specific

### MOVE

- agent persona records
- agent heads
- agent instructions
- agent lore/memory
- agent knowledge
- vector memory
- canonical conversation history
- canonical model/routing state

### REPLACE

- local AI provider selection
- local agent search
- local memory search
- local agent context construction

### ARCHIVE

Historical chat and data that cannot or should not be merged directly should be exported into an archival format before old tables are removed.

### DELETE

Only after:

- migration snapshot exists
- data reconciliation complete
- cross-interface tests pass
- a rollback point exists

## Exit condition

If `zeon7_self` is queried for canonical agent memory, agent personality, agent head, model routing or agent conversation state, the answer should be that the information lives in Council/ForeverBox and is accessed through the Council client.

---

# 13. Phase 10 — Preserve and Generalise the Self Component System

## Objective

Make the current Self UI the foundation of `i-am-self`.

Do this only after the backend boundary is stable enough that UI work is not constantly coupled to changing database structures.

## Requirements

Create/maintain a common component layer for:

- header
- sidebar
- navigation
- panels/cards
- chat
- message rendering
- memory views
- conversation history
- search
- agent status
- model/head status
- activity
- tasks
- tools
- notifications
- forms
- modals
- telemetry

## Agent templates

Agent-specific layouts should be compositions of the same components.

For example:

```text
shared/components
       |
       +---- Zeon7 cockpit
       +---- Leon workspace
       +---- Gemma coach
       +---- Otec director
       +---- Wolf worker
```

The template must not own agent logic. It receives resolved agent capability/context from Council.

## User assignments

Council already has `user_agent_assignments` with `template_id`, permissions and memory scope. fileciteturn27file0L2-L10

Use this as the canonical assignment mechanism where appropriate, while keeping purely visual presentation configuration in Self.

## Exit condition

A second agent can be introduced with a new UI composition without copying the application or introducing another backend.

---

# 14. Phase 11 — Security and Trust Boundary

## Objective

Make the architecture safe when Self becomes an agent-control surface.

There are two separate trust layers:

### Self

Answers:

> Who is this web user?

Handles:

- login
- session
- CSRF
- rate limiting
- UI access

### Council

Answers:

> What is this user allowed to do to canonical agent state?

Handles:

- agent context
- permissions
- memory scope
- privileged operations
- agent-level authorization

Council already has `Auth`, `AgentContext` and `PrivilegedActionGate` middleware. fileciteturn41file0L2-L10

## Mandatory rule

Never rely on Self's hidden UI elements as authorization.

The Council API must reject an unauthorized:

- head creation
- head modification
- model/routing modification
- memory access
- conversation retrieval
- privileged action

## Audit trail

All administrative canonical-state mutations should have sufficient logging to answer:

```text
who
changed what
when
through which interface
from which previous state
```

---

# 15. Phase 12 — Migration Tooling and Rollback

## Objective

Make database migration reversible.

Create migration scripts for Self that can:

- snapshot tables
- export rows
- mark migration status
- verify record counts
- verify hashes where practical
- archive old tables
- restore archived data

Do not perform destructive `DROP TABLE` operations in the same deployment that introduces the new Council path.

Use a sequence:

```text
snapshot
 -> migrate
 -> dual-read verification if needed
 -> switch authority
 -> verify
 -> archive
 -> later delete
```

Where possible, introduce feature flags/configuration to allow temporary rollback from Council-backed path to the old Self path during migration testing. Once the canonical path is proven, remove the fallback rather than leaving two active authorities.

---

# 16. Phase 13 — Automated Verification

Create an integration test suite focused on architectural invariants.

## Test group A — Agent catalogue

- Self can list agents through Council.
- No Self-local agent catalogue is required.

## Test group B — Head creation

1. Create `designer` for Zeon7 in Self Admin.
2. Read it through Council.
3. Read it through Hermes tooling.
4. Use it in Self preview.
5. Verify all three return the same definition.

## Test group C — Head update

1. Change one component in Self.
2. Verify canonical version changes.
3. Verify Hermes resolves new value.
4. Verify old value is not still authoritative anywhere.

## Test group D — Model routing

1. Change agent routing assignment in Self.
2. Resolve the agent through Council.
3. Verify the expected model/provider/layer.
4. Verify Hermes uses the same resolution.

## Test group E — Memory

1. Write a durable memory through Hermes.
2. Search it through Council.
3. Search it through Self.
4. Read original record.

Repeat in the opposite direction.

## Test group F — Conversation

1. Start a Self conversation.
2. Append user/assistant messages.
3. Retrieve through Council.
4. Retrieve through Hermes.
5. Verify same session/agent/history.

## Test group G — From the Noise

1. Generate a test production workflow item.
2. Verify correct agent/head/model.
3. Verify canonical context is used.
4. Verify durable learning is stored in canonical memory when applicable.

## Test group H — Authorization

- authorized user can use assigned agent
- unauthorized user receives denial
- UI hiding alone does not grant security
- privileged operations still require Council gate

---

# 17. Phase 14 — Manual End-to-End Verification

Before calling the integration complete, run the system manually in this order.

### Scenario 1 — Self Admin creates a head

```text
Self Admin
 -> Zeon7
 -> New head: designer
 -> save
```

Then:

```text
Hermes CLI
 -> Zeon7
 -> designer
 -> inspect effective identity
```

The result must match.

### Scenario 2 — Hermes changes canonical agent state

Make a permitted Council/Hermes-side change.

Then open Self Admin and verify that the change is visible immediately.

### Scenario 3 — Memory continuity

Teach Zeon7 a non-sensitive test fact through Hermes.

Open Self chat and ask a semantically equivalent question.

The answer should be derived from the same Council memory.

### Scenario 4 — Self learning continuity

Create a durable test memory through Self.

Query it through Hermes.

### Scenario 5 — From the Noise continuity

Use a test From the Noise generation with an explicit test fact/context.

Verify that any intended durable agent knowledge enters canonical storage and is retrievable through authorised interfaces.

### Scenario 6 — UI continuity

Use the agent-template system to present the same canonical agent in two different templates.

Verify that visual differences do not change underlying agent state.

---

# 18. Phase 15 — Documentation Update

Once implementation is proven, update documentation in all three repositories as appropriate.

## Self

Update:

- README
- API/client documentation
- deployment docs
- Self database documentation
- architecture docs
- migration status

## Council

Update:

- API documentation
- agent management documentation
- integration documentation
- route/controller documentation
- conversation/vector documentation

## ForeverBox Data

Update:

- agent profile documentation
- data ownership notes
- sync documentation
- interface integration notes

Do not rewrite historical V1 documents. Add V2/V3 documents as the architecture evolves.

---

# 19. Recommended Commit Strategy

The coding model should not create one enormous commit.

Use small, reversible commits aligned to architectural phases.

Suggested sequence:

```text
1. docs: add V2 implementation inventory
2. council: expose/extend agent catalogue API
3. council: add/extend head management API
4. council: expose model/routing management
5. self: add Council API client layer
6. self: migrate agent admin reads to Council
7. self: migrate head editing to Council
8. self: migrate model management to Council
9. self: migrate instruction authority to Council
10. self: migrate lore/memory operations
11. self: migrate knowledge search
12. self: migrate public chat runtime
13. council: unify conversation interface metadata
14. self: migrate chat history writes
15. self: reconcile From the Noise runtime
16. self: archive/remove duplicate agent tables
17. self: generalise component/template system
18. tests: add cross-interface acceptance suite
19. docs: mark V2 complete and record deviations
```

Each commit should leave the application runnable whenever practical.

---

# 20. Code-Quality Requirements for the Builder

Use the existing project's conventions unless a change is necessary to achieve the V2 boundary.

### PHP

- strict types where the existing repository uses them
- PDO prepared statements
- explicit error handling
- no silent fallback to obsolete agent state
- no raw duplicated database logic in controllers when a service layer exists
- validate all external input
- preserve CSRF for Self mutations

### API

- stable JSON response shape
- clear HTTP status codes
- meaningful error messages
- auth and authorization checks
- request correlation IDs where feasible
- no secrets in logs

### JavaScript

- use existing component patterns
- no inline duplication of API contracts
- centralized Council client calls
- graceful loading/error states

### Database

- do not drop tables before migration verification
- preserve backups
- use explicit migration scripts
- document ownership changes

---

# 21. Known Current-System Integration Risks

The builder should explicitly inspect these before assuming V2 is already implemented underneath.

## Risk A — Council controller ownership versus file authority

The Council blueprint states that some database structures mirror `SOUL.md` rather than replacing it, while Hermes still treats `SOUL.md` as canonical in places. fileciteturn16file0L2-L2

Do not silently choose a new authority. Determine the real current runtime authority for each Soul/head field and preserve it deliberately.

## Risk B — Memory search versus vector search

Council has both memory storage and Commons vector search, but these are not automatically the same thing. The current `MemoryController::search()` is FULLTEXT-based, while `QuiddityController` delegates shared Commons semantic search to `VectorSearch`. fileciteturn30file0L2-L2 fileciteturn42file0L2-L2

Use the correct subsystem for each requirement.

## Risk C — Conversation sequence concurrency

The current ConversationController calculates the next sequence with `MAX(message_seq)+1`. fileciteturn29file0L2-L10

This should be hardened before Self and Hermes become concurrent writers.

## Risk D — Ingestion controller maturity

The current IngestionController exposes an accepted/queued interface but contains a placeholder note that the worker performs the full ingestion. fileciteturn32file0L2-L2

Do not reimplement ingestion inside Self. Use the existing worker pipeline or extend Council's ingestion pipeline.

## Risk E — Direct filesystem access

The current QuiddityController works with `/foreverbox_data/Quiddity_Lore_Sea`. fileciteturn42file0L2-L2

Self should consume this through Council instead of assuming that production web hosting can directly see the same filesystem forever.

---

# 22. What Must Not Be Changed in This Project

This implementation does **not** include:

- LoRA
- personalised fine-tuning
- adaptive model training
- cognitive profiling
- neurodivergence-specific experimentation
- autonomous self-training
- consciousness features
- photonic architecture
- new AI research features

Those are future work.

The current job is foundational:

> **Unify the existing systems so every interface operates on one canonical agent reality.**

---

# 23. Final Definition of Done

The V2 rebuild is complete only when all of the following are true.

### Canonical state

- [ ] Council/ForeverBox is the authority for agent function.
- [ ] Self has no competing agent state database.
- [ ] Heads are canonical and editable through Self.
- [ ] Model choices/routing are canonical and editable through Self.
- [ ] Agent identity is canonical.
- [ ] Agent memory is canonical.
- [ ] Knowledge is canonical.
- [ ] Conversations are canonical.

### Interfaces

- [ ] Self Public uses canonical Council/Hermes execution.
- [ ] Self Admin is a management front end for Council.
- [ ] From the Noise uses canonical agent state.
- [ ] Hermes sees Self Admin changes without synchronization.
- [ ] Self sees Hermes/Council changes immediately.

### Memory

- [ ] Canonical logs remain authoritative.
- [ ] vectors reference logs rather than replacing them.
- [ ] cross-interface conversation retrieval works.
- [ ] permissions are enforced before memory is returned.

### UI

- [ ] existing Self component system is preserved
- [ ] existing template system remains the basis
- [ ] agents can have distinct UI compositions
- [ ] no separate application is required for a new agent template

### Security

- [ ] Self authentication remains intact
- [ ] Council authorization is enforced
- [ ] privileged actions remain gated
- [ ] CSRF/rate limiting remains intact
- [ ] no secrets are leaked into logs or client responses

### Data migration

- [ ] Self database inventory is documented
- [ ] historical data has been migrated or archived
- [ ] obsolete agent tables are removed only after verification
- [ ] rollback procedure has been tested

---

# 24. Builder Workflow Summary

The builder should follow this loop throughout the project:

```text
READ CURRENT CODE
      |
      v
IDENTIFY EXISTING CAPABILITY
      |
      v
REUSE / EXTEND COUNCIL
      |
      v
ADD SELF CLIENT / UI
      |
      v
RUN TARGETED TEST
      |
      v
RUN CROSS-INTERFACE TEST
      |
      v
COMMIT
      |
      v
DOCUMENT DEVIATION IF ANY
```

Do not skip directly from architecture to large rewrites.

Whenever an existing Council capability already satisfies a requirement, integrate with it.

Whenever Council is missing a capability, add it to Council rather than adding a duplicate capability to Self.

Whenever Self already has a working UI component, reuse it rather than replacing it with a new one.

---

# 25. Final Builder Instruction

Do not interpret this plan as permission to redesign the architecture while implementing it.

The architecture has already been decided.

The job is to make the current repositories conform to it while preserving working functionality.

The desired final state is:

```text
                         ONE FOREVERBOX SYSTEM
                                  |
            +---------------------+---------------------+
            |                     |                     |
            v                     v                     v
      foreverbox-data        council-library        i-am-self
       persistent data       cognition/state       human interface
            |                     |                     |
            |              canonical agent state       |
            |              memory / vectors            |
            |              heads / models              |
            |              routing / context           |
            |                     |                     |
            +---------------------+---------------------+
                                  |
                     +------------+------------+
                     |            |            |
                     v            v            v
                  Self        From Noise    Hermes CLI
                  Public         Prod           CLI
                     |            |            |
                     +------------+------------+
                                  |
                       SAME CANONICAL AGENTS
                                  |
                         SAME HEADS / MODELS
                                  |
                          SAME MEMORY / DATA
                                  |
                         SAME CONVERSATION LOGS
```

The implementation is successful when the system no longer behaves like three applications connected together.

It should behave like **one agent ecosystem with multiple interfaces**.
