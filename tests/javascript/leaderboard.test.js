const assert = require('node:assert/strict');
const test = require('node:test');
const path = require('node:path');

// The sort helpers are exported when the file runs under Node (no window).
const {order, nextDirection} = require(path.resolve('assets/dev/javascript/components/leaderboard.js'));

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
