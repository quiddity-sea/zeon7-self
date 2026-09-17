/**
 * CCTV Viewshed — renders estimated camera field-of-view frustum volumes.
 */
export class Viewshed {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.frustums = [];
        this.showViewsheds = false;
    }

    /** Toggle viewshed rendering for all visible CCTV entities. */
    toggle() {
        this.showViewsheds = !this.showViewsheds;
        if (this.showViewsheds) {
            this.renderFrustums();
        } else {
            this.clearFrustums();
        }
    }

    renderFrustums() {
        this.clearFrustums();
        const cctvLayer = window.eyeGlobe.layers['cctv'];
        if (!cctvLayer) return;

        cctvLayer.entities.forEach((entity) => {
            const pos = entity.position?.getValue(Cesium.JulianDate.now());
            if (!pos) return;

            // Estimate a 60-degree FOV cone, 200m range
            const carto = Cesium.Cartographic.fromCartesian(pos);
            const lon = Cesium.Math.toDegrees(carto.longitude);
            const lat = Cesium.Math.toDegrees(carto.latitude);
            const range = 200; // metres
            const fov = 30; // half-angle degrees

            // Create a simple fan polygon to approximate the viewshed
            const points = [Cesium.Cartesian3.fromDegrees(lon, lat, 5)];
            for (let angle = -fov; angle <= fov; angle += 5) {
                const rad = Cesium.Math.toRadians(angle);
                const dLon = (range / 111320) * Math.sin(rad);
                const dLat = (range / 110540) * Math.cos(rad);
                points.push(Cesium.Cartesian3.fromDegrees(lon + dLon, lat + dLat, 5));
            }

            const frustum = this.viewer.entities.add({
                polygon: {
                    hierarchy: new Cesium.PolygonHierarchy(points),
                    material: Cesium.Color.CYAN.withAlpha(0.08),
                    outline: true,
                    outlineColor: Cesium.Color.CYAN.withAlpha(0.3),
                    outlineWidth: 1,
                    height: 0,
                    extrudedHeight: 15
                }
            });
            this.frustums.push(frustum);
        });
    }

    clearFrustums() {
        this.frustums.forEach(f => this.viewer.entities.remove(f));
        this.frustums = [];
    }
}
