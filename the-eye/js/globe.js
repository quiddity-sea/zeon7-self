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

        // Keyboard flight navigation & UI button bindings
        this.setupKeyboardControls();
        this.bindUI();
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

    /** Center and reset globe camera view without clearing active layers. */
    resetView() {
        if (window.tracking) window.tracking.untrack();
        if (window.cockpit) window.cockpit.exit();

        this.viewer.camera.flyTo({
            destination: Cesium.Cartesian3.fromDegrees(0, 20, 20000000),
            duration: 1.5,
            orientation: {
                heading: 0,
                pitch: Cesium.Math.toRadians(-90),
                roll: 0
            }
        });
    }

    /** Setup continuous keyboard flight controls (WASD, Q/E, Space/Shift). */
    setupKeyboardControls() {
        this.keys = {
            forward: false,
            backward: false,
            left: false,
            right: false,
            up: false,
            down: false,
            rotateLeft: false,
            rotateRight: false
        };

        const onKeyDown = (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            switch (e.code) {
                case 'KeyW':
                case 'ArrowUp':
                    this.keys.forward = true;
                    break;
                case 'KeyS':
                case 'ArrowDown':
                    this.keys.backward = true;
                    break;
                case 'KeyA':
                case 'ArrowLeft':
                    this.keys.left = true;
                    break;
                case 'KeyD':
                case 'ArrowRight':
                    this.keys.right = true;
                    break;
                case 'KeyQ':
                    this.keys.rotateLeft = true;
                    break;
                case 'KeyE':
                    this.keys.rotateRight = true;
                    break;
                case 'Space':
                    this.keys.up = true;
                    e.preventDefault();
                    break;
                case 'ShiftLeft':
                case 'ShiftRight':
                    this.keys.down = true;
                    break;
            }
        };

        const onKeyUp = (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            switch (e.code) {
                case 'KeyW':
                case 'ArrowUp':
                    this.keys.forward = false;
                    break;
                case 'KeyS':
                case 'ArrowDown':
                    this.keys.backward = false;
                    break;
                case 'KeyA':
                case 'ArrowLeft':
                    this.keys.left = false;
                    break;
                case 'KeyD':
                case 'ArrowRight':
                    this.keys.right = false;
                    break;
                case 'KeyQ':
                    this.keys.rotateLeft = false;
                    break;
                case 'KeyE':
                    this.keys.rotateRight = false;
                    break;
                case 'Space':
                    this.keys.up = false;
                    break;
                case 'ShiftLeft':
                case 'ShiftRight':
                    this.keys.down = false;
                    break;
            }
        };

        document.addEventListener('keydown', onKeyDown);
        document.addEventListener('keyup', onKeyUp);

        // Frame update loop tied to Cesium clock tick
        this.viewer.clock.onTick.addEventListener(() => {
            const isMoving = this.keys.forward || this.keys.backward || this.keys.left ||
                             this.keys.right || this.keys.up || this.keys.down ||
                             this.keys.rotateLeft || this.keys.rotateRight;
            if (!isMoving) return;

            const camera = this.viewer.camera;
            const carto = Cesium.Cartographic.fromCartesian(camera.position);
            const height = Math.max(carto ? carto.height : 10000000, 100);

            // Dynamic movement rate scaled to current altitude
            // High orbit = rapid transcontinental traversal; Low orbit = precise tactical panning
            const moveRate = height * 0.035;
            const rotateRate = 0.025; // radians

            // Determine if camera is top-down (map mode) or perspective (horizon mode)
            const isTopDown = Math.abs(camera.pitch) > Cesium.Math.toRadians(60);

            if (this.keys.forward) {
                if (isTopDown) camera.moveUp(moveRate);
                else camera.moveForward(moveRate);
            }
            if (this.keys.backward) {
                if (isTopDown) camera.moveDown(moveRate);
                else camera.moveBackward(moveRate);
            }
            if (this.keys.left) camera.moveLeft(moveRate);
            if (this.keys.right) camera.moveRight(moveRate);

            // Altitude adjustment: Space (ascend / zoom out), Shift (descend / zoom in)
            if (this.keys.up) {
                if (isTopDown) camera.moveBackward(moveRate * 0.8);
                else camera.moveUp(moveRate * 0.8);
            }
            if (this.keys.down) {
                if (isTopDown) camera.moveForward(moveRate * 0.8);
                else camera.moveDown(moveRate * 0.8);
            }

            // Heading rotation (Q/E)
            if (this.keys.rotateLeft) {
                if (isTopDown) camera.twistRight(rotateRate);
                else camera.lookLeft(rotateRate);
            }
            if (this.keys.rotateRight) {
                if (isTopDown) camera.twistLeft(rotateRate);
                else camera.lookRight(rotateRate);
            }
        });
    }

    /** Attach UI button handlers. */
    bindUI() {
        // Reset View button
        const btnReset = document.getElementById('btn-reset-view');
        if (btnReset) {
            btnReset.addEventListener('click', () => {
                this.resetView();
            });
        }

        // Controls Manual Modal
        const btnHelp = document.getElementById('btn-control-help');
        const modal = document.getElementById('eye-help-modal');
        const btnClose = document.getElementById('close-help-modal');

        if (btnHelp && modal) {
            btnHelp.addEventListener('click', () => {
                modal.classList.toggle('hidden');
            });
        }

        if (btnClose && modal) {
            btnClose.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        }

        // Close modal on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
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
