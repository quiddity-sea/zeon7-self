/**
 * The Eye — Main Application Entry Point
 * Initialises the globe, layers, controls, and agent integration.
 */
import { EyeGlobe } from './globe.js';
import { Tracking } from './controls/tracking.js';
import { Sensors } from './controls/sensors.js';
import { Hud } from './controls/hud.js';
import { Cockpit } from './controls/cockpit.js';
import { Panels } from './ui/panels.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Initialise globe
    window.eyeGlobe = new EyeGlobe('cesiumContainer', window.EYE_CONFIG.cesiumToken, window.EYE_CONFIG.cartoKey);

    // 2. Initialise controls
    window.tracking = new Tracking(window.eyeGlobe);
    window.sensors = new Sensors(window.eyeGlobe);
    window.hud = new Hud(window.eyeGlobe, window.tracking);
    window.cockpit = new Cockpit(window.eyeGlobe, window.tracking);
    window.panels = new Panels(window.eyeGlobe);

    // 3. Agent integration (authenticated users only)
    if (window.EYE_CONFIG.isAuth) {
        const { AgentBar } = await import('./agent/agent-bar.js');
        const { EyeCommander } = await import('./agent/eye-commander.js');

        window.agentBar = new AgentBar();
        window.eyeCommander = new EyeCommander(window.eyeGlobe);

        // Connect agent bar to commander
        window.agentBar.onCommand = (reply, eyeCommand) => {
            window.eyeCommander.execute(eyeCommand);
        };

        // Annotations (auth users only — persistent)
        const { Annotations } = await import('./controls/annotations.js');
        window.annotations = new Annotations(window.eyeGlobe);
    }

    // 4. HUD refresh loop (every second)
    setInterval(() => window.hud.update(), 1000);

    // 5. Keyboard shortcut: G for detection overlay toggle
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT') return;
        if (e.key === 'g' || e.key === 'G') {
            document.body.classList.toggle('detection-active');
        }
        if (e.key === 'Escape') {
            // Reset tracking and cockpit
            if (window.cockpit.active) window.cockpit.exit();
            else window.tracking.untrack();
        }
    });

    console.log('[Eye] Initialised. Session:', window.EYE_CONFIG.sessionToken.substring(0, 8) + '...');
});
