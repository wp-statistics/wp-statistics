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

// Server-ranked tables: one <tbody data-ranking> per metric, a header click shows that metric's own top rows.
function fakeTable(keys) {
    const sorted = [];
    const bodies = keys.map((key, i) => ({
        hidden: i > 0,
        hasAttribute: (name) => name === 'data-ranking',
        getAttribute: () => key,
    }));
    const headers = keys.map(() => {
        const attrs = {};
        return {
            attrs,
            setAttribute: (n, v) => (attrs[n] = v),
            removeAttribute: (n) => delete attrs[n],
            hasAttribute: (n) => n in attrs,
        };
    });
    headers[0].attrs['aria-sort'] = 'descending';
    return {
        bodies,
        headers,
        table: {
            tBodies: bodies,
            querySelectorAll: () => headers.filter((h) => 'aria-sort' in h.attrs),
        },
        sorted,
    };
}

test('server ranking: Posts header shows the published ranking, not the views rows re-ordered', () => {
    const {table, bodies, headers} = fakeTable(['views', 'published', 'views_per_post']);

    assert.equal(showRanking(table, headers[1], 'published'), true);
    assert.deepEqual(bodies.map((b) => b.hidden), [true, false, true]);
    assert.equal(headers[1].attrs['aria-sort'], 'descending');
    assert.equal('aria-sort' in headers[0].attrs, false);

    // A second click keeps the same top list (no "lowest 5" view).
    showRanking(table, headers[1], 'published');
    assert.deepEqual(bodies.map((b) => b.hidden), [true, false, true]);
});

test('tables without rankings fall back to the browser sort', () => {
    const table = {tBodies: [{hasAttribute: () => false}], querySelectorAll: () => []};
    assert.equal(showRanking(table, {}, 'views'), false);
});
