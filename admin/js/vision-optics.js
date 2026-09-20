/**
 * VisionOptics — Real-Time Multi-Spectrum Surveillance Optics Inspector.
 * Uses pure GLSL WebGL fragment shaders to filter image assets across:
 * 1. NORMAL (True-Color Optical)
 * 2. CRT (Tactical Scanline Monitor)
 * 3. NVG (Night Vision Goggles)
 * 4. FLIR (Forward-Looking Infrared / Ironbow Thermal)
 * 5. NOIR (High-Contrast Structural Recon)
 * 6. SNOW (Polar Relief & Contour)
 */

class VisionOptics {
    constructor() {
        this.currentMode = 'normal';
        this.intensity = 1.0;
        this.currentImage = null;
        this.gl = null;
        this.programs = {};
        this.activeProgram = null;
        this.texture = null;
        this.canvas = null;
        this.animFrame = null;
        this.startTime = performance.now();

        this.shaders = {
            normal: `
                precision highp float;
                uniform sampler2D u_image;
                varying vec2 v_uv;
                void main() {
                    gl_FragColor = texture2D(u_image, v_uv);
                }
            `,
            crt: `
                precision highp float;
                uniform sampler2D u_image;
                uniform float u_time;
                uniform vec2 u_resolution;
                uniform float u_intensity;
                varying vec2 v_uv;
                void main() {
                    vec4 color = texture2D(u_image, v_uv);
                    float scanline = sin(v_uv.y * u_resolution.y * 0.45) * 0.07 * u_intensity;
                    float r = texture2D(u_image, v_uv + vec2(0.003 * u_intensity, 0.0)).r;
                    float g = color.g;
                    float b = texture2D(u_image, v_uv - vec2(0.003 * u_intensity, 0.0)).b;
                    vec2 uv = v_uv * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.35;
                    vec3 crt = vec3(r * 0.6, g * 1.25, b * 0.6) * vignette - scanline;
                    gl_FragColor = vec4(clamp(crt, 0.0, 1.0), 1.0);
                }
            `,
            nvg: `
                precision highp float;
                uniform sampler2D u_image;
                uniform float u_time;
                uniform vec2 u_resolution;
                uniform float u_intensity;
                varying vec2 v_uv;
                float hash(vec2 p) {
                    return fract(sin(dot(p, vec2(12.9898, 78.233))) * 43758.5453);
                }
                void main() {
                    vec4 color = texture2D(u_image, v_uv);
                    float lum = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    float grain = hash(v_uv * 400.0 + sin(u_time * 12.0)) * 0.08 * u_intensity;
                    float green = lum * (1.7 * u_intensity) + grain;
                    vec2 uv = v_uv * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.35;
                    gl_FragColor = vec4(0.04, green * vignette, 0.04, 1.0);
                }
            `,
            flir: `
                precision highp float;
                uniform sampler2D u_image;
                uniform float u_intensity;
                varying vec2 v_uv;
                void main() {
                    vec4 color = texture2D(u_image, v_uv);
                    float lum = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    lum = clamp(lum * u_intensity, 0.0, 1.0);
                    vec3 thermal;
                    if (lum < 0.25) {
                        thermal = mix(vec3(0.0, 0.0, 0.25), vec3(0.5, 0.0, 0.5), lum * 4.0);
                    } else if (lum < 0.5) {
                        thermal = mix(vec3(0.5, 0.0, 0.5), vec3(1.0, 0.2, 0.0), (lum - 0.25) * 4.0);
                    } else if (lum < 0.75) {
                        thermal = mix(vec3(1.0, 0.2, 0.0), vec3(1.0, 0.8, 0.0), (lum - 0.5) * 4.0);
                    } else {
                        thermal = mix(vec3(1.0, 0.8, 0.0), vec3(1.0, 1.0, 0.95), (lum - 0.75) * 4.0);
                    }
                    gl_FragColor = vec4(thermal, 1.0);
                }
            `,
            noir: `
                precision highp float;
                uniform sampler2D u_image;
                uniform float u_intensity;
                varying vec2 v_uv;
                void main() {
                    vec4 color = texture2D(u_image, v_uv);
                    float lum = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    float contrast = clamp((lum - 0.5) * (1.8 * u_intensity) + 0.5, 0.0, 1.0);
                    vec3 noir = vec3(contrast * 0.9, contrast * 0.9, contrast * 1.05);
                    vec2 uv = v_uv * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.4;
                    gl_FragColor = vec4(noir * vignette, 1.0);
                }
            `,
            snow: `
                precision highp float;
                uniform sampler2D u_image;
                uniform float u_intensity;
                varying vec2 v_uv;
                void main() {
                    vec4 color = texture2D(u_image, v_uv);
                    float lum = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    float whiteout = lum * 0.35 + 0.65;
                    vec3 snow = vec3(whiteout * 0.92, whiteout * 0.95, whiteout);
                    gl_FragColor = vec4(snow, 1.0);
                }
            `
        };

        this.init();
    }

    init() {
        this.canvas = document.getElementById('vision-glsl-canvas');
        if (!this.canvas) return;

        this.gl = this.canvas.getContext('webgl2') || this.canvas.getContext('webgl');
        if (!this.gl) return;

        const vsSource = `
            attribute vec2 a_position;
            varying vec2 v_uv;
            void main() {
                // Flip Y for standard image texture coordinates
                v_uv = vec2((a_position.x + 1.0) * 0.5, 1.0 - (a_position.y + 1.0) * 0.5);
                gl_Position = vec4(a_position, 0.0, 1.0);
            }
        `;

        const gl = this.gl;
        const vs = this.compileShader(gl.VERTEX_SHADER, vsSource);

        // Compile all optic fragment shaders
        for (const [name, fsSource] of Object.entries(this.shaders)) {
            const fs = this.compileShader(gl.FRAGMENT_SHADER, fsSource);
            if (!fs) continue;

            const prog = gl.createProgram();
            gl.attachShader(prog, vs);
            gl.attachShader(prog, fs);
            gl.linkProgram(prog);

            if (gl.getProgramParameter(prog, gl.LINK_STATUS)) {
                this.programs[name] = prog;
            }
        }

        // Quad buffer
        const buffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
            -1, -1,  1, -1, -1,  1,
            -1,  1,  1, -1,  1,  1
        ]), gl.STATIC_DRAW);

        this.texture = gl.createTexture();
        gl.bindTexture(gl.TEXTURE_2D, this.texture);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);

        // Bind UI buttons
        document.querySelectorAll('.optic-mode-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const mode = e.currentTarget.dataset.mode;
                this.setMode(mode);
            });
        });

        // Intensity slider
        const slider = document.getElementById('optic-intensity-slider');
        if (slider) {
            slider.addEventListener('input', (e) => {
                this.intensity = parseFloat(e.target.value);
                const valEl = document.getElementById('optic-intensity-val');
                if (valEl) valEl.textContent = this.intensity.toFixed(1) + 'x';
            });
        }

        // Start render loop
        this.startRender();

        // Load procedural test feed or logo initially
        this.loadSampleImage();
    }

    compileShader(type, source) {
        const gl = this.gl;
        const s = gl.createShader(type);
        gl.shaderSource(s, source);
        gl.compileShader(s);
        if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
            console.error('[VisionOptics] Shader error:', gl.getShaderInfoLog(s));
            return null;
        }
        return s;
    }

    setMode(mode) {
        if (!this.programs[mode]) return;
        this.currentMode = mode;
        document.querySelectorAll('.optic-mode-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
    }

    loadImage(imgSource) {
        if (!this.gl || !this.texture) return;
        const gl = this.gl;

        const upload = (img) => {
            this.currentImage = img;
            // Resize canvas to match aspect ratio
            const aspect = (img.width || 600) / (img.height || 400);
            this.canvas.width = 720;
            this.canvas.height = Math.round(720 / aspect);
            gl.viewport(0, 0, this.canvas.width, this.canvas.height);

            gl.bindTexture(gl.TEXTURE_2D, this.texture);
            gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img);
        };

        if (typeof imgSource === 'string') {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => upload(img);
            img.src = imgSource;
        } else if (imgSource instanceof HTMLImageElement || imgSource instanceof HTMLCanvasElement) {
            upload(imgSource);
        }
    }

    loadSampleImage() {
        // Create an authentic procedural tactical reconnaissance test card
        const testCanvas = document.createElement('canvas');
        testCanvas.width = 800;
        testCanvas.height = 500;
        const ctx = testCanvas.getContext('2d');

        // Background terrain gradient
        const grad = ctx.createLinearGradient(0, 0, 800, 500);
        grad.addColorStop(0, '#0a1626');
        grad.addColorStop(0.5, '#1e293b');
        grad.addColorStop(1, '#0f172a');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 800, 500);

        // Structural geometric target shapes & runways
        ctx.fillStyle = '#334155';
        ctx.fillRect(100, 80, 260, 160);
        ctx.fillStyle = '#475569';
        ctx.fillRect(420, 120, 280, 80);
        ctx.fillRect(200, 280, 420, 140);

        // Warm heat points (simulating engines/heat sources)
        const heatGrad = ctx.createRadialGradient(230, 160, 10, 230, 160, 90);
        heatGrad.addColorStop(0, '#ffffff');
        heatGrad.addColorStop(0.3, '#f59e0b');
        heatGrad.addColorStop(1, 'transparent');
        ctx.fillStyle = heatGrad;
        ctx.beginPath();
        ctx.arc(230, 160, 90, 0, Math.PI * 2);
        ctx.fill();

        const heatGrad2 = ctx.createRadialGradient(520, 350, 10, 520, 350, 110);
        heatGrad2.addColorStop(0, '#fef08a');
        heatGrad2.addColorStop(0.4, '#ef4444');
        heatGrad2.addColorStop(1, 'transparent');
        ctx.fillStyle = heatGrad2;
        ctx.beginPath();
        ctx.arc(520, 350, 110, 0, Math.PI * 2);
        ctx.fill();

        // Tactical HUD markings
        ctx.strokeStyle = 'rgba(34, 211, 238, 0.8)';
        ctx.lineWidth = 2;
        ctx.strokeRect(80, 60, 640, 380);
        ctx.font = 'bold 16px monospace';
        ctx.fillStyle = '#22d3ee';
        ctx.fillText('TARGET SECTOR: OMEGA-7 // SURVEILLANCE FEED', 95, 95);
        ctx.fillText('GPS: 51.5074 N, 0.1278 W | ALT: 2,400M', 95, 425);

        this.loadImage(testCanvas);
    }

    startRender() {
        const render = (now) => {
            const gl = this.gl;
            if (!gl) return;

            const prog = this.programs[this.currentMode] || this.programs['normal'];
            if (prog) {
                gl.useProgram(prog);

                const posLoc = gl.getAttribLocation(prog, 'a_position');
                gl.enableVertexAttribArray(posLoc);
                gl.vertexAttribPointer(posLoc, 2, gl.FLOAT, false, 0, 0);

                const uTimeLoc = gl.getUniformLocation(prog, 'u_time');
                const uResLoc = gl.getUniformLocation(prog, 'u_resolution');
                const uIntLoc = gl.getUniformLocation(prog, 'u_intensity');
                const uImgLoc = gl.getUniformLocation(prog, 'u_image');

                if (uTimeLoc) gl.uniform1f(uTimeLoc, (now - this.startTime) * 0.001);
                if (uResLoc) gl.uniform2f(uResLoc, this.canvas.width, this.canvas.height);
                if (uIntLoc) gl.uniform1f(uIntLoc, this.intensity);
                if (uImgLoc) gl.uniform1i(uImgLoc, 0);

                gl.activeTexture(gl.TEXTURE0);
                gl.bindTexture(gl.TEXTURE_2D, this.texture);

                gl.drawArrays(gl.TRIANGLES, 0, 6);
            }

            this.animFrame = requestAnimationFrame(render);
        };
        this.animFrame = requestAnimationFrame(render);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.VisionOptics = new VisionOptics();
});
