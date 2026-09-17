/**
 * BaseLayer — Abstract base class for all data layers.
 * Each layer handles its own fetch cycle, entity management, and visibility.
 */
export class BaseLayer {
    /**
     * @param {Cesium.Viewer} viewer   — CesiumJS viewer instance
     * @param {string}        name     — Layer slug (e.g. 'flights')
     * @param {string}        apiUrl   — PHP proxy endpoint (e.g. 'api/flights.php')
     * @param {number}        refreshMs — Refresh interval in milliseconds
     */
    constructor(viewer, name, apiUrl, refreshMs) {
        this.viewer = viewer;
        this.name = name;
        this.apiUrl = apiUrl;
        this.refreshMs = refreshMs;
        this.entities = new Map();
        this.visible = false;
        this.interval = null;
        this.entityCount = 0;
    }

    /** Start the layer — fetch data and begin refresh loop. */
    show() {
        this.visible = true;
        this.fetchData();
        this.interval = setInterval(() => this.fetchData(), this.refreshMs);
    }

    /** Stop the layer — clear refresh and hide all entities. */
    hide() {
        this.visible = false;
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.entities.forEach(entity => {
            entity.show = false;
        });
    }

    /** Remove all entities from the viewer. */
    destroy() {
        this.hide();
        this.entities.forEach(entity => {
            this.viewer.entities.remove(entity);
        });
        this.entities.clear();
    }

    /** Fetch data from the PHP proxy. Override processData() for layer-specific logic. */
    async fetchData() {
        if (!this.visible) return;
        try {
            const res = await fetch(this.apiUrl, {
                headers: { 'X-Eye-Token': window.EYE_CONFIG.sessionToken }
            });
            if (!res.ok) return;
            const data = await res.json();
            this.processData(data);
        } catch (e) {
            console.warn(`[Eye] ${this.name} fetch failed:`, e.message);
        }
    }

    /**
     * Override this in each layer subclass.
     * Should call this.addOrUpdateEntity() for each data point.
     */
    processData(data) {
        // Override in subclass
    }

    /**
     * Add or update an entity by unique ID.
     * @param {string}               id      — Unique entity identifier
     * @param {Cesium.Cartesian3}    position — Position
     * @param {object}               options  — Cesium Entity options (point, billboard, label, etc.)
     * @param {object}               metadata — Extra metadata for telemetry card
     */
    addOrUpdateEntity(id, position, options, metadata = {}) {
        if (this.entities.has(id)) {
            const entity = this.entities.get(id);
            entity.position = position;
            entity.show = true;
            // Update metadata
            if (metadata) entity._eyeMeta = metadata;
        } else {
            const entity = this.viewer.entities.add({
                id: `${this.name}_${id}`,
                position: position,
                show: this.visible,
                ...options
            });
            entity._eyeMeta = { layer: this.name, entityId: id, ...metadata };
            this.entities.set(id, entity);
        }
    }

    /** Get current entity count. */
    getCount() {
        let count = 0;
        this.entities.forEach(e => { if (e.show) count++; });
        return count;
    }
}
