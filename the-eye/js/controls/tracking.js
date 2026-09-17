/**
 * Click-to-track — click any entity to lock the camera on it.
 * Draws a fading polyline trail behind the tracked entity.
 */
export class Tracking {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.trackedEntity = null;
        this.trailPositions = [];
        this.trailEntity = null;
        this.maxTrailPoints = 60; // 60 seconds of trail at 1 update/sec
        this.trailInterval = null;

        // Click handler
        this.handler = new Cesium.ScreenSpaceEventHandler(this.viewer.scene.canvas);
        this.handler.setInputAction((click) => this.onClick(click), Cesium.ScreenSpaceEventType.LEFT_CLICK);

        // Untrack button
        const untrackBtn = document.getElementById('btn-untrack');
        if (untrackBtn) {
            untrackBtn.addEventListener('click', () => this.untrack());
        }
    }

    onClick(click) {
        const picked = this.viewer.scene.pick(click.position);
        if (Cesium.defined(picked) && picked.id && picked.id._eyeMeta) {
            this.track(picked.id);
        }
    }

    track(entity) {
        this.untrack(); // Clear previous tracking

        this.trackedEntity = entity;
        this.trailPositions = [];

        // Show contact card
        const card = document.getElementById('contact-card');
        if (card) {
            card.classList.remove('hidden');
            this.updateContactCard();
        }

        // Fly to entity
        this.viewer.flyTo(entity, { duration: 1.5 });

        // Start trail recording
        this.trailInterval = setInterval(() => this.recordTrailPoint(), 1000);

        // Create trail polyline entity
        this.trailEntity = this.viewer.entities.add({
            polyline: {
                positions: new Cesium.CallbackProperty(() => {
                    return this.trailPositions.map(p => p.position);
                }, false),
                width: 2,
                material: new Cesium.PolylineGlowMaterialProperty({
                    glowPower: 0.3,
                    color: Cesium.Color.CYAN.withAlpha(0.6)
                }),
                clampToGround: false
            }
        });
    }

    untrack() {
        this.trackedEntity = null;

        if (this.trailInterval) {
            clearInterval(this.trailInterval);
            this.trailInterval = null;
        }

        if (this.trailEntity) {
            this.viewer.entities.remove(this.trailEntity);
            this.trailEntity = null;
        }

        this.trailPositions = [];

        const card = document.getElementById('contact-card');
        if (card) card.classList.add('hidden');
    }

    recordTrailPoint() {
        if (!this.trackedEntity || !this.trackedEntity.position) return;

        const pos = this.trackedEntity.position.getValue(Cesium.JulianDate.now());
        if (!pos) return;

        this.trailPositions.push({ position: pos, time: Date.now() });

        // Trim trail to max length
        if (this.trailPositions.length > this.maxTrailPoints) {
            this.trailPositions.shift();
        }
    }

    updateContactCard() {
        if (!this.trackedEntity || !this.trackedEntity._eyeMeta) return;

        const info = document.getElementById('contact-info');
        if (!info) return;

        const m = this.trackedEntity._eyeMeta;
        let html = '';

        const fields = [
            ['callsign', 'CALLSIGN'], ['name', 'NAME'], ['icao24', 'ICAO'],
            ['mmsi', 'MMSI'], ['altitude', 'ALT'], ['speed', 'SPD'],
            ['track', 'TRK'], ['squawk', 'SQK'], ['destination', 'DEST'],
            ['magnitude', 'MAG'], ['place', 'LOCATION'], ['depth', 'DEPTH'],
            ['rocket', 'VEHICLE'], ['provider', 'PROVIDER'], ['status', 'STATUS'],
            ['country', 'COUNTRY']
        ];

        fields.forEach(([key, label]) => {
            if (m[key]) {
                html += `<span class="contact-label">${label}</span> <span class="contact-value">${m[key]}</span><br>`;
            }
        });

        info.innerHTML = html;
    }

    getTracked() {
        return this.trackedEntity;
    }
}
