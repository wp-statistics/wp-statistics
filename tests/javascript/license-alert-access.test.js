const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');

const helper = fs.readFileSync('src/Service/Admin/LicenseManagement/LicenseHelper.php', 'utf8');
const factory = fs.readFileSync('src/Service/Admin/Notification/NotificationFactory.php', 'utf8');
const header = fs.readFileSync('includes/admin/templates/header.php', 'utf8');

test('license notices are limited to main-site license managers', () => {
    assert.match(helper, /function canManageLicenses\(\)/);
    assert.match(helper, /is_main_site\(\)\s*&&\s*\$manageCap\s*&&\s*current_user_can\(\$manageCap\)/);
});

test('the bell card and the header badge check that gate before reading the add-on list', () => {
    assert.match(factory, /if \(!LicenseHelper::canManageLicenses\(\)\)\s*\{\s*return null;/);
    assert.match(header, /LicenseHelper::canManageLicenses\(\) \? LicenseHelper::getLicenseSummary\(\)/);
});
