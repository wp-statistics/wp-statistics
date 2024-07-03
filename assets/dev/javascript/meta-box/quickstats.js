wps_js.quickstats_meta_box = {

    view: function (args = []) {
        let t = '';

        t += wps_js.summary_meta_box.user_online(args);

        t += `<div class="o-table-wrapper"><table width="100%" class="o-table"><tbody>`;

        //Summary Statistics
        t += wps_js.summary_meta_box.summary_statistics(args);

        t += `</tbody></table></div>`;
        // Show Chart JS
        t += `<canvas id="` + wps_js.chart_id('quickstats') + `" height="210"></canvas>`;

        if (wps_js.global.stats_report_option == false) {
            // Enable weekly email summaries
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
            // link to the Overview page
        }
        t +=`<a href="${wps_js.global.admin_url}/admin.php?page=wps_overview_page#wpbody-content" title="${wps_js._('view_detailed_analytics')}" class="wp-quickstats-widget__link">${wps_js._('view_detailed_analytics')}</a>`;
        return t;
    },

    meta_box_init: function (args = []) {
        wps_js.hits_meta_box.hits_chart(wps_js.chart_id('quickstats'), args);
    }
};