# Hardware-Accelerated Pure GLSL WebGL Fragment Shaders
## Technical Architecture & Implementation Specification

**Platform:** FBOX: SELF (`self.foreverbox.co.uk`) & The Eye (`eye.foreverbox.co.uk`)  
**Repository:** `quiddity-sea/zeon7-self`  
**Version:** 1.0 (Production)  
**Date:** September 2026  
**Author:** Zeon7 Core Engineering / Quiddity Ltd  

---

## 1. Architectural Motivation & Design Philosophy

Traditional web animation pipelines often rely on CPU-heavy JavaScript libraries (e.g., GSAP, anime.js) or heavyweight 3D scene-graph frameworks (e.g., Three.js, Babylon.js, Pixi.js). While GSAP 3.12 is leveraged effectively across the Zeon7 administrative panels for DOM transitions, complex mathematical backgrounds, dynamic plasma fields, and multi-spectrum surveillance optics demand **zero-CPU GPU acceleration**.

### Key Tenets
1. **Zero External Dependencies:** 100% vanilla JavaScript running directly on native browser WebGL / WebGL2 contexts. No npm, no node runtime, and no external bundlers required.
2. **Deterministic 60–144 FPS Performance:** All pixel mathematics (coordinate transforms, sine-wave scanlines, Voronoi noise, and screen-space derivative edge detection) execute concurrently across thousands of GPU cores.
3. **Aggressive Power & Battery Conservation:** All background and element render loops automatically suspend execution via `IntersectionObserver` when scrolled out of view or hidden.
4. **Universal Accessibility:** Built-in compliance with `prefers-reduced-motion: reduce`, automatically disabling animated sweeps and high-frequency effects for motion-sensitive users.

---

## 2. Reusable WebGL Engine (`js/glsl-core.js`)

The `GLSLCanvas` class provides a unified runner for compiling arbitrary vertex and fragment shaders onto any HTML `<canvas>`.

### Geometry & Pipeline
A single static array buffer defining a clip-space quad (two triangles covering `[-1, -1]` to `[1, 1]`):
```javascript
const quad = new Float32Array([
    -1.0, -1.0,   1.0, -1.0,  -1.0,  1.0,
    -1.0,  1.0,   1.0, -1.0,   1.0,  1.0
]);
```

### Standard Uniforms
Every shader managed by `GLSLCanvas` automatically receives:

| Uniform | Type | Description |
| :--- | :---: | :--- |
| `u_time` | `float` | Elapsed execution time in seconds (`(now - startTime) * 0.001`). |
| `u_resolution` | `vec2` | Physical pixel dimensions of the canvas (`width, height`), adjusted for DPI. |
| `u_mouse` | `vec2` | Normalized, smoothly interpolated cursor coordinates (`[0.0, 1.0]`). |
| `u_intensity` | `float` | Dynamic gain multiplier used to scale effects during state changes. |
| `u_accent` | `vec3` | Active agent RGB theme color (`[r, g, b]` floats normalized 0.0–1.0). |
| `u_image` / `u_texture` | `sampler2D` | Bound 2D image texture for post-process filtering. |

### Lifecycle Hooks
* `start()`: Initiates the `requestAnimationFrame` render loop.
* `stop()`: Halts render frames immediately.
* `setIntensity(val)`: Smoothly adjusts shader gain.
* `setUniform(name, type, ...values)`: Binds custom uniforms (`1f`, `2f`, `3f`, `1i`).
* `destroy()`: Disconnects observers, deletes buffers, and frees GPU program memory.

---

## 3. Shader Registry & Implementations

```
┌────────────────────────────────────────────────────────┐
│              LIGHTWEIGHT GLSL CORE ENGINE              │
│                  (js/glsl-core.js)                     │
└───────┬──────────────┬──────────────┬──────────────┬───┘
        │              │              │              │
        ▼              ▼              ▼              ▼
 ┌─────────────┐┌─────────────┐┌─────────────┐┌─────────────┐
 │ Component 1 ││ Component 2 ││ Component 3 ││ Component 4 │
 │ Agent Core  ││ Cyber Grid  ││ Holographic ││ Vision Mode │
 │  Thinking   ││ Background  ││   Glitch    ││ Multi-Optics│
 │ Visualizer  ││  (Landing)  ││ Transitions ││ (Admin)     │
 └─────────────┘└─────────────┘└─────────────┘└─────────────┘
```

---

### Component 1: Agent Cognitive Core Orb
* **File:** [`js/chat-widget.js`](file:///H:/var/www/self/js/chat-widget.js)
* **Canvas Element:** `#agent-core-canvas` (24x24px retina canvas in chat header).
* **Behavior:**
  - **Idle State (`intensity = 1.0`):** Renders a calm, breathing harmonic plasma sphere pulsing in the active agent's theme accent (Cyan `#22d3ee` for Zeon7, Purple for Wolf, Amber for Leon).
  - **Reasoning State (`intensity = 1.6`):** Toggled when `[ THINKING ON ]` mode is active.
  - **Transmitting State (`intensity = 3.5`):** Ramps rotational frequency and coronal energy flares dynamically while the model is computing and streaming tokens.

#### GLSL Fragment Logic:
```glsl
vec2 uv = (gl_FragCoord.xy * 2.0 - u_resolution) / min(u_resolution.x, u_resolution.y);
float d = length(uv);

// Rotating harmonic plasma
float angle = atan(uv.y, uv.x) + u_time * (1.6 * u_intensity);
float wave = sin(angle * 3.0 + u_time * 2.5) * 0.12;
wave += sin(angle * 6.0 - u_time * 4.0) * (0.06 * u_intensity);

float r = 0.44 + wave + sin(u_time * 3.5 * u_intensity) * 0.04;
float core = smoothstep(r + 0.08, r - 0.08, d);
float glow = 0.09 / (max(d - r * 0.6, 0.01) + 0.02) * (0.8 + 0.5 * u_intensity);

vec3 col = mix(u_accent * 0.6, vec3(1.0), core * 0.85);
col += u_accent * glow;

float alpha = clamp(core + glow * 0.75, 0.0, 1.0);
if (d > 0.98) alpha = 0.0;
gl_FragColor = vec4(col, alpha);
```

---

### Component 2: Full-Screen 3D Cybernetic Perspective Grid
* **File:** [`js/cyber-grid.js`](file:///H:/var/www/self/js/cyber-grid.js)
* **Canvas Element:** Fixed background `#cyber-bg-canvas` (`z-index: -1`, `pointer-events: none`).
* **Behavior:**
  - Projects a 3D perspective cyber-grid receding toward a neon-lit horizon flare.
  - Uses GPU screen-space derivatives (`fwidth(gridCoord)`) to ensure anti-aliased grid lines that do not shimmer at distance.
  - Features smooth mouse parallax that tilts the camera pitch and lateral position as the user explores the page.
  - Applies exponential depth fog (`exp(-depth * 0.09)`) to ensure complete readability of foreground content.

---

### Component 3: Holographic Glitch & Transition Overlay
* **File:** [`js/hud-glitch.js`](file:///H:/var/www/self/js/hud-glitch.js)
* **Canvas Element:** Fixed overlay `#hud-glitch-canvas` (`z-index: 99999999`, `pointer-events: none`).
* **Controller:** `window.HUDGlitch.trigger(durationMs = 350, intensity = 1.0)`
* **Behavior:**
  - Executed automatically during `ZeonAnimations.playBootSequence()` on page load and triggerable on tab switches or admin actions.
  - Generates horizontal raster slice displacements, CRT scanline bands, chromatic RGB separation, and cyan/magenta static discharge arcs.
  - Stays hidden (`display: none; opacity: 0;`) when idle, generating 0 draw calls.

---

### Component 4: Vision Studio Multi-Spectrum Optics Inspector
* **Files:** [`admin/vision.php`](file:///H:/var/www/self/admin/vision.php), [`admin/js/vision-optics.js`](file:///H:/var/www/self/admin/js/vision-optics.js)
* **Canvas Element:** `#vision-glsl-canvas` (720x450px viewport).
* **Behavior:**
  - Evaluates uploaded or queued surveillance imagery under 6 real-time fragment shader spectra:
    1. **`NORMAL`:** Raw optical feed.
    2. **`CRT`:** Tactical monitor with raster scanlines, green phosphor tint, and curved barrel vignette.
    3. **`NVG`:** Night Vision Goggles with high-gain light amplification and dynamic analog sensor grain noise.
    4. **`FLIR`:** Forward-Looking Infrared mapping luminance into the Ironbow thermal heat spectrum (deep blue → warm orange → white-hot).
    5. **`NOIR`:** High-contrast desaturated monochrome for structural and runway reconnaissance.
    6. **`SNOW`:** Polar terrain relief using high-key whiteout and contour bands.
  - Equipped with real-time `GAIN` slider (`0.5x` to `3.0x`) and file upload support.

---

## 4. Verification & Testing Standards

All GLSL shader components must be verified against:
1. **Syntax Validation:** Compile shaders cleanly with `getShaderParameter(s, COMPILE_STATUS)`.
2. **Context Fallback:** Graceful degradation if WebGL is unavailable or disabled.
3. **Accessibility:** Verify automatic halt when `window.matchMedia('(prefers-reduced-motion: reduce)')` is true.
4. **Memory Profiling:** Ensure textures and buffers are freed upon DOM disposal.
