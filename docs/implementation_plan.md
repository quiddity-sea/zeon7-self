# Phase 3: Admin Dashboard Implementation Plan

## Goal
Build a responsive, modern Admin Dashboard to manage the Zeon7 platform. This includes a secure authentication system, a main dashboard overview, and dedicated management pages for Knowledge, Instructions, Lore, and Posts.

## User Review Required
> [!IMPORTANT]
> **Authentication**: I will implement a simple session-based authentication using an `ADMIN_PASSWORD` stored in your `.env` file. This avoids the need for a users table for a single-user system.

## Proposed Changes

### 1. Authentication System (Completed)
Secure the admin area to prevent unauthorized access.

> [!NOTE]
> **Restructuring**: To align with the Apache configuration (`DocumentRoot /public`), the `admin` and `api` directories have been moved into `public`.

#### [NEW] [src/services/AuthService.php](file:///e:/Dev/Projects/zeon7/self/src/services/AuthService.php)
- `login(string $password): bool` - Verifies password against `.env` and starts session.
- `logout(): void` - Destroys session.
- `isAuthenticated(): bool` - Checks if session is active.

#### [NEW] [src/middleware/AuthMiddleware.php](file:///e:/Dev/Projects/zeon7/self/src/middleware/AuthMiddleware.php)
- `handle()` - Redirects to `/admin/login.html` if not authenticated.

#### [NEW] [api/auth/login.php](file:///e:/Dev/Projects/zeon7/self/api/auth/login.php)
- POST endpoint for login.

#### [NEW] [api/auth/logout.php](file:///e:/Dev/Projects/zeon7/self/api/auth/logout.php)
- POST endpoint for logout.

#### [NEW] [api/auth/check.php](file:///e:/Dev/Projects/zeon7/self/api/auth/check.php)
- GET endpoint to check auth status (for frontend).

---

### 2. Admin UI Structure (Completed)
Modern, dark-themed dashboard using Vanilla CSS and JS.

#### [NEW] [admin/index.html](file:///e:/Dev/Projects/zeon7/self/admin/index.html)
- Main entry point.
- Sidebar navigation.
- Dashboard widgets (Quick stats: Total Posts, Knowledge Docs, API Usage).

#### [NEW] [admin/login.html](file:///e:/Dev/Projects/zeon7/self/admin/login.html)
- Simple, secure login page.

#### [NEW] [admin/css/style.css](file:///e:/Dev/Projects/zeon7/self/admin/css/style.css)
- Dark mode variables.
- Layout styles (Sidebar + Content).
- Component styles (Cards, Tables, Forms, Buttons).

#### [NEW] [admin/js/app.js](file:///e:/Dev/Projects/zeon7/self/admin/js/app.js)
- Global state management.
- API client wrapper (fetch with auth checks).
- Navigation handling.

---


#### [NEW] [admin/instructions.html](file:///e:/Dev/Projects/zeon7/self/admin/instructions.html)
- **Current Instruction**: Display current system instruction.
- **Editor**: Textarea to modify instruction.
- **History**: List of previous versions with timestamps.
- **Actions**: Save New Version.

#### [MODIFY] [admin/js/app.js](file:///e:/Dev/Projects/zeon7/self/admin/js/app.js)
- Add `loadInstruction()`: Fetch from `/api/instruction/current.php`.
- Add `saveInstruction()`: POST to `/api/instruction/create.php`.
- Add `loadInstructionHistory()`: Fetch from `/api/instruction/versions.php`.

---

### 4. Lore Manager (Completed)
#### [NEW] [admin/lore.html](file:///e:/Dev/Projects/zeon7/self/admin/lore.html)
- List, Add, Edit, Delete lore entries.
#### [NEW] [admin/js/lore.js](file:///e:/Dev/Projects/zeon7/self/admin/js/lore.js)
- Frontend logic for lore management.

### 5. Posts Manager (Completed)
#### [NEW] [admin/posts.html](file:///e:/Dev/Projects/zeon7/self/admin/posts.html)
- List of posts with status.
#### [NEW] [admin/post-editor.html](file:///e:/Dev/Projects/zeon7/self/admin/post-editor.html)
- Markdown editor with preview.
#### [NEW] [admin/js/posts.js](file:///e:/Dev/Projects/zeon7/self/admin/js/posts.js)
- Frontend logic for posts.

### 6. Generation Workflow (Completed)
#### [NEW] [admin/generate.html](file:///e:/Dev/Projects/zeon7/self/admin/generate.html)
- Input news URL and theme.
- Generate Blog, Twitter, LinkedIn content.
#### [NEW] [admin/js/generate.js](file:///e:/Dev/Projects/zeon7/self/admin/js/generate.js)
- Frontend logic for generation.

### 7. Settings (Completed)
#### [NEW] [admin/settings.html](file:///e:/Dev/Projects/zeon7/self/admin/settings.html)
- Configure AI Provider, Model, API Key.
#### [NEW] [admin/js/settings.js](file:///e:/Dev/Projects/zeon7/self/admin/js/settings.js)
- Frontend logic for settings.

### 8. Public Interface (Phase 4)
#### [NEW] [public/index.html](file:///e:/Dev/Projects/zeon7/self/public/index.html)
- **Hero**: Branding and introduction.
- **Latest News**: Grid of 3 most recent published posts.
- **Chat Widget**: Floating chat button.

#### [NEW] [public/blog.html](file:///e:/Dev/Projects/zeon7/self/public/blog.html)
- **List**: All published posts with pagination (or load more).

#### [NEW] [public/post.html](file:///e:/Dev/Projects/zeon7/self/public/post.html)
- **View**: Single post content (Markdown rendered).

#### [NEW] [public/js/public.js](file:///e:/Dev/Projects/zeon7/self/public/js/public.js)
- Shared logic for fetching posts and rendering Markdown.

#### [NEW] [public/js/chat-widget.js](file:///e:/Dev/Projects/zeon7/self/public/js/chat-widget.js)
- Floating chat UI and API integration.

## Verification Plan

### Automated Tests
- Test Auth API (Login success/fail, Logout).
- Test Middleware (Access protected route without session).

### Manual Verification
1. **Login Flow**:
   - Try accessing `/admin/index.html` -> Should redirect to login.
   - Enter wrong password -> Show error.
   - Enter correct password -> Redirect to dashboard.
2. **Dashboard**:
   - Verify stats load correctly.
3. **Knowledge**:
   - Upload a file -> Verify it appears in list.
   - Delete a file -> Verify removal.
4. **Instructions**:
   - View current instruction -> Verify it loads.
   - Edit and Save -> Verify new version is created and displayed.
   - Check History -> Verify new version appears in list.
5. **Lore**:
   - Add new lore -> Verify persistence.
6. **Posts**:
   - Create post -> Verify in list.
   - Publish post -> Verify status change.
7. **Generation**:
   - Run generation -> Verify content output.
8. **Public Site**:
   - Visit `/index.html` -> Check hero and latest posts.
   - Click post -> Check `/post.html` content.
   - Use Chat -> Verify AI response.

## Implementation Order
1. **Auth System** (Backend + Login Page)
2. **Dashboard Skeleton** (Layout + CSS)
3. **Knowledge Manager**
4. **Instruction Editor**
5. **Lore Manager**
6. **Posts Manager**
7. **Generation Workflow**

