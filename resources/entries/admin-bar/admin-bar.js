/**
 * WP Statistics — Admin Bar Widget
 *
 * Lightweight vanilla JS for hover/click popup interaction.
 * Popup lives outside #wpadminbar (portal pattern) and is positioned via JS.
 * Includes tab switching and full-width sparkline rendering.
 * Exposes window.wpStatisticsAdminBar for premium extension.
 */
(function () {
    'use strict';

    var SHOW_DELAY = 250;
    var HIDE_DELAY = 400;
    var MOBILE_BP = 782;

    var trigger, popup, showTimeout, hideTimeout;

    function init() {
        trigger = document.getElementById('wp-admin-bar-wp-statistics');
        if (!trigger) {
            return;
        }

        popup = document.getElementById('wps-popup');
        if (!popup) {
            return;
        }

        // Hover
        trigger.addEventListener('mouseenter', scheduleShow);
        trigger.addEventListener('mouseleave', scheduleHide);
        popup.addEventListener('mouseenter', cancelHide);
        popup.addEventListener('mouseleave', scheduleHide);

        // Click toggle (accessibility)
        trigger.addEventListener('click', onClickToggle);

        // Close on outside click
        document.addEventListener('click', onOutsideClick);

        // ESC to close
        document.addEventListener('keydown', onKeyDown);

        // Reposition on resize
        window.addEventListener('resize', onResize);

        // Initialize tabs
        initTabs();

        // Render overview sparkline from data
        var data = window.wpsAdminBarData;
        if (data && data.sparkline) {
            renderSparkline('wps-sparkline-overview', data.sparkline);
        }

        // Expose API for premium
        window.wpStatisticsAdminBar = {
            show: show,
            hide: hide,
            getTrigger: function () { return trigger; },
            getPopup: function () { return popup; },
            renderSparkline: renderSparkline
        };
    }

    /**
     * Initialize tab switching.
     */
    function initTabs() {
        var tabs = popup.querySelectorAll('.wps-tab:not(.disabled)');
        var i;

        for (i = 0; i < tabs.length; i++) {
            tabs[i].addEventListener('click', onTabClick);
        }
    }

    /**
     * Handle tab click.
     */
    function onTabClick(e) {
        var clickedTab = e.currentTarget;
        var tabId = clickedTab.getAttribute('data-tab');
        if (!tabId) {
            return;
        }

        // Update active tab
        var allTabs = popup.querySelectorAll('.wps-tab');
        var i;
        for (i = 0; i < allTabs.length; i++) {
            allTabs[i].classList.remove('active');
        }
        clickedTab.classList.add('active');

        // Show/hide content panels
        var overviewPanel = document.getElementById('wps-tab-overview');
        var thisPagePanel = document.getElementById('wps-tab-this-page');

        if (tabId === 'overview') {
            if (overviewPanel) overviewPanel.style.display = '';
            if (thisPagePanel) thisPagePanel.style.display = 'none';
        } else if (tabId === 'this-page') {
            if (overviewPanel) overviewPanel.style.display = 'none';
            if (thisPagePanel) thisPagePanel.style.display = '';
        }
    }

    /**
     * Render a full-width sparkline SVG into the given container.
     *
     * @param {string} containerId   ID of the .wps-sparkline container element.
     * @param {number[]} dataPoints  Array of numeric values.
     */
    function renderSparkline(containerId, dataPoints) {
        if (!dataPoints || dataPoints.length < 2) {
            return;
        }

        var container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        // Clear any existing content
        container.innerHTML = '';

        // Use container's actual width for full-width sparkline
        var width = container.offsetWidth;
        if (width <= 0) {
            width = 340; // fallback: 380px popup - 40px padding
        }
        var height = 32;
        var padding = 2;

        var spark = generateSparkline(dataPoints, width, height, padding);

        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
        svg.setAttribute('preserveAspectRatio', 'none');
        svg.style.display = 'block';
        svg.style.width = '100%';
        svg.style.height = '100%';

        var area = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        area.setAttribute('class', 'wps-sparkline-area');
        area.setAttribute('d', spark.area);

        var line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        line.setAttribute('class', 'wps-sparkline-line');
        line.setAttribute('d', spark.line);

        var dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dot.setAttribute('class', 'wps-sparkline-dot');
        dot.setAttribute('cx', spark.lastX);
        dot.setAttribute('cy', spark.lastY);
        dot.setAttribute('r', '2');

        svg.appendChild(area);
        svg.appendChild(line);
        svg.appendChild(dot);
        container.appendChild(svg);
    }

    /**
     * Generate sparkline SVG path data from an array of values.
     */
    function generateSparkline(data, width, height, padding) {
        var max = -Infinity;
        var min = Infinity;
        var i;

        for (i = 0; i < data.length; i++) {
            if (data[i] > max) max = data[i];
            if (data[i] < min) min = data[i];
        }

        var range = max - min || 1;
        var points = [];

        for (i = 0; i < data.length; i++) {
            var x = (i / (data.length - 1)) * width;
            var y = padding + ((max - data[i]) / range) * (height - padding * 2);
            points.push(x.toFixed(1) + ',' + y.toFixed(1));
        }

        var linePath = 'M' + points.join(' L');
        var lastPoint = points[points.length - 1].split(',');

        var areaPath = linePath +
            ' L' + width + ',' + height +
            ' L0,' + height + ' Z';

        return {
            line: linePath,
            area: areaPath,
            lastX: lastPoint[0],
            lastY: lastPoint[1]
        };
    }

    function positionPopup() {
        var rect = trigger.getBoundingClientRect();
        var isMobile = window.innerWidth <= MOBILE_BP;

        if (isMobile) {
            popup.style.top = rect.bottom + 'px';
            popup.style.left = '0';
            popup.style.right = '0';
            popup.style.width = '100%';
        } else {
            popup.style.top = rect.bottom + 'px';
            popup.style.width = '';
            popup.style.right = '';

            if (document.documentElement.dir === 'rtl') {
                popup.style.left = '';
                popup.style.right = (window.innerWidth - rect.right) + 'px';
            } else {
                popup.style.left = rect.left + 'px';
            }
        }
    }

    function scheduleShow() {
        clearTimeout(hideTimeout);
        showTimeout = setTimeout(show, SHOW_DELAY);
    }

    function scheduleHide() {
        clearTimeout(showTimeout);
        hideTimeout = setTimeout(hide, HIDE_DELAY);
    }

    function cancelHide() {
        clearTimeout(hideTimeout);
    }

    function show() {
        clearTimeout(hideTimeout);
        positionPopup();
        popup.classList.add('is-visible');
        trigger.setAttribute('aria-expanded', 'true');
    }

    function hide() {
        clearTimeout(showTimeout);
        popup.classList.remove('is-visible');
        trigger.setAttribute('aria-expanded', 'false');
    }

    function onClickToggle(e) {
        // Don't toggle when clicking footer link
        if (e.target.closest('.wps-footer a')) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        if (popup.classList.contains('is-visible')) {
            hide();
        } else {
            show();
        }
    }

    function onOutsideClick(e) {
        if (trigger && !trigger.contains(e.target) && !popup.contains(e.target)) {
            hide();
        }
    }

    function onKeyDown(e) {
        if (e.key === 'Escape') {
            hide();
        }
    }

    function onResize() {
        if (popup.classList.contains('is-visible')) {
            positionPopup();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
