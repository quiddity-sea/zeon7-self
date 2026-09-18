import { BaseLayer } from './base-layer.js';

export default class ShipsLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'ships', 'api/ships.php', 30000);
    }

    processData(data) {
        const vessels = data.ships || [];
        const seen = new Set();

        vessels.forEach(v => {
            if (!v.lat || !v.lon) return;
            const id = String(v.mmsi);
            seen.add(id);

            const position = Cesium.Cartesian3.fromDegrees(v.lon, v.lat, 0);

            this.addOrUpdateEntity(id, position, {
                point: {
                    pixelSize: 5,
                    color: Cesium.Color.fromCssColorString('#f59e0b'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: v.name || v.mmsi,
                    font: '9px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#f59e0b'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(10, -4),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 10000000)
                }
            }, {
                name: v.name,
                mmsi: v.mmsi,
                speed: v.speed.toFixed(1) + ' kts',
                course: v.course.toFixed(0) + '°',
                heading: v.heading + '°',
                destination: v.dest || 'N/A',
                length: v.length + ' m',
                type: 'ship'
            });
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
