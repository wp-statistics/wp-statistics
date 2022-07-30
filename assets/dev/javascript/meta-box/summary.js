wps_js.summary_meta_box = {

    summary_statistics: function (args = []) {
        let t = '';

        // Show Visitor Online
        // -- Moved to bottom

        // Show Visitors and Visits
        if (wps_js.is_active('visitors') || wps_js.is_active('visits')) {
            t += `<tr><th width="60%"></th>`;
            ["visitors", "visits"].forEach(function (key) {
                t += `<th class="th-center">` + (wps_js.is_active(key) ? wps_js._(key) : ``) + `</th>`;
            });
            t += `</tr>`;

            // Show Statistics in Days
            let summary_item = ["today", "yesterday", "last-week", "week", "month", "60days", "90days", "year", "this-year", "last-year"];
            for (let i = 0; i < summary_item.length; i++) {
                t += `<tr><th>${wps_js._(summary_item[i])}</th>`;
                ["visitors", "visits"].forEach(function (key) {
                    t += `<th class="th-center">` + (wps_js.is_active(key) ? `<a href="${args[key][summary_item[i]]['link']}"><span>${args[key][summary_item[i]]['value']}</span></a>` : ``) + `</th>`;
                });
                t += `</tr>`;
            }

        }

        return t;
    },

    view: function (args = []) {
        let t = '';
        if (args['user_online']) {
            t = `<div class="c-live">
                    <span class="c-live__status"></span><span class="c-live__title">${wps_js._('online_users')}:</span> <span><a class="c-live__value" href="${args['user_online']['link']}">${args['user_online']['value']}</a></span>
                </div>`;
        }
        t += `<table width="100%" class="widefat table-stats wps-summary-stats"><tbody>`;

        // Summary Statistics
        t += this.summary_statistics(args);

        t += `</tbody></table>`;
        return t;
    }

};
