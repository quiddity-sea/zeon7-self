/**
 * EyeCommander — parses eye_command JSON from agent responses
 * and executes them on the EyeGlobe instance.
 */
export class EyeCommander {
    constructor(globe) {
        this.globe = globe;
    }

    /**
     * Execute an eye_command object.
     * @param {object} cmd — { action: string, params: object }
     */
    execute(cmd) {
        if (!cmd || !cmd.action) return;

        const action = cmd.action;
        const params = cmd.params || {};

        switch (action) {
            case 'fly_to':
                this.globe.flyTo(params.lon, params.lat, params.alt || 50000);
                break;

            case 'track':
                if (params.entity_id && params.layer) {
                    this.globe.track(params.entity_id, params.layer);
                }
                break;

            case 'toggle_layer':
                if (params.layer) {
                    // If specifying visible: true/false, handle accordingly
                    if (params.visible !== undefined) {
                        const isActive = this.globe.activeLayers.has(params.layer);
                        if (params.visible && !isActive) this.globe.toggleLayer(params.layer);
                        if (!params.visible && isActive) this.globe.toggleLayer(params.layer);
                    } else {
                        this.globe.toggleLayer(params.layer);
                    }
                    // Update panel button state
                    const btn = document.querySelector(`.layer-toggle[data-layer="${params.layer}"]`);
                    if (btn) btn.classList.toggle('active', this.globe.activeLayers.has(params.layer));
                }
                break;

            case 'set_style':
                if (params.style) {
                    this.globe.setStyle(params.style);
                }
                break;

            case 'annotate':
                if (params.type && params.geometry) {
                    this.globe.annotate(params.type, params.geometry, params.label || '');
                }
                break;

            case 'save_view':
                // Handled server-side via command.php
                break;

            case 'reset':
                this.globe.reset();
                // Clear all active buttons
                document.querySelectorAll('.layer-toggle.active').forEach(b => b.classList.remove('active'));
                break;

            case 'query':
                // No visual change — data returned as text in the reply
                break;

            default:
                console.warn(`[Eye] Unknown command action: ${action}`);
        }
    }
}
