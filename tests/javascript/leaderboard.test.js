const assert = require('node:assert/strict');
const test = require('node:test');
const path = require('node:path');

// The sort helpers are exported when the file runs under Node (no window).
const {order, nextDirection, showRanking} = require(path.resolve('assets/dev/javascript/components/leaderboard.js'));

// Top Authors, last 30 days (design notes): views, posts with views, views per post.
const rows = [
    {index: 1, name: 'Anastasia', values: {views: '196', posts_with_views: '4', views_per_post: '49'}},
    {index: 2, name: 'Floyd', values: {views: '156', posts_with_views: '4', views_per_post: '39'}},
    {index: 3, name: 'Elsa', values: {views: '140', posts_with_views: '4', views_per_post: '35'}},
    {index: 4, name: 'Cleveland', values: {views: '94', posts_with_views: '3', views_per_post: '31.3'}},
    {index: 5, name: 'Giovanny', values: {views: '75', posts_with_views: '4', views_per_post: '18.8'}},
];

test('first click sorts high to low, numbers compared as numbers', () => {
    const sorted = order(rows, 'views_per_post', 'descending').map(r => r.name);
    assert.deepEqual(sorted, ['Anastasia', 'Floyd', 'Elsa', 'Cleveland', 'Giovanny']);
});

test('ascending flips the order', () => {
    const sorted = order(rows, 'views', 'ascending').map(r => r.name);
    assert.deepEqual(sorted, ['Giovanny', 'Cleveland', 'Elsa', 'Floyd', 'Anastasia']);
});

test('ties keep the server order (by views)', () => {
    const sorted = order(rows, 'posts_with_views', 'descending').map(r => r.name);
    assert.deepEqual(sorted, ['Anastasia', 'Floyd', 'Elsa', 'Giovanny', 'Cleveland']);
});

test('does not change the input array', () => {
    const copy = rows.map(r => r.name);
    order(rows, 'views', 'ascending');
    assert.deepEqual(rows.map(r => r.name), copy);
});

test('a new column starts high to low, the sorted one flips', () => {
    assert.equal(nextDirection(null), 'descending');
    assert.equal(nextDirection('ascending'), 'descending');
    assert.equal(nextDirection('descending'), 'ascending');
});

test('missing values count as zero', () => {
    const sorted = order([{index: 1, values: {}}, {index: 2, values: {views: '3'}}], 'views', 'descending');
    assert.equal(sorted[0].index, 2);
});

// Server-ranked tables: one <tbody data-ranking data-direction> per metric and direction.
function fakeTable(keys, directions) {
    directions = directions || ['descending', 'ascending'];
    const bodies = [];
    directions.forEach((direction) => keys.forEach((key) => {
        bodies.push({
            key,
            direction,
            hidden: !(key === keys[0] && direction === 'descending'),
            hasAttribute: (name) => name === 'data-ranking',
            getAttribute: (name) => (name === 'data-ranking' ? key : name === 'data-direction' ? direction : null),
        });
    }));
    const headers = keys.map(() => {
        const attrs = {};
        return {
            attrs,
            setAttribute: (n, v) => (attrs[n] = v),
            removeAttribute: (n) => delete attrs[n],
            hasAttribute: (n) => n in attrs,
            getAttribute: (n) => (n in attrs ? attrs[n] : null),
        };
    });
    headers[0].attrs['aria-sort'] = 'descending';
    const shown = () => bodies.filter((b) => !b.hidden).map((b) => b.key + ':' + b.direction);
    return {
        bodies,
        headers,
        shown,
        table: {
            tBodies: bodies,
            querySelectorAll: () => headers.filter((h) => 'aria-sort' in h.attrs),
        },
    };
}

test('server ranking: Posts header shows the published ranking, not the views rows re-ordered', () => {
    const {table, headers, shown} = fakeTable(['views', 'published', 'views_per_post']);

    assert.equal(showRanking(table, headers[1], 'published'), true);
    assert.deepEqual(shown(), ['published:descending']);
    assert.equal(headers[1].attrs['aria-sort'], 'descending');
    assert.equal('aria-sort' in headers[0].attrs, false);
});

test('server ranking: clicks on the same header toggle high-to-low and low-to-high', () => {
    const {table, headers, shown} = fakeTable(['views', 'published', 'views_per_post', 'comments']);

    // Views is sorted on load: the first click flips it.
    showRanking(table, headers[0], 'views');
    assert.deepEqual(shown(), ['views:ascending']);
    assert.equal(headers[0].attrs['aria-sort'], 'ascending');

    showRanking(table, headers[0], 'views');
    assert.deepEqual(shown(), ['views:descending']);
    assert.equal(headers[0].attrs['aria-sort'], 'descending');

    // A new header starts high to low, then flips, then back.
    const seen = [];
    for (let i = 0; i < 3; i++) {
        showRanking(table, headers[3], 'comments');
        seen.push(headers[3].attrs['aria-sort'] + '/' + shown().join());
    }
    assert.deepEqual(seen, ['descending/comments:descending', 'ascending/comments:ascending', 'descending/comments:descending']);
    assert.equal('aria-sort' in headers[0].attrs, false, 'only one header is sorted');

    // Moving to another header resets to high to low.
    showRanking(table, headers[2], 'views_per_post');
    assert.deepEqual(shown(), ['views_per_post:descending']);
});

test('server ranking without ascending lists stays high to low', () => {
    const {table, headers, shown} = fakeTable(['views', 'published'], ['descending']);

    showRanking(table, headers[1], 'published');
    showRanking(table, headers[1], 'published');
    assert.deepEqual(shown(), ['published:descending']);
    assert.equal(headers[1].attrs['aria-sort'], 'descending');
});

test('tables without rankings fall back to the browser sort', () => {
    const table = {tBodies: [{hasAttribute: () => false}], querySelectorAll: () => []};
    assert.equal(showRanking(table, {}, 'views'), false);
});
