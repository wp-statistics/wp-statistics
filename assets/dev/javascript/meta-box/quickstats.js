wps_js.quickstats_meta_box = {

    view: function (args = []) {
        let t = '';

        t += wps_js.summary_meta_box.user_online(args);

        t += `<div class="o-table-wrapper"><table width="100%" class="o-table"><tbody>`;

        //Summary Statistics
        t += wps_js.summary_meta_box.summary_statistics(args);

        t += `</tbody></table></div>`;
        t += `<br><hr width="80%"/><br>`;

        // Show Chart JS
        t += `<canvas id="` + wps_js.chart_id('quickstats') + `" height="210"></canvas>`;
        return t;
    },

    meta_box_init: function (args = []) {
        wps_js.hits_meta_box.hits_chart(wps_js.chart_id('quickstats'), args);
    }
};