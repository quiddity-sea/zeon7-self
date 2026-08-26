# Hermes Agent Control & Unified Architecture V2

**Status:** Architecture revision / implementation plan  
**Supersedes for implementation:** `docs/hermes-integrate-v1.md`  
**Preserves for history:** `docs/hermes-integrate-v1.md` remains unchanged as the previous architectural proposal.  
**Scope:** `zeon7-self` / future `i-am-self`, `council-library`, `foreverbox-data`, Hermes CLI, and the existing From the Noise production interface.

## 1. Why V2 Exists

V1 correctly identified the central problem: the Self web application and the Hermes/Council environment currently maintain overlapping agent state, creating a split-brain system. V1 proposed wiring Self into the Hermes ecosystem so that the web interface could use the same personalities, heads, models and memory as the CLI.

During architecture review, the intended outcome was clarified further.

The goal is **not merely to make Self consume Council data**.

The goal is to make **Council + ForeverBox Data the canonical source of truth for the agents**, while making Self the human-facing administration and presentation layer for that canonical system.

Self must therefore be able to provide a complete management interface for the canonical agent system. An authorised administrator should be able to use Self to inspect, create and modify agents, heads, model assignments and other supported agent configuration, while Hermes and every other interface immediately sees the same canonical state.

The resulting system must have **one agent state and multiple interfaces**, not multiple copies of the agent state that happen to synchronise.

---

# 2. Core Architectural Principle

> **Council and ForeverBox Data are the canonical source of truth for agents. Self, From the Noise and Hermes are interfaces into that same canonical system.**

The target relationship is:

```text
                         CANONICAL AGENT STATE
                    Council + ForeverBox Data
                              |
             +----------------+----------------+
             |                |                |
             v                v                v
        Self Public       Self Admin       Hermes CLI
             |                |                |
             +----------------+----------------+
                              |
                       SAME AGENT STATE
```

No interface should maintain a competing copy of:

- Agent identity
- SOUL / personality definitions
- Heads / personality variants
- Agent model configuration
- Model routing policy
- Agent memory
- Semantic vectors
- Knowledge used by the agents
- Agent conversations
- Agent capability definitions
- Agent context
- Agent-specific operational state where Council is authoritative

There must be no requirement for a synchronisation process to make two independent agent databases agree. They should be reading and writing the same canonical state in the first place.

---

# 3. The ForeverBox Triad in This Integration

The three-part architecture remains:

```text
                    FOREVERBOX
                         |
         +---------------+---------------+
         |               |               |
         v               v               v
       DATA           COUNCIL           SELF
         |               |               |
      What exists     What thinks      How humans
      and persists    and acts         interact
```

## 3.1 ForeverBox Data

`foreverbox-data` remains the persistent ecosystem/data layer.

It supplies:

- Agent profiles
- SOUL files
- Hermes configuration
- Shared skills
- Agent skill links
- Quiddity Lore Sea
- Project and ecosystem data
- File-based knowledge
- Shell wrappers used by agents
- Synchronisation resources

The existing repository already provides these structures, including agent profiles, SOULs, configuration, shared skills, Lore Sea, memory wrappers and Council integration. fileciteturn14file0L2-L2

## 3.2 Council Library

Council is the canonical cognitive and agent-state service layer.

It provides:

- Agent registry / identity configuration
- SOUL component data where applicable
- Heads / variants
- Agent context
- Sanctum memory
- Commons knowledge
- Semantic/vector search
- Conversation history
- Ingestion
- Cognitive Router
- Model routing
- Privacy gates
- Budget gates
- Wolf task infrastructure
- Privileged-action controls
- API access

The current Council implementation already has the relevant architecture: Commons, Sanctums, Registry, vector search, ingestion, conversation controllers, Cognitive Router, Wolf infrastructure and guarded REST endpoints. fileciteturn13file0L2-L2

## 3.3 Self / I-Am-Self

`zeon7-self` is being evolved toward `i-am-self`.

Self owns the **web experience and web-specific state**, including:

- Authentication
- Web users
- Sessions
- UI preferences
- Site structure
- Page/layout configuration
- Component configuration
- Template configuration
- Themes
- Public/admin presentation
- Agent-template assignments for users
- Web-specific content presentation

Self must not become a second agent backend.

---

# 4. Self Database Boundary

The Self database should be intentionally narrow.

## 4.1 Self may own

```text
users
sessions
authentication
UI preferences
themes
layouts
components
templates
site routes
navigation
web-specific configuration
user -> agent presentation assignments
```

## 4.2 Self must not own canonical agent state

The following must be migrated away from the Self database where they currently exist as agent state:

```text
agent identity
SOUL definitions
heads
agent memory
knowledge used by agents
vector indexes for agent memory
agent conversation history
agent model definitions
agent model routing
agent capabilities
agent-specific cognitive state
```

The test for ownership is simple:

> **If deleting the Self database would make an agent forget something, the data is probably in the wrong database.**

Conversely:

> **If deleting the Self database would make the website forget how to render itself, that data belongs in Self.**

---

# 5. No Parallel Agent Backend in Self

V1 allows several areas of Self to connect directly to Council databases. V2 changes the preferred boundary.

Self should not need to know the internal SQL schema of `agent_registry`, Sanctum databases or other Council storage.

Instead the desired architecture is:

```text
Self Admin / Public
        |
        v
   Council API
        |
 +------+------+----------------+
 |             |                |
Agent Mgmt   Context          Memory/Search
 |             |                |
 +-------------+----------------+
               |
        Canonical storage
```

The Council API becomes the stable contract between Self and the cognitive backend.

Direct database access from Self may be used temporarily during migration or for tightly controlled administrative operations where no suitable API exists, but direct cross-database access must not become the long-term architecture.

This keeps the database structure encapsulated inside Council and allows the internal implementation to evolve without forcing a rewrite of Self.

---

# 6. Self Becomes the Agent Management Front End

This is the major clarification from V1.

Self is not merely a read-only presentation layer.

It becomes the **human administration surface for the canonical agent system**.

An authorised administrator should be able to use Self to:

- View available agents
- View agent identities
- View available heads
- Create new heads
- Edit head components
- Manage base personality components where permitted
- Inspect model assignments
- Change model assignments
- Inspect supported capabilities
- Configure public-facing agent selection
- Configure agent/head combinations
- View agent memory and knowledge through Council
- Trigger supported memory/knowledge operations
- Inspect conversation history
- Manage agent-specific settings exposed by Council

The critical rule is:

> **Self writes these changes to Council's canonical state. Hermes then sees the same state without a second synchronisation system.**

Example:

```text
Administrator
    |
    v
Self Admin
    |
    | Create head: designer
    v
Council Agent Management API
    |
    v
Canonical Agent Registry / SOUL state
    |
    +------------+------------+
    |                         |
    v                         v
Self Runtime              Hermes CLI
```

---

# 7. Agent, Head and Model Management

## 7.1 Agent registry

The canonical agent registry remains a Council concern.

Self should request an agent catalogue through Council rather than reconstructing it locally.

Example conceptual response:

```json
{
  "agent": "zeon7",
  "display_name": "Zeon7",
  "heads": ["default", "coder", "fiction"],
  "available_models": ["local-zeon7-gemma", "deepseek-v4-flash"],
  "capabilities": ["memory", "search", "tools"]
}
```

The exact API shape is implementation-specific and should follow the existing Council conventions rather than being invented solely for Self.

## 7.2 Heads

A head is a canonical agent variant, not a Self-only prompt.

Self should provide a visual editor for heads, but the saved definition belongs to Council/ForeverBox agent state.

Creating:

```text
Zeon7 -> designer
```

through Self must cause `designer` to become available to Hermes and any other authorised interface using the same agent registry.

## 7.3 Model selection

Model configuration must likewise be canonical.

Self should provide the UI for:

- Viewing models
- Selecting model/provider assignments
- Viewing available routing layers
- Selecting supported agent overrides
- Changing public/default assignments

The actual routing decision remains Council's responsibility.

Self should never silently maintain a second model registry.

The existing Council Cognitive Router already has central model profiles and agent overrides. fileciteturn18file0L2-L2

---

# 8. Unified Runtime Path

The public Self chat, administrative test/preview functions, From the Noise workflows and Hermes must ultimately use the same canonical agent execution path.

Desired path:

```text
User / Admin / From The Noise / Hermes
                 |
                 v
          Agent Context
                 |
                 v
          Council Router
                 |
       +---------+---------+
       |                   |
       v                   v
     Memory             Model/Head
       |                   |
       +---------+---------+
                 |
                 v
              Hermes
                 |
                 v
              Agent
```

The interface that initiated the request should not change the agent's underlying identity or memory architecture.

---

# 9. Public Self Chat

The current Self chat uses its own instruction/memory pathway. That must be migrated.

The new public chat flow should be:

1. Authenticate or establish the appropriate public/user context.
2. Identify the assigned/selected agent.
3. Resolve the allowed head through Council.
4. Resolve the model through Council.
5. Resolve the required agent context through Council.
6. Perform semantic memory/knowledge retrieval through Council.
7. Execute the request through the canonical Hermes/agent pathway.
8. Persist the conversation into the canonical conversation store.
9. Make the resulting knowledge/memory available to other authorised interfaces.

Self's `api/chat.php` should become an adapter into that pipeline rather than an alternative implementation of it.

---

# 10. Conversation and Learning Principle

This is the most important runtime invariant after canonical ownership.

> **A useful interaction must not become a different memory depending on which interface produced it.**

The intended behaviour is:

```text
                     Interaction
                         |
          +--------------+--------------+
          |              |              |
        Self         From Noise       Hermes
          |              |              |
          +--------------+--------------+
                         |
                         v
                 Canonical Council
                  conversation state
                         |
                         v
                 Memory / vectors
                         |
          +--------------+--------------+
          |              |              |
        Self         From Noise       Hermes
```

If an authorised agent learns something through Hermes, it must be retrievable through Self.

If an authorised agent learns something through Self, it must be retrievable through Hermes.

If an interaction through From the Noise creates durable agent knowledge, it must enter the same canonical memory architecture.

---

# 11. Conversation Logs and Vector References

Conversation logs remain authoritative records.

Vectors are semantic indexes/references to those logs, not replacements for them.

```text
Conversation Logs
      |
      | embedding/indexing
      v
Conversation Vector Index
      |
      | reference
      v
Original Conversation / Message Range
```

The Council architecture already includes conversation history and conversation vector infrastructure. The Self migration should reuse that architecture rather than maintaining separate Self-only conversation vectors.

A vector hit should identify the source conversation/message range so that the original records can be retrieved for context.

---

# 12. Shared Knowledge and Quiddity Sea

Self must not create a second web-only copy of the agent knowledge base merely because the web interface needs search.

Where the canonical knowledge already lives in the Quiddity Lore Sea, Commons or other Council-managed structures, Self should consume the same source through the Council interface.

The existing ForeverBox Data repository already provides the Quiddity Lore Sea and ingestion/memory wrappers. fileciteturn14file0L2-L2

The existing Council Commons provides vectorised shared knowledge and semantic search. fileciteturn13file0L2-L2

The Self knowledge UI should therefore become a **front end for those facilities**, not a new knowledge system.

---

# 13. From the Noise

From the Noise already exists within the Self application as a production/content interface and should be treated as a client of the unified backend, not as a separate agent implementation.

The migration task is therefore to verify that all From the Noise workflows use:

- The canonical agent
- The canonical head
- The canonical model/routing configuration
- The canonical knowledge
- The canonical memory
- The canonical conversation/context where appropriate

Any remaining Self-local agent data required only by From the Noise should be classified and migrated to the appropriate canonical system.

The From the Noise interface itself remains a Self presentation/workflow concern.

---

# 14. Self Component and Template Architecture

The current Self component/template system is the foundation for the future interface system and must be preserved.

The integration work must not replace the existing UI architecture with separate agent applications.

Target structure:

```text
i-am-self
|
+-- shared components
|
+-- shared design system
|
+-- layouts
|
+-- site/page structure
|
+-- public interface
|
+-- admin interface
|
+-- agent templates
|     +-- zeon7
|     +-- leon
|     +-- gemma
|     +-- otec
|     +-- wolf
|     +-- future agents
|
+-- Council client
```

Agents may have very different layouts and visual identities while still consuming the same underlying component library and Council API.

The architecture is therefore:

> **Shared components, individual agent compositions.**

Not:

> Five independent web applications.

---

# 15. User Access and Agent Assignments

Self remains responsible for presenting different agent experiences to different authenticated users.

The relationship is:

```text
User
 |
 +-- authentication
 |
 +-- agent assignment
       |
       +-- agent_id
       +-- permissions
       +-- capabilities exposed in Self
       +-- template_id
```

The template and UI assignment may live in Self because it is presentation configuration.

The actual agent permissions and agent-state authority must be enforced by Council.

A user must not gain access to an agent merely because a UI element is hidden or displayed.

---

# 16. Hermes Integration

Hermes remains an agent execution interface, not a second source of truth.

The preferred architecture is:

```text
Hermes
  |
  v
Council integration
  |
  +-- Agent Context
  +-- Memory
  +-- Search
  +-- Model routing
  +-- Agent identity
  +-- Skills / actions
  |
  v
Canonical ForeverBox state
```

Where Hermes has local runtime/session state, that state should be clearly distinguished from canonical long-term agent state.

The integration must not create another parallel memory/identity database simply to satisfy Self.

---

# 17. Migration of the Existing Self Database

The migration should be explicit and reversible.

For every table currently in `zeon7_self`, classify it as:

```text
KEEP     -> genuine Self/UI/site state
MOVE     -> canonical Council/agent state
REPLACE  -> use Council API instead
DELETE   -> obsolete duplicate
ARCHIVE  -> historical data retained for migration/reference
```

Particular attention should be given to:

- `system_instructions`
- `chat_logs`
- `lore`
- `knowledge_doc`
- `knowledge_chunk`
- any agent/persona tables
- any model/provider configuration tables
- any agent routing/configuration currently stored locally

The existing V1 proposal already identifies the isolated `system_instructions` and `chat_logs` as split-brain sources that must be deprecated for agent use. fileciteturn21file0L2-L2

---

# 18. Do Not Remove the Existing UI While Migrating

The migration should be incremental.

The existing Self application already contains:

- Public interface
- Admin cockpit
- Chat interface
- Reusable components
- Knowledge/lore views
- News Desk / From the Noise
- Authentication
- Theme/design infrastructure
- AI service abstraction

These should be retained while their backend dependencies are progressively redirected into Council.

The desired process is:

```text
Existing Self UI
      |
      v
Replace local data access
      |
      v
Council-backed data access
      |
      v
Remove redundant local state
      |
      v
Refactor reusable components/templates
      |
      v
Agent-agnostic i-am-self
```

No wholesale rewrite is required.

---

# 19. Revised Implementation Phases

## Phase 0 - Establish canonical ownership

**Objective:** Decide and document exactly where each category of data belongs.

Tasks:

- Inventory Self database tables.
- Classify each table as Self-owned, Council-owned, ForeverBox-owned or obsolete.
- Identify every local agent-state dependency in Self.
- Identify every Council/ForeverBox source that already provides the required data.
- Define the canonical ownership rules in documentation.

**Exit condition:** No ambiguous ownership remains for agent state.

---

## Phase 1 - Establish the Council Agent Management API

**Objective:** Give Self a stable interface for managing canonical agent state.

Tasks:

- Define agent catalogue endpoint(s).
- Define head catalogue endpoint(s).
- Define head/component CRUD operations.
- Define model/provider catalogue access.
- Define model assignment/configuration operations.
- Define capability/configuration access.
- Apply Council authentication and authorisation.
- Ensure writes update canonical storage used by Hermes.

**Exit condition:** Self can manage an agent/head/model configuration without directly owning that data.

---

## Phase 2 - Rewire Self Admin to Council

**Objective:** Turn the existing Self admin into the management front end for Council.

Tasks:

- Build Hermes/Agent Router UI using the existing component system.
- Build agent/head management UI.
- Build head creation/editing UI.
- Build model selection UI.
- Replace local agent configuration writes with Council API calls.
- Keep Self DB only for UI/site configuration.

**Acceptance test:** Create a new head in Self and verify it is immediately available through the Hermes environment without a synchronisation step.

---

## Phase 3 - Rewire Public Chat

**Objective:** Make Self public chat execute against the same agent state used by Hermes.

Tasks:

- Resolve agent through Council.
- Resolve head through Council.
- Resolve model/routing through Council.
- Retrieve canonical context through Council.
- Execute through the canonical Hermes/agent pathway.
- Write conversations to canonical Council storage.
- Remove dependency on Self's isolated agent instructions/memory.

**Acceptance test:** A change made to an agent/head/model in Self Admin changes the behaviour of Self public chat exactly as expected, and the same agent/head/model is available from Hermes.

---

## Phase 4 - Reconcile From the Noise

**Objective:** Ensure From the Noise uses the same canonical agent backend.

Tasks:

- Trace current From the Noise generation workflow.
- Identify any remaining local agent/instruction/memory access.
- Replace duplicated agent state with Council-backed access.
- Verify generated content uses the same agent/head/model context as other interfaces.
- Verify useful learning/knowledge created by the workflow enters canonical storage where appropriate.

**Exit condition:** From the Noise is another client of the same agent system.

---

## Phase 5 - Unify Conversation and Memory

**Objective:** Eliminate remaining conversation/memory splits.

Tasks:

- Move or reconcile historical Self conversations.
- Establish canonical conversation IDs and session IDs.
- Ensure conversation records identify agent, user/operator and source interface.
- Generate semantic vector references from canonical logs.
- Ensure vector retrieval returns references to authoritative conversation records.
- Ensure permissions are enforced before returning memory.

**Exit condition:** Relevant learning from any supported interface can be retrieved from every other authorised interface.

---

## Phase 6 - Reduce the Self Database

**Objective:** Make the Self DB explicitly UI/site focused.

Tasks:

- Remove migrated agent tables.
- Remove duplicated knowledge tables.
- Remove duplicated vector/index tables.
- Remove duplicated instruction/persona tables.
- Remove redundant model/provider configuration.
- Preserve users, sessions and UI/site configuration.
- Add migration documentation for all removed tables.

**Exit condition:** The Self database contains no canonical agent-function data.

---

## Phase 7 - Consolidate the Self Component/Template System

**Objective:** Establish the reusable interface foundation for all agents.

Tasks:

- Extract shared components where required.
- Define common agent shell.
- Define agent template manifests/configuration.
- Separate component logic from agent identity.
- Preserve current Zeon7 visual identity as the first template.
- Establish template loading from agent/user assignments.

**Exit condition:** A second agent can receive a distinct UI composition without cloning the Self application.

---

## Phase 8 - Cross-Interface Verification

**Objective:** Prove that the single-source-of-truth principle works.

Mandatory tests should include:

### Test A: Self -> Hermes

1. Change an agent/head in Self Admin.
2. Start/use the corresponding Hermes profile.
3. Verify the same state is present.

### Test B: Hermes -> Self

1. Create/update supported agent state through Hermes/Council.
2. Open Self Admin.
3. Verify the same state is visible.

### Test C: Hermes -> Self Memory

1. Create a durable memory through Hermes.
2. Query the relevant topic through Self.
3. Verify canonical memory retrieval returns it.

### Test D: Self -> Hermes Memory

1. Create a durable memory through Self.
2. Query the relevant topic through Hermes.
3. Verify it is retrievable.

### Test E: From the Noise -> Other Interfaces

1. Produce/update appropriate durable agent knowledge through From the Noise.
2. Verify the same knowledge is available to authorised agent interfaces.

### Test F: No duplicate state

1. Modify canonical state.
2. Verify no secondary Self-only version remains authoritative.

---

# 20. Security and Permissions

The integration must preserve the Council security model.

Self's web authentication answers:

> Who is this web user?

Council authorisation answers:

> What is this user allowed to do to the agent system?

These are related but different concerns.

Administrative operations such as changing SOUL components, creating heads, changing model routing or performing privileged actions must pass Council-side authorisation as well as Self-side authentication/CSRF controls.

The UI must never be treated as the security boundary.

---

# 21. API Boundary Rules

The Self application should consume conceptual services rather than internal database structures.

Examples:

```text
GET  /agents
GET  /agents/{agent}
GET  /agents/{agent}/heads
GET  /agents/{agent}/models
GET  /agents/{agent}/context

POST /agents/{agent}/heads
PUT  /agents/{agent}/heads/{head}
DELETE /agents/{agent}/heads/{head}

GET  /memory/search
GET  /conversations/{id}
POST /conversations/{id}/messages
```

These are conceptual examples, not instructions to invent duplicate endpoints if equivalent Council endpoints already exist.

The implementation should first inventory the current Council controllers and expose missing operations using the existing API conventions.

---

# 22. Data Consistency Rules

The following rules are mandatory:

1. There is one canonical agent identity.
2. There is one canonical set of heads.
3. There is one canonical model/routing configuration.
4. There is one canonical long-term memory system.
5. There is one canonical conversation history for supported agent interactions.
6. Vector indexes reference canonical records.
7. Self does not silently cache canonical agent state as an alternative source of truth.
8. Hermes does not create a second authoritative memory system for the same agent state.
9. From the Noise uses the same canonical agent state.
10. New interfaces must consume Council rather than creating new agent storage.

---

# 23. What V2 Deliberately Does Not Include

This document is an **integration plan**, not the future personalisation research plan.

It does not implement or require:

- LoRA training
- User-specific model fine-tuning
- Adaptive model training
- Cognitive profiling research
- Personalised model adapters
- Master's research experiments
- Neurodivergence-specific evaluation protocols

Those may become later work once the unified architecture is stable.

The purpose of V2 is to establish the clean shared foundation on which those future capabilities can eventually be built.

---

# 24. Future Architecture Enabled by V2

Once the unified architecture works, future research and features can be added without creating another split-brain system.

Conceptually:

```text
                CANONICAL COUNCIL
                       |
          +------------+-------------+
          |            |             |
       Memory       User model     Agent state
          |            |             |
          +------------+-------------+
                       |
              future personalisation
                       |
               future model adaptation
```

The future feature set is deliberately outside the current integration scope.

---

# 25. Migration Principles

### Preserve

Preserve the existing Self UI, components, template architecture, public interface and administration system wherever possible.

### Centralise

Centralise canonical agent identity, heads, models, memory, vectors, conversations and other agent-function state in the Council/ForeverBox ecosystem.

### Expose

Expose canonical capabilities through stable Council APIs so Self can provide a powerful front end without becoming tightly coupled to internal database implementation.

### Verify

Every migration stage must have a cross-interface acceptance test.

### Do not duplicate

When Council already owns a capability, Self should consume it rather than reimplement it.

### Keep history

V1 remains unchanged as the previous architecture proposal. V2 records the clarified architecture so future changes can be compared against a traceable history rather than rewriting the past.

---

# 26. V1 -> V2 Change Summary

| Area | V1 | V2 |
|---|---|---|
| Overall goal | Unify Self with Hermes/Council | Establish Council/ForeverBox as the canonical agent system and make Self its management/presentation surface |
| Self role | Central web control centre | Human-facing control and presentation surface for canonical agent state |
| Agent data | Self reads shared backend | Self does not own agent data; Council is authoritative |
| Agent management | Direct database management proposed | Council Agent Management API is preferred boundary |
| Heads | Self can edit `soul_components` | Self manages canonical heads through Council |
| Models | Self stores public routing selection | Model/routing state is canonical in Council; Self provides the UI |
| Memory | Shared backend access | Canonical Council memory and conversation architecture |
| Conversations | Move Self chat logs to shared backend | Canonical conversation logs across Self, From the Noise and Hermes |
| Vectors | Shared memory retrieval | Canonical semantic index referencing authoritative records |
| From the Noise | Not fully defined | Explicitly treated as another client of the same agent backend |
| UI | Existing Zeon7 interface | Existing component/template system becomes the foundation of all future agent UIs |
| Self DB | Web-only after migration | Explicitly limited to UI/site/user concerns |
| LoRA | Not in V1 | Still not part of V2; intentionally future work |

---

# 27. Immediate Implementation Checklist

- [ ] Inventory Self database tables and agent-related dependencies.
- [ ] Inventory existing Council agent-management endpoints/controllers.
- [ ] Identify missing Council API operations required by Self Admin.
- [ ] Define canonical ownership for every existing Self agent-data table.
- [ ] Implement/extend Council Agent Management API as needed.
- [ ] Build the Self agent/head/model management UI using existing components.
- [ ] Replace local agent management operations with Council calls.
- [ ] Trace `api/chat.php` and replace the independent agent pipeline with the canonical Council/Hermes pipeline.
- [ ] Trace `InstructionService.php` and remove its role as an independent agent instruction authority.
- [ ] Trace Self memory/knowledge code and map each operation to Council/ForeverBox facilities.
- [ ] Trace the From the Noise workflow and identify any remaining duplicated agent state.
- [ ] Establish canonical conversation persistence and retrieval through Council.
- [ ] Migrate or archive historical Self agent data as appropriate.
- [ ] Remove redundant Self agent tables only after successful migration and verification.
- [ ] Build cross-interface acceptance tests.
- [ ] Keep `docs/hermes-integrate-v1.md` unchanged as the architectural history.

---

# 28. Definition of Done

The V2 integration is complete when all of the following are true:

1. Self no longer maintains an independent source of truth for agent function.
2. Self Admin can create and modify supported agent heads through the canonical Council system.
3. Self Admin can manage supported model/head/agent configuration through a UI.
4. Hermes sees changes made through Self without synchronisation.
5. Self sees canonical changes made through Hermes/Council.
6. Self public chat uses the same agent context, memory, head and routing state as Hermes.
7. From the Noise uses the same canonical agent state.
8. Conversations from supported interfaces enter the canonical Council conversation architecture.
9. Semantic vectors reference canonical records rather than replacing them.
10. The Self database contains UI/site/user concerns rather than agent-function state.
11. The existing Self component/template system remains the basis of the interface layer.
12. A second agent can receive a distinct UI template without creating a separate application.
13. Cross-interface tests demonstrate that an agent does not become a different agent depending on where it is accessed.

---

# 29. Final Architecture

```text
                         FOREVERBOX
                             |
           +-----------------+-----------------
           |                 |                 |
           v                 v                 v
      FOREVERBOX DATA      COUNCIL           I-AM-SELF
           |                 |                 |
      files / skills     canonical agent      users
      SOULs / lore       state / memory       UI
      projects           vectors              templates
      resources          routing              site structure
                         Hermes integration   public/admin
                              |
             +----------------+----------------+
             |                |                |
             v                v                v
          Self Public      From Noise       Hermes CLI
             |                |                |
             +----------------+----------------+
                              |
                       SAME CANONICAL AGENTS
                              |
                    SAME HEADS / MODELS / DATA
                              |
                     SAME MEMORY / KNOWLEDGE
                              |
                        SAME CONVERSATIONS
```

The objective is not to make three systems behave similarly.

The objective is to make them **operate on the same underlying agent reality**.

Self becomes the human-facing control and presentation layer. Council becomes the canonical cognitive and agent-state service. ForeverBox Data remains the persistent ecosystem/data layer. Hermes remains an execution interface into the same system.

That is the architecture V2 is intended to establish.
