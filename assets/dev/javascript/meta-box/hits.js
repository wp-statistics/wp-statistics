wps_js.hits_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {

        // Check Hit Chart size in Different Page
        let height = wps_js.is_active('overview_page') ? 300 : 210;
        if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "hits") {
            height = 80;
        }

        // Create Html
        let html = '';

        // // Check Show Button Group
        // if (wps_js.is_active('overview_page')) {
        //     html += wps_js.btn_group_chart('hits', args);
        //     setTimeout(function(){ wps_js.date_picker(); }, 1000);
        // }

        // Add Chart
        html += '<div class="o-wrap"><div class="wps-postbox-chart--data"><div class="wps-postbox-chart--items"></div><div class="wps-postbox-chart--previousPeriod">' + wps_js._('previous_period') + '</div></div><div class="wps-postbox-chart--container"><canvas id="' + wps_js.chart_id('hits') + '" height="' + height + '"></canvas></div></div>';

        // show Data
        return html;
    },

    meta_box_init: function (args = []) {

        // Show chart
        this.hits_chart(wps_js.chart_id('hits'), args);

        // Set Total For Hits Page
        if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "hits") {
            ["visits", "visitors"].forEach(function (key) {
                let tag = "span[id^='number-total-chart-" + key + "']";
                if (wps_js.exist_tag(tag)) {
                    jQuery(tag).html(args.total[key]);
                }
            });
        }
    },

    hits_chart: function (tag_id, args = []) {

        // Check Hit-chart for Quick State
        let params = args;

        if (document.getElementById(tag_id)) {
             const data = {
                data: params['data'],
                previousData: params['previousData']
            };

            wps_js.new_line_chart(data, tag_id, null);
        }
     }
};
