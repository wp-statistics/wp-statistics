const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');

const template = fs.readFileSync('views/pages/overview/overview.php', 'utf8');

test('the license banner is limited to main-site license managers', () => {
    assert.match(template, /use WP_STATISTICS\\Option;/);
    assert.match(template, /use WP_STATISTICS\\User;/);
    assert.match(
        template,
        /\$canManageLicenses\s*=\s*is_main_site\(\)\s*&&\s*\$manageCap\s*&&\s*current_user_can\(\$manageCap\);/
    );
    assert.match(template, /if \(\$canManageLicenses && \$licenseNotice\)\s*:/);
});

test('users without license access do not trigger the product summary lookup', () => {
    assert.match(
        template,
        /if \(\$canManageLicenses\) \{[\s\S]*?LicenseHelper::getLicenseSummary\(\);[\s\S]*?\}/
    );
});
