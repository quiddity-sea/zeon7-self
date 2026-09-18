import { BaseLayer } from './base-layer.js';

export default class MilitaryLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'military', 'api/military.php', 15000);
    }

    processData(data) {
        const contacts = data.military || [];
        const seen = new Set();

        contacts.forEach(ac => {
            if (!ac.lat || !ac.lon) return;
            const id = ac.hex;
            seen.add(id);

            const position = Cesium.Cartesian3.fromDegrees(ac.lon, ac.lat, ac.alt || 0);

            this.addOrUpdateEntity(id, position, {
                point: {
                    pixelSize: 7,
                    color: Cesium.Color.fromCssColorString('#f43f5e'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: ac.callsign || ac.hex,
                    font: '10px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#f43f5e'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(12, -4),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 10000000)
                }
            }, {
                callsign: ac.callsign,
                hex: ac.hex,
                altitude: (ac.alt ? (ac.alt * 3.281).toFixed(0) + ' ft' : 'N/A'),
                speed: (ac.speed ? (ac.speed * 1.944).toFixed(0) + ' kts' : 'N/A'),
                track: (ac.track ? ac.track.toFixed(0) + '°' : 'N/A'),
                aircraftType: ac.type,
                squawk: ac.squawk,
                military: true,
                type: 'military'
            });
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
