/**
 * ZEON7 KINETIC GSAP ANIMATION ENGINE
 * Parity with Plutus Financial HUD & GuruShots Cybernetic Matrix
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.ZeonAnimations = factory();
        root.HUDAnimations = root.ZeonAnimations;
    }
}(typeof self !== 'undefined' ? self : this, function () {

    const ZeonAnimations = {
        /**
         * Orchestrated Cybernetic HUD Boot Sequence
         */
        playBootSequence(onComplete) {
            if (typeof gsap === 'undefined') {
                if (onComplete) onComplete();
                return;
            }

            const tl = gsap.timeline({
                defaults: { ease: "power3.out" },
                onComplete: () => {
                    if (onComplete) onComplete();
                }
            });

            const ifExists = (selector) => {
                const nodes = document.querySelectorAll(selector);
                return (nodes && nodes.length > 0) ? nodes : null;
            };

            // 1. Scanline flash
            const scanline = ifExists(".hud-scanline");
            if (scanline) {
                tl.fromTo(scanline, 
                    { opacity: 0 }, 
                    { opacity: 1, duration: 0.4, ease: "rough({ template: none.out, strength: 1.5, points: 15 })" }
                );
            }

            // 2. Header drop
            const header = ifExists(".header-bar, .public-nav");
            if (header) {
                tl.from(header, { y: -25, opacity: 0, duration: 0.4 }, "-=0.1");
            }

            // 3. Staggered 3D unfolding of HUD Cards, Stat cards & Action panels (DO NOT TOUCH SIDEBAR)
            const cards = ifExists(".dashboard-container .hud-border, .dashboard-container .stat-card, .dashboard-container .action-card, .hero-section, #latest-posts .post-card, #posts-grid .post-card, .article-container");
            if (cards) {
                tl.from(cards, {
                    duration: 0.6,
                    y: 25,
                    opacity: 0,
                    rotationX: -6,
                    transformOrigin: "top center",
                    stagger: 0.06,
                    ease: "back.out(1.15)"
                }, "-=0.2");
            }

            // 4. 4-Corner Crosshair Ignition
            const corners = ifExists(".hud-corner-tr, .hud-corner-bl");
            if (corners) {
                tl.fromTo(corners, 
                    { scale: 0, opacity: 0 }, 
                    { scale: 1, opacity: 1, stagger: 0.02, duration: 0.25, ease: "elastic.out(1.4, 0.4)" }, 
                    "-=0.25"
                );
            }

            this.initRollingCounters();
            return tl;
        },

        initRollingCounters() {
            document.querySelectorAll('[data-count], [data-val], .stat-val').forEach(el => {
                const rawVal = el.getAttribute('data-count') || el.getAttribute('data-val') || el.textContent;
                const num = parseFloat(rawVal.replace(/[^0-9.-]+/g, ''));
                if (!isNaN(num) && num > 0) {
                    this.animateNumber(el, 0, num, 1.2);
                }
            });
        },

        animateNumber(element, startVal, endVal, duration = 1.0, formatter = null) {
            if (!element) return;
            const target = Number(endVal) || 0;
            const start = Number(startVal) || 0;

            if (typeof gsap === 'undefined') {
                element.textContent = formatter ? formatter(target) : new Intl.NumberFormat('en-US').format(Math.round(target));
                return;
            }

            const obj = { val: start };
            gsap.to(obj, {
                val: target,
                duration: duration,
                ease: "power2.out",
                onUpdate: () => {
                    const rounded = Math.round(obj.val);
                    if (formatter) {
                        element.textContent = formatter(rounded);
                    } else if (element.id === 'tokenDisplay' || element.classList.contains('header-badge-1')) {
                        element.textContent = 'TOKENS: ' + new Intl.NumberFormat('en-US').format(rounded);
                    } else {
                        element.textContent = new Intl.NumberFormat('en-US').format(rounded);
                    }
                }
            });
        },

        init3DTilt(selector = '.hud-border, .stat-card, .action-card, .post-card') {
            if (typeof gsap === 'undefined') return;

            document.querySelectorAll(selector).forEach(card => {
                card.removeEventListener('mousemove', card._tiltMove);
                card.removeEventListener('mouseleave', card._tiltLeave);

                card._tiltMove = (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    const maxTilt = 4.0;
                    const rotateX = (-y / (rect.height / 2)) * maxTilt;
                    const rotateY = (x / (rect.width / 2)) * maxTilt;

                    gsap.to(card, {
                        rotationX: rotateX,
                        rotationY: rotateY,
                        transformPerspective: 1200,
                        ease: "power1.out",
                        duration: 0.3,
                        overwrite: "auto"
                    });
                };

                card._tiltLeave = () => {
                    gsap.to(card, {
                        rotationX: 0,
                        rotationY: 0,
                        ease: "power2.out",
                        duration: 0.5,
                        overwrite: "auto"
                    });
                };

                card.addEventListener('mousemove', card._tiltMove);
                card.addEventListener('mouseleave', card._tiltLeave);
            });
        },

        staggerIn(selector, delay = 0, yOffset = 20) {
            if (typeof gsap === 'undefined') return;
            const elements = document.querySelectorAll(selector);
            if (!elements || elements.length === 0) return;
            return gsap.fromTo(elements, 
                { y: yOffset, opacity: 0 },
                { y: 0, opacity: 1, stagger: 0.06, duration: 0.5, ease: "power2.out", delay: delay }
            );
        },

        animateModalOpen(modalEl) {
            if (!modalEl) return;
            if (typeof gsap === 'undefined') {
                modalEl.classList.add('active');
                return;
            }

            modalEl.classList.add('active');
            const inner = modalEl.querySelector('.modal') || modalEl;
            gsap.killTweensOf(inner);
            gsap.fromTo(inner,
                { scale: 0.85, opacity: 0, y: 20 },
                { scale: 1, opacity: 1, y: 0, duration: 0.35, ease: "back.out(1.4)" }
            );
        },

        animateModalClose(modalEl, onComplete) {
            if (!modalEl) return;
            if (typeof gsap === 'undefined') {
                modalEl.classList.remove('active');
                if (onComplete) onComplete();
                return;
            }

            const inner = modalEl.querySelector('.modal') || modalEl;
            gsap.killTweensOf(inner);
            gsap.to(inner, {
                scale: 0.9,
                opacity: 0,
                y: 15,
                duration: 0.2,
                ease: "power2.in",
                onComplete: () => {
                    modalEl.classList.remove('active');
                    if (onComplete) onComplete();
                }
            });
        }
    };

    if (typeof document !== 'undefined') {
        document.addEventListener('DOMContentLoaded', () => {
            ZeonAnimations.playBootSequence();
            ZeonAnimations.init3DTilt();
        });
    }

    return ZeonAnimations;
}));
