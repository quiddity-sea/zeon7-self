# ForeverCore Explained for Everyone

## Introduction
ForeverCore is the name of the shared system that now powers every site Merrill Leo cares about. If the individual websites were neighborhoods, ForeverCore would be the town’s infrastructure—roads, utilities, and emergency services all rolled into one. The idea is simple: build a reliable base so each site can be unique without reinventing the essentials every time. This document skips programmer jargon and walks through ForeverCore’s purpose, the sections it offers, how it feels, and where it can go in the future.

## Why ForeverCore Exists
Imagine trying to run multiple art galleries, each with its own lighting, security, and staff. Every time you open a new space, you would need to rehire electricians, buy new ticket booths, and teach staff the same policies again. That’s tiring, expensive, and error-prone. ForeverCore solves this by centralizing the boring-but-important parts: login screens, content editors, data storage, and even the “feel” of the admin tools. Once the foundation is in place, Merrill and collaborators can concentrate on what matters—storytelling, visuals, and new experiences.

Key motivations:
1. **Consistency:** Visitors expect the same level of polish whether they are on mleo, Zeon7, or Foreverbox. ForeverCore ensures buttons behave the same, pages load in predictable ways, and content doesn’t break when PHP updates.
2. **Speed:** Launching a new idea shouldn’t require bootstrapping from scratch. With ForeverCore, a new site means toggling features, loading a theme, and focusing on the unique parts.
3. **Control:** Merrill wants to steer the creative vision but still have a safety net. With everything in one core, admin dashboards, uploads, and diagnostic tools are easier to monitor and improve.

## What ForeverCore Actually Is
At a high level, ForeverCore is a bundle of shared files that sit outside each site’s public folder. When a visitor loads `mleo.zeon7.com`, the site’s bootstrap file reaches into ForeverCore, turns on the right modules, and then hands the final rendering back to the site’s theme. The same happens for Zeon7 and Foreverbox. This reuse ensures that upgrades happen once but benefit every property.

Core ingredients include:
- **Bootstrap & Config Loader:** Ensures each site knows where its assets live, which database to use, and which features are enabled.
- **Router & Controllers:** These are the traffic managers. They decide which page to show when someone visits `/projects` or `/admin/blog.php`.
- **Templates & Overrides:** ForeverCore ships with default templates, but each site can replace (override) them to keep its unique look.
- **Admin Dashboard:** The administrative interface is shared, yet it can be restyled per site. This is where Merrill adds new blog posts, hero sections, projects, visuals, or memorial entries.
- **Helpers & Utilities:** Things like file uploads, email sending, validation, and flash messages (the status banners in admin) live in ForeverCore.

## Sections and Features
### 1. Admin Dashboard
Think of this as the air-traffic control center. The dashboard shows quick stats, cards for each content type, and access to configuration toggles (like turning blog sections on or off). Despite being a shared tool, each site’s admin inherits the original CSS and navigation structure so it feels familiar.

### 2. Content Modules
ForeverCore currently ships with modules for heroes, projects, visuals, blog posts, users, messages, and setup diagnostics. Each module includes:
- **Controller:** Handles requests (listing, creating, updating, deleting).
- **Repository:** Talks to the database.
- **Template:** Displays the data (and can be overridden for styling).

Because these modules are reusable, Merrill can roll out improvements once and all sites benefit. For example, the multi-image uploader introduced for the visuals admin is available anywhere that uses the same component.

### 3. File Upload & Media Handling
Artists and storytellers work with images constantly. ForeverCore’s upload helpers make sure files are stored in the right place, checked for type and size, and assigned consistent web paths. The new drag-and-drop uploader adds layer-on metadata editing so batch uploads don’t become tedious.

### 4. Diagnostics (Setup)
There’s a shared setup page that checks whether the database is reachable, which tables have records, and whether admin accounts exist. Instead of running random SQL queries when something feels off, Merrill can visit `/admin/setup.php` and see a human-readable health report.

### 5. Feature Toggles
ForeverCore lets each site enable or disable sections (projects, blog, visuals, etc.) through database settings. This ensures a site can temporarily hide a module without removing code. It’s like flipping a breaker switch rather than tearing out wiring.

## Visual and Emotional Consistency
For visitors and admins alike, ForeverCore aims to feel artisanal rather than corporate. The admin inherits the mleo typographic stack, navigation layout, and even the loading animation. This is intentional. Merrill wants to feel at home when publishing, not as though they’ve stepped into a generic enterprise tool. Colors, fonts, button shapes, and form spacing remain aligned with the design guide.

On the front end, the shared components (hero sections, project cards, visual grids) are flexible. A hero section can show a photography-focused message on mleo and a memorial introduction on Foreverbox without rewriting code. The system handles data; the site theme handles style.

## Sustainability and Expansion
### Short-Term (Next 6–12 Months)
- **Complete Site Conversions:** Zeon7 and Foreverbox still have legacy pockets; the plan is to finish migrating them so all logic flows through ForeverCore.
- **Documented Workflows:** Produce friendly guides (like this one) so collaborators know how to add content, tweak heroes, or run diagnostics.
- **Stability:** Continue testing uploads, admin overrides, and GSAP animations to ensure nothing regresses as PHP evolves.

### Medium-Term (1–2 Years)
- **New Modules:** Potential additions include e-commerce blocks, membership systems, and data visualizations.
- **Shared Analytics:** Embed simple reporting widgets in the admin so Merrill can see which pages perform best across all sites.
- **Partnership Interfaces:** Allow trusted collaborators (like gallery partners or memorial planners) to log in and manage specific content buckets.

### Long-Term (3–5 Years)
- **White-Labeling:** Once ForeverCore is stable, it could power external client sites, effectively becoming a product.
- **Advanced Media:** Integrate AR/VR or interactive storytelling modules that still plug into the same admin controls.
- **Automation:** Introduce auto-publishing scripts (e.g., dropping a blog post draft at a scheduled time) and system-wide backups.

## Possible Future Expansions
1. **E-commerce Support:** Add product catalog entries, checkout flows, and fulfillment hooks. Because the admin already handles cards for “Projects” and “Visuals,” the same pattern could be reused for store items.
2. **Events / Scheduling:** Build a module for webinars, studio sessions, or client consultations.
3. **API Gateway:** Expose data via a JSON API so mobile apps or partner platforms can read hero sections, blog posts, or memorial entries.
4. **Collaboration Tools:** Add notes, assignment tags, or workflow states to content so teams can coordinate.
5. **Theming Marketplace:** Package override templates so designers can apply new looks quickly (think “mleo-dark,” “zeon7-lab,” etc.).

## Aesthetic Considerations
Even though ForeverCore is infrastructure, it still celebrates design. The forms use the same Montserrat + Source Sans stack as the front end, buttons have consistent radiuses, and the modals mirror the legacy feel. This matters psychologically: when Merrill or collaborators log in, they remain inside the brand experience. It reduces cognitive friction and reinforces that the system is a creative ally rather than an IT chore.

## The Human Benefit
For Merrill, ForeverCore means less time debugging and more time storytelling. For visitors, it means every site feels polished and coherent. For future collaborators, it offers a clear entry point—learn the core once, then contribute across the ecosystem.

Ultimately, ForeverCore is the scaffolding that keeps the entire creative house stable. It enforces quality, accelerates launches, and allows imagination to flourish without collapsing under maintenance burdens. The more reliable the foundation, the bolder the artistry can become.
