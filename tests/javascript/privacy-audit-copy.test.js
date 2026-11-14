const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');

const files = [
    'src/Service/Admin/PrivacyAudit/Faqs/RequireCookieBanner.php',
    'src/Service/Admin/PrivacyAudit/Audits/RecommendConsent.php',
    'views/components/privacy-audit/cookie-banner.php',
    'views/components/privacy-audit/recommend-consent-unpassed.php',
    'includes/admin/templates/settings/privacy.php',
];

test('Privacy Audit does not guarantee compliance or unconditional consent rules', () => {
    const copy = files.map(file => fs.readFileSync(file, 'utf8')).join('\n');

    for (const claim of [
        'ensures privacy compliance',
        'Why a Cookie Banner is Not Required',
        'does not require consent by default',
        'Consent Plugin Required for Privacy Compliance',
        'is essential to protect user data',
        'adhering to privacy laws',
        'until they give consent',
    ]) {
        assert.doesNotMatch(copy, new RegExp(claim, 'i'), `remove unconditional claim: ${claim}`);
    }

    assert.match(copy, /depend[^.]*settings and applicable laws/i);
    assert.match(copy, /may still need a cookie banner/i);
});
