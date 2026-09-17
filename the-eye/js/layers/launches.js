import { BaseLayer } from './base-layer.js';

export default class LaunchesLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'launches', 'api/launches.php', 900000);
    }

    processData(data) {
        const launches = data.launches || [];
        launches.forEach(launch => {
            if (!launch.lat || !launch.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(launch.lon, launch.lat, 0);

            this.addOrUpdateEntity(launch.id, position, {
                billboard: {
                    image: 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="24"><polygon points="8,0 14,20 8,16 2,20" fill="%2300ff41" opacity="0.8"/></svg>'),
                    width: 16,
                    height: 24,
                    verticalOrigin: Cesium.VerticalOrigin.BOTTOM
                },
                label: {
                    text: launch.rocket || launch.name,
                    font: '10px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#00ff41'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(14, -12),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 5000000)
                }
            }, {
                name: launch.name,
                rocket: launch.rocket,
                provider: launch.provider,
                status: launch.status,
                net: launch.net,
                padName: launch.pad_name,
                location: launch.location,
                mission: launch.mission,
                type: 'launch'
            });
        });
    }
}
