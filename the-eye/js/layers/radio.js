import { BaseLayer } from './base-layer.js';

export default class RadioLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'radio', 'api/radio.php', 600000);
        this.audioElement = null;
    }

    processData(data) {
        const stations = data.stations || [];
        stations.forEach(st => {
            if (!st.lat || !st.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(st.lon, st.lat, 20);
            this.addOrUpdateEntity(st.id, position, {
                point: {
                    pixelSize: 4,
                    color: Cesium.Color.fromCssColorString('#a855f7').withAlpha(0.7),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: st.name,
                    font: '8px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#a855f7'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(8, -4),
                    scale: 0.75,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 100000)
                }
            }, {
                name: st.name,
                country: st.country,
                url: st.url,
                codec: st.codec,
                bitrate: st.bitrate + ' kbps',
                type: 'radio'
            });
        });
    }

    /** Play a radio station stream. Called when a radio entity is clicked. */
    playStation(url) {
        this.stopStation();
        this.audioElement = new Audio(url);
        this.audioElement.volume = 0.5;
        this.audioElement.play().catch(() => {});
    }

    stopStation() {
        if (this.audioElement) {
            this.audioElement.pause();
            this.audioElement = null;
        }
    }
}
