wps_js.render_line_chart = function (response, key) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
        if (response.response?.data) {
            let params = response.response.data;
            const data = {
                data: params['data'],
                previousData: params['previousData']
            };
            if(key !== 'traffic_overview'){
                wps_js.new_line_chart(data, `wps_${key}_meta_chart`);
            }else{
                const trafficOptions = {

                    scales: {
                        x: {
                            offset:  1,
                            grid: {
                                display: false,
                                drawBorder: false,
                                tickLength: 0,
                                drawTicks: false
                            },
                            border: {
                                color: 'transparent',
                                width: 0
                            },
                            ticks: {
                                align: 'inner',
                                autoSkip: true,
                                maxTicksLimit: 3,
                                font: {
                                    color: '#AAABAE',
                                     weight: 'lighter',
                                    size: 11
                                },
                                padding: 8,
                            }
                        },
                        y: {
                            min: 0,
                            ticks: {
                                font: {
                                    color: '#AAABAE',
                                    weight: 'lighter',
                                    size: 12
                                },
                                fontColor: '#AAABAE',
                                fontSize: 12,
                                fontWeight: 'lighter ',
                                padding: 8,
                                lineHeight: 14.06,
                                stepSize: 1
                            },
                            border: {
                                color: 'transparent',
                                width: 0
                            },
                            type: 'linear',
                            position: 'left',
                            grid: {
                                display: true,
                                tickMarkLength: 0,
                                drawBorder: false,
                                tickColor: '#EEF1F7',
                                color: '#EEF1F7'
                            },
                            gridLines: {
                                drawTicks: false
                            },
                            title: {
                                display: false,
                            }
                        }
                    },
                };
                wps_js.new_line_chart(data, `wps_${key}_meta_chart` , trafficOptions);
            }
        }
        wps_js.initDatePickerHandlers();
    }
};


wps_js.render_search_engines = wps_js.render_line_chart;
wps_js.render_daily_traffic_trend = wps_js.render_line_chart;
wps_js.render_traffic_overview = wps_js.render_line_chart;

