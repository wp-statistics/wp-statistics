const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');

function releaseBullets(file, heading) {
    const text = fs.readFileSync(file, 'utf8');
    const start = text.indexOf(heading);
    assert.notEqual(start, -1, `${heading} exists in ${file}`);
    const section = text.slice(start).split(/\n\s*\n/, 1)[0];
    return section.split('\n').slice(1);
}

test('14.16.19 release notes cover the notification and license UI changes', () => {
    const changelog = releaseBullets('CHANGELOG.md', '14.16.19 - Unreleased');
    const readme = releaseBullets('readme.txt', '= 14.16.19 - Unreleased =');

    assert.deepEqual(readme, changelog, 'CHANGELOG.md and readme.txt use the same release bullets');
    assert.ok(changelog.some(line => line.includes('scheduled promotional notifications')));
    assert.ok(changelog.some(line => line.includes('license attention')));
});
