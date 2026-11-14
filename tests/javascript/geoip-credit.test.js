const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

test('GeoIP credit follows Select2 provider changes', () => {
    const template = fs.readFileSync('includes/admin/templates/settings/advanced.php', 'utf8');
    const match = template.match(/<script>\s*([\s\S]*?wps-geoip-credit[\s\S]*?)<\/script>/);

    assert.ok(match, 'GeoIP credit script is present');

    let changeHandler;
    let creditVisible;
    const method = {value: 'maxmind'};
    const $ = target => {
        if (target === '#wps_settings\\[geoip_location_detection_method\\]') {
            return {
                on(event, callback) {
                    assert.equal(event, 'change');
                    changeHandler = callback;
                },
            };
        }

        if (target === '#wps-geoip-credit') {
            return {
                toggle(visible) {
                    creditVisible = visible;
                },
            };
        }

        if (target === method) {
            return {val: () => method.value};
        }

        throw new Error(`Unexpected jQuery target: ${String(target)}`);
    };
    const jQuery = callback => callback($);

    vm.runInNewContext(match[1], {jQuery});
    assert.equal(typeof changeHandler, 'function', 'change is bound through jQuery');

    method.value = 'cf';
    changeHandler.call(method);
    assert.equal(creditVisible, false, 'Cloudflare hides the database credit');

    method.value = 'dbip';
    changeHandler.call(method);
    assert.equal(creditVisible, true, 'database providers show the credit');
});
