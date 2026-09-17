import { BaseLayer } from './base-layer.js';

export default class TrafficLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'traffic', 'api/traffic.php', 300000);
        this.polylines = [];
    }

    processData(data) {
        // Remove previous polylines
        this.polylines.forEach(p => this.viewer.entities.remove(p));
        this.polylines = [];

        const roads = data.roads || [];
        roads.forEach(road => {
            if (!road.coords || road.coords.length < 2) return;
            const positions = road.coords.map(c => Cesium.Cartesian3.fromDegrees(c[0], c[1], 5));
            let color = Cesium.Color.fromCssColorString('#22d3ee').withAlpha(0.4);
            if (road.type === 'motorway') color = Cesium.Color.fromCssColorString('#f59e0b').withAlpha(0.5);

            const entity = this.viewer.entities.add({
                polyline: {
                    positions: positions,
                    width: road.type === 'motorway' ? 3 : 2,
                    material: color,
                    clampToGround: true
                },
                show: this.visible
            });
            entity._eyeMeta = { name: road.name, roadType: road.type, type: 'traffic' };
            this.polylines.push(entity);
        });
    }

    hide() {
        super.hide();
        this.polylines.forEach(p => p.show = false);
    }

    show() {
        this.visible = true;
        this.polylines.forEach(p => p.show = true);
        this.fetchData();
        this.interval = setInterval(() => this.fetchData(), this.refreshMs);
    }
}
