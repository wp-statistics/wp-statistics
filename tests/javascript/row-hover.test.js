const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');

// Owner rule: hovering a table row or list item changes nothing. Only the link (or pill)
// under the pointer reacts. Checks the built admin CSS (run `npx gulp compileSass` first).
const files = ['assets/css/admin.min.css', 'assets/css/rtl.min.css'].filter(f => fs.existsSync(f));

// A selector where a row-level :hover styles the row itself or something inside it.
const ROW_HOVER = /\b(tr|li)\b[^\s>+~,]*:hover(?![\w-])[^,]*/;

function rowHoverRules(css) {
    css = css.replace(/\/\*[\s\S]*?\*\//g, '');
    const found = [];
    for (const m of css.matchAll(/([^{}]+)\{([^{}]*)\}/g)) {
        const decl = m[2];
        if (!/(^|;)\s*(background(-color)?|color|text-decoration(-line)?|border(-bottom)?(-color)?|opacity|visibility|filter)\s*:/.test(decl)) continue;
        for (const sel of m[1].split(',').map(s => s.trim())) {
            const hit = sel.match(ROW_HOVER);
            if (!hit) continue;
            // The li/tr itself is the hovered link-like target (tabs, menu items) when nothing
            // follows the :hover part and it is not a table row.
            if (/^\s*$/.test(hit[0].replace(/^[^:]*:hover/, '')) && !/\btr\b/.test(hit[0])) continue;
            found.push(sel + ' { ' + decl.trim().slice(0, 80) + ' }');
        }
    }
    return found;
}

// Resets that undo add-on row hovers (Data Plus trackers) are allowed: they bring the row back to rest.
const ALLOWED = [
    /^#wpbody \.wps-tracker-analytics \.o-table-wrapper tbody tr:hover>td/,
    /^\.wps-wrap ul\.tabs li:hover/,
];

test('no table row hover changes colours, backgrounds or shows icons', () => {
    assert.ok(files.length, 'built CSS present');
    for (const file of files) {
        const bad = rowHoverRules(fs.readFileSync(file, 'utf8')).filter(r => !ALLOWED.some(a => a.test(r)));
        assert.deepEqual(bad, [], file);
    }
});

test('the checker catches a row hover tint', () => {
    assert.equal(rowHoverRules('.x tbody tr:hover > td{background:#fafafb}').length, 1);
    assert.equal(rowHoverRules('.x tr:hover:not(:first-child) td{background:#eee}').length, 1);
    assert.equal(rowHoverRules('.x tr td a:hover span{color:#404bf2}').length, 0);
});

// Owner rule (round 7): content links in tables (titles, domains, linked numbers) use the text
// colour at rest; brand blue + underline only on the link's own hover or keyboard focus.
test('content links in tables are text coloured at rest and brand only on their own hover', () => {
    const css = fs.readFileSync('assets/css/admin.min.css', 'utf8').replace(/\s+/g, ' ');
    const rest = css.match(/#wpbody \.o-table td a:not\(\.wps-button\)[^{]*\{([^}]*)\}/);
    assert.ok(rest, 'content link rule present');
    assert.match(rest[1], /color:\s*#0c0c0d( !important)?/i);
    const hover = css.match(/#wpbody \.o-table td a:not\(\.wps-button\)[^{,]*:hover[^{]*\{([^}]*)\}/);
    assert.ok(hover, 'hover rule present');
    assert.match(hover[1], /color:\s*#404bf2/i);
    assert.match(hover[1], /text-decoration:\s*underline/);
});
