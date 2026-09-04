/**
 * Zeon7 Theme Switcher
 * Handles light/dark mode toggle with localStorage persistence. Defaults to dark (Cybernetic HUD).
 */

(function () {
    'use strict';

    const STORAGE_KEY = 'zeon7-theme';
    const THEME_ATTRIBUTE = 'data-theme';
    const THEMES = {
        LIGHT: 'light',
        DARK: 'dark'
    };

    /**
     * Get current theme from localStorage (defaults to DARK)
     */
    function getInitialTheme() {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored && (stored === THEMES.LIGHT || stored === THEMES.DARK)) {
            return stored;
        }
        return THEMES.DARK;
    }

    /**
     * Apply theme to document
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute(THEME_ATTRIBUTE, theme);
    }

    /**
     * Save theme to localStorage
     */
    function saveTheme(theme) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (error) {
            console.warn('Failed to save theme preference:', error);
        }
    }

    /**
     * Toggle between light and dark themes
     */
    function toggleTheme() {
        const current = document.documentElement.getAttribute(THEME_ATTRIBUTE);
        const newTheme = current === THEMES.LIGHT ? THEMES.DARK : THEMES.LIGHT;

        applyTheme(newTheme);
        saveTheme(newTheme);
        updateThemeToggles(newTheme);

        window.dispatchEvent(new CustomEvent('themechange', {
            detail: { theme: newTheme }
        }));
    }

    /**
     * Update all theme toggle buttons
     */
    function updateThemeToggles(theme) {
        const toggles = document.querySelectorAll('[data-theme-toggle]');
        toggles.forEach(toggle => {
            const icon = toggle.querySelector('[data-theme-icon]');
            if (icon) {
                icon.textContent = theme === THEMES.DARK ? '☀️' : '🌙';
            }

            toggle.setAttribute('aria-label',
                theme === THEMES.DARK ? 'Switch to light mode' : 'Switch to dark mode'
            );
        });
    }

    /**
     * Initialise theme on page load
     */
    function init() {
        const initialTheme = getInitialTheme();
        applyTheme(initialTheme);
        updateThemeToggles(initialTheme);

        const toggles = document.querySelectorAll('[data-theme-toggle]');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', toggleTheme);
        });
    }

    if (document.readyState === 'loading') {
        applyTheme(getInitialTheme());
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.Zeon7Theme = {
        toggle: toggleTheme,
        set: function (theme) {
            if (theme === THEMES.LIGHT || theme === THEMES.DARK) {
                applyTheme(theme);
                saveTheme(theme);
                updateThemeToggles(theme);
            }
        },
        get: function () {
            return document.documentElement.getAttribute(THEME_ATTRIBUTE) || THEMES.DARK;
        }
    };
})();
