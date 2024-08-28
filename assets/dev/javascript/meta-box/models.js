wps_js.models_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {

        // Create Html
        let html = '';

        // Add Chart
        html += '<div class="o-wrap"><div class="c-chart c-chart--limited-height"><canvas id="' + wps_js.chart_id('models') + '" height="220"></canvas></div></div>';

        // show Data
        return html;
    },

    meta_box_init: function (args = []) {
        // Prepare Data
        let data = [{
            label: wps_js._('model'),
            data: args['model_value'],
         }];

        const label_callback = function (tooltipItem) {
            return tooltipItem.label;
        }

        const title_callback = (ctx) => {
            return wps_js._('visitors') + ':' + ctx[0].formattedValue
        }

        // Show Chart
        wps_js.horizontal_bar(wps_js.chart_id('models'), args['model_name'], data[0].data , null );
     }

};
