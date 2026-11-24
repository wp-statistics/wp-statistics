/**
 * Leaderboard tables (views/components/leaderboard.php) and "Rank by" lists
 * (views/components/content-rank-list.php).
 *
 * - Server-ranked tables (one <tbody data-ranking="views|published|views_per_post"> per metric):
 *   a header button shows that metric's own top rows, high to low. aria-sort moves to that header.
 * - Older tables without rankings: a header button sorts the shown rows in the browser, first
 *   click high to low, second click low to high; ranks are renumbered.
 * - The "Rank by" menu shows one ranked list at a time.
 * Delegated, so cards added later (AJAX, add-ons) work without setup.
 */
(function (root) {
    /**
     * Order rows by a numeric key. Ties keep the server order (by views).
     *
     * @param {Array<{index:number, values:Object}>} rows
     * @param {string} key
     * @param {'descending'|'ascending'} direction
     * @returns {Array} New array, sorted.
     */
    function order(rows, key, direction) {
        var sign = direction === 'ascending' ? 1 : -1;

        return rows.slice().sort(function (a, b) {
            var x = parseFloat(a.values[key]) || 0;
            var y = parseFloat(b.values[key]) || 0;

            if (x !== y) return (x - y) * sign;

            return a.index - b.index;
        });
    }

    /**
     * The direction a click on a header gives: a new column starts high to low,
     * the sorted column flips.
     */
    function nextDirection(current) {
        return current === 'descending' ? 'ascending' : 'descending';
    }

    var api = {order: order, nextDirection: nextDirection, showRanking: showRanking};

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = api;
    }

    if (!root || !root.document) return;

    root.wpsLeaderboard = api;
    var document = root.document;

    /**
     * Show the server ranking for a key. Returns false when the table has no rankings.
     */
    function showRanking(table, th, key) {
        var bodies = Array.prototype.slice.call(table.tBodies).filter(function (body) {
            return body.hasAttribute('data-ranking');
        });
        if (!bodies.length) return false;

        var target = bodies.filter(function (body) {
            return body.getAttribute('data-ranking') === key;
        })[0];
        if (!target) return true;

        bodies.forEach(function (body) {
            body.hidden = body !== target;
        });

        Array.prototype.forEach.call(table.querySelectorAll('thead th[aria-sort]'), function (cell) {
            cell.removeAttribute('aria-sort');
        });
        th.setAttribute('aria-sort', 'descending');

        return true;
    }

    function sortTable(button) {
        var th    = button.closest('th');
        var table = button.closest('table');
        if (!th || !table || !table.tBodies[0]) return;

        if (showRanking(table, th, button.getAttribute('data-sort-key'))) return;

        var tbody     = table.tBodies[0];
        var key       = button.getAttribute('data-sort-key');
        var direction = th.hasAttribute('aria-sort') ? nextDirection(th.getAttribute('aria-sort')) : 'descending';
        var trs       = Array.prototype.slice.call(tbody.rows);

        // Rank labels (localized digits) in their current top-to-bottom order: 1, 2, 3 ...
        var labels = trs.map(function (tr) {
            return tr.cells[0].textContent;
        });

        var rows = trs.map(function (tr) {
            var values = {};
            values[key] = tr.getAttribute('data-' + key);
            return {index: parseInt(tr.getAttribute('data-rank'), 10) || 0, values: values, tr: tr};
        });

        order(rows, key, direction).forEach(function (row, i) {
            row.tr.cells[0].textContent = labels[i];
            tbody.appendChild(row.tr);
        });

        Array.prototype.forEach.call(table.querySelectorAll('thead th[aria-sort]'), function (cell) {
            cell.removeAttribute('aria-sort');
        });
        th.setAttribute('aria-sort', direction);
    }

    function showList(select) {
        var card = document.getElementById(select.getAttribute('data-wps-rank-by'));
        if (!card) return;

        Array.prototype.forEach.call(card.querySelectorAll('[data-rank-list]'), function (list) {
            list.hidden = list.getAttribute('data-rank-list') !== select.value;
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest('.wps-leaderboard__sort') : null;
        if (button) sortTable(button);
    });

    document.addEventListener('change', function (event) {
        var select = event.target;
        if (select && select.matches && select.matches('[data-wps-rank-by]')) showList(select);
    });

    // The browser may restore a menu value on back/forward: show the matching list.
    root.addEventListener('pageshow', function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-wps-rank-by]'), showList);
    });
})(typeof window !== 'undefined' ? window : null);
