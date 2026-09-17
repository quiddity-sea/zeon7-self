import { BaseLayer } from './base-layer.js';

export default class FiresLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'fires', 'api/fires.php', 300000);
    }

    processData(data) {
        const fires = data.fires || [];
        fires.forEach((fire, i) => {
            if (!fire.lat || !fire.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(fire.lon, fire.lat, 0);
            const intensity = Math.min(fire.frp / 50, 1.0);
            const size = 4 + intensity * 8;

            this.addOrUpdateEntity(`fire_${i}`, position, {
                point: {
                    pixelSize: size,
                    color: Cesium.Color.fromCssColorString('#ff5722').withAlpha(0.7 + intensity * 0.3),
                    outlineColor: Cesium.Color.RED,
                    outlineWidth: 1
                }
            }, {
                brightness: fire.brightness,
                confidence: fire.confidence,
                frp: fire.frp.toFixed(1) + ' MW',
                date: fire.date + ' ' + fire.time,
                type: 'fire'
            });
        });
    }
}
