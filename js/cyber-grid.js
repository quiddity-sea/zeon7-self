/**
 * CyberGrid — Full-screen hardware-accelerated 3D perspective cyber-grid.
 * Pure WebGL / GLSL fragment shader background with atmospheric horizon glow
 * and subtle mouse parallax.
 */

(function () {
    const FRAGMENT_SHADER = `
        precision highp float;
        uniform float u_time;
        uniform vec2 u_resolution;
        uniform vec2 u_mouse;
        uniform float u_intensity;

        void main() {
            vec2 uv = (gl_FragCoord.xy - 0.5 * u_resolution.xy) / u_resolution.y;

            // Horizon position modulated slightly by mouse
            float horizonY = -0.05 + (u_mouse.y - 0.5) * 0.1;
            float camPitch = uv.y - horizonY;

            vec3 color = vec3(0.012, 0.024, 0.038); // Deep cyber abyss base

            // Horizon neon glow flare
            float horizonGlow = 0.015 / (abs(camPitch) + 0.035);
            vec3 neonCyan = vec3(0.133, 0.827, 0.933);
            vec3 neonPurple = vec3(0.659, 0.333, 0.969);
            color += mix(neonCyan, neonPurple, sin(u_time * 0.4) * 0.5 + 0.5) * horizonGlow * 0.4;

            // Ground 3D Grid Plane (below horizon)
            if (camPitch < 0.0) {
                float depth = 0.85 / (-camPitch);
                
                // Parallax world coordinates
                float worldX = uv.x * depth + (u_mouse.x - 0.5) * 1.5;
                float worldZ = depth + u_time * 2.2;

                // Grid cell lines (derivatives for clean anti-aliasing)
                vec2 gridCoord = vec2(worldX * 1.8, worldZ * 0.9);
                vec2 gridDist = abs(fract(gridCoord - 0.5) - 0.5) / fwidth(gridCoord);
                float lineIntensity = 1.0 - min(min(gridDist.x, gridDist.y), 1.0);

                // Distance atmospheric falloff
                float fog = exp(-depth * 0.09);
                
                // Colorize grid lines with depth gradient
                vec3 gridColor = mix(neonCyan * 0.8, neonPurple * 0.9, sin(worldZ * 0.15) * 0.5 + 0.5);
                color += gridColor * lineIntensity * fog * 0.75;

                // Ground plane ambient reflection
                color += neonCyan * 0.06 * fog;
            } else {
                // Sky: subtle digital particle / star dust
                float starNoise = fract(sin(dot(floor(uv * 120.0), vec2(12.9898, 78.233))) * 43758.5453);
                if (starNoise > 0.992) {
                    float twinkle = sin(u_time * 3.0 + starNoise * 10.0) * 0.5 + 0.5;
                    color += vec3(0.5, 0.8, 1.0) * twinkle * (camPitch * 0.8);
                }
            }

            // Subtle vignette around borders
            vec2 screenUv = gl_FragCoord.xy / u_resolution.xy;
            float vignette = screenUv.x * screenUv.y * (1.0 - screenUv.x) * (1.0 - screenUv.y);
            vignette = clamp(pow(16.0 * vignette, 0.35), 0.0, 1.0);

            gl_FragColor = vec4(color * vignette, 1.0);
        }
    `;

    function initCyberGrid() {
        // Prevent duplicate creation
        if (document.getElementById('cyber-bg-canvas')) return;

        // Check reduced motion preference
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.id = 'cyber-bg-canvas';
        canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            pointer-events: none;
            opacity: 0.65;
            transition: opacity 0.6s ease;
        `;
        document.body.prepend(canvas);

        if (typeof GLSLCanvas !== 'undefined') {
            new GLSLCanvas(canvas, FRAGMENT_SHADER, {
                trackMouse: true,
                autoResize: true,
                maxDpr: 1.5
            });
        } else {
            // Load glsl-core.js dynamically if not already on the page
            const script = document.createElement('script');
            script.src = 'js/glsl-core.js';
            script.onload = () => {
                new GLSLCanvas(canvas, FRAGMENT_SHADER, {
                    trackMouse: true,
                    autoResize: true,
                    maxDpr: 1.5
                });
            };
            document.head.appendChild(script);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCyberGrid);
    } else {
        initCyberGrid();
    }
})();
