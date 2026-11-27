/**
 * Visitor card: the popover behind every Visitor Information pill
 * (views/components/visitor-information.php, styles in component/_visitor-card.scss).
 *
 * - The pill (ul[data-wps-visitor-card], role="button") is the trigger; its card is the next sibling.
 * - Hover opens after 250 ms and closes 150 ms after the mouse leaves both pill and card; moving to
 *   another row switches at once. Keyboard focus opens it too.
 * - Click, tap or Enter on the pill opens the visitor's journey page (data-wps-visitor-url); Ctrl/Cmd
 *   or middle click opens it in a new tab. Touch never opens the card, so phones go straight to the page.
 *   Space pins the card for keyboard users (close button shows). Esc, outside click and the close button
 *   close it; focus goes back to the pill. Tab from the pill moves into the card. One card open at a time.
 * - While open the card lives in <body> with fixed positioning, so scrolling tables never clip it.
 *   It is measured while visibility:hidden and only then faded in, so it never jumps. It opens beside
 *   the pill (right in LTR, left in RTL), top edges lined up, so the mouse can move straight down the
 *   list without the card covering the next row. Without room beside the pill it opens 8px below
 *   (above near the bottom) and never covers its own pill. Under 480px it is a bottom sheet (keyboard only).
 * - Event delegation: rows added later by AJAX (Overview widgets, Real-Time, add-ons) work.
 *
 * window.wpsVisitorCard.position() is the pure placement helper (tests/javascript).
 */
(function (root) {
    var OPEN_DELAY = 250, CLOSE_DELAY = 150, CLOSE_MS = 100, GAP = 8, MARGIN = 8, SHEET_BELOW = 480, STACK_BELOW = 330;

    /**
     * Where to put the card.
     *
     * @param {{top:number,bottom:number,left:number,right:number}} trigger Trigger rect (viewport px).
     * @param {{width:number,height:number}} card Card size.
     * @param {{width:number,height:number}} viewport Viewport size.
     * @param {{rtl?:boolean, gap?:number, margin?:number}} [opts]
     * @returns {{left:number, top:number, side:string, maxHeight:(number|null)}}
     *   maxHeight is set when the card fits on neither side: it then scrolls instead of covering the pill.
     */
    function position(trigger, card, viewport, opts) {
        opts = opts || {};
        var gap = opts.gap != null ? opts.gap : GAP;
        var m = opts.margin != null ? opts.margin : MARGIN;

        // Beside the pill first: the mouse can then go straight down the list.
        if (opts.beside !== false) {
            var sideLeft = opts.rtl ? trigger.left - gap - card.width : trigger.right + gap;
            if (sideLeft >= m && sideLeft + card.width <= viewport.width - m) {
                var room = viewport.height - 2 * m;
                var cap = card.height > room ? Math.max(0, Math.floor(room)) : null;
                var h = cap != null ? cap : card.height;
                var sideTop = Math.max(m, Math.min(trigger.top, viewport.height - m - h));
                return {left: Math.round(sideLeft), top: Math.round(sideTop), side: opts.rtl ? 'left' : 'right', maxHeight: cap};
            }
        }
        var below = viewport.height - m - (trigger.bottom + gap);
        var above = trigger.top - gap - m;
        var side, maxHeight = null, height = card.height;

        if (height <= below) side = 'bottom';
        else if (height <= above) side = 'top';
        else {
            side = above > below ? 'top' : 'bottom';
            maxHeight = Math.max(0, Math.floor(side === 'top' ? above : below));
            height = maxHeight;
        }

        var left = opts.rtl ? trigger.right - card.width : trigger.left;
        left = Math.max(m, Math.min(left, viewport.width - card.width - m));
        var top = side === 'bottom' ? trigger.bottom + gap : trigger.top - gap - height;

        return {left: Math.round(left), top: Math.round(top), side: side, maxHeight: maxHeight};
    }

    var api = {
        position: position, OPEN_DELAY: OPEN_DELAY, CLOSE_DELAY: CLOSE_DELAY, CLOSE_MS: CLOSE_MS,
        SHEET_BELOW: SHEET_BELOW, STACK_BELOW: STACK_BELOW
    };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    if (root) root.wpsVisitorCard = api;

    if (typeof document === 'undefined' || !document.addEventListener || root.__wpsVisitorCardBound) return;
    root.__wpsVisitorCardBound = true;

    var TRIGGER = '[data-wps-visitor-card]';
    var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';
    var open = null, closing = null, showTimer = 0, hideTimer = 0, watchTimer = 0, lastPointer = 'mouse';
    var refocusing = false; // focus moved back to a pill by a close: do not reopen its card
    var bodyPad = null; // body padding-bottom before a bottom sheet added room under the last rows
    var cards = typeof WeakMap !== 'undefined' ? new WeakMap() : null;

    function cardFor(trigger) {
        var card = cards && cards.get(trigger);
        if (card) return card;
        card = trigger.nextElementSibling;
        if (!card || !card.classList.contains('wps-visitor-card')) {
            card = document.getElementById(trigger.getAttribute('aria-controls'));
        }
        if (card && cards) cards.set(trigger, card);
        return card;
    }

    function isRtl(el) {
        try {
            return getComputedStyle(el).direction === 'rtl';
        } catch (e) {
            return document.documentElement.dir === 'rtl';
        }
    }

    function isSheet() {
        return (document.documentElement.clientWidth || root.innerWidth) < SHEET_BELOW;
    }

    function scrollParent(el) {
        for (var node = el.parentElement; node && node !== document.body; node = node.parentElement) {
            var style = getComputedStyle(node);
            if (/(auto|scroll|hidden)/.test(style.overflowX + style.overflowY)) return node;
        }
        return null;
    }

    // Two rows of actions when the card is narrow or a label would wrap (long translations).
    function fitActions(card) {
        var row = card.querySelector('.wps-visitor-card__actions');
        card.classList.remove('wps-visitor-card--stack');
        if (!row || row.children.length < 3) return;
        var stack = card.offsetWidth < STACK_BELOW;
        if (!stack) {
            card.classList.add('wps-visitor-card--measure');
            Array.prototype.forEach.call(row.children, function (item) {
                var label = item.querySelector('span');
                if (label && label.scrollWidth > item.clientWidth - 12) stack = true;
            });
            card.classList.remove('wps-visitor-card--measure');
        }
        card.classList.toggle('wps-visitor-card--stack', stack);
    }

    function place() {
        if (!open) return;
        var card = open.card, sheet = isSheet();
        card.classList.toggle('wps-visitor-card--sheet', sheet);
        card.style.maxHeight = '';
        fitActions(card);
        if (sheet) {
            card.style.left = card.style.top = '';
            card.setAttribute('data-side', 'bottom');
            // Room under the page so a pill in the last rows can still scroll above the sheet.
            if (bodyPad === null) bodyPad = document.body.style.paddingBottom || '';
            document.body.style.paddingBottom = (card.offsetHeight + MARGIN * 2) + 'px';
            // Keep the tapped pill visible above the sheet.
            var tb = open.trigger.getBoundingClientRect().bottom, sheetTop = root.innerHeight - MARGIN - card.offsetHeight;
            if (tb + GAP > sheetTop && root.scrollBy) root.scrollBy(0, Math.ceil(tb + GAP - sheetTop));
            return;
        }
        restoreBodyPad();
        var pos = position(open.trigger.getBoundingClientRect(), {width: card.offsetWidth, height: card.offsetHeight}, {
            width: document.documentElement.clientWidth,
            height: root.innerHeight
        }, {rtl: isRtl(open.trigger)});
        card.style.left = pos.left + 'px';
        card.style.top = pos.top + 'px';
        card.style.maxHeight = pos.maxHeight != null ? pos.maxHeight + 'px' : '';
        card.setAttribute('data-side', pos.side);
    }

    function restoreBodyPad() {
        if (bodyPad === null) return;
        document.body.style.paddingBottom = bodyPad;
        bodyPad = null;
    }

    function setPinned(on) {
        if (open) open.card.classList.toggle('is-pinned', !!on);
    }

    function show(trigger, pin) {
        clearTimeout(showTimer);
        clearTimeout(hideTimer);
        if (open && open.trigger === trigger) {
            if (pin) setPinned(true);
            return;
        }
        hideNow(true);

        var card = cardFor(trigger);
        if (!card) return;

        var home = card.parentNode, next = card.nextSibling;
        if (closing && closing.card === card) {
            // Reopened while fading out: keep it where it is.
            clearTimeout(closing.timer);
            home = closing.home;
            next = closing.next;
            closing = null;
        } else {
            finishClose();
        }

        // Keep the id unique once it sits in <body> (separate AJAX widgets can repeat ids).
        if (card.id) trigger.setAttribute('aria-controls', card.id);

        open = {trigger: trigger, card: card, home: home, next: next, clip: scrollParent(trigger)};
        if (card.parentNode !== document.body) document.body.appendChild(card);
        card.classList.remove('is-visible');
        card.hidden = false;
        card.style.display = '';
        if (isRtl(trigger)) card.setAttribute('dir', 'rtl'); else card.removeAttribute('dir');
        card.classList.toggle('is-pinned', !!pin);
        place(); // measured while visibility:hidden: no jump
        void card.offsetWidth;
        card.classList.add('is-visible');
        trigger.setAttribute('aria-expanded', 'true');
        trigger.classList.add('is-open');

        clearInterval(watchTimer);
        watchTimer = setInterval(function () {
            if (open && !open.trigger.isConnected) hideNow(true);
        }, 500);
    }

    // Hide, then park the card next to its pill again (or drop it when the row is gone).
    function finishClose() {
        if (!closing) return;
        var c = closing;
        closing = null;
        clearTimeout(c.timer);
        c.card.hidden = true;
        c.card.style.display = 'none';
        c.card.style.maxHeight = '';
        c.card.classList.remove('wps-visitor-card--sheet', 'wps-visitor-card--stack', 'is-pinned');
        if (c.trigger.isConnected && c.home && c.home.isConnected) {
            c.home.insertBefore(c.card, c.next && c.next.parentNode === c.home ? c.next : null);
        } else if (c.card.parentNode) {
            c.card.parentNode.removeChild(c.card);
        }
    }

    function hideNow(instant) {
        clearTimeout(hideTimer);
        clearTimeout(showTimer);
        clearInterval(watchTimer);
        if (!open) return;
        var o = open;
        open = null;
        restoreBodyPad();
        o.trigger.setAttribute('aria-expanded', 'false');
        o.trigger.classList.remove('is-open');
        o.card.classList.remove('is-visible');
        closing = {card: o.card, trigger: o.trigger, home: o.home, next: o.next, timer: 0};
        if (instant) finishClose();
        else closing.timer = setTimeout(finishClose, CLOSE_MS + 20);
    }

    // Move focus without the focus handler reopening the card that was just closed.
    function quietFocus(el) {
        if (!el || !el.isConnected) return;
        refocusing = true;
        el.focus();
        setTimeout(function () { refocusing = false; }, 0);
    }

    function closeAndFocus() {
        var trigger = open && open.trigger;
        hideNow();
        quietFocus(trigger);
    }

    function hideSoon() {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function () { hideNow(); }, CLOSE_DELAY);
    }

    function inOpen(el) {
        return !!(open && el && el.nodeType === 1 && (open.trigger.contains(el) || open.card.contains(el)));
    }

    function focusables(scope) {
        return Array.prototype.filter.call(scope.querySelectorAll(FOCUSABLE), function (el) {
            return el.offsetWidth || el.offsetHeight || el.getClientRects().length;
        });
    }

    function focusAfter(trigger) {
        var all = focusables(document.body).filter(function (el) {
            return !open || !open.card.contains(el);
        });
        var i = all.indexOf(trigger);
        return i > -1 ? all[i + 1] : null;
    }

    function copyText(text, button, done) {
        var fallback = function () {
            // Inside the card, so focusing it does not count as leaving the card.
            var host = (open && open.card) || document.body;
            var area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.setAttribute('aria-hidden', 'true');
            area.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;opacity:0;pointer-events:none';
            host.appendChild(area);
            area.select();
            var ok = false;
            try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
            host.removeChild(area);
            if (button && button.isConnected) button.focus();
            done(ok);
        };
        if (navigator.clipboard && root.isSecureContext) {
            navigator.clipboard.writeText(text).then(function () { done(true); }, fallback);
        } else {
            fallback();
        }
    }

    // Open the visitor's journey page.
    function go(trigger, newTab) {
        var url = trigger.getAttribute('data-wps-visitor-url');
        if (!url) {
            show(trigger, true);
            return;
        }
        hideNow(true);
        if (newTab && root.open) root.open(url, '_blank', 'noopener');
        else root.location.href = url;
    }

    function pinned() {
        return !!(open && (open.card.classList.contains('is-pinned') || open.card.classList.contains('wps-visitor-card--sheet')));
    }

    document.addEventListener('pointerdown', function (e) {
        lastPointer = e.pointerType || 'mouse';
    }, true);

    document.addEventListener('pointerover', function (e) {
        if (e.pointerType && e.pointerType !== 'mouse') return;
        var target = e.target.closest ? e.target : null;
        if (!target) return;
        if (open && open.card.contains(target)) {
            clearTimeout(hideTimer);
            return;
        }
        var trigger = target.closest(TRIGGER);
        if (!trigger) return;
        // Under 480px the card is a bottom sheet: it opens on tap or click only, never on mouse hover.
        if (isSheet()) return;
        clearTimeout(hideTimer);
        if (open && open.trigger === trigger) return;
        clearTimeout(showTimer);
        if (open) {
            if (!pinned()) show(trigger); // one already open on hover: switch rows at once
            return;
        }
        showTimer = setTimeout(function () { show(trigger); }, OPEN_DELAY);
    });

    document.addEventListener('pointerout', function (e) {
        if (e.pointerType && e.pointerType !== 'mouse') return;
        if (!e.target.closest || !e.target.closest(TRIGGER + ', .wps-visitor-card')) return;
        if (inOpen(e.relatedTarget)) return;
        clearTimeout(showTimer);
        if (open && !pinned()) hideSoon();
    });

    document.addEventListener('focusin', function (e) {
        if (lastPointer !== 'keyboard' || refocusing) return;
        var trigger = e.target.closest && e.target.closest(TRIGGER);
        if (trigger && e.target === trigger && !(open && open.trigger === trigger) && !isSheet()) show(trigger);
    });

    document.addEventListener('focusout', function () {
        if (!open) return;
        setTimeout(function () {
            var active = document.activeElement;
            if (open && active && active !== document.body && !inOpen(active)) hideNow();
        }, 0);
    });

    document.addEventListener('keydown', function (e) {
        var trigger = e.target.closest && e.target.closest(TRIGGER);
        lastPointer = 'keyboard';

        if (trigger && e.target === trigger && e.key === 'Enter') {
            e.preventDefault();
            go(trigger, e.ctrlKey || e.metaKey);
            return;
        }

        if (trigger && e.target === trigger && (e.key === ' ' || e.key === 'Spacebar')) {
            e.preventDefault();
            if (open && open.trigger === trigger && pinned()) hideNow();
            else show(trigger, true);
            return;
        }

        if (!open) return;

        if (e.key === 'Escape' || e.key === 'Esc') {
            var wasInside = open.card.contains(document.activeElement) || open.trigger === document.activeElement;
            if (wasInside) {
                e.preventDefault();
                closeAndFocus();
            } else {
                hideNow();
            }
            return;
        }

        if (e.key !== 'Tab') return;

        // The card sits in <body>: keep Tab order as if it followed the pill.
        var items = focusables(open.card);
        if (!items.length) return;
        if (e.target === open.trigger && !e.shiftKey) {
            e.preventDefault();
            setPinned(true);
            items[0].focus();
        } else if (open.card.contains(e.target)) {
            if (e.shiftKey && e.target === items[0]) {
                e.preventDefault();
                open.trigger.focus();
            } else if (!e.shiftKey && e.target === items[items.length - 1]) {
                e.preventDefault();
                var from = open.trigger, next = focusAfter(from);
                hideNow();
                if (next) next.focus(); else quietFocus(from);
            }
        }
    });

    document.addEventListener('click', function (e) {
        var target = e.target.closest ? e.target : null;
        if (!target) return;

        var copy = target.closest('[data-wps-copy]');
        if (copy && open && open.card.contains(copy)) {
            var card = open.card, labelEl = copy.querySelector('[data-wps-copy-label]') || copy;
            var label = copy.getAttribute('data-label') || labelEl.textContent;
            copy.setAttribute('data-label', label);
            copyText(copy.getAttribute('data-wps-copy'), copy, function (ok) {
                var done = ok ? (card.getAttribute('data-copied') || 'Copied') : label;
                labelEl.textContent = done;
                copy.classList.toggle('is-copied', ok);
                var status = card.querySelector('[data-wps-copy-status]');
                if (status && ok) status.textContent = done;
                setTimeout(function () {
                    labelEl.textContent = label;
                    copy.classList.remove('is-copied');
                    if (status) status.textContent = '';
                }, 1500);
            });
            return;
        }

        if (target.closest('[data-wps-card-close]')) {
            closeAndFocus();
            return;
        }

        var trigger = target.closest(TRIGGER);
        if (trigger) {
            e.preventDefault();
            go(trigger, e.ctrlKey || e.metaKey || e.shiftKey);
            return;
        }

        if (open && !inOpen(target)) hideNow();
    });

    // Middle click on the pill: journey in a new tab, like a link.
    document.addEventListener('auxclick', function (e) {
        var trigger = e.button === 1 && e.target.closest && e.target.closest(TRIGGER);
        if (!trigger) return;
        e.preventDefault();
        go(trigger, true);
    });

    root.addEventListener('scroll', function () {
        if (!open || open.card.classList.contains('wps-visitor-card--sheet')) return;
        var r = open.trigger.getBoundingClientRect();
        var clip = open.clip ? open.clip.getBoundingClientRect() : null;
        var outOfView = r.bottom < 0 || r.top > root.innerHeight ||
            (clip && (r.right < clip.left || r.left > clip.right || r.bottom < clip.top || r.top > clip.bottom));
        if (outOfView) hideNow(true); else place();
    }, true);

    root.addEventListener('resize', place);

    // A Gravatar that cannot load (offline, blocked) is removed, so the initial underneath shows instead of a
    // broken-image icon: visitor card, leaderboard, single visitor profile and page header avatars.
    var AVATARS = '.wps-visitor-card__photo, .wps-leaderboard__avatar img, .wps-visitor-profile__avatar--user img, .wps-page-header__icon--avatar, img[data-wps-avatar]';
    document.addEventListener('error', function (e) {
        var img = e.target;
        if (img && img.tagName === 'IMG' && img.matches && img.matches(AVATARS) && img.parentNode) img.parentNode.removeChild(img);
    }, true);
    // Images that failed before this script ran.
    Array.prototype.forEach.call(document.querySelectorAll(AVATARS), function (img) {
        if (img.complete && img.naturalWidth === 0 && img.getAttribute('src') && img.parentNode) img.parentNode.removeChild(img);
    });
})(typeof window !== 'undefined' ? window : this);
