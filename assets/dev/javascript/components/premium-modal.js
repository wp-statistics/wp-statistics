/**
 * Premium popup (views/components/modals/introduce-premium/*): opens from any .js-wps-openPremiumModal link on
 * the add-on named in data-target (or the intro step, "first-step"), switches add-ons from the switcher
 * (switcher.php: .js-wps-premiumStepFeature[data-modal]), closes on ×, Escape or a click on the backdrop and
 * gives focus back to the link that opened it. Pictures load on first open.
 *
 * The Overview's one-time welcome popup (.js-wps-premiumModal-welcome) holds the same steps behind its
 * "Discover Premium Features" button.
 */
(function () {
    var lastTrigger = null;

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
        for (var k = 0; k < items.length; k++) {
            var active = items[k].getAttribute('data-modal') === target;
            items[k].classList.toggle('is-active', active);
            items[k].setAttribute('aria-pressed', active ? 'true' : 'false');
        }

        return target;
    }

    function open(target, trigger, modal) {
        modal = modal || getModal(false);
        if (!modal) return null;

        lastTrigger = trigger || null;

        var welcome = modal.querySelector('.js-wps-premiumModalWelcomeContent');
        if (welcome) welcome.style.display = 'none';
        var stepsContent = modal.querySelector('.js-wps-premiumModalSteps');
        if (stepsContent) stepsContent.style.display = 'block';

        modal.style.display = 'block';
        if (document.body) document.body.style.overflow = 'hidden';

        loadImages(modal);
        var shown = showStep(modal, target || 'first-step');

        var close = modal.querySelector('.wps-premium-popup__close');
        if (close && typeof close.focus === 'function') close.focus();

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
            open('first-step', null, closest(explore, '.js-wps-premiumModal'));
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

    document.addEventListener('keydown', function (event) {
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
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    if (typeof window !== 'undefined') {
        window.wpsPremiumModal = {open: open, close: close, showStep: showStep};
    }
})();
