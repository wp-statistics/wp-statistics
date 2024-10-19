wps_js.summary_meta_box = {

    summary_statistics: function (args = []) {
        let t = '<thead>';

        // Show Visitor Online
        // -- Moved to bottom

        // Show Visitors and Visits
        t += `<tr><th width="50%">` + wps_js._('time') + `</th>`;
        ["visitors", "visits"].forEach(function (key) {
            t += `<th>` + (wps_js.is_active(key) ? wps_js._(key) : ``) + `</th>`;
        });
        t += `</tr>`;
        t += '</thead>';
        t += '<tbody>';

        if (Object.keys(args).length) {
            // Show Statistics in Days
            let summary_item = ["today", "yesterday", "this-week", "last-week", "this-month", "last-month", "7days", "30days", "90days", "6months", "this-year", "total"];
            for (let i = 0; i < summary_item.length; i++) {
                t += `<tr><td><b>${wps_js._(summary_item[i])}</b></td>`;
                ["visitors", "visits"].forEach(function (key) {
                    if (typeof args[key] === 'undefined') {
                        t += `<td></td>`;
                    } else {
                        t += `<td>` + (wps_js.is_active(key) ? `<a href="${args[key][summary_item[i]]['link']}"><span class="quickstats-values">${args[key][summary_item[i]]['value']}</span></a>` : ``) + `</td>`;
                    }
                });
                t += `</tr>`;
            }
            t += '</tbody>';
        }

        return t;
    },

    user_online: function (args = []) {
        let t = '';
        if (args['user_online']) {
            t = `<div class="c-live">
                <div>
                    <span class="c-live__status"></span>
                    <span class="c-live__title">${wps_js._('online_users')}</span>
                </div>
            <div class="c-live__online">
                <span class="c-live__online--value">${args['user_online']['value']}</span>
                <a className="c-live__value" href="${args['user_online']['link']}"><span class="c-live__online--arrow"></span></a>
            </div>`;

            t += `</div>`;
        }
        return t;
    },

    view: function (args = []) {
        let t = '';
        t += this.user_online(args);
        t += `<div class="o-table-wrapper"><table width="100%" class="o-table o-table--wps-summary-stats">`;

        // Summary Statistics
        t += this.summary_statistics(args);

        t += `</table></div>`;

             // Enable weekly email summaries
        if (wps_js.global.stats_report_option == false) {
            t += `<div class="wp-quickstats-widget__enable-email">
                    <div class="wp-quickstats-widget__enable-email__desc">
                        <span class="wp-quickstats-widget__enable-email__icon"></span>
                        <div>
                            <p>${wps_js._('receive_weekly_email_reports')}</p>
                            <a href="${wps_js.global.setting_url}&tab=notifications-settings" title="${wps_js._('enable_now')}">${wps_js._('enable_now')}</a>
                        </div>
                    </div>
                    <div class="wp-quickstats-widget__enable-email__close"><span class="wp-close" title="${wps_js._('close')}" onclick="this.parentElement.parentElement.remove()"></span></div>
                    </div>`;
        }

        return t;
    }
};
