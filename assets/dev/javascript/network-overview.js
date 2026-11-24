/**
 * Network Admin > Statistics: sort, search and page the site table (views/pages/network/overview.php).
 * Runs over the rows already in the page, so it is fine up to a few hundred sites.
 * Without this script every row shows, sorted by visitors.
 */
(function () {
    'use strict';

    var i18n = window.wpsNetworkOverview || {};

    function format(template, values) {
        var index = 0;
        return String(template).replace(/%(\d+\$)?s/g, function (match, position) {
            var value = position ? values[parseInt(position, 10) - 1] : values[index++];
            return value === undefined ? '' : value;
        });
    }

    function number(value) {
        try {
            return Number(value).toLocaleString(document.documentElement.lang || undefined);
        } catch (e) {
            return String(value);
        }
    }

    function init(wrap) {
        var table = wrap.querySelector('.wps-network-table');
        if (!table) {
            return;
        }

        var tbody    = table.tBodies[0];
        var rows     = Array.prototype.slice.call(tbody.querySelectorAll('.wps-network-table__row'));
        var noMatch  = tbody.querySelector('.wps-network-table__no-match');
        var search   = document.querySelector('[data-network-search]');
        var pagerTxt = wrap.querySelector('[data-network-pager-text]');
        var pagerBtn = wrap.querySelector('[data-network-pager-buttons]');
        var perPage  = parseInt(wrap.getAttribute('data-per-page'), 10) || i18n.perPage || 10;
        var state    = {sort: 'visitors', dir: 'desc', query: '', page: 0};

        function value(row, key) {
            if (key === 'name') {
                return row.getAttribute('data-name') || '';
            }
            return parseFloat(row.getAttribute('data-' + key)) || 0;
        }

        function compare(a, b) {
            var x = value(a, state.sort), y = value(b, state.sort), order;
            if (state.sort === 'name') {
                order = x.localeCompare(y);
            } else {
                order = x < y ? -1 : (x > y ? 1 : 0);
            }
            if (order === 0 && state.sort !== 'name') {
                return value(a, 'name').localeCompare(value(b, 'name'));
            }
            return state.dir === 'asc' ? order : -order;
        }

        function render() {
            var query   = state.query.trim().toLowerCase();
            var matches = rows.filter(function (row) {
                return !query || (row.getAttribute('data-search') || '').indexOf(query) !== -1;
            }).sort(compare);
            var total   = matches.length;
            var pages   = Math.max(1, Math.ceil(total / perPage));

            state.page = Math.min(state.page, pages - 1);

            var start = state.page * perPage;
            var shown = matches.slice(start, start + perPage);

            rows.forEach(function (row) {
                row.hidden = true;
            });
            shown.forEach(function (row) {
                row.hidden = false;
                tbody.insertBefore(row, noMatch);
            });
            noMatch.hidden = total > 0;

            table.querySelectorAll('.wps-network-table__sort').forEach(function (button) {
                var th = button.closest('th');
                if (button.getAttribute('data-sort') === state.sort) {
                    th.setAttribute('aria-sort', state.dir === 'asc' ? 'ascending' : 'descending');
                } else {
                    th.removeAttribute('aria-sort');
                }
            });

            if (pagerTxt) {
                if (total > perPage) {
                    pagerTxt.textContent = format(i18n.range || '%1$s–%2$s of %3$s sites', [number(start + 1), number(start + shown.length), number(total)]);
                } else if (total === 1) {
                    pagerTxt.textContent = i18n.one || '1 site';
                } else {
                    pagerTxt.textContent = format(i18n.count || '%s sites', [number(total)]);
                }
            }

            if (pagerBtn) {
                pagerBtn.hidden = total <= perPage;
                pagerBtn.querySelector('[data-network-page="-1"]').disabled = state.page === 0;
                pagerBtn.querySelector('[data-network-page="1"]').disabled = state.page >= pages - 1;
            }
        }

        table.addEventListener('click', function (event) {
            var button = event.target.closest('.wps-network-table__sort');
            if (!button) {
                return;
            }
            var key = button.getAttribute('data-sort');
            if (state.sort === key) {
                state.dir = state.dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort = key;
                state.dir  = key === 'name' ? 'asc' : 'desc';
            }
            state.page = 0;
            render();
        });

        if (pagerBtn) {
            pagerBtn.addEventListener('click', function (event) {
                var button = event.target.closest('[data-network-page]');
                if (!button || button.disabled) {
                    return;
                }
                state.page += parseInt(button.getAttribute('data-network-page'), 10);
                render();
                wrap.scrollIntoView({block: 'nearest'});
            });
        }

        if (search) {
            search.addEventListener('input', function () {
                state.query = search.value;
                state.page  = 0;
                render();
            });
        }

        wrap.classList.add('is-enhanced');
        render();
    }

    function boot() {
        document.querySelectorAll('[data-network-table]').forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
