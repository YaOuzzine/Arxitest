/**
 * Arxitest Dashboard Charts
 *
 * This file contains the JavaScript for initializing and configuring
 * charts on the dashboard.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Listen for theme changes to update chart colors
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // We need to rebuild charts when the theme changes
            // Our components will handle this via their individual scripts
            setTimeout(() => {
                // Dispatch a custom event that chart components can listen for
                document.dispatchEvent(new CustomEvent('theme-changed'));
            }, 50);
        });
    }

    // Listen for window resize events to properly resize charts
    window.addEventListener('resize', function() {
        if (typeof Chart !== 'undefined') {
            Chart.instances.forEach(chart => {
                chart.resize();
            });
        }
    });
});

/**
 * Updates chart colors based on the current theme
 *
 * @param {Chart} chart - The Chart.js instance to update
 * @returns {void}
 */
function updateChartTheme(chart) {
    if (!chart) return;

    const isDarkMode = document.documentElement.classList.contains('dark');

    // Update legend colors
    if (chart.options.plugins && chart.options.plugins.legend) {
        chart.options.plugins.legend.labels.color = isDarkMode ? 'rgb(229, 231, 235)' : 'rgb(55, 65, 81)';
    }

    // Update axis colors
    if (chart.options.scales) {
        Object.values(chart.options.scales).forEach(scale => {
            if (scale.ticks) {
                scale.ticks.color = isDarkMode ? 'rgb(156, 163, 175)' : 'rgb(107, 114, 128)';
            }

            if (scale.grid) {
                scale.grid.color = isDarkMode ? 'rgba(75, 85, 99, 0.2)' : 'rgba(209, 213, 219, 0.5)';
            }
        });
    }

    chart.update();
}

/**
 * Format number of seconds into a human-readable duration
 *
 * @param {number} seconds - Number of seconds
 * @returns {string} Formatted duration
 */
function formatDuration(seconds) {
    if (seconds < 60) {
        return `${seconds}s`;
    } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}m ${remainingSeconds}s`;
    } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }
}

/**
 * Format a date for display in charts
 *
 * @param {Date|string} date - Date to format
 * @param {string} format - Format style ('short', 'long', 'time')
 * @returns {string} Formatted date
 */
function formatDate(date, format = 'short') {
    if (!date) return 'N/A';

    const d = new Date(date);

    switch (format) {
        case 'short':
            return d.toLocaleDateString();
        case 'long':
            return d.toLocaleDateString(undefined, {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        case 'time':
            return d.toLocaleTimeString(undefined, {
                hour: '2-digit',
                minute: '2-digit'
            });
        case 'datetime':
            return d.toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        default:
            return d.toLocaleString();
    }
}
