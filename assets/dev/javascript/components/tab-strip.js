/**
 * Sideways-scrolling strips on phones and narrow cards: page tab strips (ul.wps-tabs) and table
 * wrappers (.o-table-wrapper).
 *
 * - The current tab is scrolled into view on load.
 * - A soft fade on the left/right edge shows that more is hidden there: classes wps-hscroll,
 *   wps-hscroll--left, wps-hscroll--right (styles in ui.admin.scss). LTR and RTL.
 * - Tables added later (AJAX widgets, add-ons) are picked up by a debounced MutationObserver.
 */
(function () {
    if (typeof document === 'undefined' || window.__wpsHScrollBound) return;
    window.__wpsHScrollBound = true;

    var SELECTOR = 'ul.wps-tabs, .o-table-wrapper';

    function isRtl(el) {
        try { return getComputedStyle(el).direction === 'rtl'; } catch (e) { return false; }
    }

    function update(el) {
        var max = el.scrollWidth - el.clientWidth;
        var overflow = max > 1;
        var pos = Math.abs(el.scrollLeft); // 0 at the start edge, also in RTL (negative values there)
        var atStart = pos <= 1, atEnd = pos >= max - 1;
        var rtl = isRtl(el);
        el.classList.toggle('wps-hscroll', overflow);
        el.classList.toggle('wps-hscroll--left', overflow && (rtl ? !atEnd : !atStart));
        el.classList.toggle('wps-hscroll--right', overflow && (rtl ? !atStart : !atEnd));
    }

    function reveal(strip) {
        if (strip.scrollWidth <= strip.clientWidth + 1) return;
        var current = strip.querySelector('.wps-tab-link.current, li.current');
        if (!current) return;
        var box = strip.getBoundingClientRect(), r = current.getBoundingClientRect();
        if (r.left < box.left || r.right > box.right) {
            // Center it, horizontally only (no page jump).
            strip.scrollLeft += (r.left + r.width / 2) - (box.left + box.width / 2);
        }
    }

    function scan() {
        Array.prototype.forEach.call(document.querySelectorAll(SELECTOR), function (el) {
            if (!el.__wpsHScroll) {
                el.__wpsHScroll = true;
                if (el.matches('ul.wps-tabs')) reveal(el);
            }
            update(el);
        });
    }

    document.addEventListener('scroll', function (e) {
        var el = e.target;
        if (el && el.nodeType === 1 && el.matches && el.matches(SELECTOR)) update(el);
    }, true);

    var timer = 0;
    function later() {
        clearTimeout(timer);
        timer = setTimeout(scan, 150);
    }

    window.addEventListener('resize', later);

    function init() {
        scan();
        if (window.MutationObserver) {
            var root = document.getElementById('wpbody-content') || document.body;
            new MutationObserver(later).observe(root, {childList: true, subtree: true});
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
