wps_js.summary_meta_box = {

    summary_statistics: function (args = []) {
        let t = '<thead>';

        // Show Visitor Online
        // -- Moved to bottom

        // Show Visitors and Visits
        if (wps_js.is_active('visitors') || wps_js.is_active('visits')) {
            t += `<tr><th width="60%">` + wps_js._('time') + `</th>`;
            ["visitors", "visits"].forEach(function (key) {
                t += `<th>` + (wps_js.is_active(key) ? wps_js._(key) : ``) + `</th>`;
            });
            t += `</tr>`;
            t += '</thead>';
            t += '<tbody>';
            // Show Statistics in Days
            let summary_item = ["today", "yesterday", "last-week", "week", "month", "60days", "90days", "year", "this-year", "last-year"];
            for (let i = 0; i < summary_item.length; i++) {
                t += `<tr><td style="text-align: left !important;">${wps_js._(summary_item[i])}</td>`;
                ["visitors", "visits"].forEach(function (key) {
                    t += `<td>` + (wps_js.is_active(key) ? `<a href="${args[key][summary_item[i]]['link']}"><span>${args[key][summary_item[i]]['value']}</span></a>` : ``) + `</td>`;
                });
                t += `</tr>`;
            }
            t += '</tbody>';

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
        t += `<div class="o-table-wrapper"><table width="100%" class="o-table o-table--wps-summary-stats">`;

        // Summary Statistics
        t += this.summary_statistics(args);

        t += `</table></div>`;
        return t;
    }

};
