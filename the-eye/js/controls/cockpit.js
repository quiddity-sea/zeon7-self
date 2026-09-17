/**
 * Cockpit mode — locks the camera behind/below a tracked entity.
 * The terrain stays visible underneath. Sensor styles carry through.
 * Keyboard shortcut: C to toggle cockpit, Esc to exit.
 */
export class Cockpit {
    constructor(globe, tracking) {
        this.viewer = globe.viewer;
        this.tracking = tracking;
        this.active = false;
        this.animationFrame = null;

        // Cockpit camera offset (behind and above the entity)
        this.offsetDistance = 500;  // metres behind
        this.offsetHeight = 150;   // metres above

        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return;
            if (e.key === 'c' || e.key === 'C') this.toggle();
            if (e.key === 'Escape' && this.active) this.exit();
        });

        const cockpitBtn = document.getElementById('btn-cockpit');
        if (cockpitBtn) {
            cockpitBtn.addEventListener('click', () => this.toggle());
        }
    }

    toggle() {
        if (this.active) {
            this.exit();
        } else {
            this.enter();
        }
    }

    enter() {
        const tracked = this.tracking.getTracked();
        if (!tracked) return;

        this.active = true;
        this.viewer.scene.screenSpaceCameraController.enableRotate = false;
        this.viewer.scene.screenSpaceCameraController.enableTranslate = false;
        this.viewer.scene.screenSpaceCameraController.enableZoom = false;
        this.viewer.scene.screenSpaceCameraController.enableTilt = false;
        this.viewer.scene.screenSpaceCameraController.enableLook = false;

        this.update();
    }

    exit() {
        this.active = false;
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
            this.animationFrame = null;
        }

        this.viewer.scene.screenSpaceCameraController.enableRotate = true;
        this.viewer.scene.screenSpaceCameraController.enableTranslate = true;
        this.viewer.scene.screenSpaceCameraController.enableZoom = true;
        this.viewer.scene.screenSpaceCameraController.enableTilt = true;
        this.viewer.scene.screenSpaceCameraController.enableLook = true;
    }

    update() {
        if (!this.active) return;

        const tracked = this.tracking.getTracked();
        if (!tracked || !tracked.position) {
            this.exit();
            return;
        }

        const pos = tracked.position.getValue(Cesium.JulianDate.now());
        if (!pos) {
            this.animationFrame = requestAnimationFrame(() => this.update());
            return;
        }

        const meta = tracked._eyeMeta || {};
        const trackDeg = parseFloat(meta.track) || 0;
        const trackRad = Cesium.Math.toRadians(trackDeg);

        // Position camera behind the entity
        const transform = Cesium.Transforms.eastNorthUpToFixedFrame(pos);
        const offset = new Cesium.Cartesian3(
            -Math.sin(trackRad) * this.offsetDistance,
            -Math.cos(trackRad) * this.offsetDistance,
            this.offsetHeight
        );
        const cameraPos = Cesium.Matrix4.multiplyByPoint(transform, offset, new Cesium.Cartesian3());

        this.viewer.camera.setView({
            destination: cameraPos,
            orientation: {
                heading: trackRad,
                pitch: Cesium.Math.toRadians(-15),
                roll: 0
            }
        });

        this.animationFrame = requestAnimationFrame(() => this.update());
    }
}
