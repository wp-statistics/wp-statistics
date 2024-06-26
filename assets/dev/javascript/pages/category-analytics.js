
    const wpStatisticscategoryAnalytics = {
        data: [],

        init: function () {
             this.generateCharts()
        },
        generateCharts: function () {
            if (document.getElementById('performance-category-chart'))  this.generatePerformanceChart();
            if (document.getElementById('performance-category-chart-single'))  this.generatePerformanceChartSingle();
            if (document.getElementById('category_operating_systems')) this.generateOperatingSystemChart();
            if (document.getElementById('category_browsers')) this.generateBrowsersChartData();
            if (document.getElementById('category_device_models')) this.generateDeviceModelsChart();
            if (document.getElementById('category_device_usage')) this.generateDeviceUsageChart();
            if (document.getElementById('category-search-engines-chart')) this.generateSearchEngineChart();
        },
        legendHandel:function (chart){
            document.querySelectorAll('.wps-category-analytics-chart--item').forEach((legendItem, index) => {
                legendItem.addEventListener('click', () => {
                    const dataset = chart.data.datasets[index];
                    dataset.hidden = !dataset.hidden;
                    chart.update();
                    legendItem.classList.toggle('hidden', dataset.hidden);
                });
            });
        },
        generatePerformanceChart: function () {
            const performanceData = {
                labels: ['1 Apr', '2 Apr', '3 Apr', '4 Apr', '5 Apr', '6 Apr', '7 Apr', '8 Apr', '9 Apr', '10 Apr', '11 Apr', '12 Apr', '13 Apr', '14 Apr', '15 Apr'],
                views: [10, 15, 30, 25, 30, 35, 30, 45, 20, 15, 45, 15, 20, 25, 30],
                visitors: [5, 10, 15, 20, 25, 30, 25, 20, 15, 10, 5, 10, 15, 20, 25]
            };
            const performance = document.getElementById('performance-category-chart').getContext('2d');
            const performanceChart = new Chart(performance, {
                type: 'bar',
                data: {
                    labels: performanceData.labels,
                    datasets: [
                        {
                            type: 'line',
                            label: wps_js._('visits'),
                            cubicInterpolationMode: 'monotone',
                            data: performanceData.views,
                            borderColor: '#0e9444',
                            backgroundColor: '#0e9444',
                            pointRadius: 5,
                            pointStyle: 'circle',
                            fill: false,
                            yAxisID: 'y',
                            pointBorder: 5,
                            pointBorderColor: '#fff',
                            pointWidth: 5.5,
                            pointHeight: 5.5,
                            pointBackgroundColor: '#0e9444',
                            lineTension: 0.5
                        },
                        {
                            type: 'line',
                            label: wps_js._('visitors'),
                            cubicInterpolationMode: 'monotone',
                            data: performanceData.visitors,
                            borderColor: '#4915b9',
                            backgroundColor: '#4915b9',
                            pointRadius: 5,
                            fill: false,
                            yAxisID: 'y',
                            pointBorder: 5,
                            pointBorderColor: '#fff',
                            pointWidth: 5.5,
                            pointHeight: 5.5,
                            pointBackgroundColor: '#4915b9',
                            lineTension: 0.5
                        },
                        {
                            type: 'bar',
                            label: wps_js._('Published Posts'),
                            data: [5, 7, 6, 5, 9, 8, 7, 6, 5, 8, 7, 6, 5, 8, 7],
                            backgroundColor: 'rgba(159,165,248,0.7)',
                            yAxisID: 'y1',
                            borderRadius: {topLeft: 10, topRight: 10},
                        },
                    ]
                },
                options: {
                    plugins: {
                        legend: false
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false,
                                tickLength: 0,
                            }
                        },
                        y: {
                            type: 'linear',
                            position: 'right',
                            grid: {
                                display: true,
                                borderDash: [5, 5]
                            },
                            title: {
                                display: true,
                                text: wps_js._('Views'),
                                color: '#0e9444'
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'left',
                            grid: {
                                display: false,
                                drawBorder: false,
                                tickLength: 0,
                            },
                            title: {
                                display: true,
                                text: 'Published Posts',
                                color: '#9fa5f8',
                            }
                        }
                    }
                }
            });
            this.legendHandel(performanceChart);
        },
        generatePerformanceChartSingle: function () {
            const performanceSingleData = {
                labels: ['1 Apr', '2 Apr', '3 Apr', '4 Apr', '5 Apr', '6 Apr', '7 Apr', '8 Apr', '9 Apr', '10 Apr', '11 Apr', '12 Apr', '13 Apr', '14 Apr', '15 Apr'],
                views: [10, 15, 30, 25, 30, 35, 30, 45, 20, 15, 45, 15, 20, 25, 30]};
            const performanceSingle = document.getElementById('performance-category-chart-single').getContext('2d');
            const performanceSingleChart = new Chart(performanceSingle, {
                type: 'bar',
                data: {
                    labels: performanceSingleData.labels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'Views',
                            cubicInterpolationMode: 'monotone',
                            data: performanceSingleData.views,
                            borderColor: '#0e9444',
                            backgroundColor: '#0e9444',
                            pointRadius: 5,
                            pointStyle: 'circle',
                            fill: false,
                            yAxisID: 'y',
                            pointBorder: 5,
                            pointBorderColor: '#fff',
                            pointWidth: 5.5,
                            pointHeight: 5.5,
                            pointBackgroundColor: '#0e9444',
                            lineTension: 0.5
                        }
                    ]
                },
                options: {
                    plugins: {
                        legend: false
                    },
                    scales: {
                        x: {
                            grid: {
                                display: true,
                                borderDash: [5, 5]
                            }
                        },
                        y: {
                            type: 'linear',
                            position: 'left',
                            ticks: {
                                callback: function (value, index, values) {
                                    return value + 'K';
                                }
                            },
                            title: {
                                display: true,
                                text: 'Views',
                                color: '#0e9444'
                            }
                        }
                    }
                }
            });
            this.legendHandel(performanceSingleChart);
        },
        generateOperatingSystemChart: function () {
            const OperatingSystemData = {
                labels: ['Windows', 'macOs', 'iOS', 'Android', 'Linux', 'Other'],
                data: [30, 20, 10, 5, 7, 5],
            };

            const label_callback_category_operating_systems = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_category_operating_systems = (ctx) => {
                return `${wps_js._('visitors')} :` + ctx[0].formattedValue
            }
            const data_category_operating_systems = {
                labels: OperatingSystemData.labels,
                datasets: [{
                    data: OperatingSystemData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_category_operating_systems = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        hidden: false,
                        labels: {
                            padding: 13,
                            fontSize: 13,
                            fontWeight: 500,
                            color: '#56585A',
                            usePointStyle: true,
                            pointStyle: 'rect',
                            pointRadius: 2
                        }
                    },
                    tooltip: {
                        enable: true,
                        callbacks: {
                            label: label_callback_category_operating_systems,
                            title: tooltip_callback_category_operating_systems
                        }
                    }
                }
            };
            const ctx_category_operating_systems = document.getElementById('category_operating_systems').getContext('2d');
            const chart_category_operating_systems = new Chart(ctx_category_operating_systems, {
                type: 'pie',
                data: data_category_operating_systems,
                options: options_category_operating_systems
            });
        },
        generateBrowsersChartData: function () {
            const browsersData = {
                labels: ['Chrome', 'Firefox', 'Safari', 'Opera', 'edge', 'Other'],
                data: [30, 20, 10, 5, 7, 5],
            };

            const label_callback_category_browsers = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_category_browsers = (ctx) => {
                return `${wps_js._('visitors')}: ` + ctx[0].formattedValue
            }
            const data_category_browsers = {
                labels: browsersData.labels,
                datasets: [{
                    data: browsersData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_category_browsers = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        hidden: false,
                        labels: {
                            padding: 13,
                            fontSize: 13,
                            fontWeight: 500,
                            color: '#56585A',
                            usePointStyle: true,
                            pointStyle: 'rect',
                            pointRadius: 2
                        }
                    },
                    tooltip: {
                        enable: true,
                        callbacks: {
                            label: label_callback_category_browsers,
                            title: tooltip_callback_category_browsers
                        }
                    }
                }
            };
            const ctx_category_browsers = document.getElementById('category_browsers').getContext('2d');
            const chart_category_browsers = new Chart(ctx_category_browsers, {
                type: 'pie',
                data: data_category_browsers,
                options: options_category_browsers
            });
        },
        generateDeviceModelsChart: function () {
            const deviceModelData = {
                labels: ['Macintosh', 'iPhone', 'G6', 'A3', 'Galaxy A52', 'Other'],
                data: [30, 20, 10, 5, 7, 5],
                bg: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7']
            };

            const label_callback_category_device_model = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_category_device_model = (ctx) => {
                return `${wps_js._('visitors')}: ` + ctx[0].formattedValue
            }
            const data_category_device_model = {
                labels: deviceModelData.labels,
                datasets: [{
                    data: deviceModelData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_category_device_model = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        hidden: false,
                        labels: {
                            padding: 13,
                            fontSize: 13,
                            fontWeight: 500,
                            color: '#56585A',
                            usePointStyle: true,
                            pointStyle: 'rect',
                            pointRadius: 2
                        }
                    },
                    tooltip: {
                        enable: true,
                        callbacks: {
                            label: label_callback_category_device_model,
                            title: tooltip_callback_category_device_model
                        }
                    }
                }
            };
            const ctx_category_device_model = document.getElementById('category_device_models').getContext('2d');
            const chart_category_device_model = new Chart(ctx_category_device_model, {
                type: 'pie',
                data: data_category_device_model,
                options: options_category_device_model
            });
        },
        generateDeviceUsageChart: function () {
            const deviceUsageData = {
                labels: ['Desktop', 'Mobile:smart', 'Tablet', 'Signage', 'Television', 'Other'],
                data: [30, 20, 10, 5, 7, 5],
            };
            const label_callback_category_device_usage = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_category_device_usage = (ctx) => {
                return `${wps_js._('visitors')}: ` + ctx[0].formattedValue
            }
            const data_category_device_usage = {
                labels: deviceUsageData.labels,
                datasets: [{
                    data: deviceUsageData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_category_device_usage = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        hidden: false,
                        labels: {
                            padding: 13,
                            fontSize: 13,
                            fontWeight: 500,
                            color: '#56585A',
                            usePointStyle: true,
                            pointStyle: 'rect',
                            pointRadius: 2
                        }
                    },
                    tooltip: {
                        enable: true,
                        callbacks: {
                            label: label_callback_category_device_usage,
                            title: tooltip_callback_category_device_usage
                        }
                    }
                }
            };
            const ctx_category_device_usage = document.getElementById('category_device_usage').getContext('2d');
            const chart_category_usage = new Chart(ctx_category_device_usage, {
                type: 'pie',
                data: data_category_device_usage,
                options: options_category_device_usage
            });
        },
        generateSearchEngineChart: function () {
            const searchData = {
                labels: [
                    "17 Mar", "18 Mar", "19 Mar", "20 Mar", "21 Mar", "22 Mar", "23 Mar",
                    "24 Mar", "25 Mar", "26 Mar", "27 Mar", "28 Mar", "29 Mar", "30 Mar", "31 Mar",
                    "1 Apr", "2 Apr", "3 Apr", "4 Apr", "5 Apr", "6 Apr", "7 Apr", "8 Apr", "9 Apr",
                    "10 Apr", "11 Apr", "12 Apr", "13 Apr", "14 Apr", "15 Apr", "16 Apr"
                ],
                datasets: [
                    {
                        label: 'Bing',
                        data: [5, 10, 2, 7, 6, 5, 3, 8, 4, 7, 6, 5, 6, 9, 4, 7, 6, 5, 6, 7, 8, 9, 6, 4, 5, 6, 8, 9, 7, 6, 5],
                    },
                    {
                        label: 'DuckDuckGo',
                        data: [3, 8, 5, 7, 6, 5, 4, 7, 6, 5, 6, 7, 5, 8, 6, 7, 6, 5, 7, 8, 6, 5, 4, 6, 7, 8, 9, 6, 5, 4, 6],
                    },
                    {
                        label: 'Google',
                        data: [36, 45, 38, 35, 30, 25, 24, 37, 32, 28, 27, 30, 29, 38, 32, 35, 29, 28, 30, 35, 36, 37, 30, 28, 29, 33, 40, 37, 36, 32, 31],
                    },
                    {
                        label: 'Yahoo',
                        data: [4, 6, 3, 7, 6, 5, 4, 6, 7, 8, 5, 4, 6, 7, 8, 9, 6, 5, 4, 6, 7, 8, 9, 5, 4, 6, 7, 8, 9, 6, 5],
                    },
                    {
                        label: 'Yandex',
                        data: [6, 9, 6, 7, 6, 5, 7, 8, 6, 5, 4, 6, 7, 8, 9, 6, 5, 7, 8, 9, 6, 5, 4, 6, 7, 8, 9, 6, 5, 4, 6],
                    },
                    {
                        label: 'Total',
                        data: [26, 45, 28, 35, 30, 25, 24, 37, 32, 28, 27, 30, 29, 38, 32, 35, 29, 28, 30, 35, 36, 37, 30, 38, 30, 33, 40, 37, 36, 32, 31],
                    }
                ]
            };

            const searchEngineColors = [
                'rgba(244, 161, 31, 0.3)',
                'rgba(63, 158, 221, 0.3)',
                'rgba(195, 68, 55, 0.3)',
                'rgba(160, 98, 186, 0.3)',
                'rgba(51, 178, 105, 0.3)',
                'rgba(185, 185, 185, 0.3)'
            ];

            searchData.datasets.forEach((dataset, index) => {
                const color = searchEngineColors[index % searchEngineColors.length];
                dataset.backgroundColor = color;
                dataset.borderColor = color.replace('0.3', '1'); // Adjust alpha for borderColor
                dataset.borderWidth = 2;
                dataset.cubicInterpolationMode = 'monotone';
                dataset.pointRadius = 2;
                dataset.pointHoverRadius = 5;
                dataset.pointHoverBackgroundColor = '#fff';
                dataset.pointHoverBorderWidth = 4;
                dataset.fill = true;
            });
            const searchEngineChart = document.getElementById("category-search-engines-chart").getContext('2d');
            new Chart(searchEngineChart, {
                type: 'line',
                data: searchData,
                options: {
                    plugins: {
                        tooltip: {
                            caretPadding: 5,
                            boxWidth: 5,
                            usePointStyle: true,
                            boxPadding: 3
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    jQuery(document).ready(function () {
        wpStatisticscategoryAnalytics.init();
    });
