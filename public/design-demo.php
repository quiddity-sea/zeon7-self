<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zeon7 - Blended Design System Demo</title>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;900&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <!-- Design System CSS -->
  <link rel="stylesheet" href="css/variables.css">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/components.css">
</head>
<body>
  
  <!-- Header with Theme Toggle -->
  <header style="padding: var(--space-6); background-color: var(--bg-card); border-bottom: 1px solid var(--border-subtle);">
    <div class="container flex justify-between items-center">
      <div>
        <h1 class="m-0" style="font-size: var(--text-2xl);">ZEON7</h1>
        <p class="text-sm text-secondary m-0">Blended Design System</p>
      </div>
      <button 
        class="btn btn-ghost btn-sm" 
        data-theme-toggle 
        aria-label="Toggle theme"
        style="padding: var(--space-2);">
        <span data-theme-icon>🌙</span>
      </button>
    </div>
  </header>

  <main class="container section">
    
    <!-- Typography Section -->
    <section class="mb-8">
      <h2>Typography System</h2>
      
      <!-- Font Families -->
      <div class="grid grid-cols-2 gap-6 mb-8">
        <div class="card">
          <p class="text-xs text-muted mb-2">HEADLINE FONT</p>
          <p style="font-family: var(--font-headline); font-size: var(--text-2xl); font-weight: var(--font-bold); margin: 0;">Montserrat</p>
          <p class="text-xs text-secondary mt-2 mb-0">Used for: H1-H6, navigation, buttons</p>
          <p class="text-xs text-muted mb-0">Weights: 300, 400, 500, 600, 700, 900</p>
        </div>
        <div class="card">
          <p class="text-xs text-muted mb-2">BODY FONT</p>
          <p style="font-family: var(--font-body); font-size: var(--text-lg); margin: 0;">Source Sans Pro</p>
          <p class="text-xs text-secondary mt-2 mb-0">Used for: Body text, forms, paragraphs</p>
          <p class="text-xs text-muted mb-0">Weights: 300, 400, 600, 700</p>
        </div>
      </div>

      <!-- Type Scale -->
      <h1>H1 Heading - Uppercase Bold</h1>
      <h2>H2 Heading - Uppercase Medium</h2>
      <h3>H3 Heading - Uppercase Medium</h3>
      <h4>H4 Heading - Title Case Semibold</h4>
      <p class="text-lg">Large body text for lead paragraphs and introductions (18px).</p>
      <p>Regular body text with comfortable line height for reading. This is the standard paragraph style using Source Sans Pro at 16px with 1.6 line-height for optimal readability.</p>
      <p class="text-sm text-secondary">Small secondary text for captions, metadata, and supporting information (14px).</p>
    </section>

    <!-- Cards Section -->
    <section class="mb-8">
      <h2>Dashboard Cards</h2>
      <div class="grid grid-cols-3 gap-6">
        
        <div class="card">
          <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19V6.2C4 5.0799 4 4.51984 4.21799 4.09202C4.40973 3.71569 4.71569 3.40973 5.09202 3.21799C5.51984 3 6.0799 3 7.2 3H16.8C17.9201 3 18.4802 3 18.908 3.21799C19.2843 3.40973 19.5903 3.71569 19.782 4.09202C20 4.51984 20 5.0799 20 6.2V17H6C4.89543 17 4 17.8954 4 19ZM4 19C4 20.1046 4.89543 21 6 21H20M9 7H15M9 11H15"/>
          </svg>
          <h4 class="card-title">Knowledge Manager</h4>
          <p class="card-description">Upload and manage markdown knowledge files</p>
        </div>

        <div class="card">
          <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"  stroke-width="2">
            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z"/>
            <path d="M14 2V8H20"/>
            <path d="M16 13H8"/>
            <path d="M16 17H8"/>
            <path d="M10 9H8"/>
          </svg>
          <h4 class="card-title">Content Generator</h4>
          <p class="card-description">AI-powered content creation</p>
        </div>

        <div class="card">
          <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"/>
            <path d="M12 1v6m0 6v6M17.66 3.87l-3.45 3.45m-3.66 3.66l-3.45 3.45M23 12h-6m-6 0H1M20.13 6.34l-3.45 3.45m-3.66 3.66l-3.45 3.45"/>
          </svg>
          <h4 class="card-title">API Settings</h4>
          <p class="card-description">Configure AI providers</p>
        </div>

      </div>
    </section>

    <!-- Buttons Section -->
    <section class="mb-8">
      <h2>Buttons (Design-Guide.md Specs)</h2>
      <p class="text-sm text-secondary mb-4">0.75rem size, Montserrat font, uppercase, 500 weight</p>
      <p class="text-xs text-muted mb-4">PRIMARY = Dark Gray | SECONDARY = Red/Orange</p>
      <div class="flex gap-4 flex-wrap">
        <button class="btn btn-primary">Primary Button</button>
        <button class="btn btn-secondary">Secondary Button</button>
        <button class="btn btn-ghost">Ghost Button</button>
        <button class="btn btn-primary btn-sm">Small</button>
        <button class="btn btn-primary btn-lg">Large</button>
        <button class="btn btn-primary" disabled>Disabled</button>
      </div>
    </section>

    <!-- Forms Section -->
    <section class="mb-8">
      <h2>Form Elements</h2>
      <div style="max-width: 500px;">
        <div class="form-group">
          <label class="form-label" for="demo-input">Text Input</label>
          <input type="text" id="demo-input" class="form-input" placeholder="Enter text...">
          <span class="form-hint">Helpful hint text</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="demo-textarea">Textarea</label>
          <textarea id="demo-textarea" class="form-textarea" placeholder="Enter long text..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label" for="demo-select">Select</label>
          <select id="demo-select" class="form-select">
            <option>Choose provider...</option>
            <option>Google Gemini</option>
            <option>OpenRouter</option>
          </select>
        </div>

        <label>
          <input type="checkbox" class="form-checkbox">
          <span class="text-sm">I agree to terms</span>
        </label>
      </div>
    </section>

    <!-- Badges & Loading -->
    <section class="mb-8">
      <h2>Badges & Loading States</h2>
      <div class="flex gap-4 items-center mb-6">
        <span class="badge badge-primary">Active</span>
        <span class="badge badge-secondary">Draft</span>
      </div>
      <div class="flex gap-8 items-center">
        <div class="loading-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <div class="spinner"></div>
      </div>
    </section>

    <!-- Tabs -->
    <section class="mb-8">
      <h2>Tabs (Uppercase Montserrat)</h2>
      <div class="tabs">
        <button class="tab active">Monday</button>
        <button class="tab">Tuesday</button>
        <button class="tab">Wednesday</button>
      </div>
    </section>

    <!-- Complete Color System -->
    <section class="mb-8">
      <h2>Blended Color System</h2>
      
      <h3>Grayscale (design-guide.md)</h3>
      <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="card">
          <div style="height: 60px; background: var(--color-gray-900); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">gray-900</p>
          <p class="text-xs text-secondary m-0">#111827</p>
          <p class="text-xs text-muted mt-1 mb-0">Headlines, primary actions</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--color-gray-700); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">gray-700</p>
          <p class="text-xs text-secondary m-0">#374151</p>
          <p class="text-xs text-muted mt-1 mb-0">Primary body text</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--color-gray-500); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">gray-500</p>
          <p class="text-xs text-secondary m-0">#6b7280</p>
          <p class="text-xs text-muted mt-1 mb-0">Muted content, hints</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--color-gray-200); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">gray-200</p>
          <p class="text-xs text-secondary m-0">#e5e7eb</p>
          <p class="text-xs text-muted mt-1 mb-0">Borders, dividers</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--color-gray-100); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">gray-100</p>
          <p class="text-xs text-secondary m-0">#f3f4f6</p>
          <p class="text-xs text-muted mt-1 mb-0">Hover states, disabled</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--color-gray-50); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">gray-50</p>
          <p class="text-xs text-secondary m-0">#f9fafb</p>
          <p class="text-xs text-muted mt-1 mb-0">Alternate backgrounds</p>
        </div>
      </div>

      <h3>Zeon7 Brand Accents</h3>
      <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="card">
          <div style="height: 60px; background: var(--accent-primary); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">accent-primary</p>
          <p class="text-xs text-secondary m-0">#D32F2F (Light) / #FF5722 (Dark)</p>
          <p class="text-xs text-muted mt-1 mb-0">Signal Red / Bright Orange</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--accent-secondary); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">accent-secondary</p>
          <p class="text-xs text-secondary m-0">#FF5722 (Light) / #D32F2F (Dark)</p>
          <p class="text-xs text-muted mt-1 mb-0">Bright Orange / Signal Red (inverted)</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--accent-hover); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">accent-hover</p>
          <p class="text-xs text-secondary m-0">#B71C1C (Light) / #FF6F43 (Dark)</p>
          <p class="text-xs text-muted mt-1 mb-0">Hover states for accented elements</p>
        </div>
      </div>

      <h3>Optional Accents (design-guide.md)</h3>
      <div class="grid grid-cols-3 gap-4">
        <div class="card">
          <div style="height: 60px; background: var(--color-blue-accent); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">blue-accent</p>
          <p class="text-xs text-secondary m-0">#0C3D5D</p>
          <p class="text-xs text-muted mt-1 mb-0">Professional blue for links</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--color-green-dark); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">green-dark</p>
          <p class="text-xs text-secondary m-0">#1F4F10</p>
          <p class="text-xs text-muted mt-1 mb-0">Action buttons (optional)</p>
        </div>
        <div class="card">
          <div style="height: 60px; background: var(--color-green-light); border-radius: var(--radius-md); margin-bottom: var(--space-2);"></div>
          <p class="text-sm font-semibold m-0">green-light</p>
          <p class="text-xs text-secondary m-0">#57A93E</p>
          <p class="text-xs text-muted mt-1 mb-0">Action hover (optional)</p>
        </div>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer style="padding: var(--space-8); background-color: var(--bg-card); border-top: 1px solid var(--border-subtle); margin-top: var(--space-16);">
    <div class="container text-center">
      <p class="text-sm text-secondary m-0">Zeon7 Blended Design System • Montserrat + Source Sans Pro • Light/Dark Mode ✓</p>
    </div>
  </footer>

  <!-- Theme Switcher Script -->
  <script src="js/theme-switcher.js"></script>

</body>
</html>
