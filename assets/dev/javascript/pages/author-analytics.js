if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "author-analytics") {

    const wpStatisticsAuthorAnalytics = {
        init: function () {
            if (typeof Wp_Statistics_Author_Analytics_Object == "undefined") {
                console.log('Variable Wp_Statistics_Author_Analytics_Object not found.');
                return;
            }

            this.generateCharts()
        },

        generateCharts: function () {
            this.generatePublishingOverviewChart();
        },

        generatePublishingOverviewChart: function () {
            const data = {
                datasets: [{
                    label: 'overview',
                    data: Wp_Statistics_Author_Analytics_Object.publish_overview_chart_data,
                    backgroundColor(c) {
                        const value = c.dataset.data[c.dataIndex].v;
                        const alpha = (10 + value) / 60;
                        const colors = ['#E8EAEE', '#B28DFF', '#5100FD', '#4915B9', '#250766'];
                        const index = Math.floor(alpha * colors.length);
                        let color = colors[index];
                        return Chart.helpers.color(color).rgbString();
                    },
                    borderColor: 'transparent',
                    borderWidth: 4,
                    borderRadius: 2,
                    boxShadow: 0,
                    width(c) {
                        const a = c.chart.chartArea || {};
                        return ((a.right - a.left) / 53 - 1) - 2;
                    },
                    height(c) {
                        const a = c.chart.chartArea || {};
                        return ((a.bottom - a.top) / 7 - 1) - 1;
                    }
                }]
            }

            //scales
            const scales = {
                y: {
                    type: 'time',
                    offset: true,
                    time: {
                        unit: 'day',
                        round: 'day',
                        isoWeek: 1,
                        parser: 'i',
                        displayFormats: {
                            day: 'iiiiii'
                        }
                    },
                    reverse: true,
                    position: 'left',
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        padding: 5,
                        color: '#000',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                        tickLength: 0,
                    },
                    border: {
                        display: false
                    },
                },
                x: {
                    type: 'time',
                    offset: true,
                    position: 'top',
                    time: {
                        unit: 'month',
                        round: 'week',
                        isoWeekday: 1,
                        displayFormats: {
                            week: 'MMM'
                        }
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        padding: 5,
                        color: '#000000',
                        font: {
                            size: 12
                        },
                        callback: function (value, index, values) {
                            const date = new Date(value);
                            const month = date.toLocaleString('default', {
                                month: 'short'
                            });
                            const day = date.getDate();
                            return day === 1 ? month : month + ' ' + day;
                        }
                    },
                    border: {
                        display: false
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                        tickLength: 0,
                    }
                }
            }


            const config = {
                type: 'matrix',
                data,
                options: {
                    maintainAspectRatio: false,
                    scales: scales,
                    aspectRatio: 10,
                    animation: false,
                    plugins: {
                        chartAreaBorder: {
                            borderWidth: 5,
                            borderColor: '#fff',
                        },
                        legend: false,
                        tooltip: {
                            displayColors: false,
                            callbacks: {
                                title() {
                                    return '';
                                },
                                label(context) {
                                    const v = context.dataset.data[context.dataIndex];
                                    return ['Date: ' + v.d, 'Value: ' + v.v.toFixed(2)];
                                }
                            }
                        }
                    }
                }
            };

            new Chart(document.getElementById('myChart'), config);
        }
    };

    jQuery(document).ready(function () {
        wpStatisticsAuthorAnalytics.init();
    });
}
