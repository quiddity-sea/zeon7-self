import { BaseLayer } from './base-layer.js';

/**
 * Satellite layer using satellite.js (loaded via CDN as global `satellite`).
 * Fetches TLE data from PHP proxy, propagates positions client-side using SGP4.
 */
export default class SatellitesLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'satellites', 'api/satellites.php', 300000); // 5 min TLE refresh
        this.tleData = []; // Cached TLE records
        this.positionUpdateInterval = null;
    }

    show() {
        super.show();
        // Update satellite positions every 3 seconds (orbital movement is fast)
        this.positionUpdateInterval = setInterval(() => this.updatePositions(), 3000);
    }

    hide() {
        super.hide();
        if (this.positionUpdateInterval) {
            clearInterval(this.positionUpdateInterval);
            this.positionUpdateInterval = null;
        }
    }

    processData(data) {
        this.tleData = data.satellites || [];
        this.updatePositions();
    }

    /** Propagate all satellites to current time using SGP4. */
    updatePositions() {
        if (!this.visible || !this.tleData.length) return;

        const now = new Date();
        const gmst = satellite.gstime(now);
        const seen = new Set();

        this.tleData.forEach(sat => {
            if (!sat.tle_line1 || !sat.tle_line2) return;

            try {
                const satrec = satellite.twoline2satrec(sat.tle_line1, sat.tle_line2);
                const posVel = satellite.propagate(satrec, now);

                if (!posVel.position) return;

                const geodetic = satellite.eciToGeodetic(posVel.position, gmst);
                const lon = satellite.degreesLong(geodetic.longitude);
                const lat = satellite.degreesLat(geodetic.latitude);
                const alt = geodetic.height * 1000; // km to metres

                if (isNaN(lon) || isNaN(lat) || isNaN(alt)) return;

                const id = sat.norad_id || sat.name;
                seen.add(id);

                const position = Cesium.Cartesian3.fromDegrees(lon, lat, alt);

                // Determine colour by altitude (LEO=green, MEO=yellow, GEO=orange)
                let color = Cesium.Color.LIME;
                if (alt > 35000000) color = Cesium.Color.ORANGE;
                else if (alt > 2000000) color = Cesium.Color.YELLOW;

                this.addOrUpdateEntity(id, position, {
                    point: {
                        pixelSize: 4,
                        color: color,
                        outlineColor: Cesium.Color.BLACK,
                        outlineWidth: 1
                    },
                    label: {
                        text: sat.name || '',
                        font: '9px JetBrains Mono',
                        fillColor: color,
                        outlineColor: Cesium.Color.BLACK,
                        outlineWidth: 2,
                        style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                        pixelOffset: new Cesium.Cartesian2(8, -4),
                        scale: 0.8,
                        distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 10000000)
                    }
                }, {
                    name: sat.name,
                    norad_id: sat.norad_id,
                    altitude: (alt / 1000).toFixed(1) + ' km',
                    inclination: sat.inclination ? sat.inclination.toFixed(1) + '°' : 'N/A',
                    period: sat.mean_motion ? (1440 / sat.mean_motion).toFixed(1) + ' min' : 'N/A',
                    type: 'satellite'
                });
            } catch (e) {
                // Skip satellites that fail propagation
            }
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
