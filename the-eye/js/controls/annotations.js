/**
 * Annotations — draw polygons, routes, and pins on the globe.
 * Authenticated users' annotations are persisted to MariaDB.
 */
export class Annotations {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.annotations = [];
        this.drawing = false;
        this.drawPoints = [];
        this.drawType = null; // 'polygon', 'route', 'pin'

        // Load saved annotations for authenticated user
        if (window.EYE_CONFIG.isAuth) {
            this.loadSaved();
        }
    }

    /** Create annotation from agent command. */
    addFromCommand(type, geometry, label) {
        if (type === 'pin' && geometry.lon && geometry.lat) {
            this.addPin(geometry.lon, geometry.lat, label);
        } else if (type === 'route' && geometry.coordinates) {
            this.addRoute(geometry.coordinates, label);
        } else if (type === 'polygon' && geometry.coordinates) {
            this.addPolygon(geometry.coordinates, label);
        }

        // Save to DB if authenticated
        if (window.EYE_CONFIG.isAuth) {
            this.saveAnnotation(type, geometry, label, 'otec');
        }
    }

    addPin(lon, lat, label) {
        const entity = this.viewer.entities.add({
            position: Cesium.Cartesian3.fromDegrees(lon, lat, 0),
            point: { pixelSize: 10, color: Cesium.Color.CYAN, outlineColor: Cesium.Color.WHITE, outlineWidth: 2 },
            label: {
                text: label || 'Pin',
                font: '12px JetBrains Mono',
                fillColor: Cesium.Color.CYAN,
                outlineColor: Cesium.Color.BLACK,
                outlineWidth: 2,
                style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                pixelOffset: new Cesium.Cartesian2(0, -20)
            }
        });
        this.annotations.push(entity);
    }

    addRoute(coordinates, label) {
        const positions = coordinates.map(c => Cesium.Cartesian3.fromDegrees(c[0], c[1], 10));
        const entity = this.viewer.entities.add({
            polyline: {
                positions: positions,
                width: 3,
                material: Cesium.Color.CYAN.withAlpha(0.8),
                clampToGround: true
            }
        });
        this.annotations.push(entity);
    }

    addPolygon(coordinates, label) {
        const positions = coordinates.map(c => Cesium.Cartesian3.fromDegrees(c[0], c[1]));
        const entity = this.viewer.entities.add({
            polygon: {
                hierarchy: new Cesium.PolygonHierarchy(positions),
                material: Cesium.Color.CYAN.withAlpha(0.15),
                outline: true,
                outlineColor: Cesium.Color.CYAN,
                outlineWidth: 2,
                height: 0,
                perPositionHeight: false
            },
            label: label ? {
                text: label,
                font: '11px JetBrains Mono',
                fillColor: Cesium.Color.CYAN,
                outlineColor: Cesium.Color.BLACK,
                outlineWidth: 2,
                style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                position: positions[0]
            } : undefined
        });
        this.annotations.push(entity);
    }

    /** Clear all annotations. */
    clearAll() {
        this.annotations.forEach(e => this.viewer.entities.remove(e));
        this.annotations = [];
    }

    /** Save annotation to backend. */
    async saveAnnotation(type, geometry, label, agentId = null) {
        try {
            await fetch('api/agent/command.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Eye-Token': window.EYE_CONFIG.sessionToken
                },
                body: JSON.stringify({
                    action: 'save_annotation',
                    type: type,
                    geometry: geometry,
                    label: label,
                    agent_id: agentId
                })
            });
        } catch (e) {
            console.warn('[Eye] Failed to save annotation:', e);
        }
    }

    /** Load saved annotations from DB. */
    async loadSaved() {
        try {
            const res = await fetch('api/agent/history.php?type=annotations', {
                headers: { 'X-Eye-Token': window.EYE_CONFIG.sessionToken }
            });
            if (!res.ok) return;
            const data = await res.json();
            (data.annotations || []).forEach(ann => {
                const geom = typeof ann.geometry === 'string' ? JSON.parse(ann.geometry) : ann.geometry;
                this.addFromCommand(ann.type, geom, ann.label);
            });
        } catch (e) {
            console.warn('[Eye] Failed to load annotations:', e);
        }
    }
}
