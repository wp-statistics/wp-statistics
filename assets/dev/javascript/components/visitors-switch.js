/**
 * Segmented switch of the Visitors card header (Overview page and WP Dashboard):
 *
 *   [ Latest | Most active | ● Online now 3 ]
 *
 * A tab list (role="tablist"): one tab is selected and focusable (roving tabindex); arrows move
 * and select, Home / End jump to the ends, and in RTL the arrow keys follow the reading
 * direction. "Online now" has a pulsing green dot and a count badge; with nobody online the dot
 * is grey and still, the tab stays clickable and the list shows its empty message.
 *
 * This file only builds the markup and answers key presses. The card controller (overview.js)
 * puts the switch in the header, loads each list and keeps the counter up to date.
 * Pure helpers, so they run under Node for the tests (tests/javascript/visitors-switch.test.js).
 */
(function (root) {
    var PANEL_ID = 'wps-visitors-panel';

    function escapeHtml(value) {
        return String(value === undefined || value === null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * The segment a key press moves to, or null when the key does nothing.
     *
     * @param {string[]} keys    Segment keys in switch order.
     * @param {string}   current Selected segment.
     * @param {string}   key     KeyboardEvent.key.
     * @param {boolean}  rtl     Right-to-left: ArrowLeft goes forward.
     * @returns {string|null}
     */
    function nextSegment(keys, current, key, rtl) {
        if (!keys || !keys.length) return null;

        var index = keys.indexOf(current);
        if (index < 0) index = 0;

        var forward = rtl ? 'ArrowLeft' : 'ArrowRight';
        var back = rtl ? 'ArrowRight' : 'ArrowLeft';

        if (key === forward) return keys[(index + 1) % keys.length];
        if (key === back) return keys[(index - 1 + keys.length) % keys.length];
        if (key === 'Home') return keys[0];
        if (key === 'End') return keys[keys.length - 1];

        return null;
    }

    /**
     * The segment to select: the wanted one when it is in the switch, else the first.
     */
    function pickSegment(keys, wanted) {
        if (!keys || !keys.length) return null;
        return keys.indexOf(wanted) >= 0 ? wanted : keys[0];
    }

    /**
     * Online badge parts for a count: number text and whether nobody is online.
     *
     * @param {number|null} count
     * @returns {{text: string, zero: boolean}}
     */
    function onlineState(count) {
        var n = parseInt(count, 10);
        if (isNaN(n) || n < 0) n = 0;

        var text = String(n);
        try {
            text = n.toLocaleString(root && root.document ? root.document.documentElement.lang || undefined : undefined);
        } catch (e) {
            text = String(n);
        }

        return {text: text, zero: n === 0};
    }

    /**
     * Markup of the switch.
     *
     * @param {Object}        args
     * @param {Array<{key:string,label:string}>} args.segments In order; no "online" when the feature is off.
     * @param {string}        args.selected Selected segment.
     * @param {number|null}   args.online   Online count (badge).
     * @param {string}        args.label    Accessible name of the tab list.
     * @param {string}        args.idPrefix Prefix of the tab ids.
     * @returns {string}
     */
    function render(args) {
        var segments = args && args.segments ? args.segments : [];
        var keys = segments.map(function (s) { return s.key; });
        var selected = pickSegment(keys, args.selected);
        var prefix = args.idPrefix || 'wps-visitors-tab';

        var tabs = segments.map(function (segment) {
            var isSelected = segment.key === selected;
            var inner = '<span class="wps-visitors-switch__label" data-text="' + escapeHtml(segment.label) + '">' + escapeHtml(segment.label) + '</span>';

            if (segment.key === 'online') {
                var state = onlineState(args.online);
                inner = '<span class="wps-pulse-dot' + (state.zero ? ' is-off' : '') + '" aria-hidden="true"></span>' + inner +
                    '<span class="wps-visitors-switch__badge' + (state.zero ? ' is-zero' : '') + '">' + escapeHtml(state.text) + '</span>';
            }

            return '<button type="button" role="tab" class="wps-visitors-switch__tab wps-visitors-switch__tab--' + escapeHtml(segment.key) + '"' +
                ' id="' + escapeHtml(prefix + '-' + segment.key) + '"' +
                ' data-segment="' + escapeHtml(segment.key) + '"' +
                ' aria-selected="' + (isSelected ? 'true' : 'false') + '"' +
                ' aria-controls="' + PANEL_ID + '"' +
                ' tabindex="' + (isSelected ? '0' : '-1') + '">' + inner + '</button>';
        }).join('');

        return '<div class="wps-visitors-switch" role="tablist" aria-label="' + escapeHtml(args.label || '') + '">' + tabs + '</div>';
    }

    var api = {
        PANEL_ID: PANEL_ID,
        nextSegment: nextSegment,
        pickSegment: pickSegment,
        onlineState: onlineState,
        render: render,
        escapeHtml: escapeHtml
    };

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = api;
    }

    if (root) {
        root.wpsVisitorsSwitch = api;
    }
})(typeof window !== 'undefined' ? window : null);
