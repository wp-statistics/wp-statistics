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
        },

        generateViewsPerPostsChart: function () {
            if (!wps_js.isset(Wp_Statistics_Author_Analytics_Object, 'views_per_posts_chart_data')) {
                return;
            }

            const publishedChartData = Wp_Statistics_Author_Analytics_Object.views_per_posts_chart_data;

            const chartImageUrls     = publishedChartData.map(point => point.img);

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
                    label: 'Views/Published Posts',
                    data: publishedChartData,
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
                                text: 'Published Posts',
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
                                text: 'Post Views',
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
                            position: 'top'
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
            if (!wps_js.isset(Wp_Statistics_Author_Analytics_Object, 'publish_overview_chart_data')) {
                return;
            }

            const overviewPublishData = {
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
                                    return ['Date: ' + v.d, 'Value: ' + v.v.toFixed(2)];
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
