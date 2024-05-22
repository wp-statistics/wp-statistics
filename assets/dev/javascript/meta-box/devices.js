wps_js.devices_meta_box = {

    placeholder: function () {
        return wps_js.circle_placeholder();
    },

    view: function (args = []) {

        // Create Html
        let html = '';

        // Add Chart
        html += '<div class="o-wrap"><div class="c-chart c-chart--limited-height"><canvas id="' + wps_js.chart_id('devices') + '" height="220"></canvas></div></div>';

        // show Data
        return html;
    },

    meta_box_init: function (args = []) {

        // Get Background Color
        let backgroundColor = [];
        let color;
        for (let i = 0; i <= 20; i++) {
            color = wps_js.random_color(i);
            backgroundColor.push('rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + '0.4)');
        }

        // Prepare Data
        let data = [{
            label: wps_js._('device'),
            data: args['device_value'],
            backgroundColor: backgroundColor,
            tension: 0.4
        }];

        const label_callback = function (tooltipItem) {
            return tooltipItem.label;
        }

        const title_callback = (ctx) => {
            return wps_js._('visitors') + ':' + ctx[0].formattedValue
        }

        // Show Chart
        wps_js.pie_chart(wps_js.chart_id('devices'), args['device_name'], data, label_callback, title_callback);
    }

};
