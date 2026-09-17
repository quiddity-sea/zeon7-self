import { BaseLayer } from './base-layer.js';

export default class EarthquakesLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'earthquakes', 'api/earthquakes.php', 60000);
    }

    processData(data) {
        const features = data.features || [];
        const seen = new Set();

        features.forEach(f => {
            const coords = f.geometry?.coordinates;
            if (!coords || coords.length < 2) return;

            const id = f.id || f.properties?.code || `eq_${coords[0]}_${coords[1]}`;
            seen.add(id);

            const lon = coords[0];
            const lat = coords[1];
            const depth = coords[2] || 0; // km
            const mag = f.properties?.mag || 0;
            const place = f.properties?.place || 'Unknown';
            const time = f.properties?.time || 0;

            const position = Cesium.Cartesian3.fromDegrees(lon, lat, 0);

            // Size and colour based on magnitude
            let pixelSize = 4;
            let color = Cesium.Color.YELLOW;
            if (mag >= 6) { pixelSize = 16; color = Cesium.Color.RED; }
            else if (mag >= 5) { pixelSize = 12; color = Cesium.Color.fromCssColorString('#f43f5e'); }
            else if (mag >= 4) { pixelSize = 9; color = Cesium.Color.ORANGE; }
            else if (mag >= 3) { pixelSize = 6; color = Cesium.Color.YELLOW; }

            this.addOrUpdateEntity(id, position, {
                point: {
                    pixelSize: pixelSize,
                    color: color.withAlpha(0.8),
                    outlineColor: color,
                    outlineWidth: 2
                },
                label: {
                    text: `M${mag.toFixed(1)}`,
                    font: '10px JetBrains Mono',
                    fillColor: color,
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(pixelSize + 4, -4),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 2000000)
                }
            }, {
                magnitude: `M${mag.toFixed(1)}`,
                place: place,
                depth: depth.toFixed(1) + ' km',
                time: new Date(time).toISOString(),
                type: 'earthquake'
            });
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
