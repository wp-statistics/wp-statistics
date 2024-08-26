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

        // Prepare Chart Data
        let datasets = [];
        let i = 0;
        const data = {
            data: {
                labels: args.data?.labels,
                ...args.data.datasets.reduce((acc, item) => {
                    acc[item.label] = item.data;
                    return acc;
                }, {})
            },
            previousData:{
                labels: args.previousData?.labels,
                ...args.previousData.datasets.reduce((acc, item) => {
                    acc[item.label] = item.data;
                    return acc;
                }, {})
            }
        };

        wps_js.new_line_chart(data, wps_js.chart_id('search'), null)
    },

};