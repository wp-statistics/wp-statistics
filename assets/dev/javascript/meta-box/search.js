wps_js.search_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {
        // Create Html
        let html = '';

        // Add Chart
        html += '<div class="o-wrap"><div class="wps-postbox-chart--data"><div class="wps-postbox-chart--items"></div><div class="wps-postbox-chart--previousPeriod">' + wps_js._('previous_period') + '</div></div><div class="wps-postbox-chart--container"><canvas id="' + wps_js.chart_id('search') + '"></canvas></div></div>';

        // show Data
        return html;
    },

    meta_box_init: function (args = []) {
        wps_js.new_line_chart(args, wps_js.chart_id('search'))
    }
};