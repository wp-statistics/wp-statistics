wps_js.render_line_chart = function (response, key) {
    const keyName = key.replace(/_/g, '-');
    if (response && response.response) {
        wps_js.metaBoxInner(keyName).html(response.response.output);
        if (response.response?.data) {
            let params = response.response.data;
            const data = {
                data: params['data'],
                previousData: params['previousData']
            };
            if (keyName !== 'wp_statistics_quickstats_metabox') {
                wps_js.new_line_chart(data, `${keyName}-chart`);
            } else {
                const trafficOptions = {
                    scales: {
                        x: {
                            offset: 1,
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
                const trafficChart = wps_js.new_line_chart(data, `wps_${keyName}_meta_chart`, trafficOptions);

                function toggleDataset(datasetIndex) {
                    const meta = trafficChart.getDatasetMeta(datasetIndex);
                    meta.hidden = !meta.hidden;
                    trafficChart.update();
                }

                const visitorsElement = document.querySelector('#wp-statistics-quickstats-widget .wps-postbox-chart--items:nth-child(2) .wps-postbox-chart--item:nth-child(1) .current-data');
                const viewsElement = document.querySelector('#wp-statistics-quickstats-widget .wps-postbox-chart--items:nth-child(2) .wps-postbox-chart--item:nth-child(2) .current-data');
                if (visitorsElement) {
                    visitorsElement.addEventListener('click', function () {
                        this.querySelector('span:first-child').classList.toggle('wps-line-through');
                        toggleDataset(0);
                    });
                }
                if (viewsElement) {
                    viewsElement.addEventListener('click', function () {
                        this.querySelector('span:first-child').classList.toggle('wps-line-through');
                        toggleDataset(1);
                    });
                }

            }
        }
        wps_js.initDatePickerHandlers();
    }
};


wps_js.render_wp_statistics_search_widget = wps_js.render_line_chart;
wps_js.render_wp_statistics_hits_widget = wps_js.render_line_chart;
wps_js.render_wp_statistics_traffic_summary_widget = wps_js.render_line_chart;
wps_js.render_wp_statistics_quickstats_widget = wps_js.render_line_chart;
