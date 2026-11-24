const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// Premium popup (assets/dev/javascript/components/premium-modal.js): opens on the add-on from data-target,
// switches steps, loads pictures, closes on × / Escape and gives focus back.

function el(attrs = {}, classes = []) {
    const set = new Set(classes);
    return {
        attrs: {...attrs},
        hidden: false,
        style: {},
        focused: false,
        classList: {
            contains: (c) => set.has(c),
            toggle: (c, on) => (on ? set.add(c) : set.delete(c)),
            add: (c) => set.add(c),
        },
        has: (c) => set.has(c),
        getAttribute(name) { return name in this.attrs ? this.attrs[name] : null; },
        setAttribute(name, value) { this.attrs[name] = String(value); },
        focus() { this.focused = true; },
    };
}

function load() {
    const listeners = {};
    const steps = ['first-step', 'wp-statistics-data-plus', 'wp-statistics-rest-api'].map((s) => el({'data-step': s}));
    const items = ['wp-statistics-data-plus', 'wp-statistics-rest-api'].map((s) => el({'data-modal': s}));
    const images = [el({'data-src': 'a.png'}), el({'data-src': 'b.png'})];
    const popupLinks = [
        el({href: 'https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=popup&utm_campaign=data-plus&utm_content=upgrade'}),
        el({href: 'https://wp-statistics.com/add-ons/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=popup&utm_campaign=data-plus&utm_content=learn-more'}),
    ];
    const closeBtn = el();
    const modal = el({}, ['js-wps-premiumModal']);
    modal.querySelectorAll = (sel) => ({
        '.js-wps-premiumModalStep': steps,
        '.js-wps-premiumStepFeature': items,
        '.js-wps-premiumModalImage': images,
        '.js-wps-premiumModalLink': popupLinks,
    }[sel] || []);
    modal.querySelector = (sel) => (sel === '.wps-premium-popup__close' ? closeBtn : null);
    const body = {style: {}};
    const window = {};
    const context = {
        window,
        document: {
            body,
            addEventListener: (type, fn) => (listeners[type] = fn),
            querySelectorAll: (sel) => (sel === '.js-wps-premiumModal' ? [modal] : []),
        },
    };
    vm.runInNewContext(fs.readFileSync('assets/dev/javascript/components/premium-modal.js', 'utf8'), context);
    return {window, listeners, modal, steps, items, images, closeBtn, body, popupLinks};
}

test('opens on the add-on named in data-target', () => {
    const {window, modal, steps, items, images, closeBtn, body} = load();
    const shown = window.wpsPremiumModal.open('wp-statistics-rest-api');

    assert.equal(shown, 'wp-statistics-rest-api');
    assert.equal(modal.style.display, 'block');
    assert.equal(body.style.overflow, 'hidden');
    assert.deepEqual(steps.map((s) => s.hidden), [true, true, false]);
    assert.equal(items[1].getAttribute('aria-selected'), 'true');
    assert.equal(items[0].getAttribute('aria-selected'), 'false');
    assert.equal(items[1].getAttribute('tabindex'), '0');
    assert.equal(items[0].getAttribute('tabindex'), '-1');
    assert.deepEqual(images.map((i) => i.getAttribute('src')), ['a.png', 'b.png']);
    // Focus goes to the dialog (tabindex -1, no ring), not to the × button.
    assert.equal(modal.focused, true);
    assert.equal(modal.getAttribute('tabindex'), '-1');
    assert.equal(closeBtn.focused, false);
});

test('unknown target falls back to the intro step', () => {
    const {window, steps, items} = load();
    assert.equal(window.wpsPremiumModal.open('nope'), 'first-step');
    assert.deepEqual(steps.map((s) => s.hidden), [false, true, true]);
    assert.ok(items.every((i) => i.getAttribute('aria-selected') === 'false'));
    assert.equal(items[0].getAttribute('tabindex'), '0');
});

test('click on a See what\'s included link opens the popup and blocks navigation', () => {
    const {listeners, modal, steps} = load();
    const link = el({'data-target': 'first-step', href: 'https://wp-statistics.com/add-ons/'});
    let prevented = false;
    listeners.click({
        target: {closest: (sel) => (sel === '.js-wps-openPremiumModal' ? link : null)},
        preventDefault: () => (prevented = true),
        stopPropagation: () => {},
    });
    assert.equal(prevented, true);
    assert.equal(modal.style.display, 'block');
    assert.equal(steps[0].hidden, false);
});

test('Ctrl/Cmd click on an Upgrade link follows the pricing href', () => {
    const {listeners, modal} = load();
    const link = el({'data-target': 'wp-statistics-rest-api', href: 'https://wp-statistics.com/pricing/'});
    let prevented = false;
    listeners.click({
        target: {closest: (sel) => (sel === '.js-wps-openPremiumModal' ? link : null)},
        metaKey: true,
        preventDefault: () => (prevented = true),
        stopPropagation: () => {},
    });
    assert.equal(prevented, false);
    assert.notEqual(modal.style.display, 'block');
});

test('switcher click changes the step', () => {
    const {window, listeners, modal, steps} = load();
    window.wpsPremiumModal.open('first-step');
    const item = el({'data-modal': 'wp-statistics-data-plus'});
    item.closest = (sel) => (sel === '.js-wps-premiumModal' ? modal : null);
    listeners.click({target: {closest: (sel) => (sel === '.js-wps-premiumStepFeature' ? item : null)}});
    assert.deepEqual(steps.map((s) => s.hidden), [true, false, true]);
});

test('Escape closes and returns focus to the opener', () => {
    const {window, listeners, modal, body} = load();
    const opener = el();
    window.wpsPremiumModal.open('first-step', opener);
    listeners.keydown({key: 'Escape'});
    assert.equal(modal.style.display, 'none');
    assert.equal(body.style.overflow, '');
    assert.equal(opener.focused, true);
});

test('arrow keys move between switcher tabs and show the add-on', () => {
    const {window, listeners, modal, steps, items} = load();
    window.wpsPremiumModal.open('wp-statistics-data-plus');
    items.forEach((i) => (i.closest = (sel) => (sel === '.js-wps-premiumModal' ? modal : sel === '.js-wps-premiumStepFeature' ? i : null)));
    let prevented = false;
    listeners.keydown({key: 'ArrowRight', target: items[0], preventDefault: () => (prevented = true)});
    assert.equal(prevented, true);
    assert.deepEqual(steps.map((s) => s.hidden), [true, true, false]);
    assert.equal(items[1].focused, true);
    assert.equal(items[1].getAttribute('aria-selected'), 'true');

    listeners.keydown({key: 'ArrowRight', target: items[1], preventDefault: () => {}});
    assert.equal(items[0].getAttribute('aria-selected'), 'true');

    listeners.keydown({key: 'End', target: items[0], preventDefault: () => {}});
    assert.equal(items[1].getAttribute('aria-selected'), 'true');
});

test('popup links carry the surface that opened the popup as utm_term', () => {
    const {listeners, popupLinks, window} = load();
    const trigger = el({'data-target': 'wp-statistics-data-plus', 'data-surface': 'locked-button'});
    listeners.click({
        target: {closest: (sel) => (sel === '.js-wps-openPremiumModal' ? trigger : null)},
        preventDefault: () => {},
        stopPropagation: () => {},
    });
    assert.equal(popupLinks[0].getAttribute('href'), 'https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=popup&utm_campaign=data-plus&utm_content=upgrade&utm_term=locked-button');
    assert.ok(popupLinks[1].getAttribute('href').endsWith('&utm_content=learn-more&utm_term=locked-button'));

    // Opened again from another surface: the term is replaced, not added twice.
    window.wpsPremiumModal.open('first-step', el({'data-surface': 'teaser'}));
    assert.ok(popupLinks[0].getAttribute('href').endsWith('&utm_content=upgrade&utm_term=teaser'));
    assert.equal(popupLinks[0].getAttribute('href').split('utm_term').length, 2);

    // No surface: no utm_term.
    window.wpsPremiumModal.open('first-step');
    assert.ok(popupLinks[0].getAttribute('href').endsWith('&utm_content=upgrade'));
});

test('withTerm keeps other parameters and the hash', () => {
    const {window} = load();
    const withTerm = window.wpsPremiumModal.withTerm;
    assert.equal(withTerm('https://x.test/p/?utm_term=a&b=1#top', 'c'), 'https://x.test/p/?b=1&utm_term=c#top');
    assert.equal(withTerm('https://x.test/p/', 'c'), 'https://x.test/p/?utm_term=c');
    assert.equal(withTerm('https://x.test/p/?a=1&utm_term=b', ''), 'https://x.test/p/?a=1');
});
