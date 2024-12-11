wps_js.render_line_chart = function(response, key) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
        if (response.response?.data) {
            let params = response.response.data;
            const data = {
                data: params['data'],
                previousData: params['previousData']
            };
            wps_js.new_line_chart(data, `wps_${key}_meta_chart`);
        }
        wps_js.initDatePickerHandlers();
    }
};

 
wps_js.render_search_engines = wps_js.render_line_chart;
wps_js.render_daily_traffic_trend = wps_js.render_line_chart;

wps_js.render_traffic_hour = function(response, key) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
    }
    if (response.response?.data) {
        let params = response.response.data;

    // Initialize the chart if the element exists
    const chartEl = document.querySelector('#hourly-usage-chart');
    if (chartEl && typeof params !== "undefined") {
        new Chart(chartEl, {
            type: 'bar',
            data: params,
            options: {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 90,
                            minRotation: 90,
                            callback: function (val, index) {
                                return ' ' + this.getLabelForValue(val);
                            }
                        }
                    },
                    y: {
                        ticks: {
                            stepSize: 1,
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
    }

    wps_js.initDatePickerHandlers();

};