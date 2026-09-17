/**
 * EyeGlobe — Wrapper around CesiumJS Viewer.
 * Manages layers, provides methods for agent commands.
 */
export class EyeGlobe {
    constructor(containerId, ionToken) {
        // Configure Cesium Ion token
        if (ionToken) {
            Cesium.Ion.defaultAccessToken = ionToken;
        }

        // Create viewer
        this.viewer = new Cesium.Viewer(containerId, {
            baseLayerPicker: false,
            geocoder: false,
            homeButton: false,
            infoBox: false,
            navigationHelpButton: false,
            sceneModePicker: false,
            animation: false,
            timeline: false,
            fullscreenButton: false,
            selectionIndicator: false,
            shouldAnimate: true
        });

        // Configure imagery
        if (ionToken) {
            // Use Cesium Ion default (Bing Maps Aerial with Labels)
            // Already set as default when Ion token is provided
        } else {
            // Fallback to OpenStreetMap
            this.viewer.imageryLayers.removeAll();
            this.viewer.imageryLayers.addImageryProvider(
                new Cesium.OpenStreetMapImageryProvider({
                    url: 'https://tile.openstreetmap.org/'
                })
            );
        }

        // Enable lighting
        this.viewer.scene.globe.enableLighting = true;
        this.viewer.scene.globe.depthTestAgainstTerrain = true;

        // Layer registry
        this.layers = {};
        this.activeLayers = new Set();

        // Lazy-load all layer modules
        this.initLayers();
    }

    async initLayers() {
        const layerModules = [
            'flights', 'military', 'ships', 'satellites', 'earthquakes',
            'cctv', 'traffic', 'fires', 'radio', 'launches'
        ];

        for (const name of layerModules) {
            try {
                const module = await import(`./layers/${name}.js`);
                this.layers[name] = new module.default(this.viewer);
            } catch (e) {
                console.warn(`[Eye] Failed to load layer module: ${name}`, e);
            }
        }
    }

    /** Fly camera to a position. */
    flyTo(lon, lat, alt = 50000) {
        this.viewer.camera.flyTo({
            destination: Cesium.Cartesian3.fromDegrees(lon, lat, alt),
            duration: 2.0,
            orientation: {
                heading: 0,
                pitch: Cesium.Math.toRadians(-45),
                roll: 0
            }
        });
    }

    /** Toggle a layer on/off by name. */
    toggleLayer(name) {
        const layer = this.layers[name];
        if (!layer) return;

        if (this.activeLayers.has(name)) {
            layer.hide();
            this.activeLayers.delete(name);
        } else {
            layer.show();
            this.activeLayers.add(name);
        }
    }

    /** Set sensor visual style. */
    setStyle(style) {
        if (window.sensors) {
            window.sensors.setStyle(style);
        }
    }

    /** Track an entity by layer name and entity ID. */
    track(entityId, layerName) {
        const fullId = `${layerName}_${entityId}`;
        const entity = this.viewer.entities.getById(fullId);
        if (entity && window.tracking) {
            window.tracking.track(entity);
        }
    }

    /** Create an annotation. */
    annotate(type, geometry, label) {
        if (window.annotations) {
            window.annotations.addFromCommand(type, geometry, label);
        }
    }

    /** Reset globe to initial view. */
    reset() {
        // Untrack
        if (window.tracking) window.tracking.untrack();
        if (window.cockpit) window.cockpit.exit();

        // Hide all layers
        this.activeLayers.forEach(name => {
            if (this.layers[name]) this.layers[name].hide();
        });
        this.activeLayers.clear();

        // Fly to default view
        this.viewer.camera.flyTo({
            destination: Cesium.Cartesian3.fromDegrees(0, 20, 20000000),
            duration: 2.0,
            orientation: { heading: 0, pitch: Cesium.Math.toRadians(-90), roll: 0 }
        });
    }

    /** Get current camera state as serialisable object. */
    getCameraState() {
        try {
            const cam = this.viewer.camera;
            const carto = Cesium.Cartographic.fromCartesian(cam.position);
            return {
                lon: Cesium.Math.toDegrees(carto.longitude),
                lat: Cesium.Math.toDegrees(carto.latitude),
                alt: carto.height,
                heading: Cesium.Math.toDegrees(cam.heading),
                pitch: Cesium.Math.toDegrees(cam.pitch),
                roll: Cesium.Math.toDegrees(cam.roll)
            };
        } catch (e) {
            return null;
        }
    }
}
