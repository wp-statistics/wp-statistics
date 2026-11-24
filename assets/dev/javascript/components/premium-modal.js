/**
 * Premium popup (views/components/modals/introduce-premium/*): opens from any .js-wps-openPremiumModal link on
 * the add-on named in data-target (or the intro step, "first-step"), switches add-ons from the switcher
 * (switcher.php: a tablist of .js-wps-premiumStepFeature[data-modal]; arrow keys, Home and End move between
 * add-ons), closes on ×, Escape or a click on the backdrop and
 * gives focus back to the link that opened it. On open, focus sits on the dialog (no ring), Tab is kept inside. Pictures load on first open.
 *
 * The Overview's one-time welcome popup (.js-wps-premiumModal-welcome) holds the same steps behind its
 * "Discover Premium Features" button.
 *
 * Tracking: the popup's "Upgrade to Premium" / "Learn more" links (.js-wps-premiumModalLink, already tagged
 * utm_medium=popup by UpsellLink) get utm_term = the data-surface of the trigger that opened the popup
 * (tooltip, locked-tab, teaser, go-premium-box, showcase, ...), so a purchase shows where it started.
 */
(function () {
    var lastTrigger = null;

    // Sets (or, with an empty value, removes) utm_term on a URL; keeps every other parameter and the #hash.
    function withTerm(href, term) {
        href = href || '';
        var hash = '';
        var at = href.indexOf('#');
        if (at > -1) {
            hash = href.slice(at);
            href = href.slice(0, at);
        }
        href = href.replace(/([?&])utm_term=[^&]*(&|$)/, function (match, before, after) {
            return after ? before : '';
        });
        if (term) href += (href.indexOf('?') > -1 ? '&' : '?') + 'utm_term=' + encodeURIComponent(term);
        return href + hash;
    }

    function setOpener(modal, surface) {
        var links = modal.querySelectorAll('.js-wps-premiumModalLink');
        for (var i = 0; i < links.length; i++) {
            links[i].setAttribute('href', withTerm(links[i].getAttribute('href'), surface || ''));
        }
    }

    function getModal(preferWelcome) {
        var modals = document.querySelectorAll('.js-wps-premiumModal');
        var fallback = null;
        for (var i = 0; i < modals.length; i++) {
            var isWelcome = modals[i].classList.contains('js-wps-premiumModal-welcome');
            if (isWelcome === !!preferWelcome) return modals[i];
            if (!fallback) fallback = modals[i];
        }
        return fallback;
    }

    function loadImages(modal) {
        var images = modal.querySelectorAll('.js-wps-premiumModalImage');
        for (var i = 0; i < images.length; i++) {
            var src = images[i].getAttribute('data-src');
            if (src && images[i].getAttribute('src') !== src) images[i].setAttribute('src', src);
        }
    }

    function showStep(modal, target) {
        var steps = modal.querySelectorAll('.js-wps-premiumModalStep');
        var found = false;
        for (var i = 0; i < steps.length; i++) {
            if (steps[i].getAttribute('data-step') === target) found = true;
        }
        if (!found) target = 'first-step';

        for (var j = 0; j < steps.length; j++) {
            var on = steps[j].getAttribute('data-step') === target;
            steps[j].hidden = !on;
            steps[j].classList.toggle('is-active', on);
        }

        var items = modal.querySelectorAll('.js-wps-premiumStepFeature');
        var anyActive = false;
        for (var k = 0; k < items.length; k++) {
            var active = items[k].getAttribute('data-modal') === target;
            if (active) anyActive = true;
            items[k].classList.toggle('is-active', active);
            items[k].setAttribute('aria-selected', active ? 'true' : 'false');
            items[k].setAttribute('tabindex', active ? '0' : '-1');
        }
        // Intro step: no tab is selected, the first one stays reachable with Tab.
        if (!anyActive && items.length) items[0].setAttribute('tabindex', '0');

        return target;
    }

    function open(target, trigger, modal, surface) {
        modal = modal || getModal(false);
        if (!modal) return null;

        lastTrigger = trigger || null;
        if (!surface && trigger && trigger.getAttribute) surface = trigger.getAttribute('data-surface');
        setOpener(modal, surface || '');

        var welcome = modal.querySelector('.js-wps-premiumModalWelcomeContent');
        if (welcome) welcome.style.display = 'none';
        var stepsContent = modal.querySelector('.js-wps-premiumModalSteps');
        if (stepsContent) stepsContent.style.display = 'block';

        modal.style.display = 'block';
        if (document.body) document.body.style.overflow = 'hidden';

        loadImages(modal);
        var shown = showStep(modal, target || 'first-step');

        // Focus goes to the dialog itself (tabindex="-1", no ring), not the × button: screen readers announce
        // the popup, the next Tab reaches the first button, and a mouse open shows no heavy focus ring.
        if (typeof modal.focus === 'function') {
            if (modal.getAttribute('tabindex') === null) modal.setAttribute('tabindex', '-1');
            try {
                modal.focus({preventScroll: true});
            } catch (e) {
                modal.focus();
            }
        }

        return shown;
    }

    function close(modal) {
        if (!modal) return;
        modal.style.display = 'none';
        if (document.body) document.body.style.overflow = '';
        if (lastTrigger && typeof lastTrigger.focus === 'function') {
            try {
                lastTrigger.focus();
            } catch (e) {
            }
        }
        lastTrigger = null;
    }

    function isOpen(modal) {
        return !!modal && modal.style.display === 'block';
    }

    function closest(el, selector) {
        return el && el.closest ? el.closest(selector) : null;
    }

    document.addEventListener('click', function (event) {
        var trigger = closest(event.target, '.js-wps-openPremiumModal');
        if (trigger) {
            // No popup on this page, or a new-tab click: let the link (pricing / add-on page) do its job.
            if (!getModal(false) || event.metaKey || event.ctrlKey || event.shiftKey || event.button === 1) return;
            event.preventDefault();
            event.stopPropagation();
            open(trigger.getAttribute('data-target'), trigger);
            return;
        }

        var item = closest(event.target, '.js-wps-premiumStepFeature');
        if (item) {
            showStep(closest(item, '.js-wps-premiumModal'), item.getAttribute('data-modal'));
            return;
        }

        var explore = closest(event.target, '.js-wps-premiumModalExploreBtn');
        if (explore) {
            open('first-step', null, closest(explore, '.js-wps-premiumModal'), 'welcome-popup');
            return;
        }

        var closeButton = closest(event.target, '.js-wps-premiumModalClose');
        if (closeButton) {
            event.preventDefault();
            close(closest(closeButton, '.js-wps-premiumModal'));
            return;
        }

        // A click on the dark backdrop closes too.
        if (event.target && event.target.classList && event.target.classList.contains('js-wps-premiumModal')) {
            close(event.target);
        }
    });

    // Switcher tabs: arrow keys (Left/Right and Up/Down), Home and End move to an add-on and show it.
    function moveTab(event) {
        var item = closest(event.target, '.js-wps-premiumStepFeature');
        if (!item) return false;
        var keys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
        if (keys.indexOf(event.key) === -1) return false;

        var modal = closest(item, '.js-wps-premiumModal');
        if (!modal) return false;
        var items = Array.prototype.slice.call(modal.querySelectorAll('.js-wps-premiumStepFeature'));
        var index = items.indexOf(item);
        if (index === -1 || !items.length) return false;

        var rtl = !!(document.documentElement && document.documentElement.dir === 'rtl');
        var next = index;
        if (event.key === 'Home') next = 0;
        else if (event.key === 'End') next = items.length - 1;
        else if (event.key === 'ArrowDown' || event.key === (rtl ? 'ArrowLeft' : 'ArrowRight')) next = (index + 1) % items.length;
        else next = (index - 1 + items.length) % items.length;

        event.preventDefault();
        showStep(modal, items[next].getAttribute('data-modal'));
        if (typeof items[next].focus === 'function') items[next].focus();
        return true;
    }

    document.addEventListener('keydown', function (event) {
        if (moveTab(event)) return;
        if (event.key !== 'Escape' && event.key !== 'Tab') return;
        var modals = document.querySelectorAll('.js-wps-premiumModal');
        for (var i = 0; i < modals.length; i++) {
            if (!isOpen(modals[i])) continue;

            if (event.key === 'Escape') {
                close(modals[i]);
                continue;
            }

            // Keep Tab inside the open popup.
            var focusable = Array.prototype.filter.call(
                modals[i].querySelectorAll('a[href], button:not([disabled])'),
                function (el) {
                    return !closest(el, '[hidden]') && el.offsetParent !== null;
                }
            );
            if (!focusable.length) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            var active = document.activeElement;
            // Focus on the dialog itself (right after opening) or outside it: Tab goes to the first button,
            // Shift+Tab to the last one.
            if (!active || active === modals[i] || (modals[i].contains && !modals[i].contains(active))) {
                event.preventDefault();
                (event.shiftKey ? last : first).focus();
            } else if (event.shiftKey && active === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && active === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    if (typeof window !== 'undefined') {
        window.wpsPremiumModal = {open: open, close: close, showStep: showStep, withTerm: withTerm};
    }
})();
