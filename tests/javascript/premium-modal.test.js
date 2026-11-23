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
    const closeBtn = el();
    const modal = el({}, ['js-wps-premiumModal']);
    modal.querySelectorAll = (sel) => ({
        '.js-wps-premiumModalStep': steps,
        '.js-wps-premiumStepFeature': items,
        '.js-wps-premiumModalImage': images,
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
    return {window, listeners, modal, steps, items, images, closeBtn, body};
}

test('opens on the add-on named in data-target', () => {
    const {window, modal, steps, items, images, closeBtn, body} = load();
    const shown = window.wpsPremiumModal.open('wp-statistics-rest-api');

    assert.equal(shown, 'wp-statistics-rest-api');
    assert.equal(modal.style.display, 'block');
    assert.equal(body.style.overflow, 'hidden');
    assert.deepEqual(steps.map((s) => s.hidden), [true, true, false]);
    assert.equal(items[1].getAttribute('aria-pressed'), 'true');
    assert.equal(items[0].getAttribute('aria-pressed'), 'false');
    assert.deepEqual(images.map((i) => i.getAttribute('src')), ['a.png', 'b.png']);
    assert.equal(closeBtn.focused, true);
});

test('unknown target falls back to the intro step', () => {
    const {window, steps, items} = load();
    assert.equal(window.wpsPremiumModal.open('nope'), 'first-step');
    assert.deepEqual(steps.map((s) => s.hidden), [false, true, true]);
    assert.ok(items.every((i) => i.getAttribute('aria-pressed') === 'false'));
});

test('click on a Go Premium link opens the popup and blocks navigation', () => {
    const {listeners, modal, steps} = load();
    const link = el({'data-target': 'first-step', href: 'https://wp-statistics.com/pricing/'});
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
