/**
 * HUDGlitch — Full-screen hardware-accelerated Holographic Glitch & Transition Overlay.
 * Triggerable programmatically during boot sequences, tab switches, and system resets.
 */

(function () {
    const GLITCH_FRAGMENT_SHADER = `
        precision highp float;
        uniform float u_time;
        uniform vec2 u_resolution;
        uniform float u_progress; // 0.0 -> 1.0 during glitch pulse
        uniform float u_intensity;

        // Pseudo-random generator
        float hash(vec2 p) {
            return fract(sin(dot(p, vec2(12.9898, 78.233))) * 43758.5453);
        }

        void main() {
            vec2 uv = gl_FragCoord.xy / u_resolution.xy;

            // Decay envelope (spikes fast, falls off)
            float envelope = sin(u_progress * 3.14159) * u_intensity;

            // Horizontal raster slice displacement
            float sliceNoise = hash(vec2(floor(uv.y * 24.0), floor(u_time * 15.0)));
            float shift = 0.0;
            if (sliceNoise > 0.7) {
                shift = (sliceNoise - 0.7) * 0.15 * envelope;
            }

            // CRT scanline bands
            float scanline = sin(uv.y * u_resolution.y * 0.5) * 0.08 * envelope;

            // Chromatic separation (RGB displacement)
            float rVal = step(0.5, hash(uv + vec2(shift, 0.01) + u_time));
            float gVal = step(0.5, hash(uv + vec2(-shift * 0.5, -0.01) + u_time));
            float bVal = step(0.5, hash(uv + vec2(shift * 1.2, 0.0) + u_time));

            // Cyber cyan and magenta flash tints
            vec3 glitchColor = vec3(0.0);
            glitchColor.r = rVal * 0.8 + scanline;
            glitchColor.g = gVal * 0.95 + scanline;
            glitchColor.b = bVal * 1.0;

            // Electrical static discharge lines
            float discharge = step(0.985, hash(vec2(u_time * 20.0, floor(uv.y * 60.0))));
            glitchColor += vec3(0.133, 0.827, 0.933) * discharge * envelope * 1.5;

            // Alpha transparency matches envelope
            float alpha = clamp(envelope * 0.65 + discharge * 0.35, 0.0, 0.95);

            gl_FragColor = vec4(glitchColor, alpha);
        }
    `;

    const HUDGlitch = {
        canvas: null,
        gl: null,
        program: null,
        animFrame: null,
        isGlitching: false,
        startTime: 0,
        duration: 350,
        intensity: 1.0,

        init() {
            if (document.getElementById('hud-glitch-canvas')) return;

            this.canvas = document.createElement('canvas');
            this.canvas.id = 'hud-glitch-canvas';
            this.canvas.style.cssText = `
                position: fixed;
                inset: 0;
                width: 100vw;
                height: 100vh;
                z-index: 99999999;
                pointer-events: none;
                opacity: 0;
                display: none;
            `;
            document.body.appendChild(this.canvas);

            this.gl = this.canvas.getContext('webgl2') || this.canvas.getContext('webgl');
            if (!this.gl) return;

            const vsSource = `
                attribute vec2 a_position;
                void main() {
                    gl_Position = vec4(a_position, 0.0, 1.0);
                }
            `;

            const vs = this.compile(this.gl.VERTEX_SHADER, vsSource);
            const fs = this.compile(this.gl.FRAGMENT_SHADER, GLITCH_FRAGMENT_SHADER);
            if (!vs || !fs) return;

            this.program = this.gl.createProgram();
            this.gl.attachShader(this.program, vs);
            this.gl.attachShader(this.program, fs);
            this.gl.linkProgram(this.program);

            // Quad buffer
            const buffer = this.gl.createBuffer();
            this.gl.bindBuffer(this.gl.ARRAY_BUFFER, buffer);
            this.gl.bufferData(this.gl.ARRAY_BUFFER, new Float32Array([
                -1, -1,  1, -1, -1,  1,
                -1,  1,  1, -1,  1,  1
            ]), this.gl.STATIC_DRAW);

            const posLoc = this.gl.getAttribLocation(this.program, 'a_position');
            this.gl.enableVertexAttribArray(posLoc);
            this.gl.vertexAttribPointer(posLoc, 2, this.gl.FLOAT, false, 0, 0);

            this.resize();
            window.addEventListener('resize', () => this.resize());
        },

        compile(type, src) {
            const s = this.gl.createShader(type);
            this.gl.shaderSource(s, src);
            this.gl.compileShader(s);
            if (!this.gl.getShaderParameter(s, this.gl.COMPILE_STATUS)) {
                console.error('[HUDGlitch] Shader error:', this.gl.getShaderInfoLog(s));
                return null;
            }
            return s;
        },

        resize() {
            if (!this.canvas || !this.gl) return;
            const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
            this.canvas.width = Math.floor(window.innerWidth * dpr);
            this.canvas.height = Math.floor(window.innerHeight * dpr);
            this.gl.viewport(0, 0, this.canvas.width, this.canvas.height);
        },

        trigger(durationMs = 350, intensity = 1.0) {
            if (!this.gl || !this.program) this.init();
            if (!this.gl || !this.program) return;

            // Obey accessibility
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            this.duration = durationMs;
            this.intensity = intensity;
            this.startTime = performance.now();
            this.isGlitching = true;
            this.canvas.style.display = 'block';
            this.canvas.style.opacity = '1';

            const gl = this.gl;
            const prog = this.program;
            const uTimeLoc = gl.getUniformLocation(prog, 'u_time');
            const uResLoc = gl.getUniformLocation(prog, 'u_resolution');
            const uProgLoc = gl.getUniformLocation(prog, 'u_progress');
            const uIntensityLoc = gl.getUniformLocation(prog, 'u_intensity');

            const loop = (now) => {
                const elapsed = now - this.startTime;
                const progress = Math.min(elapsed / this.duration, 1.0);

                gl.useProgram(prog);
                if (uTimeLoc) gl.uniform1f(uTimeLoc, now * 0.001);
                if (uResLoc) gl.uniform2f(uResLoc, this.canvas.width, this.canvas.height);
                if (uProgLoc) gl.uniform1f(uProgLoc, progress);
                if (uIntensityLoc) gl.uniform1f(uIntensityLoc, this.intensity);

                gl.drawArrays(gl.TRIANGLES, 0, 6);

                if (progress < 1.0 && this.isGlitching) {
                    this.animFrame = requestAnimationFrame(loop);
                } else {
                    this.stop();
                }
            };

            if (this.animFrame) cancelAnimationFrame(this.animFrame);
            this.animFrame = requestAnimationFrame(loop);
        },

        stop() {
            this.isGlitching = false;
            if (this.animFrame) cancelAnimationFrame(this.animFrame);
            if (this.canvas) {
                this.canvas.style.opacity = '0';
                this.canvas.style.display = 'none';
            }
        }
    };

    window.HUDGlitch = HUDGlitch;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => HUDGlitch.init());
    } else {
        HUDGlitch.init();
    }
})();
