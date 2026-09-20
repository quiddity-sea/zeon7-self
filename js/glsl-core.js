/**
 * GLSLCanvas — Lightweight, zero-dependency WebGL Fragment Shader Engine.
 * Executes pure GLSL shaders on full-screen or element canvases with auto-resize,
 * standard uniforms (u_time, u_resolution, u_mouse, u_intensity), and power management.
 */
class GLSLCanvas {
    constructor(canvas, fragmentShaderSource, options = {}) {
        this.canvas = typeof canvas === 'string' ? document.getElementById(canvas) : canvas;
        if (!this.canvas) return;

        this.options = Object.assign({
            autoResize: true,
            maxDpr: 2,
            trackMouse: false,
            intensity: 1.0,
            observeVisibility: true,
            reducedMotionCheck: true
        }, options);

        this.gl = this.canvas.getContext('webgl2') || this.canvas.getContext('webgl');
        if (!this.gl) {
            console.warn('[GLSLCanvas] WebGL not supported.');
            return;
        }

        this.fragmentSource = fragmentShaderSource;
        this.program = null;
        this.uniforms = {};
        this.textures = {};
        this.mouse = { x: 0.5, y: 0.5, targetX: 0.5, targetY: 0.5 };
        this.intensity = this.options.intensity;
        this.startTime = performance.now();
        this.animFrame = null;
        this.isRunning = false;
        this.isVisible = true;

        this.vertexSource = `
            attribute vec2 a_position;
            varying vec2 v_uv;
            void main() {
                v_uv = (a_position + 1.0) * 0.5;
                gl_Position = vec4(a_position, 0.0, 1.0);
            }
        `;

        this.init();
    }

    init() {
        const gl = this.gl;

        // Compile vertex shader
        const vs = this.compileShader(gl.VERTEX_SHADER, this.vertexSource);
        const fs = this.compileShader(gl.FRAGMENT_SHADER, this.fragmentSource);
        if (!vs || !fs) return;

        this.program = gl.createProgram();
        gl.attachShader(this.program, vs);
        gl.attachShader(this.program, fs);
        gl.linkProgram(this.program);

        if (!gl.getProgramParameter(this.program, gl.LINK_STATUS)) {
            console.error('[GLSLCanvas] Program link error:', gl.getProgramInfoLog(this.program));
            return;
        }

        gl.useProgram(this.program);

        // Quad buffer: two triangles covering clip space
        const quad = new Float32Array([
            -1.0, -1.0,
             1.0, -1.0,
            -1.0,  1.0,
            -1.0,  1.0,
             1.0, -1.0,
             1.0,  1.0
        ]);
        this.buffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, this.buffer);
        gl.bufferData(gl.ARRAY_BUFFER, quad, gl.STATIC_DRAW);

        const posLoc = gl.getAttribLocation(this.program, 'a_position');
        gl.enableVertexAttribArray(posLoc);
        gl.vertexAttribPointer(posLoc, 2, gl.FLOAT, false, 0, 0);

        // Cache uniform locations
        this.uTimeLoc = gl.getUniformLocation(this.program, 'u_time');
        this.uResLoc = gl.getUniformLocation(this.program, 'u_resolution');
        this.uMouseLoc = gl.getUniformLocation(this.program, 'u_mouse');
        this.uIntensityLoc = gl.getUniformLocation(this.program, 'u_intensity');

        // Events & Observers
        if (this.options.autoResize) {
            this.handleResize = () => this.resize();
            window.addEventListener('resize', this.handleResize);
            this.resize();
        }

        if (this.options.trackMouse) {
            this.handleMouseMove = (e) => {
                const rect = this.canvas.getBoundingClientRect();
                this.mouse.targetX = (e.clientX - rect.left) / (rect.width || 1);
                this.mouse.targetY = 1.0 - (e.clientY - rect.top) / (rect.height || 1);
            };
            window.addEventListener('mousemove', this.handleMouseMove);
        }

        if (this.options.observeVisibility && 'IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    this.isVisible = entry.isIntersecting;
                    if (this.isVisible && !this.isRunning) {
                        this.start();
                    } else if (!this.isVisible && this.isRunning) {
                        this.stop();
                    }
                });
            }, { threshold: 0.05 });
            this.observer.observe(this.canvas);
        }

        this.start();
    }

    compileShader(type, source) {
        const gl = this.gl;
        const shader = gl.createShader(type);
        // Prefix precision qualifier for fragment shaders
        const fullSource = (type === gl.FRAGMENT_SHADER && !source.includes('precision '))
            ? 'precision highp float;\n' + source
            : source;

        gl.shaderSource(shader, fullSource);
        gl.compileShader(shader);

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            console.error('[GLSLCanvas] Shader compile error:', gl.getShaderInfoLog(shader));
            gl.deleteShader(shader);
            return null;
        }
        return shader;
    }

    resize() {
        if (!this.canvas || !this.gl) return;
        const dpr = Math.min(window.devicePixelRatio || 1, this.options.maxDpr);
        const w = Math.floor((this.canvas.clientWidth || window.innerWidth) * dpr);
        const h = Math.floor((this.canvas.clientHeight || window.innerHeight) * dpr);

        if (this.canvas.width !== w || this.canvas.height !== h) {
            this.canvas.width = w;
            this.canvas.height = h;
            this.gl.viewport(0, 0, w, h);
        }
    }

    setIntensity(val) {
        this.intensity = val;
    }

    setUniform(name, type, ...values) {
        if (!this.gl || !this.program) return;
        this.gl.useProgram(this.program);
        let loc = this.uniforms[name];
        if (loc === undefined) {
            loc = this.gl.getUniformLocation(this.program, name);
            this.uniforms[name] = loc;
        }
        if (!loc) return;

        if (type === '1f') this.gl.uniform1f(loc, values[0]);
        else if (type === '2f') this.gl.uniform2f(loc, values[0], values[1]);
        else if (type === '3f') this.gl.uniform3f(loc, values[0], values[1], values[2]);
        else if (type === '1i') this.gl.uniform1i(loc, values[0]);
    }

    loadTexture(imageOrUrl, uniformName = 'u_texture', unit = 0) {
        const gl = this.gl;
        if (!gl) return;

        const texture = gl.createTexture();
        gl.activeTexture(gl.TEXTURE0 + unit);
        gl.bindTexture(gl.TEXTURE_2D, texture);

        // Default 1x1 black pixel while loading
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, 1, 1, 0, gl.RGBA, gl.UNSIGNED_BYTE, new Uint8Array([0, 0, 0, 255]));

        const setupTex = (img) => {
            gl.useProgram(this.program);
            gl.activeTexture(gl.TEXTURE0 + unit);
            gl.bindTexture(gl.TEXTURE_2D, texture);
            gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img);
            gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
            gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
            gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
            gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);

            const loc = gl.getUniformLocation(this.program, uniformName);
            if (loc) gl.uniform1i(loc, unit);
        };

        if (typeof imageOrUrl === 'string') {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => setupTex(img);
            img.src = imageOrUrl;
        } else if (imageOrUrl instanceof HTMLImageElement || imageOrUrl instanceof HTMLCanvasElement) {
            setupTex(imageOrUrl);
        }
    }

    start() {
        if (this.isRunning) return;
        this.isRunning = true;
        const render = (time) => {
            if (!this.isRunning) return;

            const seconds = (time - this.startTime) * 0.001;
            const gl = this.gl;

            gl.useProgram(this.program);

            if (this.uTimeLoc) gl.uniform1f(this.uTimeLoc, seconds);
            if (this.uResLoc) gl.uniform2f(this.uResLoc, this.canvas.width, this.canvas.height);
            if (this.uIntensityLoc) gl.uniform1f(this.uIntensityLoc, this.intensity);

            if (this.uMouseLoc) {
                // Smooth mouse interpolation
                this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.08;
                this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.08;
                gl.uniform2f(this.uMouseLoc, this.mouse.x, this.mouse.y);
            }

            gl.drawArrays(gl.TRIANGLES, 0, 6);

            this.animFrame = requestAnimationFrame(render);
        };
        this.animFrame = requestAnimationFrame(render);
    }

    stop() {
        this.isRunning = false;
        if (this.animFrame) {
            cancelAnimationFrame(this.animFrame);
            this.animFrame = null;
        }
    }

    destroy() {
        this.stop();
        if (this.observer) this.observer.disconnect();
        if (this.handleResize) window.removeEventListener('resize', this.handleResize);
        if (this.handleMouseMove) window.removeEventListener('mousemove', this.handleMouseMove);
        if (this.gl && this.buffer) this.gl.deleteBuffer(this.buffer);
        if (this.gl && this.program) this.gl.deleteProgram(this.program);
    }
}

if (typeof window !== 'undefined') {
    window.GLSLCanvas = GLSLCanvas;
}
