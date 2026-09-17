/**
 * Layer toggle panel — renders all available layers as toggle switches.
 */
export class Panels {
    constructor(globe) {
        this.globe = globe;
        this.container = document.getElementById('layer-toggles');
        if (this.container) {
            this.render();
        }
    }

    render() {
        const layers = [
            { name: 'flights',     icon: '✈',  label: 'FLIGHTS' },
            { name: 'military',    icon: '🪖',  label: 'MILITARY',   cls: 'military' },
            { name: 'ships',       icon: '🚢',  label: 'SHIPS' },
            { name: 'satellites',  icon: '🛰',  label: 'SATELLITES' },
            { name: 'earthquakes', icon: '🌍',  label: 'EARTHQUAKES' },
            { name: 'cctv',        icon: '📷',  label: 'CCTV' },
            { name: 'traffic',     icon: '🚗',  label: 'TRAFFIC' },
            { name: 'fires',       icon: '🔥',  label: 'FIRES' },
            { name: 'radio',       icon: '📻',  label: 'RADIO' },
            { name: 'launches',    icon: '🚀',  label: 'LAUNCHES' },
        ];

        layers.forEach(layer => {
            const div = document.createElement('div');
            div.className = 'layer-toggle' + (layer.cls ? ' ' + layer.cls : '');
            div.dataset.layer = layer.name;
            div.innerHTML = `<span>${layer.icon} ${layer.label}</span>`;

            div.addEventListener('click', () => {
                this.globe.toggleLayer(layer.name);
                div.classList.toggle('active');
            });

            this.container.appendChild(div);
        });
    }
}
