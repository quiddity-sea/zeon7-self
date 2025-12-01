# Zeon7 Design Guide

**Version:** 1.0  
**Last Updated:** 2025-11-26  
**Design System:** Blended Professional + Zeon7 Brand Identity

---

## 📖 Table of Contents

1. [Design Philosophy](#design-philosophy)
2. [Color System](#color-system)
3. [Typography](#typography)
4. [Spacing & Layout](#spacing--layout)
5. [Components](#components)
6. [Icons](#icons)
7. [Shadows & Elevation](#shadows--elevation)
8. [Animations & Transitions](#animations--transitions)
9. [Themes](#themes)
10. [Accessibility](#accessibility)
11. [Best Practices](#best-practices)

---

## Design Philosophy

The Zeon7 design system blends **professional structure** with **bold brand identity**:

### Core Principles

- **Clarity First** - Clean layouts with generous whitespace
- **Signal Through Noise** - Red/orange accents draw attention to key actions
- **Bold Typography** - Uppercase headlines, clear hierarchy
- **Responsive Design** - Mobile-first approach
- **Accessibility** - WCAG 2.1 AA compliance minimum

### Brand Personality

- **Technical yet approachable** - Dotted pixel aesthetic meets modern UI
- **Signal-focused** - Cut through the noise with clear communication
- **Professional with edge** - Serious content, bold presentation

---

## Color System

### Grayscale Foundation

The grayscale palette provides the foundation for all interfaces:

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-white` | `#ffffff` | Pure white backgrounds |
| `--color-gray-50` | `#f9fafb` | Alternate backgrounds |
| `--color-gray-100` | `#f3f4f6` | Hover states, disabled |
| `--color-gray-200` | `#e5e7eb` | Borders, dividers |
| `--color-gray-300` | `#d1d5db` | Medium borders |
| `--color-gray-400` | `#9ca3af` | Secondary icons |
| `--color-gray-500` | `#6b7280` | Muted text, hints |
| `--color-gray-600` | `#4b5563` | Body text alternative |
| `--color-gray-700` | `#374151` | Primary body text |
| `--color-gray-800` | `#1f2937` | Dark backgrounds |
| `--color-gray-900` | `#111827` | Headlines, primary actions |

### Zeon7 Brand Accents

**Light Mode:**
- `--accent-primary`: `#D32F2F` (Signal Red) - Primary actions, links
- `--accent-secondary`: `#FF5722` (Bright Orange) - Highlights, secondary actions
- `--accent-hover`: `#B71C1C` (Dark Red) - Hover states

**Dark Mode:**
- `--accent-primary`: `#FF5722` (Bright Orange) - Better visibility on dark
- `--accent-secondary`: `#D32F2F` (Signal Red) - Inverted for contrast
- `--accent-hover`: `#FF6F43` (Light Orange) - Hover states

### Optional Accents

Use sparingly for specific contexts:

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-blue-accent` | `#0C3D5D` | Professional links, dark mode card icons |
| `--color-green-dark` | `#1F4F10` | Success states (optional) |
| `--color-green-light` | `#57A93E` | Success hover (optional) |

### Semantic Colors

Variables that adapt to light/dark mode:

```css
/* Backgrounds */
--bg-primary: /* white → black */
--bg-secondary: /* gray-50 → #0A0A0A */
--bg-card: /* white → #121212 */
--bg-hover: /* gray-100 → gray-800 */

/* Text */
--text-primary: /* gray-900 → gray-50 */
--text-secondary: /* gray-700 → gray-400 */
--text-muted: /* gray-500 → gray-500 */
--text-inverse: /* white → gray-900 */

/* Borders */
--border-subtle: /* gray-200 → #2A2A2A */
--border-medium: /* gray-300 → #404040 */
--border-strong: /* gray-900 → #606060 */
```

---

## Typography

### Font Families

**Montserrat** (Headline Font)
- **Usage:** H1-H6, navigation, buttons, tabs
- **Weights:** 300, 400, 500, 600, 700, 900
- **Style:** Clean, geometric sans-serif
- **Import:** `Google Fonts`

**Source Sans Pro** (Body Font)
- **Usage:** Body text, forms, paragraphs
- **Weights:** 300, 400, 600, 700
- **Style:** Readable, professional sans-serif
- **Import:** `Google Fonts`

**JetBrains Mono** (Monospace)
- **Usage:** Code blocks, technical content
- **Style:** Monospace with ligatures

**Inter** (Admin Interface)
- **Usage:** Admin dashboard, dense data displays
- **Style:** Highly legible, neutral sans-serif
- **Import:** `Google Fonts`

### Type Scale

| Element | Size (rem/px) | Weight | Transform | Line Height |
|---------|---------------|--------|-----------|-------------|
| **H1** | 4rem / 64px | 900 (black) | Uppercase | 1.25 |
| **H2** | 3rem / 48px | 500 (medium) | Uppercase | 1.25 |
| **H3** | 2rem / 32px | 500 (medium) | Uppercase | 1.25 |
| **H4** | 1.5rem / 24px | 600 (semibold) | Title Case | 1.25 |
| **H5** | 1.125rem / 18px | 500 (medium) | Title Case | 1.25 |
| **H6** | 1rem / 16px | 500 (medium) | Title Case | 1.25 |
| **Body (lg)** | 1.125rem / 18px | 400 (regular) | None | 1.6 |
| **Body (base)** | 1rem / 16px | 400 (regular) | None | 1.6 |
| **Body (sm)** | 0.875rem / 14px | 400 (regular) | None | 1.6 |
| **Body (xs)** | 0.75rem / 12px | 400 (regular) | None | 1.6 |

### Typography Rules

1. **H1-H3:** Always UPPERCASE with Montserrat
2. **H4-H6:** Title Case with Montserrat
3. **Body:** Source Sans Pro with 1.6 line-height for readability
4. **Letter Spacing:** 0.05em for uppercase text
5. **Hierarchy:** Always use proper heading hierarchy (no skipping)

### Text Utilities

```css
.text-xs { font-size: 0.75rem; }
.text-sm { font-size: 0.875rem; }
.text-base { font-size: 1rem; }
.text-lg { font-size: 1.125rem; }
.text-xl { font-size: 1.5rem; }
.text-2xl { font-size: 2rem; }
.text-3xl { font-size: 3rem; }
.text-4xl { font-size: 4rem; }
.text-5xl { font-size: 5rem; }

.font-normal { font-weight: 400; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.font-black { font-weight: 900; }
```

---

## Spacing & Layout

### Base Unit System

**8px base unit** for consistent spacing:

| Token | Value | Pixels | Usage |
|-------|-------|--------|-------|
| `--space-1` | 0.25rem | 4px | Micro spacing |
| `--space-2` | 0.5rem | 8px | Small gaps |
| `--space-3` | 0.75rem | 12px | Compact padding |
| `--space-4` | 1rem | 16px | Standard spacing |
| `--space-6` | 1.5rem | 24px | Medium spacing |
| `--space-8` | 2rem | 32px | Large spacing |
| `--space-12` | 3rem | 48px | Section spacing |
| `--space-16` | 4rem | 64px | Hero spacing |
| `--space-24` | 6rem | 96px | Extra large spacing |

### Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--radius-sm` | 4px | Small elements, code blocks |
| `--radius-md` | 8px | Inputs, form elements |
| `--radius-lg` | 12px | Cards, panels |
| `--radius-xl` | 16px | Modals, large cards |
| `--radius-full` | 9999px | Pills, badges, circular buttons |

### Layout Containers

```css
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.content-max {
  max-width: 800px; /* For reading content */
}

.section {
  padding: 4rem 0; /* Desktop */
  padding: 2rem 0; /* Mobile <640px */
}
```

### Grid System

```css
.grid-cols-1 { grid-template-columns: 1fr; }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }

/* Responsive breakpoints */
@media (max-width: 1024px) {
  .grid-cols-3 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .grid-cols-2, .grid-cols-3 { grid-template-columns: 1fr; }
}
```

---

## Components

### Buttons

**Design Specification:**
- **Font:** Montserrat
- **Size:** 0.75rem (12px)
- **Weight:** 500 (medium) for primary, **700 (bold) for secondary**
- **Padding:** 6px vertical, 18px horizontal
- **Border Radius:** 6px
- **Transform:** UPPERCASE
- **Letter Spacing:** 0.05em

**Variants:**

#### Primary Button
- **Background:** `--color-gray-900` (dark gray)
- **Text:** `--color-gray-50` (light)
- **Weight:** 500
- **Hover:** Background changes to `--color-blue-accent`

```html
<button class="btn btn-primary">Primary Button</button>
```

#### Secondary Button
- **Background:** `--accent-primary` (red/orange)
- **Text:** `--text-inverse` (white)
- **Weight:** 700 (bold for readability on colored background)
- **Hover:** Background darkens, slight lift effect

```html
<button class="btn btn-secondary">Secondary Button</button>
```

#### Ghost Button
- **Background:** Transparent
- **Text:** `--text-primary`
- **Hover:** Light background fill

```html
<button class="btn btn-ghost">Ghost Button</button>
```

**Sizes:**

```html
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary">Default</button>
<button class="btn btn-primary btn-lg">Large</button>
```

**States:**

```html
<button class="btn btn-primary" disabled>Disabled</button>
```

---

### Cards

Dashboard cards with hover effects:

```html
<div class="card">
  <svg class="card-icon"><!-- Icon --></svg>
  <h4 class="card-title">Card Title</h4>
  <p class="card-description">Description text</p>
</div>
```

**Specifications:**
- **Background:** `--bg-card`
- **Border Radius:** `--radius-lg` (12px)
- **Padding:** `--space-8` (32px)
- **Shadow:** Subtle elevation
- **Hover:** Lift effect with enhanced shadow

**Icon Colors:**
- Light mode: `--accent-primary` (red)
- Dark mode: `--color-blue-accent` (professional blue)

---

### Forms

#### Text Input

```html
<div class="form-group">
  <label class="form-label" for="example">Label</label>
  <input type="text" id="example" class="form-input" placeholder="Placeholder...">
  <span class="form-hint">Helpful hint text</span>
</div>
```

#### Textarea

```html
<div class="form-group">
  <label class="form-label" for="example">Label</label>
  <textarea id="example" class="form-textarea"></textarea>
</div>
```

#### Select Dropdown

```html
<div class="form-group">
  <label class="form-label" for="example">Label</label>
  <select id="example" class="form-select">
    <option>Option 1</option>
    <option>Option 2</option>
  </select>
</div>
```

#### Checkbox

```html
<label>
  <input type="checkbox" class="form-checkbox">
  <span class="text-sm">Checkbox label</span>
</label>
```

**Focus States:**
- Border color changes to `--accent-primary`
- 3px shadow in accent color at 10% opacity

---

### Tabs

Uppercase Montserrat navigation tabs:

```html
<div class="tabs">
  <button class="tab active">Monday</button>
  <button class="tab">Tuesday</button>
  <button class="tab">Wednesday</button>
</div>
```

**Specifications:**
- **Font:** Montserrat, 0.875rem (14px)
- **Weight:** 500 (medium)
- **Transform:** UPPERCASE
- **Active State:** Bottom border in accent color
- **Border:** 3px solid when active

---

### Badges

```html
<span class="badge badge-primary">Active</span>
<span class="badge badge-secondary">Draft</span>
```

**Specifications:**
- **Padding:** 4px 12px
- **Border Radius:** Full (pill shape)
- **Font Size:** 0.75rem (12px)
- **Weight:** 600 (semibold)
- **Transform:** UPPERCASE

---

### Loading States

#### Loading Dots

```html
<div class="loading-dots">
  <span></span>
  <span></span>
  <span></span>
</div>
```

Animated pulsing dots in accent color.

#### Spinner

```html
<div class="spinner"></div>
```

Circular rotating spinner.

---

### Modals

```html
<div class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Modal Title</h3>
      <button class="modal-close">×</button>
    </div>
    <!-- Modal content -->
  </div>
</div>
```

**Specifications:**
- **Overlay:** 50% black background
- **Max Width:** 600px
- **Max Height:** 90vh (scrollable)
- **Border Radius:** `--radius-xl` (16px)
- **Shadow:** Deep shadow for elevation
- **Z-Index:** 300

---

## Icons

### Style Guidelines

**Specifications:**
- **Stroke Width:** 2px
- **Caps/Joins:** Rounded
- **Style:** Geometric, minimalist
- **Size:** 24px × 24px default
- **Format:** SVG for scalability

### Icon Colors

- **Light Mode:** `--accent-primary` (red) for card icons
- **Dark Mode:** `--color-blue-accent` (blue) for card icons
- **Buttons:** Inherit button color
- **Navigation:** `--text-secondary` by default

### Usage

```html
<svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
  <path d="..."/>
</svg>
```

**Icon Sizes:**

```css
.icon-sm { width: 16px; height: 16px; }
.icon-md { width: 24px; height: 24px; }
.icon-lg { width: 32px; height: 32px; }
```

---

## Shadows & Elevation

### Shadow Tokens

| Token | Light Mode | Dark Mode | Usage |
|-------|------------|-----------|-------|
| `--shadow-card` | `0 1px 3px rgba(0,0,0,0.1)` | `0 4px 12px rgba(0,0,0,0.5)` | Default cards |
| `--shadow-card-hover` | `0 4px 6px rgba(0,0,0,0.1)` | `0 8px 24px rgba(0,0,0,0.7)` | Card hover state |
| `--shadow-modal` | `0 10px 25px rgba(0,0,0,0.15)` | `0 12px 48px rgba(0,0,0,0.8)` | Modals, overlays |

### Elevation Hierarchy

1. **Base (0)** - Flat elements on page background
2. **Raised (1)** - Cards, panels (shadow-card)
3. **Elevated (2)** - Hover states (shadow-card-hover)
4. **Overlay (3)** - Modals, dropdowns (shadow-modal)

---

## Animations & Transitions

### Timing Functions

| Token | Duration | Usage |
|-------|----------|-------|
| `--transition-fast` | 150ms ease | Hover states, color changes |
| `--transition-base` | 250ms ease | Standard interactions |
| `--transition-slow` | 400ms ease | Complex animations |

### Standard Transitions

```css
/* Applied to theme-switching elements */
transition: 
  background-color var(--transition-base),
  color var(--transition-base),
  border-color var(--transition-base);
```

### Animation Guidelines

- **Hover Effects:** Use fast transitions (150ms)
- **Theme Switching:** Use base transitions (250ms)
- **Loading States:** Continuous animations
- **Micro-interactions:** Keep subtle and smooth

### Reduced Motion

Honor user preferences:

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

## Themes

### Light Mode (Default)

- **Background:** Pure white (`#ffffff`)
- **Card Background:** White
- **Text:** Dark gray (`#111827`)
- **Accent:** Signal Red (`#D32F2F`)
- **Best for:** Daytime use, content-heavy pages

### Dark Mode

- **Background:** Pure black (`#000000`)
- **Card Background:** Dark gray (`#121212`)
- **Text:** Light gray (`#f9fafb`)
- **Accent:** Bright Orange (`#FF5722`)
- **Best for:** Nighttime use, reduced eye strain

### Theme Switching

Controlled by `data-theme` attribute on `<html>`:

```html
<html data-theme="light">  <!-- or "dark" -->
```

**localStorage Key:** `zeon7-theme`

**Implementation:**

```javascript
// See public/js/theme-switcher.js
const theme = localStorage.getItem('zeon7-theme') || 'light';
document.documentElement.setAttribute('data-theme', theme);
```

---

## Accessibility

### Color Contrast

All text meets **WCAG 2.1 AA** standards:

- **Primary text:** 7:1 contrast ratio minimum
- **Secondary text:** 4.5:1 contrast ratio minimum
- **Large text (18px+):** 3:1 contrast ratio minimum

### Focus States

All interactive elements have visible focus indicators:

- **Inputs:** Accent-colored border + 3px shadow
- **Buttons:** Outline or background change
- **Links:** Underline on focus

### Keyboard Navigation

- All interactive elements are keyboard accessible
- Logical tab order follows visual hierarchy
- Modal traps focus when open

### Screen Reader Support

- Semantic HTML elements used throughout
- ARIA labels on icon-only buttons
- Proper heading hierarchy (H1 → H2 → H3)

### Reduced Motion

Respect `prefers-reduced-motion` system setting by disabling animations.

---

## Best Practices

### Do's ✅

- **Use semantic HTML** - `<header>`, `<main>`, `<section>`, `<article>`
- **Follow type hierarchy** - H1 → H2 → H3, no skipping
- **Use CSS variables** - Always reference design tokens
- **Keep spacing consistent** - Use 8px base unit
- **Test both themes** - Ensure legibility in light and dark mode
- **Optimize for mobile** - Mobile-first responsive design
- **Use SVG icons** - Scalable and crisp at any size

### Don'ts ❌

- **Don't hardcode colors** - Use CSS variables
- **Don't skip heading levels** - Breaks accessibility
- **Don't use fixed pixel values** - Use rem/em for scalability
- **Don't ignore hover states** - All interactive elements need feedback
- **Don't forget focus states** - Critical for keyboard users
- **Don't nest cards deeply** - Keep layouts flat
- **Don't use low-contrast colors** - Check WCAG compliance

### Component Composition

**Good:**
```html
<div class="card">
  <h4 class="card-title">Title</h4>
  <p class="card-description">Description</p>
</div>
```

**Bad:**
```html
<div style="background: #fff; padding: 32px;">
  <h4 style="font-size: 24px;">Title</h4>
  <p style="color: gray;">Description</p>
</div>
```

### Responsive Design

Mobile breakpoints:

- **Small:** < 640px (mobile)
- **Medium:** 640px - 1024px (tablet)
- **Large:** > 1024px (desktop)

Always test on:
- iPhone SE (375px)
- iPad (768px)
- Desktop (1200px+)

---

## Resources

### CSS Files

- [variables.css](file:///e:/Dev/Projects/self/public/css/variables.css) - Design tokens
- [base.css](file:///e:/Dev/Projects/self/public/css/base.css) - Typography and utilities
- [components.css](file:///e:/Dev/Projects/self/public/css/components.css) - UI components

### Demo & Inspiration

- [design-demo.html](file:///e:/Dev/Projects/self/public/design-demo.html) - Interactive component showcase
- [design-inspiration-reference.md](file:///e:/Dev/Projects/self/docs/design-inspiration-reference.md) - Visual inspiration
- [logo-integration.md](file:///e:/Dev/Projects/self/docs/logo-integration.md) - Brand identity
- [ui-mockups.md](file:///e:/Dev/Projects/self/docs/ui-mockups.md) - Interface designs

### External Tools

- **Google Fonts:** [Montserrat](https://fonts.google.com/specimen/Montserrat) + [Source Sans Pro](https://fonts.google.com/specimen/Source+Sans+Pro)
- **Color Contrast Checker:** [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- **Icon Library:** Custom SVG icons (2px stroke, rounded)

---

**Last Updated:** 2025-11-26  
**Maintained by:** Zeon7 Team  
**Questions?** Refer to the master plan or implementation plan for additional context.
