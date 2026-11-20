const assert = require('node:assert/strict');
const fs = require('node:fs');
const test = require('node:test');
const vm = require('node:vm');

// The source file and the built tracker must classify links the same way.
const trackerFiles = [
    'assets/dev/javascript/event-tracker.js',
    'assets/js/tracker.js',
];

const fileExtensions = ['pdf', 'xlsx?', 'docx?', 'txt', 'rtf', 'csv', 'exe', 'key', 'pp(s|t|tx)', '7z', 'pkg', 'rar', 'gz', 'zip', 'avi', 'mov', 'mp4', 'mpe?g', 'wmv', 'midi?', 'mp3', 'wav', 'wma', 'vcf'];

function loadEventTracker(file, {downloadQueryKeys = ['protected_download', 'download', 'file', 'filename']} = {}) {
    const window = {location: {href: 'https://site.test/', host: 'site.test', pathname: '/', search: ''}, addEventListener() {}};
    const context = {
        window,
        URL,
        URLSearchParams,
        history: {pushState() {}, replaceState() {}},
        navigator: {},
        document: {referrer: '', cookie: '', addEventListener() {}, querySelector() { return null; }, querySelectorAll() { return []; }, getElementById() { return null; }},
        console: {log() {}, warn() {}, error() {}},
        setTimeout() {},
        clearTimeout() {},
        setInterval() {},
        fetch: async () => ({ok: true}),
        WP_Statistics_Tracker_Object: {hitParams: {source_id: 1}, option: {}, downloadQueryKeys},
        WP_Statistics_DataPlus_Event_Object: {fileExtensions, eventAjaxUrl: 'https://site.test/wp-admin/admin-ajax.php', options: {downloadTracker: true, linkTracker: true}},
    };
    context.window.window = context.window;
    vm.createContext(context);
    vm.runInContext(fs.readFileSync(file, 'utf8'), context);
    const tracker = vm.runInContext('typeof WpStatisticsEventTracker !== "undefined" ? WpStatisticsEventTracker : window.WpStatisticsEventTracker', context);
    tracker.downloadTracker = true;
    tracker.linkTracker = true;
    return tracker;
}

function classify(tracker, href) {
    const event = {
        type: 'click',
        button: 0,
        target: {textContent: 'link'},
        currentTarget: {tagName: 'A', id: '', className: '', href, classList: {contains: () => false}},
    };
    return tracker.extractLinkData(event, {en: 'click', fn: '', fx: ''});
}

for (const file of trackerFiles) {
    test(`${file}: plain file links are downloads`, () => {
        const data = classify(loadEventTracker(file), 'https://site.test/wp-content/uploads/report.pdf');
        assert.equal(data.en, 'file_download');
        assert.equal(data.fn, 'report');
        assert.equal(data.fx, 'pdf');
    });

    test(`${file}: dotted file names keep everything before the last dot`, () => {
        const data = classify(loadEventTracker(file), 'https://site.test/files/Report.final.v2.pdf');
        assert.equal(data.fn, 'Report.final.v2');
        assert.equal(data.fx, 'pdf');
    });

    test(`${file}: files served through a known download parameter are downloads`, () => {
        const tracker = loadEventTracker(file);
        for (const href of ['https://site.test/?protected_download=FSK50-Datei.pdf', 'https://site.test/?protected_download=FSK50-Datei.pdf&ver=2']) {
            const data = classify(tracker, href);
            assert.equal(data.en, 'file_download', href);
            assert.equal(data.fn, 'FSK50-Datei', href);
            assert.equal(data.fx, 'pdf', href);
        }
    });

    test(`${file}: other parameters ending in a file extension are not downloads`, () => {
        const tracker = loadEventTracker(file);
        // Outbound share link stays an outbound click.
        const share = classify(tracker, 'https://twitter.com/intent/tweet?url=https://site.test/a.pdf');
        assert.equal(share.en, 'click');
        // Internal search, player and login links are not tracked at all.
        for (const href of ['https://site.test/?s=setup.exe', 'https://site.test/player/?src=intro.mp4', 'https://site.test/wp-login.php?redirect_to=https://site.test/a.pdf']) {
            assert.equal(classify(tracker, href), false, href);
        }
    });

    test(`${file}: the parameter list can be changed`, () => {
        const tracker = loadEventTracker(file, {downloadQueryKeys: ['dl']});
        assert.equal(classify(tracker, 'https://site.test/?dl=a.zip').en, 'file_download');
        assert.equal(classify(tracker, 'https://site.test/?protected_download=a.zip'), false);
    });
}
