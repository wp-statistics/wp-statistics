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
                    <span class="c-live__title">${wps_js._('online_users')}:</span>
                    <span><a class="c-live__value" href="${args['user_online']['link']}">${args['user_online']['value']}</a></span>
                </div>`;

            if (args['real_time_button']) {
                t += `
                <a target="_blank" class="${args['real_time_button']['class']}" href="${args['real_time_button']['link']}" title="${args['real_time_button']['title']}">
                    ${wps_js._('Realtime')} 
                </a>`;
            }

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
        return t;
    }
};
