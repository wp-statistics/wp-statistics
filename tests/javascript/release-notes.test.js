const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');

// The newest release in CHANGELOG.md and in the readme.txt changelog must list the same bullets.
function latestRelease(file, headingPattern) {
    const text = fs.readFileSync(file, 'utf8');
    const match = text.match(headingPattern);
    assert.ok(match, `a release heading exists in ${file}`);
    const section = text.slice(match.index).split(/\n\s*\n/, 1)[0].split('\n');
    return {version: match[1], bullets: section.slice(1)};
}

test('latest release notes match in CHANGELOG.md and readme.txt', () => {
    const changelog = latestRelease('CHANGELOG.md', /^(\d+\.\d+(?:\.\d+)?) - .+$/m);
    const readme = latestRelease('readme.txt', /^= (\d+\.\d+(?:\.\d+)?) - .+ =$/m);

    assert.equal(readme.version, changelog.version, 'both files start with the same version');
    assert.ok(changelog.bullets.length > 0, 'the latest release has bullets');
    assert.deepEqual(readme.bullets, changelog.bullets, 'CHANGELOG.md and readme.txt use the same release bullets');
});
