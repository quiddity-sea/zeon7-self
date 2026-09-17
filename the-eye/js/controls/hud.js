/**
 * Tactical HUD — updates camera coordinates, UTC clock, layer counts,
 * and tracked entity telemetry every second.
 */
export class Hud {
    constructor(globe, tracking) {
        this.viewer = globe.viewer;
        this.globe = globe;
        this.tracking = tracking;
        this.clockEl = document.getElementById('hud-clock');
        this.coordsEl = document.getElementById('hud-coords');
        this.statsEl = document.getElementById('hud-stats');
        this.telemetryEl = document.getElementById('hud-telemetry');

        // Keyboard shortcut: H to toggle HUD
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return;
            if (e.key === 'h' || e.key === 'H') {
                const hud = document.getElementById('tactical-hud');
                hud.classList.toggle('hidden');
            }
        });
    }

    /** Called every second from eye-app.js. */
    update() {
        this.updateClock();
        this.updateCoords();
        this.updateStats();
        this.updateTelemetry();
    }

    updateClock() {
        if (!this.clockEl) return;
        const d = new Date();
        this.clockEl.textContent = d.toISOString().replace('T', ' ').substring(0, 19) + ' UTC';
    }

    updateCoords() {
        if (!this.coordsEl) return;
        try {
            const cam = this.viewer.camera;
            const carto = Cesium.Cartographic.fromCartesian(cam.position);
            const lat = Cesium.Math.toDegrees(carto.latitude).toFixed(4);
            const lon = Cesium.Math.toDegrees(carto.longitude).toFixed(4);
            const alt = carto.height;

            let altStr;
            if (alt > 100000) altStr = (alt / 1000).toFixed(0) + ' km';
            else altStr = alt.toFixed(0) + ' m';

            const heading = Cesium.Math.toDegrees(cam.heading).toFixed(0);
            const pitch = Cesium.Math.toDegrees(cam.pitch).toFixed(0);

            this.coordsEl.innerHTML =
                `LAT ${lat} LON ${lon}<br>` +
                `ALT ${altStr} HDG ${heading}° PIT ${pitch}°`;
        } catch (e) {
            // Camera position not ready yet
        }
    }

    updateStats() {
        if (!this.statsEl) return;
        const counts = [];
        this.globe.activeLayers.forEach(name => {
            const layer = this.globe.layers[name];
            if (layer) {
                counts.push(`${name.toUpperCase()}: ${layer.getCount()}`);
            }
        });
        this.statsEl.textContent = counts.join(' | ') || 'No layers active';
    }

    updateTelemetry() {
        if (!this.telemetryEl) return;
        const tracked = this.tracking.getTracked();
        if (!tracked || !tracked._eyeMeta) {
            this.telemetryEl.classList.add('hidden');
            return;
        }

        this.telemetryEl.classList.remove('hidden');
        const m = tracked._eyeMeta;
        let html = `<strong>TRACKING: ${m.callsign || m.name || m.entityId}</strong><br>`;

        if (m.altitude) html += `ALT: ${m.altitude}<br>`;
        if (m.speed) html += `SPD: ${m.speed}<br>`;
        if (m.track) html += `TRK: ${m.track}<br>`;
        if (m.squawk) html += `SQK: ${m.squawk}<br>`;
        if (m.icao24) html += `ICAO: ${m.icao24}<br>`;
        if (m.mmsi) html += `MMSI: ${m.mmsi}<br>`;
        if (m.magnitude) html += `MAG: ${m.magnitude}<br>`;
        if (m.place) html += `LOC: ${m.place}<br>`;

        this.telemetryEl.innerHTML = html;
    }
}
