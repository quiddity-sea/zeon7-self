import { BaseLayer } from './base-layer.js';

export default class FlightsLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'flights', 'api/flights.php', 10000);
    }

    processData(data) {
        const states = data.states || [];
        const seen = new Set();

        states.forEach(s => {
            const icao24   = s[0];
            const callsign = (s[1] || '').trim();
            const lon      = s[5];
            const lat      = s[6];
            const alt      = s[7] || 0;
            const onGround = s[8];
            const velocity = s[9] || 0;
            const track    = s[10] || 0;
            const squawk   = s[14] || '';

            if (lon === null || lat === null) return;
            seen.add(icao24);

            const position = Cesium.Cartesian3.fromDegrees(lon, lat, alt);
            const speedKts = (velocity * 1.944).toFixed(0);
            const altFt = (alt * 3.281).toFixed(0);

            this.addOrUpdateEntity(icao24, position, {
                point: {
                    pixelSize: onGround ? 3 : 5,
                    color: onGround ? Cesium.Color.GRAY.withAlpha(0.5) : Cesium.Color.CYAN,
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: callsign || icao24,
                    font: '10px JetBrains Mono',
                    fillColor: Cesium.Color.CYAN,
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(12, -4),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 10000000)
                }
            }, {
                callsign: callsign,
                icao24: icao24,
                altitude: altFt + ' ft',
                speed: speedKts + ' kts',
                track: track.toFixed(0) + '°',
                squawk: squawk,
                onGround: onGround,
                type: 'flight'
            });
        });

        // Remove stale entities
        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) {
                entity.show = false;
            }
        });
    }
}
