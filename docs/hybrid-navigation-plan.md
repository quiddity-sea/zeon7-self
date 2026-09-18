# Implementation Plan: The Eye Keyboard Navigation & Custom UI

## Goal Description
Implement a hybrid map-control system for "The Eye". This includes:
1. **Custom On-Screen UI (Option 2):** Adding stylized "Reset View" and "Help" buttons that match The Eye's command center aesthetic, completely bypassing Cesium's default widgets.
2. **Space Game Keyboard Controls (Option 3):** Implementing a `WASD` keyboard flight system so the user can pilot the camera like a drone or satellite across the globe.

## User Review Required
No breaking changes, but the user should approve the keyboard mapping:
- **W / S**: Move Forward / Backward (Zoom in/out or Pan based on pitch)
- **A / D**: Pan Left / Right
- **Q / E**: Rotate Heading (Look Left / Right)
- **Shift / Space**: Adjust Altitude (Move Up / Down)

## Proposed Changes

### `the-eye/index.php`
- **[MODIFY]** Integrate the new controls into the unified HUD layout. Under the existing `<div id="sensor-panel">` (Top-Right), add a new panel:
  ```html
  <div id="navigation-panel" class="glass-panel">
      <div class="panel-header">NAVIGATION</div>
      <div class="nav-actions">
          <button id="btn-reset-view" class="eye-btn-sm">RESET VIEW</button>
          <button id="btn-control-help" class="eye-btn-sm">CONTROLS</button>
      </div>
  </div>
  ```
- **[MODIFY]** Add a hidden modal `<div id="eye-help-modal" class="glass-panel hidden">` in the center of the screen containing a sleek layout explaining the Mouse and Keyboard controls.

### `the-eye/css/eye.css`
- **[MODIFY]** Add positioning and styling for `#navigation-panel` so it stacks neatly below the `#sensor-panel` on the right side, maintaining the existing margin and glassmorphism spacing.
- **[MODIFY]** Add styling for the `#eye-help-modal` to center it on the screen with a frosted glass backdrop, matching the existing `.glass-panel` aesthetic.

### `the-eye/js/globe.js`
- **[MODIFY]** Create a `setupKeyboardControls()` method inside the `EyeGlobe` class.
- **[MODIFY]** Implement a flag-based tracking system:
  ```javascript
  this.keys = { forward: false, backward: false, left: false, right: false, up: false, down: false, rotateLeft: false, rotateRight: false };
  ```
- **[MODIFY]** Add `keydown` and `keyup` event listeners to the `document` to toggle these flags based on `e.code` (e.g., `KeyW`, `KeyA`, `Space`, `ShiftLeft`).
- **[MODIFY]** Bind an event to `this.viewer.clock.onTick.addEventListener`. Inside this tick loop, apply movement to `this.viewer.camera`:
  - Calculate `moveRate` dynamically based on `camera.positionCartographic.height` (so you fly faster when zoomed out).
  - Use `camera.moveForward(rate)`, `camera.moveRight(rate)`, `camera.lookLeft(rate)`, etc. based on the active flags.
- **[MODIFY]** Expose a `bindUI()` method to attach the `reset()` function to the `#btn-reset-view` button, and the modal toggle to `#btn-control-help`.

## Verification Plan

### Manual Verification
1. Open The Eye in the browser.
2. **Test Custom UI:** Click the new "Home/Reset" button and verify the camera flies smoothly back to the default zoomed-out view. Click the "?" button to verify the controls overlay appears.
3. **Test Keyboard Flight:** Hold `W/A/S/D` and verify the globe pans. Hold `Shift/Space` and verify altitude changes. Hold `Q/E` and verify the camera rotates.
4. **Test Dynamic Speed:** Ensure the keyboard panning speed feels appropriately fast when zoomed out in space, and slow/precise when zoomed in close to the ground.
