wps_js.render_wp_statistics_hourly_usage_widget = function (response, key) {

    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
    }
    if (response.response?.data) {
        let params = response.response.data;
        const data = {
            data: params['data'],
            previousData: params['previousData']
        };

        wps_js.TrafficHourCharts(data)


    }
    wps_js.initDatePickerHandlers();
};