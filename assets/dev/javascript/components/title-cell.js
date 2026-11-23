/**
 * Title cells cut with a CSS ellipsis (component/_title-cell.scss): when the text is
 * really cut, show the full text as a native tooltip on hover/focus. Markups that already
 * carry a title (on the text or its link/wrapper) keep theirs. Works for tables rendered
 * later by JS and add-ons too (delegated, no per-table setup).
 */
(function () {
    var SELECTOR = '.wps-card-footer__label, .wps-head-filters .dropbtn > span, .wps-link-arrow > span, .wps-ellipsis-text, .wps-table-ellipsis--name, .wps-table-ellipsis--name > span, .truncate, .wps-title-cell > a:not(.wps-new-tab-link)';

    function fill(event) {
        var target = event.target;
        if (!target || !target.closest) return;

        var el = target.closest(SELECTOR);
        if (!el || el.scrollWidth <= el.clientWidth + 1) return;

        // A title already on the element or the nearest wrapper wins.
        for (var node = el, i = 0; node && i < 3; node = node.parentElement, i++) {
            if (node.getAttribute && node.getAttribute('title')) return;
        }

        var text = (el.textContent || '').replace(/\s+/g, ' ').trim();
        if (text) el.setAttribute('title', text);
    }

    document.addEventListener('mouseover', fill, true);
    document.addEventListener('focusin', fill, true);
})();
