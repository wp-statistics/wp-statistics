/**
 * Settings: long help text moves into an (i) button next to the field label.
 *
 * Add-on tabs only (div.tab-content.wps-addon-tab in settings.php); the core tabs keep their
 * help text visible under the field, as before. Works on the markup those tabs already print:
 *   <tr><th>label</th><td>field <p class="description">help</p></td></tr>
 * Nothing in the markup is renamed. A <p class="description"> longer than one short line
 * is hidden and an (i) button is added after the label:
 *   - hover or keyboard focus shows the text in a tooltip (links stay clickable),
 *   - click/Enter shows or hides the text in place (aria-expanded), so links are reachable
 *     by keyboard and on phones,
 *   - the field gets aria-describedby pointing at the text, so screen readers still read it.
 * Without JavaScript (or with an add-on printing other markup) the text simply stays visible.
 * A description that must stay visible uses another class (for example "description--keep").
 */
(function (window, document, $) {
    'use strict';

    // Some add-ons redefine the global wps_js on this page, so keep our own references.
    var core = window.wps_js || {};
    var MAX_VISIBLE_CHARS = 100;
    // Rows this applies to: add-on settings tabs only.
    var ROWS = '.wp-statistics-settings .wps-addon-tab .form-table > tbody > tr, .wp-statistics-settings .wps-addon-tab .form-table > tr';
    var uid = 0;

    function text(key, fallback) {
        try {
            var value = core.global && core.global.i18n && core.global.i18n[key];
            return value ? String(value) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    /**
     * Should this description move into the (i) button?
     * Only a plain <p class="description"> (exactly that class, no id or inline style, which
     * mean other code shows/hides it), with no form controls inside, longer than one short line.
     */
    function shouldMove(info) {
        if (!info || info.tag !== 'P') return false;
        if ((info.className || '').trim() !== 'description') return false;
        if (info.id || info.style) return false;
        if (info.hasControls) return false;
        return (info.text || '').replace(/\s+/g, ' ').trim().length > MAX_VISIBLE_CHARS;
    }

    function describe(el) {
        return {
            tag: el.tagName,
            className: el.getAttribute('class') || '',
            id: el.id || '',
            style: el.getAttribute('style') || '',
            hasControls: !!el.querySelector('input,select,textarea,button'),
            text: el.textContent || ''
        };
    }

    function closeOthers(keep) {
        if (!$.tooltipster || typeof $.tooltipster.instances !== 'function') return;
        $.each($.tooltipster.instances(), function (i, instance) {
            if (instance !== keep && instance.status && instance.status().open) instance.close();
        });
    }

    function enhanceRow(row) {
        if (row.classList.contains('wps-settings-box_head') || row.getAttribute('data-wps-help') === 'done') return;
        var th = row.querySelector(':scope > th');
        var td = row.querySelector(':scope > td');
        if (!th || !td || th.colSpan > 1) return;
        // The label already has its own info tooltip (for example "locked"): one (i) is enough.
        if (th.querySelector('.wps-tooltip .wps-tooltip-icon:not(.privacy), .wps-setting-help')) return;

        var moved = [];
        Array.prototype.forEach.call(td.querySelectorAll(':scope > p.description'), function (p) {
            if (shouldMove(describe(p))) moved.push(p);
        });
        if (!moved.length) return;
        row.setAttribute('data-wps-help', 'done');

        var ids = moved.map(function (p) {
            if (!p.id) p.id = 'wps-setting-help-' + (++uid);
            p.classList.add('wps-setting-help__text');
            p.hidden = true;
            return p.id;
        });

        // The field (or the first one) reads the help text.
        var field = td.querySelector('input:not([type=hidden]),select,textarea');
        if (field) {
            var current = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
            field.setAttribute('aria-describedby', current.concat(ids).join(' '));
        }

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'wps-setting-help';
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-controls', ids.join(' '));
        var labelText = (th.textContent || '').replace(/\s+/g, ' ').trim();
        button.setAttribute('aria-label', text('setting_help', 'More info') + (labelText ? ': ' + labelText : ''));
        button.innerHTML = '<span class="wps-setting-help__icon" aria-hidden="true"></span>';

        // After the label text, inside the label wrapper when it is not a <label> (a button
        // inside <label> would take its clicks).
        var host = th.querySelector('.wps-setting-label > span') || th.querySelector('.wps-setting-label');
        if (host) {
            // Right after the label text, before a privacy shield or badge.
            host.insertBefore(button, host.querySelector(':scope > .wps-tooltip, :scope > .wps-badge'));
        } else {
            var label = th.querySelector('label');
            var wrap = document.createElement('span');
            wrap.className = 'wps-setting-help__label';
            if (label) {
                label.parentNode.insertBefore(wrap, label);
                wrap.appendChild(label);
            } else {
                while (th.firstChild) wrap.appendChild(th.firstChild);
                th.appendChild(wrap);
            }
            wrap.appendChild(button);
        }

        var setOpen = function (open) {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            moved.forEach(function (p) { p.hidden = !open; });
            row.classList.toggle('wps-setting-help--open', open);
        };

        var $button = $(button);
        var hasTooltip = typeof $.fn.tooltipster === 'function';
        var tipOpen = function () {
            if (!hasTooltip || button.getAttribute('aria-expanded') === 'true') return;
            $button.tooltipster('open');
        };
        var tipClose = function () {
            if (hasTooltip && $button.hasClass('tooltipstered')) $button.tooltipster('close');
        };

        if (hasTooltip) {
            var content = $('<div class="wps-setting-help__tip"></div>');
            moved.forEach(function (p) { content.append($('<p></p>').html(p.innerHTML)); });
            var viewport = document.documentElement.clientWidth || window.innerWidth;
            $button.tooltipster({
                theme: 'tooltipster-shadow',
                content: content,
                contentCloning: true,
                interactive: true,
                trigger: 'custom',
                triggerOpen: {mouseenter: true},
                triggerClose: {mouseleave: true, originClick: true, tap: true, scroll: true},
                maxWidth: Math.min(340, viewport - 16),
                side: ['top', 'bottom', 'right', 'left'],
                functionBefore: function (instance) { closeOthers(instance); },
                functionPosition: typeof core.tooltip_keep_in_viewport === 'function' ? core.tooltip_keep_in_viewport : undefined
            });
        }

        button.addEventListener('focus', function () {
            // Keyboard focus only (a mouse click focuses too, and toggles the text instead).
            if (button.matches(':focus-visible')) tipOpen();
        });
        button.addEventListener('blur', tipClose);
        button.addEventListener('click', function () {
            tipClose();
            setOpen(button.getAttribute('aria-expanded') !== 'true');
        });
        button.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                tipClose();
                setOpen(false);
            }
        });
    }

    function init(root) {
        root = root || document;
        var scope = root.querySelector ? root : document;
        Array.prototype.forEach.call(scope.querySelectorAll(ROWS), enhanceRow);
    }

    // Styled file field (Customization > Import Settings): the native input stays (same id and
    // name, the add-on reads it), the chosen file name shows next to the "Choose file" button.
    function fileName(input) {
        var box = input.closest('.wps-file-input');
        var name = box && box.querySelector('.wps-file-input__name');
        if (!name) return;
        var file = input.files && input.files[0];
        name.textContent = file ? file.name : (name.getAttribute('data-empty') || '');
        box.classList.toggle('wps-file-input--chosen', !!file);
    }

    core.settingsHelp = {init: init, shouldMove: shouldMove, MAX_VISIBLE_CHARS: MAX_VISIBLE_CHARS, ROWS: ROWS};

    if ($ && document.querySelector) {
        $(function () {
            if (document.querySelector('.wp-statistics-settings #wp-statistics-settings-form')) init(document);
        });
        document.addEventListener('change', function (event) {
            var input = event.target;
            if (input && input.matches && input.matches('.wps-file-input input[type=file]')) fileName(input);
        });
    }
})(window, document, window.jQuery);
