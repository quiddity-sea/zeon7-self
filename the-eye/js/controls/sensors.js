/**
 * Sensor visual modes — GLSL PostProcessStage shaders.
 * Keyboard shortcuts: 1=Normal, 2=CRT, 3=NVG, 4=FLIR, 5=Noir, 6=Snow
 */
export class Sensors {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.currentStage = null;
        this.currentStyle = 'normal';

        // GLSL fragment shaders for each sensor mode
        this.shaders = {
            normal: null,

            crt: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    // Scanline effect
                    float scanline = sin(v_textureCoordinates.y * 800.0) * 0.04;
                    // Phosphor RGB separation
                    float r = texture(colorTexture, v_textureCoordinates + vec2(0.001, 0.0)).r;
                    float g = color.g;
                    float b = texture(colorTexture, v_textureCoordinates - vec2(0.001, 0.0)).b;
                    // Vignette
                    vec2 uv = v_textureCoordinates * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.3;
                    // Green phosphor tint
                    vec3 crt = vec3(r * 0.6, g * 1.2, b * 0.6) * vignette - scanline;
                    out_FragColor = vec4(crt, 1.0);
                }
            `,

            nvg: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // Night vision green with grain
                    float grain = fract(sin(dot(v_textureCoordinates * 500.0, vec2(12.9898, 78.233))) * 43758.5453) * 0.06;
                    float green = luminance * 1.8 + grain;
                    // Slight vignette
                    vec2 uv = v_textureCoordinates * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.35;
                    out_FragColor = vec4(0.05, green * vignette, 0.05, 1.0);
                }
            `,

            flir: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // Ironbow thermal palette
                    vec3 thermal;
                    if (luminance < 0.25) {
                        thermal = mix(vec3(0.0, 0.0, 0.2), vec3(0.5, 0.0, 0.5), luminance * 4.0);
                    } else if (luminance < 0.5) {
                        thermal = mix(vec3(0.5, 0.0, 0.5), vec3(1.0, 0.2, 0.0), (luminance - 0.25) * 4.0);
                    } else if (luminance < 0.75) {
                        thermal = mix(vec3(1.0, 0.2, 0.0), vec3(1.0, 0.8, 0.0), (luminance - 0.5) * 4.0);
                    } else {
                        thermal = mix(vec3(1.0, 0.8, 0.0), vec3(1.0, 1.0, 0.9), (luminance - 0.75) * 4.0);
                    }
                    out_FragColor = vec4(thermal, 1.0);
                }
            `,

            noir: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // High contrast desaturation
                    float contrast = (luminance - 0.5) * 1.8 + 0.5;
                    contrast = clamp(contrast, 0.0, 1.0);
                    // Slight sepia/blue tint
                    vec3 noir = vec3(contrast * 0.9, contrast * 0.9, contrast * 1.05);
                    // Vignette
                    vec2 uv = v_textureCoordinates * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.45;
                    out_FragColor = vec4(noir * vignette, 1.0);
                }
            `,

            snow: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // White-out with altitude visibility bands
                    float whiteout = luminance * 0.3 + 0.7;
                    // Subtle blue tint
                    vec3 snow = vec3(whiteout * 0.92, whiteout * 0.95, whiteout);
                    // Edge darkening for terrain relief
                    float dx = dFdx(luminance);
                    float dy = dFdy(luminance);
                    float edge = sqrt(dx * dx + dy * dy) * 8.0;
                    snow -= vec3(edge * 0.5);
                    out_FragColor = vec4(clamp(snow, 0.0, 1.0), 1.0);
                }
            `
        };

        this.initKeyboardShortcuts();
        this.renderButtons();
    }

    /** Set the active sensor style. */
    setStyle(name) {
        if (!this.shaders.hasOwnProperty(name)) return;

        // Remove current post-process stage
        if (this.currentStage) {
            this.viewer.scene.postProcessStages.remove(this.currentStage);
            this.currentStage = null;
        }

        this.currentStyle = name;

        // Apply new shader (skip for 'normal')
        if (name !== 'normal' && this.shaders[name]) {
            this.currentStage = new Cesium.PostProcessStage({
                fragmentShader: this.shaders[name]
            });
            this.viewer.scene.postProcessStages.add(this.currentStage);
        }

        // Update button states
        document.querySelectorAll('.sensor-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.style === name);
        });
    }

    /** Keyboard shortcuts: 1-6 for sensor styles. */
    initKeyboardShortcuts() {
        const keys = { '1': 'normal', '2': 'crt', '3': 'nvg', '4': 'flir', '5': 'noir', '6': 'snow' };
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return; // Don't intercept while typing
            if (keys[e.key]) {
                this.setStyle(keys[e.key]);
            }
        });
    }

    /** Render sensor mode buttons into #sensor-buttons. */
    renderButtons() {
        const container = document.getElementById('sensor-buttons');
        if (!container) return;

        const styles = ['normal', 'crt', 'nvg', 'flir', 'noir', 'snow'];
        styles.forEach((style, i) => {
            const btn = document.createElement('button');
            btn.className = 'sensor-btn' + (style === 'normal' ? ' active' : '');
            btn.dataset.style = style;
            btn.textContent = `${i + 1} ${style.toUpperCase()}`;
            btn.addEventListener('click', () => this.setStyle(style));
            container.appendChild(btn);
        });
    }
}
