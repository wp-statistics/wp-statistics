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

            const hasAllZeroData = wps_js.allDatasetsZero(data.data?.datasets, data.previousData?.datasets);
            const hasNoData = !data.data?.datasets || data.data.datasets.length === 0;
            if (hasNoData || hasAllZeroData) {
                const chartItem = document.getElementById(`${keyName}-chart`);
                const parentElement = chartItem.parentElement;
                const wrap = parentElement.closest('.o-wrap');
                const chartData = wrap?.querySelector('.wps-postbox-chart--data');
                const toDate = response.filters?.date?.to;

                let noDataMessage = wps_js._('no_data_this_range');
                if (toDate && wps_js.isTodayOrFutureDate(toDate)) {
                    noDataMessage = wps_js._('coming_soon');
                }

                parentElement.innerHTML = wps_js.placeholder_txt(noDataMessage);
                parentElement.classList.add('wps-no-result');
                document.querySelectorAll('.wps-ph-item').forEach(el => el.remove());
                if (chartData) chartData.style.display = 'none';
            } else {
                if (keyName !== 'wp-statistics-quickstats-widget' && keyName !== 'wp-statistics-search-traffic-widget') {
                    wps_js.new_line_chart(data, `${keyName}-chart`);
                }

                if (keyName === 'wp-statistics-search-traffic-widget') {
                    const newOptions = {
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    color: '#898A8E',
                                    font: {
                                        size: 13
                                    }
                                },
                                grid: {display: false, drawBorder: false, tickLength: 0, drawTicks: false},
                                border: {color: 'transparent', width: 0},
                            },
                            y: {
                                position: 'right',
                                title: {
                                    display: true,
                                    text: wps_js._('impressions'),
                                    color: '#898A8E',
                                    rotation: 2000,
                                    font: {
                                        size: 13
                                    }
                                },
                                ticks: {
                                    maxTicksLimit: 7,
                                    callback: formatNumChart
                                },
                                grid: {display: false, drawBorder: false, tickLength: 0, drawTicks: false},
                                border: {display: false, color: 'transparent', width: 0},
                            },
                            y1: {
                                border: {
                                    color: 'transparent',
                                    width: 0
                                },
                                type: 'linear',
                                position: 'left',
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: 7,
                                    fontColor: '#898A8E',
                                    fontSize: 13,
                                    padding: 8,
                                    lineHeight: 15,
                                    callback: renderFormatNum
                                },
                                afterBuildTicks: wpsBuildTicks,
                                title: {
                                    display: true,
                                    text: wps_js._('clicks'),
                                    color: '#898A8E',
                                    font: {
                                        size: 13
                                    }
                                },
                                grid: {display: true, borderDash: [5, 5], tickColor: '#EEEFF1', color: '#EEEFF1'},
                            }
                        }
                    }
                    wps_js.new_line_chart(data, `${keyName}-chart`, newOptions);
                }
                if (keyName === 'wp-statistics-quickstats-widget') {
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
                                    callback: renderFormatNum,
                                },
                                afterBuildTicks: wpsBuildTicks,
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
                    const trafficChart = wps_js.new_line_chart(data, `wp-statistics-quickstats-widget-chart`, trafficOptions);

                    function toggleDataset(datasetIndex) {
                        const meta = trafficChart.chart.getDatasetMeta(datasetIndex);
                        meta.hidden = !meta.hidden;
                        trafficChart.chart.update();
                    }

                    document.querySelectorAll('#wp-statistics-quickstats-widget .wps-postbox-chart--items:nth-child(2) .wps-postbox-chart--item')
                        .forEach((item, index) => {
                            const spanElement = item.querySelector('.current-data span:first-child');
                            if (spanElement) {
                                item.addEventListener('click', () => {
                                    spanElement.classList.toggle('wps-line-through');
                                    toggleDataset(index);
                                });
                            }
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
wps_js.render_wp_statistics_search_traffic_widget = wps_js.render_line_chart;
