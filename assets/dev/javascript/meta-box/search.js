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

        // Set Total
        if (args['total']['active'] === 1) {
            datasets.push({
                label: wps_js._('total'),
                data: args['total']['stat'],
            });
        }

        Object.keys(args['search-engine']).forEach(function (key) {
            let search_engine_name = args['search-engine'][key]['name'];
            let color = wps_js.random_color(i);
            datasets.push({
                label: search_engine_name,
                data: args['stat'][search_engine_name],
            });
            i++;
        });

        const labels = datasets.map(item => item.label);

        const data = {
            data: {
                labels: args['date'],
                ...datasets.reduce((acc, item) => {
                    acc[item.label] = item.data;
                    return acc;
                }, {})
            },


        };
        if (args['total']['active'] === 1) {
            const totalData = datasets.filter(item => item.label === wps_js._('total'))[0].data;
            data.previousData = {
                labels: args['date'],
                [wps_js._('total')]: totalData
            };
        }
        //Todo chart Add
        wps_js.new_line_chart(data, wps_js.chart_id('search'), null)
    },

};