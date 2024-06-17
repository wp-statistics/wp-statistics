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
            this.generateViewsPerPostsChart();
            this.generateOperatingSystemChart();
            this.generateBrowsersChartData();
        },

        generateBrowsersChartData: function() {
            if (!wps_js.isset(Wp_Statistics_Author_Analytics_Object, 'browser_chart_data')) {
                return;
            }

            const chartData = Wp_Statistics_Author_Analytics_Object.browser_chart_data;

            if (chartData.data.length == 0) {
                jQuery('#wps-browsers').parent().text(wps_js._('no_result'));
                jQuery('#wps-browsers').remove();
                return;
            }

            const browsersData = {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const browsersOptions = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        hidden: false,
                        labels: {
                            padding: 13,
                            fontSize: 13,
                            color: '#56585A',
                            usePointStyle: true,
                            pointStyle: 'rect',
                            pointRadius: 2
                        }
                    }
                }
            };
            const browsersCtx = document.getElementById('wps-browsers').getContext('2d');
            const browsersChart = new Chart(browsersCtx, {
                type: 'pie',
                data: browsersData,
                options: browsersOptions
            });
        },

        generateOperatingSystemChart: function() {
            if (!wps_js.isset(Wp_Statistics_Author_Analytics_Object, 'os_chart_data')) {
                return;
            }

            const chartData = Wp_Statistics_Author_Analytics_Object.os_chart_data;

            if (chartData.labels.length == 0 || chartData.data.length ==0) {
                jQuery('#wps-operating-systems').parent().text(wps_js._('no_result'));
                jQuery('#wps-operating-systems').remove();
                return;
            }

            const operatingSystemsData = {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const operatingSystemsOptions = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        hidden: false,
                        labels: {
                            padding: 13,
                            fontSize: 13,
                            color: '#56585A',
                            usePointStyle: true,
                            pointStyle: 'rect',
                            pointRadius: 2
                        }
                    }
                }
            };
            const operatingSystemsCtx = document.getElementById('wps-operating-systems').getContext('2d');
            const operatingSystemsChart = new Chart(operatingSystemsCtx, {
                type: 'pie',
                data: operatingSystemsData,
                options: operatingSystemsOptions
            });
        },

        generateViewsPerPostsChart: function () {
            if (!wps_js.isset(Wp_Statistics_Author_Analytics_Object, 'views_per_posts_chart_data')) {
                return;
            }

            const publishedChartData = Wp_Statistics_Author_Analytics_Object.views_per_posts_chart_data;

            const chartImageUrls     = publishedChartData.data.map(point => point.img);

            const chartImages = chartImageUrls.map(url => {
                const img = new Image();
                img.src = url;
                return img;
            });

            const afterRenderPlugin = {
                id: 'afterRenderPlugin',  

                afterDraw: function (chart, args, options) {

                    const canvas = document.getElementById('publishedChart');
                    const ctx = canvas.getContext('2d');
                    chart.data.datasets.forEach((dataset, datasetIndex) => {
                        dataset.data.forEach((point, index) => {
                            const img = chartImages[index % chartImages.length];
                            const x = chart.scales.x.getPixelForValue(point.x);
                            const y = chart.scales.y.getPixelForValue(point.y);
                            const radius = 15;
                            const borderWidth = 2; // Adjust border width
                            const centerX = x - radius;
                            const centerY = y - radius;

                            // Draw border circle
                            ctx.beginPath();
                            ctx.arc(x, y, radius + borderWidth, 0, 2 * Math.PI);
                            ctx.lineWidth = borderWidth * 2;
                            ctx.strokeStyle = 'rgba(81,0,253,20%)';
                            ctx.stroke();
                            ctx.closePath();

                            // Clip to the circle
                            ctx.save();
                            ctx.beginPath();
                            ctx.arc(x, y, radius, 0, 2 * Math.PI);
                            ctx.clip();

                            // Draw image
                            ctx.drawImage(img, centerX, centerY, radius * 2, radius * 2);
                            ctx.restore();
                        });
                    });
                }
            };

            Chart.register(afterRenderPlugin);

            Chart.Tooltip.positioners.top = function (element, eventPosition) {
                const tooltip = this;

                const { chartArea: { bottom }, scales: { x, y } } = this.chart;

                return {
                    x: x.getPixelForValue(x.getValueForPixel(eventPosition.x)),
                    y: y.getPixelForValue(y.getValueForPixel(eventPosition.y)) - 20,
                    xAlign: 'center',
                    yAlign: 'bottom'
                }
            }

            const publishedData = {
                datasets: [{
                    label: publishedChartData.chartLabel,
                    data: publishedChartData.data,
                    backgroundColor: '#E8EAEE'
                }],
            };
            const publishedConfig = {
                type: 'scatter',
                data: publishedData,
                options: {
                    responsive: true,
                    pointRadius: 16,
                    pointHoverRadius: 16,
                    tooltipPosition: {
                        x: 10,
                        y: 30
                    },
                    layout: {
                        padding: {
                            Right: 20,
                            Left: 20,
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',

                            ticks: {
                                stepSize: 4,
                                color: '#56585A',
                                fontSize: 13,
                                padding: 15,
                            },
                            title: {
                                display: true,
                                text: publishedChartData.yAxisLabel,
                                fontSize: 14,
                                color: '#000'
                            },
                            grid: {
                                drawBorder: false,
                                tickLength: 0,
                            }
                        },
                        x: {
                            type: 'linear',
                            position: 'bottom',
                            title: {
                                display: true,
                                text: publishedChartData.xAxisLabel,
                                fontSize: 14,
                                color: '#000'
                            },
                            ticks: {
                                stepSize: 50000,
                                autoSkip: false,
                                maxRotation: 90,
                                minRotation: 90,
                                color: '#56585A',
                                padding: 15,
                                fontSize: 13
                            },
                            grid: {
                                drawBorder: false,
                                tickLength: 0,
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            position: 'top',
                            callbacks: {
                                label: function(context) {
                                    const point = context.raw;
                                    return [
                                        `${wps_js._('visits')}/${wps_js._('published')}: (${point.x}, ${point.y})`,
                                        `${wps_js._('author')}: ${point.author}`
                                    ];
                                }
                            }
                        }
                    },
                },
                plugins: [afterRenderPlugin]
            };

            new Chart(
                document.getElementById('publishedChart'),
                publishedConfig
            );
        },

        generatePublishingOverviewChart: function () {
            if (!wps_js.isset(Wp_Statistics_Author_Analytics_Object, 'publish_chart_data')) {
                return;
            }

            function interpolateColor(minColor, maxColor, minValue, maxValue, value) {
                const colors = ['#B28DFF', '#5100FD', '#4915B9', '#250766']; // Colors array
                const index = Math.floor((value - minValue) / (maxValue - minValue) * (colors.length - 1));
                const clampedIndex = Math.min(Math.max(index, 0), colors.length - 1);
                return colors[clampedIndex];
            }

            const backgroundColor = (c) => {
                const value = c.dataset.data[c.dataIndex].v;
                const minValue = Math.min(...c.dataset.data.map(data => data.v));
                const maxValue = Math.max(...c.dataset.data.map(data => data.v));
                 if (value === 0) {
                    return '#e8eaee';
                }
                const interpolatedColor = interpolateColor('#B28DFF', '#250766', minValue, maxValue, value);

                return interpolatedColor;
             };

            const overviewPublishData = {
                datasets: [{
                    label: 'overview',
                    data: Wp_Statistics_Author_Analytics_Object.publish_chart_data,
                    backgroundColor: (c) => backgroundColor(c),
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
            const overviewPublishScales = {
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


            const overviewPublishConfig = {
                type: 'matrix',
                data:overviewPublishData,
                options: {
                    maintainAspectRatio: false,
                    scales: overviewPublishScales,
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
                                    return [
                                        `${wps_js._('date')}: ${v.d}`, 
                                        `${wps_js.global.active_post_type}: ${v.v}`
                                    ];
                                }
                            }
                        }
                    }
                }
            };

            new Chart(document.getElementById('overviewPublishChart'), overviewPublishConfig);
        }
    };

    jQuery(document).ready(function () {
        wpStatisticsAuthorAnalytics.init();
    });
}