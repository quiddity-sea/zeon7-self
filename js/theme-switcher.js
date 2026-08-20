/**
 * Zeon7 Theme Switcher
 * 
 * Handles light/dark mode toggle with localStorage persistence
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
     * Get the current theme from localStorage or system preference
     */
    function getInitialTheme() {
        // Check localStorage first
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored && (stored === THEMES.LIGHT || stored === THEMES.DARK)) {
            return stored;
        }

        // Fall back to system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return THEMES.DARK;
        }

        return THEMES.LIGHT;
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
        const newTheme = current === THEMES.DARK ? THEMES.LIGHT : THEMES.DARK;

        applyTheme(newTheme);
        saveTheme(newTheme);
        updateThemeToggles(newTheme);

        // Dispatch custom event for theme change
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
                // Update icon (sun for light mode, moon for dark mode)
                icon.textContent = theme === THEMES.DARK ? '☀️' : '🌙';
            }

            // Update aria-label
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

        // Attach event listeners to all theme toggles
        const toggles = document.querySelectorAll('[data-theme-toggle]');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', toggleTheme);
        });

        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Only apply if user hasn't set a preference
                if (!localStorage.getItem(STORAGE_KEY)) {
                    const theme = e.matches ? THEMES.DARK : THEMES.LIGHT;
                    applyTheme(theme);
                    updateThemeToggles(theme);
                }
            });
        }
    }

    // Initialise immediately to avoid flash of wrong theme
    if (document.readyState === 'loading') {
        // Apply theme as early as possible
        applyTheme(getInitialTheme());
        // Full initialization after DOM is ready
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Export functions for external use
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
            return document.documentElement.getAttribute(THEME_ATTRIBUTE) || THEMES.LIGHT;
        }
    };

})();
