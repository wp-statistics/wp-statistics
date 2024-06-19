if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "content-analytics") {

    const wpStatisticsContentAnalytics = {
        data: [],

        init: function () {
            if (typeof Wp_Statistics_Content_Analytics_Object == "undefined") {
                console.log('Variable Wp_Statistics_Author_Analytics_Object not found.');
                return;
            } 

            this.data = Wp_Statistics_Content_Analytics_Object;
            this.generateCharts()
        },
        generateCharts: function () {
            if (document.getElementById('performance-chart')) {
                this.generatePerformanceChart();
            } else {
                this.generatePerformanceChartSingle();
            }
            this.generateOperatingSystemChart();
            this.generateBrowsersChartData();
            this.generateDeviceModelsChart();
            this.generateDeviceUsageChart();
            this.generateSearchEngineChart();
        },
        generatePerformanceChart: function () {
            const performanceData = this.data.performance_chart_data;
            const performance = document.getElementById('performance-chart').getContext('2d');
            const performanceChart = new Chart(performance, {
                type: 'bar',
                data: {
                    labels: performanceData.labels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'Views',
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
                            label: 'Visitors',
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
                            label: 'Published Posts',
                            data: performanceData.posts,
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
                                display: true,
                                borderDash: [5, 5] // This creates dashed lines for the x-axis grid
                            }
                        },
                        y: {
                            type: 'linear',
                            position: 'right',
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
                        },
                        y1: {
                            type: 'linear',
                            position: 'left',
                            ticks: {
                                callback: function (value, index, values) {
                                    return value;
                                }
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
        },
        generateOperatingSystemChart: function () {
            const OperatingSystemData = this.data.os_chart_data;

            const label_callback_content_operating_systems = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_content_operating_systems = (ctx) => {
                return 'Visitors :' + ctx[0].formattedValue
            }
            const data_content_operating_systems = {
                labels: OperatingSystemData.labels,
                datasets: [{
                    data: OperatingSystemData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_content_operating_systems = {
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
                            label: label_callback_content_operating_systems,
                            title: tooltip_callback_content_operating_systems
                        }
                    }
                }
            };
            const ctx_content_operating_systems = document.getElementById('content_operating_systems').getContext('2d');
            const chart_content_operating_systems = new Chart(ctx_content_operating_systems, {
                type: 'pie',
                data: data_content_operating_systems,
                options: options_content_operating_systems
            });
        },
        generateBrowsersChartData: function () {
            const browsersData = this.data.browser_chart_data;

            const label_callback_content_browsers = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_content_browsers = (ctx) => {
                return 'Visitors :' + ctx[0].formattedValue
            }
            const data_content_browsers = {
                labels: browsersData.labels,
                datasets: [{
                    data: browsersData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_content_browsers = {
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
                            label: label_callback_content_browsers,
                            title: tooltip_callback_content_browsers
                        }
                    }
                }
            };
            const ctx_content_browsers = document.getElementById('content_browsers').getContext('2d');
            const chart_content_browsers = new Chart(ctx_content_browsers, {
                type: 'pie',
                data: data_content_browsers,
                options: options_content_browsers
            });
        },
        generateDeviceModelsChart: function () {
            const deviceModelData = this.data.model_chart_data;

            const label_callback_content_device_model = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_content_device_model = (ctx) => {
                return 'Visitors :' + ctx[0].formattedValue
            }
            const data_content_device_model = {
                labels: deviceModelData.labels,
                datasets: [{
                    data: deviceModelData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_content_device_model = {
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
                            label: label_callback_content_device_model,
                            title: tooltip_callback_content_device_model
                        }
                    }
                }
            };
            const ctx_content_device_model = document.getElementById('content_device_models').getContext('2d');
            const chart_content_device_model = new Chart(ctx_content_device_model, {
                type: 'pie',
                data: data_content_device_model,
                options: options_content_device_model
            });
        },
        generateDeviceUsageChart: function () {
            const deviceUsageData = this.data.device_chart_data;
            const label_callback_content_device_usage = function (tooltipItem) {
                return tooltipItem.label;
            }
            const tooltip_callback_content_device_usage = (ctx) => {
                return 'Visitors :' + ctx[0].formattedValue
            }
            const data_content_device_usage = {
                labels: deviceUsageData.labels,
                datasets: [{
                    data: deviceUsageData.data,
                    backgroundColor: ['#F7D399', '#99D3FB', '#D7BDE2', '#D7BDE2', '#EBA39B', '#F5CBA7'],
                    borderColor: '#fff',
                    borderWidth: 1,
                }]
            };
            const options_content_device_usage = {
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
                            label: label_callback_content_device_usage,
                            title: tooltip_callback_content_device_usage
                        }
                    }
                }
            };
            const ctx_content_device_usage = document.getElementById('content_device_usage').getContext('2d');
            const chart_content_usage = new Chart(ctx_content_device_usage, {
                type: 'pie',
                data: data_content_device_usage,
                options: options_content_device_usage
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
            const searchEngineChart = document.getElementById("search-engines-chart").getContext('2d');
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
        },
        generatePerformanceChartSingle: function () {
            const performanceSingleData = {
                labels: ['1 Apr', '2 Apr', '3 Apr', '4 Apr', '5 Apr', '6 Apr', '7 Apr', '8 Apr', '9 Apr', '10 Apr', '11 Apr', '12 Apr', '13 Apr', '14 Apr', '15 Apr'],
                views: [10, 15, 20, 25, 30, 35, 30, 45, 20, 15, 45, 15, 20, 25, 30],
                visitors: [5, 10, 15, 20, 25, 30, 25, 20, 15, 10, 5, 10, 15, 20, 25]
            };
            const performanceSingle = document.getElementById('performance-chart-single').getContext('2d');
            const performanceChartSingle = new Chart(performanceSingle, {
                type: 'line',
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
                        },
                        {
                            type: 'line',
                            label: 'Visitors',
                            cubicInterpolationMode: 'monotone',
                            data: performanceSingleData.visitors,
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
                                    return value;
                                }
                            },
                            title: {
                                display: true,
                                text: 'Views',
                                color: '#0E9444',
                            }
                        }
                    }
                }
            });

        }
    }

    jQuery(document).ready(function () {
        wpStatisticsContentAnalytics.init();
    });
}