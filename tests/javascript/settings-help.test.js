const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// Settings: long help text moves into an (i) button next to the label (pages/settings-help.js).
// The rule only touches a plain <p class="description"> so add-on markup and JS-toggled text stay put.
function load() {
    const window = {wps_js: {global: {i18n: {}}}};
    const document = {querySelector: () => null, addEventListener: () => {}};
    const jQuery = fn => { if (typeof fn === 'function') fn(); };
    jQuery.fn = {};
    vm.runInNewContext(fs.readFileSync('assets/dev/javascript/pages/settings-help.js', 'utf8'), {window, document, jQuery});
    return window.wps_js.settingsHelp;
}

const long = 'Tracks the activities of logged-in users, including page views, and records them with their WordPress User IDs.';
const p = extra => Object.assign({tag: 'P', className: 'description', id: '', style: '', hasControls: false, text: long}, extra);

test('long plain descriptions move into the (i) button', () => {
    const help = load();
    assert.equal(help.shouldMove(p()), true);
    assert.equal(help.shouldMove(p({className: ' description '})), true);
});

test('short, toggled, opt-out or interactive descriptions stay visible', () => {
    const help = load();
    assert.equal(help.shouldMove(p({text: 'Select a JSON file exported from WP Statistics.'})), false);
    assert.equal(help.shouldMove(p({id: 'wps-geoip-credit'})), false, 'JS shows/hides it by id');
    assert.equal(help.shouldMove(p({style: 'display:none'})), false);
    assert.equal(help.shouldMove(p({className: 'description description--keep'})), false);
    assert.equal(help.shouldMove(p({hasControls: true})), false);
    assert.equal(help.shouldMove(p({tag: 'DIV'})), false);
});

test('import field keeps the id and name the Customization add-on reads', () => {
    const tpl = fs.readFileSync('includes/admin/templates/settings/add-ons/customization.php', 'utf8');
    for (const s of ['id="wps-input-import-file"', 'name="import_file"', 'id="wps-input-import-images"', 'name="import_images"',
        'id="wps-btn-import-settings"', 'id="wps-btn-export-settings"', 'class="wps-export__checkbox"', 'name="addons[]"']) {
        assert.ok(tpl.includes(s), s);
    }
    assert.match(tpl, /<label for="wps-input-import-file" class="wps-button wps-button--default wps-file-input__button">/);
    assert.match(tpl, /wps-button--primary wps-import__button" id="wps-btn-import-settings"/);
});

test('built CSS has the help button, colour field and file field styles', () => {
    const css = fs.readFileSync('assets/css/admin.min.css', 'utf8');
    for (const s of ['.wps-setting-help', '.wps-file-input__native', '.wp-picker-input-wrap.hidden', '.wp-picker-holder']) {
        assert.ok(css.includes(s), s);
    }
});
