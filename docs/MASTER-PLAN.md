# Zeon7 Web Platform - Complete Master Plan

**Project:** Migrate Zeon7 AI from gem to web platform  
**Timeline:** ~15 days  
**Stack:** PHP, MariaDB, HTML/CSS/JavaScript  
**Status:** Planning Complete, Ready for Implementation

---

## 📋 Quick Links

- **[Task Checklist](file:///E:/Userdata/merri/.gemini/antigravity/brain/8d083a95-c81a-4c4e-b182-679fa1775fa8/task.md)** - Organized by phase
- **[Implementation Plan](file:///E:/Userdata/merri/.gemini/antigravity/brain/8d083a95-c81a-4c4e-b182-679fa1775fa8/implementation_plan.md)** - Detailed technical specification
- **[Design System](file:///e:/Dev/Projects/self/docs/design-system.md)** - Colors, typography, spacing, components
- **[Logo Integration](file:///e:/Dev/Projects/self/docs/logo-integration.md)** - Brand identity and logo usage
- **[UI Mockups](file:///e:/Dev/Projects/self/docs/ui-mockups.md)** - Visual designs and specifications
- **[Design Inspiration](file:///e:/Dev/Projects/self/docs/design-inspiration-reference.md)** - Original inspiration images

---

## 🎯 Project Overview

### What We're Building

**Zeon7** is an AI-powered media platform where a well-defined AI persona:
- Curates news and generates "From the Noise" content
- Creates 8-part content suites (blog, social media, TikTok scripts, image prompts)
- Chats with visitors via public widget
- Manages extensive knowledge base and memory (lore)

### Core Features

1. **Admin Dashboard** - Card-based interface for managing all aspects
2. **Public Interface** - "From the Noise" posts listing + chat widget
3. **AI Integration** - Switch between Google Gemini & OpenRouter
4. **Modern Design** - Light/dark mode with bold typography and dotted logo aesthetic

---

## 🎨 Brand Identity

### Logo

![Zeon7 Logo - Dotted Pixel Style](../design-and-insperation-images/logo/zeon7-logo-original.png)

**Characteristics:**
- Dotted/pixelated bitmap aesthetic
- "ZE·7·N7" with circular "7" in center
- Evokes LED displays, signal processing, retro-digital
- Works in black, white, or orange

**Logo Variations:**

![Logo Variations Sheet](../design-and-insperation-images/logo/logo-variations.png)

### Design Inspiration

````carousel
![Dark Mode - Bold Typography](../design-and-insperation-images/inspiration/dark-mode-portfolio.png)

**Dark Mode Reference**
- Pure black backgrounds
- Oversized white headlines
- Orange/red accents
- Timeline layouts

<!-- slide -->

![Dark Mode - Orange Accents](../design-and-insperation-images/inspiration/dark-mode-changelog.png)

**Dark Mode - Changelog**
- Black background
- Vibrant orange (#FF5722)
- Left-aligned dates
- Layered graphics

<!-- slide -->

![Light Mode - Minimal](../design-and-insperation-images/inspiration/light-mode-portfolio.png)

**Light Mode Reference**
- Clean white/off-white
- Huge serif headlines
- Minimal layout
- Generous spacing

<!-- slide -->

![Light Mode - Product](../design-and-insperation-images/inspiration/light-mode-product.png)

**Light Mode - Photography**
- Very light gray
- Product photography
- Soft shadows
- Spacious vertical layouts

<!-- slide -->

![Icon Style Reference](../design-and-insperation-images/inspiration/icon-style-reference.jpg)

**Icon Guidelines**
- Simple 2px line icons
- Rounded caps/joins
- Geometric shapes
- Minimalist
````

---

## 🖼️ UI Mockups

### Admin Dashboard (Dark Mode)

![Admin Dashboard with Logo](../design-and-insperation-images/mockups/admin-dashboard-with-logo.png)

**Features:**
- ZEON7 dotted logo top-left
- Pure black background with subtle dot grid pattern
- 6 dashboard cards in responsive grid:
  - 📖 Knowledge Manager
  - 📝 Instructions Editor
  - 💾 Lore Manager
  - 🤖 Content Generator
  - 📰 Posts Manager
  - ⚙️ API Settings
- Orange accent colors (#FF5722)
- Theme toggle (sun/moon) top-right

---

### "From the Noise" Posts Page (Light Mode)

![Posts Header with Logo](../design-and-insperation-images/mockups/posts-header-with-logo.png)

**Features:**
- Centered ZEON7 logo at top
- Large bold "FROM THE NOISE" headline
- Day theme filter tabs (MON-SUN)
- Post cards with colored left border (theme indicator)
- Clean white background (#FAFAFA)
- Generous spacing

---

- `instruction_set` - Versioned AI prompts
- `lore` - Key-value memory storage
- `posts` - Generated blog posts
- `image_prompt` - AI image generation prompts
- `api_config` - API provider settings
- `api_usage` - Rate limiting tracking
- `gemini_log` - Token usage logging

### Backend Services

**Service Layer:**
- `KnowledgeService` - File upload, chunking, search
- `InstructionService` - Version management
- `LoreService` - Memory CRUD operations
- `GeminiService` - Google Gemini API integration
- `OpenRouterService` - OpenRouter API integration
- `AIServiceFactory` - AI provider abstraction
- `ConfigService` - API settings management
- `ChatService` - Public chat functionality
- `PostService` - Content generation & management

**Middleware:**
- `CsrfMiddleware` - CSRF token protection
- `RateLimitMiddleware` - Request throttling
- `BaseController` - Standardized request/response
- `BaseService` - Database operations

### API Endpoints

**Admin APIs:**
- `/api/knowledge/*` - Upload, list, retrieve, delete files
- `/api/instruction/*` - Get/create instruction versions
- `/api/lore/*` - CRUD memory entries
- `/api/posts/*` - Manage posts
- `/api/generate/*` - AI content generation
- `/api/config/*` - API provider settings

**Public APIs:**
- `/api/chat` - Chat with Zeon7 (rate-limited)
- `/api/posts/published/:slug` - View published post

---

## 📱 User Interface Components

### Admin Pages

| Page | Path | Purpose |
|------|------|---------|
| Dashboard | `/admin/zeon7/` | Main hub with 6 cards |
| Knowledge | `/admin/zeon7/knowledge.php` | Upload/manage markdown files |
| Instructions | `/admin/zeon7/instructions.php` | Edit AI prompts with versioning |
| Lore | `/admin/zeon7/lore.php` | Manage memory key-values |
| Generator | `/admin/zeon7/generate.php` | AI content creation workflow |
| Posts | `/admin/zeon7/posts.php` | Edit/publish posts |
| Settings | `/admin/zeon7/settings.php` | API provider switching |

### Public Pages

| Page | Path | Purpose |
|------|------|---------|
| Posts Listing | `/public/noise/` | Browse "From the Noise" posts |
| Individual Post | `/public/noise/post.php?slug=...` | Read full post |
| Chat Widget | *Component* | Floating chat on all pages |

---

## 🎬 Content Generation Workflow

### "From the Noise" Daily Themes

| Day | Theme | Tagline | Focus |
|-----|-------|---------|-------|
| Monday | Signal Still Comes Through | *The connection's weak, but it's still there* | Survival, resistance |
| Tuesday | Through the Static | *Clarity in the noise* | Media critique, debunking |
| Wednesday | Out From the Noise | *A moment to breathe* | Personal reflection |
| Thursday | 404: Hope Not Found | *The system worked as intended* | System failures, dark humor |
| Friday | Maddest Stuff | *Can you believe they're getting away with this?* | Political absurdity |
| Saturday | Everything's Fine | *Smiling while the room burns* | Ironic denial, corporate spin |
| Sunday | Last Warm Place | *Where the fire hasn't gone out* | Community, warmth |

### 8-Part Content Suite

When generating content, Zeon7 creates:

1. **Blog Post** (1200-2000 words) - Full article with citations
2. **Facebook Post** (500-750 words) - Condensed with link to blog
3. **Instagram Post** (350-500 words) - Visual storytelling caption
4. **X/Bluesky Post** (250-280 chars) - Sharp, pithy
5. **Threads Post** - Conversational, slightly longer
6. **Truth Social Post** (500-750 words) - Tailored for skeptical audience
7. **TikTok Script** - Markdown table with timestamps, visuals, voiceover
8. **Image Prompts** - For Nano Banana Pro model with overlay text

---

## ✅ Implementation Phases

### Phase 1: Foundation (Days 1-2)
- [x] Create design system CSS files
- [ ] Implement theme switcher
- [ ] Create icon library
- [ ] Set up base component styles

### Phase 2: Backend (Days 3-5)
- [ ] Run database migrations
- [ ] Create all service classes
- [ ] Create API endpoints
- [ ] Test with Postman/curl

### Phase 3: Admin Dashboard (Days 6-9)
- [ ] Create main dashboard with card grid
- [ ] Build knowledge manager page
- [ ] Build instruction editor
- [ ] Build lore manager
- [ ] Build content generator workflow
- [ ] Build API settings page

### Phase 4: Public Interface (Days 10-12)
- [ ] Create posts listing page
- [ ] Create individual post view
- [ ] Build chat widget component
- [ ] Integrate chat with API
- [ ] Add animations and polish

### Phase 5: Testing & Polish (Days 13-14)
- [ ] Write and run automated tests
- [ ] Perform manual testing checklist
- [ ] Fix bugs and refine UX
- [ ] Performance optimization
- [ ] Security audit

### Phase 6: Deployment (Day 15)
- [ ] Create deployment documentation
- [ ] Set up production environment
- [ ] Run migrations on production
- [ ] Seed initial data
- [ ] Monitor and iterate

**[Full Task Checklist →](file:///E:/Userdata/merri/.gemini/antigravity/brain/8d083a95-c81a-4c4e-b182-679fa1775fa8/task.md)**

---

## 🔐 Security Measures

- **CSRF Protection** - Token validation on all state-changing operations
- **Rate Limiting** - 10 req/min public, 100 req/min admin
- **Input Validation** - Sanitization and type checking
- **Prepared Statements** - No raw SQL queries
- **Session Management** - 30-minute timeout, secure cookies
- **File Upload Validation** - MIME type, extension whitelist, size limits

---

## 🎯 Success Criteria

- [ ] Admin dashboard displays 7 functional cards in modern card grid
- [ ] Light/dark mode toggle works smoothly with localStorage persistence
- [ ] Knowledge files can be uploaded, chunked, searched, and deleted
- [ ] API provider can be switched between Gemini and OpenRouter
- [ ] Content generation workflow produces all 8 parts of suite
- [ ] Public chat widget responds to messages with rate limiting
- [ ] "From the Noise" posts display with theme-based filtering
- [ ] Design matches inspiration: bold typography, spacious layouts, dotted aesthetic
- [ ] All forms have CSRF protection
- [ ] Mobile responsive layouts work on <640px width
- [ ] Page load times < 2 seconds for admin, < 1 second for public

---

## 📚 Knowledge Base Files

The project includes Zeon7's personality and context:

- **[Zeon7_Biography.md](file:///e:/Dev/Projects/self/knowledge/Zeon7_Biography.md)** - Complete backstory including far-future sci-fi arc
- **[Zeon7_ProfileSheet.md](file:///e:/Dev/Projects/self/knowledge/Zeon7_ProfileSheet.md)** - Quick reference for traits, appearance, skills
- **[Zeon7_Lore.md](file:///e:/Dev/Projects/self/knowledge/Zeon7_Lore.md)** - Accumulated memories and narrative arcs
- **[current-instructions.md](file:///e:/Dev/Projects/self/instructions/current-instructions.md)** - The AI prompt using CRISPE framework
- **[From The Noise – Channel Design & Maintenance.md](file:///e:/Dev/Projects/self/knowledge/From%20The%20Noise%20%E2%80%93%20Channel%20Design%20%26%20Maintenance.md)** - Brand guidelines

---

## 🚀 Next Steps

**Ready to start implementation?** The planning phase is complete with:

✅ Comprehensive design system based on inspiration images  
✅ Logo integration with dotted/pixel aesthetic  
✅ UI mockups for all major interfaces  
✅ Complete technical architecture  
✅ Database schema and service layer design  
✅ Security measures and verification plan  

**To begin Phase 1 (Foundation):**

1. Create CSS files with design system variables
2. Implement theme switcher JavaScript
3. Set up icon library
4. Build base component styles

**Estimated timeline:** 15 days total, ~2 days per phase

---

## 📞 Questions or Adjustments?

Before proceeding with implementation, please review:
- **[Implementation Plan](file:///E:/Userdata/merri/.gemini/antigravity/brain/8d083a95-c81a-4c4e-b182-679fa1775fa8/implementation_plan.md)** for detailed specifications
- UI mockups (embedded above) for design approval
- Task checklist for phase breakdown

Ready to build when you are! 🎨🤖
