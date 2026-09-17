import { BaseLayer } from './base-layer.js';

export default class CctvLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'cctv', 'api/cctv.php', 600000);
    }

    processData(data) {
        const cameras = data.cameras || [];
        cameras.forEach(cam => {
            if (!cam.lat || !cam.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(cam.lon, cam.lat, 10);
            this.addOrUpdateEntity(String(cam.id), position, {
                billboard: {
                    image: 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"><circle cx="8" cy="8" r="6" fill="none" stroke="%2322d3ee" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="%2322d3ee"/></svg>'),
                    width: 16,
                    height: 16,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 500000)
                }
            }, { name: cam.name, zone: cam.zone, type: 'cctv' });
        });
    }
}
